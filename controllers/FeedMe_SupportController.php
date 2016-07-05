<?php
namespace Craft;

class FeedMe_SupportController extends BaseController
{
    // Public Methods
    // =========================================================================
    
    public function actionSendSupportRequest()
    {
        $this->requirePostRequest();

        craft()->config->maxPowerCaptain();

        $success = false;
        $errors = array();
        $zipFile = null;
        $tempFolder = null;

        $getHelpModel = new FeedMe_GetHelpModel();
        $getHelpModel->fromEmail = craft()->request->getPost('fromEmail');
        $getHelpModel->feedIssue = craft()->request->getPost('feedIssue');
        $getHelpModel->message = trim(craft()->request->getPost('message'));
        $getHelpModel->attachLogs = (bool) craft()->request->getPost('attachLogs');
        $getHelpModel->attachSettings = (bool) craft()->request->getPost('attachSettings');
        $getHelpModel->attachFeed = (bool) craft()->request->getPost('attachFeed');
        $getHelpModel->attachFields = (bool) craft()->request->getPost('attachFields');
        $getHelpModel->attachment = UploadedFile::getInstanceByName('attachAdditionalFile');

        if ($getHelpModel->validate()) {
            $plugin = craft()->plugins->getPlugin('feedMe');
            $feed = craft()->feedMe_feeds->getFeedById($getHelpModel->feedIssue);

            // Add some extra info about this install
            $message = $getHelpModel->message . "\n\n" .
                "------------------------------\n\n" .
                'Craft '.craft()->getEditionName().' '.craft()->getVersion().'.'.craft()->getBuild() . "\n\n" .
                'Feed Me '.$plugin->getVersion();

            try {
                $zipFile = $this->_createZip();

                $tempFolder = craft()->path->getTempPath().StringHelper::UUID().'/';

                if (!IOHelper::folderExists($tempFolder)) {
                    IOHelper::createFolder($tempFolder);
                }


                //
                // Attached just the Feed Me log
                //
                if ($getHelpModel->attachLogs) {
                    if (IOHelper::folderExists(craft()->path->getLogPath())) {
                        $logFolderContents = IOHelper::getFolderContents(craft()->path->getLogPath());

                        foreach ($logFolderContents as $file) {

                            // Just grab the Feed Me log
                            if (IOHelper::fileExists($file) && basename($file) == 'feedme.log') {
                                Zip::add($zipFile, $file, craft()->path->getStoragePath());
                            }
                        }
                    }
                }

                //
                // Backup our feed settings
                //
                if ($getHelpModel->attachSettings) {
                    if (IOHelper::folderExists(craft()->path->getDbBackupPath())) {
                        $backup = craft()->path->getDbBackupPath().StringHelper::toLowerCase('feedme_'.gmdate('ymd_His').'.sql');

                        $feedInfo = $this->_prepareSqlFeedSettings($getHelpModel->feedIssue);

                        IOHelper::writeToFile($backup, $feedInfo . PHP_EOL, true, true);

                        Zip::add($zipFile, $backup, craft()->path->getStoragePath());
                    }
                }

                //
                // Save the contents of the feed
                //
                if ($getHelpModel->attachFeed) {
                    $feedData = craft()->feedMe_feed->getRawData($feed->feedUrl);

                    $tempFile = $tempFolder.'feed.'.StringHelper::toLowerCase($feed->feedType);

                    IOHelper::writeToFile($tempFile, $feedData . PHP_EOL, true, true);

                    if (IOHelper::fileExists($tempFile)) {
                        Zip::add($zipFile, $tempFile, $tempFolder);
                    }
                }

                //
                // Get some information about the fields we're mapping to - handy to know
                //
                if ($getHelpModel->attachFields) {
                    $fieldInfo = array();

                    foreach ($feed->fieldMapping as $feedHandle => $fieldHandle) {
                        $field = craft()->fields->getFieldByHandle($fieldHandle);

                        if ($field) {
                            $fieldInfo[] = $this->_prepareExportField($field);
                        }
                    }

                    // Support PHP <5.4, JSON_PRETTY_PRINT = 128, JSON_NUMERIC_CHECK = 32
                    $json = json_encode($fieldInfo, 128 | 32);

                    $tempFile = $tempFolder.'fields.json';

                    IOHelper::writeToFile($tempFile, $json . PHP_EOL, true, true);

                    if (IOHelper::fileExists($tempFile)) {
                        Zip::add($zipFile, $tempFile, $tempFolder);
                    }
                }


                //
                // Add in any additional attachments 
                //
                if ($getHelpModel->attachment) {
                    $tempFile = $tempFolder.$getHelpModel->attachment->getName();
                    $getHelpModel->attachment->saveAs($tempFile);

                    // Make sure it actually saved.
                    if (IOHelper::fileExists($tempFile)) {
                        Zip::add($zipFile, $tempFile, $tempFolder);
                    }
                }
            } catch(\Exception $e) {
                FeedMePlugin::log('Tried to attach debug logs to a support request and something went horribly wrong: '.$e->getMessage(), LogLevel::Warning, true);
            }

            $email = new EmailModel();
            $email->fromEmail = $getHelpModel->fromEmail;
            $email->toEmail = "web@sgroup.com.au";
            $email->subject = "Feed Me Support";
            $email->body = $message;

            if ($zipFile) {
                $email->addAttachment($zipFile, 'FeedMeSupportAttachment.zip', 'base64', 'application/zip');
            }

            $result = craft()->email->sendEmail($email);

            if ($result) {
                if ($zipFile) {
                    if (IOHelper::fileExists($zipFile)) {
                        IOHelper::deleteFile($zipFile);
                    }
                }

                if ($tempFolder) {
                    IOHelper::clearFolder($tempFolder);
                    IOHelper::deleteFolder($tempFolder);
                }

                $success = true;
            } else {
                $errors = array('Support' => array('Unable to contact support. Please try again soon.'));
            }
        } else {
            $errors = $getHelpModel->getErrors();
        }

        $this->returnJson(array(
            'success' => $success,
            'errors' => $errors,
        ));
    }







