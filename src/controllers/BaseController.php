<?php

namespace craft\feedme\controllers;

use Craft;
use craft\feedme\Plugin;
use craft\web\Controller;
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
        $this->requireAdmin(false);

        $settings = Plugin::$plugin->getSettings();

        return $this->renderTemplate('feed-me/settings/general', [
            'settings' => $settings,
            'readOnly' => !Craft::$app->getConfig()->getGeneral()->allowAdminChanges,
        ]);
    }
}
