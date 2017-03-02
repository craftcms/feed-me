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

    public function getMappingTemplate()
    {
        return 'feedme/_includes/elements/asset/map';
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
        
        $criteria->sourceId = $settings['elementGroup']['Asset'];

        return $criteria;
    }

    public function matchExistingElement(&$criteria, $data, $settings)
    {
        foreach ($settings['fieldUnique'] as $handle => $value) {
            if ((int)$value === 1) {
                $feedValue = Hash::get($data, $handle . '.data', $data[$handle]);

                if ($feedValue) {
                    $criteria->$handle = DbHelper::escapeParam($feedValue);
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
        $fieldData = Hash::get($data, 'filename', array());

        // Are we uploading a new asset? If so, the element gets created so return that
        if (isset($fieldData['options']['upload'])) {
            $service = craft()->feedMe->getFieldTypeService('Assets');
            $folder = craft()->assets->getRootFolderBySourceId($element->sourceId);
            $urlData = $fieldData['data'];

            // Check config settings if we need to clean url
            if (craft()->config->get('cleanAssetUrls', 'feedMe')) {
                $urlData = UrlHelper::stripQueryString($urlData);
            }

            // Cleanup filenames to match Craft Assets
            $urlData = str_replace(',', '\,', $urlData);

            $fileId = $service->fetchRemoteImage($urlData, $folder->id, $fieldData['options']);

            $element = craft()->assets->getFileById($fileId);
        } else {
            foreach ($data as $handle => $value) {
                switch ($handle) {
                    case 'id';
                        $element->$handle = $value['data'];
                        break;
                    case 'filename';
                        $element->$handle = $value['data'];
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
        }

        return $element;
    }

    public function save(BaseElementModel &$element, array $data, $settings)
    {
        // Are we targeting a specific locale here? If so, we create an essentially blank element
        // for the primary locale, and instead create a locale for the targeted locale
        if (isset($settings['locale'])) {
            // Save the default locale element empty
            if (craft()->assets->storeFile($element)) {
                // Now get the successfully saved (empty) element, and set content on that instead
                $elementLocale = craft()->assets->getFileById($element->id, $settings['locale']);
                $elementLocale->setContentFromPost($data);

                // Save the locale entry
                return craft()->assets->storeFile($elementLocale);
            } else {
                if ($element->getErrors()) {
                    throw new Exception(json_encode($element->getErrors()));
                } else {
                    throw new Exception(Craft::t('Unknown Element error occurred.'));
                }
            }

            return false;
        } else {
            return craft()->assets->storeFile($element);
        }
    }

    public function afterSave(BaseElementModel $element, array $data, $settings)
    {

    }

}