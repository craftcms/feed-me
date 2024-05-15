<?php

namespace craft\feedme\controllers;

use Craft;
use craft\feedme\Plugin;
use craft\web\Controller;
use yii\web\Response;

class LogsController extends Controller
{
    // Public Methods
    // =========================================================================

    /**
     * @return Response
     * @throws \yii\base\Exception
     */
    public function actionLogs(): Response
    {
        $show = Craft::$app->getRequest()->getParam('show');
        $logEntries = Plugin::$plugin->getLogs()->getLogEntries($show);

        // Limit to 300 for UI
        $logEntries = array_slice($logEntries, 0, 300);

        return $this->renderTemplate('feed-me/logs/index', [
            'show' => $show,
            'logEntries' => $logEntries,
        ]);
    }

    /**
     * @return Response
     */
    public function actionClear(): Response
    {
        Plugin::$plugin->getLogs()->clear();

        return $this->redirect('feed-me/logs');
    }
}
