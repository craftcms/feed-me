<?php
namespace verbb\feedme\helpers;

use verbb\feedme\FeedMe;

use craft\helpers\DateTimeHelper;

use DateTime;
use Carbon\Carbon;
use Cake\Utility\Hash;

class DateHelper
{
    // Public Methods
    // =========================================================================

    public static function parseString($value, $formatting = 'auto')
    {
        // Check for null or empty strings
        if ($value === null || $value === '' || $value === '0') {
            return null;
        }

        // Was this a date/time-picker?
        if (is_array($value)) {
            $dateIndex = Hash::get($value, 'date');
            $timeIndex = Hash::get($value, 'time');

            if (!$dateIndex || !$timeIndex) {
                return null;
            }

            return DateTimeHelper::toDateTime($value);
        }

        // Check if provided as a timestamp
        if (DateTimeHelper::isValidTimeStamp($value)) {
            $date = null;

            // Check if provided as milliseconds first
            if (strlen((int)$value) === 13) {
                $date = Carbon::createFromTimestampMs($value);
            }

            // Then, check if in seconds
            if (strlen((int)$value) === 10) {
                $date = Carbon::createFromTimestamp($value);
            }

            if ($date) {
                $dateTimeString = $date->toDateTimeString();

                return DateTimeHelper::toDateTime($dateTimeString, true, false);
            }
        }

        try {
            $date = null;

            // Because US-based dates can be unpredictable, we need to be able to handle them
            // Typically Carbon will see dates formatted with slashes are American, but thats often not the case
            if ($formatting === 'auto') {
                $date = Carbon::parse($value);
            } else {
                $date = str_replace(['/', '.'], '-', $value);

                if ($formatting === 'america') {
                    preg_match('/([0-9]{1,2})\/([0-9]{1,2})\/([0-9]{4})/', $value, $matches);

                    $month = Hash::get($matches, '1');
                    $day = Hash::get($matches, '2');
                    $year = Hash::get($matches, '3');
                    $time = explode(' ', $value);

                    $value = $year . '-' . $month . '-' . $day . ' ' . Hash::get($time, '1');
                }

                if ($formatting === 'asia') {
                    preg_match('/([0-9]{4})\/([0-9]{1,2})\/([0-9]{1,2})/', $value, $matches);

                    $month = Hash::get($matches, '2');
                    $day = Hash::get($matches, '3');
                    $year = Hash::get($matches, '1');
                    $time = explode(' ', $value);

                    $value = $year . '-' . $month . '-' . $day . ' ' . Hash::get($time, '1');
                }

                if ($formatting === 'world') {
                    preg_match('/([0-9]{1,2})\/([0-9]{1,2})\/([0-9]{4})/', $value, $matches);

                    $month = Hash::get($matches, '2');
                    $day = Hash::get($matches, '1');
                    $year = Hash::get($matches, '3');
                    $time = explode(' ', $value);

                    $value = $year . '-' . $month . '-' . $day . ' ' . Hash::get($time, '1');
                }

                if ($formatting === 'yyyymmdd') {
                    preg_match('/([0-9]{4})([0-9]{2})([0-9]{2})/', $value, $matches);

                    $month = Hash::get($matches, '2');
                    $day = Hash::get($matches, '3');
                    $year = Hash::get($matches, '1');
                    $time = explode(' ', $value);

                    $value = $year . '-' . $month . '-' . $day . ' ' . Hash::get($time, '1');
                }

                if ($formatting === 'yyyyddmm') {
                    preg_match('/([0-9]{4})([0-9]{2})([0-9]{2})/', $value, $matches);

                    $month = Hash::get($matches, '3');
                    $day = Hash::get($matches, '2');
                    $year = Hash::get($matches, '1');
                    $time = explode(' ', $value);

                    $value = $year . '-' . $month . '-' . $day . ' ' . Hash::get($time, '1');
                }

                $date = Carbon::parse($value);
            }

            if ($date) {
                $dateTimeString = $date->toDateTimeString();

                return DateTimeHelper::toDateTime($dateTimeString, true, false);
            }
        } catch (\Exception $e) {
            FeedMe::error('Date parse error: `{value}` - `{e}`.', ['value' => $value, 'e' => $e->getMessage()]);
        }
    }

    // public static function isValidTimeStamp($string)
    // {
    //     try {
    //         new DateTime('@' . $string);
    //     } catch(\Exception $e) {
    //         return false;
    //     }

    //     return true;
    // }
}
