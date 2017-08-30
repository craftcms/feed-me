<?php
namespace Craft;

use Cake\Utility\Hash as Hash;

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

    public function prepFieldData($element, $field, $fieldData, $handle, $options)
    {
        $preppedData = array();

        $data = Hash::get($fieldData, 'data');

        if (empty($data)) {
            return array();
        }

        if (!is_array($data)) {
            $data = array($data);
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
        } else if ($sources === '*') {
            $sectionIds = '*';
        }

        // Find existing
        foreach ($data as $entry) {
            $criteria = craft()->elements->getCriteria(ElementType::Entry);
            $criteria->status = null;
            $criteria->sectionId = $sectionIds;
            $criteria->limit = $settings->limit;

            // Check if we've specified which attribute we're trying to match against
            $attribute = Hash::get($fieldData, 'options.match', 'title');
            $criteria->$attribute = DbHelper::escapeParam($entry);
            $elements = $criteria->ids();

            $preppedData = array_merge($preppedData, $elements);

            // Create the elements if we require
            if (count($elements) == 0) {
                if (isset($fieldData['options']['create'])) {
                    $preppedData[] = $this->_createElement($entry, $sectionIds, $attribute);
                }
            }
        }

        // Check for field limit - only return the specified amount
        if ($preppedData) {
            if ($field->settings['limit']) {
                $preppedData = array_chunk($preppedData, $field->settings['limit']);
                $preppedData = $preppedData[0];
            }
        }

        // Check if we've got any data for the fields in this element
        if (isset($fieldData['fields'])) {
            $this->_populateElementFields($preppedData, $fieldData['fields']);
        }

        return $preppedData;
    }



    // Private Methods
    // =========================================================================

    private function _populateElementFields($entryData, $fieldData)
    {
        foreach ($entryData as $i => $entryId) {
            $entry = craft()->entries->getEntryById($entryId);

            // Prep each inner field
            $preppedData = array();
            foreach ($fieldData as $fieldHandle => $fieldContent) {
                $data = craft()->feedMe_fields->prepForFieldType(null, $fieldContent, $fieldHandle, null);

                if (is_array($data)) {
                    $data = Hash::get($data, $i);
                }

                $preppedData[$fieldHandle] = $data;

                if (craft()->config->get('checkExistingFieldData', 'feedMe')) {
                    $field = craft()->fields->getFieldByHandle($fieldHandle);

                    craft()->feedMe_fields->checkExistingFieldData($entry, $preppedData, $fieldHandle, $field);
                }
            }

            if ($preppedData) {
                $entry->setContentFromPost($preppedData);

                if (!craft()->entries->saveEntry($entry)) {
                    FeedMePlugin::log('Entry error: ' . json_encode($entry->getErrors()), LogLevel::Error, true);
                } else {
                    FeedMePlugin::log('Updated Entry (ID ' . $entryId . ') inner-element with content: ' . json_encode($preppedData), LogLevel::Info, true);
                }
            }
        }
    }

    private function _createElement($entry, $sectionIds, $attribute)
    {
        $fieldSections = array_values(Hash::filter($sectionIds));
        $firstSectionId = $fieldSections[0];

        $element = new EntryModel();

        if ($attribute == 'title') {
            $element->getContent()->title = $entry;
        } else {
            $element->$attribute = DbHelper::escapeParam($entry);
        }

        $element->sectionId = $firstSectionId;

        // Save category
        if (craft()->entries->saveEntry($element)) {
            return $element->id;
        } else {
            throw new Exception(json_encode($element->getErrors()));
        }
    }
    
}