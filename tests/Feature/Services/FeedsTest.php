<?php

namespace craft\feedme\tests\Feature\Services;

use craft\elements\Entry as EntryElement;
use craft\feedme\errors\FeedException;
use craft\feedme\events\FeedEvent;
use craft\feedme\models\FeedModel;
use craft\feedme\Plugin;
use craft\feedme\services\Feeds;
use craft\feedme\tests\Helpers\ElementFactory;
use Exception;

beforeEach(function() {
    $this->service = Plugin::$plugin->feeds;
});

/**
 * Builds a minimal, saveable FeedModel.
 */
function makeFeed(array $overrides = []): FeedModel
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

it('creates a new feed with a sort order on save', function() {
    $feed = makeFeed();

    expect($this->service->saveFeed($feed))->toBeTrue();
    expect($feed->id)->not->toBeNull();
    expect($this->service->getFeedById($feed->id)->sortOrder)->toBe(1);
});

it('assigns an incrementing sort order on save', function() {
    $this->service->saveFeed(makeFeed());
    $second = makeFeed();
    $this->service->saveFeed($second);

    expect($this->service->getFeedById($second->id)->sortOrder)->toBe(2);
});

it('updates an existing feed by id on save', function() {
    $feed = makeFeed();
    $this->service->saveFeed($feed);

    $feed->name = 'Updated name';
    $this->service->saveFeed($feed);

    $updated = $this->service->getFeedById($feed->id);
    expect($updated->name)->toBe('Updated name');
    // Updating an existing feed doesn't touch its sortOrder - only assigned on create.
    expect($updated->sortOrder)->toBe(1);
});

it('throws when saving a feed with an unknown id', function() {
    $feed = makeFeed(['id' => ElementFactory::NONEXISTENT_ID_OFFSET]);

    $this->service->saveFeed($feed);
})->throws(Exception::class);

it('returns false without persisting when save validation fails', function() {
    $feed = makeFeed(['name' => '']);

    expect($this->service->saveFeed($feed))->toBeFalse();
    expect($feed->id)->toBeNull();
    expect($this->service->getTotalFeeds())->toBe(0);
});

it('skips validation on save when runValidation is false', function() {
    $feed = makeFeed(['name' => '']);

    expect($this->service->saveFeed($feed, false))->toBeTrue();
    expect($feed->id)->not->toBeNull();
});

it('forces the update duplicate handle for a singleton feed', function() {
    $feed = makeFeed(['singleton' => true, 'duplicateHandle' => ['add']]);
    $this->service->saveFeed($feed);

    expect($this->service->getFeedById($feed->id)->duplicateHandle)->toBe(['update']);
});

it('fires before and after save events on save', function() {
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
        $this->service->saveFeed(makeFeed());
    } finally {
        $this->service->off(Feeds::EVENT_BEFORE_SAVE_FEED, $beforeHandler);
        $this->service->off(Feeds::EVENT_AFTER_SAVE_FEED, $afterHandler);
    }

    expect($beforeIsNew)->toBeTrue();
    expect($afterIsNew)->toBeTrue();
});

it('returns null from getFeedById for a missing id', function() {
    expect($this->service->getFeedById(ElementFactory::NONEXISTENT_ID_OFFSET))->toBeNull();
});

it('returns feeds ordered by sort order', function() {
    $first = makeFeed(['name' => 'First']);
    $second = makeFeed(['name' => 'Second']);
    $this->service->saveFeed($first);
    $this->service->saveFeed($second);

    $names = array_map(fn($feed) => $feed->name, $this->service->getFeeds());

    expect($names)->toBe(['First', 'Second']);
});

it('removes the record on deleteFeedById', function() {
    $feed = makeFeed();
    $this->service->saveFeed($feed);

    expect($this->service->deleteFeedById($feed->id))->toBe(1);
    expect($this->service->getFeedById($feed->id))->toBeNull();
    // Deleting an already-gone feed affects nothing rather than erroring.
    expect($this->service->deleteFeedById($feed->id))->toBe(0);
});

it('creates a new feed with a different id and passkey on duplicate', function() {
    $feed = makeFeed();
    $this->service->saveFeed($feed);
    $originalId = $feed->id;
    $originalPasskey = $feed->passkey;

    $this->service->duplicateFeed($feed);

    expect($feed->id)->not->toBe($originalId);
    expect($feed->passkey)->not->toBe($originalPasskey);
    expect($this->service->getTotalFeeds())->toBe(2);
});

it('updates the sort order on reorderFeeds', function() {
    $first = makeFeed(['name' => 'First']);
    $second = makeFeed(['name' => 'Second']);
    $third = makeFeed(['name' => 'Third']);
    $this->service->saveFeed($first);
    $this->service->saveFeed($second);
    $this->service->saveFeed($third);

    $this->service->reorderFeeds([$third->id, $first->id, $second->id]);

    $names = array_map(fn($feed) => $feed->name, $this->service->getFeeds());
    expect($names)->toBe(['Third', 'First', 'Second']);
});

it('rolls back reorderFeeds on an invalid feed id', function() {
    $first = makeFeed(['name' => 'First']);
    $second = makeFeed(['name' => 'Second']);
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
    expect($names)->toBe(['First', 'Second']);
});
