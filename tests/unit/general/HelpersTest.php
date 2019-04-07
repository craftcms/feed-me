<?php

use verbb\feedme\helpers\BaseHelper;
use verbb\feedme\helpers\DateHelper;

class HelpersTest extends \Codeception\Test\Unit
{
    protected $tester;

    protected function _before()
    {
    }

    protected function _after()
    {
    }

    public function testDateHelper()
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

        $value = '1210193026';
        $date = DateHelper::parseString($value);
        $this->assertEquals('2008-05-07 13:43:46', $date->format('Y-m-d H:i:s'));

        $value = '1512090030615';
        $date = DateHelper::parseString($value);
        $this->assertEquals('2017-11-30 17:00:30', $date->format('Y-m-d H:i:s'));

        $value = '';
        $date = DateHelper::parseString($value);
        $this->assertNull($date);

        $value = null;
        $date = DateHelper::parseString($value);
        $this->assertNull($date);

        // From datepicker field (default normally)
        // $value = ['date' => '1/17/2018', 'timezone' => 'Australia\/Melbourne', 'time' => '3:00 AM'];
        // $date = DateHelper::parseString($value);
        // $this->assertEquals('2018-01-17 14:00:00', $date->format('Y-m-d H:i:s'));

        // $value = ['date' => '', 'timezone' => 'Australia\/Melbourne', 'time' => ''];
        // $date = DateHelper::parseString($value);
        // $this->assertNull($date);

        // Check against specific formatting
        // $value = '12/25/2018 10:00:00';
        // $date = DateHelper::parseString($value, 'america');
        // $this->assertEquals('2018-12-25 10:00:00', $date->format('Y-m-d H:i:s'));

        // $value = '2018/12/25 10:00:00';
        // $date = DateHelper::parseString($value, 'asia');
        // $this->assertEquals('2018-12-25 10:00:00', $date->format('Y-m-d H:i:s'));

        // $value = '25/12/2018 10:00:00';
        // $date = DateHelper::parseString($value, 'world');
        // $this->assertEquals('2018-12-25 10:00:00', $date->format('Y-m-d H:i:s'));

    }

    public function testBooleanHelper()
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
