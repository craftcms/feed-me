<?php
namespace Craft;

class FeedMeService extends BaseApplicationComponent
{
    // Public Methods
    // =========================================================================

    public function setupForImport($feed)
    {
        $return = array(
            'fields' => array(),
            'processedEntries' => array(),
            'existingEntries' => array(),
        );

        // Start looping through all the mapped fields - checking for nested nodes
        foreach ($feed['fieldMapping'] as $itemNode => $destination) {

            // Forget about any fields mapped as not to import
            if ($destination != 'noimport') {
                $return['fields'][$itemNode] = $destination;
            }
        }

        //
        // If our duplication handling is to delete - we delete all entries in this section/entrytype
        //
        if ($feed['duplicateHandle'] == FeedMe_Duplicate::Delete) {
            $criteria = craft()->feedMe_entry->setCriteria($feed);
            $return['existingEntries'] = $criteria->ids();
        }

        //
        // Return variables that need to be used per-node, but only need to be processed once.
        //
        return $return;
    }

    public function importNode($nodes, $feed, $settings)
    {
        $processedEntries = array();
        $hasAnyErrors = false;

        $time_start = microtime(true); 
        FeedMePlugin::log($feed->name . ': Processing started', LogLevel::Info, true);

        foreach ($nodes as $key => $node) {
            $result = $this->importSingleNode($node, $feed, $settings);

            if (isset($result['entryId'])) {
                $processedEntries[] = $result['entryId'];
            }

            // Report back if even one feed node failed
            if (!$result['result']) {
                $hasAnyErrors = true;
            }
        }

        $time_end = microtime(true);
        $execution_time = number_format(($time_end - $time_start), 2);
        FeedMePlugin::log($feed->name . ': Processing finished in ' . $execution_time . 's', LogLevel::Info, true);

        return array('result' => !$hasAnyErrors, 'processedEntries' => $processedEntries);
    }

