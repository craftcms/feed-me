<?php

namespace craft\feedme\helpers;

use Cake\Utility\Hash;
use Craft;
use craft\base\ElementInterface;
use craft\base\Field;
use craft\elements\Asset as AssetElement;
use craft\errors\ElementNotFoundException;
use craft\feedme\Plugin;
use craft\fields\Assets;
use craft\helpers\Assets as AssetsHelper;
use craft\helpers\FileHelper;
use craft\helpers\Json;
use craft\helpers\StringHelper;
use craft\helpers\UrlHelper;
use Throwable;
use yii\base\Exception;
use yii\base\InvalidArgumentException;

class AssetHelper
{
    // Public Methods
    // =========================================================================

    /**
     * @param $srcName
     * @param $dstName
     * @param int $chunkSize
     * @param bool $returnbytes
     * @return bool|int
     */
    public static function downloadFile($srcName, $dstName, int $chunkSize = 1, bool $returnbytes = true): bool|int
    {
        $assetDownloadCurl = Plugin::$plugin->getSettings()->assetDownloadCurl;

        // Provide some legacy support
        if ($assetDownloadCurl) {
            $ch = curl_init($srcName);
            $fp = fopen($dstName, 'wb');

            curl_setopt($ch, CURLOPT_FILE, $fp);
            curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);

            curl_exec($ch);
            curl_close($ch);

            return fclose($fp);
        }

        $newChunkSize = $chunkSize * (1024 * 1024);
        $bytesCount = 0;
        $handle = fopen($srcName, 'rb');
        $fp = fopen($dstName, 'wb');

        if ($handle === false) {
            return false;
        }

        while (!feof($handle)) {
            $data = fread($handle, $newChunkSize);
            fwrite($fp, $data, strlen($data));

            if ($returnbytes) {
                $bytesCount += strlen($data);
            }
        }

        $status = fclose($handle);

        fclose($fp);

        if ($returnbytes && $status) {
            return $bytesCount;
        }

