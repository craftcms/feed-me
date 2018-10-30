<?php
namespace verbb\feedme\controllers;

use verbb\feedme\FeedMe;

use Craft;
use craft\web\Controller;

class LogsController extends Controller
{
    // Public Methods
    // =========================================================================

    public function actionLogs()
    {
        $logEntries = FeedMe::$plugin->logs->getLogEntries();

        // Limit to 300 for UI
        $logEntries = array_slice($logEntries, 0, 300);

        return $this->renderTemplate('feed-me/logs/index', [
            'logEntries' => $logEntries,
        ]);
    }

    public function actionClear()
    {
        $logEntries = FeedMe::$plugin->logs->clear();

        return $this->redirect('feed-me/logs');
    }
}
