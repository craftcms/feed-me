<?php

namespace craft\feedme\tests\Unit\DataTypes;

use craft\feedme\datatypes\GoogleSheet;
use craft\feedme\events\FeedDataEvent;
use craft\feedme\models\FeedModel;
use craft\feedme\services\DataTypes;
use yii\base\Event;

beforeEach(function() {
    $this->fetchResponseData = null;

    $this->supplyRawData = function(FeedDataEvent $event) {
        $event->response = ['success' => true, 'data' => $this->fetchResponseData];
    };

    Event::on(DataTypes::class, DataTypes::EVENT_BEFORE_FETCH_FEED, $this->supplyRawData);
});

afterEach(function() {
    Event::off(DataTypes::class, DataTypes::EVENT_BEFORE_FETCH_FEED, $this->supplyRawData);
});

function fetchGoogleSheet(mixed $rawBody): array
{
    test()->fetchResponseData = is_string($rawBody) ? $rawBody : json_encode($rawBody);

    return (new GoogleSheet())->getFeed('https://example.com/feed.json', new FeedModel(['id' => 1]));
}

it('parses rows with header row', function() {
    $result = fetchGoogleSheet(['values' => [['id', 'title'], ['1', 'Foo']]]);

    expect($result['success'])->toBeTrue();
    expect($result['data'])->toBe([['id' => '1', 'title' => 'Foo']]);
});

test('blank heading gets positional fallback name', function() {
    $result = fetchGoogleSheet(['values' => [['id', '', 'title'], ['1', 'x', 'Foo']]]);

    expect($result['data'][0])->toBe(['id' => '1', 'blank_heading_2' => 'x', 'title' => 'Foo']);
});

test('ragged row longer than header returns graceful error', function() {
    $result = fetchGoogleSheet(['values' => [['id', 'title'], ['1', 'Foo', 'extra']]]);

    expect($result['success'])->toBeFalse();
    expect($result['error'])->not->toBeEmpty();
});

test('ragged row shorter than header omits missing keys', function() {
    $result = fetchGoogleSheet(['values' => [['id', 'title', 'extra'], ['1', 'Foo']]]);

    expect($result['data'][0])->toBe(['id' => '1', 'title' => 'Foo']);
});

test('blank rows are not filtered', function() {
    // Unlike Csv, GoogleSheet has no equivalent of _isArrayEmpty() - a fully-blank row survives.
    $result = fetchGoogleSheet(['values' => [['id', 'title'], ['', '']]]);

    expect($result['data'])->toHaveCount(1);
    expect($result['data'][0])->toBe(['id' => '', 'title' => '']);
});

test('missing values key returns graceful error', function() {
    $result = fetchGoogleSheet(['rows' => []]);

    expect($result['success'])->toBeFalse();
    expect($result['error'])->not->toBeEmpty();
});

test('empty response body returns graceful error', function() {
    $result = fetchGoogleSheet('');

    expect($result['success'])->toBeFalse();
    expect($result['error'])->not->toBeEmpty();
});
