<?php

namespace craft\feedme\tests\Feature\Services;

use craft\elements\Entry as EntryElement;
use craft\feedme\errors\FeedException;
use craft\feedme\events\FeedEvent;
use craft\feedme\models\FeedModel;
use craft\feedme\Plugin;
use craft\feedme\services\Feeds;
use craft\feedme\tests\Helpers\ElementFactory;
use craft\feedme\tests\TestCase;
use Exception;

class FeedsTest extends TestCase
{
    private Feeds $service;

    protected function setUp(): void
    {
        parent::setUp();

        $this->service = Plugin::$plugin->feeds;
    }

    private function makeFeed(array $overrides = []): FeedModel
    {
        return new FeedModel(array_merge([
            'name' => 'Test feed',
            'feedUrl' => 'https://example.com/feed.xml',
            'feedType' => 'xml',
            'elementType' => EntryElement::class,
            'duplicateHandle' => ['add'],
            'passkey' => 'test-passkey',
            // The `feedme_feeds.backup`/`setEmptyValues` columns are NOT NULL with a DB default
            // of `false`, but `FeedModel`'s own property default is `null` and `saveFeed()`
            // assigns it onto the record unconditionally - so a model built without these set
            // explicitly (as this factory would otherwise do) fails to save at all.
            'backup' => false,
            'setEmptyValues' => false,
        ], $overrides));
    }

    public function testSaveFeedCreatesNewFeedWithSortOrder(): void
    {
        $feed = $this->makeFeed();

        $this->assertTrue($this->service->saveFeed($feed));
        $this->assertNotNull($feed->id);
        $this->assertSame(1, $this->service->getFeedById($feed->id)->sortOrder);
    }

    public function testSaveFeedAssignsIncrementingSortOrder(): void
    {
        $this->service->saveFeed($this->makeFeed());
        $second = $this->makeFeed();
        $this->service->saveFeed($second);

        $this->assertSame(2, $this->service->getFeedById($second->id)->sortOrder);
    }

    public function testSaveFeedUpdatesExistingFeedById(): void
    {
        $feed = $this->makeFeed();
        $this->service->saveFeed($feed);

        $feed->name = 'Updated name';
        $this->service->saveFeed($feed);

        $updated = $this->service->getFeedById($feed->id);
        $this->assertSame('Updated name', $updated->name);
        // Updating an existing feed doesn't touch its sortOrder - only assigned on create.
        $this->assertSame(1, $updated->sortOrder);
    }

    public function testSaveFeedWithUnknownIdThrows(): void
    {
        $feed = $this->makeFeed(['id' => ElementFactory::NONEXISTENT_ID_OFFSET]);

        $this->expectException(Exception::class);
        $this->service->saveFeed($feed);
    }

    public function testSaveFeedValidationFailureReturnsFalseWithoutPersisting(): void
    {
        $feed = $this->makeFeed(['name' => '']);

        $this->assertFalse($this->service->saveFeed($feed));
        $this->assertNull($feed->id);
        $this->assertSame(0, $this->service->getTotalFeeds());
    }

    public function testSaveFeedSkipsValidationWhenRunValidationIsFalse(): void
    {
        $feed = $this->makeFeed(['name' => '']);

        $this->assertTrue($this->service->saveFeed($feed, false));
        $this->assertNotNull($feed->id);
    }

    public function testSingletonFeedForcesUpdateDuplicateHandle(): void
    {
        $feed = $this->makeFeed(['singleton' => true, 'duplicateHandle' => ['add']]);
        $this->service->saveFeed($feed);

        $this->assertSame(['update'], $this->service->getFeedById($feed->id)->duplicateHandle);
    }

