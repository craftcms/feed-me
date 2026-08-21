<?php

namespace craft\feedme\tests\Feature\Fields;

use craft\elements\Entry as EntryElement;
use craft\feedme\fields\Entries;
use craft\feedme\tests\Helpers\ElementFactory;
use craft\feedme\tests\TestCase;
use craft\fields\Entries as EntriesField;

class EntriesFieldTest extends TestCase
{
    private Entries $service;

    private EntryElement $entry;

    protected function setUp(): void
    {
        parent::setUp();

        $this->entry = ElementFactory::createEntry();

        $this->service = new Entries();
        // `sources: '*'` means "search every section" - avoids having to resolve a specific
        // source UID for the field settings.
        $this->service->field = new EntriesField(['sources' => '*']);
        $this->service->feed = ['id' => 1, 'siteId' => null];
    }

    public function testMatchByTitle(): void
    {
        $this->service->fieldInfo = ['node' => 'related', 'options' => ['match' => 'title']];
        $this->service->feedData = ['related' => $this->entry->title];

        $this->assertSame([$this->entry->id], $this->service->parseField());
    }

    public function testMatchById(): void
    {
        $this->service->fieldInfo = ['node' => 'related', 'options' => ['match' => 'id']];
        $this->service->feedData = ['related' => (string)$this->entry->id];

        $this->assertSame([$this->entry->id], $this->service->parseField());
    }

    public function testNoMatchReturnsNull(): void
    {
        $this->service->fieldInfo = ['node' => 'related', 'options' => ['match' => 'title']];
        $this->service->feedData = ['related' => $this->entry->title . '-nonexistent'];

        // No matches found and nothing to fall back to - relation gets cleared, not left alone.
        $this->assertNull($this->service->parseField());
    }

    public function testEmptyValueReturnsEmptyArray(): void
    {
        $this->service->fieldInfo = ['node' => 'related', 'options' => ['match' => 'title']];
        $this->service->feedData = ['related' => ''];

        $this->assertSame([], $this->service->parseField());
    }

    public function testEmptyValueWithConfiguredDefaultFallsBackToDefault(): void
    {
        // When the feed value is empty but a default IS configured (and the node isn't the
        // 'usedefault' sentinel), the default is used as-is rather than either matching nothing
        // or - worse - matching every entry via an empty query criterion.
        // https://github.com/craftcms/feed-me/issues/1195
        $this->service->fieldInfo = [
            'node' => 'related',
            'default' => (string)$this->entry->id,
            'options' => ['match' => 'title'],
        ];
        $this->service->feedData = ['related' => ''];

        $this->assertSame([(string)$this->entry->id], $this->service->parseField());
    }
}
