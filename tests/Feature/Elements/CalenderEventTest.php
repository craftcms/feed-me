<?php

namespace craft\feedme\tests\Feature\Elements;

use craft\elements\User as UserElement;
use craft\feedme\elements\CalenderEvent;
use craft\feedme\tests\Helpers\ElementFactory;
use craft\feedme\tests\TestCase;

class CalenderEventTest extends TestCase
{
    private CalenderEvent $service;

    private UserElement $author;

    protected function setUp(): void
    {
        parent::setUp();

        $this->service = new CalenderEvent();
        $this->author = ElementFactory::createUser();
    }

    public function testAuthorIdByEmail(): void
    {
        $feedData = ['author' => $this->author->email];

        $feedMapping = [
            'attribute' => true,
            'node' => 'author',
            'options' => ['match' => 'email'],
        ];

        $this->assertEquals($this->author->id, $this->service->parseAttribute($feedData, 'authorId', $feedMapping));

        // Check invalid match
        $feedData = ['author' => 'nonexistent-' . $this->author->email];

        $this->assertNull($this->service->parseAttribute($feedData, 'authorId', $feedMapping));
    }

    public function testAuthorIdByUsername(): void
    {
        $feedData = ['author' => $this->author->username];

        $feedMapping = [
            'attribute' => true,
            'node' => 'author',
            'options' => ['match' => 'username'],
        ];

        $this->assertEquals($this->author->id, $this->service->parseAttribute($feedData, 'authorId', $feedMapping));

        // Check invalid match
        $feedData = ['author' => 'nonexistent-' . $this->author->username];

        $this->assertNull($this->service->parseAttribute($feedData, 'authorId', $feedMapping));
    }

    public function testAuthorIdById(): void
    {
        $feedData = ['author' => (string)$this->author->id];

        $feedMapping = [
            'attribute' => true,
            'node' => 'author',
            'options' => ['match' => 'id'],
        ];

        $this->assertEquals($this->author->id, $this->service->parseAttribute($feedData, 'authorId', $feedMapping));

        // Check invalid match
        $feedData = ['author' => $this->author->id + 999999];

        $this->assertNull($this->service->parseAttribute($feedData, 'authorId', $feedMapping));
    }

    public function testAuthorIdEmpty(): void
    {
        $feedData = ['author' => ''];

        $feedMapping = [
            'attribute' => true,
            'node' => 'author',
            'options' => ['match' => 'id'],
        ];

        $this->assertNull($this->service->parseAttribute($feedData, 'authorId', $feedMapping));
    }
}
