<?php

namespace craft\feedme\tests\Feature\Fields;

use craft\base\Element;
use craft\feedme\base\Field as FeedMeField;
use craft\feedme\tests\Helpers\ElementFactory;
use PHPUnit\Framework\Attributes\DataProvider;

/**
 * Shared `parseField()` coverage for relational field services (Users, Entries) that resolve a
 * match the same way: match by a configured attribute, or null/empty-array on no match/empty
 * value. Consuming classes provide the field service under test, the matched subject, and the
 * feed node name, and define their own `matchTypeProvider()` since the supported match types
 * differ per field (e.g. email/username/id vs title/id).
 */
trait MatchesRelatedByTypeTests
{
    abstract protected function relatedFieldService(): FeedMeField;

    abstract protected function relatedSubject(): Element;

    abstract protected function relatedFieldNode(): string;

    #[DataProvider('matchTypeProvider')]
    public function testMatchesByType(string $matchType): void
    {
        $subject = $this->relatedSubject();
        $service = $this->relatedFieldService();
        $node = $this->relatedFieldNode();

        $service->fieldInfo = ['node' => $node, 'options' => ['match' => $matchType]];
        $service->feedData = [$node => $matchType === 'id' ? (string)$subject->id : $subject->{$matchType}];

        $this->assertSame([$subject->id], $service->parseField());
    }

    public function testNoMatchReturnsNull(): void
    {
        $subject = $this->relatedSubject();
        $service = $this->relatedFieldService();
        $node = $this->relatedFieldNode();

        $service->fieldInfo = ['node' => $node, 'options' => ['match' => 'id']];
        $service->feedData = [$node => $subject->id + ElementFactory::NONEXISTENT_ID_OFFSET];

        // No matches found and nothing to fall back to - relation gets cleared, not left alone.
        $this->assertNull($service->parseField());
    }

    public function testEmptyValueReturnsEmptyArray(): void
    {
        $service = $this->relatedFieldService();
        $node = $this->relatedFieldNode();

        $service->fieldInfo = ['node' => $node, 'options' => ['match' => 'id']];
        $service->feedData = [$node => ''];

        $this->assertSame([], $service->parseField());
    }
}
