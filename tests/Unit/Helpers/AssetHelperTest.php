<?php

namespace craft\feedme\tests\Unit\Helpers;

use craft\feedme\helpers\AssetHelper;
use craft\feedme\tests\UnitTestCase;

class AssetHelperTest extends UnitTestCase
{
    public function testGetRemoteUrlExtension(): void
    {
        $this->assertSame('jpg', AssetHelper::getRemoteUrlExtension('http://example.com/image.jpg'));
        $this->assertSame('jpg', AssetHelper::getRemoteUrlExtension('http://example.com/image.jpg?width=1280&cid=5049'));
        $this->assertSame('jpg', AssetHelper::getRemoteUrlExtension('http://example.com/IMAGE.JPG'));
    }

    public function testGetRemoteUrlFilename(): void
    {
        $this->assertSame('test.jpg', AssetHelper::getRemoteUrlFilename('http://example.com/test.jpg'));

        // A query string always gets folded into the filename (regardless of whether the
        // extension was resolvable), so the resulting asset name stays unique per URL.
        $this->assertSame(
            'image-width1280cid5049.jpg',
            AssetHelper::getRemoteUrlFilename('http://example.com/image.jpg?width=1280&cid=5049'),
        );
    }
}
