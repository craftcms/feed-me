<?php

namespace craft\feedme\tests\Feature\Fields;

use Craft;
use craft\elements\Asset as AssetElement;
use craft\feedme\fields\Assets;
use craft\feedme\tests\Helpers\ElementFactory;
use craft\feedme\tests\Helpers\FieldServiceFactory;
use craft\fields\Assets as AssetsField;
use craft\helpers\FileHelper;
use craft\models\VolumeFolder;

it('finds an asset by id in a non-default volume when the field is not restricted', function() {
    // https://github.com/craftcms/feed-me/issues/1751 - matching by ID used to always scope
    // the query to the field's default upload volume, so an asset living in any other volume
    // the field was allowed to use was never found.
    $defaultVolume = ElementFactory::createVolume();
    $otherVolume = ElementFactory::createVolume();
    $asset = ElementFactory::createAsset($otherVolume);

    $field = new AssetsField([
        'restrictLocation' => false,
        'defaultUploadLocationSource' => 'volume:' . $defaultVolume->uid,
        'sources' => ['volume:' . $defaultVolume->uid, 'volume:' . $otherVolume->uid],
    ]);

    $service = FieldServiceFactory::create(Assets::class, $field);
    $service->fieldInfo = [
        'node' => 'image',
        'options' => ['match' => 'id'],
    ];
    $service->feedData = ['image' => (string)$asset->id];

    expect($service->parseField())->toBe([$asset->id]);
});

it('ignores an asset outside the restricted folder when matching by id', function() {
    // Guards against over-widening the #1751 fix: when the field IS restricted to a single
    // location, an otherwise-valid asset ID living in a different volume should not match.
    $restrictedVolume = ElementFactory::createVolume();
    $otherVolume = ElementFactory::createVolume();
    $asset = ElementFactory::createAsset($otherVolume);

    $field = new AssetsField([
        'restrictLocation' => true,
        'restrictedLocationSource' => 'volume:' . $restrictedVolume->uid,
    ]);

    $service = FieldServiceFactory::create(Assets::class, $field);
    $service->fieldInfo = [
        'node' => 'image',
        'options' => ['match' => 'id'],
    ];
    $service->feedData = ['image' => (string)$asset->id];

    expect($service->parseField())->toBeNull();
});

it('matches by id across multiple allowed volumes', function() {
    // Regression test for the array_merge($folderIds, $ids) follow-up fix in PR #1764 -
    // $folderIds starts out null, so merging folder IDs across two or more volumes must not
    // throw/behave incorrectly on the very first iteration.
    $volumeOne = ElementFactory::createVolume();
    $volumeTwo = ElementFactory::createVolume();
    $volumeThree = ElementFactory::createVolume();
    $asset = ElementFactory::createAsset($volumeThree);

    $field = new AssetsField([
        'restrictLocation' => false,
        'defaultUploadLocationSource' => 'volume:' . $volumeOne->uid,
        'sources' => [
            'volume:' . $volumeOne->uid,
            'volume:' . $volumeTwo->uid,
            'volume:' . $volumeThree->uid,
        ],
    ]);

    $service = FieldServiceFactory::create(Assets::class, $field);
    $service->fieldInfo = [
        'node' => 'image',
        'options' => ['match' => 'id'],
    ];
    $service->feedData = ['image' => (string)$asset->id];

    expect($service->parseField())->toBe([$asset->id]);
});

it('sets the native alt field via the sub-fields mechanism', function() {
    // https://github.com/craftcms/feed-me/issues/1727 - populating a native asset attribute
    // (like `alt`) through the Assets field's sub-fields mapping used to throw
    // "Invalid field: alt", because setAttributes() was called in safe mode.
    $volume = ElementFactory::createVolume();
    $asset = ElementFactory::createAsset($volume);

    $field = new AssetsField([
        'restrictLocation' => false,
        'defaultUploadLocationSource' => 'volume:' . $volume->uid,
        'sources' => ['volume:' . $volume->uid],
    ]);

    $service = FieldServiceFactory::create(Assets::class, $field);
    $service->fieldInfo = [
        'node' => 'image',
        'options' => ['match' => 'id'],
        'nativeFields' => [
            'alt' => ['node' => 'altText'],
        ],
    ];
    $service->feedData = [
        'image' => (string)$asset->id,
        'altText' => 'A description of the asset',
    ];

    $service->parseField();

    $updatedAsset = Craft::$app->getElements()->getElementById($asset->id);
    expect($updatedAsset->alt)->toBe('A description of the asset');
});

