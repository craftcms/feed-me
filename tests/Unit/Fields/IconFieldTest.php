<?php

namespace craft\feedme\tests\Unit\Fields;

use craft\feedme\fields\Icon;
use craft\feedme\tests\Helpers\FieldServiceFactory;
use craft\feedme\tests\UnitTestCase;
use craft\fields\Icon as IconField;

class IconFieldTest extends UnitTestCase
{
    private Icon $service;

    protected function setUp(): void
    {
        parent::setUp();

        $this->service = FieldServiceFactory::create(Icon::class, new IconField());
        $this->service->fieldInfo = ['node' => 'value'];
    }

    public function testNonEmptyValueRoundTripsThroughIconData(): void
    {
        $this->service->feed = ['setEmptyValues' => false];
        $this->service->feedData = ['value' => 'home'];

        $this->assertSame('home', $this->service->parseField());
    }

    public function testSetEmptyValuesAsIntOneReturnsEmptyString(): void
    {
        // Unlike DefaultField's truthy check, Icon's `setEmptyValues` check is written as a
        // strict `=== 1` comparison, so it's only guaranteed to take effect when the setting is
        // literally the integer 1 (as it is when read from a saved feed's settings).
        // https://github.com/craftcms/feed-me/issues/1321
        $this->service->feed = ['setEmptyValues' => 1];
        $this->service->feedData = ['value' => ''];

        $this->assertSame('', $this->service->parseField());
    }
}
