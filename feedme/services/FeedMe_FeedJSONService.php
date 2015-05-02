<?php
namespace Craft;

class FeedMe_FeedJSONService extends BaseApplicationComponent
{
	public function getFeed($url, $primaryElement) {
    	if (false === ($raw_content = @file_get_contents($url))) {
    		craft()->userSession->setError(Craft::t('Unable to parse Feed URL.'));
    		return false;
    	}

		$json_array = $this->fetchJSON($raw_content, $primaryElement);

		if (!is_array($json_array)) {
    		craft()->userSession->setError(Craft::t('Invalid JSON.'));
    		return false;
    	}

		return $json_array;
	}

    public function fetchJSON($raw_content, $primaryElement) {
    	// Parse the JSON string
		$parsedJSON = json_decode($raw_content, true);

		// Look for and return only the items for primary element
    	return $this->findPrimaryElement($primaryElement, $parsedJSON);
    }

	function findPrimaryElement($element, $parsed) {
		if (empty($parsed)) {
			return false;
		}

		if (isset($parsed[$element])) {
			return $parsed[$element];
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

	public function getFeedMapping($url, $primaryElement) {
		$json_array = $this->getFeed($url, $primaryElement);

    	$json_array = craft()->feedMe_feed->getFormattedMapping($json_array[0]);

		return $json_array;
	}

}
