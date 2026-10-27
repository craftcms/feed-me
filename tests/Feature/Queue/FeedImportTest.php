<?php

namespace craft\feedme\tests\Feature\Queue;

use Craft;
use craft\db\Query;
use craft\elements\Entry as EntryElement;
use craft\feedme\events\FeedDataEvent;
use craft\feedme\events\FeedProcessEvent;
use craft\feedme\models\FeedModel;
use craft\feedme\Plugin;
use craft\feedme\queue\jobs\FeedImport;
use craft\feedme\records\SequencesRecord;
use craft\feedme\services\DataTypes;
use craft\feedme\services\Process;
use craft\feedme\tests\Helpers\ElementFactory;
use DateTime;
use yii\base\Event;
use yii\queue\sync\Queue as DummyQueue;

beforeEach(function() {
    $this->section = ElementFactory::createSection();
    $this->entryType = $this->section->getEntryTypes()[0];

    // Rows returned to the currently-running import via the EVENT_BEFORE_FETCH_FEED listener
    // below - lets tests supply feed content directly instead of a real HTTP/file fetch.
    $this->feedRows = [];

    // Same cross-test singleton reset ProcessTest needs - FeedImport::execute() drives the
    // same Entry element service singleton.
    Plugin::$plugin->elements->getRegisteredElement(EntryElement::class)->element = null;

    // DataTypes::getRawData() returns this event's `response` immediately if it's set, before
    // ever touching the filesystem or network - lets `$this->feedRows` stand in for a real fetch.
    $this->supplyFeedRows = function(FeedDataEvent $event) {
        $event->response = ['success' => true, 'data' => json_encode($this->feedRows)];
    };

    Event::on(DataTypes::class, DataTypes::EVENT_BEFORE_FETCH_FEED, $this->supplyFeedRows);
});

afterEach(function() {
    Event::off(DataTypes::class, DataTypes::EVENT_BEFORE_FETCH_FEED, $this->supplyFeedRows);
});

/**
 * Builds a JSON FeedModel mapping `slug` (used as the unique-match field) and `title` into
 * the current test's section/entry type.
 */
function makeFeed(array $overrides = []): FeedModel
{
    return new FeedModel(array_merge([
        'name' => 'Test feed',
        'feedUrl' => 'https://example.com/feed.json',
        'feedType' => 'json',
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
        'backup' => false,
        'setEmptyValues' => false,
    ], $overrides));
}

/**
 * Returns the most recently pushed queue job.
 */
function newestQueueJob(): FeedImport
{
    $id = (new Query())->select(['id'])->from('{{%queue}}')->orderBy(['id' => SORT_DESC])->limit(1)->scalar();

    return Craft::$app->getQueue()->getJobDetails((string)$id)['job'];
}

/**
 * `BaseBatchedJob::execute()` checks `$queue->isReserved($queue->getJobId())` between items
 * whenever `$queue instanceof \craft\queue\Queue` - which only makes sense mid a real
 * run()/handleMessage() cycle that reserves a job first. Calling execute() directly (as these
 * tests do, to drive the job synchronously without a worker) never reserves anything, so
 * `Craft::$app->getQueue()` itself can't be passed in here. A bare sync-driver Queue satisfies
 * the type hints `setProgress()`/`execute()` need without being an instance of Craft's own
 * Queue class, so neither check ever triggers - it's a dummy, not something jobs get pushed
 * through (FeedImport always pushes via `Queue::push()`, which targets
 * `Craft::$app->getQueue()` regardless of what's passed here).
 */
function runJob(FeedImport $job): void
{
    $job->execute(new DummyQueue());
}

it('is a no-op when there is no feed data', function() {
    $this->feedRows = [];
    $totalJobsBefore = Craft::$app->getQueue()->getTotalJobs();

    $job = new FeedImport(['feed' => makeFeed()]);
    runJob($job);

    expect(EntryElement::find()->status(null)->sectionId($this->section->id)->count())->toBe(0);
    expect(Craft::$app->getQueue()->getTotalJobs())->toBe($totalJobsBefore);
});

it('logs an empty feed under its own name', function() {
    // https://github.com/craftcms/feed-me/issues/1725 - the feed name used for logging was only
    // set once there was data to process, so an empty feed got logged under the previous
    // feed's name.
    $this->feedRows = [];
    Plugin::$feedName = 'Previous feed';

    runJob(new FeedImport(['feed' => makeFeed(['name' => 'Empty feed'])]));

    expect(Plugin::$feedName)->toBe('Empty feed');
});

it('imports all rows on execute', function() {
    $this->feedRows = [
        ['slug' => 'row-a', 'title' => 'Row A'],
        ['slug' => 'row-b', 'title' => 'Row B'],
    ];

    $job = new FeedImport(['feed' => makeFeed()]);
    runJob($job);

    expect(EntryElement::find()->status(null)->slug('row-a')->one()?->title)->toBe('Row A');
    expect(EntryElement::find()->status(null)->slug('row-b')->one()?->title)->toBe('Row B');
});

it('slices rows by offset and limit', function() {
    $this->feedRows = [
        ['slug' => 'row-a', 'title' => 'A'],
        ['slug' => 'row-b', 'title' => 'B'],
        ['slug' => 'row-c', 'title' => 'C'],
        ['slug' => 'row-d', 'title' => 'D'],
        ['slug' => 'row-e', 'title' => 'E'],
    ];

    // offset slices first, then limit - only rows b and c should make it through.
    $job = new FeedImport(['feed' => makeFeed(), 'offset' => 1, 'limit' => 2]);
    runJob($job);

    expect(EntryElement::find()->status(null)->slug('row-b')->one())->not->toBeNull();
    expect(EntryElement::find()->status(null)->slug('row-c')->one())->not->toBeNull();
    expect(EntryElement::find()->status(null)->slug('row-a')->one())->toBeNull();
    expect(EntryElement::find()->status(null)->slug('row-d')->one())->toBeNull();
    expect(EntryElement::find()->status(null)->slug('row-e')->one())->toBeNull();
});

