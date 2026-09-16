<?php

namespace craft\feedme\tests\Unit\Fields;

use craft\feedme\fields\Matrix;
use craft\feedme\tests\UnitTestCase;
use ReflectionMethod;

class MatrixFieldTest extends UnitTestCase
{
    private ReflectionMethod $getBlockKey;

    private Matrix $service;

    protected function setUp(): void
    {
        parent::setUp();

        $this->service = new Matrix();
        $this->getBlockKey = new ReflectionMethod(Matrix::class, '_getBlockKey');
        $this->getBlockKey->setAccessible(true);
    }

    private function getBlockKey(array $nodePathSegments, string $blockHandle, string $fieldHandle): string
    {
        return $this->getBlockKey->invoke($this->service, $nodePathSegments, $blockHandle, $fieldHandle);
    }

    public function testNumericSecondSegmentIsUsedDirectly(): void
    {
        // e.g. a feed path like `MatrixBlock/0/Images` -> block index 0.
        $this->assertSame(
            '0.blockHandle.fieldHandle',
            $this->getBlockKey(['MatrixBlock', '0', 'Images'], 'blockHandle', 'fieldHandle'),
        );

        $this->assertSame(
            '3.blockHandle.fieldHandle',
            $this->getBlockKey(['MatrixBlock', '3', 'Images'], 'blockHandle', 'fieldHandle'),
        );
    }

    public function testNonNumericSecondSegmentRecursesUntilItFindsOne(): void
    {
        // Each non-numeric segment gets stripped off the front and the search retries, until a
        // numeric segment is found or there's nothing meaningful left to check.
        $this->assertSame(
            '5.blockHandle.fieldHandle',
            $this->getBlockKey(['A', 'B', 'C', '5', 'D'], 'blockHandle', 'fieldHandle'),
        );
    }

    public function testRunningOutOfSegmentsDefaultsToIndexZero(): void
    {
        // Once fewer than 2 segments remain without ever finding a numeric one, it gives up and
        // assumes block index 0 rather than recursing forever.
        $this->assertSame(
            '0.blockHandle.fieldHandle',
            $this->getBlockKey(['MatrixBlock', 'Images'], 'blockHandle', 'fieldHandle'),
        );

        $this->assertSame(
            '0.blockHandle.fieldHandle',
            $this->getBlockKey(['MatrixBlock'], 'blockHandle', 'fieldHandle'),
        );
    }
}
