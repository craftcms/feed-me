<?php

namespace craft\feedme\tests\Feature\Elements;

use craft\elements\Entry as EntryElement;
use craft\elements\User as UserElement;
use craft\feedme\elements\Entry;
use craft\feedme\tests\Helpers\ElementFactory;
use function craft\feedme\tests\Helpers\matchesAuthorByType;
use function craft\feedme\tests\Helpers\parsesParentAttribute;

beforeEach(function() {
    $this->service = new Entry();
    // parseParent() assigns onto $this->service->element (the entry being imported into),
    // so it needs to be a real element rather than null, same as a real import would set via setModel().
    $this->service->element = new EntryElement();
    $this->parent = ElementFactory::createEntry();
    $this->author = ElementFactory::createUser();
});

parsesParentAttribute();

matchesAuthorByType(
    attribute: 'authorIds',
    expected: fn(UserElement $author) => [$author->id],
    feedMapping: fn(string $matchType) => [
        'attribute' => true,
        'node' => 'author',
        'default' => '',
        'options' => ['match' => $matchType],
    ],
);

it('parses a simple attribute with a default', function(string $attribute, string $default, string $value) {
    $feedMapping = [
        'attribute' => true,
        'node' => $attribute,
        'default' => $default,
    ];

    $feedData = [$attribute => $value];
    expect($this->service->parseAttribute($feedData, $attribute, $feedMapping))->toEqual($value);

    // Test default
    $feedData = [$attribute => ''];
    expect($this->service->parseAttribute($feedData, $attribute, $feedMapping))->toEqual($default);

    // Test mapping with no default, but empty value
    $feedMapping['default'] = '';
    expect($this->service->parseAttribute($feedData, $attribute, $feedMapping))->toEqual('');
})->with([
    'id' => ['id', '123', '15868'],
    'title' => ['title', 'Default Title', 'RSS News'],
    'slug' => ['slug', 'default-slug', 'rss-news'],
]);

it('uses the author default', function() {
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

    expect($this->service->parseAttribute($feedData, 'authorIds', $feedMapping))->toEqual([$this->author->id]);

    $feedMapping = [
        'attribute' => true,
        'node' => 'usedefault',
        'default' => '',
        'options' => ['match' => 'title'],
    ];

    expect($this->service->parseAttribute($feedData, 'authorIds', $feedMapping))->toBeNull();
});
