<?php

namespace craft\feedme\tests\Unit\Services;

use craft\feedme\Plugin;
use craft\feedme\tests\UnitTestCase;

class DataTypesTest extends UnitTestCase
{
    public function testGetFeedNodes(): void
    {
        $data = [
            ['title' => 'One', 'tags' => ['News', 'Sports']],
            ['title' => 'Two', 'tags' => ['World']],
        ];

        $nodes = Plugin::$plugin->data->getFeedNodes($data);

        $this->assertSame('/root (x2 elements)', $nodes['']);
        // Only array-valued (repeatable) nodes get an entry - scalar leaf fields like `title`
        // don't, since this is meant to surface the feed's *repeatable* structure.
        $this->assertArrayHasKey('tags', $nodes);
        $this->assertArrayNotHasKey('title', $nodes);
    }

    public function testGetFeedNodesSingularElementCount(): void
    {
        $nodes = Plugin::$plugin->data->getFeedNodes([['title' => 'One']]);

        $this->assertSame('/root (x1 element)', $nodes['']);
    }

    public function testGetFeedMapping(): void
    {
        $data = [
            ['title' => 'One', 'tags' => ['News', 'Sports']],
        ];

        $mapping = Plugin::$plugin->data->getFeedMapping($data);

        // Numeric array-index segments (e.g. `0/title`, `0/tags/0`) are normalized out of the
        // path, so repeatable nodes are represented once regardless of their position in the feed.
        $this->assertSame('One', $mapping['title']);
        $this->assertSame('News', $mapping['tags']);
    }

    public function testFindPrimaryElement(): void
    {
        $parsed = ['channel' => ['item' => [['title' => 'One'], ['title' => 'Two']]]];

        // No element specified - returns the root as-is.
        $this->assertSame($parsed, Plugin::$plugin->data->findPrimaryElement(null, $parsed));

        // Found directly, but not "multidimensional" (no numeric `0` key) - wrapped in an array.
        $this->assertSame([$parsed['channel']], Plugin::$plugin->data->findPrimaryElement('channel', $parsed));

        // Found by recursing into a sub-array; already a list (has a `0` key), so returned as-is.
        $this->assertSame(
            $parsed['channel']['item'],
            Plugin::$plugin->data->findPrimaryElement('item', $parsed),
        );

        // Not found anywhere.
        $this->assertFalse(Plugin::$plugin->data->findPrimaryElement('nonexistent', $parsed));

        // Nothing to search at all.
        $this->assertFalse(Plugin::$plugin->data->findPrimaryElement('item', []));
    }
}
