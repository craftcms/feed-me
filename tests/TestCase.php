<?php
/**
 * @link https://craftcms.com/
 * @copyright Copyright (c) Pixel & Tonic, Inc.
 * @license https://craftcms.github.io/license/
 */

namespace craft\feedme\tests;

use Craft;
use craft\config\DbConfig;
use craft\enums\CmsEdition;
use craft\feedme\tests\Helpers\ElementFactory;
use craft\helpers\App;
use craft\migrations\Install;
use craft\models\Site;
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
            // Equivalent to `TestSetup::setupCraftDb($db)`, minus its project-config-seeded-site
            // branch - that branch calls `\craft\test\Craft::$instance`, which forces autoloading
            // of Craft's Codeception-based test module (`craft\test\Craft extends
            // Codeception\Module\Yii2`). This package doesn't seed a project-config folder for
            // tests, so that branch is a no-op for us anyway - inlining lets us skip Codeception
            // entirely, consistent with this package no longer depending on it.
            $site = new Site([
                'name' => 'Craft test site',
                'handle' => 'defaultSite',
                'hasUrls' => true,
                'baseUrl' => TestSetup::SITE_URL,
                'language' => 'en-US',
                'primary' => true,
            ]);

            $migration = new Install([
                'db' => $db,
                'username' => TestSetup::USERNAME,
                'password' => 'craftcms2018!!',
                'email' => 'support@craftcms.com',
                'site' => $site,
            ]);
            $migration->up(true);
        }
        $db->close();

        /** @var Application $app */
        $app = Craft::createObject(require CRAFT_CONFIG_PATH . '/test.php');
        Craft::$app = $app;

        // Fresh installs default to the Solo edition (1-user cap) - tests that create more than
        // one user (e.g. author fixtures in EntryTest) need Pro so `Users::canCreateUsers()`
        // doesn't silently reject the save in `User::beforeSave()` before validation even runs.
        Craft::$app->setEdition(CmsEdition::Pro);

        if (!Craft::$app->getPlugins()->getPlugin('feed-me')) {
            Craft::$app->getPlugins()->installPlugin('feed-me');
        }

        // Project config writes (edition + plugin enablement) are normally persisted by a
        // `flush()` listener on `Application::EVENT_AFTER_REQUEST` — which never fires here,
        // since this harness never runs a real request through the app. Without this, those
        // writes stay in memory and are lost at the end of the process.
        Craft::$app->getProjectConfig()->saveModifiedConfigData();

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
