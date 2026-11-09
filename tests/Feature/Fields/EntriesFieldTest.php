<?php

namespace craft\feedme\tests\Feature\Fields;

use Craft;
use craft\elements\Entry as EntryElement;
use craft\feedme\fields\Entries;
use craft\feedme\helpers\FieldHelper;
use craft\feedme\models\FeedModel;
use craft\feedme\tests\Helpers\ElementFactory;
use craft\feedme\tests\Helpers\FieldServiceFactory;
use craft\fields\Entries as EntriesField;
use craft\helpers\StringHelper;
use craft\models\Section;
use craft\models\Section_SiteSettings;
use craft\models\Site;
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

it('falls back to all unique id fields when the entry type has no field layout', function() {
    // https://github.com/craftcms/feed-me/issues/1674 - an entry type without a saved field
    // layout (e.g. a single migrated from Craft 3) made the mapping screen throw a TypeError.
    $section = ElementFactory::createSection();
    $entryType = $section->getEntryTypes()[0];
    // getEntryTypeById() returns the same memoized model, so this is what getMatchFields() sees.
    Craft::$app->getEntries()->getEntryTypeById($entryType->id)->fieldLayoutId = null;

    $feed = new FeedModel([
        'elementGroup' => [EntryElement::class => ['section' => $section->id, 'entryType' => $entryType->id]],
    ]);

    expect(Entries::getMatchFields($feed))->toBe(FieldHelper::getAllUniqueIdFields());
    expect(Entries::getMatchFields($feed, new EntriesField(['sources' => ['section:' . $section->uid]])))->toBe([]);
});

it('keeps entries found on the target site when maintaining hierarchy', function() {
    // https://github.com/craftcms/feed-me/issues/1732 - with `maintainHierarchy` on, the found
    // entries were re-fetched from the current site, so entries that only exist on the field's
    // target site were dropped.
    $sitesService = Craft::$app->getSites();
    $otherSite = new Site([
        'groupId' => $sitesService->getPrimarySite()->groupId,
        'name' => 'Other site',
        'handle' => 'otherSite' . StringHelper::randomString(6),
        'language' => 'en-US',
        'hasUrls' => false,
    ]);
    $sitesService->saveSite($otherSite);
    setIsMultiSite(true);

    try {
        $section = ElementFactory::createSection([
            'type' => Section::TYPE_STRUCTURE,
            'siteSettings' => [$otherSite->id => new Section_SiteSettings(['siteId' => $otherSite->id])],
        ]);

        $entry = new EntryElement([
            'sectionId' => $section->id,
            'typeId' => $section->getEntryTypes()[0]->id,
            'siteId' => $otherSite->id,
            'title' => 'Only on the other site',
        ]);
        Craft::$app->getElements()->saveElement($entry);

        $this->service->field->maintainHierarchy = true;
        $this->service->field->targetSiteId = $otherSite->uid;
        $this->service->fieldInfo = ['node' => 'related', 'options' => ['match' => 'title']];
        $this->service->feedData = ['related' => 'Only on the other site'];

        expect($this->service->parseField())->toBe([$entry->id]);
    } finally {
        // Sites are cached in memory and stored in the project config, neither of which gets
        // rolled back with the DB transaction.
        $sitesService->deleteSite($otherSite);
        // The deleted site is only soft-deleted until the transaction is rolled back.
        setIsMultiSite(false);
    }
});

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
