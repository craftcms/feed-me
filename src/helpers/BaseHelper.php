<?php

namespace craft\feedme\helpers;

use Craft;
use craft\fields\data\ColorData;
use craft\validators\ColorValidator;

class BaseHelper
{
    // Public Methods
    // =========================================================================

    /**
     * @param $value
     * @return bool|mixed|void
     */
    public static function parseBoolean($value)
    {
        $result = filter_var($value, FILTER_VALIDATE_BOOLEAN);

        // Additional checks
        if (is_array($value)) {
            return;
        }

        // Also check for translated values of boolean-like terms
        if (strtolower($value) === Craft::t('feed-me', 'yes')) {
            $result = true;
        }

        if (strtolower($value) === Craft::t('feed-me', 'on')) {
            $result = true;
        }

        if (strtolower($value) === Craft::t('feed-me', 'open')) {
            $result = true;
        }

        if (strtolower($value) === Craft::t('feed-me', 'enabled')) {
            $result = true;
        }

        if (strtolower($value) === Craft::t('feed-me', 'live')) {
            $result = true;
        }

        if (strtolower($value) === Craft::t('feed-me', 'active')) {
            $result = true;
        }

        if (strtolower($value) === Craft::t('feed-me', 'y')) {
            $result = true;
        }


        if (strtolower($value) === Craft::t('feed-me', 'no')) {
            $result = false;
        }

        if (strtolower($value) === Craft::t('feed-me', 'off')) {
            $result = false;
        }

        if (strtolower($value) === Craft::t('feed-me', 'closed')) {
            $result = false;
        }

        if (strtolower($value) === Craft::t('feed-me', 'disabled')) {
            $result = false;
        }

        if (strtolower($value) === Craft::t('feed-me', 'inactive')) {
            $result = false;
        }

        if (strtolower($value) === Craft::t('feed-me', 'n')) {
            $result = false;
        }

        return $result;
    }

    /**
     * @param $value
     * @return ColorData|string|null
     */
    public static function parseColor($value): string|ColorData|null
    {
        if ($value instanceof ColorData) {
            return $value;
        }

        if (!$value || $value === '#') {
            return null;
        }

        $value = strtolower($value);

        if ($value[0] !== '#') {
            $value = '#' . $value;
        }

        if (strlen($value) === 4) {
            $value = '#' . $value[1] . $value[1] . $value[2] . $value[2] . $value[3] . $value[3];
        }

        return ColorValidator::normalizeColor($value);
    }

    /**
     * @param $userAgent
     * @return string
     */
    public static function getBrowserName($userAgent): string
    {
        if (strpos($userAgent, 'Opera') || strpos($userAgent, 'OPR/')) {
            return 'Opera';
        }

        if (strpos($userAgent, 'Edge')) {
            return 'Edge';
        }

        if (strpos($userAgent, 'Chrome')) {
            return 'Chrome';
        }

        if (strpos($userAgent, 'Safari')) {
            return 'Safari';
        }

        if (strpos($userAgent, 'Firefox')) {
            return 'Firefox';
        }

        if (strpos($userAgent, 'MSIE') || strpos($userAgent, 'Trident/7')) {
            return 'Internet Explorer';
        }

        return 'Other';
    }
}
