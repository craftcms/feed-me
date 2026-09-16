<?php

namespace craft\feedme\tests\Feature\Base;

use craft\elements\Entry as EntryElement;
use craft\feedme\elements\Entry;
use craft\feedme\tests\Helpers\ElementFactory;
use craft\feedme\tests\TestCase;

class ElementTest extends TestCase
{
    public function testMatchExistingElementMatchesOnStringUniqueIdentifierValue(): void
    {
        // Companion to the #1750 regression test in Unit/Base/ElementTest.php - confirms the
        // fix (throwing only for non-string values) didn't also break matching on ordinary
        // string unique-identifier values, like `title`.
        $entry = ElementFactory::createEntry();

        $service = new Entry();
        $service->element = new EntryElement();

        $settings = [
            'fieldUnique' => ['title' => true],
            'singleton' => false,
            'elementGroup' => [
                EntryElement::class => [
                    'section' => $entry->sectionId,
                    'entryType' => $entry->typeId,
                ],
            ],
        ];

        $data = ['title' => $entry->title];

        $matched = $service->matchExistingElement($data, $settings);

        $this->assertNotNull($matched);
        $this->assertSame($entry->id, $matched->id);
    }
}