it('rethrows when continueOnError is false', function() {
    // A row missing its mapped unique field's node crashes inside Element::parseSlug()'s
    // fallback slug generation: DataHelper::fetchSimpleValue() only applies the mapping's
    // configured default via `!empty($default)`, so an empty-string default (this feed's
    // `slug` mapping) is never applied either, leaving the value `null` - which _createSlug()
    // then rejects with a TypeError. A real, if narrow, edge case - reliable enough to drive
    // continueOnError's catch-all `Throwable` handling either way.
    $this->feedRows = [
        ['title' => 'Missing slug'],
    ];

    $job = new FeedImport(['feed' => makeFeed(), 'continueOnError' => false]);

    runJob($job);
})->throws(\TypeError::class);

it('swallows errors and continues when continueOnError is true', function() {
    $this->feedRows = [
        ['title' => 'Missing slug'],
        ['slug' => 'row-b', 'title' => 'Row B'],
    ];

    $job = new FeedImport(['feed' => makeFeed(), 'continueOnError' => true]);
    runJob($job);

    expect(EntryElement::find()->status(null)->slug('row-b')->one())->not->toBeNull();
});

it('lets the before process feed event cancel the import', function() {
    $handler = function(FeedProcessEvent $event) {
        $event->isValid = false;
    };

    $this->feedRows = [['slug' => 'row-a', 'title' => 'Row A']];

    Plugin::$plugin->process->on(Process::EVENT_BEFORE_PROCESS_FEED, $handler);

    try {
        $job = new FeedImport(['feed' => makeFeed()]);
        runJob($job);
    } finally {
        Plugin::$plugin->process->off(Process::EVENT_BEFORE_PROCESS_FEED, $handler);
    }

    expect(EntryElement::find()->status(null)->slug('row-a')->one())->toBeNull();
});

it('skips an invalid feed in a sequenced chain', function() {
    $this->feedRows = [];

    // The currently-running feed - doesn't need to be persisted, `SequencesRecord.feedId`
    // isn't foreign-keyed. Uses an ID no saved feed can get, since auto-increment values
    // aren't rolled back with each test's transaction.
    $currentFeed = makeFeed();
    $currentFeed->id = ElementFactory::NONEXISTENT_ID_OFFSET + 1;

    // The real next feed in the chain, persisted so getFeedById() can resolve it.
    $nextFeed = makeFeed(['name' => 'Next feed']);
    Plugin::$plugin->feeds->saveFeed($nextFeed);

    $now = new DateTime();
    (new SequencesRecord(['key' => 'seq', 'feedId' => $currentFeed->id, 'options' => '{}', 'timestamp' => $now]))->save(false);
    // An invalid hop in between - no feed with this ID exists.
    (new SequencesRecord(['key' => 'seq', 'feedId' => 999999, 'options' => json_encode(['limit' => null, 'offset' => null, 'continueOnError' => true]), 'timestamp' => $now]))->save(false);
    (new SequencesRecord(['key' => 'seq', 'feedId' => $nextFeed->id, 'options' => json_encode(['limit' => null, 'offset' => null, 'continueOnError' => true]), 'timestamp' => $now]))->save(false);

    $totalJobsBefore = Craft::$app->getQueue()->getTotalJobs();

    $job = new FeedImport(['feed' => $currentFeed]);
    runJob($job);

    expect(Craft::$app->getQueue()->getTotalJobs())->toBe($totalJobsBefore + 1);
    expect(newestQueueJob()->feed->id)->toBe($nextFeed->id);
});

it('keeps continueOnError on the pagination continuation job', function() {
    // `nextPage` is a plain top-level key rather than part of `items`, so it survives
    // primaryElement slicing (which runs after pagination-node lookup) and resolves as a
    // valid absolute URL, which is what getNextPagination() requires.
    $this->feedRows = [];
    $rawFeed = [
        'items' => [['slug' => 'row-a', 'title' => 'Row A']],
        'nextPage' => 'https://example.com/feed.json?page=2',
    ];

    Event::off(DataTypes::class, DataTypes::EVENT_BEFORE_FETCH_FEED, $this->supplyFeedRows);
    $handler = function(FeedDataEvent $event) use ($rawFeed) {
        $event->response = ['success' => true, 'data' => json_encode($rawFeed)];
    };
    Event::on(DataTypes::class, DataTypes::EVENT_BEFORE_FETCH_FEED, $handler);

    try {
        $feed = makeFeed(['primaryElement' => 'items', 'paginationNode' => 'nextPage']);
        $job = new FeedImport(['feed' => $feed, 'continueOnError' => false]);
        runJob($job);
    } finally {
        Event::off(DataTypes::class, DataTypes::EVENT_BEFORE_FETCH_FEED, $handler);
        Event::on(DataTypes::class, DataTypes::EVENT_BEFORE_FETCH_FEED, $this->supplyFeedRows);
    }

    $pushedJob = newestQueueJob();
    expect($pushedJob)->toBeInstanceOf(FeedImport::class);
    // The pagination continuation job must carry over continueOnError, rather than silently
    // resetting to the property default (true).
    expect($pushedJob->continueOnError)->toBeFalse();
});
