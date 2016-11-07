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
        return $this->getPlugin('feedMe')->getPluginUrl();
    }

    public function getPluginName()
    {
        return $this->getPlugin('feedMe')->getName();
    }

    public function getPluginVersion()
    {
        return $this->getPlugin()->getVersion();
    }

    public function getSelectOptions($options, $label = 'name', $index = 'id', $includeNone = true) {
        $values = array();

        if ($includeNone) {
            $values[''] = 'None';
        }

        if (is_array($options)) {
            foreach ($options as $key => $value) {
                $values[$value[$index]] = $value[$label];
            }
        }

        return $values;
    }

    public function getElementTypeGroups($elementType)
    {
        if ($service = craft()->feedMe->getElementTypeService($elementType)) {
            return $service->getGroups();
        }

        return false;
    }

    public function getElementTypeGroupsTemplate($elementType)
    {
        if ($service = craft()->feedMe->getElementTypeService($elementType)) {
            return $service->getGroupsTemplate();
        }

        return false;
    }

    public function getElementTypeColumnTemplate($elementType)
    {
        if ($service = craft()->feedMe->getElementTypeService($elementType)) {
            return $service->getColumnTemplate();
        }

        return false;
    }

    public function getEntryTypeById($entryTypeId)
    {
        return craft()->sections->getEntryTypeById($entryTypeId);
    }

    public function feed($options = array())
    {
        return craft()->feedMe_data->getFeedForTemplate($options);
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

    public function isProEdition()
    {
        return craft()->feedMe_license->isProEdition();
    }

    //
    // Fields + Field Mapping
    //

    public function getFieldMapping($fieldHandle)
    {
        return craft()->feedMe_fields->getFieldMapping($fieldHandle);
    }

    public function formatDateTime($dateTime)
    {
        return DateTime::createFromString($dateTime, craft()->getTimeZone());
    }



    // Helper function for handling Matrix fields
    public function getMatrixBlocks($fieldId)
    {
        return craft()->matrix->getBlockTypesByFieldId($fieldId);
    }

    // Commerce doesn't have a getProductTypeById() function
    public function getProductTypeById($productTypeId)
    {
        return craft()->commerce_productTypes->getProductTypeById($productTypeId);
    }


    // Helper functions for element fields in getting their inner-element field layouts
    public function getAssetFieldLayout($settings)
    {
        if (empty($settings['useSingleFolder'])) {
            $folderSourceId = $settings['defaultUploadLocationSource'];
        } else {
            $folderSourceId = $settings['singleUploadLocationSource'];
        }

        $layoutId = craft()->assetSources->getSourceById($folderSourceId)->fieldLayoutId;
        return craft()->fields->getLayoutById($layoutId);
    }

    public function getCategoriesFieldLayout($categoryGroup)
    {
        $id = str_replace('group:', '', $categoryGroup);
        $layoutId = craft()->categories->getGroupById($id)->fieldLayoutId;
        return craft()->fields->getLayoutById($layoutId);
    }

    public function getTagsFieldLayout($tagGroup)
    {
        $id = str_replace('taggroup:', '', $tagGroup);
        $layoutId = craft()->tags->getTagGroupById($id)->fieldLayoutId;
        return craft()->fields->getLayoutById($layoutId);
    }






}
