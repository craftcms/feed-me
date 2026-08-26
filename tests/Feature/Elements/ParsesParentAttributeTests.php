<?php

namespace craft\feedme\tests\Feature\Elements;

use craft\base\Element;
use craft\feedme\base\Element as FeedMeElement;
use craft\feedme\tests\Helpers\ElementFactory;
use PHPUnit\Framework\Attributes\DataProvider;

/**
 * Shared `parseAttribute('parent', ...)` coverage for element types (Category, Entry) that
 * resolve their `parent` mapping the same way: match by title/id/slug, or fall back to a
 * configured default. Using classes provide the parent element and the service under test.
 */
trait ParsesParentAttributeTests
{
    abstract protected function parentMatchService(): FeedMeElement;

    abstract protected function parentElement(): Element;

    public static function parentMatchTypeProvider(): array
    {
        return [
            'title' => ['title'],
            'id' => ['id'],
            'slug' => ['slug'],
        ];
    }

    #[DataProvider('parentMatchTypeProvider')]
    public function testParentMatchesByType(string $matchType): void
    {
        $parent = $this->parentElement();
        $service = $this->parentMatchService();

        $feedMapping = [
            'attribute' => true,
            'node' => 'parent',
            'default' => '',
            'options' => ['match' => $matchType],
        ];

        $value = $matchType === 'id' ? (string)$parent->id : $parent->{$matchType};
        $feedData = ['parent' => $value];
        $this->assertEquals($parent->id, $service->parseAttribute($feedData, 'parent', $feedMapping));

        // Check invalid match.
        $invalidValue = $matchType === 'id'
            ? $parent->id + ElementFactory::NONEXISTENT_ID_OFFSET
            : $value . '-nonexistent';
        $feedData = ['parent' => $invalidValue];
        $this->assertNull($service->parseAttribute($feedData, 'parent', $feedMapping));
    }

    public function testParentDefault(): void
    {
        $parent = $this->parentElement();
        $service = $this->parentMatchService();

        $feedData = ['parent' => ''];

        $feedMapping = [
            'attribute' => true,
            'node' => 'parent',
            'default' => (string)$parent->id,
            'options' => ['match' => 'title'],
        ];
        $this->assertEquals($parent->id, $service->parseAttribute($feedData, 'parent', $feedMapping));

        $feedMapping['default'] = '';
        $this->assertNull($service->parseAttribute($feedData, 'parent', $feedMapping));
    }
}
