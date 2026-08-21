<?php

namespace craft\feedme\tests\Unit\Helpers;

use craft\feedme\helpers\DuplicateHelper;
use PHPUnit\Framework\TestCase;

class DuplicateHelperTest extends TestCase
{
    public function testGetFriendly(): void
    {
        $this->assertSame('Add & Update', DuplicateHelper::getFriendly(['add', 'update']));
        $this->assertSame('Disable', DuplicateHelper::getFriendly(['disable']));
        $this->assertSame('', DuplicateHelper::getFriendly([]));
    }

    public function testContains(): void
    {
        $this->assertTrue(DuplicateHelper::contains(['add', 'update'], 'add'));
        $this->assertFalse(DuplicateHelper::contains(['add', 'update'], 'delete'));
    }

    public function testContainsOnly(): void
    {
        // With $only, the handle must be the sole entry in the list to match.
        $this->assertTrue(DuplicateHelper::contains(['add'], 'add', true));
        $this->assertFalse(DuplicateHelper::contains(['add', 'update'], 'add', true));
    }

    public function testIsAdd(): void
    {
        $this->assertTrue(DuplicateHelper::isAdd(['duplicateHandle' => ['add']]));
        $this->assertFalse(DuplicateHelper::isAdd(['duplicateHandle' => ['update']]));
    }

    public function testIsUpdate(): void
    {
        $this->assertTrue(DuplicateHelper::isUpdate(['duplicateHandle' => ['update']]));
        $this->assertFalse(DuplicateHelper::isUpdate(['duplicateHandle' => ['add']]));
    }

    public function testIsDisable(): void
    {
        $this->assertTrue(DuplicateHelper::isDisable(['duplicateHandle' => ['disable']]));
        $this->assertFalse(DuplicateHelper::isDisable(['duplicateHandle' => ['add']]));
    }

    public function testIsDisableForSite(): void
    {
        $this->assertTrue(DuplicateHelper::isDisableForSite(['duplicateHandle' => ['disableForSite']]));
        $this->assertFalse(DuplicateHelper::isDisableForSite(['duplicateHandle' => ['add']]));
    }

    public function testIsDelete(): void
    {
        $this->assertTrue(DuplicateHelper::isDelete(['duplicateHandle' => ['delete']]));
        $this->assertFalse(DuplicateHelper::isDelete(['duplicateHandle' => ['add']]));
    }
}
