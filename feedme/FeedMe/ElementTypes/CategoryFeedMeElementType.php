<?php
namespace Craft;

use Cake\Utility\Hash as Hash;

class CategoryFeedMeElementType extends BaseFeedMeElementType
{
    // Templates
    // =========================================================================

    public function getGroupsTemplate()
    {
        return 'feedme/_includes/elements/category/groups';
    }

    public function getColumnTemplate()
    {
        return 'feedme/_includes/elements/category/column';
    }

    public function getMappingTemplate()
    {
        return 'feedme/_includes/elements/category/map';
    }


    // Public Methods
    // =========================================================================

    public function getGroups()
    {
        return craft()->categories->getEditableGroups();
    }

    public function setModel($settings)
    {
        // Set up new category model
        $element = new CategoryModel();
        $element->groupId = $settings['elementGroup']['Category'];

        if ($settings['locale']) {
            $element->locale = $settings['locale'];
        }

        return $element;
    }

    public function setCriteria($settings)
    {
        // Match with current data
        $criteria = craft()->elements->getCriteria(ElementType::Category);
        $criteria->status = null;
        $criteria->limit = null;
        $criteria->localeEnabled = null;
        
        $criteria->groupId = $settings['elementGroup']['Category'];

        return $criteria;
    }

    public function matchExistingElement(&$criteria, $data, $settings)
    {
        foreach ($settings['fieldUnique'] as $handle => $value) {
            if ((int)$value === 1) {
                $feedValue = Hash::get($data, $handle);
                $feedValue = Hash::get($data, $handle . '.data', $feedValue);

                if ($feedValue) {
                    $criteria->$handle = DbHelper::escapeParam($feedValue);
                } else {
                    FeedMePlugin::log('Category: no data for `' . $handle . '` to match an existing element on. Is data present for this in your feed?', LogLevel::Error, true);
                    return false;
                }
            }
        }

        // Check to see if an element already exists - interestingly, find()[0] is faster than first()
        $elements = $criteria->find();

        if (count($elements)) {
            return $elements[0];
        }

        return null;
    }

    public function delete(array $elements)
    {
        $success = true;

        foreach ($elements as $element) {
            if (!craft()->categories->deleteCategory($element)) {
                if ($element->getErrors()) {
                    throw new Exception(json_encode($element->getErrors()));
                } else {
                    throw new Exception(Craft::t('Something went wrong while updating elements.'));
                }

                $success = false;
            }
        }

        return $success;
    }
    
    public function prepForElementModel(BaseElementModel $element, array &$data, $settings)
    {
        foreach ($data as $handle => $value) {
            if (is_null($value)) {
                continue;
            }

            if (isset($value['data']) && $value['data'] === null) {
                continue;
            }

            if (is_array($value)) {
                $dataValue = Hash::get($value, 'data', null);
            } else {
                $dataValue = $value;
            }

            // Check for any Twig shorthand used
            $this->parseInlineTwig($data, $dataValue);

            switch ($handle) {
                case 'id';
                    $element->$handle = $dataValue;
                    break;
                case 'slug':
                    if (craft()->config->get('limitAutoSlugsToAscii')) {
                        $dataValue = StringHelper::asciiString($dataValue);
                    }
                    
                    $element->$handle = ElementHelper::createSlug($dataValue);
                    break;
                case 'title':
                    $element->getContent()->$handle = $dataValue;
                    break;
                case 'enabled':
                case 'localeEnabled':
                    $element->$handle = FeedMeHelper::parseBoolean($dataValue);
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
        // Are we targeting a specific locale here? If so, we create an essentially blank element
        // for the primary locale, and instead create a locale for the targeted locale
        if (isset($settings['locale']) && $settings['locale']) {
            // Save the default locale element empty
            if (craft()->categories->saveCategory($element)) {
                // Now get the successfully saved (empty) element, and set content on that instead
                $elementLocale = craft()->categories->getCategoryById($element->id, $settings['locale']);
                $elementLocale->setContentFromPost($data);

                // Save the locale entry
                if (craft()->categories->saveCategory($elementLocale)) {
                    return true;
                } else {
                    if ($elementLocale->getErrors()) {
                        throw new Exception(json_encode($elementLocale->getErrors()));
                    } else {
                        throw new Exception(Craft::t('Unknown Element error occurred.'));
                    }
                }
            } else {
                if ($element->getErrors()) {
                    throw new Exception(json_encode($element->getErrors()));
                } else {
                    throw new Exception(Craft::t('Unknown Element error occurred.'));
                }
            }

            return false;
        } else {
            return craft()->categories->saveCategory($element);
        }
    }

    public function afterSave(BaseElementModel $element, array $data, $settings)
    {
        $parentCategory = null;

        if (isset($data['parent'])) {
            $parentCategory = $this->_prepareParentForElement($data['parent'], $element->groupId);
        }

        if ($parentCategory) {
            $categoryGroup = craft()->categories->getGroupById($element->groupId);
            craft()->structures->append($categoryGroup->structureId, $element, $parentCategory, 'auto');
        }
    }



    // Private Methods
    // =========================================================================

    private function _prepareParentForElement($fieldData, $groupId)
    {
        $parentCategory = null;

        $data = Hash::get($fieldData, 'data');
        $attribute = Hash::get($fieldData, 'options.match', 'id');

        if (!empty($data)) {
            $criteria = craft()->elements->getCriteria(ElementType::Category);
            $criteria->groupId = $groupId;
            $criteria->$attribute = DbHelper::escapeParam($data);
            $criteria->limit = 1;
            $parentCategory = $criteria->first();
        }

        return $parentCategory;
    }
}
