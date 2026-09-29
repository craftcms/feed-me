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
use craft\feedme\tests\TestCase;
use craft\models\EntryType;
use craft\models\Section;
use DateTime;
use yii\base\Event;
use yii\queue\sync\Queue as DummyQueue;

class FeedImportTest extends TestCase
{
    private Section $section;

    private EntryType $entryType;

    /**
     * Rows returned to the currently-running import via the EVENT_BEFORE_FETCH_FEED listener
     * below - lets tests supply feed content directly instead of a real HTTP/file fetch.
     */
    private array $feedRows = [];

    protected function setUp(): void
    {
        parent::setUp();

        $this->section = ElementFactory::createSection();
        $this->entryType = $this->section->getEntryTypes()[0];

        // Same cross-test singleton reset ProcessTest needs - FeedImport::execute() drives the
        // same Entry element service singleton.
        Plugin::$plugin->elements->getRegisteredElement(EntryElement::class)->element = null;

        Event::on(DataTypes::class, DataTypes::EVENT_BEFORE_FETCH_FEED, [$this, 'supplyFeedRows']);
    }

    protected function tearDown(): void
    {
        Event::off(DataTypes::class, DataTypes::EVENT_BEFORE_FETCH_FEED, [$this, 'supplyFeedRows']);

        parent::tearDown();
    }

    /**
     * DataTypes::getRawData() returns this event's `response` immediately if it's set, before
     * ever touching the filesystem or network - lets `$this->feedRows` stand in for a real fetch.
     */
    public function supplyFeedRows(FeedDataEvent $event): void
    {
        $event->response = ['success' => true, 'data' => json_encode($this->feedRows)];
    }

    private function makeFeed(array $overrides = []): FeedModel
    {
        return new FeedModel(array_merge([
            'name' => 'Test feed',
            'feedUrl' => 'https://example.com/feed.json',
            'feedType' => 'json',
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
            'backup' => false,
            'setEmptyValues' => false,
        ], $overrides));
    }

    private function newestQueueJob(): FeedImport
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
    private function runJob(FeedImport $job): void
    {
        $job->execute(new DummyQueue());
    }

    public function testNoFeedDataIsANoOp(): void
    {
        $this->feedRows = [];
        $totalJobsBefore = Craft::$app->getQueue()->getTotalJobs();

        $job = new FeedImport(['feed' => $this->makeFeed()]);
        $this->runJob($job);

        $this->assertSame(0, EntryElement::find()->status(null)->sectionId($this->section->id)->count());
        $this->assertSame($totalJobsBefore, Craft::$app->getQueue()->getTotalJobs());
    }

    public function testExecuteImportsAllRows(): void
    {
        $this->feedRows = [
            ['slug' => 'row-a', 'title' => 'Row A'],
            ['slug' => 'row-b', 'title' => 'Row B'],
        ];

        $job = new FeedImport(['feed' => $this->makeFeed()]);
        $this->runJob($job);

        $this->assertSame('Row A', EntryElement::find()->status(null)->slug('row-a')->one()?->title);
        $this->assertSame('Row B', EntryElement::find()->status(null)->slug('row-b')->one()?->title);
    }

    public function testOffsetAndLimitSliceRows(): void
    {
        $this->feedRows = [
            ['slug' => 'row-a', 'title' => 'A'],
            ['slug' => 'row-b', 'title' => 'B'],
            ['slug' => 'row-c', 'title' => 'C'],
            ['slug' => 'row-d', 'title' => 'D'],
            ['slug' => 'row-e', 'title' => 'E'],
        ];

        // offset slices first, then limit - only rows b and c should make it through.
        $job = new FeedImport(['feed' => $this->makeFeed(), 'offset' => 1, 'limit' => 2]);
        $this->runJob($job);

        $this->assertNotNull(EntryElement::find()->status(null)->slug('row-b')->one());
        $this->assertNotNull(EntryElement::find()->status(null)->slug('row-c')->one());
        $this->assertNull(EntryElement::find()->status(null)->slug('row-a')->one());
        $this->assertNull(EntryElement::find()->status(null)->slug('row-d')->one());
        $this->assertNull(EntryElement::find()->status(null)->slug('row-e')->one());
    }

    public function testContinueOnErrorFalseRethrows(): void
    {
        // A row missing its mapped unique field's node crashes inside Element::parseSlug()'s
        // fallback slug generation: DataHelper::fetchSimpleValue() only applies the mapping's
        // configured default via `!empty($default)`, so an empty-string default (this feed's
        // `slug` mapping) is never applied either, leaving the value `null` - which _createSlug()
        // then rejects with a TypeError. A real, if narrow, edge case - reliable enough to drive
        // continueOnError's catch-all `Throwable` handling either way.
        $this->feedRows = [
            ['title' => 'Missing slug'],
        ];

        $job = new FeedImport(['feed' => $this->makeFeed(), 'continueOnError' => false]);

        $this->expectException(\TypeError::class);
        $this->runJob($job);
    }

