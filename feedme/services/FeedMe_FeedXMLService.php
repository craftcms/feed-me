<?php
namespace Craft;

class FeedMe_FeedXMLService extends BaseApplicationComponent
{
	public function getFeed($url, $primaryElement) {
    	if (false === ($raw_content = craft()->feedMe_feed->getRawData($url))) {
    		craft()->userSession->setError(Craft::t('Unable to parse Feed URL.'));
    		return false;
    	}

    	// Parse the XML string
		$xml_array = $this->parseXML($raw_content);

		// Convert it to an array
		$xml_array = $this->elementArray($xml_array);

		// Look for and return only the items for primary element
    	$xml_array = craft()->feedMe_feed->findPrimaryElement($primaryElement, $xml_array);

		if (!is_array($xml_array)) {
    		craft()->userSession->setError(Craft::t('Invalid XML.'));
    		return false;
    	}

		return $xml_array;
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

}
