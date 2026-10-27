<?php

namespace craft\feedme\tests\Unit\Fields;

use craft\feedme\fields\DefaultField;
use craft\feedme\tests\Helpers\FieldServiceFactory;
use craft\fields\PlainText;

beforeEach(function() {
    // DefaultField handles the generic case for simple field types - PlainText is a
    // representative stand-in with real normalizeValue()/serializeValue() behavior.
    $this->service = FieldServiceFactory::create(DefaultField::class, new PlainText(), ['setEmptyValues' => false]);
});

test('array value is json encoded', function() {
    $this->service->fieldInfo = ['node' => 'value'];
    $this->service->feedData = ['value' => ['a', 'b']];

    expect($this->service->parseField())->toBe('["a","b"]');
});

test('set empty values returns empty string not null', function() {
    // PlainText::normalizeValue() trims a whitespace-only value down to '' and then
    // returns null for it - without the fix, DefaultField would pass that null straight
    // through instead of the empty string `setEmptyValues` is meant to produce.
    // https://github.com/craftcms/feed-me/issues/1321
    // https://github.com/craftcms/feed-me/issues/1560
    $this->service->field = new PlainText();
    $this->service->feed = ['setEmptyValues' => true];
    $this->service->fieldInfo = ['node' => 'value'];
    $this->service->feedData = ['value' => ' '];

    expect($this->service->parseField())->toBe('');
});

test('without set empty values a whitespace only value becomes null', function() {
    $this->service->feed = ['setEmptyValues' => false];
    $this->service->fieldInfo = ['node' => 'value'];
    $this->service->feedData = ['value' => ' '];

    expect($this->service->parseField())->toBeNull();
});
