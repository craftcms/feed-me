<?php

namespace craft\feedme\tests\Unit\Base;

use craft\feedme\elements\Entry;
use Exception;
use PHPUnit\Framework\TestCase;

class ElementTest extends TestCase
{
    public function testMatchExistingElementThrowsForNonStringUniqueIdentifierValue(): void
    {
        // https://github.com/craftcms/feed-me/issues/1750 - a native Link field returns a
        // craft\fields\data\LinkData object (not a string) when mapped as the unique identifier,
        // which used to reach Db::escapeParam() and crash with a generic TypeError. It should now
        // throw an explicit, descriptive exception instead.
        $service = new Entry();

        $settings = [
            'fieldUnique' => ['someLinkField' => true],
            'singleton' => false,
        ];

        $data = ['someLinkField' => ['not', 'a', 'string']];

        $this->expectException(Exception::class);
        $this->expectExceptionMessage('Cannot match on a non-string value for someLinkField.');

        $service->matchExistingElement($data, $settings);
    }
}
