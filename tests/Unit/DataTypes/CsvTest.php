<?php

namespace craft\feedme\tests\Unit\DataTypes;

use craft\feedme\datatypes\Csv;
use craft\feedme\events\FeedDataEvent;
use craft\feedme\models\FeedModel;
use craft\feedme\services\DataTypes;
use craft\feedme\tests\UnitTestCase;
use yii\base\Event;

class CsvTest extends UnitTestCase
{
    private ?string $fetchResponseData = null;

    protected function setUp(): void
    {
        parent::setUp();

        Event::on(DataTypes::class, DataTypes::EVENT_BEFORE_FETCH_FEED, [$this, 'supplyRawData']);
    }

    protected function tearDown(): void
    {
        Event::off(DataTypes::class, DataTypes::EVENT_BEFORE_FETCH_FEED, [$this, 'supplyRawData']);

        parent::tearDown();
    }

    /**
     * DataTypes::getRawData() returns this event's `response` immediately if it's set, before
     * ever touching the filesystem or network.
     */
    public function supplyRawData(FeedDataEvent $event): void
    {
        $event->response = ['success' => true, 'data' => $this->fetchResponseData];
    }

    private function fetch(string $csv, array $settingsOverrides = []): array
    {
        $this->fetchResponseData = $csv;

        return (new Csv())->getFeed('https://example.com/feed.csv', new FeedModel(array_merge(['id' => 1], $settingsOverrides)));
    }

    public function testParsesRowsWithHeaderRow(): void
    {
        $result = $this->fetch("id,title\n1,Foo\n2,Bar\n");

        $this->assertTrue($result['success']);
        $this->assertSame([
            ['id' => '1', 'title' => 'Foo'],
            ['id' => '2', 'title' => 'Bar'],
        ], $result['data']);
    }

    public function testBlankHeadingGetsPositionalFallbackName(): void
    {
        $result = $this->fetch("id,,title\n1,x,Foo\n");

        $this->assertSame(['id' => '1', 'blank_heading_2' => 'x', 'title' => 'Foo'], $result['data'][0]);
    }

    public function testNumericHeaderCoercesToIntArrayKey(): void
    {
        // PHP itself coerces a numeric-string array key to int - a header column literally named
        // "1" doesn't stay a string key, which can desync from code (e.g.
        // DataTypes::findPrimaryElement()) that uses a `'0'` key as its "is this a list" test.
        $result = $this->fetch("id,1,name\n5,x,Bob\n");

        $this->assertSame(['id', 1, 'name'], array_keys($result['data'][0]));
    }

    public function testBlankRowsAreDropped(): void
    {
        $result = $this->fetch("id,title\n1,Foo\n,\n2,Bar\n");

        $this->assertCount(2, $result['data']);
        $this->assertSame('1', $result['data'][0]['id']);
        $this->assertSame('2', $result['data'][1]['id']);
    }

    public function testFailedFetchReturnsGracefulError(): void
    {
        Event::off(DataTypes::class, DataTypes::EVENT_BEFORE_FETCH_FEED, [$this, 'supplyRawData']);
        $handler = function(FeedDataEvent $event) {
            $event->response = ['success' => false, 'error' => 'boom'];
        };
        Event::on(DataTypes::class, DataTypes::EVENT_BEFORE_FETCH_FEED, $handler);

        try {
            $result = (new Csv())->getFeed('https://example.com/feed.csv', new FeedModel(['id' => 1]));
        } finally {
            Event::off(DataTypes::class, DataTypes::EVENT_BEFORE_FETCH_FEED, $handler);
            Event::on(DataTypes::class, DataTypes::EVENT_BEFORE_FETCH_FEED, [$this, 'supplyRawData']);
        }

        $this->assertFalse($result['success']);
        $this->assertStringContainsString('boom', $result['error']);
    }
}
