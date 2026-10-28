<?php

namespace craft\feedme\tests\Unit\Fields;

use craft\feedme\fields\Mapbox;
use craft\feedme\tests\Helpers\FieldServiceFactory;
use craft\fields\PlainText;

beforeEach(function() {
    // https://github.com/craftcms/feed-me/pull/1774 - the Mapbox plugin isn't installed in the
    // test environment, but parseField() only reads the mapping and feed data, never the field.
    $this->service = FieldServiceFactory::create(Mapbox::class, new PlainText());
});

test('mapping without sub fields returns null', function() {
    $this->service->fieldInfo = ['node' => 'address'];
    $this->service->feedData = ['address' => '1 Main St'];

    expect($this->service->parseField())->toBeNull();
});

test('all sub fields resolving empty returns null', function() {
    $this->service->fieldInfo = [
        'fields' => [
            'street1' => ['node' => 'street'],
            'city' => ['node' => 'city'],
        ],
    ];
    $this->service->feedData = ['somethingElse' => '1 Main St'];

    expect($this->service->parseField())->toBeNull();
});

test('mapped sub fields are returned keyed by handle', function() {
    $this->service->fieldInfo = [
        'fields' => [
            'street1' => ['node' => 'street'],
            'city' => ['node' => 'city'],
            'lat' => ['node' => 'lat'],
            'lng' => ['node' => 'lng'],
            'country' => ['node' => 'country'],
        ],
    ];
    $this->service->feedData = [
        'street' => '1 Main St',
        'city' => 'Portland',
        'lat' => '45.5152',
        'lng' => '-122.6784',
    ];

    expect($this->service->parseField())->toBe([
        'street1' => '1 Main St',
        'city' => 'Portland',
        'lat' => '45.5152',
        'lng' => '-122.6784',
    ]);
});
