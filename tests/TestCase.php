<?php
/**
 * @link https://craftcms.com/
 * @copyright Copyright (c) Pixel & Tonic, Inc.
 * @license https://craftcms.github.io/license/
 */

namespace craft\feedme\tests;

use Craft;
use craft\db\Connection;
use craft\enums\CmsEdition;
use craft\feedme\tests\Helpers\ElementFactory;
use craft\helpers\App;
use craft\helpers\ArrayHelper;
use craft\migrations\Install;
use craft\models\Site;
use craft\services\Config;
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
    /**
     * The app this class booted. Compared against `Craft::$app` rather than tracked with a bool,
     * since {@see UnitTestCase} boots its own app - if a unit test class runs in between, this
     * class needs to boot again rather than keep running against the non-installed app.
     */
    private static ?Application $bootedApp = null;

    private Transaction $transaction;

    public static function setUpBeforeClass(): void
    {
        parent::setUpBeforeClass();

        if (self::$bootedApp !== null && Craft::$app === self::$bootedApp) {
            return;
        }

        // Wipe the DB before booting Craft - otherwise the app loads the previous run's installed
        // plugins on init, and installPlugin() below would think Feed Me is already installed.
        /** @var Connection $db */
        $db = Craft::createObject(array_merge(
            App::dbConfig(self::createConfigService()->getDb()),
            // There's no app yet to resolve the default `cache` component from.
            ['schemaCache' => null],
        ));
        TestSetup::cleanseDb($db);
        $db->close();

        self::resetAppState();

        /** @var Application $app */
        $app = Craft::createObject(self::createTestCraftObjectConfig());
        Craft::$app = $app;

        self::setUpDb();

        Craft::$app->setEdition(CmsEdition::Pro);

        if (!Craft::$app->getPlugins()->getPlugin('feed-me')) {
            ob_start();
            Craft::$app->getPlugins()->installPlugin('feed-me');
            if (ob_get_level() > 0) {
                ob_end_clean();
            }
        }

        Craft::$app->getProjectConfig()->saveModifiedConfigData();

        self::$bootedApp = Craft::$app;
    }

    protected function setUp(): void
    {
        parent::setUp();

        Craft::$app->getDb()->open();
        $this->transaction = Craft::$app->getDb()->beginTransaction();

        ElementFactory::resetFaker(static::class . '::' . $this->nameWithDataSet());
    }

    protected function tearDown(): void
    {
        $this->transaction->rollBack();

        ElementFactory::cleanUpTempPaths();

        // Craft::$app persists across tests in the same run, so a logged-in identity, or query/body
        // params/method set for a controller test, would otherwise leak into the next test.
        Craft::$app->getUser()->setIdentity(null);
        Craft::$app->getRequest()->setQueryParams([]);
        Craft::$app->getRequest()->setBodyParams([]);
        Craft::$app->getRequest()->setAcceptableContentTypes([]);
        unset($_SERVER['REQUEST_METHOD']);

        // Craft::$app->getResponse() is also a shared singleton — a controller action that set a
        // status code/format/data (e.g. asFailure()'s setStatusCode(400)) would otherwise leak too.
        $response = Craft::$app->getResponse();
        $response->setStatusCode(200);
        $response->format = \yii\web\Response::FORMAT_HTML;
        $response->data = null;
        $response->content = null;

        parent::tearDown();
    }

    //////// all this could be shared between plugins ///////
    public static function createTestCraftObjectConfig(): array
    {
        $_SERVER['REMOTE_ADDR'] = '1.1.1.1';
        $_SERVER['REMOTE_PORT'] = 654321;

        $vendorPath = self::normalizePathSeparators(CRAFT_VENDOR_PATH);
        $srcPath = $vendorPath . '/craftcms/cms/src';

        $appType = 'web';

        // Normalize some Craft-defined path aliases.
        Craft::setAlias('@lib', self::normalizePathSeparators(Craft::getAlias('@lib')));
        Craft::setAlias('@config', self::normalizePathSeparators(Craft::getAlias('@config')));
        Craft::setAlias('@contentMigrations', self::normalizePathSeparators(Craft::getAlias('@contentMigrations')));
        Craft::setAlias('@storage', self::normalizePathSeparators(Craft::getAlias('@storage')));
        Craft::setAlias('@templates', self::normalizePathSeparators(Craft::getAlias('@templates')));
        Craft::setAlias('@translations', self::normalizePathSeparators(Craft::getAlias('@translations')));

        $configService = self::createConfigService();

        $config = ArrayHelper::merge(
            [
                'components' => [
                    'config' => $configService,
                ],
            ],
            require $srcPath . '/config/app.php',
            require $srcPath . '/config/app.' . $appType . '.php',
            $configService->getConfigFromFile('app'),
            $configService->getConfigFromFile("app.$appType")
        );

        if (defined('CRAFT_SITE')) {
            $config['components']['sites']['currentSite'] = CRAFT_SITE;
        }

        $config['vendorPath'] = $vendorPath;

        return ArrayHelper::merge($config, [
            'class' => Application::class,
            'id' => 'craft-test',
            'env' => 'test',
            'basePath' => $srcPath,
        ]);
    }

    /**
     * Clears process-global state a previously booted app leaves behind, the same way
     * `craft\test\CraftConnector::resetApplication()` does. In particular, `Sites::setCurrentSite()`
     * sets `$_SERVER['CRAFT_SITE']`, which the next app's request then tries to resolve - before
     * the site exists, if the DB was just cleansed.
     */
    public static function resetAppState(): void
    {
        unset($_SERVER['CRAFT_SITE'], $_SERVER['CRAFT_SITE_UPPER']);
    }

    protected static function createConfigService(): Config
    {
        $configService = new Config();
        $configService->env = 'test';
        $configService->configDir = CRAFT_CONFIG_PATH;
        $configService->appDefaultsDir = CRAFT_VENDOR_PATH . '/craftcms/cms/src/config/defaults';

        return $configService;
    }

    protected static function normalizePathSeparators(mixed $path): string|false
    {
        return is_string($path) ? str_replace("\\", '/', $path) : false;
    }

    protected static function setUpDb(): void
    {
        $db = Craft::$app->getDb();
        $db->schemaCache = false;
        $db->emulatePrepare = false;

        if ($db->schema->getTableNames() === []) {
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
                // Also requires `Craft::$app` (for `getProjectConfig()`), and this package
                // doesn't seed a `config/project/` folder for tests, so there's nothing to apply.
                'applyProjectConfigYaml' => false,
            ]);

            ob_start(); // don't show migration logs
            try {
                $migration->up(true);
            } catch (\Throwable $e) {
                ob_end_clean(); // don't show migration logs
                TestSetup::cleanseDb($db);
                throw $e;
            } finally { // don't show migration logs
                if (ob_get_level() > 0) {
                    ob_end_clean();
                }
            }
        }
    }
}
