<?php

namespace craft\feedme\tests\Feature\Services;

use Craft;
use craft\elements\Entry as EntryElement;
use craft\feedme\models\FeedModel;
use craft\feedme\Plugin;
use craft\feedme\services\Process;
use craft\feedme\tests\Helpers\ElementFactory;
use craft\services\Elements as ElementsService;

beforeEach(function() {
    $this->service = new Process();
    $this->section = ElementFactory::createSection();
    $this->entryType = $this->section->getEntryTypes()[0];

    // The feed-me Entry element service is a plugin-wide singleton (registered once at
    // plugin init) whose `element` property persists across every processFeed() call for
    // the life of the test process. Without this reset, Entry::getQuery() could read a
    // stale $element left over from a previous test, pointing at a section that no longer
    // exists once that test's transaction rolled back.
    Plugin::$plugin->elements->getRegisteredElement(EntryElement::class)->element = null;
});

/**
 * A minimal FeedModel mapping `slug` (used as the unique-match field) and `title` into
 * this test's section/entry type - matches the shape `Process::debugFeed()` builds from a
 * real feed config, but constructed directly so tests can drive it without a network fetch.
 */
function makeProcessFeed(array $overrides = []): FeedModel
{
    return new FeedModel(array_merge([
        'name' => 'Test feed',
        'feedUrl' => 'https://example.com/feed.xml',
        'feedType' => 'xml',
        'elementType' => EntryElement::class,
        'elementGroup' => [
            EntryElement::class => [
                'section' => test()->section->id,
                'entryType' => test()->entryType->id,
            ],
        ],
        'passkey' => 'test',
        'fieldMapping' => [
            'slug' => ['attribute' => true, 'node' => 'slug', 'default' => ''],
            'title' => ['attribute' => true, 'node' => 'title', 'default' => ''],
        ],
        'fieldUnique' => ['slug' => 1],
        'duplicateHandle' => ['add'],
    ], $overrides));
}

/**
 * Mirrors the call sequence `Process::debugFeed()` uses internally
 * (beforeProcessFeed -> getFeedSettings -> processFeed per row -> afterProcessFeed), but
 * with `$rows` passed directly instead of fetched via `FeedModel::getFeedData()`.
 */
function runFeed(FeedModel $feed, array $rows): array
{
    $service = test()->service;
    $processedElementIds = [];

    $service->beforeProcessFeed($feed, $rows);
    $settings = $service->getFeedSettings($feed, $rows);

    foreach ($rows as $key => $row) {
        $service->processFeed($key, $settings, $processedElementIds, $row);
    }

    $service->afterProcessFeed($settings, $feed, $processedElementIds);

    return $processedElementIds;
}

it('adds a new element', function() {
    $feed = makeProcessFeed(['duplicateHandle' => ['add']]);

    runFeed($feed, [
        ['slug' => 'fixed-slug', 'title' => 'Original Title'],
    ]);

    $entry = EntryElement::find()->status(null)->slug('fixed-slug')->one();
    expect($entry)->not->toBeNull();
    expect($entry->title)->toBe('Original Title');
});

it('updates an existing element', function() {
    runFeed(makeProcessFeed(['duplicateHandle' => ['add']]), [
        ['slug' => 'fixed-slug', 'title' => 'Original Title'],
    ]);
    $original = EntryElement::find()->status(null)->slug('fixed-slug')->one();

    runFeed(makeProcessFeed(['duplicateHandle' => ['update']]), [
        ['slug' => 'fixed-slug', 'title' => 'Updated Title'],
    ]);

    $updated = EntryElement::find()->status(null)->slug('fixed-slug')->one();
    expect($updated->id)->toBe($original->id);
    expect($updated->title)->toBe('Updated Title');
    expect(EntryElement::find()->status(null)->sectionId($this->section->id)->count())->toEqual(1);
});

it('skips when add-only and the element exists', function() {
    $addFeed = makeProcessFeed(['duplicateHandle' => ['add']]);

    runFeed($addFeed, [
        ['slug' => 'fixed-slug', 'title' => 'Original Title'],
    ]);

    // Same add-only strategy, matching row now has a different title - since an element
    // already exists and duplicateHandle is add-only, the row should be skipped entirely.
    runFeed($addFeed, [
        ['slug' => 'fixed-slug', 'title' => 'Different Title'],
    ]);

    $entry = EntryElement::find()->status(null)->slug('fixed-slug')->one();
    expect($entry->title)->toBe('Original Title');
});

it('disables a missing element', function() {
    $keep = ElementFactory::createEntry(['sectionId' => $this->section->id, 'typeId' => $this->entryType->id]);
    $missing = ElementFactory::createEntry(['sectionId' => $this->section->id, 'typeId' => $this->entryType->id]);

    // Only the "keep" row is present in this run's feed data - with a disable-only
    // strategy, anything else in this section/entry type that isn't matched gets disabled.
    runFeed(makeProcessFeed(['duplicateHandle' => ['disable']]), [
        ['slug' => $keep->slug, 'title' => $keep->title],
    ]);

    expect(EntryElement::find()->status(null)->id($missing->id)->one()->enabled)->toBeFalse();
    expect(EntryElement::find()->status(null)->id($keep->id)->one()->enabled)->toBeTrue();
});

it('deletes a missing element', function() {
    $keep = ElementFactory::createEntry(['sectionId' => $this->section->id, 'typeId' => $this->entryType->id]);
    $missing = ElementFactory::createEntry(['sectionId' => $this->section->id, 'typeId' => $this->entryType->id]);

    runFeed(makeProcessFeed(['duplicateHandle' => ['delete']]), [
        ['slug' => $keep->slug, 'title' => $keep->title],
    ]);

    expect(EntryElement::find()->status(null)->id($missing->id)->one())->toBeNull();
    expect(EntryElement::find()->status(null)->id($keep->id)->one())->not->toBeNull();
});

it('skips the save when content is unchanged', function() {
    runFeed(makeProcessFeed(['duplicateHandle' => ['update']]), [
        ['slug' => 'fixed-slug', 'title' => 'Same Title'],
    ]);

    $saveEventFired = false;
    $handler = function() use (&$saveEventFired) {
        $saveEventFired = true;
    };

    Craft::$app->getElements()->on(ElementsService::EVENT_BEFORE_SAVE_ELEMENT, $handler);

    try {
        // Same slug and title as the first run - compareContent (on by default) should skip
        // the save entirely rather than re-saving identical content.
        runFeed(makeProcessFeed(['duplicateHandle' => ['update']]), [
            ['slug' => 'fixed-slug', 'title' => 'Same Title'],
        ]);
    } finally {
        Craft::$app->getElements()->off(ElementsService::EVENT_BEFORE_SAVE_ELEMENT, $handler);
    }

    expect($saveEventFired)->toBeFalse();
});
