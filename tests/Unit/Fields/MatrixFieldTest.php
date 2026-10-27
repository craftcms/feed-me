<?php

namespace craft\feedme\tests\Unit\Fields;

use craft\feedme\fields\Matrix;
use ReflectionMethod;

beforeEach(function() {
    $this->service = new Matrix();
    $this->getBlockKey = new ReflectionMethod(Matrix::class, '_getBlockKey');
    $this->getBlockKey->setAccessible(true);
});

function getBlockKey(array $nodePathSegments, string $blockHandle, string $fieldHandle): string
{
    return test()->getBlockKey->invoke(test()->service, $nodePathSegments, $blockHandle, $fieldHandle);
}

test('numeric second segment is used directly', function() {
    // e.g. a feed path like `MatrixBlock/0/Images` -> block index 0.
    expect(getBlockKey(['MatrixBlock', '0', 'Images'], 'blockHandle', 'fieldHandle'))
        ->toBe('0.blockHandle.fieldHandle');

    expect(getBlockKey(['MatrixBlock', '3', 'Images'], 'blockHandle', 'fieldHandle'))
        ->toBe('3.blockHandle.fieldHandle');
});

test('non numeric second segment recurses until it finds one', function() {
    // Each non-numeric segment gets stripped off the front and the search retries, until a
    // numeric segment is found or there's nothing meaningful left to check.
    expect(getBlockKey(['A', 'B', 'C', '5', 'D'], 'blockHandle', 'fieldHandle'))
        ->toBe('5.blockHandle.fieldHandle');
});

test('running out of segments defaults to index zero', function() {
    // Once fewer than 2 segments remain without ever finding a numeric one, it gives up and
    // assumes block index 0 rather than recursing forever.
    expect(getBlockKey(['MatrixBlock', 'Images'], 'blockHandle', 'fieldHandle'))
        ->toBe('0.blockHandle.fieldHandle');

    expect(getBlockKey(['MatrixBlock'], 'blockHandle', 'fieldHandle'))
        ->toBe('0.blockHandle.fieldHandle');
});
