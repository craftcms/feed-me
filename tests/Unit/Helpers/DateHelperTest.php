<?php

namespace craft\feedme\tests\Unit\Helpers;

use craft\feedme\helpers\DateHelper;

test('date helper regional formats', function(string $value, string $format) {
    // month/day are always assumed in that order regardless of region - only the resulting
    // month/day/year positions taken from the string differ.
    $date = DateHelper::parseString($value, $format);
    expect($date->format('Y-m-d H:i:s'))->toEqual('2018-03-15 10:00:00');
})->with([
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
]);

test('date helper timestamps', function() {
    // Convert to UTC explicitly before asserting - `Carbon::createFromTimestamp()` /
    // `createFromTimestampMs()` render using PHP's ambient default timezone, which Craft's
    // app boot silently sets from `system.timeZone` project config (not necessarily UTC,
    // regardless of what Pest.php sets at bootstrap). Asserting in UTC makes this test
    // depend only on the fixed input timestamp, not on that ambient state.
    $date = DateHelper::parseString(1512090030, 'seconds');
    expect($date->setTimezone('UTC')->format('Y-m-d H:i:s'))->toEqual('2017-12-01 01:00:30');

    $date = DateHelper::parseString(1512090030615, 'milliseconds');
    expect($date->setTimezone('UTC')->format('Y-m-d H:i:s'))->toEqual('2017-12-01 01:00:30');
});

test('date helper date time picker', function() {
    // A fully-populated date/time-picker array gets converted to a real date. (The exact
    // time isn't asserted here, since parsing it goes through locale-specific formatting.)
    $date = DateHelper::parseString(['date' => '2018-03-15', 'time' => '10:00 AM']);
    expect($date->format('Y-m-d'))->toEqual('2018-03-15');

    // A partially-empty date/time-picker array is treated as an explicit "clear the date"
    // value, rather than falling back to a default.
    expect(DateHelper::parseString(['date' => '', 'time' => '10:00 AM']))->toBe('');
    expect(DateHelper::parseString(['date' => '2018-03-15', 'time' => '']))->toBe('');
});

it('parses time string', function() {
    $date = DateHelper::parseTimeString('10:00 AM');
    expect($date->format('H:i:s'))->toEqual('10:00:00');

    expect(DateHelper::parseTimeString(''))->toBeNull();
});

test('date helper', function() {
    $value = '2018-01-01 10:00:00';
    $date = DateHelper::parseString($value);
    expect($date->format('Y-m-d H:i:s'))->toEqual('2018-01-01 10:00:00');

    $value = '2018-01-01T23:28:56.782Z';
    $date = DateHelper::parseString($value);
    expect($date->format('Y-m-d H:i:s'))->toEqual('2018-01-01 23:28:56');

    $value = 'Tue, 16 Jul 2013 17:14:36 +0000';
    $date = DateHelper::parseString($value);
    expect($date->format('Y-m-d H:i:s'))->toEqual('2013-07-16 17:14:36');

    $value = 'Jan 01 2018 00:00:00 GMT+0000';
    $date = DateHelper::parseString($value);
    expect($date->format('Y-m-d H:i:s'))->toEqual('2018-01-01 00:00:00');

    $value = '';
    $date = DateHelper::parseString($value);
    expect($date)->toBeEmpty();

    $value = null;
    $date = DateHelper::parseString($value);
    expect($date)->toBeEmpty();

    // '0' is treated the same as null/'' - a common "empty" sentinel in feed data.
    $value = '0';
    $date = DateHelper::parseString($value);
    expect($date)->toBeEmpty();
});
