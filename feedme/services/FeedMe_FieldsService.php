<?php
namespace Craft;

class FeedMe_FieldsService extends BaseApplicationComponent
{
    // Properties
    // =========================================================================

    private $_entryFields = array();

    // Public Methods
    // =========================================================================

    public function prepForFieldType(&$data, $handle, $field = null)
    {
        if (!is_array($data)) {
            $data = StringHelper::convertToUTF8($data);
            $data = trim($data);
        }

        if (!$field) {
            // Check for sub-fields, we only want to grab the top-level handle (for now)
            preg_match('/(\w+)/', $handle, $matches);
            $fieldHandle = $matches[0];

            if (isset($this->_entryFields[$fieldHandle])) {
                $field = $this->_entryFields[$fieldHandle];
            } else {
                $field = craft()->fields->getFieldByHandle($fieldHandle);

                $this->_entryFields[$fieldHandle] = $field;
            }
        } else {
            $fieldHandle = $handle;
        }

        if (!is_null($field)) {
            switch ($field->type) {
                case FeedMe_FieldType::Assets:
                    $data = array( $fieldHandle => $this->prepAssets($data, $field) ); break;
                case FeedMe_FieldType::Categories:
                    $data = array( $fieldHandle => $this->prepCategories($data, $field) ); break;
                case FeedMe_FieldType::Checkboxes:
                    $data = array( $fieldHandle => $this->prepCheckboxes($data, $field) ); break;
                case FeedMe_FieldType::Date:
                    $data = array( $fieldHandle => $this->prepDate($data, $field) ); break;
                case FeedMe_FieldType::Dropdown:
                    $data = array( $fieldHandle => $this->prepDropdown($data, $field) ); break;
                case FeedMe_FieldType::Entries:
                    $data = array( $fieldHandle => $this->prepEntries($data, $field) ); break;
                case FeedMe_FieldType::Matrix:
                    $data = array( $fieldHandle => $this->prepMatrix($data, $handle, $field) ); break;
                case FeedMe_FieldType::MultiSelect:
                    $data = array( $fieldHandle => $this->prepMultiSelect($data, $field) ); break;
                case FeedMe_FieldType::Number:
                    $data = array( $fieldHandle => $this->prepNumber($data, $field) ); break;
                case FeedMe_FieldType::RadioButtons:
                    $data = array( $fieldHandle => $this->prepRadioButtons($data, $field) ); break;
                case FeedMe_FieldType::RichText:
                    $data = array( $fieldHandle => $this->prepRichText($data, $field) ); break;
                case FeedMe_FieldType::Table:
                    $data = array( $fieldHandle => $this->prepTable($data, $handle, $field) ); break;
                case FeedMe_FieldType::Tags:
                    $data = array( $fieldHandle => $this->prepTags($data, $field) ); break;
                case FeedMe_FieldType::Users:
                    $data = array( $fieldHandle => $this->prepUsers($data, $field) ); break;

                // Color, Lightswitch, PlainText, PositionSelect all take care of themselves
                default:
                    $data = array( $fieldHandle => $data );
            }

            // Third-party fieldtype support
            craft()->plugins->call('prepForFeedMeFieldType', array($field, &$data, $handle));
        } else {
            // For core entry fields - still need to return with handle
            $data = array( $fieldHandle => $data );
        }

        return $data;
    }

    public function prepAssets($data, $field) {
        $fieldData = array();

        if (!empty($data)) {
            $settings = $field->getFieldType()->getSettings();

            // Get source id's for connecting
            $sourceIds = array();
            $sources = $settings->sources;
            if (is_array($sources)) {
                foreach ($sources as $source) {
                    list($type, $id) = explode(':', $source);
                    $sourceIds[] = $id;
                }
            }

            // Find matching element in sources
            $criteria = craft()->elements->getCriteria(ElementType::Asset);
            $criteria->sourceId = $sourceIds;
            $criteria->limit = $settings->limit;

            // Get search strings
            $search = ArrayHelper::stringToArray($data);

            // Loop through keywords
            foreach ($search as $query) {
                $criteria->search = $query;

                $fieldData = array_merge($fieldData, $criteria->ids());
            }
        }

        // Check for field limit - only return the specified amount
        if ($fieldData) {
            if ($field->settings['limit']) {
                $fieldData = array_chunk($fieldData, $field->settings['limit']);
                $fieldData = $fieldData[0];
            }
        }

        return $fieldData;
    }

