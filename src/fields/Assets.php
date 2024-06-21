<?php

namespace craft\feedme\fields;

use Cake\Utility\Hash;
use Craft;
use craft\db\Query;
use craft\db\Table;
use craft\elements\Asset as AssetElement;
use craft\feedme\base\Field;
use craft\feedme\base\FieldInterface;
use craft\feedme\events\AssetFilenameEvent;
use craft\feedme\helpers\AssetHelper;
use craft\feedme\helpers\DataHelper;
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
    public const EVENT_ASSET_FILENAME = 'onAssetFilename';

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
        $default = $this->fetchDefaultArrayValue();

        // if the mapped value is not set in the feed
        if ($value === null) {
            return null;
        }

        // if value from the feed is empty and default is not set
        // return an empty array; no point bothering further
        if (empty($default) && DataHelper::isArrayValueEmpty($value)) {
            return [];
        }

        $settings = Hash::get($this->field, 'settings');
        $folders = Hash::get($this->field, 'settings.sources');
        $limit = Hash::get($this->field, 'settings.maxRelations');
        $targetSiteId = Hash::get($this->field, 'settings.targetSiteId');
        $feedSiteId = Hash::get($this->feed, 'siteId');
        $match = Hash::get($this->fieldInfo, 'options.match', 'filename');
        $upload = Hash::get($this->fieldInfo, 'options.upload');
        $conflict = Hash::get($this->fieldInfo, 'options.conflict');
        $fields = Hash::get($this->fieldInfo, 'fields');
        $nativeFields = Hash::get($this->fieldInfo, 'nativeFields');
        $node = Hash::get($this->fieldInfo, 'node');
        $nodeKey = null;

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

        $filenamesFromFeed = $upload ? DataHelper::fetchArrayValue($this->feedData, $this->fieldInfo, 'options.filenameNode') : null;

        // Fire an 'onAssetFilename' event
        $event = new AssetFilenameEvent([
            'field' => $this->field,
            'element' => $this->element,
            'fieldValue' => $value,
            'filenames' => $filenamesFromFeed,
        ]);

        $this->trigger(self::EVENT_ASSET_FILENAME, $event);

        // Allow event to overwrite filenames to be used
        $filenamesFromFeed = $event->filenames;

        foreach ($value as $key => $dataValue) {
            // Prevent empty or blank values (string or array), which match all elements
            if (empty($dataValue) && empty($default)) {
                continue;
            }

            // If we're using the default value - skip, we've already got an id array
            if ($node === 'usedefault') {
                $foundElements = $value;
                break;
            }

            // special provision for falling back on default BaseRelationField value
            // https://github.com/craftcms/feed-me/issues/1195
            if (DataHelper::isArrayValueEmpty($value)) {
                $foundElements = $default;
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
                    $urlsToUpload[$key]['value'] = $dataValue;

                    if (isset($filenamesFromFeed[$key])) {
                        $filename = $filenamesFromFeed[$key] . '.' . AssetHelper::getRemoteUrlExtension($urlsToUpload[$key]['value']);
                        $urlsToUpload[$key]['newFilename'] = $filename;
                    } else {
                        $filename = AssetHelper::getRemoteUrlFilename($dataValue);
                        $urlsToUpload[$key]['newFilename'] = null;
                    }
                } else {
                    $filename = basename($dataValue);
                }

                $criteria['status'] = null;
                $criteria['folderId'] = $folderIds;
                $criteria['kind'] = $settings['allowedKinds'];
                $criteria['limit'] = $limit;

                if ($match === 'id') {
                    $criteria['id'] = $dataValue;
                } else {
                    $criteria['filename'] = $filename;
                }

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

            $nodeKey = $this->getArrayKeyFromNode($node);
        }

        if ($upload) {
            if ($urlsToUpload) {
                foreach ($urlsToUpload as $item) {
                    $uploadedElements = AssetHelper::fetchRemoteImage(
                        [$item['value']],
                        $this->fieldInfo,
                        $this->feed,
                        $this->field,
                        $this->element,
                        null,
                        $item['newFilename']
                    );
                    $foundElements = array_merge($foundElements, $uploadedElements);
                }
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
            $this->populateElementFields($foundElements, $nodeKey);
        }

        // this is used by the sub-fields of the assets field; not when importing into Asset element directly;
        // when importing into Asset element directly, src/fieldlayoutelements/assets/Alt.php is used
        if ($nativeFields) {
            $this->populateNativeFields($foundElements, $nodeKey);
        }

        $foundElements = array_unique($foundElements);

        // Protect against sending an empty array - removing any existing elements
        if (!$foundElements) {
            return null;
        }

        return $foundElements;
    }
}
