<?php

namespace craft\feedme\tests\Feature\Helpers;

use craft\feedme\helpers\BaseHelper;
use craft\feedme\helpers\DateHelper;
use craft\feedme\tests\TestCase;

class HelpersTest extends TestCase
{
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
