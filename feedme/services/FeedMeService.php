<?php
namespace Craft;

class FeedMeService extends BaseApplicationComponent
{
	public function importNode($step, $node, $feed, $settings)
	{
        $canSaveEntry = true;

		craft()->config->maxPowerCaptain();

		// Protect from malformed data
        if (count($feed['fieldMapping']) != count($node)) {
            craft()->feedMe_logs->log($settings->logsId, Craft::t('Columns and data did not match, could be due to malformed feed.'), LogLevel::Error);
            FeedMePlugin::log(count($feed['fieldMapping']) . ' - ' . count($node), LogLevel::Error);
            FeedMePlugin::log(print_r($feed, true), LogLevel::Error);
            FeedMePlugin::log(print_r($node, true), LogLevel::Error);
            return false;
        }

        // Get our field data via what we've mapped
		$fields = array_combine($feed['fieldMapping'], $node);

        // But don't map any fields we've said not to import
        if (isset($fields['noimport'])) { unset($fields['noimport']); }

		// Prepare an EntryModel (for this section and entrytype)
		$entry = craft()->feedMe_entry->setModel($feed);



        //
        // Check for Add/Update/Delete for existing entries
        //

        // Set criteria according to elementtype
        $criteria = craft()->feedMe_entry->setCriteria($feed);

        // If we're deleting, we only do it once, before the first entry is processed.
        // Don't forget, this is deleting all entries in the section/entrytype
        if ($feed['duplicateHandle'] == 'delete') {

            // Only do this once man! You'll keep deleting entries we're adding otherwise...
            if ($step == 0) {

                // Get all elements to delete for section/entrytype
                $entries = $criteria->find();

                try {
                    // Delete
                    if (!craft()->feedMe_entry->delete($entries)) {
                        craft()->feedMe_logs->log($settings->logsId, Craft::t('Something went wrong while deleting entries.'), LogLevel::Error);
                        return false;
                    }
                } catch (\Exception $e) {
                    craft()->feedMe_logs->log($settings->logsId, Craft::t('Error: ' . $e->getMessage() . '. Check plugin log files for full error.'), LogLevel::Error);
                    return false;
                }
            }
        }

        // Set up criteria model for matching
        $cmodel = array();
        foreach ($feed['fieldMapping'] as $key => $value) {
            if (isset($feed['fieldUnique'][$key]) && intval($feed['fieldUnique'][$key]) == 1 && !empty($fields[$value])) {
                $criteria->$feed['fieldMapping'][$key] = $cmodel[$feed['fieldMapping'][$key]] = $fields[$value];
            }
        }



        // If there's an existing matching entry
        if (count($cmodel) && $criteria->count()) {

            // If we're updating
            if ($feed['duplicateHandle'] == 'update') {

                // Fill new EntryModel with match
                $entry = $criteria->first();

            // If we're adding, make sure not to overwrite existing entry
            } else if ($feed['duplicateHandle'] == 'add') {
                $canSaveEntry = false;
            }
        }



        //
        //
        //

        if ($canSaveEntry) {
            // Prepare Element model (the default stuff)
            $entry = craft()->feedMe_entry->prepForElementModel($fields, $entry);

            try {
                // Hook to prepare as appropriate fieldtypes
                array_walk($fields, function(&$data, $handle) {
                    return craft()->feedMe->prepForFieldType($data, $handle);
                });
            } catch (\Exception $e) {
                craft()->feedMe_logs->log($settings->logsId, Craft::t('Field Error: ' . $e->getMessage() . '. Check plugin log files for full error.'), LogLevel::Error);
                return false;
            }

            // Set our data for this EntryModel (our mapped data)
            $entry->setContentFromPost($fields);

            try {
                // Save the entry!
                if (!craft()->feedMe_entry->save($entry, $feed)) {
                    craft()->feedMe_logs->log($settings->logsId, $entry->getErrors(), LogLevel::Error);
                    return false;
                }
            } catch (\Exception $e) {
                craft()->feedMe_logs->log($settings->logsId, Craft::t('Save Error: ' . $e->getMessage() . '. Check plugin log files for full error.'), LogLevel::Error);
                return false;
            }
        }
	}

