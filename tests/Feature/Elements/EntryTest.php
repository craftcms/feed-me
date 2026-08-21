<?php

namespace craft\feedme\tests\Feature\Elements;

use craft\elements\Entry as EntryElement;
use craft\elements\User as UserElement;
use craft\feedme\elements\Entry;
use craft\feedme\tests\Helpers\ElementFactory;
use craft\feedme\tests\TestCase;

class EntryTest extends TestCase
{
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

    public function testParentTitle(): void
    {
        $feedMapping = [
            'attribute' => true,
            'node' => 'parent',
            'default' => '',
            'options' => ['match' => 'title'],
        ];

        $feedData = ['parent' => $this->parentEntry->title];
        $this->assertEquals($this->parentEntry->id, $this->service->parseAttribute($feedData, 'parent', $feedMapping));

        // Check invalid match
        $feedData = ['parent' => $this->parentEntry->title . '-nonexistent'];

        $this->assertNull($this->service->parseAttribute($feedData, 'parent', $feedMapping));
    }

    public function testParentID(): void
    {
        $feedData = ['parent' => (string)$this->parentEntry->id];

        $feedMapping = [
            'attribute' => true,
            'node' => 'parent',
            'default' => '',
            'options' => ['match' => 'id'],
        ];

        $this->assertEquals($this->parentEntry->id, $this->service->parseAttribute($feedData, 'parent', $feedMapping));

        // Check invalid match
        $feedData = ['parent' => $this->parentEntry->id + 999999];

        $this->assertNull($this->service->parseAttribute($feedData, 'parent', $feedMapping));
    }

    public function testParentSlug(): void
    {
        $feedData = ['parent' => $this->parentEntry->slug];

        $feedMapping = [
            'attribute' => true,
            'node' => 'parent',
            'default' => '',
            'options' => ['match' => 'slug'],
        ];

        $this->assertEquals($this->parentEntry->id, $this->service->parseAttribute($feedData, 'parent', $feedMapping));

        // Check invalid match
        $feedData = ['parent' => $this->parentEntry->slug . '-nonexistent'];

        $this->assertNull($this->service->parseAttribute($feedData, 'parent', $feedMapping));
    }

    public function testParentDefault(): void
    {
        $feedData = ['parent' => ''];

        $feedMapping = [
            'attribute' => true,
            'node' => 'parent',
            'default' => (string)$this->parentEntry->id,
            'options' => ['match' => 'title'],
        ];

        $this->assertEquals($this->parentEntry->id, $this->service->parseAttribute($feedData, 'parent', $feedMapping));

        $feedMapping = [
            'attribute' => true,
            'node' => 'parent',
            'default' => '',
            'options' => ['match' => 'title'],
        ];

        $this->assertNull($this->service->parseAttribute($feedData, 'parent', $feedMapping));
    }

    public function testAuthorFullName(): void
    {
        // 'fullName' matching goes through `UserElement::findOne(['search' => ...])`, i.e. MySQL
        // InnoDB full-text search - which only sees committed data. Since every test here runs
        // inside a rolled-back transaction (see TestCase), a user created earlier in this same
        // test can never be found this way, no matter how correctly it's indexed. Skipped until
        // this suite has a non-transactional way to test full-text-dependent matching.
        $this->markTestSkipped(
            'fullName author matching relies on MySQL full-text search, which only sees committed '
            . 'data - incompatible with this suite\'s per-test transaction rollback.',
        );
    }

    public function testAuthorEmail(): void
    {
        $feedData = ['author' => $this->author->email];

        $feedMapping = [
            'attribute' => true,
            'node' => 'author',
            'default' => '',
            'options' => ['match' => 'email'],
        ];

        $this->assertEquals([$this->author->id], $this->service->parseAttribute($feedData, 'authorIds', $feedMapping));

        // Check invalid match
        $feedData = ['author' => 'nonexistent-' . $this->author->email];

        $this->assertNull($this->service->parseAttribute($feedData, 'authorIds', $feedMapping));
    }

    public function testAuthorUsername(): void
    {
        $feedData = ['author' => $this->author->username];

        $feedMapping = [
            'attribute' => true,
            'node' => 'author',
            'default' => '',
            'options' => ['match' => 'username'],
        ];

        $this->assertEquals([$this->author->id], $this->service->parseAttribute($feedData, 'authorIds', $feedMapping));

        // Check invalid match
        $feedData = ['author' => 'nonexistent-' . $this->author->username];

        $this->assertNull($this->service->parseAttribute($feedData, 'authorIds', $feedMapping));
    }

    public function testAuthorID(): void
    {
        $feedData = ['author' => (string)$this->author->id];

        $feedMapping = [
            'attribute' => true,
            'node' => 'author',
            'default' => '',
            'options' => ['match' => 'id'],
        ];

        $this->assertEquals([$this->author->id], $this->service->parseAttribute($feedData, 'authorIds', $feedMapping));

        // Check invalid match
        $feedData = ['author' => $this->author->id + 999999];

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
