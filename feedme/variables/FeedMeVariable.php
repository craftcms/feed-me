<?php
namespace Craft;

use Cake\Utility\Hash as Hash;

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

    public function getElementTypes()
    {
        return craft()->feedMe->getRegisteredElementTypes();
    }

    public function getElementTypeMapping($elementType)
    {
        return craft()->feedMe_elementTypes->getElementTypeMapping($elementType);
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

    public function feedHeaders($options = array())
    {
        return craft()->feedMe_data->getFeedHeadersForTemplate($options);
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

    public function getOrderSettings()
    {
        return craft()->commerce_orderSettings->getOrderSettingByHandle('order');
    }


    // Helper functions for element fields in getting their inner-element field layouts
    public function getAssetFieldLayout($settings)
    {
        $folderSourceId = null;

        if (!$settings) {
            return false;
        }

        if (empty($settings['useSingleFolder'])) {
            $folderSourceId = Hash::get($settings, 'defaultUploadLocationSource');
        } else {
            $folderSourceId = Hash::get($settings, 'singleUploadLocationSource');
        }

        if (!$folderSourceId) {
            return false;
        }

        $source = craft()->assetSources->getSourceById($folderSourceId);

        if (!$source) {
            return false;
        }

        $layoutId = $source->fieldLayoutId;
        return craft()->fields->getLayoutById($layoutId);
    }

    public function getCategoriesFieldLayout($categoryGroup)
    {
        // Craft throws an error if there are no Categories at all
        if (!craft()->categories->getAllGroupIds()) {
            return false;
        }

        // Get the Category Group ID
        $id = str_replace('group:', '', $categoryGroup);
        $group = craft()->categories->getGroupById($id);

        if (!$group) {
            return false;
        }

        // Get the field layout for this Category Group
        $layoutId = $group->fieldLayoutId;
        return craft()->fields->getLayoutById($layoutId);
    }

    public function getEntriesFieldLayout($sources)
    {
        $sectionIds = array();

        // Because an Entry field can have multiple sources selected, we need to filter a bit
        if (is_array($sources)) {
            foreach ($sources as $source) {
                // When singles is selected as the only option to search in, it doesn't contain any ids...
                if ($source == 'singles') {
                    foreach (craft()->sections->getAllSections() as $section) {
                        if ($section->type == 'single') {
                            $sectionIds[] = $section->id;
                        }
                    }
                } else {
                    list($type, $id) = explode(':', $source);
                    $sectionIds[] = $id;
                }
            }
        }

        if (count($sectionIds)) {
            $entryType = craft()->sections->getEntryTypesBySectionId($sectionIds[0]);

            if (!$entryType) {
                return false;
            }

            // Get the field layout of the first entry type for this section
            $layoutId = $entryType[0]->fieldLayoutId;
            return craft()->fields->getLayoutById($layoutId);
        }
    }

    public function getTagsFieldLayout($tagGroup)
    {
        // Get the Tag Group ID
        $id = str_replace('taggroup:', '', $tagGroup);
        $group = craft()->tags->getTagGroupById($id);

        if (!$group) {
            return false;
        }

        // Get the field layout for this Tag Group
        $layoutId = $group->fieldLayoutId;
        return craft()->fields->getLayoutById($layoutId);
    }

    public function getAssetSourceById($id)
    {
        return craft()->assetSources->getSourceById($id);
    }

    public function getAssetFolderBySourceId($id)
    {
        $folders = craft()->assets->getFolderTreeBySourceIds(array($id));

        $return = array();

        $return[''] = 'Don\'t Import';

        if (is_array($folders)) {
            foreach ($folders as $folder) {
                $return[] = array(
                    'value' => 'root',
                    'label' => Craft::t('Root Folder'),
                );

                $children = $folder->getChildren();

                if ($children) {
                    foreach ($children as $childFolder) {
                        $return[] = array(
                            'value' => $childFolder['id'],
                            'label' => $childFolder['name'],
                        );
                    }
                }
            }
        }

        return $return;
    }

    public function getClientUsers()
    {
        $criteria = craft()->elements->getCriteria(ElementType::User);
        $criteria->status = null;
        
        return $criteria->find();
    }






}