    public function importSingleNode($node, $feed, $settings)
    {
        $canSaveEntry = true;
        $existingEntry = false;
        $fieldData = array();
        $entry = array();

        $fields = $settings['fields'];



        //
        // Lets get started!
        //

        $criteria = craft()->feedMe_entry->setCriteria($feed);

        
        // Start looping through all the mapped fields - grab their data from the feed node
        foreach ($fields as $itemNode => $handle) {

            // Fetch the value for the field from the feed node. Deep-search.
            $data = craft()->feedMe_feed->getValueForNode($itemNode, $node);

            // While we're in the loop, lets check for unique data to match existing entries on.
            if (isset($feed['fieldUnique'][$itemNode]) && intval($feed['fieldUnique'][$itemNode]) == 1 && !empty($data)) {
                $criteria->$handle = DbHelper::escapeParam($data);
            }

            //
            // Each field needs special processing, sort that out here
            //

            try {
                // Grab the field's content - formatted specifically for it
                $content = craft()->feedMe_fields->prepForFieldType($data, $handle);

                // The first key of $content will always be the field handle - grab that to create our field data.
                $contentKeys = array_keys($content);
                $fieldHandle = $contentKeys[0];

                // Then, we check if we've already got any partial content for the field. Most commongly, this is
                // the case for Matrix and Table fields, but also likely other Third-Party fields. So its important to
                // combine values, rather than overwriting or omitting as each feed node contains just part of the data.
                if (array_key_exists($fieldHandle, $fieldData) && is_array($fieldData[$fieldHandle])) {
                    $fieldData[$fieldHandle] = array_replace_recursive($fieldData[$fieldHandle], $content[$fieldHandle]);
                } else {
                    $fieldData[$fieldHandle] = $content[$fieldHandle];
                }

            } catch (\Exception $e) {
                FeedMePlugin::log($feed->name . ': FeedMeError: ' . $e->getMessage() . '.', LogLevel::Error, true);

                return array('result' => false);
            }
        }

        $existingEntry = $criteria->first();


        //
        // Check for Add/Update/Delete for existing entries
        //


        // If there's an existing matching entry
        if ($existingEntry && $feed['duplicateHandle']) {

            // If we're deleting
            if ($feed['duplicateHandle'] == FeedMe_Duplicate::Delete) {

                // Fill new EntryModel with match
                $entry = $existingEntry;
            }

            // If we're updating
            if ($feed['duplicateHandle'] == FeedMe_Duplicate::Update) {

                // Fill new EntryModel with match
                $entry = $existingEntry;
            }

            // If we're adding, make sure not to overwrite existing entry
            if ($feed['duplicateHandle'] == FeedMe_Duplicate::Add) {
                $canSaveEntry = false;
            }
        } else {
            // Prepare a new EntryModel (for this section and entrytype)
            $entry = craft()->feedMe_entry->setModel($feed);
        }



        //
        //
        //

        if ($canSaveEntry && $entry) {

            // Any post-processing on our nice collection of entry-ready data.
            craft()->feedMe_fields->postForFieldType($fieldData, $entry);

            // Prepare Element model (the default stuff)
            $entry = craft()->feedMe_entry->prepForElementModel($fieldData, $entry);

            // Set our data for this EntryModel (our mapped data)
            if (!$feed['locale']) {
                $entry->setContentFromPost($fieldData);
            }

            //echo '<pre>';
            //print_r($fieldData);
            //echo '</pre>';

            // Set enabled based on feed settings
            $entry->enabled = (bool)$feed->status;

            try {
                // Save the entry!
                if (!craft()->entries->saveEntry($entry)) {
                    FeedMePlugin::log($feed->name . ': ' . json_encode($entry->getErrors()), LogLevel::Error, true);

                    return array('result' => false);
                } else {

                    // If we're importing into a specific locale, we need to create this entry if it doesn't already exist
                    // completely blank of custom field content. After thats saved, we then re-fetch the entry for the specific
                    // locale and then add our field data. Doing this ensures its not copied across all locales.
                    if ($feed['locale']) {
                        $entryLocale = craft()->entries->getEntryById($entry->id, $feed['locale']);

                        $entryLocale->setContentFromPost($fieldData);

                        if (!craft()->entries->saveEntry($entryLocale)) {
                            FeedMePlugin::log($feed->name . ': ' . json_encode($entryLocale->getErrors()), LogLevel::Error, true);

                            return array('result' => false);
                        } else {

                            // Successfully saved/added entry
                            if ($feed['duplicateHandle'] == FeedMe_Duplicate::Update) {
                                FeedMePlugin::log($feed->name . ': Entry successfully updated: ' . $entryLocale->id, LogLevel::Info, true);
                            } else {
                                FeedMePlugin::log($feed->name . ': Entry successfully added: ' . $entryLocale->id, LogLevel::Info, true);
                            }

                            return array('result' => true, 'entryId' => $entryLocale->id);
                        }
                    } else {

                        // Successfully saved/added entry
                        if ($feed['duplicateHandle'] == FeedMe_Duplicate::Update) {
                            FeedMePlugin::log($feed->name . ': Entry successfully updated: ' . $entry->id, LogLevel::Info, true);
                        } else {
                            FeedMePlugin::log($feed->name . ': Entry successfully added: ' . $entry->id, LogLevel::Info, true);
                        }

                        return array('result' => true, 'entryId' => $entry->id);
                    }
                }
            } catch (\Exception $e) {
                FeedMePlugin::log($feed->name . ': Entry FeedMeError: ' . $e->getMessage() . '.', LogLevel::Error, true);

                return array('result' => false, 'entryId' => $entry->id);
            }
        } else {
            if ($existingEntry) {
                FeedMePlugin::log($feed->name . ': Entry skipped: ' . $existingEntry->id . '.', LogLevel::Error, true);
            }

            return array('result' => true);
        }
    }

    public function deleteLeftoverEntries($settings, $feed, $processedEntries, $result)
    {
        if ($feed['duplicateHandle'] == FeedMe_Duplicate::Delete && $result['result']) {
            $deleteIds = array_diff($settings['existingEntries'], $processedEntries);

            $criteria = craft()->feedMe_entry->setCriteria($feed);
            $criteria->id = $deleteIds;
            $entriesToDelete = $criteria->find();

            try {
                if ($entriesToDelete) {
                    if (!craft()->entries->deleteEntry($entriesToDelete)) {
                        FeedMePlugin::log('FeedMeError - Something went wrong while deleting entries.', LogLevel::Error, true);
                    } else {
                        FeedMePlugin::log($feed->name . ': The following entries have been deleted: ' . print_r($deleteIds, true) . '.', LogLevel::Error, true);
                    }
                }
            } catch (\Exception $e) {
                FeedMePlugin::log($feed->name . ': FeedMeError: ' . $e->getMessage() . '.', LogLevel::Error, true);
            }
        }
    }
}
