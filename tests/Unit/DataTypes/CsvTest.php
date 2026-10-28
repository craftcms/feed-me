<?php

namespace craft\feedme\tests\Unit\DataTypes;

use craft\feedme\datatypes\Csv;
use craft\feedme\events\FeedDataEvent;
use craft\feedme\models\FeedModel;
use craft\feedme\services\DataTypes;
use yii\base\Event;

beforeEach(function() {
    $this->fetchResponseData = null;

    /**
     * DataTypes::getRawData() returns this event's `response` immediately if it's set, before
     * ever touching the filesystem or network.
     */
    $this->supplyRawData = function(FeedDataEvent $event) {
        $event->response = ['success' => true, 'data' => $this->fetchResponseData];
    };

    Event::on(DataTypes::class, DataTypes::EVENT_BEFORE_FETCH_FEED, $this->supplyRawData);
});

afterEach(function() {
    Event::off(DataTypes::class, DataTypes::EVENT_BEFORE_FETCH_FEED, $this->supplyRawData);
});

function fetchCsv(string $csv, array $settingsOverrides = []): array
{
    test()->fetchResponseData = $csv;

    return (new Csv())->getFeed('https://example.com/feed.csv', new FeedModel(array_merge(['id' => 1], $settingsOverrides)));
}

it('parses rows with header row', function() {
    $result = fetchCsv("id,title\n1,Foo\n2,Bar\n");

    expect($result['success'])->toBeTrue();
    expect($result['data'])->toBe([
        ['id' => '1', 'title' => 'Foo'],
        ['id' => '2', 'title' => 'Bar'],
    ]);
});

test('blank heading gets positional fallback name', function() {
    $result = fetchCsv("id,,title\n1,x,Foo\n");

    expect($result['data'][0])->toBe(['id' => '1', 'blank_heading_2' => 'x', 'title' => 'Foo']);
});

test('numeric header coerces to int array key', function() {
    // PHP itself coerces a numeric-string array key to int - a header column literally named
    // "1" doesn't stay a string key, which can desync from code (e.g.
    // DataTypes::findPrimaryElement()) that uses a `'0'` key as its "is this a list" test.
    $result = fetchCsv("id,1,name\n5,x,Bob\n");

    expect(array_keys($result['data'][0]))->toBe(['id', 1, 'name']);
});

test('blank rows are dropped', function() {
    $result = fetchCsv("id,title\n1,Foo\n,\n2,Bar\n");

    expect($result['data'])->toHaveCount(2);
    expect($result['data'][0]['id'])->toBe('1');
    expect($result['data'][1]['id'])->toBe('2');
});

test('failed fetch returns graceful error', function() {
    Event::off(DataTypes::class, DataTypes::EVENT_BEFORE_FETCH_FEED, $this->supplyRawData);
    $handler = function(FeedDataEvent $event) {
        $event->response = ['success' => false, 'error' => 'boom'];
    };
    Event::on(DataTypes::class, DataTypes::EVENT_BEFORE_FETCH_FEED, $handler);

    try {
        $result = (new Csv())->getFeed('https://example.com/feed.csv', new FeedModel(['id' => 1]));
    } finally {
        Event::off(DataTypes::class, DataTypes::EVENT_BEFORE_FETCH_FEED, $handler);
        Event::on(DataTypes::class, DataTypes::EVENT_BEFORE_FETCH_FEED, $this->supplyRawData);
    }

    expect($result['success'])->toBeFalse();
    expect($result['error'])->toContain('boom');
});
