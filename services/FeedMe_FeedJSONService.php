<?php
namespace Craft;

class FeedMe_FeedJSONService extends BaseApplicationComponent
{
    // Public Methods
    // =========================================================================

    public function getFeed($url, $primaryElement) {
        if (false === ($raw_content = craft()->feedMe_feed->getRawData($url))) {
            craft()->userSession->setError(Craft::t('Unable to parse Feed URL.'));
            FeedMePlugin::log('Unable to parse Feed URL.', LogLevel::Error, true);

            return false;
        }

        // Parse the JSON string - using Yii's built-in cleanup
        $json_array = JsonHelper::decode($raw_content, true);

        // Look for and return only the items for primary element
        $json_array = craft()->feedMe_feed->findPrimaryElement($primaryElement, $json_array);

        if (!is_array($json_array)) {
            $error = 'Invalid JSON - ' . $this->getJsonError();

            craft()->userSession->setError(Craft::t($error));
            FeedMePlugin::log($error, LogLevel::Error, true);
            
            return false;
        }

        return $json_array;
    }

    public function getJsonError()
    {
        if (!function_exists('json_last_error_msg')) {
            $errors = array(
                JSON_ERROR_NONE             => null,
                JSON_ERROR_DEPTH            => 'Maximum stack depth exceeded',
                JSON_ERROR_STATE_MISMATCH   => 'Underflow or the modes mismatch',
                JSON_ERROR_CTRL_CHAR        => 'Unexpected control character found',
                JSON_ERROR_SYNTAX           => 'Syntax error, malformed JSON',
                JSON_ERROR_UTF8             => 'Malformed UTF-8 characters, possibly incorrectly encoded'
            );

            $error = json_last_error();
            return array_key_exists($error, $errors) ? $errors[$error] : "Unknown error ({$error})";
        } else {
            return json_last_error_msg();
        }
    }
}
