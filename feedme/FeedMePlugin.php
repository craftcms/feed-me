<?php
namespace Craft;

// http://craftcms.stackexchange.com/questions/1097/best-way-to-use-custom-enums-in-my-plugin-templates
include(dirname(__FILE__) . '/enums/FeedMe_Duplicate.php');
include(dirname(__FILE__) . '/enums/FeedMe_Element.php');
include(dirname(__FILE__) . '/enums/FeedMe_FeedType.php');
include(dirname(__FILE__) . '/enums/FeedMe_FieldType.php');
include(dirname(__FILE__) . '/enums/FeedMe_Status.php');

class FeedMePlugin extends BasePlugin
{
    // =========================================================================
    // PLUGIN INFO
    // =========================================================================

    public function getName()
    {
        $pluginName = Craft::t('Feed Me');
        $pluginNameOverride = $this->getSettings()->pluginNameOverride;

        return ($pluginNameOverride) ? $pluginNameOverride : $pluginName;
    }

    public function getVersion()
    {
        return '1.4.12';
    }

    public function getSchemaVersion()
    {
        return '1.2.0';
    }

    public function getDeveloper()
    {
        return 'S. Group';
    }

    public function getDeveloperUrl()
    {
        return 'http://sgroup.com.au';
    }

    public function getPluginUrl()
    {
        return 'https://github.com/engram-design/FeedMe';
    }

    public function getDocumentationUrl()
    {
        return $this->getPluginUrl() . '/blob/master/README.md';
    }

    public function getReleaseFeedUrl()
    {
        return 'https://raw.githubusercontent.com/engram-design/FeedMe/master/changelog.json';
    }

    public function hasCpSection()
    {
        return true;
    }

    public function getSettingsHtml()
    {
        return craft()->templates->render('feedme/settings', array(
            'settings' => $this->getSettings()
        ));
    }

    protected function defineSettings()
    {
        return array(
            'pluginNameOverride'    => AttributeType::String,
            'cache'                 => array(AttributeType::Number, 'default' => 60),
            'enabledTabs'           => array(AttributeType::Mixed, 'default' => true),
        );
    }

    public function registerCpRoutes()
    {
        return array(
            'feedme'                            => array('action' => 'FeedMe/feeds/feedsIndex'),
            'feedme/feeds'                      => array('action' => 'FeedMe/feeds/feedsIndex'),
            'feedme/feeds/new'                  => array('action' => 'FeedMe/feeds/editFeed'),
            'feedme/feeds/(?P<feedId>\d+)'      => array('action' => 'FeedMe/feeds/editFeed'),
            'feedme/runTask/(?P<feedId>\d+)'    => array('action' => 'FeedMe/feeds/runTask'),

            'feedme/logs'                       => array('action' => 'FeedMe/logs/logs'),
        );
    }

    public function onBeforeInstall()
    {   
        // Craft 2.3.2636 fixed an issue with BaseEnum::getConstants()
        if (version_compare(craft()->getVersion() . '.' . craft()->getBuild(), '2.3.2636', '<')) {
            throw new Exception($this->getName() . ' requires Craft CMS 2.3.2636+ in order to run.');
        }
    }


    // =========================================================================
    // HOOKS
    // =========================================================================

    public function addTwigExtension()
    {
        Craft::import('plugins.feedme.twigextensions.UniqidTwigExtension');
        return new UniqidTwigExtension();
    }
 
}
