<?php
namespace verbb\feedme\models;

use verbb\feedme\FeedMe;
use verbb\feedme\helpers\DuplicateHelper;

use craft\base\Model;

use Cake\Utility\Hash;

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
    public $duplicateHandle;
    public $fieldMapping;
    public $fieldUnique;
    public $passkey;
    public $backup;
    public $dateCreated;
    public $dateUpdated;
    public $uid;

    // Model-only properties
    public $debug;
    

    // Public Methods
    // =========================================================================

    public function __toString()
    {
        return Craft::t('feed-me', $this->name);
    }

    public function getDuplicateHandleFriendly()
    {
        return DuplicateHelper::getFrieldly($this->duplicateHandle);
    }

    public function getDataType()
    {
        return FeedMe::$plugin->data->getRegisteredDataType($this->feedType);
    }

    public function getElement()
    {
        return FeedMe::$plugin->elements->getRegisteredElement($this->elementType);
    }

    public function getFeedData()
    {
        $feedDataResponse = $this->getDataType()->getFeed($this->feedUrl, $this);

        return Hash::get($feedDataResponse, 'data');
    }

    public function getFeedNodes()
    {
        $feedDataResponse = $this->getDataType()->getFeed($this->feedUrl, $this, false);

        $feedData = Hash::get($feedDataResponse, 'data');

        $feedDataResponse['data'] = FeedMe::$plugin->data->getFeedNodes($feedData);

        return $feedDataResponse;
    }

    public function getFeedMapping()
    {
        $feedDataResponse = $this->getDataType()->getFeed($this->feedUrl, $this);

        $feedData = Hash::get($feedDataResponse, 'data');

        $feedDataResponse['data'] = FeedMe::$plugin->data->getFeedMapping($feedData);

        return $feedDataResponse;
    }

    public function rules()
    {
        return [
            [['name', 'feedUrl', 'feedType', 'elementType', 'duplicateHandle', 'passkey'], 'required'],
            [['backup'], 'boolean'],
        ];
    }

}


  