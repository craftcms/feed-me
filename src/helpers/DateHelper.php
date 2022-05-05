<?php

namespace craft\feedme\helpers;

use Cake\Utility\Hash;
use Carbon\Carbon;
use Craft;
use craft\feedme\Plugin;
use craft\helpers\DateTimeHelper;
use DateTime;
use Exception;

class DateHelper
{
    // Public Methods
    // =========================================================================

    /**
     * @param $value
     * @param string $formatting
     * @return DateTime|bool|array|Carbon|string|null
     * @throws \yii\base\InvalidConfigException
     */
    public static function parseString($value, string $formatting = 'auto'): DateTime|bool|array|Carbon|string|null
    {
        // Check for null or empty strings
        if ($value === null || $value === '' || $value === '0') {
            return [];
        }

        // Was this a date/time-picker?
        if (is_array($value)) {
            $dateIndex = Hash::get($value, 'date');
            $timeIndex = Hash::get($value, 'time');

            // It's okay to return this if it was an empty date-time array. This will often be the default
            // value for an empty stringed date value in the feed. At this point, we want to retain the
            // empty value in the feed to overwrite the date value on the element.
            if (!$dateIndex || !$timeIndex) {
                return '';
            }

            return DateTimeHelper::toDateTime($value);
        }

        try {
            $date = null;

            // Because US-based dates can be unpredictable, we need to be able to handle them
            // Typically Carbon will see dates formatted with slashes are American, but that's often not the case
            if ($formatting === 'auto') {
                $date = Carbon::parse($value);
            } elseif ($formatting === 'milliseconds') {
                $date = Carbon::createFromTimestampMs($value);
            } elseif ($formatting === 'seconds') {
                $date = Carbon::createFromTimestamp($value);
            } else {
                if ($formatting === 'america') {
                    preg_match('/([0-9]{1,2})\/([0-9]{1,2})\/([0-9]{4})/', $value, $matches);

                    $month = Hash::get($matches, '1');
                    $day = Hash::get($matches, '2');
                    $year = Hash::get($matches, '3');
                    $time = explode(' ', $value);

                    $value = $year . '-' . $month . '-' . $day . ' ' . Hash::get($time, '1');
                }

                if ($formatting === 'america-short') {
                    preg_match('/([0-9]{1,2})\/([0-9]{1,2})\/([0-9]{2})/', $value, $matches);

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

                if ($formatting === 'asia-short') {
                    preg_match('/([0-9]{2})\/([0-9]{1,2})\/([0-9]{1,2})/', $value, $matches);

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

                if ($formatting === 'world-short') {
                    preg_match('/([0-9]{1,2})\/([0-9]{1,2})\/([0-9]{2})/', $value, $matches);

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

                if ($formatting === 'yymmdd') {
                    preg_match('/([0-9]{2})([0-9]{2})([0-9]{2})/', $value, $matches);

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

                if ($formatting === 'yyddmm') {
                    preg_match('/([0-9]{2})([0-9]{2})([0-9]{2})/', $value, $matches);

                    $month = Hash::get($matches, '3');
                    $day = Hash::get($matches, '2');
                    $year = Hash::get($matches, '1');
                    $time = explode(' ', $value);

                    $value = $year . '-' . $month . '-' . $day . ' ' . Hash::get($time, '1');
                }

                $date = Carbon::parse($value);
            }

            if ($date) {
                return $date;
            }
        } catch (Exception $e) {
            Plugin::error('Date parse error: `{value}` - `{e}`.', ['value' => $value, 'e' => $e->getMessage()]);
            Craft::$app->getErrorHandler()->logException($e);
        }

        return null;
    }

    /**
     * @param $value
     * @return DateTime|false|null
     */
    public static function parseTimeString($value): DateTime|bool|null
    {
        try {
            return DateTimeHelper::toDateTime($value) ?: null;
        } catch (Exception $e) {
            Plugin::error('Time parse error: `{value}` - `{e}`.', ['value' => $value, 'e' => $e->getMessage()]);
            Craft::$app->getErrorHandler()->logException($e);
        }

        return null;
    }
}
