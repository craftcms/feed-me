<?php

namespace craft\feedme\tests\Unit\Services;

use craft\feedme\Plugin;

it('gets feed nodes', function() {
    $data = [
        ['title' => 'One', 'tags' => ['News', 'Sports']],
        ['title' => 'Two', 'tags' => ['World']],
    ];

    $nodes = Plugin::$plugin->data->getFeedNodes($data);

    expect($nodes[''])->toBe('/root (x2 elements)');
    // Only array-valued (repeatable) nodes get an entry - scalar leaf fields like `title`
    // don't, since this is meant to surface the feed's *repeatable* structure.
    expect($nodes)->toHaveKey('tags');
    expect($nodes)->not->toHaveKey('title');
});

it('gets feed nodes singular element count', function() {
    $nodes = Plugin::$plugin->data->getFeedNodes([['title' => 'One']]);

    expect($nodes[''])->toBe('/root (x1 element)');
});

it('gets feed mapping', function() {
    $data = [
        ['title' => 'One', 'tags' => ['News', 'Sports']],
    ];

    $mapping = Plugin::$plugin->data->getFeedMapping($data);

    // Numeric array-index segments (e.g. `0/title`, `0/tags/0`) are normalized out of the
    // path, so repeatable nodes are represented once regardless of their position in the feed.
    expect($mapping['title'])->toBe('One');
    expect($mapping['tags'])->toBe('News');
});

it('creates data type with unresolvable class', function() {
    // createDataType() is declared to return DataTypeInterface, and falls back to
    // `new MissingDataType($config)` when the configured class can't be resolved - but
    // MissingDataType only implements MissingComponentInterface, not DataTypeInterface (its
    // parent DataType doesn't implement it either). Pinning down what actually happens here:
    // PHP enforces declared return types at runtime regardless of the `@var` docblock, so
    // this is expected to throw rather than degrade gracefully - the same shape of bug
    // already found and documented in Fields::createField()/MissingField.
    Plugin::$plugin->data->createDataType('craft\\feedme\\tests\\NotARealDataType');
})->throws(\TypeError::class);

it('finds primary element', function() {
    $parsed = ['channel' => ['item' => [['title' => 'One'], ['title' => 'Two']]]];

    // No element specified - returns the root as-is.
    expect(Plugin::$plugin->data->findPrimaryElement(null, $parsed))->toBe($parsed);

    // Found directly, but not "multidimensional" (no numeric `0` key) - wrapped in an array.
    expect(Plugin::$plugin->data->findPrimaryElement('channel', $parsed))->toBe([$parsed['channel']]);

    // Found by recursing into a sub-array; already a list (has a `0` key), so returned as-is.
    expect(Plugin::$plugin->data->findPrimaryElement('item', $parsed))
        ->toBe($parsed['channel']['item']);

    // Not found anywhere.
    expect(Plugin::$plugin->data->findPrimaryElement('nonexistent', $parsed))->toBeFalse();

    // Nothing to search at all.
    expect(Plugin::$plugin->data->findPrimaryElement('item', []))->toBeFalse();
});
