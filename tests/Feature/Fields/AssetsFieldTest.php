<?php

namespace craft\feedme\tests\Feature\Fields;

use craft\feedme\fields\Assets;
use craft\feedme\tests\Helpers\ElementFactory;
use craft\feedme\tests\Helpers\FieldServiceFactory;
use craft\feedme\tests\TestCase;
use craft\fields\Assets as AssetsField;

class AssetsFieldTest extends TestCase
{
    public function testMatchByIdFindsAssetInNonDefaultVolumeWhenFieldIsNotRestricted(): void
    {
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

        $this->assertSame([$asset->id], $service->parseField());
    }

    public function testMatchByIdIgnoresAssetOutsideTheRestrictedFolder(): void
    {
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

        $this->assertNull($service->parseField());
    }

    public function testMatchByIdAcrossMultipleAllowedVolumes(): void
    {
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

        $this->assertSame([$asset->id], $service->parseField());
    }

    public function testNativeAltFieldIsSetViaSubFieldsMechanism(): void
    {
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

        $updatedAsset = \Craft::$app->getElements()->getElementById($asset->id);
        $this->assertSame('A description of the asset', $updatedAsset->alt);
    }
}
