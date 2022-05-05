<?php

namespace craft\feedme\controllers;

use Cake\Utility\Hash;
use Craft;
use craft\errors\MissingComponentException;
use craft\feedme\models\FeedModel;
use craft\feedme\Plugin;
use craft\feedme\queue\jobs\FeedImport;
use craft\helpers\Json;
use craft\helpers\StringHelper;
use craft\web\Controller;
use Exception;
use Throwable;
use yii\base\ExitException;
use yii\web\BadRequestHttpException;
use yii\web\Response;

/**
 * @property Plugin $module
 */
class FeedsController extends Controller
{
    // Properties
    // =========================================================================

    /**
     * @var string[]
     */
    protected int|bool|array $allowAnonymous = ['run-task'];


    // Public Methods
    // =========================================================================

    /**
     * @return Response
     */
    public function actionFeedsIndex(): Response
    {
        $variables['feeds'] = Plugin::$plugin->feeds->getFeeds();

        return $this->renderTemplate('feed-me/feeds/index', $variables);
    }

    /**
     * @param null $feedId
     * @param null $feed
     * @return Response
     */
    public function actionEditFeed($feedId = null, $feed = null): Response
    {
        $variables = [];

        if (!$feed) {
            if ($feedId) {
                $variables['feed'] = Plugin::$plugin->feeds->getFeedById($feedId);
            } else {
                $variables['feed'] = new FeedModel();
                $variables['feed']->passkey = StringHelper::randomString(10);
            }
        } else {
            $variables['feed'] = $feed;
        }

        $variables['dataTypes'] = Plugin::$plugin->data->dataTypesList();
        $variables['elements'] = Plugin::$plugin->elements->getRegisteredElements();

        return $this->renderTemplate('feed-me/feeds/_edit', $variables);
    }

    /**
     * @param null $feedId
     * @param null $postData
     * @return Response
     */
    public function actionElementFeed($feedId = null, $postData = null): Response
    {
        $variables = [];

        $feed = Plugin::$plugin->feeds->getFeedById($feedId);

        if ($postData) {
            $feed = Hash::merge($feed, $postData);
        }

        $variables['primaryElements'] = $feed->getFeedNodes();
        $variables['feedMappingData'] = $feed->getFeedMapping(false);
        $variables['feed'] = $feed;

        return $this->renderTemplate('feed-me/feeds/_element', $variables);
    }

    /**
     * @param null $feedId
     * @param null $postData
     * @return Response
     */
    public function actionMapFeed($feedId = null, $postData = null): Response
    {
        $variables = [];

        $feed = Plugin::$plugin->feeds->getFeedById($feedId);

        if ($postData) {
            $feed = Hash::merge($feed, $postData);
        }

        $variables['feedMappingData'] = $feed->getFeedMapping();
        $variables['feed'] = $feed;

        return $this->renderTemplate('feed-me/feeds/_map', $variables);
    }

    /**
     * @param null $feedId
     * @return Response
     * @throws \yii\base\Exception
     */
    public function actionRunFeed($feedId = null): Response
    {
        $request = Craft::$app->getRequest();

        $feed = Plugin::$plugin->feeds->getFeedById($feedId);

        $return = $request->getParam('return') ?: 'feed-me';

        $variables['feed'] = $feed;
        $variables['task'] = $this->_runImportTask($feed);

        if ($request->getParam('direct')) {
            $view = $this->getView();
            $view->setTemplateMode($view::TEMPLATE_MODE_CP);
            return $this->renderTemplate('feed-me/feeds/_direct', $variables);
        }

        return $this->redirect($return);
    }

    /**
     * @param null $feedId
     * @return Response
     */
    public function actionStatusFeed($feedId = null): Response
    {
        $feed = Plugin::$plugin->feeds->getFeedById($feedId);

        $variables['feed'] = $feed;

        return $this->renderTemplate('feed-me/feeds/_status', $variables);
    }

    /**
     * @return Response|null
     * @throws BadRequestHttpException
     * @throws MissingComponentException
     */
    public function actionSaveFeed(): ?Response
    {
        $feed = $this->_getModelFromPost();

        return $this->_saveAndRedirect($feed, 'feed-me/feeds/', true);
    }

    /**
     * @return Response|null
     * @throws BadRequestHttpException
     * @throws MissingComponentException
     */
    public function actionSaveAndElementFeed(): ?Response
    {
        $feed = $this->_getModelFromPost();

        if ($feed->getErrors()) {
            $this->setFailFlash(Craft::t('feed-me', 'Couldn’t save the feed.'));

            // Send the category group back to the template
            Craft::$app->getUrlManager()->setRouteParams([
                'feed' => $feed,
            ]);

            return null;
        }

        return $this->_saveAndRedirect($feed, 'feed-me/feeds/element/', true);
    }

