<?php
namespace Craft;

use Carbon\Carbon;
use Cake\Utility\Hash as Hash;

class FeedMeDateHelper
{
    // Public Methods
    // =========================================================================

    public static function parseString($date, $formatting = 'auto', $useTimezone = null)
    {
        $parsedDate = null;

        // Check for empty-string dates
        if ($date === '' || !$date) {
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
                $timestampInt = (int)$date;

                // Check for millisecond timestamp - Carbon doesn't really support these, and the above will fail
                if (strlen($date) >= 13) {
                    $timestampInt = $timestampInt / 1000;
                }

                $dt = Carbon::createFromTimestamp($timestampInt);

            } else {
                // Because US-based dates can be unpredictable, we need to be able to handle them
                // Typically Carbon will see dates formatted with slashes are American, but thats often not the case
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
            }

            if ($dt) {
                $dateTimeString = $dt->toDateTimeString();

                // https://github.com/verbb/feed-me/issues/388
                // For some reason postDate (and possibly expiryDate) values are sometimes +1 hour than they should be, but it doesn't happen consistently
                // Debugging the date helper it is when createFromString is used on the postDate/expiryDate value without a timezone.
                // Problem is when using craft()->timezone on other date fields (not Craft defaults) this causes -1 hour differences. WTF.
                if ($useTimezone) {
                    $parsedDate = DateTime::createFromString($dateTimeString, craft()->timezone);
                }
                else {
                    $parsedDate = DateTime::createFromString($dateTimeString, null, true);
                }
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
