<?php

namespace craft\feedme\tests\Unit\Fields;

use craft\feedme\fields\Addresses;
use ReflectionMethod;

beforeEach(function() {
    $this->service = new Addresses();
    $this->getNumberOfAddresses = new ReflectionMethod(Addresses::class, 'getNumberOfAddresses');
});

function getNumberOfAddresses(array $feedData, string $node): int
{
    test()->service->feedData = $feedData;

    return test()->getNumberOfAddresses->invoke(test()->service, [
        'nativeFields' => ['title' => ['node' => $node]],
    ]);
}

test('counts addresses with an index after each node path segment', function() {
    // https://github.com/craftcms/feed-me/issues/1715 - a mapped node like
    // `addresses/address/label` only matched the feed data when an index sat between every pair
    // of segments (or the segments were separated by double slashes).
    $feedData = [
        'addresses/address/0/label' => 'Home',
        'addresses/address/1/label' => 'Work',
    ];

    expect(getNumberOfAddresses($feedData, 'addresses/address/label'))->toBe(2);
});

test('counts addresses with multi digit indexes', function() {
    $feedData = [];
    for ($i = 0; $i < 12; $i++) {
        $feedData["addresses/$i/label"] = "Address $i";
    }

    expect(getNumberOfAddresses($feedData, 'addresses/label'))->toBe(12);
});
