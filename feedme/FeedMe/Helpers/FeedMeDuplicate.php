<?php
namespace Craft;

use Cake\Utility\Hash as Hash;

class FeedMeDuplicate
{
    const Add       = 'add';
    const Update    = 'update';
    const Disable   = 'disable';
    const Delete    = 'delete';

    // Public Methods
    // =========================================================================

    public static function getFrieldly($handles)
    {
        $array = array();
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
        return FeedMeDuplicate::contains($feedData['duplicateHandle'], FeedMeDuplicate::Add, $only);
    }

    public static function isUpdate($feedData, $only = false)
    {
        return FeedMeDuplicate::contains($feedData['duplicateHandle'], FeedMeDuplicate::Update, $only);
    }

    public static function isDisable($feedData, $only = false)
    {
        return FeedMeDuplicate::contains($feedData['duplicateHandle'], FeedMeDuplicate::Disable, $only);
    }

    public static function isDelete($feedData, $only = false)
    {
        return FeedMeDuplicate::contains($feedData['duplicateHandle'], FeedMeDuplicate::Delete, $only);
    }
}
