<?php

namespace craft\feedme;

use Craft;
use craft\events\RegisterUrlRulesEvent;
use craft\feedme\base\PluginTrait;
use craft\feedme\models\Settings;
use craft\feedme\services\DataTypes;
use craft\feedme\services\Elements;
use craft\feedme\services\Feeds;
use craft\feedme\services\Fields;
use craft\feedme\services\Logs;
use craft\feedme\services\Process;
use craft\feedme\services\Service;
use craft\feedme\web\twig\Extension;
use craft\feedme\web\twig\variables\FeedMeVariable;
use craft\helpers\UrlHelper;
use craft\web\twig\variables\CraftVariable;
use craft\web\UrlManager;
use yii\base\Event;

/**
 * Class Plugin
 *
 * @property-read DataTypes $data
 * @property-read Elements $elements
 * @property-read Feeds $feeds
 * @property-read Fields $fields
 * @property-read Logs $logs
 * @property-read Process $process
 * @property-read void $settingsResponse
 * @property-read mixed $pluginName
 * @property-read mixed $cpNavItem
 * @property-read Service $service
 */
class Plugin extends \craft\base\Plugin
{
    // Public Properties
    // =========================================================================

    public $schemaVersion = '4.4.0';
    public $hasCpSettings = true;
    public $hasCpSection = true;


    // Traits
    // =========================================================================

    use PluginTrait;


    // Public Methods
    // =========================================================================

    /**
     * @inheritDoc
     */
    public function init()
    {
        parent::init();

        self::$plugin = $this;

        $this->_setPluginComponents();
        $this->_registerCpRoutes();
        $this->_registerTwigExtensions();
        $this->_registerVariables();
    }

    /**
     * @inheritDoc
     */
    public function afterInstall()
    {
        if (Craft::$app->getRequest()->getIsConsoleRequest()) {
            return;
        }

        Craft::$app->controller->redirect(UrlHelper::cpUrl('feed-me/welcome'))->send();
    }

    /**
     * @inheritDoc
     */
    public function getSettingsResponse()
    {
        Craft::$app->controller->redirect(UrlHelper::cpUrl('feed-me/settings'));
    }

    /**
     * @inheritDoc
     */
    public function getPluginName()
    {
        return Craft::t('feed-me', $this->getSettings()->pluginName);
    }

    /**
     * @inheritDoc
     */
    public function getCpNavItem()
    {
        $navItem = parent::getCpNavItem();
        $navItem['label'] = $this->getPluginName();

        return $navItem;
    }

    // Protected Methods
    // =========================================================================

    /**
     * @inheritDoc
     */
    protected function createSettingsModel(): Settings
    {
        return new Settings();
    }

    // Private Methods
    // =========================================================================

    /**
     *
     */
    private function _registerTwigExtensions()
    {
        Craft::$app->view->registerTwigExtension(new Extension);
    }

    /**
     *
     */
    private function _registerCpRoutes()
    {
        Event::on(UrlManager::class, UrlManager::EVENT_REGISTER_CP_URL_RULES, function(RegisterUrlRulesEvent $event) {
            $event->rules = array_merge($event->rules, [
                'feed-me/feeds' => 'feed-me/feeds/feeds-index',
                'feed-me/feeds/new' => 'feed-me/feeds/edit-feed',
                'feed-me/feeds/<feedId:\d+>' => 'feed-me/feeds/edit-feed',
                'feed-me/feeds/element/<feedId:\d+>' => 'feed-me/feeds/element-feed',
                'feed-me/feeds/map/<feedId:\d+>' => 'feed-me/feeds/map-feed',
                'feed-me/feeds/run/<feedId:\d+>' => 'feed-me/feeds/run-feed',
                'feed-me/feeds/status/<feedId:\d+>' => 'feed-me/feeds/status-feed',
                'feed-me/logs' => 'feed-me/logs/logs',
                'feed-me/settings/general' => 'feed-me/base/settings',
            ]);
        });
    }

    /**
     *
     */
    private function _registerVariables()
    {
        Event::on(CraftVariable::class, CraftVariable::EVENT_INIT, function(Event $event) {
            $event->sender->set('feedme', FeedMeVariable::class);
        });
    }

}
