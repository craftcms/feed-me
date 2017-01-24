<?php
namespace Craft;

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
                    $criteria->$handle = DbHelper::escapeParam($data[$handle]);
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
                case 'filename';
                    $element->$handle = $value;
                    break;
                case 'title':
                    $element->getContent()->$handle = $value;
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
        return true;
    }

    public function afterSave(BaseElementModel $element, array $data, $settings)
    {

    }

}