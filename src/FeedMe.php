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
        $this->_addTwigExtensions();

        // Register CP routes
        Event::on(UrlManager::class, UrlManager::EVENT_REGISTER_CP_URL_RULES, [$this, 'registerCpUrlRules']);

        // Plugin Install event
        Event::on(Plugins::class, Plugins::EVENT_AFTER_INSTALL_PLUGIN, [$this, 'afterInstallPlugin']);

        // Setup Variables class (for backwards compatibility)
        Event::on(CraftVariable::class, CraftVariable::EVENT_INIT, function(Event $event) {
            $variable = $event->sender;
            $variable->set('feedme', FeedMeVariable::class);
        });
    }

    public function getPluginName()
    {
        return Craft::t('feed-me', $this->getSettings()->pluginName);
    }

    public function registerCpUrlRules(RegisterUrlRulesEvent $event)
    {
        $rules = [
            'feed-me/feeds'                     => 'feed-me/feeds/feeds-index',
            'feed-me/feeds/new'                 => 'feed-me/feeds/edit-feed',
            'feed-me/feeds/<feedId:\d+>'        => 'feed-me/feeds/edit-feed',
            'feed-me/feeds/element/<feedId:\d+>'=> 'feed-me/feeds/element-feed',
            'feed-me/feeds/map/<feedId:\d+>'    => 'feed-me/feeds/map-feed',
            'feed-me/feeds/run/<feedId:\d+>'    => 'feed-me/feeds/run-feed',
            'feed-me/feeds/status/<feedId:\d+>' => 'feed-me/feeds/status-feed',
            'feed-me/logs'                      => 'feed-me/logs/logs',
            'feed-me/settings/general'          => 'feed-me/base/settings',
        ];

        $event->rules = array_merge($event->rules, $rules);
    }

    public function afterInstallPlugin(PluginEvent $event)
    {
        $isCpRequest = Craft::$app->getRequest()->isCpRequest;

        if ($event->plugin === $this && $isCpRequest) {
            Craft::$app->controller->redirect(UrlHelper::cpUrl('feed-me/welcome'))->send();
        }
    }

    public function getSettingsResponse()
    {
        Craft::$app->controller->redirect(UrlHelper::cpUrl('feed-me/settings'));
    }

    public function getCpNavItem()
    {
        $navItem = parent::getCpNavItem();
        $navItem['label'] = $this->getPluginName();

        return $navItem;
    }


    // Protected Methods
    // =========================================================================

    protected function createSettingsModel()
    {
        return new Settings();
    }


    // Private Methods
    // =========================================================================

    private function _addTwigExtensions()
    {
        Craft::$app->view->registerTwigExtension(new Extension);
    }
}
