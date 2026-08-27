<?php

namespace craft\feedme\helpers;

class DuplicateHelper
{
    public const Add = 'add';
    public const Update = 'update';
    public const Disable = 'disable';
    public const DisableForSite = 'disableForSite';
    public const Delete = 'delete';

    // Public Methods
    // =========================================================================

    /**
     * @param $handles
     * @return string
     */
    public static function getFriendly($handles): string
    {
        $array = [];

        foreach ($handles as $handle) {
            $array[] = ucfirst($handle);
        }

        return implode(' & ', $array);
    }

    /**
     * @param $handles
     * @param $handle
     * @param false $only
     * @return bool
     */
    public static function contains($handles, $handle, bool $only = false): bool
    {
        if (in_array($handle, $handles, true)) {
            if ($only) {
                if (count($handles) == 1) {
                    return true;
                }
            } else {
                return true;
            }
        }

        return false;
    }

    /**
     * @param $feedData
     * @param false $only
     * @return bool
     */
    public static function isAdd($feedData, bool $only = false): bool
    {
        return self::contains($feedData['duplicateHandle'], self::Add, $only);
    }

    /**
     * @param $feedData
     * @param false $only
     * @return bool
     */
    public static function isUpdate($feedData, bool $only = false): bool
    {
        return self::contains($feedData['duplicateHandle'], self::Update, $only);
    }

    /**
     * @param $feedData
     * @param false $only
     * @return bool
     */
    public static function isDisable($feedData, bool $only = false): bool
    {
        return self::contains($feedData['duplicateHandle'], self::Disable, $only);
    }

    /**
     * @param $feedData
     * @param false $only
     * @return bool
     */
    public static function isDisableForSite($feedData, bool $only = false): bool
    {
        return self::contains($feedData['duplicateHandle'], self::DisableForSite, $only);
    }

    /**
     * @param $feedData
     * @param false $only
     * @return bool
     */
    public static function isDelete($feedData, bool $only = false): bool
    {
        return self::contains($feedData['duplicateHandle'], self::Delete, $only);
    }
}
