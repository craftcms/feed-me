<?php
namespace Craft;

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
        return '2.0.0 beta 1';
    }

    public function getSchemaVersion()
    {
        return '2.0.0 beta 1';
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

    public function getSettingsUrl()
    {
        return 'feedme/settings';
    }

    public function registerCpRoutes()
    {
        return array(
            'feedme' => array('action' => 'feedMe/feeds/feedsIndex'),
            'feedme/feeds' => array('action' => 'feedMe/feeds/feedsIndex'),
            'feedme/feeds/new' => array('action' => 'feedMe/feeds/editFeed'),
            'feedme/feeds/(?P<feedId>\d+)' => array('action' => 'feedMe/feeds/editFeed'),
            'feedme/feeds/map/(?P<feedId>\d+)' => array('action' => 'feedMe/feeds/mapFeed'),
            'feedme/feeds/run/(?P<feedId>\d+)' => array('action' => 'feedMe/feeds/runFeed'),
            'feedme/logs' => array('action' => 'feedMe/logs/logs'),
            'feedme/settings/general' => array('action' => 'feedMe/settings'),
            'feedme/settings/license' => array('action' => 'feedMe/license/edit'),
        );
    }

    protected function defineSettings()
    {
        return array(
            'pluginNameOverride' => AttributeType::String,
            'cache' => array(AttributeType::Number, 'default' => 60),
            'enabledTabs' => array(AttributeType::Mixed, 'default' => true),
            'edition' => array(AttributeType::Mixed),
        );
    }

    public function onBeforeInstall()
    {
        $version = craft()->getVersion();

        // Craft 2.6.2951 deprecated `craft()->getBuild()`, so get the version number consistently
        if (version_compare(craft()->getVersion(), '2.6.2951', '<')) {
            $version = craft()->getVersion() . '.' . craft()->getBuild();
        }

        // Craft 2.3.2636 fixed an issue with BaseEnum::getConstants()
        if (version_compare($version, '2.3.2636', '<')) {
            throw new Exception($this->getName() . ' requires Craft CMS 2.3.2636+ in order to run.');
        }
    }

    public function init()
    {
        Craft::import('plugins.feedme.FeedMe.DataTypes.*');
        Craft::import('plugins.feedme.FeedMe.ElementTypes.*');
        Craft::import('plugins.feedme.FeedMe.FieldTypes.*');
        Craft::import('plugins.feedme.FeedMe.License.*');

        if (craft()->request->isCpRequest()) {
            craft()->feedMe_license->ping();
        }
    }


    // =========================================================================
    // HOOKS
    // =========================================================================

    // Native Data Type Support
    public function registerFeedMeDataTypes()
    {
        return array(
            new JsonFeedMeDataType(),
            new XmlFeedMeDataType(),
        );
    }

    // Native Element Type Support
    public function registerFeedMeElementTypes()
    {
        if (craft()->feedMe_license->isProEdition()) {
            return array(
                new CategoryFeedMeElementType(),
                new Commerce_ProductFeedMeElementType(),
                new EntryFeedMeElementType(),
                new UserFeedMeElementType(),
            );
        } else {
            return array(
                new EntryFeedMeElementType(),
            );
        }
    }

    // Native Field Type Support
    public function registerFeedMeFieldTypes()
    {
        return array(
            new AssetsFeedMeFieldType(),
            new CategoriesFeedMeFieldType(),
            new CheckboxesFeedMeFieldType(),
            new DateFeedMeFieldType(),
            new DefaultFeedMeFieldType(),
            new DropdownFeedMeFieldType(),
            new EntriesFeedMeFieldType(),
            new LightswitchFeedMeFieldType(),
            new MatrixFeedMeFieldType(),
            new MultiSelectFeedMeFieldType(),
            new NumberFeedMeFieldType(),
            new RadioButtonsFeedMeFieldType(),
            new TableFeedMeFieldType(),
            new TagsFeedMeFieldType(),
            new UsersFeedMeFieldType(),
        );
    }
 
}
