<?php

namespace craft\feedme\tests\Feature\Elements;

use craft\elements\Entry as EntryElement;
use craft\elements\User as UserElement;
use craft\feedme\elements\Entry;
use craft\feedme\tests\Helpers\ElementFactory;
use craft\feedme\tests\TestCase;
use PHPUnit\Framework\Attributes\DataProvider;

class EntryTest extends TestCase
{
    use MatchesAuthorByTypeTests;
    use ParsesParentAttributeTests;

    private Entry $service;

    private EntryElement $parentEntry;

    private UserElement $author;

    protected function setUp(): void
    {
        parent::setUp();

        $this->service = new Entry();
        // parseParent() assigns onto $this->service->element (the entry being imported into),
        // so it needs to be a real element rather than null, same as a real import would set via setModel().
        $this->service->element = new EntryElement();
        $this->parentEntry = ElementFactory::createEntry();
        $this->author = ElementFactory::createUser();
    }

    protected function parentMatchService(): Entry
    {
        return $this->service;
    }

    protected function parentElement(): EntryElement
    {
        return $this->parentEntry;
    }

    protected function authorMatchService(): Entry
    {
        return $this->service;
    }

    protected function authorSubject(): UserElement
    {
        return $this->author;
    }

    protected function authorMatchAttribute(): string
    {
        return 'authorIds';
    }

    protected function authorMatchExpected(UserElement $author): mixed
    {
        return [$author->id];
    }

    protected function authorMatchFeedMapping(string $matchType): array
    {
        return [
            'attribute' => true,
            'node' => 'author',
            'default' => '',
            'options' => ['match' => $matchType],
        ];
    }

    public static function simpleAttributeProvider(): array
    {
        return [
            'id' => ['id', '123', '15868'],
            'title' => ['title', 'Default Title', 'RSS News'],
            'slug' => ['slug', 'default-slug', 'rss-news'],
        ];
    }

    #[DataProvider('simpleAttributeProvider')]
    public function testSimpleAttributeParsesWithDefault(string $attribute, string $default, string $value): void
    {
        $feedMapping = [
            'attribute' => true,
            'node' => $attribute,
            'default' => $default,
        ];

        $feedData = [$attribute => $value];
        $this->assertEquals($value, $this->service->parseAttribute($feedData, $attribute, $feedMapping));

        // Test default
        $feedData = [$attribute => ''];
        $this->assertEquals($default, $this->service->parseAttribute($feedData, $attribute, $feedMapping));

        // Test mapping with no default, but empty value
        $feedMapping['default'] = '';
        $this->assertEquals('', $this->service->parseAttribute($feedData, $attribute, $feedMapping));
    }

    public function testAuthorDefault(): void
    {
        // 'usedefault' is Feed Me's "always use the configured default, regardless of feed
        // data" sentinel node value (see the "Use default value" mapping option) - it's the
        // only way `parseAuthorIds()` applies a default, unlike the generic simple-value fallback.
        $feedData = ['author' => 'irrelevant'];

        $feedMapping = [
            'attribute' => true,
            'node' => 'usedefault',
            'default' => (string)$this->author->id,
            'options' => ['match' => 'title'],
        ];

        $this->assertEquals([$this->author->id], $this->service->parseAttribute($feedData, 'authorIds', $feedMapping));

        $feedMapping = [
            'attribute' => true,
            'node' => 'usedefault',
            'default' => '',
            'options' => ['match' => 'title'],
        ];

        $this->assertNull($this->service->parseAttribute($feedData, 'authorIds', $feedMapping));
    }
}
