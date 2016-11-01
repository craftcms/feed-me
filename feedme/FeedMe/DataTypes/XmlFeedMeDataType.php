<?php
namespace Craft;

class XmlFeedMeDataType extends BaseFeedMeDataType
{
    // Public Methods
    // =========================================================================

    public function getFeed($url, $primaryElement)
    {
        if (false === ($raw_content = craft()->feedMe_data->getRawData($url))) {
            FeedMePlugin::log('Unable to reach ' . $url . '. Check this is the correct URL.', LogLevel::Error, true);

            return false;
        }

        // Parse the XML string into an array
        $xml_array = $this->_parseXML($raw_content);

        // Look for and return only the items for primary element
        if ($primaryElement && is_array($xml_array)) {
            $xml_array = craft()->feedMe_data->findPrimaryElement($primaryElement, $xml_array);
        }

        if (!is_array($xml_array)) {
            FeedMePlugin::log('Invalid XML - ' . print_r($xml_array, true), LogLevel::Error, true);

            return false;
        }

        return $xml_array;
    }


    // Private Methods
    // =========================================================================

    /*private function _parseXML($xml_string) {
        // Perform cleanup on raw data first
        $xml_string = preg_replace("/[\r\n]+/", " ", $xml_string);
        $xml_string = stripslashes($xml_string);
        $xml_string = utf8_encode($xml_string);
        $xml_string = StringHelper::convertToUTF8($xml_string);
        
        $parser = xml_parser_create(); 
        xml_parser_set_option($parser, XML_OPTION_TARGET_ENCODING, 'UTF-8');
        xml_parser_set_option($parser, XML_OPTION_CASE_FOLDING, 0); 
        xml_parser_set_option($parser, XML_OPTION_SKIP_WHITE, 1); 
        $response = xml_parse_into_struct($parser, trim($xml_string), $xml_values); 

        // Error-handling
        if (!$response) {
            $error = 'Error: ' . xml_error_string(xml_get_error_code($parser)) . ' at line ' . xml_get_current_line_number($parser);
        }

        xml_parser_free($parser);

        // If there was an error, and we've released the parser, return now
        if (isset($error)) {
            return $error;
        }
        
        $xml_array = array();
        $parents = array();
        $opened_tags = array();
        $arr = array();

        $current = &$xml_array;

        foreach ($xml_values as $data) {
            unset($attributes, $value);
            extract($data);

            $result = '';
            $result = array();
            if (isset($value)) {
                $result['value'] = $value;
            }

            if (isset($attributes)) {
                foreach ($attributes as $attr => $val) {
                    $result['attributes'][$attr] = $val;
                }
            }

            if ($type == "open") {
                $parent[$level-1] = &$current;

                if (!is_array($current) or (!in_array($tag, array_keys($current)))) {
                    $current[$tag] = $result;
                    $current = &$current[$tag];
                } else {
                    if (isset($current[$tag][0])) {
                        array_push($current[$tag], $result);
                    } else {
                        $current[$tag] = array($current[$tag], $result);
                    }

                    $last = count($current[$tag]) - 1;
                    $current = &$current[$tag][$last];
                }
            } elseif ($type == "complete") {
                if (!isset($current[$tag])) {
                    $current[$tag] = $result;
                } else {
                    if (isset($current[$tag][0]) and is_array($current[$tag][0])) {
                        array_push($current[$tag], $result);
                    } else {
                        $current[$tag] = array($current[$tag], $result);
                    }
                }
            } elseif($type == 'close') {
                $current = &$parent[$level-1];
            }
        }

        return($xml_array);
    }*/


    private function _parseXML($xml_string) {
        $xml = new \DomDocument('1.0', 'utf-8');

        if (is_string($xml_string)) {
            try {
                $xml->loadXML($xml_string);

                if (!is_object($xml) || empty($xml->documentElement)) {
                    throw new Exception();
                }
            } catch (Exception $ex) {
                throw new Exception('[XML2Array] Error parsing the XML string.'.PHP_EOL . $ex->getMessage());
            }
        }

        $array[$xml->documentElement->tagName] = $this->_convert($xml->documentElement);

        return $array;
    }

    private function _convert(\DOMNode $node)
    {
        $output = array();

        switch ($node->nodeType) {
            case XML_CDATA_SECTION_NODE:
                $output = trim($node->textContent);
                break;
            case XML_TEXT_NODE:
                $output = trim($node->textContent);
                break;
            case XML_ELEMENT_NODE:
                for ($i = 0, $m = $node->childNodes->length; $i < $m; ++$i) {
                    $child = $node->childNodes->item($i);
                    $v = $this->_convert($child);
                    
                    if (isset($child->tagName)) {
                        $t = $child->tagName;
                        
                        if (!array_key_exists($t, $output)) {
                            $output[$t] = [];
                        }

                        $output[$t][] = $v;
                    } else {
                        if (!empty($v)) {
                            $output = $v;
                        }
                    }
                }

                if (is_array($output)) {
                    foreach ($output as $t => $v) {
                        if (is_array($v) && count($v) == 1) {
                            $output[$t] = $v[0];
                        }
                    }

                    if (empty($output)) {
                        $output = '';
                    }
                }

                if ($node->attributes->length) {
                    $a = array();

                    foreach ($node->attributes as $attrName => $attrNode) {
                        $a[$attrName] = $attrNode->value;
                    }

                    if (!is_array($output)) {
                        $output = array('value' => $output);
                    }

                    $output['attributes'] = $a;
                }

                break;
        }

        return $output;
    }


}
