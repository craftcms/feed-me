<?php
namespace verbb\feedme\elements;

use verbb\feedme\base\Element;
use verbb\feedme\base\ElementInterface;
use verbb\feedme\helpers\AssetHelper;

use Craft;
use craft\db\Query;
use craft\elements\Asset as AssetElement;
use craft\helpers\UrlHelper;

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


    // Protected Methods
    // =========================================================================

    protected function parseFilename($feedData, $fieldInfo)
    {
        $value = $this->fetchSimpleValue($feedData, $fieldInfo);

        $upload = Hash::get($fieldInfo, 'options.upload');
        $conflict = Hash::get($fieldInfo, 'options.conflict');

        // Try to find an existing element
        $query = AssetElement::find();

        $urlToUpload = null;

        // If we're uploading files, this will need to be an absolute URL. If it is, save until later.
        // We also don't check for existing assets here, so break out instantly.
        if ($upload && UrlHelper::isAbsoluteUrl($value)) {
            $urlToUpload = $value;

            // If we're opting to use the already uploaded asset, we can check here
            if ($conflict === AssetElement::SCENARIO_INDEX) {
                $value = AssetHelper::getRemoteUrlFilename($value);
            }
        }

        $folderId = $this->parseFolderId($feedData, $fieldInfo);

        $criteria['folderId'] = $folderId;
        $criteria['filename'] = $value;
        $criteria['includeSubfolders'] = true;

        Craft::configure($query, $criteria);

        $foundElement = $query->one();

        // Do we want to match existing elements, and was one found?
        if ($foundElement && $conflict === AssetElement::SCENARIO_INDEX) {
            $this->element = $foundElement;

            return $foundElement->filename;
        }

        // We can't find an existing asset, we need to download it, or plain ignore it
        if ($urlToUpload) {
            $uploadedElementIds = AssetHelper::fetchRemoteImage([$urlToUpload], $fieldInfo, $this->feed, null, $this->element, $folderId);

            if ($uploadedElementIds) {
                $foundElement = AssetElement::findOne(['id' => $uploadedElementIds[0]]);
                $this->element = $foundElement;

                return $foundElement->filename;
            }
        }
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