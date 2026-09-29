<?php

namespace craft\feedme\tests\Unit\Services;

use craft\feedme\fields\Assets;
use craft\feedme\fields\DefaultField;
use craft\feedme\Plugin;
use craft\feedme\tests\UnitTestCase;
use craft\fields\Assets as AssetsField;

class FieldsTest extends UnitTestCase
{
    public function testCreateFieldReturnsFieldServiceInstance(): void
    {
        $field = Plugin::$plugin->fields->createField(Assets::class);

        $this->assertInstanceOf(Assets::class, $field);
    }

    public function testGetRegisteredFieldReturnsServiceForKnownCraftFieldClass(): void
    {
        $field = Plugin::$plugin->fields->getRegisteredField(AssetsField::class);

        $this->assertInstanceOf(Assets::class, $field);
    }

    public function testGetRegisteredFieldFallsBackToDefaultFieldForUnknownHandle(): void
    {
        $field = Plugin::$plugin->fields->getRegisteredField('not-a-real-handle');

        $this->assertInstanceOf(DefaultField::class, $field);
    }

    public function testGetRegisteredNativeFieldFallsBackToDefaultFieldForUnknownHandle(): void
    {
        $field = Plugin::$plugin->fields->getRegisteredNativeField('not-a-real-handle');

        $this->assertInstanceOf(DefaultField::class, $field);
    }

    public function testFieldsListReturnsHandleToDisplayNameMap(): void
    {
        $list = Plugin::$plugin->fields->fieldsList();

        $this->assertArrayHasKey(AssetsField::class, $list);
        $this->assertSame(Assets::$name, $list[AssetsField::class]);
    }

    public function testGetRegisteredFieldsReturnsThePostInitRegistryNotTheRawClassList(): void
    {
        // `getRegisteredFields()` is meant to build/return the raw list of field-service class
        // names to register (see the hardcoded list + EVENT_REGISTER_FEED_ME_FIELDS in its own
        // body), but its short-circuit cache (`$_fields`) is the exact same private property that
        // `init()` - which always runs first, since the plugin singleton is constructed once per
        // process - overwrites with a `[craftFieldClass => fieldServiceInstance]` map instead. So
        // by the time any test can call this, it no longer returns the class list at all - it
        // returns the post-init registry. Documenting the current behavior here.
        $registered = Plugin::$plugin->fields->getRegisteredFields();

        $this->assertArrayHasKey(AssetsField::class, $registered);
        $this->assertInstanceOf(Assets::class, $registered[AssetsField::class]);
    }
}