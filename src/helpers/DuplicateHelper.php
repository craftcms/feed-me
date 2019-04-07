<?php

namespace verbb\feedme\helpers;

class DuplicateHelper
{
    const Add = 'add';
    const Update = 'update';
    const Disable = 'disable';
    const Delete = 'delete';

    // Public Methods
    // =========================================================================

    public static function getFrieldly($handles)
    {
        $array = [];

        foreach ($handles as $handle) {
            $array[] = ucfirst($handle);
        }

        return implode(' & ', $array);
    }

    public static function contains($handles, $handle, $only = false)
    {
        if (in_array($handle, $handles)) {
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

    public static function isAdd($feedData, $only = false)
    {
        return DuplicateHelper::contains($feedData['duplicateHandle'], DuplicateHelper::Add, $only);
    }

    public static function isUpdate($feedData, $only = false)
    {
        return DuplicateHelper::contains($feedData['duplicateHandle'], DuplicateHelper::Update, $only);
    }

    public static function isDisable($feedData, $only = false)
    {
        return DuplicateHelper::contains($feedData['duplicateHandle'], DuplicateHelper::Disable, $only);
    }

    public static function isDelete($feedData, $only = false)
    {
        return DuplicateHelper::contains($feedData['duplicateHandle'], DuplicateHelper::Delete, $only);
    }
}
