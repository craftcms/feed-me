<?php
namespace Craft;

class FeedMeVariable
{
    public function getPlugin()
    {
        return craft()->plugins->getPlugin('feedMe');
    }

    public function getPluginUrl()
    {
        return $this->getPlugin()->getPluginUrl();
    }

    public function getPluginName()
    {
        return $this->getPlugin()->getName();
    }

    public function getPluginVersion()
    {
        return $this->getPlugin()->getVersion();
    }

    public function getCpTabs()
    {
        $settings = $this->getPlugin()->settings;
        $tabs = array();

        if ($settings['enabledTabs']) {
            if (in_array('feeds', (array)$settings['enabledTabs']) || $settings['enabledTabs'] == '*') {
                $tabs['feeds'] = array(
                    'label' => Craft::t('Feeds'),
                    'url' => UrlHelper::getUrl('feedme'),
                );
            }

            if (in_array('logs', (array)$settings['enabledTabs']) || $settings['enabledTabs'] == '*') {
                $tabs['logs'] = array(
                    'label' => Craft::t('Logs'),
                    'url' => UrlHelper::getUrl('feedme/logs'),
                );
            }

            if (in_array('help', (array)$settings['enabledTabs']) || $settings['enabledTabs'] == '*') {
                $tabs['help'] = array(
                    'label' => Craft::t('Help'),
                    'url' => UrlHelper::getUrl('feedme/help'),
                );
            }

            if (in_array('settings', (array)$settings['enabledTabs']) || $settings['enabledTabs'] == '*') {
                $tabs['settings'] = array(
                    'label' => Craft::t('Settings'),
                    'url' => UrlHelper::getUrl('settings/plugins/feedme'),
                );
            }
        }

        return $tabs;
    }

    public function getSelectOptions($options, $includeNull = true) {
        if ($includeNull) { $values[null] = 'None'; }

        foreach($options as $key => $value) {
            $values[$value['id']] = $value['name'];
        }
        return $values;
    }

    public function getGroups()
    {
        return craft()->feedMe_entry->getGroups();
    }

    public function logs()
    {
        return craft()->feedMe_logs->show();
    }

    public function log($logs)
    {
        return craft()->feedMe_logs->showLog($logs);
    }

    public function feed($options = array())
    {
        return craft()->feedMe_feeds->getFeedForTemplate($options);
    }

    public function getFeeds()
    {
        $result = array();

        $feeds = craft()->feedMe_feeds->getFeeds();

        foreach ($feeds as $key => $feed) {
            $result[$feed->id] = $feed->name;
        }

        return $result;
    }


    // Helper function for handling Matrix fields
    public function getMatrixBlocks($fieldId)
    {
        return craft()->matrix->getBlockTypesByFieldId($fieldId);
    }

    public function getSuperTableBlocks($fieldId)
    {
        return craft()->superTable->getBlockTypesByFieldId($fieldId);
    }



}
