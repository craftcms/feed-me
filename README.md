<p align="center"><img src="./src/icon.svg" width="100" height="100" alt="Feed Me icon"></p>

<h1 align="center">Feed Me for Craft CMS</h1>

Feed Me is a Craft plugin for super-simple importing of content, either once-off or at regular intervals. With support for XML, RSS, ATOM, CSV or JSON feeds, you'll be able to import your content as Entries, Categories, Craft Commerce Products (and variants), and more.

## Requirements

This plugin requires Craft CMS 5.0.0-beta.2 or later.

## Installation

You can install this plugin from the Plugin Store or with Composer.

#### From the Plugin Store

Go to the Plugin Store in your project’s Control Panel and search for “Feed Me”. Then click on the “Install” button in its modal window.

#### With Composer

Open your terminal and run the following commands:

```bash
# go to the project directory
cd /path/to/my-project.test

# tell Composer to load the plugin
composer require craftcms/feed-me

# tell Craft to install the plugin
./craft plugin/install feed-me
```

## Customizing Logs

As of version `5.6`/`6.2`, logging is handled by Craft's log component and stored in the database instead of the filesystem.
To log to files (or anywhere else) instead, you can disable the default logging add your own log target:

### config/feed-me.php

```php
<?php
return [
    // disable default logging to database
    'logging' => false,
];
```

### config/app.php

```php
<?php
return [
    'components' => [
        'log' => [
            'monologTargetConfig' => [
                // optionally, omit from Craft's default logs
                'except' => ['feed-me'],
            ],
            
            // add your own log target to write logs to file
            'targets' => [
                [
                    // log to file or STDOUT/STDERR if CRAFT_STREAM_LOG=1 is set
                    'class' => \craft\log\MonologTarget::class,
                    'name' => 'feed-me',
                    'categories' => ['feed-me'],
                    
                    // Don't log request and env vars
                    'logContext' => false,
                    
                    // Minimum level to log
                    'level' => \Psr\Log\LogLevel::INFO,
                ],
            ],
        ],
    ],
];
```

## Resources

- **[Feed Me Plugin Page](https://plugins.craftcms.com/feed-me)** – The official plugin page for Feed Me
- **[Feed Me Documentation](https://docs.craftcms.com/feed-me/v6/)** – The official documentation
- **[Migrating a Website to Craft CMS](https://craftquest.io/courses/migrating-a-website-to-craft-cms/)** – Full video course from CraftQuest that covers Feed Me
