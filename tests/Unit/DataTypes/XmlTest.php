<?php

namespace craft\feedme\tests\Unit\DataTypes;

use craft\feedme\datatypes\Atom;
use craft\feedme\datatypes\Rss;
use craft\feedme\datatypes\Xml;
use craft\feedme\events\FeedDataEvent;
use craft\feedme\models\FeedModel;
use craft\feedme\services\DataTypes;
use craft\feedme\tests\UnitTestCase;
use yii\base\Event;

class XmlTest extends UnitTestCase
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

    private function fetch(string $xml, array $settingsOverrides = [], string $class = Xml::class): array
    {
        $this->fetchResponseData = $xml;

        return (new $class())->getFeed('https://example.com/feed.xml', new FeedModel(array_merge(['id' => 1], $settingsOverrides)));
    }

    public function testMultipleSiblingElementsNormalizeToList(): void
    {
        $xml = '<channel><item><title>A</title></item><item><title>B</title></item></channel>';

        $result = $this->fetch($xml, ['primaryElement' => 'item']);

        $this->assertTrue($result['success']);
        $this->assertSame([['title' => 'A'], ['title' => 'B']], $result['data']);
    }

    public function testSingleNestedElementDoesNotGetListWrapper(): void
    {
        // findPrimaryElement() normalizes the *top-level* primary element to a list either way
        // (one <item> or many), but nested repeated elements aren't normalized by anything - a
        // single <category> stays a scalar, while two or more become a numeric list. Both shapes
        // below are the "correct" current behavior, just inconsistent with each other.
        $single = $this->fetch(
            '<channel><item><title>A</title><category>News</category></item></channel>',
            ['primaryElement' => 'item'],
        );
        $this->assertSame('News', $single['data'][0]['category']);

        $multiple = $this->fetch(
            '<channel><item><title>A</title><category>News</category><category>World</category></item></channel>',
            ['primaryElement' => 'item'],
        );
        $this->assertSame(['News', 'World'], $multiple['data'][0]['category']);
    }

    public function testCdataMergesAsPlainText(): void
    {
        $cdata = $this->fetch('<item><title><![CDATA[Foo & Bar]]></title></item>', ['primaryElement' => 'item']);
        $plain = $this->fetch('<item><title>Foo &amp; Bar</title></item>', ['primaryElement' => 'item']);

        $this->assertSame('Foo & Bar', $cdata['data'][0]['title']);
        $this->assertSame($plain['data'][0]['title'], $cdata['data'][0]['title']);
    }

    public function testAttributesCapturedWithAtPrefixAndMixedContentUnderAtKey(): void
    {
        $result = $this->fetch('<item><price currency="USD">9.99</price></item>', ['primaryElement' => 'item']);

        $this->assertSame(['@currency' => 'USD', '@' => '9.99'], $result['data'][0]['price']);
    }

    public function testSelfClosingElementParsesToEmptyString(): void
    {
        $result = $this->fetch('<item><link/></item>', ['primaryElement' => 'item']);

        $this->assertSame('', $result['data'][0]['link']);
    }

    public function testMalformedXmlReturnsGracefulError(): void
    {
        $result = $this->fetch('<item><title>Unclosed</item>');

        $this->assertFalse($result['success']);
        $this->assertStringContainsString('Invalid XML', $result['error']);
    }

    public function testPrimaryElementNotFoundReturnsSuccessWithFalseData(): void
    {
        $result = $this->fetch('<root><foo>bar</foo></root>', ['primaryElement' => 'nonexistent']);

        // Callers must check truthiness of `data`, not just `success` - this is a "successful"
        // response whose data is `false`.
        $this->assertTrue($result['success']);
        $this->assertFalse($result['data']);
    }

    public function testRssAndAtomDelegateToXmlParsingWithTheirOwnName(): void
    {
        $xml = '<feed><entry><title>A</title></entry><entry><title>B</title></entry></feed>';

        $rss = $this->fetch($xml, ['primaryElement' => 'entry'], Rss::class);
        $atom = $this->fetch($xml, ['primaryElement' => 'entry'], Atom::class);

        $this->assertSame([['title' => 'A'], ['title' => 'B']], $rss['data']);
        $this->assertSame($rss['data'], $atom['data']);
        $this->assertSame('RSS', (new Rss())->getName());
        $this->assertSame('ATOM', (new Atom())->getName());
    }
}
