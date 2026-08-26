<?php

namespace craft\feedme\tests\Feature\Elements;

use craft\elements\User as UserElement;
use craft\feedme\elements\CalenderEvent;
use craft\feedme\tests\Helpers\ElementFactory;
use craft\feedme\tests\TestCase;
use PHPUnit\Framework\Attributes\DataProvider;

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

    public static function authorMatchTypeProvider(): array
    {
        return [
            'email' => ['email'],
            'username' => ['username'],
            'id' => ['id'],
        ];
    }

    #[DataProvider('authorMatchTypeProvider')]
    public function testAuthorIdMatchesByType(string $matchType): void
    {
        $value = $matchType === 'id' ? (string)$this->author->id : $this->author->{$matchType};
        $feedData = ['author' => $value];

        $feedMapping = [
            'attribute' => true,
            'node' => 'author',
            'options' => ['match' => $matchType],
        ];

        $this->assertEquals($this->author->id, $this->service->parseAttribute($feedData, 'authorId', $feedMapping));

        // Check invalid match.
        $invalidValue = $matchType === 'id'
            ? $this->author->id + ElementFactory::NONEXISTENT_ID_OFFSET
            : 'nonexistent-' . $value;
        $feedData = ['author' => $invalidValue];

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
