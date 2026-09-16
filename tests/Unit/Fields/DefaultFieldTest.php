<?php

namespace craft\feedme\tests\Unit\Fields;

use craft\feedme\fields\DefaultField;
use craft\feedme\tests\Helpers\FieldServiceFactory;
use craft\feedme\tests\UnitTestCase;
use craft\fields\PlainText;

class DefaultFieldTest extends UnitTestCase
{
    private DefaultField $service;

    protected function setUp(): void
    {
        parent::setUp();

        // DefaultField handles the generic case for simple field types - PlainText is a
        // representative stand-in with real normalizeValue()/serializeValue() behavior.
        $this->service = FieldServiceFactory::create(DefaultField::class, new PlainText(), ['setEmptyValues' => false]);
    }

    public function testArrayValueIsJsonEncoded(): void
    {
        $this->service->fieldInfo = ['node' => 'value'];
        $this->service->feedData = ['value' => ['a', 'b']];

        $this->assertSame('["a","b"]', $this->service->parseField());
    }

    public function testSetEmptyValuesReturnsEmptyStringNotNull(): void
    {
        // PlainText::normalizeValue() trims a whitespace-only value down to '' and then
        // returns null for it - without the fix, DefaultField would pass that null straight
        // through instead of the empty string `setEmptyValues` is meant to produce.
        // https://github.com/craftcms/feed-me/issues/1321
        // https://github.com/craftcms/feed-me/issues/1560
        $this->service->field = new PlainText();
        $this->service->feed = ['setEmptyValues' => true];
        $this->service->fieldInfo = ['node' => 'value'];
        $this->service->feedData = ['value' => ' '];

        $this->assertSame('', $this->service->parseField());
    }

    public function testWithoutSetEmptyValuesAWhitespaceOnlyValueBecomesNull(): void
    {
        $this->service->feed = ['setEmptyValues' => false];
        $this->service->fieldInfo = ['node' => 'value'];
        $this->service->feedData = ['value' => ' '];

        $this->assertNull($this->service->parseField());
    }
}
