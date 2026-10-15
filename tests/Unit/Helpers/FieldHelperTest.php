<?php

namespace craft\feedme\tests\Unit\Helpers;

use craft\feedme\helpers\FieldHelper;
use craft\fields\Matrix;
use craft\fields\PlainText;
use PHPUnit\Framework\TestCase;

class FieldHelperTest extends TestCase
{
    public function testFieldCanBeUniqueId(): void
    {
        // A real field object is classified by its class name.
        $this->assertTrue(FieldHelper::fieldCanBeUniqueId(new PlainText()));
        $this->assertFalse(FieldHelper::fieldCanBeUniqueId(new Matrix()));

        // A plain attribute array with no 'type' defaults to 'attribute', which is supported.
        $this->assertTrue(FieldHelper::fieldCanBeUniqueId([]));

        // The 'assets' shorthand value is explicitly supported. ('handle' must be present
        // alongside 'type' - the method assumes both keys are always set together.)
        $this->assertTrue(FieldHelper::fieldCanBeUniqueId(['type' => 'assets', 'handle' => 'assets']));

        // A 'parent' handle is always treated as supported, regardless of its declared type.
        $this->assertTrue(FieldHelper::fieldCanBeUniqueId(['type' => 'anything', 'handle' => 'parent']));

        $this->assertFalse(FieldHelper::fieldCanBeUniqueId(['type' => 'unsupported-type', 'handle' => 'someField']));

        // A value that isn't array-accessible (e.g. a plain object with no ArrayAccess
        // implementation) throws on `$field['type']` - caught by the method's own try/catch,
        // which returns false rather than ever reaching the is_object() classification below it.
        $this->assertFalse(FieldHelper::fieldCanBeUniqueId(new \stdClass()));
    }

    public function testFieldHasOnlyCustomSources(): void
    {
        $this->assertFalse(FieldHelper::fieldHasOnlyCustomSources(null));
        $this->assertFalse(FieldHelper::fieldHasOnlyCustomSources([]));

        // Single 'source' field (Categories/Tags-style).
        $this->assertFalse(FieldHelper::fieldHasOnlyCustomSources(['source' => 'group:some-uid']));
        $this->assertTrue(FieldHelper::fieldHasOnlyCustomSources(['source' => 'custom:some-uid']));

        // Multi 'sources' field (Entries/Users-style).
        $this->assertTrue(FieldHelper::fieldHasOnlyCustomSources(['sources' => ['custom:a', 'custom:b']]));
        $this->assertFalse(FieldHelper::fieldHasOnlyCustomSources(['sources' => ['custom:a', 'section:b']]));
    }
}
