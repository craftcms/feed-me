<?php

namespace craft\feedme\controllers;

use Craft;
use craft\feedme\Plugin;
use craft\web\Controller;
use yii\db\Exception;
use yii\web\Response;

class BaseController extends Controller
{
    /**
     * @var string[]
     */
    protected int|bool|array $allowAnonymous = ['actionClearTasks'];

    // Public Methods
    // =========================================================================

    /**
     * @return Response
     */
    public function actionSettings(): Response
    {
        $settings = Plugin::$plugin->getSettings();

        return $this->renderTemplate('feed-me/settings/general', [
            'settings' => $settings,
        ]);
    }

    /**
     * @return Response
     * @throws Exception
     */
    public function actionClearTasks(): Response
    {
        // Function to clear (delete) all stuck tasks.
        Craft::$app->getDb()->createCommand()
            ->delete('{{%queue}}')
            ->execute();

        return $this->redirect('feed-me/settings/general');
    }
}
