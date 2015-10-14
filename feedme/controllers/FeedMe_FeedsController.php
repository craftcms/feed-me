<?php
namespace Craft;

class FeedMe_FeedsController extends BaseController
{
    protected $allowAnonymous = array('actionRunTask');

    public function actionFeedsIndex()
    {
        $variables['feeds'] = craft()->feedMe_feeds->getFeeds();

        $this->renderTemplate('feedme/feeds/index', $variables);
    }

    public function actionEditFeed(array $variables = array())
    {
        if (!empty($variables['feedId'])) {
            $variables['feed'] = craft()->feedMe_feeds->getFeedById($variables['feedId']);
        } else {
            $variables['feed'] = new FeedMe_FeedModel();
        }

        $this->renderTemplate('feedme/feeds/_edit', $variables);
    }

    public function getModelFromPost() {
        $this->requirePostRequest();

        $feed = new FeedMe_FeedModel();

        // Shared attributes
        if (craft()->request->getPost('feedId')) {
            $feed->id = craft()->request->getPost('feedId');
        }

        $feed->name             = craft()->request->getPost('name');
        $feed->feedUrl          = craft()->request->getPost('feedUrl');
        $feed->feedType         = craft()->request->getPost('feedType');
        $feed->primaryElement   = craft()->request->getPost('primaryElement');
        $feed->section          = craft()->request->getPost('section');
        $feed->entrytype        = craft()->request->getPost('entrytype');
        $feed->duplicateHandle  = craft()->request->getPost('duplicateHandle');
        $feed->passkey          = craft()->request->getPost('passkey');
        $feed->backup           = craft()->request->getPost('backup');
        $feed->status           = craft()->request->getPost('status');

        // Don't overwrite mappings when saving from first screen
        if (craft()->request->getPost('fieldMapping')) {
            $feed->fieldMapping = craft()->request->getPost('fieldMapping');
        }
        if (craft()->request->getPost('fieldUnique')) {
            $feed->fieldUnique = craft()->request->getPost('fieldUnique');
        }

        return $feed;
    }

    public function actionSaveFeed()
    {
        $feed = $this->getModelFromPost();

        // Save it
        if (craft()->feedMe_feeds->saveFeed($feed)) {
            craft()->userSession->setNotice(Craft::t('Feed saved.'));
            $this->redirect('feedme/feeds');
        } else {
            craft()->userSession->setError(Craft::t('Couldn’t save feed.'));
        }

        // Send the feed back to the template
        craft()->urlManager->setRouteVariables(array(
            'feed' => $feed
        ));
    }

    public function actionDeleteFeed()
    {
        $this->requirePostRequest();
        $this->requireAjaxRequest();

        $feedId = craft()->request->getRequiredPost('id');

        craft()->feedMe_feeds->deleteFeedById($feedId);
        $this->returnJson(array('success' => true));
    }

    public function actionMapFeed()
    {
        $feed = $this->getModelFromPost();

        // We're onto the mapping step, but lets save what we've got so far anyway.
        if (craft()->feedMe_feeds->saveFeed($feed)) {
            craft()->userSession->setNotice(Craft::t('Feed saved.'));

            // Get the data for the mapping screen, based on the URL provided
            $feedData = craft()->feedMe_feed->getFeedMapping($feed->feedType, $feed->feedUrl, $feed->primaryElement);

            if ($feedData) {
                $this->renderTemplate('feedme/feeds/_map', array(
                    'feed'      => $feed,
                    'feedData'  => $feedData,
                ));
            }
        } else {
            craft()->userSession->setError(Craft::t('Couldn’t save feed.'));
        }
    }

    public function actionPerformFeed()
    {
        $feed = $this->getModelFromPost();

        // Mapping and all other setting are ready to go. Save and proceed with actual feed
        if (craft()->feedMe_feeds->saveFeed($feed)) {
            craft()->userSession->setNotice(Craft::t('Feed saved.'));

            // Feed settings have saved, now we're ready to trigger the import
            $this->runImportTask($feed->id);
        } else {
            craft()->userSession->setError(Craft::t('Couldn’t save feed.'));
        }
    }

    public function actionRunTask(array $variables = array())
    {
        if (!empty($variables['feedId'])) {
            $feedId = $variables['feedId'];
        } else if (craft()->request->getParam('feedId')) {
            $feedId = craft()->request->getParam('feedId');
        } else {
            $feedId = null;
        }

        if ($feedId) {
            $this->runImportTask($feedId);
        }
    }

    public function runImportTask($feedId) {
        $feed = craft()->feedMe_feeds->getFeedById($feedId);

        $settings = array(
            'feed' => $feed,
        );

        // If a custom URL param is provided (for direct-processing), use that instead of stored URL
        if (craft()->request->getParam('direct') && craft()->request->getParam('url')) {
            $feed->feedUrl = craft()->request->getParam('url');
        }

        // Are we running from the CP?
        if (craft()->request->isCpRequest()) {
            // Create the import task
            craft()->tasks->createTask('FeedMe', $feed->name, $settings);

            // if not using the direct param for this request, so UI stuff
            craft()->userSession->setNotice(Craft::t('Feed processing started.'));

            $this->redirect('feedme/feeds');
        }

        // If not, are we running directly?
        if (craft()->request->getParam('direct')) {
            if (craft()->request->getParam('passkey') == $feed['passkey']) {
                // Create the import task
                craft()->tasks->createTask('FeedMe', $feed->name, $settings);

                // Trigger the task to run right now!
                if (!craft()->tasks->isTaskRunning()) {
                    $task = craft()->tasks->getNextPendingTask();

                    if ($task) {
                        craft()->tasks->runPendingTasks();
                    }
                }

                // Let the requester know whats going on.
                $this->returnJson(array('success' => 'Feed ID: '.$feed['id'].' - Task started'));
            } else {
                $this->returnJson(array('error' => 'Invalid Passkey'));
            }
        }
    }

}
