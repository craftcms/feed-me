<?php
namespace Craft;

class FeedMe_HelpController extends BaseController
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

            // Cater for pre-Craft 2.6.2951
            if (version_compare(craft()->getVersion(), '2.6.2951', '<')) {
                $version = craft()->getVersion() . '.' . craft()->getBuild();
            } else {
                $version = craft()->getVersion();
            }

            // Add some extra info about this install
            $message = $getHelpModel->message . "\n\n" .
                "------------------------------\n\n" .
                'Craft '.craft()->getEditionName().' '.$version . "\n\n" .
                'Feed Me '.$plugin->getVersion() . "\n\n" .
                'License Key: '.craft()->feedMe_license->getLicenseKey();

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
                    // Check for and environment variables in url
                    $url = craft()->config->parseEnvironmentString($feed->feedUrl);

                    $feedData = craft()->feedMe_data->getRawData($url);

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

                    foreach ($feed->fieldMapping as $fieldHandle => $feedHandle) {
                        if ($fieldHandle && !is_array($fieldHandle)) {
                            // Check for sub-fields and options
                            $fieldHandleInfo = explode('-', $fieldHandle);
                            $fieldHandle = $fieldHandleInfo[0];

                            $field = craft()->fields->getFieldByHandle($fieldHandle);

                            if ($field && !isset($fieldInfo[$field->handle])) {
                                $fieldInfo[$field->handle] = $this->_prepareExportField($field);
                            }
                        }
                    }

                    // Strip field handles in array - we don't need them an easier to import into FM
                    $fieldInfo = array_values($fieldInfo);

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
            $email->toEmail = "support@verbb.io";
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

        $this->renderTemplate('feedMe/help/response', array(
            'success' => $success,
            'errors' => JsonHelper::encode($errors),
            'widgetId' => 'feedMeHelp',
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
        $newField = array(
            'name' => $field->name,
            'handle' => $field->handle,
            'instructions' => $field->instructions,
            'required' => $field->required,
            'translatable' => $field->translatable,
            'type' => $field->type,
            'settings' => $field->settings,
        );

        if ($field->type == 'Matrix') {
            $newField['settings'] = $this->_prepareExportMatrixField($field);
        }

        if ($field->type == 'SuperTable') {
            $newField['settings'] = $this->_prepareExportSuperTableField($field);
        }

        // Position Select - you sly dog!
        if ($field->type == 'PositionSelect') {
            $newField['settings'] = $this->_prepareExportPositionSelectField($field);
        }

        return $newField;
    }

    public function _prepareExportMatrixField($field)
    {
        $fieldSettings = $field->settings;

        $blockTypes = craft()->matrix->getBlockTypesByFieldId($field->id);

        $blockCount = 1;
        foreach ($blockTypes as $blockType) {
            $fieldSettings['blockTypes']['new' . $blockCount] = array(
                'name' => $blockType->name,
                'handle' => $blockType->handle,
                'fields' => array(),
            );

            $fieldCount = 1;
            foreach ($blockType->fields as $blockField) {
                // Case for nested Super Table
                if ($blockField->type == 'SuperTable') {
                    $settings = $this->_prepareExportSuperTableField($blockField);
                } else if ($blockField->type == 'PositionSelect') {
                    $settings = $this->_prepareExportPositionSelectField($blockField);
                } else {
                    $settings = $blockField->settings;
                }

                $fieldSettings['blockTypes']['new' . $blockCount]['fields']['new' . $fieldCount] = array(
                    'name' => $blockField->name,
                    'handle' => $blockField->handle,
                    'required' => $blockField->required,
                    'instructions' => $blockField->instructions,
                    'translatable' => $blockField->translatable,
                    'type' => $blockField->type,
                    'typesettings' => $settings,
                );

                $fieldCount++;
            }

            $blockCount++;
        }

        return $fieldSettings;
    }

    public function _prepareExportSuperTableField($field)
    {
        $fieldSettings = $field->settings;

        $blockTypes = craft()->superTable->getBlockTypesByFieldId($field->id);

        $blockCount = 1;
        foreach ($blockTypes as $blockType) {
            $fieldSettings['blockTypes']['new' . $blockCount] = array(
                'fields' => array(),
            );

            $fieldCount = 1;
            foreach ($blockType->fields as $blockField) {
                // Case for nested Matrix
                if ($blockField->type == 'Matrix') {
                    $settings = $this->_prepareExportMatrixField($blockField);
                } else if ($blockField->type == 'PositionSelect') {
                    $settings = $this->_prepareExportPositionSelectField($blockField);
                } else {
                    $settings = $blockField->settings;
                }

                $fieldSettings['blockTypes']['new' . $blockCount]['fields']['new' . $fieldCount] = array(
                    'name' => $blockField->name,
                    'handle' => $blockField->handle,
                    'required' => $blockField->required,
                    'instructions' => $blockField->instructions,
                    'translatable' => $blockField->translatable,
                    'type' => $blockField->type,
                    'typesettings' => $settings,
                );

                $fieldCount++;
            }

            $blockCount++;
        }

        return $fieldSettings;
    }

    public function _prepareExportPositionSelectField($field)
    {
        $fieldSettings = $field->settings;
        $options = array();
        
        foreach ($fieldSettings['options'] as $value) {
            $options[$value] = true;
        }

        $fieldSettings['options'] = $options;

        return $fieldSettings;
    }
}
