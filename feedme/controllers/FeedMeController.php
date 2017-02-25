<?php
namespace Craft;

class FeedMeController extends BaseController
{
    protected $allowAnonymous = array('actionClearTasks');

    // Public Methods
    // =========================================================================

    public function actionSettings()
    {
        $settings = craft()->feedMe->getSettings();

        $this->renderTemplate('feedme/settings/general', array(
            'settings' => $settings,
        ));
    }

    public function actionClearTasks()
    {
        // Function to clear (delete) all stuck tasks.
        craft()->db->createCommand()->delete('tasks');

        $this->redirect(craft()->request->getUrlReferrer());
    }

    
}
