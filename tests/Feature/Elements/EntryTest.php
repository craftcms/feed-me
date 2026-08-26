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

    public function testId(): void
    {
        $feedMapping = [
            'attribute' => true,
            'node' => 'id',
            'default' => '123',
        ];

        $feedData = ['id' => '15868'];
        $this->assertEquals('15868', $this->service->parseAttribute($feedData, 'id', $feedMapping));

        // Test default
        $feedData = ['id' => ''];
        $this->assertEquals('123', $this->service->parseAttribute($feedData, 'id', $feedMapping));
    }

    public function testTitle(): void
    {
        $feedMapping = [
            'attribute' => true,
            'node' => 'title',
            'default' => 'Default Title',
        ];

        $feedData = ['title' => 'RSS News'];
        $this->assertEquals('RSS News', $this->service->parseAttribute($feedData, 'title', $feedMapping));

        // Test default
        $feedData = ['title' => ''];
        $this->assertEquals('Default Title', $this->service->parseAttribute($feedData, 'title', $feedMapping));

        // Test mapping with no default, but empty value
        $feedMapping['default'] = '';
        $this->assertEquals('', $this->service->parseAttribute($feedData, 'title', $feedMapping));
    }

    public function testSlug(): void
    {
        $feedMapping = [
            'attribute' => true,
            'node' => 'slug',
            'default' => 'default-slug',
        ];

        $feedData = ['slug' => 'rss-news'];
        $this->assertEquals('rss-news', $this->service->parseAttribute($feedData, 'slug', $feedMapping));

        // Test default
        $feedData = ['slug' => ''];
        $this->assertEquals('default-slug', $this->service->parseAttribute($feedData, 'slug', $feedMapping));

        // Test mapping with no default, but empty value
        $feedMapping['default'] = '';
        $this->assertEquals('', $this->service->parseAttribute($feedData, 'slug', $feedMapping));
    }

    public static function authorMatchTypeProvider(): array
    {
        return [
            'email' => ['email'],
            'username' => ['username'],
            'id' => ['id'],
        ];
    }

    #[DataProvider('authorMatchTypeProvider')]
    public function testAuthorMatchesByType(string $matchType): void
    {
        $value = $matchType === 'id' ? (string)$this->author->id : $this->author->{$matchType};
        $feedData = ['author' => $value];

        $feedMapping = [
            'attribute' => true,
            'node' => 'author',
            'default' => '',
            'options' => ['match' => $matchType],
        ];

        $this->assertEquals([$this->author->id], $this->service->parseAttribute($feedData, 'authorIds', $feedMapping));

        // Check invalid match.
        $invalidValue = $matchType === 'id'
            ? $this->author->id + ElementFactory::NONEXISTENT_ID_OFFSET
            : 'nonexistent-' . $value;
        $feedData = ['author' => $invalidValue];

        $this->assertNull($this->service->parseAttribute($feedData, 'authorIds', $feedMapping));
    }

    public function testAuthorEmpty(): void
    {
        $feedData = ['author' => ''];

        $feedMapping = [
            'attribute' => true,
            'node' => 'author',
            'default' => '',
            'options' => ['match' => 'id'],
        ];

        $this->assertNull($this->service->parseAttribute($feedData, 'authorIds', $feedMapping));
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
