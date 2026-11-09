<?php

namespace craft\feedme\tests\Feature;

use Craft;

test('craft has a database', function() {
    expect(Craft::$app->getDb()->getIsActive())->toBeTrue();
});

test('feed me is installed', function() {
    expect(Craft::$app->plugins->getPlugin('feed-me'))->not->toBeNull();
});
