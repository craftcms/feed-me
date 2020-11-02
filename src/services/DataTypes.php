<?php

namespace craft\feedme\services;

use Cake\Utility\Hash;
use Craft;
use craft\base\Component;
use craft\feedme\base\DataTypeInterface;
use craft\feedme\datatypes\Atom;
use craft\feedme\datatypes\Csv;
use craft\feedme\datatypes\GoogleSheet;
use craft\feedme\datatypes\Json;
use craft\feedme\datatypes\Rss;
use craft\feedme\datatypes\Xml;
use craft\feedme\events\FeedDataEvent;
use craft\feedme\events\RegisterFeedMeDataTypesEvent;
use craft\feedme\models\FeedModel;
use craft\feedme\Plugin;
use craft\helpers\Component as ComponentHelper;
use craft\helpers\UrlHelper;
use yii\base\Event;

class DataTypes extends Component
{
    // Constants
    // =========================================================================

    const EVENT_REGISTER_FEED_ME_DATA_TYPES = 'registerFeedMeDataTypes';
    const EVENT_BEFORE_FETCH_FEED = 'onBeforeFetchFeed';
    const EVENT_AFTER_FETCH_FEED = 'onAfterFetchFeed';
    const EVENT_AFTER_PARSE_FEED = 'onAfterParseFeed';


    // Properties
    // =========================================================================

    private $_dataTypes = [];
    private $_headers = null;


    // Public Methods
    // =========================================================================

    public function init()
    {
        parent::init();

        foreach ($this->getRegisteredDataTypes() as $dataTypeClass) {
            $dataType = $this->createDataType($dataTypeClass);

            // Does this data type exist in Craft right now?
            if (!class_exists($dataType->getClass())) {
                continue;
            }

            // strtolower for backwards compatibility
            $handle = strtolower($dataType::displayName());

            $this->_dataTypes[$handle] = $dataType;
        }
    }

    public function dataTypesList()
    {
        $list = [];

        foreach ($this->_dataTypes as $handle => $dataType) {
            $list[$handle] = $dataType::$name;
        }

        return $list;
    }

    public function getRegisteredDataType($handle)
    {
        if (isset($this->_dataTypes[$handle])) {
            return $this->_dataTypes[$handle];
        }
    }

    public function getRegisteredDataTypes()
    {
        $event = new RegisterFeedMeDataTypesEvent([
            'dataTypes' => [
                Atom::class,
                Csv::class,
                GoogleSheet::class,
                Json::class,
                Rss::class,
                Xml::class,
            ],
        ]);

        $this->trigger(self::EVENT_REGISTER_FEED_ME_DATA_TYPES, $event);

        return $event->dataTypes;
    }

    public function createDataType($config)
    {
        if (is_string($config)) {
            $config = ['type' => $config];
        }

        try {
            $dataType = ComponentHelper::createComponent($config, DataTypeInterface::class);
        } catch (MissingComponentException $e) {
            $config['errorMessage'] = $e->getMessage();
            $config['expectedType'] = $config['type'];
            unset($config['type']);

            $dataType = new MissingDataType($config);
        }

        return $dataType;
    }

    public function getRawData($url, $feedId = null)
    {
        $event = new FeedDataEvent([
            'url' => $url,
        ]);

        Event::trigger(static::class, self::EVENT_BEFORE_FETCH_FEED, $event);

        if ($event->response) {
            return $event->response;
        }

        $url = $event->url;

        $url = Craft::getAlias($url);

        // If the path starts with / but not //
        if (strpos($url, '/') === 0 && !UrlHelper::isProtocolRelativeUrl($url)) {
            error_clear_last();

            $filepath = realpath($url);

            if (!$filepath) {
                return ['success' => false, 'error' => 'File path cannot be found.'];
            }

            $data = @file_get_contents($filepath);

            $error = error_get_last();

            if ($error) {
                $response = ['success' => false, 'error' => $error['message']];
            } else if (!$data) {
                $response = ['success' => false, 'error' => 'Unable to parse data.'];
            } else {
                $response = ['success' => true, 'data' => $data];
            }

            return $response;
        }

        try {
            $client = Plugin::$plugin->service->createGuzzleClient($feedId);
            $options = Plugin::$plugin->service->getRequestOptions($feedId);

            $resp = $client->request('GET', $url, $options);
            $data = (string)$resp->getBody();

            // Save headers for later
            $this->_headers = $resp->getHeaders();

            $response = ['success' => true, 'data' => $data];
        } catch (\Exception $e) {
            $response = ['success' => false, 'error' => $e->getMessage()];
        }

        $event = new FeedDataEvent([
            'url' => $url,
            'response' => $response,
        ]);

        Event::trigger(static::class, self::EVENT_AFTER_FETCH_FEED, $event);

        return $event->response;
    }

    public function getFeedData($feedModel, $usePrimaryElement = true)
    {
        $feedDataResponse = $feedModel->getDataType()->getFeed($feedModel->getFeedUrl(), $feedModel, $usePrimaryElement);

        $event = new FeedDataEvent([
            'url' => $feedModel->getFeedUrl(),
            'response' => $feedDataResponse,
        ]);

        Event::trigger(static::class, self::EVENT_AFTER_PARSE_FEED, $event);

        return $event->response;
    }

