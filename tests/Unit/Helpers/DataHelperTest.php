<?php

namespace craft\feedme\tests\Unit\Helpers;

use craft\feedme\helpers\DataHelper;
use craft\feedme\tests\UnitTestCase;

class DataHelperTest extends UnitTestCase
{
    public function testIsArrayValueEmpty(): void
    {
        $this->assertTrue(DataHelper::isArrayValueEmpty(null));
        $this->assertTrue(DataHelper::isArrayValueEmpty(''));
        $this->assertTrue(DataHelper::isArrayValueEmpty([]));
        $this->assertTrue(DataHelper::isArrayValueEmpty(['', null]));
        $this->assertFalse(DataHelper::isArrayValueEmpty(['a']));
        $this->assertFalse(DataHelper::isArrayValueEmpty('a'));

        // Zero is empty by default...
        $this->assertTrue(DataHelper::isArrayValueEmpty([0]));
        $this->assertTrue(DataHelper::isArrayValueEmpty(['0']));
        // ...unless $allowZero is set.
        $this->assertFalse(DataHelper::isArrayValueEmpty([0], true));
        $this->assertFalse(DataHelper::isArrayValueEmpty(['0'], true));
    }

    public function testFetchSimpleValue(): void
    {
        $fieldInfo = ['node' => 'title', 'default' => 'Default Title'];

        $this->assertSame('Feed Title', DataHelper::fetchSimpleValue(['title' => ' Feed Title '], $fieldInfo));
        $this->assertSame('Default Title', DataHelper::fetchSimpleValue(['title' => ''], $fieldInfo));
        $this->assertSame('Default Title', DataHelper::fetchSimpleValue(['title' => null], $fieldInfo));
        $this->assertNull(DataHelper::fetchSimpleValue([], ['node' => 'title', 'default' => '']));
    }

    public function testFetchDefaultArrayValue(): void
    {
        $this->assertSame([], DataHelper::fetchDefaultArrayValue(['default' => '']));
        $this->assertSame([], DataHelper::fetchDefaultArrayValue(['default' => null]));
        $this->assertSame(['1'], DataHelper::fetchDefaultArrayValue(['default' => '1']));
        $this->assertSame(['1', '2'], DataHelper::fetchDefaultArrayValue(['default' => ['1', '2']]));
    }

    public function testFetchArrayValue(): void
    {
        $fieldInfo = ['node' => 'tags'];

        $this->assertSame(['News'], DataHelper::fetchArrayValue(['tags' => 'News'], $fieldInfo));

        // The default `dataDelimiter` ('-|-') splits a single value into multiple.
        $this->assertSame(['News', 'Sports'], DataHelper::fetchArrayValue(['tags' => 'News-|-Sports'], $fieldInfo));

        // Trims whitespace around delimited values.
        $this->assertSame(['News', 'Sports'], DataHelper::fetchArrayValue(['tags' => ' News -|- Sports '], $fieldInfo));

        // Feed paths with numeric array segments (e.g. `MatrixBlock/0/Images`) match against
        // the mapped node (`MatrixBlock/Images`) with the numbers stripped out.
        $this->assertSame(
            ['one.jpg', 'two.jpg'],
            DataHelper::fetchArrayValue(
                ['MatrixBlock/0/Images' => 'one.jpg', 'MatrixBlock/1/Images' => 'two.jpg'],
                ['node' => 'MatrixBlock/Images'],
            ),
        );

        // The 'usedefault' node is a sentinel meaning "always use the configured default".
        $this->assertSame(
            ['fallback'],
            DataHelper::fetchArrayValue(['tags' => 'News'], ['node' => 'usedefault', 'default' => 'fallback']),
        );
    }

    public function testArrayCompare(): void
    {
        // No differences.
        $this->assertFalse(DataHelper::arrayCompare(['a' => 1], ['a' => 1]));

        // Changed value.
        $this->assertSame(
            [0 => ['a' => 1], 1 => ['a' => 2]],
            DataHelper::arrayCompare(['a' => 1], ['a' => 2]),
        );

        // Key only in the first array.
        $this->assertSame(
            [0 => ['a' => 1]],
            DataHelper::arrayCompare(['a' => 1], []),
        );

        // Key only in the second array.
        $this->assertSame(
            [1 => ['a' => 1]],
            DataHelper::arrayCompare([], ['a' => 1]),
        );

        // Recurses into nested arrays.
        $this->assertSame(
            [0 => ['a' => ['b' => 1]], 1 => ['a' => ['b' => 2]]],
            DataHelper::arrayCompare(['a' => ['b' => 1]], ['a' => ['b' => 2]]),
        );
    }

    public function testPrepValueForElementMatch(): void
    {
        // Plain values pass through unescaped by this method's return value (escapeParam mutates
        // in place for arrays, but returns the (escaped) value for strings).
        $this->assertSame('foo', DataHelper::prepValueForElementMatch('foo'));

        // The literal strings "or"/"and" are special in Craft's query syntax, so they get an
        // explicit `=` prefix (preserving original casing) to force an exact match rather than
        // being parsed as operators.
        $this->assertSame('=or', DataHelper::prepValueForElementMatch('or'));
        $this->assertSame('=AND', DataHelper::prepValueForElementMatch('AND'));
    }
}