    public function prepCategories($data, $field) {
        $fieldData = array();

        if (!empty($data)) {
            $settings = $field->getFieldType()->getSettings();

            // Get category group id
            $source = $settings->getAttribute('source');
            list($type, $groupId) = explode(':', $source);

            $categories = ArrayHelper::stringToArray($data);

            foreach ($categories as $category) {

                // Skip empty
                if (empty($category)) {
                    continue;
                }

                $categoryArray = array();
                $category =  DbHelper::escapeParam($category);

                // Find existing category by title or slug
                $criteria = craft()->elements->getCriteria(ElementType::Category);
                $criteria->groupId = $groupId;
                $criteria->limit = 1;

                $query = craft()->elements->buildElementsQuery($criteria);
                $query->select('elements.id');

                $conditions = array(
                    'or',
                    array('in', 'title', $category),
                    array('in', 'slug', $category)
                );

                $query->andWhere($conditions);

                $results = $query->queryAll();

                if ( !empty($results) ) {

                    foreach ($results as $result) {
                        $categoryArray = [$result['id']];
                    }

                } else {

                    // Create category if one doesn't already exist
                    $newCategory = new CategoryModel();
                    $newCategory->getContent()->title = $category;
                    $newCategory->groupId = $groupId;

                    // Save category
                    if (craft()->categories->saveCategory($newCategory)) {
                        $categoryArray = [$newCategory->id];
                    }
                }

                // Add categories to data array
                $fieldData = array_merge($fieldData, $categoryArray);
            }
        }

        // Check for field limit - only return the specified amount
        if ($fieldData) {
            if ($field->settings['limit']) {
                $fieldData = array_chunk($fieldData, $field->settings['limit']);
                $fieldData = $fieldData[0];
            }
        }

        return $fieldData;
    }

    public function prepCheckboxes($data, $field) {
        return ArrayHelper::stringToArray($data);
    }

    public function prepDate($data, $field) {
        return DateTimeHelper::formatTimeForDb(DateTimeHelper::fromString($data, craft()->timezone));
    }

    public function prepDropdown($data, $field) {
        $fieldData = null;

        $settings = $field->getFieldType()->getSettings();
        $options = $settings->getAttribute('options');

        // find matching option label
        foreach ($options as $option) {
            if ($data == $option['value']) {
                $fieldData = $option['value'];
                break;
            }
        }

        return $fieldData;
    }

    public function prepEntries($data, $field) {
        $fieldData = array();

        if (!empty($data)) {
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

            $entries = ArrayHelper::stringToArray($data);

            foreach ($entries as $entry) {
                $criteria = craft()->elements->getCriteria(ElementType::Entry);
                $criteria->sectionId = $sectionIds;
                $criteria->limit = $settings->limit;
                $criteria->search = 'title:'.$entry.' OR slug:'.$entry;

                $fieldData = array_merge($fieldData, $criteria->ids());
            }
        }

        // Check for field limit - only return the specified amount
        if ($fieldData) {
            if ($field->settings['limit']) {
                $fieldData = array_chunk($fieldData, $field->settings['limit']);
                $fieldData = $fieldData[0];
            }
        }

        return $fieldData;
    }

    public function prepMatrix($data, $handle, $field) {
        $fieldData = array();

        preg_match_all('/\w+/', $handle, $matches);

        if (isset($matches[0])) {
            $fieldHandle = $matches[0][0];
            $blocktypeHandle = $matches[0][1];
            $subFieldHandle = $matches[0][2];

            // Store the fields for this Matrix - can't use the fields service due to context
            $blockTypes = craft()->matrix->getBlockTypesByFieldId($field->id, 'handle');
            $blockType = $blockTypes[$blocktypeHandle];

            foreach ($blockType->getFields() as $f) {
                if ($f->handle == $subFieldHandle) {
                    $subField = $f;
                }
            }

            $rows = array();

            if (!empty($data)) {
                if (!is_array($data)) {
                    $data = array($data);
                }

                // Additional check for Table or other 'special' nested fields
                if (count($matches[0]) > 3) {
                    $filteredSubFieldHandle = $matches[0][2] . '[' . $matches[0][3] . ']';
                } else {
                    $filteredSubFieldHandle = $subFieldHandle;
                }

                foreach ($data as $i => $singleFieldData) {
                    $subFieldData = $this->prepForFieldType($singleFieldData, $filteredSubFieldHandle, $subField);

                    if (count($matches[0]) > 3) {
                        $subFieldData = array($subFieldHandle => $subFieldData[$filteredSubFieldHandle]);
                    }

                    $fieldData['new'.$blocktypeHandle.($i+1)] = array(
                        'type' => $blocktypeHandle,
                        'order' => $i,
                        'enabled' => true,
                        'fields' => $subFieldData,
                    );
                }
            }
        }

        return $fieldData;
    }

    public function prepMultiSelect($data, $field) {
        return ArrayHelper::stringToArray($data);
    }

    public function prepNumber($data, $field) {
        return floatval(LocalizationHelper::normalizeNumber($data));
    }

