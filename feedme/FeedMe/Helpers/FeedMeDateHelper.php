<?php
namespace Craft;

use Carbon\Carbon;
use Cake\Utility\Hash as Hash;

class FeedMeDateHelper
{
    // Public Methods
    // =========================================================================

    public static function parseString($date)
    {
        $parsedDate = null;

        // Check for empty-string dates
        if ($date === '') {
            return $parsedDate;
        }

        if (is_array($date)) {
            $dateString = Hash::get($date, 'date');

            if ($dateString) {
                return $date;
            } else {
                return $parsedDate;
            }
        }

        try {
            $timestamp = FeedMeDateHelper::isTimestamp($date);

            if ($timestamp) {
                $date = '@' . $date;
            }

            $dt = Carbon::parse($date);

            if ($dt) {
                $dateTimeString = $dt->toDateTimeString();

                $parsedDate = DateTime::createFromString($dateTimeString, null, true);
            }
        } catch (\Exception $e) {
            FeedMePlugin::log('Date parse error: ' . $date . ' - ' . $e->getMessage(), LogLevel::Error, true);
        }

        return $parsedDate;
    }

    public static function getDateTimeString($date)
    {
        if (is_array($date) && (isset($date['date']) || isset($date['time']))) {
            $dateObject = DateTime::createFromString($date);
        } else {
            $dateObject = DateTime::createFromString($date, craft()->timezone);
        }

        return $dateObject->format('Y-m-d H:i:s');
    }

    public static function isTimestamp($string)
    {
        try {
            new DateTime('@' . $string);
        } catch(\Exception $e) {
            return false;
        }

        return true;
    }

}