    public function getFeedNodes($data)
    {
        if (!is_array($data)) {
            return [];
        }

        $tree = [];
        $this->_parseNodeTree($tree, $data);
        $nodes = [];

        $elements = (count($data) > 1) ? ' elements' : ' element';
        $nodes[''] = '/root (x' . count($data) . $elements . ')';

        foreach ($tree as $key => $value) {
            $elements = ($value > 1) ? ' elements' : ' element';
            $index = array_values(array_slice(explode('/', $key), -1))[0];

            if (!isset($nodes[$index])) {
                $nodes[$index] = $key . ' (x' . $value . $elements . ')';
            }
        }

        return $nodes;
    }

    public function getFeedMapping($data)
    {
        if (!is_array($data)) {
            return [];
        }

        $mappingPaths = [];

        // Go through entire feed and grab all nodes - that way, its normalised across the entire feed
        // as some nodes don't exist on the first primary element, but do throughout the feed.
        foreach (Hash::flatten($data, '/') as $nodePath => $value) {
            $feedPath = preg_replace('/(\/\d+\/)/', '/', $nodePath);
            $feedPath = preg_replace('/^(\d+\/)|(\/\d+)/', '', $feedPath);

            // The above is used to normalise repeatable nodes. Paths to nodes will look similar to:
            // 0.Assets.Asset.0.Img.0 - we want to change this to Assets/Asset/Img, This is mostly
            // for user-friendliness, we don't need to keep specific details on what is repeatable
            // or not. Thats for the feed-parsing stage (and is greatly improved from our first iteration!)

            if (!isset($mappingPaths[$feedPath])) {
                $mappingPaths[$feedPath] = $value;
            }
        }

        return $mappingPaths;
    }

    public function findPrimaryElement($element, $parsed)
    {
        if (empty($parsed)) {
            return false;
        }

        // If no primary element, return root
        if (!$element) {
            return $parsed;
        }

        if (isset($parsed[$element])) {
            // Ensure we return an array - even if only one element found
            if (is_array($parsed[$element])) {
                if (array_key_exists('0', $parsed[$element])) { // is multidimensional
                    return $parsed[$element];
                } else {
                    return [$parsed[$element]];
                }
            }
        }

        foreach ($parsed as $key => $val) {
            if (is_array($val)) {
                $return = $this->findPrimaryElement($element, $val);

                if ($return !== false) {
                    return $return;
                }
            }
        }

        return false;
    }

    public function getFeedForTemplate($options = [])
    {
        $pluginSettings = Plugin::$plugin->getSettings();

        $url = Hash::get($options, 'url');
        $type = Hash::get($options, 'type');
        $element = Hash::get($options, 'element');
        $cache = Hash::get($options, 'cache', true);

        $limit = Hash::get($options, 'limit');
        $offset = Hash::get($options, 'offset');

        // We can additionally fetch just the headers for the request if required
        $headers = Hash::get($options, 'headers');

        $cacheId = ($headers) ? $url . '#' . $element : $url . '#headers-' . $element;

        // Check for some required options
        if (!$url || !$type) {
            return [];
        }

        $feed = new FeedModel();
        $feed->feedUrl = $url;
        $feed->feedType = $type;

        if ($element) {
            $feed->primaryElement = $element;
        }

        // If cache explicitly set to false, always return latest data
        if ($cache === false) {
            if ($headers) {
                $data = $this->_headers;
            } else {
                $data = Hash::get($this->getFeedData($feed), 'data');
            }

            if ($offset) {
                $data = array_slice($data, $offset);
            }

            if ($limit) {
                $data = array_slice($data, 0, $limit);
            }

            return $data;
        }

        // We want some caching action!
        if (is_numeric($cache) || $cache === true) {
            $cache = (is_numeric($cache)) ? $cache : $pluginSettings->cache;

            $cachedRequest = $this->_getCache($cacheId);

            if ($cachedRequest) {
                return $cachedRequest;
            } else {
                if ($headers) {
                    $data = $this->_headers;
                } else {
                    $data = Hash::get($this->getFeedData($feed), 'data');
                }

                if ($offset) {
                    $data = array_slice($data, $offset);
                }

                if ($limit) {
                    $data = array_slice($data, 0, $limit);
                }

                $this->_setCache($cacheId, $data, $cache);
                return $data;
            }
        }
    }


    // Private
    // =========================================================================

    private function _parseNodeTree(&$tree, $array, $index = '')
    {
        foreach ($array as $key => $val) {
            if (!is_numeric($key)) {
                if (is_array($val)) {
                    $count = count($val);

                    if (Hash::dimensions($val) == 1) {
                        $count = 1;
                    }

                    $tree[$index . '/' . $key] = $count;

                    $this->_parseNodeTree($tree, $val, $index . '/' . $key);
                }
            } else {
                // Keep diving
                if (is_array($val)) {
                    $this->_parseNodeTree($tree, $val, $index);
                }
            }
        }
    }

    private function _setCache($url, $value, $duration)
    {
        return Craft::$app->cache->set(base64_encode(urlencode($url)), $value, $duration, null);
    }

    private function _getCache($url)
    {
        return Craft::$app->cache->get(base64_encode(urlencode($url)));
    }

}
