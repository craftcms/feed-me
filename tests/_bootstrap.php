<?php

define('YII_ENV', 'test');
define('YII_DEBUG', true);

// Set path constants
define('CRAFT_BASE_PATH', __DIR__ . '/_craft');
define('CRAFT_STORAGE_PATH', __DIR__ . '/_craft/storage');
define('CRAFT_TEMPLATES_PATH', __DIR__ . '/_craft/templates');
define('CRAFT_CONFIG_PATH', __DIR__ . '/_craft/config');
define('CRAFT_VENDOR_PATH', __DIR__ . '/../vendor');

error_reporting(E_ALL);
ini_set('log_errors', 1);
ini_set('error_log', CRAFT_STORAGE_PATH . '/logs/phperrors.log');
ini_set('display_errors', 1);

// Load Composer's autoloader
require_once CRAFT_VENDOR_PATH . '/autoload.php';

// Load dotenv?
if (file_exists(CRAFT_BASE_PATH . '/.env')) {
    (new Dotenv\Dotenv(CRAFT_BASE_PATH))->load();
}

// Load and run Craft
define('CRAFT_ENVIRONMENT', 'test');
$app = require CRAFT_VENDOR_PATH . '/craftcms/cms/bootstrap/console.php';
