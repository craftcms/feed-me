<?php
namespace Craft;

class FeedMe_FeedXMLService extends BaseApplicationComponent
{
	public function getFeed($url, $primaryElement, $returnAttr = false)
	{
    	if (false === ($raw_content = craft()->feedMe_feed->getRawData($url))) {
    		craft()->userSession->setError(Craft::t('Unable to parse Feed URL.'));
    		return false;
    	}

    	// Parse the XML string
		$xml_array = $this->parseXML($raw_content);

		// Convert it to an array
		$xml_array = $this->elementArray($xml_array, true, $returnAttr);

		// Look for and return only the items for primary element
    	$xml_array = craft()->feedMe_feed->findPrimaryElement($primaryElement, $xml_array);

		if (!is_array($xml_array)) {
    		craft()->userSession->setError(Craft::t('Invalid XML.'));
    		return false;
    	}

		return $xml_array;
	}

	function elementArray($xml, $first = true, $returnAttr = false)
	{
		if (!$xml) {
			return null;
		}

		if (empty($xml->children)) {
			if ($returnAttr) {
				// Used when calling via template code - return the attributes for the node
				$nodeModel = new FeedMe_FeedNodeModel();
				$nodeModel->attributes = $xml->attributes;
				$nodeModel->value = $xml->value;
				$return = $nodeModel;
			} else {
				$return = $xml->value;
			}
		} else {
			$return = array();
			foreach($xml->children as $child) {
				$child->tag = strtolower($child->tag);
				
				if (isset($return[$child->tag])) {
					if (!is_array($return[$child->tag]) OR !isset($return[$child->tag][0])) {
						$return[$child->tag] = array(0 => $return[$child->tag]);
					}

					$return[$child->tag][] = $this->elementArray($child, false, $returnAttr);
				} else {
					$return[$child->tag] = $this->elementArray($child, false, $returnAttr);
				}
			}
		}

		if ($first === false) {
			return $return;
		}

		$return = array($xml->tag => $return);

		return $return;
	}

	function parseXML($xml)
	{
		$xmlArray = null;

		$parser = xml_parser_create();
		xml_parser_set_option($parser, XML_OPTION_SKIP_WHITE, 1);
		xml_parser_set_option($parser, XML_OPTION_CASE_FOLDING, false);
		xml_parse_into_struct($parser, $xml, $xmlArray, $indexdata);
		xml_parser_free($parser);

		$elements = array();
		$child = array();
		foreach ($xmlArray as $item) {
			$current = count($elements);

			if ($item['type'] == 'open' || $item['type'] == 'complete') {
				$elements[$current] 			= new \stdClass;
				$elements[$current]->tag		= $item['tag'];
				$elements[$current]->attributes	= (array_key_exists('attributes', $item)) ? $item['attributes'] : '';
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

		if ($elements) {
			return $elements[0];
		} else {
			return null;
		}
	}

}
