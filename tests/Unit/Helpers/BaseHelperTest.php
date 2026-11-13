<?php

namespace craft\feedme\tests\Unit\Helpers;

use craft\feedme\helpers\BaseHelper;
use craft\fields\data\ColorData;

it('parses color', function() {
    expect(BaseHelper::parseColor('#ff0000'))->toBe('#ff0000');

    // Adds a missing leading '#'.
    expect(BaseHelper::parseColor('ff0000'))->toBe('#ff0000');

    // Expands 3-character shorthand to 6.
    expect(BaseHelper::parseColor('fff'))->toBe('#ffffff');

    expect(BaseHelper::parseColor(''))->toBeNull();
    expect(BaseHelper::parseColor('#'))->toBeNull();

    // An already-normalized ColorData instance passes straight through.
    $color = new ColorData('#ff0000');
    expect(BaseHelper::parseColor($color))->toBe($color);
});

it('gets browser name', function() {
    expect(BaseHelper::getBrowserName('Mozilla/5.0 (X11; Linux x86_64; rv:109.0) Gecko/20100101 Firefox/117.0'))->toBe('Firefox');
    expect(BaseHelper::getBrowserName('curl/7.64.1'))->toBe('Other');

    // `getBrowserName()` uses `strpos($userAgent, 'Opera')` as a boolean check, so a match at
    // position 0 (falsy in PHP) is wrongly treated as "not found" and falls through to the
    // next check instead of returning 'Opera'. Documenting the current (buggy) behavior.
    expect(BaseHelper::getBrowserName('Opera Chrome/1.0'))->toBe('Chrome');
});

test('boolean helper', function() {
    expect(BaseHelper::parseBoolean(1))->toBeTrue();
    expect(BaseHelper::parseBoolean(true))->toBeTrue();
    expect(BaseHelper::parseBoolean('1'))->toBeTrue();
    expect(BaseHelper::parseBoolean('true'))->toBeTrue();
    expect(BaseHelper::parseBoolean('yes'))->toBeTrue();
    expect(BaseHelper::parseBoolean('on'))->toBeTrue();
    expect(BaseHelper::parseBoolean('enabled'))->toBeTrue();
    expect(BaseHelper::parseBoolean('live'))->toBeTrue();

    expect(BaseHelper::parseBoolean(0))->toBeFalse();
    expect(BaseHelper::parseBoolean(false))->toBeFalse();
    expect(BaseHelper::parseBoolean('0'))->toBeFalse();
    expect(BaseHelper::parseBoolean('false'))->toBeFalse();
    expect(BaseHelper::parseBoolean('no'))->toBeFalse();
    expect(BaseHelper::parseBoolean('off'))->toBeFalse();
    expect(BaseHelper::parseBoolean('closed'))->toBeFalse();
    expect(BaseHelper::parseBoolean('disabled'))->toBeFalse();

    expect(BaseHelper::parseBoolean(2))->toBeFalse();

    // Array input is a distinct branch that returns void/null, not false like every other
    // unrecognized value.
    expect(BaseHelper::parseBoolean(['yes']))->toBeNull();
});
