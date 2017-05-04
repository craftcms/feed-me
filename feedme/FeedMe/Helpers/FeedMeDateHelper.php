<?php
namespace Craft;

use Carbon\Carbon;

class FeedMeDateHelper
{
    // Public Methods
    // =========================================================================

    public static function parseString($date)
    {
        $parsedDate = null;

        if (is_array($date)) {
            return $date;
        }

        try {
            $timestamp = FeedMeDateHelper::isTimestamp($date);

            if ($timestamp) {
                $date = '@' . $date;
            }

            $dt = Carbon::parse($date);

            if ($dt) {
                $dateTimeString = $dt->toDateTimeString();

                $parsedDate = DateTime::createFromString($dateTimeString, craft()->timezone);
            }
        } catch (\Exception $e) {
            FeedMePlugin::log('Date parse error: ' . $date . ' - ' . $e->getMessage(), LogLevel::Error, true);
        }

        return $parsedDate;
    }

    public static function isTimestamp($string)
    {
        try {
            new DateTime('@' . $string);
        } catch(\Exception $e) {
            FeedMePlugin::log('Date parse error: ' . $string . ' - ' . $e->getMessage(), LogLevel::Error, true);

            return false;
        }

        return true;
    }

}