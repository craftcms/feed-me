<?php

namespace craft\feedme\tests\Feature\Helpers;

use craft\elements\Entry as EntryElement;
use craft\feedme\helpers\DataHelper;
use craft\feedme\tests\Helpers\ElementFactory;
use craft\feedme\tests\TestCase;

class DataHelperCompareTest extends TestCase
{
    public function testCompareElementContentTreatsEmptyValueAndMissingKeyTheSame(): void
    {
        // A field whose serialized value is `null` (present in the array, but empty) must still
        // be treated as "unchanged" against an incoming empty value - `Hash::check()` alone
        // misses this case because it doesn't consider a `null` leaf value "set".
        // https://github.com/craftcms/feed-me/issues/1615
        //
        // A real, section-backed entry is needed here (rather than a bare `new Entry()`) since
        // `compareElementContent()` touches things like the element's section along the way.
        $realEntry = ElementFactory::createEntry();

        $element = new class extends EntryElement {
            public function getSerializedFieldValues(?array $fieldHandles = null): array
            {
                return ['myField' => null];
            }
        };
        $element->sectionId = $realEntry->sectionId;
        $element->typeId = $realEntry->typeId;
        $element->siteId = $realEntry->siteId;

        $this->assertTrue(DataHelper::compareElementContent(['myField' => ''], $element));
    }
}
