<?php

namespace verbb\feedme\controllers;

use Craft;
use craft\helpers\App;
use craft\helpers\FileHelper;
use craft\helpers\StringHelper;
use craft\web\Controller;
use craft\web\UploadedFile;
use verbb\feedme\Plugin;
use verbb\feedme\models\GetHelp;
use verbb\feedme\records\FeedRecord;
use yii\base\ErrorException;
use yii\base\Exception;
use ZipArchive;

class HelpController extends Controller
{
    // Public Methods
    // =========================================================================

    public function actionSendSupportRequest()
    {
        $this->requirePostRequest();

        App::maxPowerCaptain();

        $request = Craft::$app->getRequest();
        $plugins = Craft::$app->getPlugins();

        $widgetId = 'feedMeHelp';
        $namespace = $request->getBodyParam('namespace');
        $namespace = $namespace ? $namespace . '.' : '';

        $getHelpModel = new GetHelp();
        $getHelpModel->fromEmail = $request->getBodyParam($namespace . 'fromEmail');
        $getHelpModel->feedIssue = $request->getBodyParam($namespace . 'feedIssue');
        $getHelpModel->message = trim($request->getBodyParam($namespace . 'message'));
        $getHelpModel->attachLogs = (bool)$request->getBodyParam($namespace . 'attachLogs');
        $getHelpModel->attachSettings = (bool)$request->getBodyParam($namespace . 'attachSettings');
        $getHelpModel->attachFeed = (bool)$request->getBodyParam($namespace . 'attachFeed');
        $getHelpModel->attachFields = (bool)$request->getBodyParam($namespace . 'attachFields');
        $getHelpModel->attachment = UploadedFile::getInstanceByName($namespace . 'attachAdditionalFile');

        $success = false;
        $errors = [];
        $zipFile = null;
        $tempFolder = Craft::$app->getPath()->getTempPath();
        $backupPath = Craft::$app->getPath()->getDbBackupPath();

        if (!$getHelpModel->validate()) {
            return $this->renderTemplate('feed-me/help/response', [
                'widgetId' => $widgetId,
                'success' => false,
                'errors' => $getHelpModel->getErrors(),
            ]);
        }

        $user = Craft::$app->getUser()->getIdentity();
        $feed = Plugin::$plugin->feeds->getFeedById($getHelpModel->feedIssue);

        // Add some extra info about this install
        $message = $getHelpModel->message . "\n\n" .
            "------------------------------\n\n" .
            'Craft ' . Craft::$app->getEditionName() . ' ' . Craft::$app->getVersion() . "\n\n";

        $message .= 'Feed Me: ' . Plugin::$plugin->getVersion() . "\n";
        $message .= 'License: ' . $plugins->getPluginLicenseKey('feed-me') . ' - ' . $plugins->getPluginLicenseKeyStatus('feed-me') . "\n\n";

        // if (Craft::$app->plugins->getPlugin('feed-me-pro')) {
        //     $message .= 'Feed Me Pro: ' . FeedMePro::$plugin->getVersion() . "\n";
        //     $message .= 'License: ' . $plugins->getPluginLicenseKey('feed-me-pro') . ' - ' . $plugins->getPluginLicenseKeyStatus('feed-me-pro') . "\n\n";
        // }

        $message .= 'Domain: ' . Craft::$app->getRequest()->getHostInfo();

        $requestParamDefaults = [
            'firstName' => $user->getFriendlyName(),
            'lastName' => $user->lastName ?: 'Doe',
            'email' => $getHelpModel->fromEmail,
            'note' => $message,
        ];

        $requestParams = $requestParamDefaults;

        // Create the SupportAttachment zip
        $zipPath = $tempFolder . '/' . StringHelper::UUID() . '.zip';

        try {
            $tempFileSettings = null;
            $tempFileFeed = null;
            $tempFileFields = null;

            // Create the zip
            $zip = new ZipArchive();

            if ($zip->open($zipPath, ZipArchive::CREATE) !== true) {
                throw new Exception('Cannot create zip at ' . $zipPath . '.');
            }

            // Composer files
            try {
                $composerService = Craft::$app->getComposer();
                $zip->addFile($composerService->getJsonPath(), 'composer.json');

                if (($composerLockPath = $composerService->getLockPath()) !== null) {
                    $zip->addFile($composerLockPath, 'composer.lock');
                }
            } catch (Exception $e) {
                // that's fine
            }

            //
            // Attached just the Feed Me log
            //

            if ($getHelpModel->attachLogs) {
                $logPath = Craft::$app->getPath()->getLogPath();

                if (is_dir($logPath)) {
                    try {
                        $logFiles = FileHelper::findFiles($logPath, [
                            'only' => ['feedme.log'],
                            'recursive' => false
                        ]);
                    } catch (ErrorException $e) {
                        $logFiles = [];
                    }

                    foreach ($logFiles as $logFile) {
                        $zip->addFile($logFile, 'logs/' . pathinfo($logFile, PATHINFO_BASENAME));
                    }
                }
            }

            //
            // Backup our feed settings
            //

            if ($getHelpModel->attachSettings) {
                try {
                    $feedInfo = $this->_prepareSqlFeedSettings($getHelpModel->feedIssue);
                    $tempFileSettings = $backupPath . '/' . StringHelper::toLowerCase('feedme_' . gmdate('ymd_His') . '.sql');

                    FileHelper::writeToFile($tempFileSettings, $feedInfo . PHP_EOL);

                    $zip->addFile($tempFileSettings, 'backups/' . pathinfo($tempFileSettings, PATHINFO_BASENAME));
                } catch (\Throwable $e) {
                    $noteError = "\n\nError adding database to help request: `" . $e->getMessage() . ":" . $e->getLine() . "`.";
                    $requestParamDefaults['note'] .= $noteError;
                    $requestParams['note'] .= $noteError;

                    Plugin::error($noteError);
                }
            }

            //
            // Save the contents of the feed
            //

            if ($getHelpModel->attachFeed) {
                try {
                    $feedData = Plugin::$plugin->data->getRawData($feed->feedUrl, $feed->id);
                    $tempFileFeed = $tempFolder . '/feed.' . StringHelper::toLowerCase($feed->feedType);

                    FileHelper::writeToFile($tempFileFeed, print_r($feedData, true) . PHP_EOL);

                    $zip->addFile($tempFileFeed, 'feed/' . pathinfo($tempFileFeed, PATHINFO_BASENAME));
                } catch (\Throwable $e) {
                    $noteError = "\n\nError adding feed to help request: `" . $e->getMessage() . ":" . $e->getLine() . "`.";
                    $requestParamDefaults['note'] .= $noteError;
                    $requestParams['note'] .= $noteError;

                    Plugin::error($noteError);
                }
            }

            //
            // Get some information about the fields we're mapping to - handy to know
            //

            if ($getHelpModel->attachFields) {
                try {
                    $fieldInfo = [];

                    if (is_array($feed->fieldMapping)) {
                        foreach ($feed->fieldMapping as $fieldHandle => $feedHandle) {
                            if (isset($feedHandle['field'])) {
                                $field = Craft::$app->fields->getFieldByHandle($fieldHandle);

                                if ($field) {
                                    $attributes = $field->attributes;
                                    $attributes['type'] = get_class($field);

                                    if ($attributes['type'] == 'craft\fields\Matrix') {
                                        foreach ($field->blocktypes as $key => $blocktype) {
                                            $attributes['blocktypes'][$key] = $blocktype->attributes;

                                            foreach ($blocktype->fields as $key2 => $blocktypeField) {
                                                $attributes['blocktypes'][$key]['fields'][$key2] = $blocktypeField->attributes;
                                                $attributes['blocktypes'][$key]['fields'][$key2]['type'] = get_class($blocktypeField);
                                            }
                                        }
                                    }

                                    $fieldInfo[$fieldHandle] = $attributes;
                                } else {
                                    $fieldInfo[$fieldHandle] = $field;
                                }
                            }
                        }

                        $json = json_encode($fieldInfo, JSON_PRETTY_PRINT);

                        $tempFileFields = $tempFolder . '/fields.json';

                        FileHelper::writeToFile($tempFileFields, $json . PHP_EOL);

                        $zip->addFile($tempFileFields, 'fields/' . pathinfo($tempFileFields, PATHINFO_BASENAME));
                    }
                } catch (\Throwable $e) {
                    $noteError = "\n\nError adding field into to help request: `" . $e->getMessage() . ":" . $e->getLine() . "`.";
                    $requestParamDefaults['note'] .= $noteError;
                    $requestParams['note'] .= $noteError;

                    Plugin::error($noteError);
                }
            }

            //
            // Uploaded attachment
            //

            if ($getHelpModel->attachment) {
                $zip->addFile($getHelpModel->attachment->tempName, $getHelpModel->attachment->name);
            }

            // Close and attach the zip
            $zip->close();
            $requestParams['filename'] = 'FeedMeSupportAttachment-' . StringHelper::UUID() . '.zip';
            $requestParams['fileMimeType'] = 'application/zip';
            $requestParams['fileBody'] = base64_encode(file_get_contents($zipPath));

            // Remove the temp files we've created
            if (is_file($tempFileSettings)) {
                FileHelper::unlink($tempFileSettings);
            }

            if (is_file($tempFileFeed)) {
                FileHelper::unlink($tempFileFeed);
            }

            if (is_file($tempFileFields)) {
                FileHelper::unlink($tempFileFields);
            }
        } catch (\Throwable $e) {
            Plugin::info('Tried to attach debug logs to a support request and something went horribly wrong: `' . $e->getMessage() . ':' . $e->getLine() . '`.');

            // There was a problem zipping, so reset the params and just send the email without the attachment.
            $requestParams = $requestParamDefaults;
            $requestParams['note'] .= "\n\nError attaching zip: `" . $e->getMessage() . ":" . $e->getLine() . "`.";
        }

        $guzzleClient = Craft::createGuzzleClient(['timeout' => 120, 'connect_timeout' => 120]);

        try {
            $guzzleClient->post('https://support.verbb.io/api/get-help', ['json' => $requestParams]);
        } catch (\Throwable $e) {
            Plugin::error('`' . (string)$e->getresponse()->getBody() . '`');

            return $this->renderTemplate('feed-me/help/response', [
                'widgetId' => $widgetId,
                'success' => false,
                'errors' => [
                    'Support' => [$e->getMessage()],
                ]
            ]);
        }

        // Delete the zip file
        if (is_file($zipPath)) {
            FileHelper::unlink($zipPath);
        }

        return $this->renderTemplate('feed-me/help/response', [
            'widgetId' => $widgetId,
            'success' => true,
            'errors' => []
        ]);
    }


    // Private Methods
    // =========================================================================

    private function _prepareSqlFeedSettings($id)
    {
        $tableName = Craft::$app->db->getSchema()->getRawTableName(FeedRecord::tableName());

        $row = FeedRecord::find()
            ->select(['*'])
            ->where(['id' => $id])
            ->one()
            ->toArray();

        // Remove the id col
        unset($row['id']);

        foreach ($row as $columnName => $value) {
            if ($value === null) {
                $row[$columnName] = 'NULL';
            } else {
                $row[$columnName] = Craft::$app->db->quoteValue($value);
            }
        }

        $attrs = array_map([Craft::$app->db, 'quoteColumnName'], array_keys($row));
        $insertStatement = 'INSERT INTO ' . $tableName . ' (' . implode(', ', $attrs) . ') VALUES ' . PHP_EOL;
        $insertStatement .= '(' . implode(', ', $row) . ');';

        return $insertStatement;
    }
}
