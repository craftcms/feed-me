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
        //check if the pagination url provided is just a page number
        if ($this->_generateNextPaginationFromPageNumber()) {
            return true;
        }

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

    private function _generateNextPaginationFromPageNumber() {
        if (is_numeric($this->paginationUrl)) {
            $nextPage = $this->paginationUrl + 1;
            if($nextPage > 13) {
                return false;
            }

            $this->feedUrl = $this->_setPageQueryString($this->feedUrl, "page", $nextPage);
            return true;
        }
    }

    private function _setPageQueryString($url, $param, $value)
    {
        //remove query string if it already exists
        $pieces = parse_url($url);
        if (!isset($pieces['query']) || !$pieces['query']) {
            return $this->_addQueryString($url, $param, $value);
        }

        $query = [];
        parse_str($pieces['query'], $query);
        if (!isset($query[$param])) {
            return $this->_addQueryString($url, $param, $value);
        }

        unset($query[$param]);
        $pieces['query'] = http_build_query($query);

        $url = http_build_url($pieces);
        return $this->_addQueryString($url, $param, $value);
    }

    private function _addQueryString($url, $param, $value)
    {
        $url = preg_replace('/(.*)(?|&)'. $param .'=[^&]+?(&)(.*)/i', '$1$2$4', $url .'&');
        $url = substr($url, 0, -1);
        if (strpos($url, '?') === false) {
            return ($url .'?'. $param .'='. $value);
        } else {
            return ($url .'&'. $param .'='. $value);
        }
    }

}