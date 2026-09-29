<?php

namespace craft\feedme\tests\Feature\Services;

use Craft;
use craft\elements\Entry as EntryElement;
use craft\feedme\models\FeedModel;
use craft\feedme\Plugin;
use craft\feedme\services\Process;
use craft\feedme\tests\Helpers\ElementFactory;
use craft\feedme\tests\TestCase;
use craft\models\EntryType;
use craft\models\Section;
use craft\services\Elements as ElementsService;

class ProcessTest extends TestCase
{
    private Process $service;

    private Section $section;

    private EntryType $entryType;

    protected function setUp(): void
    {
        parent::setUp();

        $this->service = new Process();
        $this->section = ElementFactory::createSection();
        $this->entryType = $this->section->getEntryTypes()[0];

        // The feed-me Entry element service is a plugin-wide singleton (registered once at
        // plugin init) whose `element` property persists across every processFeed() call for
        // the life of the test process. Without this reset, Entry::getQuery() could read a
        // stale $element left over from a previous test, pointing at a section that no longer
        // exists once that test's transaction rolled back.
        Plugin::$plugin->elements->getRegisteredElement(EntryElement::class)->element = null;
    }

    /**
     * A minimal FeedModel mapping `slug` (used as the unique-match field) and `title` into
     * this test's section/entry type - matches the shape `Process::debugFeed()` builds from a
     * real feed config, but constructed directly so tests can drive it without a network fetch.
     */
    private function makeFeed(array $overrides = []): FeedModel
    {
        return new FeedModel(array_merge([
            'name' => 'Test feed',
            'feedUrl' => 'https://example.com/feed.xml',
            'feedType' => 'xml',
            'elementType' => EntryElement::class,
            'elementGroup' => [
                EntryElement::class => [
                    'section' => $this->section->id,
                    'entryType' => $this->entryType->id,
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
    private function runFeed(FeedModel $feed, array $rows): array
    {
        $processedElementIds = [];

        $this->service->beforeProcessFeed($feed, $rows);
        $settings = $this->service->getFeedSettings($feed, $rows);

        foreach ($rows as $key => $row) {
            $this->service->processFeed($key, $settings, $processedElementIds, $row);
        }

        $this->service->afterProcessFeed($settings, $feed, $processedElementIds);

        return $processedElementIds;
    }

    public function testAddsNewElement(): void
    {
        $feed = $this->makeFeed(['duplicateHandle' => ['add']]);

        $this->runFeed($feed, [
            ['slug' => 'fixed-slug', 'title' => 'Original Title'],
        ]);

        $entry = EntryElement::find()->status(null)->slug('fixed-slug')->one();
        $this->assertNotNull($entry);
        $this->assertSame('Original Title', $entry->title);
    }

    public function testUpdatesExistingElement(): void
    {
        $this->runFeed($this->makeFeed(['duplicateHandle' => ['add']]), [
            ['slug' => 'fixed-slug', 'title' => 'Original Title'],
        ]);
        $original = EntryElement::find()->status(null)->slug('fixed-slug')->one();

        $this->runFeed($this->makeFeed(['duplicateHandle' => ['update']]), [
            ['slug' => 'fixed-slug', 'title' => 'Updated Title'],
        ]);

        $updated = EntryElement::find()->status(null)->slug('fixed-slug')->one();
        $this->assertSame($original->id, $updated->id);
        $this->assertSame('Updated Title', $updated->title);
        $this->assertEquals(1, EntryElement::find()->status(null)->sectionId($this->section->id)->count());
    }

    public function testSkipsWhenAddOnlyAndElementExists(): void
    {
        $addFeed = $this->makeFeed(['duplicateHandle' => ['add']]);

        $this->runFeed($addFeed, [
            ['slug' => 'fixed-slug', 'title' => 'Original Title'],
        ]);

        // Same add-only strategy, matching row now has a different title - since an element
        // already exists and duplicateHandle is add-only, the row should be skipped entirely.
        $this->runFeed($addFeed, [
            ['slug' => 'fixed-slug', 'title' => 'Different Title'],
        ]);

        $entry = EntryElement::find()->status(null)->slug('fixed-slug')->one();
        $this->assertSame('Original Title', $entry->title);
    }

    public function testDisablesMissingElement(): void
    {
        $keep = ElementFactory::createEntry(['sectionId' => $this->section->id, 'typeId' => $this->entryType->id]);
        $missing = ElementFactory::createEntry(['sectionId' => $this->section->id, 'typeId' => $this->entryType->id]);

        // Only the "keep" row is present in this run's feed data - with a disable-only
        // strategy, anything else in this section/entry type that isn't matched gets disabled.
        $this->runFeed($this->makeFeed(['duplicateHandle' => ['disable']]), [
            ['slug' => $keep->slug, 'title' => $keep->title],
        ]);

        $this->assertFalse(EntryElement::find()->status(null)->id($missing->id)->one()->enabled);
        $this->assertTrue(EntryElement::find()->status(null)->id($keep->id)->one()->enabled);
    }

    public function testDeletesMissingElement(): void
    {
        $keep = ElementFactory::createEntry(['sectionId' => $this->section->id, 'typeId' => $this->entryType->id]);
        $missing = ElementFactory::createEntry(['sectionId' => $this->section->id, 'typeId' => $this->entryType->id]);

        $this->runFeed($this->makeFeed(['duplicateHandle' => ['delete']]), [
            ['slug' => $keep->slug, 'title' => $keep->title],
        ]);

        $this->assertNull(EntryElement::find()->status(null)->id($missing->id)->one());
        $this->assertNotNull(EntryElement::find()->status(null)->id($keep->id)->one());
    }

    public function testSkipsSaveWhenContentUnchanged(): void
    {
        $this->runFeed($this->makeFeed(['duplicateHandle' => ['update']]), [
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
            $this->runFeed($this->makeFeed(['duplicateHandle' => ['update']]), [
                ['slug' => 'fixed-slug', 'title' => 'Same Title'],
            ]);
        } finally {
            Craft::$app->getElements()->off(ElementsService::EVENT_BEFORE_SAVE_ELEMENT, $handler);
        }

        $this->assertFalse($saveEventFired);
    }
}
