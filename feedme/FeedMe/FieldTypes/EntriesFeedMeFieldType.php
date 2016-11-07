<?php
namespace Craft;

class EntriesFeedMeFieldType extends BaseFeedMeFieldType
{
    // Templates
    // =========================================================================

    public function getMappingTemplate()
    {
        return 'feedme/_includes/fields/entries';
    }
    


    // Public Methods
    // =========================================================================

    public function prepFieldData($element, $field, $data, $handle, $options)
    {
        $fieldData = array();

        if (empty($data)) {
            return;
        }

        $settings = $field->getFieldType()->getSettings();

        // Get source id's for connecting
        $sectionIds = array();
        $sources = $settings->sources;
        if (is_array($sources)) {
            foreach ($sources as $source) {
                // When singles is selected as the only option to search in, it doesn't contain any ids...
                if ($source == 'singles') {
                    foreach (craft()->sections->getAllSections() as $section) {
                        $sectionIds[] = ($section->type == 'single') ? $section->id : '';
                    }
                } else {
                    list($type, $id) = explode(':', $source);
                    $sectionIds[] = $id;
                }
            }
        }

        // Find existing
        $entries = ArrayHelper::stringToArray($data);

        foreach ($entries as $entry) {
            if ($entry == '__') {
                continue;
            }

            $criteria = craft()->elements->getCriteria(ElementType::Entry);
            $criteria->sectionId = $sectionIds;
            $criteria->limit = $settings->limit;

            // Check if we've specified which attribute we're trying to match against
            if (isset($options['options']['match'])) {
                $attribute = $options['options']['match'];
                $criteria->$attribute = DbHelper::escapeParam($entry);
            } else {
                $criteria->title = DbHelper::escapeParam($entry);
            }

            $elements = $criteria->ids();

            $fieldData = array_merge($fieldData, $elements);

            // Create the elements if we require
            if (count($elements) == 0) {
                if (isset($options['options']['create'])) {
                    $fieldData[] = $this->_createElement($entry, $sectionIds);
                }
            }
        }

        // Check for field limit - only return the specified amount
        if ($fieldData) {
            if ($field->settings['limit']) {
                $fieldData = array_chunk($fieldData, $field->settings['limit']);
                $fieldData = $fieldData[0];
            }
        }

        // Check if we've got any data for the fields in this element
        if (isset($options['fields'])) {
            $this->_populateElementFields($fieldData, $options['fields']);
        }

        return $fieldData;
    }



    // Private Methods
    // =========================================================================

    private function _populateElementFields($fieldData, $elementData)
    {
        foreach ($fieldData as $key => $id) {
            $entry = craft()->entries->getEntryById($id);

            // Prep each inner field
            $preppedElementData = array();
            foreach ($elementData as $elementHandle => $elementContent) {
                if ($elementContent != '__') {
                    $preppedElementData[$elementHandle] = craft()->feedMe_fields->prepForFieldType(null, $elementContent, $elementHandle, null);
                }
            }

            $entry->setContentFromPost($preppedElementData);

            if (!craft()->entries->saveEntry($entry)) {
                throw new Exception(json_encode($entry->getErrors()));
            }
        }
    }

    private function _createElement($entry, $sectionIds)
    {
        $element = new EntryModel();
        $element->getContent()->title = $entry;
        $element->sectionId = $sectionIds;

        // Save category
        if (craft()->entries->saveEntry($element)) {
            return $element->id;
        } else {
            throw new Exception(json_encode($element->getErrors()));
        }
    }
    
}