        return $status;
    }

    /**
     * @param array $urls
     * @param $fieldInfo
     * @param $feed
     * @param null $field
     * @param null $element
     * @param null $folderId
     * @param null $newFilename
     * @return array
     * @throws Exception
     */
    public static function fetchRemoteImage(array $urls, $fieldInfo, $feed, $field = null, $element = null, $folderId = null, $newFilename = null): array
    {
        $uploadedAssets = [];

        $conflict = Hash::get($fieldInfo, 'options.conflict');

        $tempFeedMePath = self::createTempFeedMePath();

        // Download each image. Note we've already checked if there's an existing asset and if the
        // user has set to use that instead, so we're good to proceed.
        foreach ($urls as $url) {
            try {
                $filename = $newFilename ? AssetsHelper::prepareAssetName($newFilename, false) : self::getRemoteUrlFilename($url);

                $fetchedImage = $tempFeedMePath . $filename;

                // But also check if we've downloaded this recently, use the copy in the temp directory
                $cachedImage = FileHelper::findFiles($tempFeedMePath, [
                    'only' => [$filename],
                    'recursive' => false,
                ]);

                Plugin::info('Fetching remote image `{i}` - `{j}`', ['i' => $url, 'j' => $filename]);

                if (!$cachedImage) {
                    self::downloadFile($url, $fetchedImage);
                } else {
                    $fetchedImage = $cachedImage[0];
                }

                $result = self::createAsset($fetchedImage, $filename, $folderId, $field, $element, $conflict, Hash::get($feed, 'updateSearchIndexes'));

                if ($result) {
                    $uploadedAssets[] = $result;
                } else {
                    Plugin::error('Failed to create asset from `{i}`', ['i' => $url]);
                }
            } catch (Throwable $e) {
                if ($field) {
                    Plugin::error('`{handle}` - Asset error: `{url}` - `{e}`.', ['url' => $url, 'e' => $e->getMessage(), 'handle' => $field->handle]);
                } else {
                    Plugin::error('Asset error: `{url}` - `{e}`.', ['url' => $url, 'e' => $e->getMessage()]);
                }
                Craft::$app->getErrorHandler()->logException($e);
            }
        }

        return $uploadedAssets;
    }

    /**
     * @param $base64
     * @param $fieldInfo
     * @param $feed
     * @param null $field
     * @param null $element
     * @param null $folderId
     * @return array
     * @throws Exception
     */
    public static function createBase64Image($base64, $fieldInfo, $feed, $field = null, $element = null, $folderId = null): array
    {
        $uploadedAssets = [];
        $fetchedImageWithExtension = '';

        $conflict = Hash::get($fieldInfo, 'options.conflict');

        $tempFeedMePath = self::createTempFeedMePath();

        // Download each image. Note we've already checked if there's an existing asset and if the
        // user has set to use that instead, so we're good to proceed.
        foreach ($base64 as $dataString) {
            try {
                // Remove leading "data:mime/type;base64," string.
                [, $encodedString] = explode(',', $dataString);

                $filename = md5($encodedString);
                $fetchedImage = $tempFeedMePath . $filename;

                // Decode string and write to file.
                $decodedImage = base64_decode($encodedString);
                FileHelper::writeToFile($fetchedImage, $decodedImage);

                // Get mime type and create file with proper file extension.
                $mimeType = FileHelper::getMimeType($fetchedImage, null, false);
                $extensions = FileHelper::getExtensionsByMimeType($mimeType);
                $filename .= '.' . $extensions[0];
                $fetchedImageWithExtension = $tempFeedMePath . $filename;
                FileHelper::writeToFile($fetchedImageWithExtension, $decodedImage);

                $result = self::createAsset($fetchedImageWithExtension, $filename, $folderId, $field, $element, $conflict, Hash::get($feed, 'updateSearchIndexes'));

                if ($result) {
                    $uploadedAssets[] = $result;
                } else {
                    Plugin::error('Failed to create asset from `{i}`', ['i' => $dataString]);
                }
            } catch (Throwable $e) {
                Plugin::error('Base64 error: `{url}` - `{e}`.', ['url' => $fetchedImageWithExtension, 'e' => $e->getMessage()]);
                Craft::$app->getErrorHandler()->logException($e);
                echo $e->getMessage();
            }
        }

        return $uploadedAssets;
    }

    /**
     * @param string $tempFilePath
     * @param string $filename
     * @param int|null $folderId
     * @param Assets|null $field
     * @param ElementInterface|null $element
     * @param string $conflict
     * @param bool $updateSearchIndexes
     * @return bool|int
     * @throws ElementNotFoundException
     * @throws Exception
     * @throws Throwable
     * @throws \craft\errors\VolumeException
     * @throws \yii\base\InvalidConfigException
     */
    private static function createAsset(string $tempFilePath, string $filename, ?int $folderId, ?Assets $field, ?ElementInterface $element, string $conflict, bool $updateSearchIndexes): bool|int
    {
        $assets = Craft::$app->getAssets();

        if (!$folderId) {
            if (!$field) {
                throw new InvalidArgumentException('$folderId and $field cannot both be null.');
            }
            $folderId = $field->resolveDynamicPathToFolderId($element);
        }

        $folder = $assets->findFolder(['id' => $folderId]);

        // Create the new asset (even if we're setting it to replace)
        $asset = new AssetElement();
        $asset->tempFilePath = $tempFilePath;
        $asset->filename = $filename;
        $asset->newFolderId = $folder->id;
        $asset->volumeId = $folder->volumeId;
        $asset->avoidFilenameConflicts = true;
        $asset->setScenario(AssetElement::SCENARIO_CREATE);

        Plugin::info('Creating asset with content `{i}`', [
            'i' => Json::encode([
                'tempFilePath' => $tempFilePath,
                'filename' => $filename,
                'newFolderId' => $folder->id,
                'volumeId' => $folder->volumeId,
                'avoidFilenameConflicts' => true,
                'scenario' => AssetElement::SCENARIO_CREATE,
                'conflict' => $conflict,
            ]),
        ]);

        $result = Craft::$app->getElements()->saveElement($asset, true, true, $updateSearchIndexes);

        if ($result) {
            // Annoyingly, you have to create the asset field, then move it to the temp directly, then replace the conflicting
            // asset, so there's a bit more work here than I would've thought...
            if ($asset->conflictingFilename !== null && $conflict === AssetElement::SCENARIO_REPLACE) {
                $conflictingAsset = AssetElement::findOne(['folderId' => $folder->id, 'filename' => $asset->conflictingFilename]);

                if ($conflictingAsset) {
                    Plugin::info('Replacing existing asset `#{i}` with `#{j}`', ['i' => $conflictingAsset->id, 'j' => $asset->id]);

                    $tempPath = $asset->getCopyOfFile();
                    $assets->replaceAssetFile($conflictingAsset, $tempPath, $conflictingAsset->filename);
                    Craft::$app->getElements()->deleteElement($asset);

                    return $conflictingAsset->id;
                }
            }

            return $asset->id;
        }

        return false;
    }

    /**
     * @return string
     * @throws Exception
     */
    private static function createTempFeedMePath(): string
    {
        $tempFeedMePath = Craft::$app->getPath()->getTempPath() . '/feedme/';

        if (!is_dir($tempFeedMePath)) {
            FileHelper::createDirectory($tempFeedMePath);
        }

        return $tempFeedMePath;
    }

    /**
     * @param $url
     * @return string
     */
    public static function getRemoteUrlFilename($url): string
    {
        // Function to extract a filename from a URL path. It does not query the actual URL, however.
        // There are some tricky cases being tested again, and mostly revolves around query strings. We do our best to figure it out!
        // http://example.com/test.php
        // http://example.com/test.php?pubid=image.jpg
        // http://example.com/image.jpg?width=1280&cid=5049
        // http://example.com/image.jpg?width=1280&cid=5049&un=support%40gdomain.com
        // http://example.com/test
        // http://example.com/test?width=1280&cid=5049
        // http://example.com/test?width=1280&cid=5049&un=support%40gdomain.com

        $extension = self::getRemoteUrlExtension($url);

        // PathInfo can't really deal with query strings, so remove it
        $filename = UrlHelper::stripQueryString($url);

        // Can we easily get the extension for this URL?
        $filename = pathinfo($filename, PATHINFO_FILENAME);

        // If there was a query string, append it so this asset remains unique
        $query = parse_url($url, PHP_URL_QUERY);

        if ($query) {
            $filename .= '-' . $query;
        }

        $filename = AssetsHelper::prepareAssetName($filename, false);

        return $filename . '.' . $extension;
    }

    /**
     * @param $url
     * @return string
     */
    public static function getRemoteUrlExtension($url): string
    {
        // PathInfo can't really deal with query strings, so remove it
        $extension = UrlHelper::stripQueryString($url);

        // Can we easily get the extension for this URL?
        $extension = StringHelper::toLowerCase(pathinfo($extension, PATHINFO_EXTENSION));

        // We might now have a perfectly acceptable extension, but is it real and allowed by Craft?
        if (!in_array($extension, Craft::$app->getConfig()->getGeneral()->allowedFileExtensions, true)) {
            $extension = '';
        }

        // If we can't easily determine the extension of the url, fetch it
        if (!$extension) {
            $client = Plugin::$plugin->service->createGuzzleClient();
            $response = null;

            // Try using HEAD requests (for performance), if it fails use GET
            try {
                $response = $client->head($url);
            } catch (Throwable $e) {
            }

            try {
                if (!$response) {
                    $response = $client->get($url);
                }
            } catch (Throwable $e) {
            }

            if ($response) {
                $contentType = $response->getHeader('Content-Type');

                if (isset($contentType[0])) {
                    // Because some servers cram unnecessary things into the Content-Type header.
                    $contentType = explode(';', $contentType[0]);
                    // Convert MIME type to extension
                    $extension = FileHelper::getExtensionByMimeType($contentType[0]);
                }
            }
        }

        return StringHelper::toLowerCase($extension);
    }
}
