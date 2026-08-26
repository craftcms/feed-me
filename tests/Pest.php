<?php
/**
 * @link https://craftcms.com/
 * @copyright Copyright (c) Pixel & Tonic, Inc.
 * @license https://craftcms.github.io/license/
 */

use craft\feedme\tests\TestCase;
use craft\feedme\tests\UnitTestCase;
use craft\test\TestSetup;

ini_set('date.timezone', 'UTC');
date_default_timezone_set('UTC');

define('CRAFT_TESTS_PATH', __DIR__);
define('CRAFT_ROOT_PATH', dirname(__DIR__));
define('CRAFT_STORAGE_PATH', __DIR__ . '/_craft/storage');
define('CRAFT_TEMPLATES_PATH', __DIR__ . '/_craft/templates');
define('CRAFT_CONFIG_PATH', __DIR__ . '/_craft/config');
define('CRAFT_MIGRATIONS_PATH', __DIR__ . '/_craft/migrations');
define('CRAFT_TRANSLATIONS_PATH', __DIR__ . '/_craft/translations');
define('CRAFT_VENDOR_PATH', dirname(__DIR__) . DIRECTORY_SEPARATOR . 'vendor');

// Storage is gitignored, so it may not exist on a fresh checkout. `TestSetup::configureCraft()`
// resolves it with `realpath()`, which returns false for a missing path and silently breaks the
// `@storage`/`@runtime` aliases (surfaces as a `mkdir(): Permission denied` on `/runtime`).
foreach ([
    CRAFT_STORAGE_PATH,
    CRAFT_STORAGE_PATH . '/config-deltas',
    CRAFT_STORAGE_PATH . '/logs',
    CRAFT_STORAGE_PATH . '/rebrand',
    CRAFT_STORAGE_PATH . '/runtime',
    CRAFT_STORAGE_PATH . '/runtime/assets',
    CRAFT_STORAGE_PATH . '/runtime/cache',
    CRAFT_STORAGE_PATH . '/runtime/compiled_classes',
    CRAFT_STORAGE_PATH . '/runtime/compiled_templates',
    CRAFT_STORAGE_PATH . '/runtime/temp',
] as $dir) {
    if (!is_dir($dir)) {
        mkdir($dir, 0777, true);
    }
}

$devMode = true;

TestSetup::configureCraft();

// Set the @webroot alias so that the cpresources folder is created in the correct directory
Craft::setAlias('@webroot', __DIR__ . '/_craft/web');

uses(TestCase::class)->in('Feature');
uses(UnitTestCase::class)->in('Unit');
