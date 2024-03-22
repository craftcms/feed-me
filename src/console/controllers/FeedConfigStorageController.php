<?php

namespace craft\feedme\console\controllers;

use craft\feedme\Plugin;
use craft\helpers\Console;
use yii\console\Controller;
use yii\console\ExitCode;

/**
 * Read from or write to configuration file
 *
 * @property Plugin $module
 */
class FeedConfigStorageController extends Controller
{
    // Public Methods
    // =========================================================================

    /**
     * @inheritDoc
     */
    public function options($actionID): array
    {
        $options = parent::options($actionID);
        return $options;
    }

    /**
     * Writes feeds records to file
     *
     * @return int
     */
    public function actionWrite(): int
    {
        return Plugin::$plugin->config->write() ? ExitCode::CANTCREAT : ExitCode::OK;
    }

    /**
     * Reads feeds records from file
     *
     * @return int
     */
    public function actionRead(): int
    {
        $result = Plugin::$plugin->config->read();

        if($result->success) {
            return ExitCode::OK;
        }

        $this->stderr("FAILED FEEDS" . PHP_EOL, Console::FG_RED);
        foreach($result->failed_feeds as $feed) {
            $this->stderr("({$feed->id}) {$feed->name}".PHP_EOL, Console::FG_RED);
        }

        if(count($result->success_feeds) > 0) {
            $this->stdout(PHP_EOL . "SUCCESSFUL FEEDS" . PHP_EOL, Console::FG_GREEN);
            foreach ($result->success_feeds as $feed) {
                $this->stdout("({$feed->id}) {$feed->name} " . PHP_EOL, Console::FG_GREEN);
            }
        }

        return ExitCode::UNSPECIFIED_ERROR;
    }
}
