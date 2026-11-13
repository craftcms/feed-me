<?php

namespace craft\feedme\tests\Unit\Helpers;

use craft\feedme\helpers\DataHelper;

test('is array value empty', function() {
    expect(DataHelper::isArrayValueEmpty(null))->toBeTrue();
    expect(DataHelper::isArrayValueEmpty(''))->toBeTrue();
    expect(DataHelper::isArrayValueEmpty([]))->toBeTrue();
    expect(DataHelper::isArrayValueEmpty(['', null]))->toBeTrue();
    expect(DataHelper::isArrayValueEmpty(['a']))->toBeFalse();
    expect(DataHelper::isArrayValueEmpty('a'))->toBeFalse();

    // Zero is empty by default...
    expect(DataHelper::isArrayValueEmpty([0]))->toBeTrue();
    expect(DataHelper::isArrayValueEmpty(['0']))->toBeTrue();
    // ...unless $allowZero is set.
    expect(DataHelper::isArrayValueEmpty([0], true))->toBeFalse();
    expect(DataHelper::isArrayValueEmpty(['0'], true))->toBeFalse();
});

it('fetches simple value', function() {
    $fieldInfo = ['node' => 'title', 'default' => 'Default Title'];

    expect(DataHelper::fetchSimpleValue(['title' => ' Feed Title '], $fieldInfo))->toBe('Feed Title');
    expect(DataHelper::fetchSimpleValue(['title' => ''], $fieldInfo))->toBe('Default Title');
    expect(DataHelper::fetchSimpleValue(['title' => null], $fieldInfo))->toBe('Default Title');
    expect(DataHelper::fetchSimpleValue([], ['node' => 'title', 'default' => '']))->toBeNull();
});

it('fetches default array value', function() {
    expect(DataHelper::fetchDefaultArrayValue(['default' => '']))->toBe([]);
    expect(DataHelper::fetchDefaultArrayValue(['default' => null]))->toBe([]);
    expect(DataHelper::fetchDefaultArrayValue(['default' => '1']))->toBe(['1']);
    expect(DataHelper::fetchDefaultArrayValue(['default' => ['1', '2']]))->toBe(['1', '2']);
});

it('fetches array value', function() {
    $fieldInfo = ['node' => 'tags'];

    expect(DataHelper::fetchArrayValue(['tags' => 'News'], $fieldInfo))->toBe(['News']);

    // The default `dataDelimiter` ('-|-') splits a single value into multiple.
    expect(DataHelper::fetchArrayValue(['tags' => 'News-|-Sports'], $fieldInfo))->toBe(['News', 'Sports']);

    // Trims whitespace around delimited values.
    expect(DataHelper::fetchArrayValue(['tags' => ' News -|- Sports '], $fieldInfo))->toBe(['News', 'Sports']);

    // Feed paths with numeric array segments (e.g. `MatrixBlock/0/Images`) match against
    // the mapped node (`MatrixBlock/Images`) with the numbers stripped out.
    expect(DataHelper::fetchArrayValue(
        ['MatrixBlock/0/Images' => 'one.jpg', 'MatrixBlock/1/Images' => 'two.jpg'],
        ['node' => 'MatrixBlock/Images'],
    ))->toBe(['one.jpg', 'two.jpg']);

    // The 'usedefault' node is a sentinel meaning "always use the configured default".
    expect(DataHelper::fetchArrayValue(['tags' => 'News'], ['node' => 'usedefault', 'default' => 'fallback']))
        ->toBe(['fallback']);
});

test('array compare', function() {
    // No differences.
    expect(DataHelper::arrayCompare(['a' => 1], ['a' => 1]))->toBeFalse();

    // Changed value.
    expect(DataHelper::arrayCompare(['a' => 1], ['a' => 2]))
        ->toBe([0 => ['a' => 1], 1 => ['a' => 2]]);

    // Key only in the first array.
    expect(DataHelper::arrayCompare(['a' => 1], []))
        ->toBe([0 => ['a' => 1]]);

    // Key only in the second array.
    expect(DataHelper::arrayCompare([], ['a' => 1]))
        ->toBe([1 => ['a' => 1]]);

    // Recurses into nested arrays.
    expect(DataHelper::arrayCompare(['a' => ['b' => 1]], ['a' => ['b' => 2]]))
        ->toBe([0 => ['a' => ['b' => 1]], 1 => ['a' => ['b' => 2]]]);
});

it('fetches value', function() {
    // Zero and "0" must be preserved rather than treated as empty.
    // https://github.com/craftcms/feed-me/issues/779
    expect(DataHelper::fetchValue(['count' => '0'], ['node' => 'count']))->toBe('0');
    expect(DataHelper::fetchValue(['count' => 0], ['node' => 'count']))->toBe(0);

    // A genuinely empty (non-numeric) value normalizes to null.
    expect(DataHelper::fetchValue(['count' => ''], ['node' => 'count']))->toBeNull();

    // A single-item array collapses to its scalar value.
    expect(DataHelper::fetchValue(['tags' => 'News'], ['node' => 'tags']))->toBe('News');

    // With `setEmptyValues` on, an explicit empty string is returned as-is (rather than
    // being normalized to null), so it can overwrite existing content. (`default` must be
    // an explicit empty string here too, otherwise the empty node value gets substituted
    // with an unset/null default before this check even runs.)
    expect(DataHelper::fetchValue(['title' => ''], ['node' => 'title', 'default' => ''], ['setEmptyValues' => true]))
        ->toBe('');
});

it('preps value for element match', function() {
    // Plain values pass through unescaped by this method's return value (escapeParam mutates
    // in place for arrays, but returns the (escaped) value for strings).
    expect(DataHelper::prepValueForElementMatch('foo'))->toBe('foo');

    // The literal strings "or"/"and" are special in Craft's query syntax, so they get an
    // explicit `=` prefix (preserving original casing) to force an exact match rather than
    // being parsed as operators.
    expect(DataHelper::prepValueForElementMatch('or'))->toBe('=or');
    expect(DataHelper::prepValueForElementMatch('AND'))->toBe('=AND');
});

it('preps non-string values for element match', function() {
    // https://github.com/craftcms/feed-me/issues/1645 - an array of values (e.g. multiple
    // related elements, as seen when using the Debug option) used to throw a TypeError, as it
    // was passed straight to `Db::escapeParam()` and `strcasecmp()`, which only take strings.
    expect(DataHelper::prepValueForElementMatch(['foo', 'or']))->toBe(['foo', 'or']);
    expect(DataHelper::prepValueForElementMatch(123))->toBe(123);
});
