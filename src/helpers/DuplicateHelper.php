<?php

namespace craft\feedme\helpers;

class DuplicateHelper
{
    const Add = 'add';
    const Update = 'update';
    const Disable = 'disable';
    const DisableForSite = 'disableForSite';
    const Delete = 'delete';

    // Public Methods
    // =========================================================================

    /**
     * @param $handles
     * @return string
     */
    public static function getFriendly($handles)
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
    public static function contains($handles, $handle, $only = false)
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
    public static function isAdd($feedData, $only = false)
    {
        return self::contains($feedData['duplicateHandle'], self::Add, $only);
    }

    /**
     * @param $feedData
     * @param false $only
     * @return bool
     */
    public static function isUpdate($feedData, $only = false)
    {
        return self::contains($feedData['duplicateHandle'], self::Update, $only);
    }

    /**
     * @param $feedData
     * @param false $only
     * @return bool
     */
    public static function isDisable($feedData, $only = false)
    {
        return self::contains($feedData['duplicateHandle'], self::Disable, $only);
    }

    /**
     * @param $feedData
     * @param false $only
     * @return bool
     */
    public static function isDisableForSite($feedData, $only = false)
    {
        return self::contains($feedData['duplicateHandle'], self::DisableForSite, $only);
    }

    /**
     * @param $feedData
     * @param false $only
     * @return bool
     */
    public static function isDelete($feedData, $only = false)
    {
        return self::contains($feedData['duplicateHandle'], self::Delete, $only);
    }
}
