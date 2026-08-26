<?php
/**
 * @link https://craftcms.com/
 * @copyright Copyright (c) Pixel & Tonic, Inc.
 * @license https://craftcms.github.io/license/
 */

namespace craft\feedme\tests;

use Craft;
use craft\config\DbConfig;
use craft\feedme\tests\Helpers\ElementFactory;
use craft\helpers\App;
use craft\test\TestSetup;
use craft\web\Application;
use PHPUnit\Framework\TestCase as BaseTestCase;
use yii\db\Transaction;

/**
 * Base test case for tests that need a booted Craft application and database.
 *
 * Boots Craft once per test run against the `tests/_craft` fixture install (an empty schema,
 * migrated by installing the plugin below), then wraps each test in a DB transaction that's
 * rolled back afterwards. Tests seed their own fixture data, so no pre-seeded DB dump is needed.
 */
class TestCase extends BaseTestCase
{
    private static bool $craftBooted = false;

    private Transaction $transaction;

    public static function setUpBeforeClass(): void
    {
        parent::setUpBeforeClass();

        if (self::$craftBooted) {
            return;
        }

        // The configured test database may be genuinely empty (no Craft schema at all) — install
        // Craft into it if so, using a standalone connection *before* building the full app.
        // `craft\services\Plugins::loadPlugins()` runs automatically from `Application::init()`
        // and bails permanently for this process if the DB isn't installed yet at that point, so
        // installing after the app is already built is too late — `getPlugin()` would never work.
        $dbConfig = Craft::createObject(array_merge(
            ['class' => DbConfig::class],
            require CRAFT_CONFIG_PATH . '/db.php'
        ));
        $db = Craft::createObject(App::dbConfig($dbConfig));
        $db->open();
        if ($db->schema->getTableNames() === []) {
            TestSetup::setupCraftDb($db);
        }
        $db->close();

        /** @var Application $app */
        $app = Craft::createObject(require CRAFT_CONFIG_PATH . '/test.php');
        Craft::$app = $app;

        if (!Craft::$app->getPlugins()->getPlugin('feed-me')) {
            Craft::$app->getPlugins()->installPlugin('feed-me');

            // Project config writes are normally persisted by a `flush()` listener on
            // `Application::EVENT_AFTER_REQUEST` — which never fires here, since this harness
            // never runs a real request through the app. Without this, `installPlugin()`'s
            // `plugins.feed-me.enabled` write stays in memory and is lost at the end of the
            // process, so `getPlugin('feed-me')` would look uninstalled again on every next run.
            Craft::$app->getProjectConfig()->saveModifiedConfigData();
        }

        self::$craftBooted = true;
    }

    protected function setUp(): void
    {
        parent::setUp();

        Craft::$app->getDb()->open();
        $this->transaction = Craft::$app->getDb()->beginTransaction();

        ElementFactory::resetFaker();
    }

    protected function tearDown(): void
    {
        $this->transaction->rollBack();

        parent::tearDown();
    }
}