    public function testSaveFeedFiresBeforeAndAfterSaveEvents(): void
    {
        $beforeIsNew = null;
        $afterIsNew = null;

        $beforeHandler = function(FeedEvent $event) use (&$beforeIsNew) {
            $beforeIsNew = $event->isNew;
        };
        $afterHandler = function(FeedEvent $event) use (&$afterIsNew) {
            $afterIsNew = $event->isNew;
        };

        // Attach/detach on the plugin's own singleton instance rather than via the static
        // `Event::on()` registry - the singleton (and any handler left on it) outlives this
        // single test for the whole process, so the handler must be removed again below to
        // avoid leaking into later tests.
        $this->service->on(Feeds::EVENT_BEFORE_SAVE_FEED, $beforeHandler);
        $this->service->on(Feeds::EVENT_AFTER_SAVE_FEED, $afterHandler);

        try {
            $this->service->saveFeed($this->makeFeed());
        } finally {
            $this->service->off(Feeds::EVENT_BEFORE_SAVE_FEED, $beforeHandler);
            $this->service->off(Feeds::EVENT_AFTER_SAVE_FEED, $afterHandler);
        }

        $this->assertTrue($beforeIsNew);
        $this->assertTrue($afterIsNew);
    }

    public function testGetFeedByIdReturnsNullForMissingId(): void
    {
        $this->assertNull($this->service->getFeedById(ElementFactory::NONEXISTENT_ID_OFFSET));
    }

    public function testGetFeedsReturnsFeedsOrderedBySortOrder(): void
    {
        $first = $this->makeFeed(['name' => 'First']);
        $second = $this->makeFeed(['name' => 'Second']);
        $this->service->saveFeed($first);
        $this->service->saveFeed($second);

        $names = array_map(fn($feed) => $feed->name, $this->service->getFeeds());

        $this->assertSame(['First', 'Second'], $names);
    }

    public function testDeleteFeedByIdRemovesRecord(): void
    {
        $feed = $this->makeFeed();
        $this->service->saveFeed($feed);

        $this->assertSame(1, $this->service->deleteFeedById($feed->id));
        $this->assertNull($this->service->getFeedById($feed->id));
        // Deleting an already-gone feed affects nothing rather than erroring.
        $this->assertSame(0, $this->service->deleteFeedById($feed->id));
    }

    public function testDuplicateFeedCreatesNewFeedWithDifferentIdAndPasskey(): void
    {
        $feed = $this->makeFeed();
        $this->service->saveFeed($feed);
        $originalId = $feed->id;
        $originalPasskey = $feed->passkey;

        $this->service->duplicateFeed($feed);

        $this->assertNotSame($originalId, $feed->id);
        $this->assertNotSame($originalPasskey, $feed->passkey);
        $this->assertSame(2, $this->service->getTotalFeeds());
    }

    public function testReorderFeedsUpdatesSortOrder(): void
    {
        $first = $this->makeFeed(['name' => 'First']);
        $second = $this->makeFeed(['name' => 'Second']);
        $third = $this->makeFeed(['name' => 'Third']);
        $this->service->saveFeed($first);
        $this->service->saveFeed($second);
        $this->service->saveFeed($third);

        $this->service->reorderFeeds([$third->id, $first->id, $second->id]);

        $names = array_map(fn($feed) => $feed->name, $this->service->getFeeds());
        $this->assertSame(['Third', 'First', 'Second'], $names);
    }

    public function testReorderFeedsRollsBackOnInvalidFeedId(): void
    {
        $first = $this->makeFeed(['name' => 'First']);
        $second = $this->makeFeed(['name' => 'Second']);
        $this->service->saveFeed($first);
        $this->service->saveFeed($second);

        try {
            $this->service->reorderFeeds([$second->id, ElementFactory::NONEXISTENT_ID_OFFSET, $first->id]);
            $this->fail('Expected a FeedException to be thrown.');
        } catch (FeedException) {
            // expected
        }

        // No partial writes - sortOrder unchanged from the original save order.
        $names = array_map(fn($feed) => $feed->name, $this->service->getFeeds());
        $this->assertSame(['First', 'Second'], $names);
    }
}
