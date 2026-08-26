<?php

namespace craft\feedme\tests\Unit\Helpers;

use craft\feedme\helpers\DuplicateHelper;
use PHPUnit\Framework\Attributes\DataProvider;
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

    public function testContainsHandlesNonArrayInput(): void
    {
        // `duplicateHandle` is nullable and never re-validated on the read path Process.php
        // uses, so a malformed/legacy feed row can reach here with it missing or null - this
        // should report "no match" rather than crash (`in_array()`/`count()` throw a TypeError
        // on a non-array $haystack).
        $this->assertFalse(DuplicateHelper::contains(null, 'add'));
        $this->assertFalse(DuplicateHelper::contains(null, 'add', true));
    }

    public static function isXMethodProvider(): array
    {
        return [
            'isAdd' => ['isAdd', 'add'],
            'isUpdate' => ['isUpdate', 'update'],
            'isDisable' => ['isDisable', 'disable'],
            'isDisableForSite' => ['isDisableForSite', 'disableForSite'],
            'isDelete' => ['isDelete', 'delete'],
        ];
    }

    #[DataProvider('isXMethodProvider')]
    public function testIsXMethodMatchesItsOwnHandle(string $method, string $handle): void
    {
        $this->assertTrue(DuplicateHelper::$method(['duplicateHandle' => [$handle]]));
        $this->assertFalse(DuplicateHelper::$method(['duplicateHandle' => ['add' === $handle ? 'update' : 'add']]));
    }

    #[DataProvider('isXMethodProvider')]
    public function testIsXMethodHandlesNullDuplicateHandle(string $method): void
    {
        $this->assertFalse(DuplicateHelper::$method(['duplicateHandle' => null]));
    }
}
