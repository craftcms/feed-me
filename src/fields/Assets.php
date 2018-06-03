<?php
namespace verbb\feedme\fields;

use verbb\feedme\FeedMe;
use verbb\feedme\base\Field;
use verbb\feedme\base\FieldInterface;
use verbb\feedme\helpers\AssetHelper;
use verbb\feedme\helpers\DataHelper;

use Craft;
use craft\elements\Asset as AssetElement;
use craft\helpers\Db;
use craft\helpers\UrlHelper;

use Cake\Utility\Hash;

class Assets extends Field implements FieldInterface
{
    // Properties
    // =========================================================================

    public static $name = 'Assets';
    public static $class = 'craft\fields\Assets';
    private $_uploadData = [];


    // Templates
    // =========================================================================

    public function getMappingTemplate()
    {
        return 'feed-me/_includes/fields/assets';
    }


    // Public Methods
    // =========================================================================

    public function parseField()
    {
        $value = $this->fetchArrayValue();

        $settings = Hash::get($this->field, 'settings');
        $folders = Hash::get($this->field, 'settings.sources');
        $limit = Hash::get($this->field, 'settings.limit');
        $upload = Hash::get($this->fieldInfo, 'options.upload');
        $conflict = Hash::get($this->fieldInfo, 'options.conflict');
        $fields = Hash::get($this->fieldInfo, 'fields');

        // Get folder id's for connecting
        $folderIds = [];

        if (is_array($folders)) {
            foreach ($folders as $folder) {
                list(, $id) = explode(':', $folder);
                $folderIds[] = $id;

                // Get all sub-folders for this root folder
                $folderModel = Craft::$app->getAssets()->getFolderById($id);

                if ($folderModel) {
                    $subFolders = Craft::$app->getAssets()->getAllDescendantFolders($folderModel);

                    if (is_array($subFolders)) {
                        foreach ($subFolders as $subFolder) {
                            $folderIds[] = $subFolder->id;
                        }
                    }
                }
            }
        }

        $foundElements = [];
        $urlsToUpload = [];

        foreach ($value as $key => $dataValue) {
            // Prevent empty or blank values (string or array), which match all elements
            if (empty($dataValue)) {
                continue;
            }
            
            $query = AssetElement::find();

            // If we're uploading files, this will need to be an absolute URL. If it is, save until later.
            // We also don't check for existing assets here, so break out instantly.
            if ($upload && UrlHelper::isAbsoluteUrl($dataValue)) {
                $urlsToUpload[$key] = $dataValue;

                // If we're opting to use the already uploaded asset, we can check here
                if ($conflict === AssetElement::SCENARIO_INDEX) {
                    $dataValue = AssetHelper::getRemoteUrlFilename($dataValue);
                }
            }

            $criteria['status'] = null;
            $criteria['folderId'] = $folderIds;
            $criteria['kind'] = $settings['allowedKinds'];
            $criteria['limit'] = $limit;
            $criteria['filename'] = Db::escapeParam($dataValue);

            Craft::configure($query, $criteria);

            $foundElements = array_merge($foundElements, $query->ids());

            // Are we uploading, and did we find existing assets? No need to process
            if ($upload && $foundElements && $conflict === AssetElement::SCENARIO_INDEX) {
                unset($urlsToUpload[$key]);
            }
        }

        if ($upload && $urlsToUpload) {
            $uploadedElements = AssetHelper::fetchRemoteImage($urlsToUpload, $this->fieldInfo, $this->field, $this->element);
            $foundElements = array_merge($foundElements, $uploadedElements);
        }

        // Check for field limit - only return the specified amount
        if ($foundElements && $limit) {
            $foundElements = array_chunk($foundElements, $limit)[0];
        }

        // Check for any sub-fields for the lement
        if ($fields) {
            $this->populateElementFields($foundElements);
        }

        return $foundElements;
    }

}