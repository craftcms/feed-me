<?php

namespace craft\feedme\tests\Unit\Fields;

use craft\feedme\fields\Icon;
use craft\feedme\tests\Helpers\FieldServiceFactory;
use craft\fields\Icon as IconField;

beforeEach(function() {
    $this->service = FieldServiceFactory::create(Icon::class, new IconField());
    $this->service->fieldInfo = ['node' => 'value'];
});

test('non empty value round trips through icon data', function() {
    $this->service->feed = ['setEmptyValues' => false];
    $this->service->feedData = ['value' => 'home'];

    expect($this->service->parseField())->toBe('home');
});

test('set empty values as int one returns empty string', function() {
    // Unlike DefaultField's truthy check, Icon's `setEmptyValues` check is written as a
    // strict `=== 1` comparison, so it's only guaranteed to take effect when the setting is
    // literally the integer 1 (as it is when read from a saved feed's settings).
    // https://github.com/craftcms/feed-me/issues/1321
    $this->service->feed = ['setEmptyValues' => 1];
    $this->service->feedData = ['value' => ''];

    expect($this->service->parseField())->toBe('');
});
