<?php

namespace craft\feedme\tests\Feature\Services;

use Craft;
use craft\elements\Entry as EntryElement;
use craft\enums\PropagationMethod;
use craft\feedme\models\FeedModel;
use craft\feedme\Plugin;
use craft\feedme\services\Process;
use craft\feedme\tests\Helpers\ElementFactory;
use craft\helpers\StringHelper;
use craft\models\Section_SiteSettings;
use craft\models\Site;
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

it('does not apply an incoming id to a new element', function() {
    // https://github.com/craftcms/feed-me/issues/1773 - a mapped `id` is only used to match
    // existing elements; it used to be set on the element itself, so saving a new element
    // tried to update a non-existent row.
    $feed = makeProcessFeed([
        'fieldMapping' => [
            'id' => ['attribute' => true, 'node' => 'id', 'default' => ''],
            'slug' => ['attribute' => true, 'node' => 'slug', 'default' => ''],
            'title' => ['attribute' => true, 'node' => 'title', 'default' => ''],
        ],
        'duplicateHandle' => ['add'],
    ]);

    runFeed($feed, [
        ['id' => 999999, 'slug' => 'fixed-slug', 'title' => 'Original Title'],
    ]);

    $entry = EntryElement::find()->status(null)->slug('fixed-slug')->one();
    expect($entry)->not->toBeNull();
    expect($entry->id)->not->toEqual(999999);
});

it('does not apply an incoming id to a matched element', function() {
    // https://github.com/craftcms/feed-me/issues/1773
    $existing = ElementFactory::createEntry(['sectionId' => $this->section->id, 'typeId' => $this->entryType->id]);

    $feed = makeProcessFeed([
        'fieldMapping' => [
            'id' => ['attribute' => true, 'node' => 'id', 'default' => ''],
            'slug' => ['attribute' => true, 'node' => 'slug', 'default' => ''],
            'title' => ['attribute' => true, 'node' => 'title', 'default' => ''],
        ],
        'duplicateHandle' => ['update'],
    ]);

    runFeed($feed, [
        ['id' => 999999, 'slug' => $existing->slug, 'title' => 'Updated Title'],
    ]);

    $updated = EntryElement::find()->status(null)->id($existing->id)->one();
    expect($updated->title)->toBe('Updated Title');
    expect(EntryElement::find()->status(null)->sectionId($this->section->id)->count())->toEqual(1);
});

it('still matches existing elements by id', function() {
    $existing = ElementFactory::createEntry(['sectionId' => $this->section->id, 'typeId' => $this->entryType->id]);

    $feed = makeProcessFeed([
        'fieldMapping' => [
            'id' => ['attribute' => true, 'node' => 'id', 'default' => ''],
            'title' => ['attribute' => true, 'node' => 'title', 'default' => ''],
        ],
        'fieldUnique' => ['id' => 1],
        'duplicateHandle' => ['update'],
    ]);

    runFeed($feed, [
        ['id' => $existing->id, 'title' => 'Updated Title'],
    ]);

    $updated = EntryElement::find()->status(null)->id($existing->id)->one();
    expect($updated->title)->toBe('Updated Title');
    expect(EntryElement::find()->status(null)->sectionId($this->section->id)->count())->toEqual(1);
});

it('uses the target site default status when adding a matched entry to a new site', function(bool $enabledByDefault) {
    // https://github.com/craftcms/feed-me/issues/1787 - with the `custom` propagation method, an
    // entry matched on another site gets added to the feed's target site by checkPropagation().
    // Its status there should come from the section's default status for that site (same as
    // setModel() does for new entries), rather than being copied from whichever site the entry
    // was found on.
    $sitesService = Craft::$app->getSites();
    $primarySite = $sitesService->getPrimarySite();
    $targetSite = new Site([
        'groupId' => $primarySite->groupId,
        'name' => 'Target site',
        'handle' => 'targetSite' . StringHelper::randomString(6),
        'language' => 'en-US',
        'hasUrls' => false,
    ]);
    $sitesService->saveSite($targetSite);
    setIsMultiSite(true);

    try {
        $this->section = ElementFactory::createSection([
            'propagationMethod' => PropagationMethod::Custom,
            'siteSettings' => [
                $primarySite->id => new Section_SiteSettings(['siteId' => $primarySite->id]),
                $targetSite->id => new Section_SiteSettings(['siteId' => $targetSite->id, 'enabledByDefault' => $enabledByDefault]),
            ],
        ]);
        $this->entryType = $this->section->getEntryTypes()[0];

        // Enabled, and only saved to the primary site
        $entry = new EntryElement([
            'sectionId' => $this->section->id,
            'typeId' => $this->entryType->id,
            'siteId' => $primarySite->id,
            'slug' => 'fixed-slug',
            'title' => 'Original Title',
        ]);
        $entry->setEnabledForSite([$primarySite->id => true]);
        Craft::$app->getElements()->saveElement($entry);

        // The feed doesn't map the status ("Don't import")
        runFeed(makeProcessFeed(['siteId' => $targetSite->id, 'duplicateHandle' => ['add', 'update']]), [
            ['slug' => 'fixed-slug', 'title' => 'Original Title'],
        ]);

        $targetEntry = EntryElement::find()->status(null)->id($entry->id)->siteId($targetSite->id)->one();
        expect($targetEntry)->not->toBeNull();
        expect($targetEntry->getEnabledForSite())->toBe($enabledByDefault);

        $primaryEntry = EntryElement::find()->status(null)->id($entry->id)->siteId($primarySite->id)->one();
        expect($primaryEntry->getEnabledForSite())->toBeTrue();
    } finally {
        // Sites are cached in memory and stored in the project config, neither of which gets
        // rolled back with the DB transaction.
        $sitesService->deleteSite($targetSite);
        // The deleted site is only soft-deleted until the transaction is rolled back.
        setIsMultiSite(false);
    }
})->with([
    'disabled by default' => [false],
    'enabled by default' => [true],
]);

/**
 * Overrides the app's memoized "is multi-site" values, which element queries use to decide whether
 * to filter by site at all.
 */
function setIsMultiSite(bool $value): void
{
    (function() use ($value) {
        $this->_isMultiSite = $value;
        $this->_isMultiSiteWithTrashed = $value;
    })->call(Craft::$app);
}
