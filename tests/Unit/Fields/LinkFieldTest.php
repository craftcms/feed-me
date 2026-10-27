<?php

namespace craft\feedme\tests\Unit\Fields;

use craft\feedme\fields\Link;
use craft\feedme\tests\Helpers\FieldServiceFactory;
use craft\fields\Link as LinkField;

beforeEach(function() {
    $this->service = FieldServiceFactory::create(Link::class, new LinkField());
});

test('legacy mapping returns value as string', function() {
    // A flat mapping saved before Link fields had sub-field mappings is still supported,
    // and returns a string so that Craft can auto-detect the link type.
    $this->service->fieldInfo = ['node' => 'link'];
    $this->service->feedData = ['link' => 'https://example.com'];

    expect($this->service->parseField())->toBe('https://example.com');
});

test('all sub fields resolving empty returns null', function() {
    $this->service->fieldInfo = [
        'node' => 'link',
        'fields' => [
            'value' => ['node' => 'linkValue'],
        ],
    ];
    $this->service->feedData = ['somethingElse' => 'https://example.com'];

    expect($this->service->parseField())->toBeNull();
});

test('type defaults to url when not mapped', function() {
    // https://github.com/craftcms/feed-me/issues/1510 - Link values used to import
    // incorrectly because no link type was ever set on the prepped data.
    $this->service->fieldInfo = [
        'node' => 'link',
        'fields' => [
            'value' => ['node' => 'linkValue'],
        ],
    ];
    $this->service->feedData = ['linkValue' => 'https://example.com'];

    $result = $this->service->parseField();

    expect($result['type'])->toBe('url');
    expect($result['value'])->toBe('https://example.com');
});

test('explicitly mapped type is not overridden', function() {
    $this->service->fieldInfo = [
        'node' => 'link',
        'fields' => [
            'type' => ['node' => 'linkType'],
            'value' => ['node' => 'linkValue'],
        ],
    ];
    $this->service->feedData = [
        'linkType' => 'email',
        'linkValue' => 'person@example.com',
    ];

    $result = $this->service->parseField();

    expect($result['type'])->toBe('email');
    expect($result['value'])->toBe('person@example.com');
});

test('label and advanced fields pass through when mapped', function() {
    // https://github.com/craftcms/feed-me/issues/1754 - the Link field mapping used to
    // only expose a single generic value input; label/advanced sub-fields like `target`
    // now map through into the returned data.
    $this->service->fieldInfo = [
        'node' => 'link',
        'fields' => [
            'value' => ['node' => 'linkValue'],
            'label' => ['node' => 'linkLabel'],
            'target' => ['node' => 'linkTarget'],
        ],
    ];
    $this->service->feedData = [
        'linkValue' => 'https://example.com',
        'linkLabel' => 'Visit us',
        'linkTarget' => '_blank',
    ];

    $result = $this->service->parseField();

    expect($result['value'])->toBe('https://example.com');
    expect($result['label'])->toBe('Visit us');
    expect($result['target'])->toBe('_blank');
});
