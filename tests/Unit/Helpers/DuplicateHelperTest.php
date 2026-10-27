<?php

namespace craft\feedme\tests\Unit\Helpers;

use craft\feedme\helpers\DuplicateHelper;

it('gets friendly', function() {
    expect(DuplicateHelper::getFriendly(['add', 'update']))->toBe('Add & Update');
    expect(DuplicateHelper::getFriendly(['disable']))->toBe('Disable');
    expect(DuplicateHelper::getFriendly([]))->toBe('');
});

test('contains', function() {
    expect(DuplicateHelper::contains(['add', 'update'], 'add'))->toBeTrue();
    expect(DuplicateHelper::contains(['add', 'update'], 'delete'))->toBeFalse();
});

test('contains only', function() {
    // With $only, the handle must be the sole entry in the list to match.
    expect(DuplicateHelper::contains(['add'], 'add', true))->toBeTrue();
    expect(DuplicateHelper::contains(['add', 'update'], 'add', true))->toBeFalse();
});

test('is x method matches its own handle', function(string $method, string $handle) {
    expect(DuplicateHelper::$method(['duplicateHandle' => [$handle]]))->toBeTrue();
    expect(DuplicateHelper::$method(['duplicateHandle' => ['add' === $handle ? 'update' : 'add']]))->toBeFalse();
})->with([
    'isAdd' => ['isAdd', 'add'],
    'isUpdate' => ['isUpdate', 'update'],
    'isDisable' => ['isDisable', 'disable'],
    'isDisableForSite' => ['isDisableForSite', 'disableForSite'],
    'isDelete' => ['isDelete', 'delete'],
]);