    // Private Methods
    // =========================================================================

    private function _createZip()
    {
        $zipFile = craft()->path->getTempPath().StringHelper::UUID().'.zip';
        IOHelper::createFile($zipFile);

        return $zipFile;
    }

    private function _prepareSqlFeedSettings($id)
    {
        if (($tablePrefix = craft()->config->get('tablePrefix', ConfigFile::Db)) !== '') {
            $tableName = craft()->config->get('tablePrefix', ConfigFile::Db) . '_feedme_feeds';
        } else {
            $tableName = 'feedme_feeds';
        }

        $row = craft()->db->createCommand('SELECT * FROM ' . craft()->db->quoteTableName($tableName) . ' WHERE id = ' . $id . ';')->queryRow();

        foreach ($row as $columnName => $value) {
            if ($value === null) {
                $row[$columnName] = 'NULL';
            } else {
                $row[$columnName] = craft()->db->getPdoInstance()->quote($value);
            }
        }

        $attrs = array_map(array(craft()->db, 'quoteColumnName'), array_keys($row));
        $insertStatement = 'INSERT INTO ' . craft()->db->quoteTableName($tableName) . ' (' . implode(', ', $attrs) . ') VALUES ' . PHP_EOL;
        $insertStatement .= '('.implode(', ', $row).');';

        return $insertStatement;
    }

    private function _prepareExportField($field)
    {
        $fieldDefs = array();

        $fieldDefs[$field->handle] = array(
            'name'         => $field->name,
            'context'      => $field->context,
            'instructions' => $field->instructions,
            'translatable' => $field->translatable,
            'type'         => $field->type,
            'settings'     => $field->settings
        );

        if ($field->type == 'Matrix') {
            $blockTypeDefs = array();
            $blockTypes = craft()->matrix->getBlockTypesByFieldId($field->id);

            foreach ($blockTypes as $blockType) {
                $blockTypeFieldDefs = array();

                foreach ($blockType->getFields() as $blockTypeField) {
                    $blockTypeFieldDefs[$blockTypeField->handle] = array(
                        'name'         => $blockTypeField->name,
                        'required'     => $blockTypeField->required,
                        'translatable' => $blockTypeField->translatable,
                        'type'         => $blockTypeField->type,
                        'settings'     => $blockTypeField->settings
                    );
                }

                $blockTypeDefs[$blockType->handle] = array(
                    'name'   => $blockType->name,
                    'fields' => $blockTypeFieldDefs
                );
            }

            $fieldDefs[$field->handle]['blockTypes'] = $blockTypeDefs;
        }

        if ($field->type == 'SuperTable') {
            $blockTypeDefs = array();
            $blockTypes = craft()->superTable->getBlockTypesByFieldId($field->id);

            foreach ($blockTypes as $blockType) {
                $blockTypeFieldDefs = array();

                foreach ($blockType->getFields() as $blockTypeField) {
                    $blockTypeFieldDefs[$blockTypeField->handle] = array(
                        'name'         => $blockTypeField->name,
                        'required'     => $blockTypeField->required,
                        'translatable' => $blockTypeField->translatable,
                        'type'         => $blockTypeField->type,
                        'settings'     => $blockTypeField->settings
                    );
                }

                $blockTypeDefs = array(
                    'fields' => $blockTypeFieldDefs
                );
            }

            $fieldDefs[$field->handle]['blockTypes'] = $blockTypeDefs;
        }

        return $fieldDefs;
    }
}
