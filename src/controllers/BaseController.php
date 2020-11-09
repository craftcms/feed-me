<?php

namespace craft\feedme\controllers;

use Craft;
use craft\feedme\Plugin;
use craft\web\Controller;

class BaseController extends Controller
{
    /**
     * @var string[]
     */
    protected $allowAnonymous = ['actionClearTasks'];

    // Public Methods
    // =========================================================================

    /**
     * @return \yii\web\Response
     */
    public function actionSettings()
    {
        $settings = Plugin::$plugin->getSettings();

        return $this->renderTemplate('feed-me/settings/general', [
            'settings' => $settings,
        ]);
    }

    /**
     * @return \yii\web\Response
     * @throws \yii\db\Exception
     */
    public function actionClearTasks()
    {
        // Function to clear (delete) all stuck tasks.
        Craft::$app->getDb()->createCommand()
            ->delete('{{%queue}}')
            ->execute();

        return $this->redirect('feed-me/settings/general');
    }

}
