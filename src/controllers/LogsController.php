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
     * @inheritdoc
     */
    public function beforeAction($action): bool
    {
        if (!parent::beforeAction($action)) {
            return false;
        }

        $this->requirePermission('accessPlugin-feed-me');

        return true;
    }

    /**
     * @return Response
     * @throws \yii\base\Exception
     */
    public function actionLogs(): Response
    {
        $show = Craft::$app->getRequest()->getParam('show');
        $logEntries = Plugin::$plugin->getLogs()->getLogEntries($show);

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
