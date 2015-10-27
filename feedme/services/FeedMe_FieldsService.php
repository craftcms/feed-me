<?php
namespace Craft;

class FeedMe_FieldsService extends BaseApplicationComponent
{
    public function prepForFieldType(&$data, &$handle, $field = null)
    {
        if (!is_array($data)) {
            $data = StringHelper::convertToUTF8($data);
            $data = trim($data);
        }

        if (!$field) {
            $field = craft()->fields->getFieldByHandle($handle);
        }

        // Special case for Matrix fields
        if (substr($handle, 0, 10) == '__matrix__') {
            $handle = str_replace('__matrix__', '', $handle);

            // [0]matrix - [1]blocktype - [2]field
            $matrixInfo = explode('__', $handle);

            $field = craft()->fields->getFieldByHandle($matrixInfo[0]);
        }

        // Special case for SuperTable fields
        if (substr($handle, 0, 14) == '__supertable__') {
            $handle = str_replace('__supertable__', '', $handle);

            // [0]matrix - [1]blocktype - [2]field
            $matrixInfo = explode('__', $handle);

            $field = craft()->fields->getFieldByHandle($matrixInfo[0]);
        }

        // Special case for Table fields
        if (substr($handle, 0, 9) == '__table__') {
            $handle = str_replace('__table__', '', $handle);

            // [0]table - [1]column
            $tableInfo = explode('__', $handle);

            $field = craft()->fields->getFieldByHandle($tableInfo[0]);
        }

        if (!is_null($field)) {
            switch ($field->type) {
                case FeedMe_FieldType::Assets:
                    $data = $this->prepAssets($data, $field); break;
                case FeedMe_FieldType::Categories:
                    $data = $this->prepCategories($data, $field); break;
                case FeedMe_FieldType::Checkboxes:
                    $data = $this->prepCheckboxes($data, $field); break;
                case FeedMe_FieldType::Date:
                    $data = $this->prepDate($data, $field); break;
                case FeedMe_FieldType::Dropdown:
                    $data = $this->prepDropdown($data, $field); break;
                case FeedMe_FieldType::Entries:
                    $data = $this->prepEntries($data, $field); break;
                case FeedMe_FieldType::Matrix:
                    $data = $this->prepMatrix($data, $matrixInfo, $field, $handle); break;
                case FeedMe_FieldType::MultiSelect:
                    $data = $this->prepMultiSelect($data, $field); break;
                case FeedMe_FieldType::Number:
                    $data = $this->prepNumber($data, $field); break;
                case FeedMe_FieldType::RadioButtons:
                    $data = $this->prepRadioButtons($data, $field); break;
                case FeedMe_FieldType::RichText:
                    $data = $this->prepRichText($data, $field); break;
                case FeedMe_FieldType::Table:
                    $data = $this->prepTable($data, $tableInfo, $field, $handle); break;
                case FeedMe_FieldType::Tags:
                    $data = $this->prepTags($data, $field); break;
                case FeedMe_FieldType::Users:
                    $data = $this->prepUsers($data, $field); break;

                // Color, Lightswitch, PlainText, PositionSelect all take care of themselves

                // Third-Party
                case FeedMe_FieldType::SuperTable:
                    $data = $this->prepSuperTable($data, $matrixInfo, $field, $handle); break;
            }

            // Third-party fieldtype support
            $thirdPartyFields = craft()->plugins->call('registerFeedMeField');

            if (in_array($field->type, $thirdPartyFields)) {
                craft()->plugins->call('prepForFeedMeFieldType', array(&$data, $handle));
            }
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
                $categoryArray = array();

                if (!empty($category)) {

                    // Find existing category
                    $criteria = craft()->elements->getCriteria(ElementType::Category);
                    $criteria->title = DbHelper::escapeParam($category);
                    $criteria->groupId = $groupId;
                    $criteria->limit = 1;

                    if (!$criteria->total()) {
                        // Create category if one doesn't already exist
                        $newCategory = new CategoryModel();
                        $newCategory->getContent()->title = $category;
                        $newCategory->groupId = $groupId;

                        // Save category
                        if (craft()->categories->saveCategory($newCategory)) {
                            $categoryArray = array($newCategory->id);
                        }
                    } else {
                        $categoryArray = $criteria->ids();
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
            if ($data == $option['label']) {
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

    public function prepMatrix($data, $matrixInfo, $field, &$handle) {
        $fieldData = array();

        $matrixHandle = $matrixInfo[0];
        $blocktypeHandle = $matrixInfo[1];
        $fieldHandle = $matrixInfo[2];

        // Set the original handle (index key) property to the matrix field handle
        $handle = $matrixHandle;

        if (!empty($data)) {
            $blockFieldData = $this->prepForFieldType($data, $fieldHandle);

            if (!is_array($blockFieldData)) {
                $blockFieldData = array($blockFieldData);
            }

            foreach ($blockFieldData as $i => $singleFieldData) {

                // For each field in each Matrix block, be sure to run these through the same
                // process as we would for standalone fields.
                $blockTypes = craft()->matrix->getBlockTypesByFieldId($field->id, 'handle');
                $blockType = $blockTypes[$blocktypeHandle];

                foreach ($blockType->getFields() as $f) {
                    if ($f->handle == $fieldHandle) {
                        $parsedFieldData = $this->prepForFieldType($singleFieldData, $fieldHandle, $f);
                    }
                }

                $fieldData['new'.$blocktypeHandle.($i+1)] = array(
                    'type' => $blocktypeHandle,
                    'enabled' => true,
                    'fields' => array(
                        $fieldHandle => $parsedFieldData,
                    )
                );
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
            if ($data == $option['label']) {
                $fieldData = $option['value'];
                break;
            }
        }

        return $fieldData;
    }

    public function prepSuperTable($data, $matrixInfo, $field, &$handle) {
        $fieldData = array();

        $matrixHandle = $matrixInfo[0];
        $blocktypeId = $matrixInfo[1];
        $fieldHandle = $matrixInfo[2];

        // Set the original handle (index key) property to the matrix field handle
        $handle = $matrixHandle;

        if (!empty($data)) {
            $blockFieldData = $this->prepForFieldType($data, $fieldHandle);

            foreach ($blockFieldData as $i => $singleFieldData) {

                // For each field in each Matrix block, be sure to run these through the same
                // process as we would for standalone fields.
                $blockTypes = craft()->superTable->getBlockTypesByFieldId($field->id, 'id');
                $blockType = $blockTypes[$blocktypeId];

                foreach ($blockType->getFields() as $f) {
                    if ($f->handle == $fieldHandle) {
                        $parsedFieldData = $this->prepForFieldType($singleFieldData, $fieldHandle, $f);
                    }
                }

                $fieldData['new'.$blocktypeId.($i+1)] = array(
                    'type' => $blocktypeId,
                    'enabled' => true,
                    'fields' => array(
                        $fieldHandle => $parsedFieldData,
                    )
                );
            }
        }

        return $fieldData;
    }

    public function prepTable($data, $tableInfo, $field, &$handle) {
        $fieldData = array();

        // Set the original handle (index key) property to the table field handle
        $handle = $tableInfo[0];

        $rows = ArrayHelper::stringToArray($data);

        foreach ($rows as $i => $row) {
            // Check for false for checkbox
            if ($row === 'false') {
                $row = null;
            }

            $fieldData[$i+1] = array(
                $tableInfo[1] => $row,
            );
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
                $criteria->search = 'username:'.$user.' OR email:'.$user;

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




    // Function that (almost) mimics Craft's inner slugify process.
    // But... we allow forward slashes to stay, so we can create full uri's.
    public function slugify($slug)
    {

        // Remove HTML tags
        $slug = preg_replace('/<(.*?)>/u', '', $slug);

        // Remove inner-word punctuation.
        $slug = preg_replace('/[\'"‘’“”\[\]\(\)\{\}:]/u', '', $slug);

        if (craft()->config->get('allowUppercaseInSlug') === false) {
            // Make it lowercase
            $slug = StringHelper::toLowerCase($slug, 'UTF-8');
        }

        // Get the "words".  Split on anything that is not a unicode letter or number. Periods, underscores, hyphens and forward slashes get a pass.
        preg_match_all('/[\p{L}\p{N}\.\/_-]+/u', $slug, $words);
        $words = ArrayHelper::filterEmptyStringsFromArray($words[0]);
        $slug = implode(craft()->config->get('slugWordSeparator'), $words);

        return $slug;
    }



    // Extra functions when dealing with certain field types - mostly revolves around combining fields for Table and Matrix
    public function handleMatrixData($newFields, $handle, $content)
    {
        $return = $content;

        if (isset($newFields[$handle])) {
            foreach ($newFields[$handle] as $matrixBlockKey => $matrixBlock) {
                if (isset($content[$matrixBlockKey])) {
                    // Merge just the fields property
                    $merged = array_merge($content[$matrixBlockKey]['fields'], $matrixBlock['fields']);
                    $return[$matrixBlockKey]['fields'] = $merged;
                } else {
                    $return = array_merge($newFields[$handle], $content);
                }
            }
        }
        
        return $return;
    }

    public function handleTableData($newFields, $handle, $content)
    {
        $return = $content;

        if (isset($newFields[$handle])) {
            foreach ($newFields[$handle] as $rowId => $row) {
                if (isset($content[$rowId])) {
                    // Merge just the fields property
                    $merged = array_merge($content[$rowId], $row);
                    $return[$rowId] = array_merge($content[$rowId], $row);
                } else {
                    $return = array_merge($newFields[$handle], $content);
                }
            }
        }

        return $return;
    }

    public function handleSuperTableData($newFields, $handle, $content)
    {
        $return = $content;

        if (isset($newFields[$handle])) {
            foreach ($newFields[$handle] as $matrixBlockKey => $matrixBlock) {
                if (isset($content[$matrixBlockKey])) {
                    // Merge just the fields property
                    $merged = array_merge($content[$matrixBlockKey]['fields'], $matrixBlock['fields']);
                    $return[$matrixBlockKey]['fields'] = $merged;
                } else {
                    $return = array_merge($newFields[$handle], $content);
                }
            }
        }
        
        return $return;
    }

}
