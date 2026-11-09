<?php

namespace craft\feedme\tests\Unit\DataTypes;

use craft\feedme\datatypes\Atom;
use craft\feedme\datatypes\Rss;
use craft\feedme\datatypes\Xml;
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

function fetchXml(string $xml, array $settingsOverrides = [], string $class = Xml::class): array
{
    test()->fetchResponseData = $xml;

    return (new $class())->getFeed('https://example.com/feed.xml', new FeedModel(array_merge(['id' => 1], $settingsOverrides)));
}

test('multiple sibling elements normalize to list', function() {
    $xml = '<channel><item><title>A</title></item><item><title>B</title></item></channel>';

    $result = fetchXml($xml, ['primaryElement' => 'item']);

    expect($result['success'])->toBeTrue();
    expect($result['data'])->toBe([['title' => 'A'], ['title' => 'B']]);
});

test('single nested element does not get list wrapper', function() {
    // findPrimaryElement() normalizes the *top-level* primary element to a list either way
    // (one <item> or many), but nested repeated elements aren't normalized by anything - a
    // single <category> stays a scalar, while two or more become a numeric list. Both shapes
    // below are the "correct" current behavior, just inconsistent with each other.
    $single = fetchXml(
        '<channel><item><title>A</title><category>News</category></item></channel>',
        ['primaryElement' => 'item'],
    );
    expect($single['data'][0]['category'])->toBe('News');

    $multiple = fetchXml(
        '<channel><item><title>A</title><category>News</category><category>World</category></item></channel>',
        ['primaryElement' => 'item'],
    );
    expect($multiple['data'][0]['category'])->toBe(['News', 'World']);
});

test('cdata merges as plain text', function() {
    $cdata = fetchXml('<item><title><![CDATA[Foo & Bar]]></title></item>', ['primaryElement' => 'item']);
    $plain = fetchXml('<item><title>Foo &amp; Bar</title></item>', ['primaryElement' => 'item']);

    expect($cdata['data'][0]['title'])->toBe('Foo & Bar');
    expect($cdata['data'][0]['title'])->toBe($plain['data'][0]['title']);
});

test('attributes captured with at prefix and mixed content under at key', function() {
    $result = fetchXml('<item><price currency="USD">9.99</price></item>', ['primaryElement' => 'item']);

    expect($result['data'][0]['price'])->toBe(['@currency' => 'USD', '@' => '9.99']);
});

test('self closing element parses to empty string', function() {
    $result = fetchXml('<item><link/></item>', ['primaryElement' => 'item']);

    expect($result['data'][0]['link'])->toBe('');
});

test('malformed xml returns graceful error', function() {
    $result = fetchXml('<item><title>Unclosed</item>');

    expect($result['success'])->toBeFalse();
    expect($result['error'])->toContain('Invalid XML');
});

test('primary element not found returns success with false data', function() {
    $result = fetchXml('<root><foo>bar</foo></root>', ['primaryElement' => 'nonexistent']);

    // Callers must check truthiness of `data`, not just `success` - this is a "successful"
    // response whose data is `false`.
    expect($result['success'])->toBeTrue();
    expect($result['data'])->toBeFalse();
});

test('rss and atom delegate to xml parsing with their own name', function() {
    $xml = '<feed><entry><title>A</title></entry><entry><title>B</title></entry></feed>';

    $rss = fetchXml($xml, ['primaryElement' => 'entry'], Rss::class);
    $atom = fetchXml($xml, ['primaryElement' => 'entry'], Atom::class);

    expect($rss['data'])->toBe([['title' => 'A'], ['title' => 'B']]);
    expect($atom['data'])->toBe($rss['data']);
    expect((new Rss())->getName())->toBe('RSS');
    expect((new Atom())->getName())->toBe('ATOM');
});
