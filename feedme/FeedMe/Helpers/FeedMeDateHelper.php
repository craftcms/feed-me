<?php
namespace Craft;

use Carbon\Carbon;
use Cake\Utility\Hash as Hash;

class FeedMeDateHelper
{
    // Public Methods
    // =========================================================================

    public static function parseString($date, $formatting = 'auto')
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
            $dt = null;
            $timestamp = FeedMeDateHelper::isTimestamp($date);

            if ($timestamp) {
                $date = '@' . $date;
            }

            // Because US-based dates can be unpredictable, we need to be able to handle them
            // Typically Carboh will see dates formatted with slashes are American, but thats often not the case
            if ($formatting === 'auto') {
                $dt = Carbon::parse($date);
            } else {
                $date = str_replace(array('/', '.'), '-', $date);

                if ($formatting === 'america') {
                    preg_match('/([0-9]{1,2})-([0-9]{1,2})-([0-9]{4})/', $date, $matches);

                    $month = Hash::get($matches, '1');
                    $day = Hash::get($matches, '2');
                    $year = Hash::get($matches, '3');
                    $time = explode(' ', $date);

                    $date = $year . '-' . $month . '-' . $day . ' ' . Hash::get($time, '1');
                }

                $dt = Carbon::parse($date);
            }

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
