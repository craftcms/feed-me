<?php

namespace craft\feedme\tests\Unit\Elements;

use craft\feedme\elements\Asset;
use craft\feedme\tests\UnitTestCase;

class AssetTest extends UnitTestCase
{
    private Asset $service;

    protected function setUp(): void
    {
        parent::setUp();

        $this->service = new Asset();
    }

    public function testParseFilenameFromAbsoluteUrl(): void
    {
        $feedMapping = ['attribute' => true, 'node' => 'image'];

        $this->assertSame(
            'photo.jpg',
            $this->service->parseAttribute(['image' => 'http://example.com/path/photo.jpg'], 'filename', $feedMapping),
        );
    }

    public function testParseFilenameFromLocalPath(): void
    {
        $feedMapping = ['attribute' => true, 'node' => 'image'];

        $this->assertSame(
            'photo.jpg',
            $this->service->parseAttribute(['image' => '/var/some/local/path/photo.jpg'], 'filename', $feedMapping),
        );
    }
}
