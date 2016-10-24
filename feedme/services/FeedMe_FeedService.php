<?php
namespace Craft;

class FeedMe_FeedService extends BaseApplicationComponent
{
    // Public Methods
    // =========================================================================

    public function getFeed($type, $url, $element, $returnAttr = false) {
        // Check for and environment variables in url
        $url = craft()->config->parseEnvironmentString($url);

        if ($type == FeedMe_FeedType::JSON) {
            return craft()->feedMe_feedJSON->getFeed($url, $element);
        } else {
            return craft()->feedMe_feedXML->getFeed($url, $element, $returnAttr);
        }
    }

    public function getFeedMapping($type, $url, $element) {
        $array = $this->getFeed($type, $url, $element);

        if (isset($array[0])) {
            $array = $this->getFormattedMapping($array[0]);
        } else {
            $array = $this->getFormattedMapping($array);
        }

        return $array;
    }

    function findPrimaryElement($element, $parsed) {
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

    public function getFormattedMapping($data, $sep = '') {
        $return = array();

        if ($sep != '') {
            $sep .= '/';
        }

        if (!is_array($data)) {
            return $data;
        }

        foreach($data as $key => $value) {
            if (!is_array($value)) {
                $return[$sep . $key] = $value;
            } elseif (count($value) == 0) {
                $return[$sep . $key . '/...'] = array();
            } elseif(isset($value[0])) {
                if (is_string($value[0]) || is_numeric($value[0])) {
                    $return[$sep . $key] = $value[0];
                } else {
                    $return = array_merge($return, $this->getFormattedMapping($value[0], $sep . $key.'/...'));
                }
            } else {
                $return = array_merge($return, $this->getFormattedMapping($value, $sep . $key));
            }
        }

        return $return;
    }

    public function getValueForNode($element, $data)
    {
        if (empty($data)) {
            return null;
        }

        if (!is_string($element) || $element == '') {
            return null;
        }

        if (stristr($element, '/')) {
            $original_data = $data;

            $indexes = explode('/', $element);

            while (count($indexes) > 0) {
                $elementNode = array_shift($indexes);

                if ($elementNode === '...') {
                    if (is_array($data)) {

                        if (!isset($data[0])) {
                            $data = array($data);
                        }
                        
                        $next = array_shift($indexes);

                        if (!isset($next)) {
                            return $data;
                        }

                        $next_data = array();

                        foreach($data as $subkey => $subvalue) {
                            unset($data[$subkey]);
                            $next_element = $this->getValueForNode($next, $subvalue);

                            //if (!empty($next_element)) {
                                $next_data[] = $next_element;
                            //}
                        }

                        return (empty($next_data)) ? false : $next_data;
                    } else {
                        $data = (is_string($data)) ? $data : null;
                    }
                } else {
                    $data = $this->getValueForNode($elementNode, $data);
                }

                if ($data === null) {
                    break;
                }
            }

            if ($data !== false) {
                return $data;
            }

            $data = $original_data;
        }

        if (isset($data[$element])) {
            if (is_array($data[$element])) {
                if (count($data[$element]) == 0) {
                    return '';
                }

                if (isset($data[$element][0])) {
                    return $data[$element];
                }
            }

            return $data[$element];
        }

        if (is_array($data)) {
            foreach ($data as $key => $val) {
                if (is_array($val)) {
                    $return = $this->getValueForNode($element, $val);

                    if ($return !== null) {
                        if (is_array($return) && isset($return[0])) {
                            return $return;
                        }

                        return $return;
                    }
                }
            }
        }

        return null;
    }

    public function getRawData($url)
    {
        $curl = curl_init();
        curl_setopt($curl, CURLOPT_URL, $url);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($curl, CURLOPT_FOLLOWLOCATION, true);
        curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
        $response = curl_exec($curl);

        if (!$response) {
            FeedMePlugin::log(print_r($response, true), LogLevel::Error, true);
            FeedMePlugin::log(curl_error($curl), LogLevel::Error, true);
            return false;
        }

        curl_close($curl);

        return $response;
    }

}