    /**
     * @return Response|null
     * @throws BadRequestHttpException
     * @throws MissingComponentException
     */
    public function actionSaveAndMapFeed(): ?Response
    {
        $feed = $this->_getModelFromPost();

        return $this->_saveAndRedirect($feed, 'feed-me/feeds/map/', true);
    }

    /**
     * @return Response|null
     * @throws BadRequestHttpException
     * @throws MissingComponentException
     */
    public function actionSaveAndReviewFeed(): ?Response
    {
        $feed = $this->_getModelFromPost();

        return $this->_saveAndRedirect($feed, 'feed-me/feeds/status/', true);
    }

    /**
     * @return Response
     * @throws MissingComponentException
     */
    public function actionSaveAndDuplicateFeed(): Response
    {
        $request = Craft::$app->getRequest();

        $feedId = $request->getParam('feedId');
        $feed = Plugin::$plugin->feeds->getFeedById($feedId);

        Plugin::$plugin->feeds->duplicateFeed($feed);

        Craft::$app->getSession()->setNotice(Craft::t('feed-me', 'Feed duplicated.'));

        return $this->redirect('feed-me/feeds');
    }

    /**
     * @return Response
     * @throws BadRequestHttpException
     * @throws \yii\db\Exception
     */
    public function actionDeleteFeed(): Response
    {
        $this->requirePostRequest();

        $request = Craft::$app->getRequest();

        $feedId = $request->getRequiredBodyParam('id');

        Plugin::$plugin->feeds->deleteFeedById($feedId);

        return $this->asJson(['success' => true]);
    }

    /**
     * @throws \yii\base\Exception
     * @throws ExitException
     */
    public function actionRunTask(): void
    {
        $request = Craft::$app->getRequest();

        $feedId = $request->getParam('feedId');

        if ($feedId) {
            $this->actionRunFeed($feedId);
        }

        Craft::$app->end();
    }

    /**
     * @return false|string
     * @throws Exception
     */
    public function actionDebug(): bool|string
    {
        $request = Craft::$app->getRequest();

        $feedId = $request->getParam('feedId');
        $limit = $request->getParam('limit');
        $offset = $request->getParam('offset');

        $feed = Plugin::$plugin->feeds->getFeedById($feedId);

        ob_start();

        // Keep track of processed elements here - particularly for paginated feeds
        $processedElementIds = [];

        Plugin::$plugin->process->debugFeed($feed, $limit, $offset, $processedElementIds);

        return ob_get_clean();
    }

    /**
     * @return Response
     * @throws Throwable
     * @throws BadRequestHttpException
     */
    public function actionReorderFeeds(): Response
    {
        $this->requirePostRequest();
        $this->requireAcceptsJson();

        $feedIds = Json::decode(Craft::$app->getRequest()->getRequiredBodyParam('ids'));
        $feedIds = array_filter($feedIds);
        Plugin::$plugin->getFeeds()->reorderFeeds($feedIds);

        return $this->asJson(['success' => true]);
    }


    // Private Methods
    // =========================================================================

    /**
     * @param $feed
     * @return bool|null
     * @throws MissingComponentException
     */
    private function _runImportTask($feed): ?bool
    {
        $request = Craft::$app->getRequest();

        $direct = $request->getParam('direct');
        $passkey = $request->getParam('passkey');
        $url = $request->getParam('url');

        $limit = $request->getParam('limit');
        $offset = $request->getParam('offset');

        // Keep track of processed elements here - particularly for paginated feeds
        $processedElementIds = [];

        // Are we running from the CP?
        if ($request->getIsCpRequest()) {
            // if not using the direct param for this request, do UI stuff
            Craft::$app->getSession()->setNotice(Craft::t('feed-me', 'Feed processing started.'));

            // Create the import task
            $this->module->queue->push(new FeedImport([
                'feed' => $feed,
                'limit' => $limit,
                'offset' => $offset,
                'processedElementIds' => $processedElementIds,
            ]));
        }

        // If not, are we running directly?
        if ($direct) {
            // If a custom URL param is provided (for direct-processing), use that instead of stored URL
            if ($url) {
                $feed->feedUrl = $url;
            }

            $proceed = $passkey == $feed['passkey'];

            // Create the import task only if provided the correct passkey
            if ($proceed) {
                $this->module->queue->push(new FeedImport([
                    'feed' => $feed,
                    'limit' => $limit,
                    'offset' => $offset,
                    'processedElementIds' => $processedElementIds,
                ]));
            }

            return $proceed;
        }

        return null;
    }

