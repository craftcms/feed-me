<?php
namespace verbb\feedme;

use verbb\feedme\base\PluginTrait;
use verbb\feedme\models\Settings;
use verbb\feedme\services\Plugin as FeedMePlugin;
use verbb\feedme\web\twig\Extension;
use verbb\feedme\web\twig\variables\FeedMeVariable;

use Craft;
use craft\base\Plugin;
use craft\events\PluginEvent;
use craft\events\RegisterUrlRulesEvent;
use craft\helpers\UrlHelper;
use craft\services\Plugins;
use craft\web\twig\variables\CraftVariable;
use craft\web\UrlManager;

use yii\base\Event;

class FeedMe extends Plugin
{
    // Constants
    // =========================================================================

    const EDITION_LITE = 'lite';
    const EDITION_PRO = 'pro';


    // Public Properties
    // =========================================================================

    public $schemaVersion = '1.0.2';
    public $hasCpSettings = true;
    public $hasCpSection = true;


    // Traits
    // =========================================================================

    use PluginTrait;


    // Public Methods
    // =========================================================================

    public function init()
    {
        parent::init();

        self::$plugin = $this;

        $this->_setPluginComponents();
        $this->_registerCpRoutes();
        $this->_registerTwigExtensions();
        $this->_registerVariables();
    }

    public function afterInstall()
    {
        if (Craft::$app->getRequest()->getIsConsoleRequest()) {
            return;
        }
        
        Craft::$app->controller->redirect(UrlHelper::cpUrl('feed-me/welcome'))->send();
    }

    public function getSettingsResponse()
    {
        Craft::$app->controller->redirect(UrlHelper::cpUrl('feed-me/settings'));
    }

    public function getPluginName()
    {
        return Craft::t('feed-me', $this->getSettings()->pluginName);
    }

    public function getCpNavItem()
    {
        $navItem = parent::getCpNavItem();
        $navItem['label'] = $this->getPluginName();

        return $navItem;
    }

    public static function editions(): array
    {
        return [
            self::EDITION_LITE,
            self::EDITION_PRO,
        ];
    }


    // Protected Methods
    // =========================================================================

    protected function createSettingsModel(): Settings
    {
        return new Settings();
    }


    // Private Methods
    // =========================================================================

    private function _registerTwigExtensions()
    {
        Craft::$app->view->registerTwigExtension(new Extension);
    }

    private function _registerCpRoutes()
    {
        Event::on(UrlManager::class, UrlManager::EVENT_REGISTER_CP_URL_RULES, function(RegisterUrlRulesEvent $event) {
            $event->rules = array_merge($event->rules, [
                'feed-me/feeds' => 'feed-me/feeds/feeds-index',
                'feed-me/feeds/new' => 'feed-me/feeds/edit-feed',
                'feed-me/feeds/<feedId:\d+>' => 'feed-me/feeds/edit-feed',
                'feed-me/feeds/element/<feedId:\d+>'=> 'feed-me/feeds/element-feed',
                'feed-me/feeds/map/<feedId:\d+>' => 'feed-me/feeds/map-feed',
                'feed-me/feeds/run/<feedId:\d+>' => 'feed-me/feeds/run-feed',
                'feed-me/feeds/status/<feedId:\d+>' => 'feed-me/feeds/status-feed',
                'feed-me/logs' => 'feed-me/logs/logs',
                'feed-me/settings/general' => 'feed-me/base/settings',
            ]);
        });
    }

    private function _registerVariables()
    {
        Event::on(CraftVariable::class, CraftVariable::EVENT_INIT, function(Event $event) {
            $event->sender->set('feedme', FeedMeVariable::class);
        });
    }
    
}
