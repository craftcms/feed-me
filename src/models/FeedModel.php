<?php

namespace craft\feedme\models;

use Cake\Utility\Hash;
use Craft;
use craft\base\Model;
use craft\feedme\base\Element;
use craft\feedme\base\ElementInterface;
use craft\feedme\helpers\DuplicateHelper;
use craft\feedme\Plugin;

/**
 * Class FeedModel
 *
 * @property-read ElementInterface|Element|null $element
 */
class FeedModel extends Model
{
    // Properties
    // =========================================================================

    public $id;
    public $name;
    public $feedUrl;
    public $feedType;
    public $primaryElement;
    public $elementType;
    public $elementGroup;
    public $siteId;
    public $sortOrder;
    public $duplicateHandle;
    public $paginationNode;
    public $fieldMapping;
    public $fieldUnique;
    public $passkey;
    public $backup;
    public $dateCreated;
    public $dateUpdated;
    public $uid;

    // Model-only properties
    public $debug;
    public $paginationUrl;


    // Public Methods
    // =========================================================================

    public function __toString()
    {
        return Craft::t('feed-me', $this->name);
    }

    public function getFeedUrl(): string
    {
        return Craft::parseEnv($this->feedUrl);
    }

    public function getDuplicateHandleFriendly()
    {
        return DuplicateHelper::getFrieldly($this->duplicateHandle);
    }

    public function getDataType()
    {
        return Plugin::$plugin->data->getRegisteredDataType($this->feedType);
    }

    /**
     * @return ElementInterface|null
     */
    public function getElement()
    {
        $element = Plugin::$plugin->elements->getRegisteredElement($this->elementType);

        if ($element) {
            /** @var Element $element */
            $element->feed = $this;
        }

        return $element;
    }

    public function getFeedData($usePrimaryElement = true)
    {
        $feedDataResponse = Plugin::$plugin->data->getFeedData($this, $usePrimaryElement);

        return Hash::get($feedDataResponse, 'data');
    }

    public function getFeedNodes($usePrimaryElement = false)
    {
        $feedDataResponse = Plugin::$plugin->data->getFeedData($this, $usePrimaryElement);

        $feedData = Hash::get($feedDataResponse, 'data');

        $feedDataResponse['data'] = Plugin::$plugin->data->getFeedNodes($feedData);

        return $feedDataResponse;
    }

    public function getFeedMapping($usePrimaryElement = true)
    {
        $feedDataResponse = Plugin::$plugin->data->getFeedData($this, $usePrimaryElement);

        $feedData = Hash::get($feedDataResponse, 'data');

        $feedDataResponse['data'] = Plugin::$plugin->data->getFeedMapping($feedData);

        return $feedDataResponse;
    }

    public function getNextPagination()
    {
        if (!$this->paginationUrl) {
            return;
        }

        // Set the URL dynamically on the feed, then kick off processing again
        $this->feedUrl = $this->paginationUrl;

        return true;
    }

    public function rules()
    {
        return [
            [['name', 'feedUrl', 'feedType', 'elementType', 'duplicateHandle', 'passkey'], 'required'],
            [['backup'], 'boolean'],
        ];
    }

}
