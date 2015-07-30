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
    /* --------------------------------------------------------------
    * PLUGIN INFO
    * ------------------------------------------------------------ */

    public function getName()
    {
        $pluginName = Craft::t('Feed Me');
        $pluginNameOverride = $this->getSettings()->pluginNameOverride;

        return ($pluginNameOverride) ? $pluginNameOverride : $pluginName;
    }

    public function getVersion()
    {
        return '1.3.1';
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

    public function onAfterInstall()
    {
        $minBuild = '2636';

        if (craft()->getBuild() < $minBuild) {
            craft()->plugins->disablePlugin($this->getClassHandle());

            craft()->plugins->uninstallPlugin($this->getClassHandle());

            craft()->userSession->setError(Craft::t('{plugin} only works on Craft build {build} or higher', array(
                'plugin' => $this->getName(),
                'build' => $minBuild,
            )));
        }
    }


    /* --------------------------------------------------------------
    * HOOKS
    * ------------------------------------------------------------ */

    public function addTwigExtension()
    {
        Craft::import('plugins.feedme.twigextensions.UniqidTwigExtension');
        return new UniqidTwigExtension();
    }
 
}
