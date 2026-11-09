<?php

namespace craft\feedme\tests\Unit\Services;

use craft\feedme\fields\Assets;
use craft\feedme\fields\DefaultField;
use craft\feedme\Plugin;
use craft\fields\Assets as AssetsField;

test('create field returns field service instance', function() {
    $field = Plugin::$plugin->fields->createField(Assets::class);

    expect($field)->toBeInstanceOf(Assets::class);
});

test('get registered field returns service for known craft field class', function() {
    $field = Plugin::$plugin->fields->getRegisteredField(AssetsField::class);

    expect($field)->toBeInstanceOf(Assets::class);
});

test('get registered field falls back to default field for unknown handle', function() {
    $field = Plugin::$plugin->fields->getRegisteredField('not-a-real-handle');

    expect($field)->toBeInstanceOf(DefaultField::class);
});

test('get registered native field falls back to default field for unknown handle', function() {
    $field = Plugin::$plugin->fields->getRegisteredNativeField('not-a-real-handle');

    expect($field)->toBeInstanceOf(DefaultField::class);
});

test('fields list returns handle to display name map', function() {
    $list = Plugin::$plugin->fields->fieldsList();

    expect($list)->toHaveKey(AssetsField::class);
    expect($list[AssetsField::class])->toBe(Assets::$name);
});

test('get registered fields returns the post init registry not the raw class list', function() {
    // `getRegisteredFields()` is meant to build/return the raw list of field-service class
    // names to register (see the hardcoded list + EVENT_REGISTER_FEED_ME_FIELDS in its own
    // body), but its short-circuit cache (`$_fields`) is the exact same private property that
    // `init()` - which always runs first, since the plugin singleton is constructed once per
    // process - overwrites with a `[craftFieldClass => fieldServiceInstance]` map instead. So
    // by the time any test can call this, it no longer returns the class list at all - it
    // returns the post-init registry. Documenting the current behavior here.
    $registered = Plugin::$plugin->fields->getRegisteredFields();

    expect($registered)->toHaveKey(AssetsField::class);
    expect($registered[AssetsField::class])->toBeInstanceOf(Assets::class);
});
