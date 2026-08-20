<?php

namespace craft\feedme\tests\Feature\Elements;

use craft\feedme\elements\Entry;
use craft\feedme\tests\TestCase;

class EntryTest extends TestCase
{
    private Entry $service;

    protected function setUp(): void
    {
        parent::setUp();

        $this->service = new Entry();
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

        $feedData = ['parent' => 'Homepage'];
        $this->assertEquals('6', $this->service->parseAttribute($feedData, 'parent', $feedMapping));

        // Check invalid match
        $feedData = ['parent' => 'Homepage2'];

        $this->assertNull($this->service->parseAttribute($feedData, 'parent', $feedMapping));
    }

    public function testParentID(): void
    {
        $feedData = ['parent' => '6'];

        $feedMapping = [
            'attribute' => true,
            'node' => 'parent',
            'default' => '',
            'options' => ['match' => 'id'],
        ];

        $this->assertEquals('6', $this->service->parseAttribute($feedData, 'parent', $feedMapping));

        // Check invalid match
        $feedData = ['parent' => '6987'];

        $this->assertNull($this->service->parseAttribute($feedData, 'parent', $feedMapping));
    }

    public function testParentSlug(): void
    {
        $feedData = ['parent' => 'homepage'];

        $feedMapping = [
            'attribute' => true,
            'node' => 'parent',
            'default' => '',
            'options' => ['match' => 'slug'],
        ];

        $this->assertEquals('6', $this->service->parseAttribute($feedData, 'parent', $feedMapping));

        // Check invalid match
        $feedData = ['parent' => 'homepage2'];

        $this->assertNull($this->service->parseAttribute($feedData, 'parent', $feedMapping));
    }

    public function testParentDefault(): void
    {
        $feedData = ['parent' => ''];

        $feedMapping = [
            'attribute' => true,
            'node' => 'parent',
            'default' => '6',
            'options' => ['match' => 'title'],
        ];

        $this->assertEquals('6', $this->service->parseAttribute($feedData, 'parent', $feedMapping));

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
        $feedData = ['author' => 'Josh Crawford'];

        $feedMapping = [
            'attribute' => true,
            'node' => 'author',
            'default' => '',
            'options' => ['match' => 'fullName'],
        ];

        $this->assertEquals('1', $this->service->parseAttribute($feedData, 'authorId', $feedMapping));

        // Check invalid match
        $feedData = ['author' => 'Joshua Crawford'];

        $this->assertNull($this->service->parseAttribute($feedData, 'authorId', $feedMapping));
    }

    public function testAuthorEmail(): void
    {
        $feedData = ['author' => 'web@sgroup.com.au'];

        $feedMapping = [
            'attribute' => true,
            'node' => 'author',
            'default' => '',
            'options' => ['match' => 'email'],
        ];

        $this->assertEquals('1', $this->service->parseAttribute($feedData, 'authorId', $feedMapping));

        // Check invalid match
        $feedData = ['author' => 'webbie@sgroup.com.au'];

        $this->assertNull($this->service->parseAttribute($feedData, 'authorId', $feedMapping));
    }

    public function testAuthorUsername(): void
    {
        $feedData = ['author' => 'web@sgroup.com.au'];

        $feedMapping = [
            'attribute' => true,
            'node' => 'author',
            'default' => '',
            'options' => ['match' => 'username'],
        ];

        $this->assertEquals('1', $this->service->parseAttribute($feedData, 'authorId', $feedMapping));

        // Check invalid match
        $feedData = ['author' => 'webbie@sgroup.com.au'];

        $this->assertNull($this->service->parseAttribute($feedData, 'authorId', $feedMapping));
    }

    public function testAuthorID(): void
    {
        $feedData = ['author' => '1'];

        $feedMapping = [
            'attribute' => true,
            'node' => 'author',
            'default' => '',
            'options' => ['match' => 'id'],
        ];

        $this->assertEquals('1', $this->service->parseAttribute($feedData, 'authorId', $feedMapping));

        // Check invalid match
        $feedData = ['author' => '999999'];

        $this->assertNull($this->service->parseAttribute($feedData, 'authorId', $feedMapping));
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

        $this->assertNull($this->service->parseAttribute($feedData, 'authorId', $feedMapping));
    }

    public function testAuthorDefault(): void
    {
        $feedData = ['author' => ''];

        $feedMapping = [
            'attribute' => true,
            'node' => 'author',
            'default' => '1',
            'options' => ['match' => 'title'],
        ];

        $this->assertEquals('1', $this->service->parseAttribute($feedData, 'authorId', $feedMapping));

        $feedMapping = [
            'attribute' => true,
            'node' => 'author',
            'default' => '',
            'options' => ['match' => 'title'],
        ];

        $this->assertNull($this->service->parseAttribute($feedData, 'authorId', $feedMapping));
    }
}
