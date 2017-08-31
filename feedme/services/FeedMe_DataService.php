<?php
namespace Craft;

use Cake\Utility\Hash as Hash;

class FeedMe_DataService extends BaseApplicationComponent
{
    // Properties
    // =========================================================================

    private $_headers = array();


    // Public Methods
    // =========================================================================

    public function getFeed($type, $url, $element, $settings)
    {
        // Check for and environment variables in url
        $url = craft()->config->parseEnvironmentString($url);

        if (!($service = craft()->feedMe->getDataTypeService($type))) {
            throw new Exception(Craft::t('Unknown Data Type Service called.'));
        }

        $data = $service->getFeed($url, $element, $settings);

        if (!isset($data[0])) {
            $data = array($data);
        }

        if (empty($data[0])) {
            return null;
        } else {
            return $data;
        }
    }

    public function getFeedMapping($dataArray)
    {
        $mappingPaths = array();

        if (!is_array($dataArray)) {
            return array();
        }

        // Go through entire feed and grab all nodes - that way, its normalised across the entire feed
        // as some nodes don't exist on the first primary element, but do throughout the feed.
        foreach (Hash::flatten($dataArray, '/') as $nodePath => $value) {
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

    public function getContentMapping($dataArray)
    {
        $contentNodes = array();

        foreach (Hash::flatten($dataArray) as $nodePath => $value) {
            $nodePath = preg_replace('/(\.\d+)$/', '', $nodePath);

            preg_match('/^(\d+)\./', $nodePath, $matches);

            if (isset($matches[1]) && $matches[1] != '') {
                $index = $matches[1];
            } else {
                $index = 0;
                $nodePath = $nodePath;
            }

            $contentNodes[$index][] = $nodePath;
        }

        foreach ($contentNodes as $key => $value) {
            $contentNodes[$key] = array_unique($value);
        }

        return $contentNodes;
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
                    return array($parsed[$element]);
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

    public function getRawData($url)
    {
        // Check for local URL
        if (!UrlHelper::isAbsoluteUrl($url)) {
            return file_get_contents($url);
        }

        $curl = curl_init();

        $defaultOptions = array(
            CURLOPT_URL => $url,
            CURLOPT_RETURNTRANSFER => 1,
            CURLOPT_FOLLOWLOCATION => 1,
            CURLOPT_SSL_VERIFYPEER => 0,
            CURLOPT_USERAGENT => craft()->plugins->getPlugin('feedMe')->getName(),
            CURLOPT_HEADERFUNCTION => array($this, '_handleFeedMeDataHeader'),
        );

        $configOptions = craft()->config->get('curlOptions', 'feedMe');

        if ($configOptions) {
            $options = $configOptions + $defaultOptions;
        } else {
            $options = $defaultOptions;
        }

        curl_setopt_array($curl, $options);
        $response = curl_exec($curl);

        $httpCode = curl_getinfo($curl, CURLINFO_HTTP_CODE);

        if (!$response) {
            FeedMePlugin::log($url . ' response: ' . print_r($response, true), LogLevel::Error, true);
            FeedMePlugin::log(curl_error($curl), LogLevel::Error, true);

            return false;
        } else {
            if ($httpCode != 200 && $httpCode != 226) {
                FeedMePlugin::log($url . ' responded with code ' . $httpCode, LogLevel::Error, true);

                return false;
            }
        }

        curl_close($curl);

        return $response;
    }

    public function getFeedHeadersForTemplate($options = array())
    {
        $plugin = craft()->plugins->getPlugin('feedMe');
        $settings = $plugin->getSettings();

        $url = (array_key_exists('url', $options) ? $options['url'] : null);
        $element = (array_key_exists('element', $options) ? $options['element'] : '');
        $cache = (array_key_exists('cache', $options) ? $options['cache'] : true);
        $cacheId = $url . '#headers-' . $element; // cache for this URL and Element Node

        // URL = required
        if (!$url) {
            return array();
        }

        // If cache explicitly set to false, always return latest data
        if ($cache === false) {
            return $this->_headers;
        }

        // We want some caching action!
        if (is_numeric($cache) || $cache === true) {
            $cache = (is_numeric($cache)) ? $cache : $settings->cache;

            $cachedRequest = $this->_get($cacheId);

            if ($cachedRequest) {
                return $cachedRequest;
            } else {
                $data = $this->_headers;
                $this->_set($cacheId, $data, $cache);
                return $data;
            }
        }
    }

    public function getFeedForTemplate($options = array())
    {
        $plugin = craft()->plugins->getPlugin('feedMe');
        $settings = $plugin->getSettings();

        $url = (array_key_exists('url', $options) ? $options['url'] : null);
        $type = (array_key_exists('type', $options) ? $options['type'] : 'xml');
        $element = (array_key_exists('element', $options) ? $options['element'] : '');
        $cache = (array_key_exists('cache', $options) ? $options['cache'] : true);
        $cacheId = $url . '#' . $element; // cache for this URL and Element Node

        // URL = required
        if (!$url) {
            return array();
        }

        // If cache explicitly set to false, always return latest data
        if ($cache === false) {
            return craft()->feedMe_data->getFeed($type, $url, $element, null);
        }

        // We want some caching action!
        if (is_numeric($cache) || $cache === true) {
            $cache = (is_numeric($cache)) ? $cache : $settings->cache;

            $cachedRequest = $this->_get($cacheId);

            if ($cachedRequest) {
                return $cachedRequest;
            } else {
                $data = craft()->feedMe_data->getFeed($type, $url, $element, null);
                $this->_set($cacheId, $data, $cache);
                return $data;
            }
        }
    }



    // Private Methods
    // =========================================================================

    private function _set($url, $value, $duration)
    {
        return craft()->cache->set(base64_encode(urlencode($url)), $value, $duration, null);
    }

    private function _get($url)
    {
        return craft()->cache->get(base64_encode(urlencode($url)));
    }

    private function _handleFeedMeDataHeader($curl, $header)
    {
        $len = strlen($header);
        $header = explode(':', $header, 2);

        if (count($header) < 2) {
            return $len;
        }

        $name = strtolower(trim($header[0]));
        $this->_headers[$name] = trim($header[1]);

        return $len;
    }

}