    public function testContinueOnErrorTrueSwallowsAndContinues(): void
    {
        $this->feedRows = [
            ['title' => 'Missing slug'],
            ['slug' => 'row-b', 'title' => 'Row B'],
        ];

        $job = new FeedImport(['feed' => $this->makeFeed(), 'continueOnError' => true]);
        $this->runJob($job);

        $this->assertNotNull(EntryElement::find()->status(null)->slug('row-b')->one());
    }

    public function testBeforeProcessFeedEventCanCancelImport(): void
    {
        $handler = function(FeedProcessEvent $event) {
            $event->isValid = false;
        };

        $this->feedRows = [['slug' => 'row-a', 'title' => 'Row A']];

        Plugin::$plugin->process->on(Process::EVENT_BEFORE_PROCESS_FEED, $handler);

        try {
            $job = new FeedImport(['feed' => $this->makeFeed()]);
            $this->runJob($job);
        } finally {
            Plugin::$plugin->process->off(Process::EVENT_BEFORE_PROCESS_FEED, $handler);
        }

        $this->assertNull(EntryElement::find()->status(null)->slug('row-a')->one());
    }

    public function testSequencedFeedSkipsInvalidFeedInChain(): void
    {
        $this->feedRows = [];

        // The currently-running feed - doesn't need to be persisted, `SequencesRecord.feedId`
        // isn't foreign-keyed.
        $currentFeed = $this->makeFeed();
        $currentFeed->id = 555;

        // The real next feed in the chain, persisted so getFeedById() can resolve it.
        $nextFeed = $this->makeFeed(['name' => 'Next feed']);
        Plugin::$plugin->feeds->saveFeed($nextFeed);

        $now = new DateTime();
        (new SequencesRecord(['key' => 'seq', 'feedId' => $currentFeed->id, 'options' => '{}', 'timestamp' => $now]))->save(false);
        // An invalid hop in between - no feed with this ID exists.
        (new SequencesRecord(['key' => 'seq', 'feedId' => 999999, 'options' => json_encode(['limit' => null, 'offset' => null, 'continueOnError' => true]), 'timestamp' => $now]))->save(false);
        (new SequencesRecord(['key' => 'seq', 'feedId' => $nextFeed->id, 'options' => json_encode(['limit' => null, 'offset' => null, 'continueOnError' => true]), 'timestamp' => $now]))->save(false);

        $totalJobsBefore = Craft::$app->getQueue()->getTotalJobs();

        $job = new FeedImport(['feed' => $currentFeed]);
        $this->runJob($job);

        $this->assertSame($totalJobsBefore + 1, Craft::$app->getQueue()->getTotalJobs());
        $this->assertSame($nextFeed->id, $this->newestQueueJob()->feed->id);
    }

    public function testPaginationContinuationResetsContinueOnError(): void
    {
        // `nextPage` is a plain top-level key rather than part of `items`, so it survives
        // primaryElement slicing (which runs after pagination-node lookup) and resolves as a
        // valid absolute URL, which is what getNextPagination() requires.
        $this->feedRows = [];
        $rawFeed = [
            'items' => [['slug' => 'row-a', 'title' => 'Row A']],
            'nextPage' => 'https://example.com/feed.json?page=2',
        ];

        Event::off(DataTypes::class, DataTypes::EVENT_BEFORE_FETCH_FEED, [$this, 'supplyFeedRows']);
        $handler = function(FeedDataEvent $event) use ($rawFeed) {
            $event->response = ['success' => true, 'data' => json_encode($rawFeed)];
        };
        Event::on(DataTypes::class, DataTypes::EVENT_BEFORE_FETCH_FEED, $handler);

        try {
            $feed = $this->makeFeed(['primaryElement' => 'items', 'paginationNode' => 'nextPage']);
            $job = new FeedImport(['feed' => $feed, 'continueOnError' => false]);
            $this->runJob($job);
        } finally {
            Event::off(DataTypes::class, DataTypes::EVENT_BEFORE_FETCH_FEED, $handler);
            Event::on(DataTypes::class, DataTypes::EVENT_BEFORE_FETCH_FEED, [$this, 'supplyFeedRows']);
        }

        $pushedJob = $this->newestQueueJob();
        $this->assertInstanceOf(FeedImport::class, $pushedJob);
        // Documents current behavior - the pagination continuation job's constructor args don't
        // include continueOnError, so it silently resets to the property default (true) even
        // though this run's original job had it set to false.
        $this->assertTrue($pushedJob->continueOnError);
    }
}
