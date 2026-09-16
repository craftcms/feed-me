<?php

namespace craft\feedme\tests\Unit\Fields;

use craft\feedme\fields\Link;
use craft\feedme\tests\Helpers\FieldServiceFactory;
use craft\feedme\tests\UnitTestCase;
use craft\fields\Link as LinkField;

class LinkFieldTest extends UnitTestCase
{
    private Link $service;

    protected function setUp(): void
    {
        parent::setUp();

        $this->service = FieldServiceFactory::create(Link::class, new LinkField());
    }

    public function testNoFieldsMappingReturnsNull(): void
    {
        $this->service->fieldInfo = ['node' => 'link'];
        $this->service->feedData = ['link' => 'https://example.com'];

        $this->assertNull($this->service->parseField());
    }

    public function testAllSubFieldsResolvingEmptyReturnsNull(): void
    {
        $this->service->fieldInfo = [
            'node' => 'link',
            'fields' => [
                'value' => ['node' => 'linkValue'],
            ],
        ];
        $this->service->feedData = ['somethingElse' => 'https://example.com'];

        $this->assertNull($this->service->parseField());
    }

    public function testTypeDefaultsToUrlWhenNotMapped(): void
    {
        // https://github.com/craftcms/feed-me/issues/1510 - Link values used to import
        // incorrectly because no link type was ever set on the prepped data.
        $this->service->fieldInfo = [
            'node' => 'link',
            'fields' => [
                'value' => ['node' => 'linkValue'],
            ],
        ];
        $this->service->feedData = ['linkValue' => 'https://example.com'];

        $result = $this->service->parseField();

        $this->assertSame('url', $result['type']);
        $this->assertSame('https://example.com', $result['value']);
    }

    public function testExplicitlyMappedTypeIsNotOverridden(): void
    {
        $this->service->fieldInfo = [
            'node' => 'link',
            'fields' => [
                'type' => ['node' => 'linkType'],
                'value' => ['node' => 'linkValue'],
            ],
        ];
        $this->service->feedData = [
            'linkType' => 'email',
            'linkValue' => 'person@example.com',
        ];

        $result = $this->service->parseField();

        $this->assertSame('email', $result['type']);
        $this->assertSame('person@example.com', $result['value']);
    }

    public function testLabelAndAdvancedFieldsPassThroughWhenMapped(): void
    {
        // https://github.com/craftcms/feed-me/issues/1754 - the Link field mapping used to
        // only expose a single generic value input; label/advanced sub-fields like `target`
        // now map through into the returned data.
        $this->service->fieldInfo = [
            'node' => 'link',
            'fields' => [
                'value' => ['node' => 'linkValue'],
                'label' => ['node' => 'linkLabel'],
                'target' => ['node' => 'linkTarget'],
            ],
        ];
        $this->service->feedData = [
            'linkValue' => 'https://example.com',
            'linkLabel' => 'Visit us',
            'linkTarget' => '_blank',
        ];

        $result = $this->service->parseField();

        $this->assertSame('https://example.com', $result['value']);
        $this->assertSame('Visit us', $result['label']);
        $this->assertSame('_blank', $result['target']);
    }
}
