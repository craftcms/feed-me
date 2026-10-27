<?php

namespace craft\feedme\tests\Feature\Fields;

use craft\elements\Entry as EntryElement;
use craft\feedme\fields\Entries;
use craft\feedme\tests\Helpers\ElementFactory;
use craft\feedme\tests\Helpers\FieldServiceFactory;
use craft\fields\Entries as EntriesField;
use function craft\feedme\tests\Helpers\matchesRelatedByType;

beforeEach(function() {
    $this->subject = ElementFactory::createEntry();

    // `sources: '*'` means "search every section" - avoids having to resolve a specific
    // source UID for the field settings.
    $this->service = FieldServiceFactory::create(Entries::class, new EntriesField(['sources' => '*']));
});

matchesRelatedByType(
    matchTypes: [
        'title' => ['title'],
        'id' => ['id'],
    ],
    node: 'related',
);

it('falls back to the configured default for an empty value', function() {
    // When the feed value is empty but a default IS configured (and the node isn't the
    // 'usedefault' sentinel), the default is used as-is rather than either matching nothing
    // or - worse - matching every entry via an empty query criterion.
    // https://github.com/craftcms/feed-me/issues/1195
    $this->service->fieldInfo = [
        'node' => 'related',
        'default' => (string)$this->subject->id,
        'options' => ['match' => 'title'],
    ];
    $this->service->feedData = ['related' => ''];

    expect($this->service->parseField())->toBe([(string)$this->subject->id]);
});

it('gives a created entry a post date', function() {
    // https://github.com/craftcms/feed-me/issues/1747 and
    // https://github.com/craftcms/feed-me/issues/1752 - entries created via "Create entries
    // if they do not exist" used to be saved without a post date, leaving them stuck in the
    // `pending` status even though the section enables entries by default.
    $this->service->fieldInfo = [
        'node' => 'related',
        'options' => ['match' => 'title', 'create' => true],
    ];
    $this->service->feedData = ['related' => 'A brand new related entry'];

    $ids = $this->service->parseField();

    expect($ids)->toHaveCount(1);

    $createdEntry = EntryElement::find()->id($ids[0])->status(null)->one();

    expect($createdEntry->postDate)->not->toBeNull();
    expect($createdEntry->status)->toBe(EntryElement::STATUS_LIVE);
});
