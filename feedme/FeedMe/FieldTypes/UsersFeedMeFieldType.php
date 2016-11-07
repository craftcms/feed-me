<?php
namespace Craft;

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

    public function prepFieldData($element, $field, $data, $handle, $options)
    {
        $fieldData = array();

        if (empty($data)) {
            return;
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

        $users = ArrayHelper::stringToArray($data);

        foreach ($users as $user) {
            if ($user == '__') {
                continue;
            }

            $criteria = craft()->elements->getCriteria(ElementType::User);
            $criteria->groupId = $groupIds;
            $criteria->limit = $settings->limit;

            // Check if we've specified which attribute we're trying to match against
            if (isset($options['options']['match'])) {
                $attribute = $options['options']['match'];
                $criteria->$attribute = DbHelper::escapeParam($user);
            } else {
                $criteria->email = DbHelper::escapeParam($user);
            }

            $elements = $criteria->ids();

            $fieldData = array_merge($fieldData, $elements);

            // Create the elements if we require
            if (count($elements) == 0) {
                if (isset($options['options']['create'])) {
                    $fieldData[] = $this->_createElement($user, $groupIds);
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
            $user = craft()->users->getUserById($id);

            // Prep each inner field
            $preppedElementData = array();
            foreach ($elementData as $elementHandle => $elementContent) {
                if ($elementContent != '__') {
                    $preppedElementData[$elementHandle] = craft()->feedMe_fields->prepForFieldType(null, $elementContent, $elementHandle, null);
                }
            }

            $user->setContentFromPost($preppedElementData);

            if (!craft()->users->saveUser($user)) {
                throw new Exception(json_encode($user->getErrors()));
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