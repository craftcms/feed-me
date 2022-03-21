<?php

namespace craft\feedme\models;

use Cake\Utility\Hash;
use craft\base\Model;
use craft\feedme\base\Element;
use craft\feedme\base\ElementInterface;
use craft\feedme\helpers\DuplicateHelper;
use craft\feedme\Plugin;

/**
 * Class FeedModel
 *
 * @property-read mixed $duplicateHandleFriendly
 * @property-read mixed $dataType
 * @property-read bool $nextPagination
 * @property-read ElementInterface|Element|null $element
 */
class FeedModel extends Model
{
    // Properties
    // =========================================================================

    /**
     * @var
     */
    public $id;

    /**
     * @var
     */
    public $name;

    /**
     * @var
     */
    public $feedUrl;

    /**
     * @var
     */
    public $feedType;

    /**
     * @var
     */
    public $primaryElement;

    /**
     * @var
     */
    public $elementType;

    /**
     * @var
     */
    public $elementGroup;

    /**
     * @var
     */
    public $siteId;

    /**
     * @var
     */
    public $sortOrder;

    /**
     * @var bool
     * @since 4.3.0
     */
    public $singleton = false;

    /**
     * @var
     */
    public $duplicateHandle;

    /**
     * @var bool
     * @since 4.4.0
     */
    public $updateSearchIndexes = true;

    /**
     * @var
     */
    public $paginationNode;

    /**
     * @var
     */
    public $fieldMapping;

    /**
     * @var
     */
    public $fieldUnique;

    /**
     * @var
     */
    public $passkey;

    /**
     * @var
     */
    public $backup;

    /**
     * @var
     */
    public $dateCreated;

    /**
     * @var
     */
    public $dateUpdated;

    /**
     * @var
     */
    public $uid;

    // Model-only properties

    /**
     * @var
     */
    public $debug;

    /**
     * @var
     */
    public $paginationUrl;


    // Public Methods
    // =========================================================================

    /**
     * @return mixed
     */
    public function __toString()
    {
        return Craft::t('feed-me', $this->name);
    }

    /**
     * @return string
     */
    public function getDuplicateHandleFriendly()
    {
        return DuplicateHelper::getFriendly($this->duplicateHandle);
    }

    /**
     * @return mixed|null
     */
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

    /**
     * @param bool $usePrimaryElement
     * @return array|\ArrayAccess|mixed|null
     */
    public function getFeedData($usePrimaryElement = true)
    {
        $feedDataResponse = Plugin::$plugin->data->getFeedData($this, $usePrimaryElement);

        return Hash::get($feedDataResponse, 'data');
    }

    /**
     * @param false $usePrimaryElement
     * @return mixed
     */
    public function getFeedNodes($usePrimaryElement = false)
    {
        $feedDataResponse = Plugin::$plugin->data->getFeedData($this, $usePrimaryElement);

        $feedData = Hash::get($feedDataResponse, 'data');

        $feedDataResponse['data'] = Plugin::$plugin->data->getFeedNodes($feedData);

        return $feedDataResponse;
    }

    /**
     * @param bool $usePrimaryElement
     * @return mixed
     */
    public function getFeedMapping($usePrimaryElement = true)
    {
        $feedDataResponse = Plugin::$plugin->data->getFeedData($this, $usePrimaryElement);

        $feedData = Hash::get($feedDataResponse, 'data');

        $feedDataResponse['data'] = Plugin::$plugin->data->getFeedMapping($feedData);

        return $feedDataResponse;
    }

    /**
     * @return bool
     */
    public function getNextPagination()
    {
        if (!$this->paginationUrl || !filter_var($this->paginationUrl, FILTER_VALIDATE_URL)) {
            return false;
        }

        // Set the URL dynamically on the feed, then kick off processing again
        $this->feedUrl = $this->paginationUrl;

        return true;
    }

    /**
     * @var
     */
    public function rules()
    {
        return [
            [['name', 'feedUrl', 'feedType', 'elementType', 'duplicateHandle', 'passkey'], 'required'],
            [['backup'], 'boolean'],
        ];
    }
}
