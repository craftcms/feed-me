<?php
namespace Craft;

class FeedMe_DataService extends BaseApplicationComponent
{
    // Public Methods
    // =========================================================================

    public function getFeed($type, $url, $element, $settings) {
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

    public function getFeedMapping($type, $url, $element, $settings) {
        $data_array = $this->getFeed($type, $url, $element, $settings);

        // Go through entire feed and grab all nodes - that way, its normalised across the entire feed
        // as some nodes don't exist on the first primary element, but do throughout the feed.
        $array = array();

        if ($data_array) {
            foreach ($data_array as $data_array_item) {
                $array = $array + $this->_getFormattedMapping($data_array_item);
            }
        }

        return $array;
    }

    public function findPrimaryElement($element, $parsed) {
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
        $curl = curl_init();

        $defaultOptions = array(
            CURLOPT_URL => $url,
            CURLOPT_RETURNTRANSFER => 1,
            CURLOPT_FOLLOWLOCATION => 1,
            CURLOPT_SSL_VERIFYPEER => 0,
            CURLOPT_USERAGENT => craft()->plugins->getPlugin('feedMe')->getName(),
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

    public function getFeedForTemplate($options = array())
    {
        $plugin = craft()->plugins->getPlugin('feedMe');
        $settings = $plugin->getSettings();

        $url = (array_key_exists('url', $options) ? $options['url'] : null);
        $type = (array_key_exists('type', $options) ? $options['type'] : FeedMe_FeedType::XML);
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

    private function _getFormattedMapping($data, $sep = '') {
        $return = array();

        if ($sep != '') {
            $sep .= '/';
        }

        if (!is_array($data)) {
            return $data;
        }

        foreach ($data as $key => $value) {
            if (!is_array($value)) {
                $return[$sep . $key] = $value;
            } elseif (count($value) == 0) {
                $return[$sep . $key . '/...'] = array();
            } elseif (isset($value[0])) {
                if (is_string($value[0]) || is_numeric($value[0])) {
                    $return[$sep . $key] = $value[0];
                } else {
                    foreach ($value as $v) {
                        $nested = $this->_getFormattedMapping($v, $sep . $key . '/...');

                        if (is_array($nested)) {
                            $return = array_merge($return, $nested);
                        }
                    }
                }
            } else {
                $return = array_merge($return, $this->_getFormattedMapping($value, $sep . $key));
            }
        }

        return $return;
    }

    private function _set($url, $value, $duration)
    {
        return craft()->cache->set(base64_encode(urlencode($url)), $value, $duration, null);
    }

    private function _get($url)
    {
        return craft()->cache->get(base64_encode(urlencode($url)));
    }

}