    /**
     * @param $feed
     * @param $redirect
     * @param false $withId
     * @return Response|null
     * @throws MissingComponentException
     */
    private function _saveAndRedirect($feed, $redirect, bool $withId = false): ?Response
    {
        if (!Plugin::$plugin->feeds->saveFeed($feed)) {
            Craft::$app->getSession()->setError(Craft::t('feed-me', 'Unable to save feed.'));

            Craft::$app->getUrlManager()->setRouteParams([
                'feed' => $feed,
            ]);

            return null;
        }

        Craft::$app->getSession()->setNotice(Craft::t('feed-me', 'Feed saved.'));

        if ($withId) {
            $redirect .= $feed->id;
        }

        return $this->redirect($redirect);
    }

    /**
     * @return FeedModel
     * @throws BadRequestHttpException
     */
    private function _getModelFromPost(): FeedModel
    {
        $this->requirePostRequest();

        $request = Craft::$app->getRequest();

        if ($request->getBodyParam('feedId')) {
            $feed = Plugin::$plugin->feeds->getFeedById($request->getBodyParam('feedId'));
        } else {
            $feed = new FeedModel();
        }

        $feed->name = $request->getBodyParam('name', $feed->name);
        $feed->feedUrl = $request->getBodyParam('feedUrl', $feed->feedUrl);
        $feed->feedType = $request->getBodyParam('feedType', $feed->feedType);
        $feed->primaryElement = $request->getBodyParam('primaryElement', $feed->primaryElement);
        $feed->elementType = $request->getBodyParam('elementType', $feed->elementType);
        $feed->elementGroup = $request->getBodyParam('elementGroup', $feed->elementGroup);
        $feed->siteId = $request->getBodyParam('siteId', $feed->siteId);
        $feed->singleton = $request->getBodyParam('singleton', $feed->singleton);
        $feed->duplicateHandle = $request->getBodyParam('duplicateHandle', $feed->duplicateHandle);
        $feed->updateSearchIndexes = (bool)$request->getBodyParam('updateSearchIndexes', $feed->updateSearchIndexes);
        $feed->paginationNode = $request->getBodyParam('paginationNode', $feed->paginationNode);
        $feed->passkey = $request->getBodyParam('passkey', $feed->passkey);
        $feed->backup = (bool)$request->getBodyParam('backup', $feed->backup);

        // Don't overwrite mappings when saving from first screen
        if ($request->getBodyParam('fieldMapping')) {
            $feed->fieldMapping = $request->getBodyParam('fieldMapping');
        }

        if ($request->getBodyParam('fieldUnique')) {
            $feed->fieldUnique = $request->getBodyParam('fieldUnique');
        }

        // Check conditionally on Element Group fields - depending on the Element Type selected
        if (isset($feed->elementGroup[$feed->elementType])) {
            $elementGroup = $feed->elementGroup[$feed->elementType];

            if (($feed->elementType === 'craft\elements\Category') && empty($elementGroup)) {
                $feed->addError('elementGroup', Craft::t('feed-me', 'Category Group is required'));
            }

            if ($feed->elementType === 'craft\elements\Entry') {
                if (empty($elementGroup['section']) || empty($elementGroup['entryType'])) {
                    $feed->addError('elementGroup', Craft::t('feed-me', 'Entry Section and Type are required'));
                }
            }

            if (($feed->elementType === 'craft\commerce\elements\Product') && empty($elementGroup)) {
                $feed->addError('elementGroup', Craft::t('feed-me', 'Commerce Product Type is required'));
            }

            if (($feed->elementType === 'craft\digitalproducts\elements\Product') && empty($elementGroup)) {
                $feed->addError('elementGroup', Craft::t('feed-me', 'Digital Product Group is required'));
            }

            if (($feed->elementType === 'craft\elements\Asset') && empty($elementGroup)) {
                $feed->addError('elementGroup', Craft::t('feed-me', 'Asset Volume is required'));
            }

            if (($feed->elementType === 'craft\elements\Tag') && empty($elementGroup)) {
                $feed->addError('elementGroup', Craft::t('feed-me', 'Tag Group is required'));
            }

            if (($feed->elementType === 'Solspace\Calendar\Elements\Event') && empty($elementGroup)) {
                $feed->addError('elementGroup', Craft::t('feed-me', 'Calendar is required'));
            }
        }

        return $feed;
    }
}
