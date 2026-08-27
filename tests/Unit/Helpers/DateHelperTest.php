<?php

namespace craft\feedme\tests\Unit\Helpers;

use craft\feedme\helpers\DateHelper;
use craft\feedme\tests\UnitTestCase;
use PHPUnit\Framework\Attributes\DataProvider;

class DateHelperTest extends UnitTestCase
{
    public static function regionalDateFormatProvider(): array
    {
        return [
            'america' => ['03/15/2018 10:00:00', 'america'],
            'america-short' => ['03/15/18 10:00:00', 'america-short'],
            'asia' => ['2018/03/15 10:00:00', 'asia'],
            'asia-short' => ['18/03/15 10:00:00', 'asia-short'],
            'world' => ['15/03/2018 10:00:00', 'world'],
            'world-short' => ['15/03/18 10:00:00', 'world-short'],
            'yyyymmdd' => ['20180315 10:00:00', 'yyyymmdd'],
            'yymmdd' => ['180315 10:00:00', 'yymmdd'],
            // Digit groups are year+day+month for these formats (note the digit order differs
            // from the input used for the yyyymmdd/yymmdd cases above).
            'yyyyddmm' => ['20181503 10:00:00', 'yyyyddmm'],
            'yyddmm' => ['181503 10:00:00', 'yyddmm'],
        ];
    }

    #[DataProvider('regionalDateFormatProvider')]
    public function testDateHelperRegionalFormats(string $value, string $format): void
    {
        // month/day are always assumed in that order regardless of region - only the resulting
        // month/day/year positions taken from the string differ.
        $date = DateHelper::parseString($value, $format);
        $this->assertEquals('2018-03-15 10:00:00', $date->format('Y-m-d H:i:s'));
    }

    public function testDateHelperTimestamps(): void
    {
        // These expected values are only correct under America/Los_Angeles (UTC-8) - Pest.php
        // sets date_default_timezone_set('UTC'), but booting Craft::$app reads system.timeZone
        // from project config, which defaults to America/Los_Angeles on a fresh install and
        // silently overrides it. If that default ever changes, these assertions need updating
        // too - they're not actually testing DateHelper against UTC.
        $date = DateHelper::parseString(1512090030, 'seconds');
        $this->assertEquals('2017-11-30 17:00:30', $date->format('Y-m-d H:i:s'));

        $date = DateHelper::parseString(1512090030615, 'milliseconds');
        $this->assertEquals('2017-11-30 17:00:30', $date->format('Y-m-d H:i:s'));
    }

    public function testDateHelperDateTimePicker(): void
    {
        // A fully-populated date/time-picker array gets converted to a real date. (The exact
        // time isn't asserted here, since parsing it goes through locale-specific formatting.)
        $date = DateHelper::parseString(['date' => '2018-03-15', 'time' => '10:00 AM']);
        $this->assertEquals('2018-03-15', $date->format('Y-m-d'));

        // A partially-empty date/time-picker array is treated as an explicit "clear the date"
        // value, rather than falling back to a default.
        $this->assertSame('', DateHelper::parseString(['date' => '', 'time' => '10:00 AM']));
        $this->assertSame('', DateHelper::parseString(['date' => '2018-03-15', 'time' => '']));
    }

    public function testParseTimeString(): void
    {
        $date = DateHelper::parseTimeString('10:00 AM');
        $this->assertEquals('10:00:00', $date->format('H:i:s'));

        $this->assertNull(DateHelper::parseTimeString(''));
    }

    public function testDateHelper(): void
    {
        $value = '2018-01-01 10:00:00';
        $date = DateHelper::parseString($value);
        $this->assertEquals('2018-01-01 10:00:00', $date->format('Y-m-d H:i:s'));

        $value = '2018-01-01T23:28:56.782Z';
        $date = DateHelper::parseString($value);
        $this->assertEquals('2018-01-01 23:28:56', $date->format('Y-m-d H:i:s'));

        $value = 'Tue, 16 Jul 2013 17:14:36 +0000';
        $date = DateHelper::parseString($value);
        $this->assertEquals('2013-07-16 17:14:36', $date->format('Y-m-d H:i:s'));

        $value = 'Jan 01 2018 00:00:00 GMT+0000';
        $date = DateHelper::parseString($value);
        $this->assertEquals('2018-01-01 00:00:00', $date->format('Y-m-d H:i:s'));

        $value = '';
        $date = DateHelper::parseString($value);
        $this->assertEmpty($date);

        $value = null;
        $date = DateHelper::parseString($value);
        $this->assertEmpty($date);

        // '0' is treated the same as null/'' - a common "empty" sentinel in feed data.
        $value = '0';
        $date = DateHelper::parseString($value);
        $this->assertEmpty($date);
    }
}
