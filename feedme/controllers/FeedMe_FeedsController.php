<?php
namespace Craft;

class FeedMe_FeedsController extends BaseController
{
    // Properties
    // =========================================================================

    protected $allowAnonymous = array('actionRunTask');


    // Public Methods
    // =========================================================================

    public function actionFeedsIndex()
    {
        $variables['feeds'] = craft()->feedMe_feeds->getFeeds();

        $this->renderTemplate('feedme/feeds/index', $variables);
    }

    public function actionEditFeed(array $variables = array())
    {
        if (empty($variables['feed'])) {
            if (!empty($variables['feedId'])) {
                $variables['feed'] = craft()->feedMe_feeds->getFeedById($variables['feedId']);
            } else {
                $variables['feed'] = new FeedMe_FeedModel();
                $variables['feed']->passkey = StringHelper::randomString(10);
            }
        }

        $this->renderTemplate('feedme/feeds/_edit', $variables);
    }

    public function actionMapFeed(array $variables = array())
    {
        if (empty($variables['feed'])) {
            $feed = craft()->feedMe_feeds->getFeedById($variables['feedId']);
            $feedData = craft()->feedMe_data->getFeedMapping($feed->feedType, $feed->feedUrl, $feed->primaryElement, $feed);

            $variables['feed'] = $feed;

            if ($feedData) {
                $variables['feedRawData'] = $feedData;
            }
        }

        $this->renderTemplate('feedme/feeds/_map', $variables);
    }

    public function actionRunFeed(array $variables = array())
    {
        $feed = craft()->feedMe_feeds->getFeedById($variables['feedId']);

        $variables['feed'] = $feed;
        $variables['task'] = $this->_runImportTask($feed->id);

        if (craft()->request->getParam('direct')) {
            $this->renderTemplate('feedme/feeds/_direct', $variables);
        } else {
            $this->renderTemplate('feedme/feeds/_run', $variables);
        }
    }

    public function actionSaveFeed()
    {
        $feed = $this->_getModelFromPost();

        $this->_saveAndRedirect($feed, 'feedme/feeds/', true);
    }

    public function actionSaveAndMapFeed()
    {
        $feed = $this->_getModelFromPost();

        $this->_saveAndRedirect($feed, 'feedme/feeds/map/', true);
    }

    public function actionSaveAndImportFeed()
    {
        $feed = $this->_getModelFromPost();

        $this->_saveAndRedirect($feed, 'feedme/feeds/run/', true);
    }

    public function actionDeleteFeed()
    {
        $this->requirePostRequest();
        $this->requireAjaxRequest();

        $feedId = craft()->request->getRequiredPost('id');

        craft()->feedMe_feeds->deleteFeedById($feedId);
        $this->returnJson(array('success' => true));
    }

    public function actionRunTask(array $variables = array())
    {
        if (craft()->request->getParam('feedId')) {
            $variables = array('feedId' => craft()->request->getParam('feedId'));
            $this->actionRunFeed($variables);
        }
    }

    public function actionDebug()
    {
        $feedId = craft()->request->getParam('feedId');
        $limit = craft()->request->getParam('limit');

        craft()->feedMe_process->debugFeed($feedId, $limit);
        craft()->end();
    }

    



    // Private Methods
    // =========================================================================

    private function _runImportTask($feedId)
    {
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

            // if not using the direct param for this request, do UI stuff 
            craft()->userSession->setNotice(Craft::t('Feed processing started.'));
        }

        // If not, are we running directly?
        if (craft()->request->getParam('direct')) {
            $proceed = craft()->request->getParam('passkey') == $feed['passkey'];

            // Create the import task only if provided the correct passkey
            if ($proceed) {
                craft()->tasks->createTask('FeedMe', $feed->name, $settings);
            }

            return $proceed;
        }
    }

    private function _saveAndRedirect($feed, $redirect, $withId = false)
    {
        if (craft()->feedMe_feeds->saveFeed($feed)) {
            craft()->userSession->setNotice(Craft::t('Feed saved.'));

            if ($withId) {
                $redirect = $redirect . $feed->id;
            }

            $this->redirect($redirect);
        } else {
            craft()->userSession->setError(Craft::t('Couldn’t save feed: ' . implode($feed->getAllErrors(), ' ')));
        }

        craft()->urlManager->setRouteVariables(array('feed' => $feed));
    }

    private function _getModelFromPost()
    {
        $this->requirePostRequest();

        if (craft()->request->getPost('feedId')) {
            $feed = craft()->feedMe_feeds->getFeedById(craft()->request->getPost('feedId'));
        } else {
            $feed = new FeedMe_FeedModel();
        }

        $feed->name             = craft()->request->getRequiredPost('name', $feed->name);
        $feed->feedUrl          = craft()->request->getRequiredPost('feedUrl', $feed->feedUrl);
        $feed->feedType         = craft()->request->getRequiredPost('feedType', $feed->feedType);
        $feed->primaryElement   = craft()->request->getPost('primaryElement', $feed->primaryElement);
        $feed->elementType      = craft()->request->getRequiredPost('elementType', $feed->elementType);
        $feed->elementGroup     = craft()->request->getPost('elementGroup', $feed->elementGroup);
        $feed->locale           = craft()->request->getPost('locale', $feed->locale);
        $feed->duplicateHandle  = craft()->request->getPost('duplicateHandle', $feed->duplicateHandle);
        $feed->passkey          = craft()->request->getRequiredPost('passkey', $feed->passkey);
        $feed->backup           = craft()->request->getPost('backup', $feed->backup);

        // Don't overwrite mappings when saving from first screen
        if (craft()->request->getPost('fieldMapping')) {
            $feed->fieldMapping = craft()->request->getPost('fieldMapping');
        }
        
        if (craft()->request->getPost('fieldDefaults')) {
            $feed->fieldDefaults = craft()->request->getPost('fieldDefaults');
        }
        
        if (craft()->request->getPost('fieldElementMapping')) {
            $feed->fieldElementMapping = craft()->request->getPost('fieldElementMapping');
        }
        
        if (craft()->request->getPost('fieldElementDefaults')) {
            $feed->fieldElementDefaults = craft()->request->getPost('fieldElementDefaults');
        }
        
        if (craft()->request->getPost('fieldUnique')) {
            $feed->fieldUnique = craft()->request->getPost('fieldUnique');
        }

        // Check conditionally on Element Group fields - depending on the Element Type selected
        if (isset($feed->elementGroup[$feed->elementType])) {
            $elementGroup = $feed->elementGroup[$feed->elementType];

            if ($feed->elementType == 'Category') {
                if (empty($elementGroup)) {
                    $feed->addError('elementGroup', Craft::t('Category Group is required'));
                }
            }

            if ($feed->elementType == 'Entry') {
                if (empty($elementGroup['section']) || empty($elementGroup['entryType'])) {
                    $feed->addError('elementGroup', Craft::t('Entry Section and Type are required'));
                }
            }

            if ($feed->elementType == 'Commerce_Product') {
                if (empty($elementGroup)) {
                    $feed->addError('elementGroup', Craft::t('Commerce Product Type is required'));
                }
            }

            if ($feed->elementType == 'User') {
                if (empty($elementGroup)) {
                    $feed->addError('elementGroup', Craft::t('User Group Type is required'));
                }
            }
        }

        return $feed;
    }

}
