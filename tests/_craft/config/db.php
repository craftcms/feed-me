<?php

/**
 * Database Configuration
 *
 * All of your system's database configuration settings go in here.
 * You can see a list of the default settings in craft/app/config/defaults/db.php
 */

return [
    'dsn' => getenv('DB_DSN'),
    'user' => getenv('DB_USER'),
    'password' => getenv('DB_PASSWORD'),
    'schema' => getenv('DB_SCHEMA'),
    'tablePrefix' => getenv('DB_TABLE_PREFIX'),
];
