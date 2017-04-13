<?php
namespace Craft;

use Cake\Utility\Hash as Hash;

class AssetsFeedMeFieldType extends BaseFeedMeFieldType
{
    // Properties
    // =========================================================================

    private $_uploadData = array();


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

                if ($folderModel) {
                    $subFolders = craft()->assets->getAllDescendantFolders($folderModel);

                    if (is_array($subFolders)) {
                        foreach ($subFolders as $subFolder) {
                            $folderIds[] = $subFolder->id;
                        }
                    }
                }
            }
        }

        // Find existing asset
        foreach ($data as $asset) {
            // Clean the URL
            $asset = UrlHelper::stripQueryString($asset);

            // Cleanup filenames to match Craft Assets
            //$asset = AssetsHelper::cleanAssetName($asset);
            $asset = str_replace(',', '\,', $asset);

            $criteria = craft()->elements->getCriteria(ElementType::Asset);
            $criteria->status = null;
            $criteria->folderId = $folderIds;
            $criteria->limit = $settings->limit;
            $criteria->filename = $asset;

            $preppedData = array_merge($preppedData, $criteria->ids());
        }

        // Check to see if we should be uploading these assets - but save for later, to be
        // processed in `postFieldData`, once our owner element have been populated
        if (isset($fieldData['options']['upload'])) {
            $this->_uploadData[$field->handle] = $fieldData;
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


    // Check if we want to upload our asset. This is done after the above 'regular' parsing so we
    // have a chance to setup our owner element with attributes - particularly useful for folder locations
    // that use the element owners attributes, which are now set.
    public function postFieldData($element, $field, &$fieldData, $handle)
    {
        // Check for our saved data from the main parsing function above. We've already destroyed the
        // initial feed data with 'real' information, but in this case, we still need it!
        $uploadData = $this->_uploadData[$field->handle];

        $data = Hash::get($uploadData, 'data');

        if (empty($data)) {
            return;
        }

        if (!is_array($data)) {
            $data = array($data);
        }

        if (isset($uploadData['options']['upload'])) {
            // Get the folder we should upload into from the field
            $folderId = $field->getFieldType()->resolveSourcePath();

            $ids = $this->fetchRemoteImage($data, $folderId, $uploadData['options']);

            $fieldData[$handle] = $ids;
        }

        // Check for field limit - only return the specified amount
        if ($fieldData[$handle]) {
            if ($field->settings['limit']) {
                $assetChunks = array_chunk($fieldData[$handle], $field->settings['limit']);
                $fieldData[$handle] = $assetChunks[0];
            }
        }
    }


    public function fetchRemoteImage($urls, $folderId, $options)
    {
        $conflictResolution = $options['conflict'];

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

            // Clean the URL for filename when saving as an Asset
            $cleanUrl = UrlHelper::stripQueryString($url);

            // Check if this URL has a file extension - sometimes its a dynamic URL without
            // a filename and extension, so we generate one.
            $extension = IOHelper::getExtension($cleanUrl);

            if (!$extension) {
                // Generate a hash of the URL - this constitutes our ID
                $filename = AssetsHelper::cleanAssetName(md5($url));

                // Detect the mime type from url content
                $finfo = new \finfo(FILEINFO_MIME_TYPE);
                $type = $finfo->buffer(file_get_contents($url));
                $extension = FileHelper::getExtensionByMimeType($type);

                if (!ImageHelper::isImageManipulatable($extension)) {
                    $extension = 'jpg';
                }

                $filename = $filename . $extension;
            } else {
                $filename = basename($cleanUrl);
            }

            $saveLocation = $tempPath . $filename;

            // Check to see if there are any matching existing assets
            $criteria = craft()->elements->getCriteria(ElementType::Asset);
            $criteria->status = null;
            $criteria->folderId = $folderId;
            $criteria->filename = $filename;
            $existing = $criteria->find();

            // If there's an existing file, but we want to keep the existing one, exit before trying to download - much faster!
            if ($conflictResolution == AssetConflictResolution::Cancel && isset($existing[0])) {
                $fileIds[] = $existing[0]->id;
                continue;
            }

            // Cleanup filenames for Curl specifically
            $curlUrl = str_replace(' ', '%20', $url);

            // Download the file - ensuring we're not loading into memory for efficiency
            $defaultOptions = array(
                CURLOPT_FILE => fopen($saveLocation, 'w'),
                CURLOPT_FOLLOWLOCATION => true,
                CURLOPT_URL => $curlUrl,
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
                FeedMePlugin::log('Asset error: ' . $url . ' - ' . curl_error($ch), LogLevel::Error, true);
                continue;
            }

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