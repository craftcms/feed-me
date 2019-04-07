<?php

use verbb\feedme\elements\Entry;

class EntryTest extends \Codeception\Test\Unit
{
    protected $tester;

    protected function _before()
    {
        $this->service = new Entry();
    }

    protected function _after()
    {

    }

    public function testID()
    {
        $feedData = ['id' => '15868'];

        $feedMapping = [
            'attribute' => true,
            'node' => 'title',
            'default' => '123',
        ];

        $this->assertEquals('15868', $this->service->parseAttribute($feedData, 'id', $feedMapping));

        // Test default
        $feedData = ['id' => '123'];

        $this->assertEquals('123', $this->service->parseAttribute($feedData, 'id', $feedMapping));

        // Test invalid
        $feedMapping['id'] = '7894532376937';

        $this->assertNull($this->service->parseAttribute($feedData, 'id', $feedMapping));
    }


    public function testTitle()
    {
        $feedData = ['title' => 'RSS News'];

        $feedMapping = [
            'attribute' => true,
            'node' => 'title',
            'default' => 'Default Title',
        ];

        $this->assertEquals('RSS News', $this->service->parseAttribute($feedData, 'title', $feedMapping));

        // Test default
        $feedData = ['title' => ''];

        $this->assertEquals('Default Title', $this->service->parseAttribute($feedData, 'title', $feedMapping));

        // Test mapping with no default, but empty value
        $feedMapping['default'] = '';

        $this->assertEquals('', $this->service->parseAttribute($feedData, 'title', $feedMapping));
    }


    public function testSlug()
    {
        $feedData = ['slug' => 'rss-news'];

        $feedMapping = [
            'attribute' => true,
            'node' => 'slug',
            'default' => 'default-slug',
        ];

        $this->assertEquals('rss-news', $this->service->parseAttribute($feedData, 'slug', $feedMapping));

        // Test default
        $feedData = ['slug' => ''];

        $this->assertEquals('default-slug', $this->service->parseAttribute($feedData, 'slug', $feedMapping));

        // Test mapping with no default, but empty value
        $feedMapping['default'] = '';

        $this->assertEquals('', $this->service->parseAttribute($feedData, 'slug', $feedMapping));
    }


    public function testParentTitle()
    {
        $feedData = ['parent' => 'Homepage'];

        $feedMapping = [
            'attribute' => true,
            'node' => 'parent',
            'default' => '',
            'options' => ['match' => 'title'],
        ];

        $this->assertEquals('6', $this->service->parseAttribute($feedData, 'parent', $feedMapping));

        // Check invalid match
        $feedData = ['parent' => 'Homepage2'];

        $this->assertNull($this->service->parseAttribute($feedData, 'parent', $feedMapping));
    }

    public function testParentID()
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

    public function testParentSlug()
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

    public function testParentDefault()
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


    public function testAuthorFullName()
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

    public function testAuthorEmail()
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

    public function testAuthorUsername()
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

    public function testAuthorID()
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

    public function testAuthorEmpty()
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

    public function testAuthorDefault()
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
