<?php
namespace Craft;

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
        return '1.0';
    }

    public function getDeveloper()
    {
        return 'S. Group';
    }

    public function getDeveloperUrl()
    {
        return 'http://sgroup.com.au';
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

            'feedme/logs/(?P<logsId>\d+)'       => 'feedme/logs/_log',
        );
    }


    /* --------------------------------------------------------------
    * HOOKS
    * ------------------------------------------------------------ */
 
}
