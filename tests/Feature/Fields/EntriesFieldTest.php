<?php

namespace craft\feedme\tests\Feature\Fields;

use craft\elements\Entry as EntryElement;
use craft\feedme\fields\Entries;
use craft\feedme\tests\Helpers\ElementFactory;
use craft\feedme\tests\Helpers\FieldServiceFactory;
use craft\feedme\tests\TestCase;
use craft\fields\Entries as EntriesField;
use PHPUnit\Framework\Attributes\DataProvider;

class EntriesFieldTest extends TestCase
{
    private Entries $service;

    private EntryElement $entry;

    protected function setUp(): void
    {
        parent::setUp();

        $this->entry = ElementFactory::createEntry();

        // `sources: '*'` means "search every section" - avoids having to resolve a specific
        // source UID for the field settings.
        $this->service = FieldServiceFactory::create(Entries::class, new EntriesField(['sources' => '*']));
    }

    public static function matchTypeProvider(): array
    {
        return [
            'title' => ['title'],
            'id' => ['id'],
        ];
    }

    #[DataProvider('matchTypeProvider')]
    public function testMatchesByType(string $matchType): void
    {
        $this->service->fieldInfo = ['node' => 'related', 'options' => ['match' => $matchType]];
        $this->service->feedData = ['related' => $matchType === 'id' ? (string)$this->entry->id : $this->entry->title];

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
