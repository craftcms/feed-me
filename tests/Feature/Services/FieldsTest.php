<?php

namespace craft\feedme\tests\Feature\Services;

use craft\feedme\Plugin;
use craft\fieldlayoutelements\CustomField;
use craft\fields\PlainText;
use craft\helpers\StringHelper;

it('returns null for a layout element whose field no longer exists', function() {
    // https://github.com/craftcms/feed-me/issues/1679 - a field that was deleted but is still
    // referenced by a field layout made the mapping screen throw a FieldNotFoundException.
    $layoutElement = new CustomField(null, ['fieldUid' => StringHelper::UUID()]);

    expect(Plugin::$plugin->fields->getFieldFromLayoutElement($layoutElement))->toBeNull();
});

it('returns the field for a layout element', function() {
    $layoutElement = new CustomField(new PlainText(['handle' => 'text', 'uid' => StringHelper::UUID()]));

    expect(Plugin::$plugin->fields->getFieldFromLayoutElement($layoutElement)->handle)->toBe('text');
});
