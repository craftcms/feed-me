<?php

namespace craft\feedme\tests\Unit\Fields;

use craft\elements\Entry;
use craft\feedme\fields\Matrix;
use craft\fieldlayoutelements\CustomField;
use craft\fields\Date;
use craft\fields\Matrix as MatrixField;
use craft\fields\PlainText;
use craft\helpers\StringHelper;
use craft\models\EntryType;
use craft\models\FieldLayout;
use craft\models\FieldLayoutTab;
use DateTime;
use ReflectionMethod;
use ReflectionProperty;

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

/**
 * Builds a Matrix field with a single in-memory `block` entry type that has a `text` (Plain Text)
 * and a `date` (Date) field, so parseField() can resolve the nested fields without the database.
 */
function matrixFieldWithTextAndDate(): MatrixField
{
    $fieldLayout = new FieldLayout(['type' => Entry::class]);
    $tab = new FieldLayoutTab(['layout' => $fieldLayout]);
    $tab->setElements([
        new CustomField(new PlainText(['handle' => 'text', 'uid' => StringHelper::UUID()])),
        new CustomField(new Date(['handle' => 'date', 'uid' => StringHelper::UUID()])),
    ]);
    $fieldLayout->setTabs([$tab]);

    $entryType = new EntryType(['handle' => 'block']);
    $entryType->setFieldLayout($fieldLayout);

    $field = new MatrixField(['handle' => 'matrix']);
    (new ReflectionProperty(MatrixField::class, '_entryTypes'))->setValue($field, [$entryType]);

    return $field;
}

/**
 * Parses `$feedData` into a `matrix` field, with the `block` entry type's `title`, `text` and
 * `date` mapped to the `title`, `text` and `date` nodes.
 */
function parseMatrix(array $feedData): mixed
{
    $service = new Matrix();
    $service->field = matrixFieldWithTextAndDate();
    $service->feed = ['id' => 1, 'siteId' => null, 'setEmptyValues' => false];
    $service->feedData = $feedData;
    $service->fieldInfo = [
        'blocks' => [
            'block' => [
                'attributes' => [
                    'title' => ['node' => 'matrix/title'],
                ],
                'fields' => [
                    'text' => ['node' => 'matrix/text', 'field' => PlainText::class],
                    'date' => ['node' => 'matrix/date', 'field' => Date::class, 'options' => ['match' => 'auto']],
                ],
            ],
        ],
    ];

    return $service->parseField();
}

test('a block with only a title is kept', function() {
    // https://github.com/craftcms/feed-me/issues/1626 - a nested entry that only had its title
    // set (with all its mapped custom fields empty) was treated as empty and not imported.
    $blocks = parseMatrix([
        'matrix/0/title' => 'Just a title',
        'matrix/0/text' => '',
    ]);

    expect($blocks)->toHaveCount(1);
    expect($blocks['new1']['title'])->toBe('Just a title');
});

test('a block with only a date is kept', function() {
    // https://github.com/craftcms/feed-me/issues/1707 - the Date field service returns a
    // DateTime object, which wasn't considered a non-empty value, so a nested entry whose only
    // value was a date was treated as empty and not imported.
    $blocks = parseMatrix([
        'matrix/0/text' => '',
        'matrix/0/date' => '2026-01-15',
    ]);

    expect($blocks)->toHaveCount(1);
    expect($blocks['new1']['fields']['date'])->toBeInstanceOf(DateTime::class);
});

test('a block with only empty values is ignored', function() {
    expect(parseMatrix(['matrix/0/text' => '']))->toBe([]);
});
