<?php
namespace Craft;

use Cake\Utility\Hash as Hash;

class AssetFeedMeElementType extends BaseFeedMeElementType
{
    // Templates
    // =========================================================================

    public function getGroupsTemplate()
    {
        return 'feedme/_includes/elements/asset/groups';
    }

    public function getColumnTemplate()
    {
        return 'feedme/_includes/elements/asset/column';
    }


    // Public Methods
    // =========================================================================

    public function getGroups()
    {
        return craft()->assetSources->getAllSources();
    }

    public function setModel($settings)
    {
        // Set up new asset model
        $element = new AssetFileModel();
        $element->sourceId = $settings['elementGroup']['Asset'];

        if ($settings['locale']) {
            $element->locale = $settings['locale'];
        }

        return $element;
    }

    public function setCriteria($settings)
    {
        // Match with current data
        $criteria = craft()->elements->getCriteria(ElementType::Asset);
        $criteria->status = null;
        $criteria->limit = null;
        $criteria->localeEnabled = null;
        
        $criteria->groupId = $settings['elementGroup']['Asset'];

        return $criteria;
    }

    public function matchExistingElement(&$criteria, $data, $settings)
    {
        foreach ($settings['fieldUnique'] as $handle => $value) {
            if (intval($value) == 1 && ($data != '__')) {
                if (isset($data[$handle])) {
                    if (isset($data[$handle]['data'])) {
                        $criteria->$handle = DbHelper::escapeParam($data[$handle]['data']);
                    } else {
                        $criteria->$handle = DbHelper::escapeParam($data[$handle]);
                    }
                } else {
                    throw new Exception(Craft::t('Unable to match against '.$handle.' - no data found.'));
                }
            }
        }

        // Check to see if an element already exists - interestingly, find()[0] is faster than first()
        return $criteria->find();
    }

    public function delete(array $elements)
    {
        return craft()->assets->deleteFiles($elements);
    }
    
    public function prepForElementModel(BaseElementModel $element, array &$data, $settings)
    {
        if (isset($settings['locale'])) {
            $element->localeEnabled = true;
        }

        foreach ($data as $handle => $value) {
            switch ($handle) {
                case 'id';
                    $element->$handle = $value['data'];
                    break;
                case 'filename';
                    $element->$handle = $value;
                    break;
                case 'title':
                    $element->getContent()->$handle = $value['data'];
                    break;
                default:
                    continue 2;
            }

            // Update the original data in our feed - for clarity in debugging
            $data[$handle] = $element->$handle;
        }

        return $element;
    }

    public function save(BaseElementModel &$element, array $data, $settings)
    {
        // Prep some variables
        $fieldData = $data[array_keys($data)[0]];

        // Check if we're dealing with uplading assets
        if (isset($fieldData['options']['upload'])) {
            $service = craft()->feedMe->getFieldTypeService('Assets');
            $folder = craft()->assets->getRootFolderBySourceId($element->sourceId);
            $urlData = $fieldData['data'];

            $fileIds = $service->fetchRemoteImage($urlData, $folder->id, $fieldData['options']);
        } else {
            // There's no real case at the moment if we're not uploading. Why?
            // Because theu're already in Craft. Leave for the moment
        }

        return true;
    }

    public function afterSave(BaseElementModel $element, array $data, $settings)
    {

    }

}