<?php

namespace craft\feedme\controllers;

use Craft;
use craft\feedme\Plugin;
use craft\web\Controller;

class LogsController extends Controller
{
    // Public Methods
    // =========================================================================

    public function actionLogs()
    {
        $show = Craft::$app->getRequest()->getParam('show');
        $logEntries = Plugin::$plugin->logs->getLogEntries($show);

        // Limit to 300 for UI
        $logEntries = array_slice($logEntries, 0, 300);

        return $this->renderTemplate('feed-me/logs/index', [
            'show' => $show,
            'logEntries' => $logEntries,
        ]);
    }

    public function actionClear()
    {
        Plugin::$plugin->logs->clear();

        return $this->redirect('feed-me/logs');
    }
}