    public function prepRichText($data, $field) {
        if (is_array($data)) {
            return implode($data);
        } else {
            return $data;
        }
    }

    public function prepRadioButtons($data, $field) {
        $fieldData = null;

        $settings = $field->getFieldType()->getSettings();
        $options = $settings->getAttribute('options');

        // find matching option label
        foreach ($options as $option) {
            if ($data == $option['value']) {
                $fieldData = $option['value'];
                break;
            }
        }

        return $fieldData;
    }

    public function prepTable($data, $handle, $field) {
        $fieldData = array();

        // Get the table columns - sent through as fieldname[col]
        preg_match_all('/\w+/', $handle, $matches);

        if (isset($matches[0])) {
            $fieldHandle = $matches[0][0];
            $columnHandle = $matches[0][1];

            $rows = ArrayHelper::stringToArray($data);

            foreach ($rows as $i => $row) {
                if (is_array($row)) {
                    foreach ($row as $j => $r) {
                        // Check for false for checkbox
                        if ($r === 'false') {
                            $r = null;
                        }

                        $fieldData[$i+1] = array(
                            'col'.$columnHandle => $r,
                        );
                    }
                } else {
                    // Check for false for checkbox
                    if ($row === 'false') {
                        $row = null;
                    }

                    $fieldData[$i+1] = array(
                        'col'.$columnHandle => $row,
                    );
                }
            }
        }

        return $fieldData;
    }

    public function prepTags($data, $field) {
        $fieldData = array();

        if (!empty($data)) {
            $settings = $field->getFieldType()->getSettings();

            // Get tag group id
            $source = $settings->getAttribute('source');
            list($type, $groupId) = explode(':', $source);

            $tags = ArrayHelper::stringToArray($data);

            foreach ($tags as $tag) {
                $tagArray = array();

                if (!empty($tag)) {

                    // Find existing tag
                    $criteria = craft()->elements->getCriteria(ElementType::Tag);
                    $criteria->title = DbHelper::escapeParam($tag);
                    $criteria->limit = 1;
                    $criteria->groupId = $groupId;

                    if (!$criteria->total()) {
                        // Create tag if one doesn't already exist
                        $newtag = new TagModel();
                        $newtag->getContent()->title = $tag;
                        $newtag->groupId = $groupId;

                        // Save tag
                        if (craft()->tags->saveTag($newtag)) {
                            $tagArray = array($newtag->id);
                        }
                    } else {
                        $tagArray = $criteria->ids();
                    }
                }

                // Add tags to data array
                $fieldData = array_merge($fieldData, $tagArray);
            }
        }

        return $fieldData;
    }

    public function prepUsers($data, $field) {
        $fieldData = array();

        if (!empty($data)) {
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
                $criteria = craft()->elements->getCriteria(ElementType::User);
                $criteria->groupId = $groupIds;
                $criteria->limit = $settings->limit;
                $criteria->search = $user;

                $fieldData = array_merge($fieldData, $criteria->ids());
            }
        }

        // Check for field limit - only return the specified amount
        if ($fieldData) {
            if ($field->settings['limit']) {
                $fieldData = array_chunk($fieldData, $field->settings['limit']);
                $fieldData = $fieldData[0];
            }
        }

        return $fieldData;
    }


    // Function for third-party plugins to provide custom mapping options for fieldtypes
    public function getCustomOption($fieldHandle)
    {
        $options = craft()->plugins->call('registerFeedMeMappingOptions');

        foreach ($options as $pluginHandle => $option) {
            if (isset($option[$fieldHandle])) {
                return $option[$fieldHandle];
            }
        }

        return false;
    }


    // Some post-processing needs to be done, specifically for a Matrix field. Unfortuntely, multiple
    // blocks are added out of order, which is messy - fix this here. Fortuntely, we have a 'order' attribute
    // on each block. Also call any third-party post processing (looking at you Super Table).
    public function postForFieldType(&$fieldData, $element)
    {
        // This is less intensive than craft()->fields->getFieldByHandle($fieldHandle);
        /*foreach ($fieldData as $fieldHandle => $data) {
            if (is_array($data)) {

                // Check for the order attr, otherwise not what we're after
                if (isset(array_values($data)[0]['order'])) {
                    $orderedMatrixData = array();
                    $tempMatrixData = array();

                    foreach ($data as $key => $subField) {
                        $tempMatrixData[$subField['order']][$key] = $subField;
                    }

                    $fieldData[$fieldHandle] = array();

                    foreach ($tempMatrixData as $key => $subField) {
                        $fieldData[$fieldHandle] = array_merge($fieldData[$fieldHandle], $subField);
                    }
                }
            }
        }*/

        // Third-party fieldtype support
        craft()->plugins->call('postForFeedMeFieldType', array(&$fieldData, $element));
    }

}
