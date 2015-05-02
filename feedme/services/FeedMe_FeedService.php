<?php
namespace Craft;

class FeedMe_FeedService extends BaseApplicationComponent
{
    public function getFeedMapping($type, $url, $element) {
        if ($type == FeedMe_FeedType::JSON) {
            return craft()->feedMe_feedJSON->getFeedMapping($url, $element);
        } else {
            return craft()->feedMe_feedXML->getFeedMapping($url, $element);
        }
    }

    public function getFeed($type, $url, $element) {
        if ($type == FeedMe_FeedType::JSON) {
            return craft()->feedMe_feedJSON->getFeed($url, $element);
        } else {
            return craft()->feedMe_feedXML->getFeed($url, $element);
        }
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
                if (is_string($value[0])) {
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

        if (!is_string($element) OR $element == '') {
            return null;
        }

        if (stristr($element, '/')) {
            $original_data = $data;

            $indexes = explode('/', $element);

            while(count($indexes) > 0) {
                $elementNode = array_shift($indexes);

                if ($elementNode === '...') {
                    if (is_array($data)) {

                        if (isset($data[0])) {
                            $next = array_shift($indexes);

                            if (!isset($next)) {
                                return $data;
                            }

                            $next_data = array();

                            foreach($data as $subkey => $subvalue) {
                                unset($data[$subkey]);
                                $next_element = $this->getValueForNode($next, $subvalue);

                                if (!empty($next_element)) {
                                    $next_data[] = $next_element;
                                }
                            }

                            $data = (empty($next_data)) ? false : $next_data;
                        } else {
                            return null;
                        }
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



}
