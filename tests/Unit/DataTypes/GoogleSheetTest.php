<?php

namespace craft\feedme\tests\Unit\DataTypes;

use craft\feedme\datatypes\GoogleSheet;
use craft\feedme\events\FeedDataEvent;
use craft\feedme\models\FeedModel;
use craft\feedme\services\DataTypes;
use craft\feedme\tests\UnitTestCase;
use yii\base\Event;

class GoogleSheetTest extends UnitTestCase
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

    public function supplyRawData(FeedDataEvent $event): void
    {
        $event->response = ['success' => true, 'data' => $this->fetchResponseData];
    }

    private function fetch(mixed $rawBody): array
    {
        $this->fetchResponseData = is_string($rawBody) ? $rawBody : json_encode($rawBody);

        return (new GoogleSheet())->getFeed('https://example.com/feed.json', new FeedModel(['id' => 1]));
    }

    public function testParsesRowsWithHeaderRow(): void
    {
        $result = $this->fetch(['values' => [['id', 'title'], ['1', 'Foo']]]);

        $this->assertTrue($result['success']);
        $this->assertSame([['id' => '1', 'title' => 'Foo']], $result['data']);
    }

    public function testBlankHeadingGetsPositionalFallbackName(): void
    {
        $result = $this->fetch(['values' => [['id', '', 'title'], ['1', 'x', 'Foo']]]);

        $this->assertSame(['id' => '1', 'blank_heading_2' => 'x', 'title' => 'Foo'], $result['data'][0]);
    }

    public function testRaggedRowLongerThanHeaderReturnsGracefulError(): void
    {
        $result = $this->fetch(['values' => [['id', 'title'], ['1', 'Foo', 'extra']]]);

        $this->assertFalse($result['success']);
        $this->assertNotEmpty($result['error']);
    }

    public function testRaggedRowShorterThanHeaderOmitsMissingKeys(): void
    {
        $result = $this->fetch(['values' => [['id', 'title', 'extra'], ['1', 'Foo']]]);

        $this->assertSame(['id' => '1', 'title' => 'Foo'], $result['data'][0]);
    }

    public function testBlankRowsAreNotFiltered(): void
    {
        // Unlike Csv, GoogleSheet has no equivalent of _isArrayEmpty() - a fully-blank row survives.
        $result = $this->fetch(['values' => [['id', 'title'], ['', '']]]);

        $this->assertCount(1, $result['data']);
        $this->assertSame(['id' => '', 'title' => ''], $result['data'][0]);
    }

    public function testMissingValuesKeyReturnsGracefulError(): void
    {
        $result = $this->fetch(['rows' => []]);

        $this->assertFalse($result['success']);
        $this->assertNotEmpty($result['error']);
    }

    public function testEmptyResponseBodyReturnsGracefulError(): void
    {
        $result = $this->fetch('');

        $this->assertFalse($result['success']);
        $this->assertNotEmpty($result['error']);
    }
}
