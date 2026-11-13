<?php

namespace craft\feedme\tests\Unit\Helpers;

use craft\feedme\helpers\AssetHelper;

it('gets remote url extension', function() {
    expect(AssetHelper::getRemoteUrlExtension('http://example.com/image.jpg'))->toBe('jpg');
    expect(AssetHelper::getRemoteUrlExtension('http://example.com/image.jpg?width=1280&cid=5049'))->toBe('jpg');
    expect(AssetHelper::getRemoteUrlExtension('http://example.com/IMAGE.JPG'))->toBe('jpg');
});

it('gets remote url filename', function() {
    expect(AssetHelper::getRemoteUrlFilename('http://example.com/test.jpg'))->toBe('test.jpg');

    // A query string always gets folded into the filename (regardless of whether the
    // extension was resolvable), so the resulting asset name stays unique per URL.
    expect(AssetHelper::getRemoteUrlFilename('http://example.com/image.jpg?width=1280&cid=5049'))
        ->toBe('image-width1280cid5049.jpg');
});
