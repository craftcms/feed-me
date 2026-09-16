<?php

namespace craft\feedme\tests\Feature\Elements;

use craft\elements\User as UserElement;
use craft\feedme\elements\CalenderEvent;
use craft\feedme\tests\Helpers\ElementFactory;
use craft\feedme\tests\TestCase;

class CalenderEventTest extends TestCase
{
    use MatchesAuthorByTypeTests;

    private CalenderEvent $service;

    private UserElement $author;

    protected function setUp(): void
    {
        parent::setUp();

        $this->service = new CalenderEvent();
        $this->author = ElementFactory::createUser();
    }

    protected function authorMatchService(): CalenderEvent
    {
        return $this->service;
    }

    protected function authorSubject(): UserElement
    {
        return $this->author;
    }

    protected function authorMatchAttribute(): string
    {
        return 'authorId';
    }

    protected function authorMatchExpected(UserElement $author): mixed
    {
        return $author->id;
    }

    protected function authorMatchFeedMapping(string $matchType): array
    {
        return [
            'attribute' => true,
            'node' => 'author',
            'options' => ['match' => $matchType],
        ];
    }
}