it('keeps the feed order when new assets are mixed with existing ones', function() {
    // https://github.com/craftcms/feed-me/issues/1722 - newly uploaded assets used to be
    // appended after all the existing ones, so the field's order (and any sub-field data,
    // which is applied by position) no longer matched the feed.
    $volume = ElementFactory::createVolume();
    $first = ElementFactory::createAsset($volume);
    $last = ElementFactory::createAsset($volume);

    $field = new AssetsField([
        'restrictLocation' => false,
        'defaultUploadLocationSource' => 'volume:' . $volume->uid,
        'sources' => ['volume:' . $volume->uid],
    ]);

    $service = FieldServiceFactory::create(Assets::class, $field, ['id' => 1, 'siteId' => null, 'updateSearchIndexes' => true]);
    $service->fieldInfo = [
        'node' => 'images',
        'options' => ['match' => 'filename', 'upload' => true, 'conflict' => AssetElement::SCENARIO_INDEX],
    ];
    $service->feedData = [
        'images/0' => $first->getFilename(),
        // a 1x1 PNG
        'images/1' => 'data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAYAAAAfFcSJAAAADUlEQVR42mNkYPhfDwAChwGA60e6kgAAAABJRU5ErkJggg==',
        'images/2' => $last->getFilename(),
    ];

    $ids = $service->parseField();

    expect($ids)->toHaveCount(3);
    expect($ids[0])->toBe($first->id);
    expect($ids[1])->not->toBeIn([$first->id, $last->id]);
    expect($ids[2])->toBe($last->id);
});

it('keeps the existing asset folder when replacing it', function() {
    // https://github.com/craftcms/feed-me/issues/1704 - with "Replace existing asset", an
    // existing asset in a subfolder was matched, but the new file was uploaded to the field's
    // default upload location instead of replacing the file in the existing asset's folder.
    $volume = ElementFactory::createVolume();
    $rootFolder = Craft::$app->getAssets()->getRootFolderByVolumeId($volume->id);
    $subfolder = new VolumeFolder([
        'parentId' => $rootFolder->id,
        'volumeId' => $volume->id,
        'name' => 'sub',
        'path' => 'sub/',
    ]);
    Craft::$app->getAssets()->createFolder($subfolder);
    $existing = ElementFactory::createAsset($volume, ['newFolderId' => $subfolder->id]);

    // fetchRemoteImage() reuses a file with the same name from Feed Me's temp folder instead of
    // downloading it, so the "remote" file doesn't need to exist.
    $url = 'https://example.com/' . $existing->getFilename();
    $tempFile = Craft::$app->getPath()->getTempPath() . '/feedme/' . $existing->getFilename();
    FileHelper::writeToFile($tempFile, 'replaced content');

    $field = new AssetsField([
        'restrictLocation' => false,
        'defaultUploadLocationSource' => 'volume:' . $volume->uid,
        'sources' => ['volume:' . $volume->uid],
    ]);

    $service = FieldServiceFactory::create(Assets::class, $field, ['id' => 1, 'siteId' => null, 'updateSearchIndexes' => true]);
    $service->fieldInfo = [
        'node' => 'image',
        'options' => ['match' => 'filename', 'upload' => true, 'conflict' => AssetElement::SCENARIO_REPLACE],
    ];
    $service->feedData = ['image' => $url];

    try {
        $ids = $service->parseField();
    } finally {
        @unlink($tempFile);
    }

    expect($ids)->toBe([$existing->id]);
    expect(AssetElement::find()->volumeId($volume->id)->count())->toEqual(1);
    $replaced = AssetElement::find()->id($existing->id)->one();
    expect($replaced->folderId)->toBe($subfolder->id);
    expect($replaced->getContents())->toBe('replaced content');
});
