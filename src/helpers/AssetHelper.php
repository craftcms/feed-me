<?php
namespace verbb\feedme\helpers;

use verbb\feedme\FeedMe;
use verbb\feedme\base\Field;
use verbb\feedme\base\FieldInterface;
use verbb\feedme\helpers\AssetHelper;

use Craft;
use craft\elements\Asset as AssetElement;
use craft\helpers\Assets as AssetsHelper;
use craft\helpers\FileHelper;
use craft\helpers\UrlHelper;

use Cake\Utility\Hash;
use Mimey\MimeTypes;

class AssetHelper
{
    // Public Methods
    // =========================================================================

    public static function downloadFile($srcName, $dstName, $chunkSize = 1, $returnbytes = true) {
        $chunksize = $chunkSize * (1024 * 1024);
        $data = '';
        $bytesCount = 0;
        $handle = fopen($srcName, 'rb');
        $fp = fopen($dstName, 'w');
        
        if ($handle === false) {
            return false;
        }
        
        while (!feof($handle)) {
            $data = fread($handle, $chunksize);
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

    public static function fetchRemoteImage($urls, $fieldInfo, $field = null, $element = null, $folderId = null)
    {
        $uploadedAssets = [];

        $upload = Hash::get($fieldInfo, 'options.upload');
        $conflict = Hash::get($fieldInfo, 'options.conflict');

        $assets = Craft::$app->getAssets();
        $tempFeedMePath = Craft::$app->getPath()->getTempPath() . '/feedme/';

        if (!is_dir($tempFeedMePath)) {
            FileHelper::createDirectory($tempFeedMePath);
        }

        // Download each image. Note we've already checked if there's an existing asset and if the 
        // user has set to use that instead so we're good to proceed.
        foreach ($urls as $url) {
            try {
                $filename = self::getRemoteUrlFilename($url);

                $fetchedImage = $tempFeedMePath . $filename;

                // But also check if we've downloaded this recently, use the copy in the temp directory
                $cachedImage = FileHelper::findFiles($tempFeedMePath, [
                    'only' => [$filename],
                    'recursive' => false,
                ]);

                if (!$cachedImage) {
                    AssetHelper::downloadFile($url, $fetchedImage);
                } else {
                    $fetchedImage = $cachedImage[0];
                }

                if (!$folderId) {
                    $folderId = $field->resolveDynamicPathToFolderId($element);
                }

                $folder = $assets->findFolder(['id' => $folderId]);

                // Create the new asset (even if we're setting it to replace)
                $asset = new AssetElement();
                $asset->tempFilePath = $fetchedImage;
                $asset->filename = $filename;
                $asset->newFolderId = $folder->id;
                $asset->volumeId = $folder->volumeId;
                $asset->avoidFilenameConflicts = true;
                $asset->setScenario(AssetElement::SCENARIO_CREATE);

                $result = Craft::$app->getElements()->saveElement($asset);

                if ($result) {
                    // Annoyingly, you have to create the asset field, then move it to the temp directly, then replace the conflicting
                    // asset, so there's a bit more work here than I would've thought...
                    if ($asset->conflictingFilename !== null && $conflict === AssetElement::SCENARIO_REPLACE) {
                        $conflictingAsset = AssetElement::findOne(['folderId' => $folder->id, 'filename' => $asset->conflictingFilename]);

                        $tempPath = $asset->getCopyOfFile();
                        $assets->replaceAssetFile($conflictingAsset, $tempPath, $conflictingAsset->filename);
                        Craft::$app->getElements()->deleteElement($asset);

                        $uploadedAssets[] = $conflictingAsset->id;
                    } else {
                        $uploadedAssets[] = $asset->id;
                    }
                }
            } catch (\Throwable $e) {
                FeedMe::error(null, 'Asset error: ' . $url . ' - ' . $e->getMessage());
                echo $e->getMessage();
            }
        }

        return $uploadedAssets;
    }

    public static function getRemoteUrlFilename($url)
    {
        // Function to extract a filename from a URL path. It does not query the actual URL however.
        // There are some tricky cases being tested again, and mostly revolves around query strings. We do our best to figure it out!
        // http://example.com/test.php
        // http://example.com/test.php?pubid=image.jpg
        // http://example.com/image.jpg?width=1280&cid=5049
        // http://example.com/image.jpg?width=1280&cid=5049&un=support%40gdomain.com
        // http://example.com/test
        // http://example.com/test?width=1280&cid=5049
        // http://example.com/test?width=1280&cid=5049&un=support%40gdomain.com

        $filename = '';
        $extension = self::getRemoteUrlExtension($url);

        // PathInfo can't really deal with query strings, so remove it
        $filename = UrlHelper::stripQueryString($url);

        // Can we easily get the extension for this URL?
        $filename = pathinfo($filename, PATHINFO_FILENAME);

        // If there was a query string, append a hash of it so this asset remains unique
        $query = parse_url($url, PHP_URL_QUERY);

        if ($query) {
            $filename = $filename . '-' . static::queryHash($query);
        }

        // Clean up the filename
        $filename = AssetsHelper::prepareAssetName($filename, false);

        return $filename . '.' . $extension;
    }

    public static function getRemoteUrlExtension($url)
    {
        $extension = '';

        $mimes = new MimeTypes;

        // PathInfo can't really deal with query strings, so remove it
        $extension = UrlHelper::stripQueryString($url);

        // Can we easily get the extension for this URL?
        $extension = pathinfo($extension, PATHINFO_EXTENSION);

        // We might now have a perfectly acceptable extension, but is it real and allowed by Craft?
        if (!in_array($extension, Craft::$app->getConfig()->getGeneral()->allowedFileExtensions)) {
            $extension = '';
        }

        // If we can't easily determine the extension of the url, fetch it
        if (!$extension) {
            $client = FeedMe::$plugin->service->createGuzzleClient();
            $response = null;

            // Try using HEAD requests (for performance), if it fails use GET
            try {
                $response = $client->head($url);
            } catch (\Throwable $e) {}

            try {
                if (!$response) {
                    $response = $client->get($url);
                }
            } catch (\Throwable $e) {}
            
            if ($response) {
                $contentType = $response->getHeader('Content-Type');

                if (isset($contentType[0])) {
                    // Convert MIME type to extension
                    $extension = $mimes->getExtension($contentType[0]);
                }
            }
        }

        return $extension;
    }

    public static function queryHash($string)
    {
        return base_convert($string, 10, 36);
    }

}
