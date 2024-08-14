<?php

namespace craft\feedme\console\controllers;

use craft\console\Controller;
use craft\feedme\Plugin;
use yii\console\ExitCode;

class LogsController extends Controller
{
    public function actionClear(): int
    {
        Plugin::$plugin->getLogs()->clear();

        return ExitCode::OK;
    }
}
