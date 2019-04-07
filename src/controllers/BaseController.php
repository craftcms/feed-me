<?php

namespace verbb\feedme\controllers;

use Craft;
use craft\web\Controller;
use verbb\feedme\FeedMe;

class BaseController extends Controller
{
    protected $allowAnonymous = ['actionClearTasks'];


    // Public Methods
    // =========================================================================

    public function actionSettings()
    {
        $settings = FeedMe::$plugin->getSettings();

        return $this->renderTemplate('feed-me/settings/general', [
            'settings' => $settings,
        ]);
    }

    public function actionClearTasks()
    {
        // Function to clear (delete) all stuck tasks.
        Craft::$app->getDb()->createCommand()
            ->delete('{{%queue}}')
            ->execute();

        return $this->redirect('feed-me/settings/general');
    }

}
