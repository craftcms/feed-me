<?php
namespace Craft;

use Cake\Utility\Hash as Hash;

class AssetsFeedMeFieldType extends BaseFeedMeFieldType
{
    // Templates
    // =========================================================================

    public function getMappingTemplate()
    {
        return 'feedme/_includes/fields/assets';
    }
    


    // Public Methods
    // =========================================================================

    public function prepFieldData($element, $field, $fieldData, $handle, $options)
    {
        $preppedData = array();

        $data = Hash::get($fieldData, 'data');

        if (empty($data)) {
            return;
        }

        if (!is_array($data)) {
            $data = array($data);
        }

        $settings = $field->getFieldType()->getSettings();

        // Get folder id's for connecting
        $folderIds = array();
        $folders = $settings->getAttribute('sources');
        if (is_array($folders)) {
            foreach ($folders as $folder) {
                list(, $id) = explode(':', $folder);
                $folderIds[] = $id;

                // Get all sub-folders for this root folder
                $folderModel = craft()->assets->getFolderById($id);
                $subFolders = craft()->assets->getAllDescendantFolders($folderModel);

                if (is_array($subFolders)) {
                    foreach ($subFolders as $subFolder) {
                        $folderIds[] = $subFolder->id;
                    }
                }
            }
        }

        // Find existing asset
        foreach ($data as $asset) {
            // Check config settings if we need to clean url
            if (craft()->config->get('cleanAssetUrls', 'feedMe')) {
                $asset = UrlHelper::stripQueryString($asset);
            }

            $criteria = craft()->elements->getCriteria(ElementType::Asset);
            $criteria->folderId = $folderIds;
            $criteria->limit = $settings->limit;
            $criteria->filename = $asset;

            $preppedData = array_merge($preppedData, $criteria->ids());
        }

        // Check to see if we should be uploading these assets
        if (isset($fieldData['options']['upload'])) {
            // Get the folder we should upload into from the field
            $folderId = $field->getFieldType()->resolveSourcePath();

            $ids = $this->fetchRemoteImage($data, $folderId, $fieldData['options']);

            $preppedData = array_merge($preppedData, $ids);
        }

        // Check for field limit - only return the specified amount
        if ($preppedData) {
            if ($field->settings['limit']) {
                $preppedData = array_chunk($preppedData, $field->settings['limit']);
                $preppedData = $preppedData[0];
            }
        }

        // Check if we've got any data for the fields in this element
        if (isset($fieldData['fields'])) {
            $this->_populateElementFields($preppedData, $fieldData['fields']);
        }

        return $preppedData;
    }

    public function fetchRemoteImage($urls, $folderId, $options)
    {
        if (!is_array($urls)) {
            $urls = array($urls);
        }

        $fileIds = array();
        $tempPath = craft()->path->getTempPath();

        // Check if the temp path exists first
        if (!IOHelper::getRealPath($tempPath)) {
            IOHelper::createFolder($tempPath, craft()->config->get('defaultFolderPermissions'), true);

            if (!IOHelper::getRealPath($tempPath)) {
                throw new Exception(Craft::t('Temp folder “{tempPath}” does not exist and could not be created', array('tempPath' => $tempPath)));
            }
        }

        // Download each image
        foreach ($urls as $key => $url) {
            if (!isset($url) || $url === '') {
                continue;
            }

            // Check config settings if we need to clean url
            if (craft()->config->get('cleanAssetUrls', 'feedMe')) {
                $url = UrlHelper::stripQueryString($url);
            }

            $filename = basename($url);
            $saveLocation = $tempPath . $filename;

            // Check if this URL has has a file extension - grab it if not...
            $extension = IOHelper::getExtension($saveLocation);

            if (!$extension) {
                $image = getimagesize($url);
                $extension = FileHelper::getExtensionByMimeType($image['mime']);

                $saveLocation = $saveLocation . '.' . $extension;
                $filename = $filename . '.' . $extension;
            }

            // Download the file - ensuring we're not loading into memory for efficiency
            $defaultOptions = array(
                CURLOPT_FILE => fopen($saveLocation, 'w'),
                CURLOPT_FOLLOWLOCATION => true,
                CURLOPT_URL => $url,
                CURLOPT_FAILONERROR => true,
            );

            $configOptions = craft()->config->get('curlOptions', 'feedMe');

            if ($configOptions) {
                $opts = $configOptions + $defaultOptions;
            } else {
                $opts = $defaultOptions;
            }

            $ch = curl_init();
            curl_setopt_array($ch, $opts);
            $result = curl_exec($ch);

            if ($result === false) {
                //throw new Exception(curl_error($ch));
                FeedMePlugin::log('Asset error: ' . $url . ' - ' . curl_error($ch), LogLevel::Error, true);
                continue;
            }

            // We've successfully downloaded the image - now insert it into Craft
            $conflictResolution = $options['conflict'];

            // Look for an existing file
            $criteria = craft()->elements->getCriteria(ElementType::Asset);
            $criteria->folderId = $folderId;
            $criteria->limit = null;
            $criteria->filename = $filename;
            $targetFile = $criteria->find();

            // It seems that even when 'cancel' is set for a conflict resolution, a new element is created
            // that seems unnecessary, and could easily cause element bloat...
            if ($conflictResolution == AssetConflictResolution::Cancel && isset($targetFile[0])) {
                $fileIds[] = $targetFile[0]->id;
            } else {
                // Wrap in a try/catch to ensure any errors with saving an asset are logged, but don't break the import process
                try {
                    $response = craft()->assets->insertFileByLocalPath($saveLocation, $filename, $folderId, $conflictResolution);
            
                    // Delete temporary file
                    IOHelper::deleteFile($saveLocation, true);

                    if ($response->isSuccess()) {
                        $fileId = $response->getDataItem('fileId');

                        if ($fileId) {
                            $fileIds[] = $fileId;
                        }
                    } else {
                        FeedMePlugin::log('Asset error: ' . $url . ' - ' . $response->errorMessage, LogLevel::Error, true);
                        continue;
                    }
                } catch (Exception $e) {
                    FeedMePlugin::log('Asset error: ' . $url . ' - ' . $e->getMessage(), LogLevel::Error, true);
                }
            }
        }

        return $fileIds;
    }



    // Private Methods
    // =========================================================================

    private function _populateElementFields($assetData, $fieldData)
    {
        foreach ($assetData as $i => $assetId) {
            $asset = craft()->assets->getFileById($assetId);

            // Prep each inner field
            $preppedData = array();
            foreach ($fieldData as $fieldHandle => $fieldContent) {
                $data = craft()->feedMe_fields->prepForFieldType(null, $fieldContent, $fieldHandle, null);

                if (is_array($data)) {
                    $data = Hash::get($data, $i);
                }

                $preppedData[$fieldHandle] = $data;
            }

            $asset->setContentFromPost($preppedData);

            if (!craft()->assets->storeFile($asset)) {
                FeedMePlugin::log('Asset error: ' . json_encode($asset->getErrors()), LogLevel::Error, true);
            } else {
                FeedMePlugin::log('Updated Asset (ID ' . $assetId . ') inner-element with content: ' . json_encode($preppedData), LogLevel::Info, true);
            }
        }
    }

}