    // Prepare fields for fieldtypes
    public function prepForFieldType(&$data, $handle)
    {

        // Fresh up $data
        if (!is_array($data)) {
	        $data = StringHelper::convertToUTF8($data);
	        $data = trim($data);
	    }

        // Get field info
        $field = craft()->fields->getFieldByHandle($handle);

        // If it's a field ofcourse
        if (!is_null($field)) {

            // For some fieldtypes the're special rules
            switch ($field->type) {

                case 'Entries':

                    if (!empty($data)) {
                        // Get field settings
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
                        $data = array();

                        foreach ($entries as $entry) {
                            $criteria = craft()->elements->getCriteria(ElementType::Entry);
                            $criteria->sectionId = $sectionIds;
                            $criteria->limit = $settings->limit;
                            $criteria->search = 'title:'.$entry.' OR slug:'.$entry;

                            $data = array_merge($data, $criteria->ids());
                        }
                    } else {
                        $data = array();
                    }

                    break;

                case 'Categories':

                    if (!empty($data)) {
	                    // Get field settings
	                    $settings = $field->getFieldType()->getSettings();

	                    // Get category group id
	                    $source = $settings->getAttribute('source');
	                    list($type, $groupId) = explode(':', $source);

	                    $categories = ArrayHelper::stringToArray($data);
	                    $data = array();

	                    foreach ($categories as $category) {
	                        // Find existing category
	                        $criteria = craft()->elements->getCriteria(ElementType::Category);
	                        $criteria->title = $category;
	                        $criteria->groupId = $groupId;

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

	                        // Add categories to data array
	                        $data = array_merge($data, $categoryArray);
	                    }

                    } else {
                        $data = array();
                    }

                    break;

                case 'Assets':

                    if (!empty($data)) {
                        // Get field settings
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

                        // Ability to import multiple Assets at once
                        $data = array();

                        // Loop through keywords
                        foreach ($search as $query) {
                            $criteria->search = $query;

                            $data = array_merge($data, $criteria->ids());
                        }
                    } else {
                        $data = array();
                    }

                    break;

                case 'Users':

                    if (!empty($data)) {
                        // Get field settings
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
                        $data = array();

                        foreach ($users as $user) {
                            $criteria = craft()->elements->getCriteria(ElementType::User);
                            $criteria->groupId = $groupIds;
                            $criteria->limit = $settings->limit;
                            $criteria->search = 'username:'.$user.' OR email:'.$user;

                            $data = array_merge($data, $criteria->ids());
                        }
                    } else {
                        $data = array();
                    }

                    break;

                case 'Tags':

                    if (!empty($data)) {
                        // Get field settings
                        $settings = $field->getFieldType()->getSettings();

                        // Get tag group id
                        $source = $settings->getAttribute('source');
                        list($type, $groupId) = explode(':', $source);

                        $tags = ArrayHelper::stringToArray($data);
                        $data = array();

                        foreach ($tags as $tag) {
                            // Find existing tag
                            $criteria = craft()->elements->getCriteria(ElementType::Tag);
                            $criteria->title = $tag;
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

                            // Add tags to data array
                            $data = array_merge($data, $tagArray);
                        }
                    } else {
                        $data = array();
                    }

                    break;

                case 'Number':

                    // Parse as number
                    $data = LocalizationHelper::normalizeNumber($data);

                    // Parse as float
                    $data = floatval($data);

                    break;

                case 'Date':

                    // Parse date from string
                    $data = DateTimeHelper::formatTimeForDb(DateTimeHelper::fromString($data, craft()->timezone));

                    break;

                case 'RadioButtons':
                case 'Dropdown':

                    // get field settings
                    $settings = $field->getFieldType()->getSettings();

                    // get field options
                    $options = $settings->getAttribute('options');

                    // find matching option label
                    $labelSelected = false;
                    foreach ($options as $option) {
                        if ($labelSelected) {
                            continue;
                        }

                        if ($data == $option['label']) {
                            $data = $option['value'];
                            //stop looking after first match
                            $labelSelected = true;
                        }
                    }

                    break;

                case 'Checkboxes':
                case 'MultiSelect':

                    // Convert to array
                    $data = ArrayHelper::stringToArray($data);

                    break;

                // Any other FieldTypes aren't yet supported...
                case 'default':

                    $data = array();

                    break;
            }
        }

        return $data;
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
}
