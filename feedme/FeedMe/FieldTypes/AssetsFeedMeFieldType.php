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
            $ids = $this->_fetchRemoteImage($data, $field, $options);

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
            $response = craft()->assets->insertFileByLocalPath($saveLocation, $filename, $folderId, $conflictResolution);

            if ($response->isSuccess()) {
                $fileId = $response->getDataItem('fileId');

                if ($fileId) {
                    $fileIds[] = $fileId;
                } else {
                    // Here, we've succeeded in downloading and inserting this new asset into Craft - but no ID was returned.
                    // Usually this is when its preferred a new file not replace an existing one. We want to return the existing one.
                    $criteria = craft()->elements->getCriteria(ElementType::Asset);
                    $criteria->sourceId = null;
                    $criteria->limit = null;
                    $criteria->filename = $filename;
                    $file = $criteria->first();

                    if ($file) {
                        $fileIds[] = $file->id;
                    }
                }
            } else {
                //throw new Exception($response->errorMessage);
                FeedMePlugin::log('Asset error: ' . $url . ' - ' . $response->errorMessage, LogLevel::Error, true);
                continue;
            }
        }

        return $fileIds;
    }

    
}