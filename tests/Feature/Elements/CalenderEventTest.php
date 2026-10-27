<?php

namespace craft\feedme\tests\Feature\Elements;

use craft\elements\User as UserElement;
use craft\feedme\elements\CalenderEvent;
use craft\feedme\tests\Helpers\ElementFactory;
use function craft\feedme\tests\Helpers\matchesAuthorByType;

beforeEach(function() {
    $this->service = new CalenderEvent();
    $this->author = ElementFactory::createUser();
});

matchesAuthorByType(
    attribute: 'authorId',
    expected: fn(UserElement $author) => $author->id,
    feedMapping: fn(string $matchType) => [
        'attribute' => true,
        'node' => 'author',
        'options' => ['match' => $matchType],
    ],
);
