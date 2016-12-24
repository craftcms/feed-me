<?php
namespace Craft;

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

    public function prepFieldData($element, $field, $data, $handle, $options)
    {
        $fieldData = array();

        if (empty($data)) {
            return;
        }

        $settings = $field->getFieldType()->getSettings();

        // Get folder id's for connecting
        $folderIds = array();
        $folders = $settings->getAttribute('sources');
        if (is_array($folders)) {
            foreach ($folders as $folder) {
                list(, $id) = explode(':', $folder);
                $folderIds[] = $id;
            }
        }

        // Find existing asset
        $assets = ArrayHelper::stringToArray($data);

        foreach ($assets as $asset) {
            if ($asset == '__') {
                continue;
            }
            
            // Check config settings if we need to clean url
            if (craft()->config->get('cleanAssetUrls', 'feedMe')) {
                $asset = UrlHelper::stripQueryString($asset);
            }

            $criteria = craft()->elements->getCriteria(ElementType::Asset);
            $criteria->folderId = $folderIds;
            $criteria->limit = $settings->limit;
            $criteria->filename = $asset;

            $fieldData = array_merge($fieldData, $criteria->ids());
        }

        // Check to see if we should be uploading these assets
        if (isset($options['options']['upload'])) {
            $ids = $this->_fetchRemoteImage($assets, $field, $options);

            $fieldData = array_merge($fieldData, $ids);
        }

        // Check for field limit - only return the specified amount
        if ($fieldData) {
            if ($field->settings['limit']) {
                $fieldData = array_chunk($fieldData, $field->settings['limit']);
                $fieldData = $fieldData[0];
            }
        }

        // Check if we've got any data for the fields in this element
        if (isset($options['fields'])) {
            $this->_populateElementFields($fieldData, $options['fields']);
        }

        return $fieldData;
    }



    // Private Methods
    // =========================================================================

    private function _populateElementFields($fieldData, $elementData)
    {
        foreach ($fieldData as $key => $id) {
            $asset = craft()->assets->getFileById($id);

            // Prep each inner field
            $preppedElementData = array();
            foreach ($elementData as $elementHandle => $elementContent) {
                if ($elementContent != '__') {
                    $preppedElementData[$elementHandle] = craft()->feedMe_fields->prepForFieldType(null, $elementContent, $elementHandle, null);
                }
            }

            $asset->setContentFromPost($preppedElementData);

            if (!craft()->assets->storeFile($asset)) {
                throw new Exception(json_encode($asset->getErrors()));
            }
        }
    }

    private function _fetchRemoteImage($urls, $field, $options)
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
            if ($url == '__') {
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

            // Get the folder we should upload into from the field
            $folderId = $field->getFieldType()->resolveSourcePath();

            // We've successfully downloaded the image - now insert it into Craft
            $conflictResolution = $options['options']['conflict'];

            // Look for an existing file
            $criteria = craft()->elements->getCriteria(ElementType::Asset);
            $criteria->sourceId = null;
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

}