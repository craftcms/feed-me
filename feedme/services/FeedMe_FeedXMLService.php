<?php
namespace Craft;

class FeedMe_FeedXMLService extends BaseApplicationComponent
{
	public function getFeed($url, $primaryElement) {
    	if (false === ($raw_content = @file_get_contents($url))) {
    		craft()->userSession->setError(Craft::t('Unable to parse Feed URL.'));
    		return false;
    	}

		$xml_array = $this->fetchXML($raw_content, $primaryElement);

		if (!is_array($xml_array)) {
    		craft()->userSession->setError(Craft::t('Invalid XML.'));
    		return false;
    	}

		return $xml_array;
	}

    public function fetchXML($raw_content, $primaryElement) {
    	// Parse the XML string
		$parsedXML = $this->parseXML($raw_content);

		// Convert it to an array
		$parsed = $this->elementArray($parsedXML);

		// Look for and return only the items for primary element
    	return $this->findPrimaryElement($primaryElement, $parsed);
    }

	function elementArray($xml, $first = true)
	{
		$xml->tag = strtolower($xml->tag);

		if (empty($xml->children)) {
			$return = $xml->value;
		} else {
			$return = array();
			foreach($xml->children as $child) {
				$child->tag = strtolower($child->tag);

				if (isset($return[$child->tag])) {
					if (!is_array($return[$child->tag]) OR !isset($return[$child->tag][0])) {
						$return[$child->tag] = array(0 => $return[$child->tag]);
					}

					$return[$child->tag][] = $this->elementArray($child, false);
				} else {
					$return[$child->tag] = $this->elementArray($child, false);
				}
			}
		}

		if ($first === false) {
			return $return;
		}

		$return = array($xml->tag => $return);

		return $return;
	}

	function findPrimaryElement($element, $xml) {
		if (empty($xml)) {
			return false;
		}

		// If no primary element, return root
		if (!$element) {
			return $xml;
		}

		if (isset($xml[$element])) {
			// Ensure we return an array - even if only one element found
			if (is_array($xml[$element])) {
				if (array_key_exists('0', $xml[$element])) { // is multidimensional
					return $xml[$element];
				} else {
					return array($xml[$element]);
				}
			}
		}

		foreach ($xml as $key => $val) {
			if (is_array($val)) {
				$return = $this->findPrimaryElement( $element, $val);

				if ($return !== false) {
					return $return;
				}
			}
		}

		return false;
	}

	function parseXML($xml) {
		$xmlArray = null;

		$parser = xml_parser_create();
		xml_parser_set_option($parser, XML_OPTION_SKIP_WHITE, 1);
		xml_parse_into_struct($parser, $xml, $xmlArray, $indexdata);
		xml_parser_free($parser);

		$elements = array();
		$child = array();
		foreach ($xmlArray as $item) {
			$current = count($elements);

			if ($item['type'] == 'open' OR $item['type'] == 'complete') {
				$elements[$current] 			= new \stdClass;
				$elements[$current]->tag		= strtolower($item['tag']);
				//$elements[$current]->attributes	= (array_key_exists('attributes', $item)) ? $item['attributes'] : '';
				$elements[$current]->value		= (array_key_exists('value', $item)) ? $item['value'] : '';

				if ($item['type'] == "open") {
					$elements[$current]->children = array();
					$child[count($child)] = &$elements;
					$elements = &$elements[$current]->children;
				}
			} else if ($item['type'] == 'close') {
				$elements = &$child[count($child) - 1];
				unset($child[count($child) - 1]);
			}
		}

		return $elements[0];
	}


	function cutArray($array, $depth, $currDepth = 0) {
		$contains_array = false;

		if ($currDepth > $depth) {
			return null;  
		}

		$returnArray = array();
		foreach($array as $key => $value) {        
			if (is_array($value)) {

				// Helps to remove max-depth nodes whose children contains arrays.
				// This is mainly to take care of deeply nested objects in the feed which (at this stage) can't be mapped effectively
				//
				// eg = okay: array(
				//     'category1',
				//     'category2',
				// )
				//
				// eg = not okay: array(
				//     'category1',
				//     'category2' => array( // this will be removed
				//			'category2'
				// 		)
				// )
				//

				if ($currDepth+1 == $depth) {
				    foreach ($value as $i => $v) {
				        if (is_array($v)) {
				        	unset($value[$i]); // remove this array - keeps all other elements
				        }
				    }
				}

				$returnArray[$key] = $this->cutArray($value, $depth , $currDepth +1);
			} else {
				$returnArray[$key] = $value;
			}
		}

		return $returnArray;
	}

	function containsArray(&$array){
	    return false;
	}

	public function getFeedMapping($url, $primaryElement) {
		$xml_array = $this->getFeed($url, $primaryElement);

    	$xml_array = $this->getFormattedMapping($xml_array[0]);

		return $xml_array;
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
				$return[$sep . $key . '/[...]'] = array();
			} elseif(isset($value[0])) {
				if (is_string($value[0])) {
					$return[$sep . $key] = $value[0];
				} else {
					$return = array_merge($return, $this->getFormattedMapping($value[0], $sep . $key.'/[...]'));
				}
			} else {
				$return = array_merge($return, $this->getFormattedMapping($value, $sep . $key));
			}
		}

		return $return;
	}
}
