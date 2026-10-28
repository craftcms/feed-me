<?php

namespace craft\feedme\tests\Unit\Helpers;

use craft\feedme\helpers\FieldHelper;
use craft\fields\Link;
use craft\fields\Matrix;
use craft\fields\PlainText;
use craft\fields\Url;

test('field can be unique id', function() {
    // A real field object is classified by its class name.
    expect(FieldHelper::fieldCanBeUniqueId(new PlainText()))->toBeTrue();
    expect(FieldHelper::fieldCanBeUniqueId(new Matrix()))->toBeFalse();

    // A plain attribute array with no 'type' defaults to 'attribute', which is supported.
    expect(FieldHelper::fieldCanBeUniqueId([]))->toBeTrue();

    // The 'assets' shorthand value is explicitly supported. ('handle' must be present
    // alongside 'type' - the method assumes both keys are always set together.)
    expect(FieldHelper::fieldCanBeUniqueId(['type' => 'assets', 'handle' => 'assets']))->toBeTrue();

    // A 'parent' handle is always treated as supported, regardless of its declared type.
    expect(FieldHelper::fieldCanBeUniqueId(['type' => 'anything', 'handle' => 'parent']))->toBeTrue();

    expect(FieldHelper::fieldCanBeUniqueId(['type' => 'unsupported-type', 'handle' => 'someField']))->toBeFalse();

    // A value that isn't array-accessible (e.g. a plain object with no ArrayAccess
    // implementation) throws on `$field['type']` - caught by the method's own try/catch,
    // which returns false rather than ever reaching the is_object() classification below it.
    expect(FieldHelper::fieldCanBeUniqueId(new \stdClass()))->toBeFalse();
});

test('url and link fields cannot be unique ids', function() {
    // https://github.com/craftcms/feed-me/issues/1750 - the Url field type maps to the Link field
    // (since Craft 5.3.0), whose value isn't a simple string, so it can't be used to match an
    // existing element.
    expect(FieldHelper::fieldCanBeUniqueId(new Url()))->toBeFalse();
    expect(FieldHelper::fieldCanBeUniqueId(new Link()))->toBeFalse();
});

test('field has only custom sources', function() {
    expect(FieldHelper::fieldHasOnlyCustomSources(null))->toBeFalse();
    expect(FieldHelper::fieldHasOnlyCustomSources([]))->toBeFalse();

    // Single 'source' field (Categories/Tags-style).
    expect(FieldHelper::fieldHasOnlyCustomSources(['source' => 'group:some-uid']))->toBeFalse();
    expect(FieldHelper::fieldHasOnlyCustomSources(['source' => 'custom:some-uid']))->toBeTrue();

    // Multi 'sources' field (Entries/Users-style).
    expect(FieldHelper::fieldHasOnlyCustomSources(['sources' => ['custom:a', 'custom:b']]))->toBeTrue();
    expect(FieldHelper::fieldHasOnlyCustomSources(['sources' => ['custom:a', 'section:b']]))->toBeFalse();
});
