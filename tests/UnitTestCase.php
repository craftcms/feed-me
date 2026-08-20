<?php
/**
 * @link https://craftcms.com/
 * @copyright Copyright (c) Pixel & Tonic, Inc.
 * @license https://craftcms.github.io/license/
 */

namespace craft\feedme\tests;

use Craft;
use craft\web\Application;
use PHPUnit\Framework\TestCase as BaseTestCase;

/**
 * Lightweight base test case for tests that need Craft's service locator and the Feed Me
 * plugin instance, but not a live database.
 *
 * Boots Craft the same way {@see TestCase} does, but skips `setIsInstalled()` and the DB
 * transaction wrap. `Plugin::$plugin` is still initialized via `createPlugin()`, which — unlike
 * `installPlugin()` — only instantiates the plugin module (triggering its `init()`) without
 * writing to the database or project config.
 */
class UnitTestCase extends BaseTestCase
{
    private static bool $craftBooted = false;

    public static function setUpBeforeClass(): void
    {
        parent::setUpBeforeClass();

        if (self::$craftBooted) {
            return;
        }

        /** @var Application $app */
        $app = Craft::createObject(require CRAFT_CONFIG_PATH . '/test.php');
        Craft::$app = $app;

        if (!Craft::$app->getPlugins()->getPlugin('feed-me')) {
            Craft::$app->getPlugins()->createPlugin('feed-me');
        }

        self::$craftBooted = true;
    }
}
