<?php
namespace Craft;

class XmlFeedMeDataType extends BaseFeedMeDataType
{
    // Public Methods
    // =========================================================================

    public function getFeed($url, $primaryElement, $settings)
    {
        if (false === ($raw_content = craft()->feedMe_data->getRawData($url))) {
            FeedMePlugin::log($settings->name . ': Unable to reach ' . $url . '. Check this is the correct URL.', LogLevel::Error, true);

            return false;
        }

        // Parse the XML string into an array
        try {
            $xml_array = $this->_parseXML($raw_content);
        } catch (Exception $e) {
            FeedMePlugin::log($settings->name . ': Invalid XML - ' . $e->getMessage(), LogLevel::Error, true);

            return false;
        }

        // Look for and return only the items for primary element
        if ($primaryElement && is_array($xml_array)) {
            $xml_array = craft()->feedMe_data->findPrimaryElement($primaryElement, $xml_array);
        }

        if (!is_array($xml_array)) {
            FeedMePlugin::log($settings->name . ': Invalid XML - ' . print_r($xml_array, true), LogLevel::Error, true);

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

    /*public function _parseXML($payload)
    {
        if ($payload) {
            try {
                $xml = simplexml_load_string($payload, 'SimpleXMLElement', (LIBXML_VERSION >= 20700) ? (LIBXML_PARSEHUGE | LIBXML_NOCDATA) : LIBXML_NOCDATA);
                $ns = array('' => null) + $xml->getDocNamespaces(true);

                return $this->_recursiveParse($xml, $ns);
            } catch (\Exception $ex) {
                throw new ParserException('Failed To Parse XML');
            }
        }

        return array();
    }


    protected function _recursiveParse($xml, $ns)
    {
        $xml_string = (string)$xml;

        if ($xml->count() == 0 and $xml_string != '') {
            if (count($xml->attributes()) == 0) {
                if (trim($xml_string) == '') {
                    $result = null;
                } else {
                    $result = $xml_string;
                }
            } else {
                $result[] = array('value' => $xml_string);
            }
        } else {
            $result = null;
        }

        foreach ($xml->attributes() as $attName => $attValue) {
            $result[][$attName] = (string)$attValue;
        }

        foreach ($xml->children() as $childName => $child) {

            $child = $this->_recursiveParse($child, $ns);
            if (is_array($result) and array_key_exists($childName, $result)) {
                if (is_array($result[$childName]) and is_numeric(key($result[$childName]))) {
                    if (isset($child[0])) {
                        $result[$childName][] = $child[0];
                    } else {
                        $result[$childName][] = $child;
                    }
                } else {
                    $temp = $result[$childName];

                    $result[$childName] = array($temp, $child);
                }
            } else {
                $result[$childName] = $child;
            }
        }

        return $result;
    }*/


    private function _parseXML($xml_string) {
        libxml_use_internal_errors(true);
        $xml = new \DomDocument('1.0', 'utf-8');

        if (is_string($xml_string)) {
            $xml->loadXML($xml_string);

            if (!is_object($xml) || empty($xml->documentElement)) {
                $error = libxml_get_errors();
                throw new Exception($error[0]->message);
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
