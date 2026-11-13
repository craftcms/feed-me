<?php

namespace craft\feedme\tests\Unit\Helpers;

use craft\feedme\helpers\AssetHelper;
use craft\feedme\Plugin;

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

it('falls back to fopen when guzzle and curl are disabled', function() {
    $settings = Plugin::$plugin->getSettings();
    $settings->assetDownloadGuzzle = false;
    $settings->assetDownloadCurl = false;

    $src = tempnam(sys_get_temp_dir(), 'feedme-src');
    $dst = tempnam(sys_get_temp_dir(), 'feedme-dst');
    file_put_contents($src, 'feed me asset');

    try {
        expect(AssetHelper::downloadFile($src, $dst))->toBe(strlen('feed me asset'));
        expect(file_get_contents($dst))->toBe('feed me asset');
    } finally {
        $settings->assetDownloadGuzzle = true;
        $settings->assetDownloadCurl = false;
        @unlink($src);
        @unlink($dst);
    }
});

it('copies local files when guzzle is enabled', function() {
    $settings = Plugin::$plugin->getSettings();
    $settings->assetDownloadGuzzle = true;
    $settings->assetDownloadCurl = false;

    $src = tempnam(sys_get_temp_dir(), 'feedme-src');
    $dst = tempnam(sys_get_temp_dir(), 'feedme-dst');
    file_put_contents($src, 'feed me asset');

    try {
        expect(AssetHelper::downloadFile($src, $dst))->toBe(strlen('feed me asset'));
        expect(file_get_contents($dst))->toBe('feed me asset');
    } finally {
        @unlink($src);
        @unlink($dst);
    }
});
