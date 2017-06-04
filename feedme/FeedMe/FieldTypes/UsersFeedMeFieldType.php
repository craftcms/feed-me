<?php
namespace Craft;

use Cake\Utility\Hash as Hash;

class UsersFeedMeFieldType extends BaseFeedMeFieldType
{
    // Templates
    // =========================================================================

    public function getMappingTemplate()
    {
        return 'feedme/_includes/fields/users';
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
        $groupIds = array();
        $sources = $settings->sources;
        if (is_array($sources)) {
            foreach ($sources as $source) {
                list($type, $id) = explode(':', $source);
                $groupIds[] = $id;
            }
        }

        // Find existing
        foreach ($data as $user) {
            $criteria = craft()->elements->getCriteria(ElementType::User);
            $criteria->status = null;
            $criteria->groupId = $groupIds;
            $criteria->limit = $settings->limit;

            // Check if we've specified which attribute we're trying to match against
            $attribute = Hash::get($fieldData, 'options.match', 'email');
            $criteria->$attribute = DbHelper::escapeParam($user);
            $elements = $criteria->ids();

            $preppedData = array_merge($preppedData, $elements);

            // Create the elements if we require
            if (count($elements) == 0) {
                if (isset($fieldData['options']['create'])) {
                    $preppedData[] = $this->_createElement($user, $groupIds);
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

    private function _populateElementFields($userData, $fieldData)
    {
        foreach ($userData as $i => $userId) {
            $user = craft()->users->getUserById($userId);

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

                    craft()->feedMe_fields->checkExistingFieldData($user, $preppedData, $fieldHandle, $field);
                }
            }

            if ($preppedData) {
                $user->setContentFromPost($preppedData);

                if (!craft()->users->saveUser($user)) {
                    FeedMePlugin::log('User error: ' . json_encode($user->getErrors()), LogLevel::Error, true);
                } else {
                    FeedMePlugin::log('Updated User (ID ' . $userId . ') inner-element with content: ' . json_encode($preppedData), LogLevel::Info, true);
                }
            }
        }
    }

    private function _createElement($user, $groupIds)
    {
        $element = new UserModel();
        $element->email = $user;
        $element->groupId = $groupIds;

        // Save category
        if (craft()->users->saveUser($element)) {
            return $element->id;
        } else {
            throw new Exception(json_encode($element->getErrors()));
        }
    }
    
}