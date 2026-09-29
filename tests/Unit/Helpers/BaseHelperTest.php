<?php

namespace craft\feedme\tests\Unit\Helpers;

use craft\feedme\helpers\BaseHelper;
use craft\feedme\tests\UnitTestCase;
use craft\fields\data\ColorData;

class BaseHelperTest extends UnitTestCase
{
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

        // Array input is a distinct branch that returns void/null, not false like every other
        // unrecognized value.
        $this->assertNull(BaseHelper::parseBoolean(['yes']));
    }
}
