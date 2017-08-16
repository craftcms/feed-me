<?php
namespace Craft;

class FeedMeHelper
{
    // Public Methods
    // =========================================================================

    public static function parseBoolean($value)
    {
        $result = filter_var($value, FILTER_VALIDATE_BOOLEAN);

        // Also check for translated values of boolean-like terms
        if (strtolower($value) === Craft::t('yes')) {
            $result = true;
        }
        
        if (strtolower($value) === Craft::t('on')) {
            $result = true;
        }

        return $result;
    }

}
