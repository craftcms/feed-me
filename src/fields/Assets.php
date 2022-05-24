<?php

namespace craft\feedme\fields;

use Cake\Utility\Hash;
use Craft;
use craft\db\Query;
use craft\db\Table;
use craft\elements\Asset as AssetElement;
use craft\feedme\base\Field;
use craft\feedme\base\FieldInterface;
use craft\feedme\helpers\AssetHelper;
use craft\feedme\Plugin;
use craft\fields\Assets as AssetsField;
use craft\helpers\Db;
use craft\helpers\Json;
use craft\helpers\UrlHelper;

/**
 *
 * @property-read string $mappingTemplate
 */
class Assets extends Field implements FieldInterface
{
    // Properties
    // =========================================================================

    /**
     * @var string
     */
    public static string $name = 'Assets';

    /**
     * @var string
     */
    public static string $class = AssetsField::class;

    /**
     * @var string
     */
    public static string $elementType = AssetElement::class;


    // Templates
    // =========================================================================

    /**
     * @inheritDoc
     */
    public function getMappingTemplate(): string
    {
        return 'feed-me/_includes/fields/assets';
    }

    // Public Methods
    // =========================================================================

    /**
     * @inheritDoc
     */
    public function parseField(): mixed
    {
        $value = $this->fetchArrayValue();

        $settings = Hash::get($this->field, 'settings');
        $folders = Hash::get($this->field, 'settings.sources');
        $limit = Hash::get($this->field, 'settings.limit');
        $targetSiteId = Hash::get($this->field, 'settings.targetSiteId');
        $feedSiteId = Hash::get($this->feed, 'siteId');
        $upload = Hash::get($this->fieldInfo, 'options.upload');
        $conflict = Hash::get($this->fieldInfo, 'options.conflict');
        $fields = Hash::get($this->fieldInfo, 'fields');
        $node = Hash::get($this->fieldInfo, 'node');

        // Get folder id's for connecting
        $folderIds = $this->field->resolveDynamicPathToFolderId($this->element);

        if (!$folderIds) {
            if (is_array($folders)) {
                foreach ($folders as $folder) {
                    [, $uid] = explode(':', $folder);
                    $volumeId = Db::idByUid(Table::VOLUMES, $uid);

                    // Get all folders for this volume
                    $ids = (new Query())
                        ->select(['id'])
                        ->from([Table::VOLUMEFOLDERS])
                        ->where(['volumeId' => $volumeId])
                        ->column();

                    $folderIds = array_merge($folderIds, $ids);
                }
            } elseif ($folders === '*') {
                $folderIds = null;
            }
        }

        $foundElements = [];
        $urlsToUpload = [];
        $base64ToUpload = [];

        if (!$value) {
            return $foundElements;
        }

        foreach ($value as $key => $dataValue) {
            // Prevent empty or blank values (string or array), which match all elements
            if (empty($dataValue)) {
                continue;
            }

            // If we're using the default value - skip, we've already got an id array
            if ($node === 'usedefault') {
                $foundElements = $value;
                break;
            }

            $query = AssetElement::find();

            // In multi-site, there's currently no way to query across all sites - we use the current site
            // See https://github.com/craftcms/cms/issues/2854
            if (Craft::$app->getIsMultiSite()) {
                if ($targetSiteId) {
                    $criteria['siteId'] = Craft::$app->getSites()->getSiteByUid($targetSiteId)->id;
                } elseif ($feedSiteId) {
                    $criteria['siteId'] = $feedSiteId;
                } else {
                    $criteria['siteId'] = Craft::$app->getSites()->getCurrentSite()->id;
                }
            }

            // Check if the URL is actually a base64 encoded file.
            preg_match('/^data:\w+\/\w+;base64,/i', $dataValue, $matches);

            if ($upload && count($matches) > 0) {
                $base64ToUpload[$key] = $dataValue;
            } else {
                // If we're uploading files, this will need to be an absolute URL. If it is, save until later.
                // We also don't check for existing assets here, so break out instantly.
                if ($upload && UrlHelper::isAbsoluteUrl($dataValue)) {
                    $urlsToUpload[$key] = $dataValue;
                    $filename = AssetHelper::getRemoteUrlFilename($dataValue);
                } else {
                    $filename = basename($dataValue);
                }

                $criteria['status'] = null;
                $criteria['folderId'] = $folderIds;
                $criteria['kind'] = $settings['allowedKinds'];
                $criteria['limit'] = $limit;
                $criteria['filename'] = $filename;
                $criteria['includeSubfolders'] = true;

                Craft::configure($query, $criteria);

                Plugin::info('Search for existing asset with query `{i}`', ['i' => Json::encode($criteria)]);

                $ids = $query->ids();
                $foundElements = array_merge($foundElements, $ids);

                Plugin::info('Found `{i}` existing assets: `{j}`', ['i' => count($foundElements), 'j' => Json::encode($foundElements)]);

                // Are we uploading, and did we find existing assets? No need to process
                if ($upload && $ids && $conflict === AssetElement::SCENARIO_INDEX) {
                    unset($urlsToUpload[$key]);

                    Plugin::info('Skipping asset upload (already exists).');
                }
            }
        }

        if ($upload) {
            if ($urlsToUpload) {
                $uploadedElements = AssetHelper::fetchRemoteImage($urlsToUpload, $this->fieldInfo, $this->feed, $this->field, $this->element);
                $foundElements = array_merge($foundElements, $uploadedElements);
            }

            if ($base64ToUpload) {
                $uploadedElements = AssetHelper::createBase64Image($base64ToUpload, $this->fieldInfo, $this->feed, $this->field, $this->element);
                $foundElements = array_merge($foundElements, $uploadedElements);
            }
        }

        // Check for field limit - only return the specified amount
        if ($foundElements && $limit) {
            $foundElements = array_chunk($foundElements, $limit)[0];
        }

        // Check for any sub-fields for the element
        if ($fields) {
            $this->populateElementFields($foundElements);
        }

        $foundElements = array_unique($foundElements);

        // Protect against sending an empty array - removing any existing elements
        if (!$foundElements) {
            return null;
        }

        return $foundElements;
    }
}
