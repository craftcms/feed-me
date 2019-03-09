<?php
namespace verbb\feedme\elements;

use verbb\feedme\base\Element;
use verbb\feedme\base\ElementInterface;
use verbb\feedme\events\FeedProcessEvent;
use verbb\feedme\helpers\AssetHelper;
use verbb\feedme\helpers\DuplicateHelper;
use verbb\feedme\services\Process;

use Craft;
use craft\db\Query;
use craft\elements\Asset as AssetElement;
use craft\helpers\Assets as AssetsHelper;
use craft\helpers\UrlHelper;

use yii\base\Event;
use Cake\Utility\Hash;

class Asset extends Element implements ElementInterface
{
    // Properties
    // =========================================================================

    public static $name = 'Asset';
    public static $class = 'craft\elements\Asset';

    public $element;


    // Templates
    // =========================================================================

    public function getGroupsTemplate()
    {
        return 'feed-me/_includes/elements/asset/groups';
    }

    public function getColumnTemplate()
    {
        return 'feed-me/_includes/elements/asset/column';
    }

    public function getMappingTemplate()
    {
        return 'feed-me/_includes/elements/asset/map';
    }


    // Public Methods
    // =========================================================================

    public function init()
    {
        // If we are adding a new asset, it has to be done before the content is populated on the element.
        // We of course, want content to be populated on the newly-created element, not one that won't be uploaded
        Event::on(Process::class, Process::EVENT_STEP_BEFORE_PARSE_CONTENT, function(FeedProcessEvent $event) {
            $this->_handleImageCreation($event);
        });
    }

    public function getGroups()
    {
        return Craft::$app->volumes->getAllVolumes();
    }

    public function getQuery($settings, $params = [])
    {
        $query = AssetElement::find();

        $criteria = array_merge([
            'status' => null,
            'volumeId' => $settings['elementGroup'][AssetElement::class],
            'includeSubfolders' => true,
        ], $params);

        $siteId = Hash::get($settings, 'siteId');

        if ($siteId) {
            $criteria['siteId'] = $siteId;
        }

        Craft::configure($query, $criteria);

        return $query;
    }

    public function setModel($settings)
    {
        $this->element = new AssetElement();
        $this->element->volumeId = $settings['elementGroup'][AssetElement::class];

        $siteId = Hash::get($settings, 'siteId');

        if ($siteId) {
            $this->element->siteId = $siteId;
        }

        return $this->element;
    }

    public function beforeSave($element, $settings)
    {
        parent::beforeSave($element, $settings);

        // If we don't have an ID for this element, we don't want to continue. The reason is that we only want Feed Me to 
        // update an asset, not create a new one. Instead, asset-creation (upload from remote) is handled earlier in processing.
        // If this were to proceed, we'd get invalid assets created.
        if (!$element->id) {
            return false;
        }

        return true;
    }


    // Private Methods
    // =========================================================================

    private function _handleImageCreation($event)
    {
        $feed = $event->feed;
        $feedData = $event->feedData;

        // If we're not adding new assets, skip this altogether
        if (!DuplicateHelper::isAdd($feed)) {
            return;
        }

        $fieldInfo = $feed['fieldMapping']['urlOrPath'] ?? [];

        // Just in case...
        if (!$fieldInfo) {
            return;
        }

        $folderIdInfo = $feed['fieldMapping']['folderId'] ?? [];

        $value = $this->fetchSimpleValue($feedData, $fieldInfo);
        $folderId = $this->parseFolderId($feedData, $folderIdInfo);

        $conflict = Hash::get($fieldInfo, 'options.conflict');
        
        // Do we want to match existing element? If one exists, we need to set our element to be that
        if ($conflict === AssetElement::SCENARIO_INDEX) {
            // Make sure to parse the URL into a filename to find the asset by
            $filename = $this->_getFilename($value);

            $filename = AssetsHelper::prepareAssetName($filename);

            $foundElement = AssetElement::find()
                ->folderId($folderId)
                ->filename($filename)
                ->includeSubfolders(true)
                ->one();

            if ($foundElement) {
                $event->element = $foundElement;
                return;
            }
        }

        // We can't find an existing asset, we need to download from a remote URL, or local path
        $uploadedElementIds = AssetHelper::fetchRemoteImage([$value], $fieldInfo, $this->feed, null, $this->element, $folderId);

        if ($uploadedElementIds) {
            $foundElement = AssetElement::find()
                ->id($uploadedElementIds[0])
                ->one();

            if ($foundElement) {
                $event->element = $foundElement;
                return;
            }
        }
    }

    private function _getFilename($value)
    {
        // If this is an absolute URL, we're uploading the asset. Parse it to just get the filename
        if (UrlHelper::isAbsoluteUrl($value)) {
            return AssetHelper::getRemoteUrlFilename($value);
        }

        // Otherwise, probably a local path
        return basename($value);
    }


    // Protected Methods
    // =========================================================================

    protected function parseFilename($feedData, $fieldInfo)
    {
        $value = $this->fetchSimpleValue($feedData, $fieldInfo);

        return $this->_getFilename($value);
    }

    protected function parseFolderId($feedData, $fieldInfo)
    {
        $value = $this->fetchSimpleValue($feedData, $fieldInfo);

        if (is_numeric($value)) {
            return $value;
        }

        $result = (new Query())
            ->select(['id', 'name'])
            ->from(['{{%volumefolders}}'])
            ->where(['name' => $value])
            ->one();

        if ($result) {
            return $result->id;
        }

        // If we've provided a bad folder, just return the root - we always need a folderId
        return Craft::$app->assets->getRootFolderByVolumeId($this->element->volumeId)->id;
    }

}