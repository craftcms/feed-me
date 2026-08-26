<?php

namespace craft\feedme\tests\Unit\Helpers;

use craft\feedme\helpers\BaseHelper;
use craft\feedme\helpers\DateHelper;
use craft\feedme\tests\UnitTestCase;
use craft\fields\data\ColorData;
use PHPUnit\Framework\Attributes\DataProvider;

class HelpersTest extends UnitTestCase
{
    public static function regionalDateFormatProvider(): array
    {
        return [
            'america' => ['03/15/2018 10:00:00', 'america'],
            'america-short' => ['03/15/18 10:00:00', 'america-short'],
            'asia' => ['2018/03/15 10:00:00', 'asia'],
            'world' => ['15/03/2018 10:00:00', 'world'],
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

    public function testParseColor(): void
    {
        $this->assertSame('#ff0000', BaseHelper::parseColor('#ff0000'));

        // Adds a missing leading '#'.
        $this->assertSame('#ff0000', BaseHelper::parseColor('ff0000'));

        // Expands 3-character shorthand to 6.
        $this->assertSame('#ffffff', BaseHelper::parseColor('fff'));

        $this->assertNull(BaseHelper::parseColor(''));
        $this->assertNull(BaseHelper::parseColor('#'));

        // An already-normalized ColorData instance passes straight through.
        $color = new ColorData('#ff0000');
        $this->assertSame($color, BaseHelper::parseColor($color));
    }

    public function testGetBrowserName(): void
    {
        $this->assertSame('Firefox', BaseHelper::getBrowserName('Mozilla/5.0 (X11; Linux x86_64; rv:109.0) Gecko/20100101 Firefox/117.0'));
        $this->assertSame('Other', BaseHelper::getBrowserName('curl/7.64.1'));

        // `getBrowserName()` uses `strpos($userAgent, 'Opera')` as a boolean check, so a match at
        // position 0 (falsy in PHP) is wrongly treated as "not found" and falls through to the
        // next check instead of returning 'Opera'. Documenting the current (buggy) behavior.
        $this->assertSame('Chrome', BaseHelper::getBrowserName('Opera Chrome/1.0'));
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
    }

    public function testBooleanHelper(): void
    {
        $this->assertTrue(BaseHelper::parseBoolean(1));
        $this->assertTrue(BaseHelper::parseBoolean(true));
        $this->assertTrue(BaseHelper::parseBoolean('1'));
        $this->assertTrue(BaseHelper::parseBoolean('true'));
        $this->assertTrue(BaseHelper::parseBoolean('yes'));
        $this->assertTrue(BaseHelper::parseBoolean('on'));
        $this->assertTrue(BaseHelper::parseBoolean('enabled'));
        $this->assertTrue(BaseHelper::parseBoolean('live'));

        $this->assertFalse(BaseHelper::parseBoolean(0));
        $this->assertFalse(BaseHelper::parseBoolean(false));
        $this->assertFalse(BaseHelper::parseBoolean('0'));
        $this->assertFalse(BaseHelper::parseBoolean('false'));
        $this->assertFalse(BaseHelper::parseBoolean('no'));
        $this->assertFalse(BaseHelper::parseBoolean('off'));
        $this->assertFalse(BaseHelper::parseBoolean('closed'));
        $this->assertFalse(BaseHelper::parseBoolean('disabled'));

        $this->assertFalse(BaseHelper::parseBoolean(2));
    }
}
