<?php
namespace Craft;

class FeedMeService extends BaseApplicationComponent
{
    public function setupForImport($feed)
    {
        // Return a collection of only the fields we're mapping
        $fields = array();

        // Start looping through all the mapped fields - checking for nested nodes
        foreach ($feed['fieldMapping'] as $itemNode => $destination) {

            // Forget about any fields mapped as not to import
            if ($destination != 'noimport') {
                $fields[$itemNode] = $destination;
            }
        }

        //
        // If our duplication handling is to delete - we delete all entries in this section/entrytype
        //

        if ($feed['duplicateHandle'] == FeedMe_Duplicate::Delete) {
            try {

                $criteria = craft()->feedMe_entry->setCriteria($feed);
                $entries = $criteria->find();

                if (!craft()->entries->deleteEntry($entries)) {
                    FeedMePlugin::log('FeedMeError - Something went wrong while deleting entries.', LogLevel::Error, true);
                }
            } catch (\Exception $e) {
                FeedMePlugin::log($feed->name . ': FeedMeError: ' . $e->getMessage() . '.', LogLevel::Error, true);
            }
        }

        //
        // Return variables that need to be used per-node, but only need to be processed once.
        //
        return array(
            'fields' => $fields,
        );
    }

    public function importNode($nodes, $feed, $settings)
    {
        $time_start = microtime(true); 
        FeedMePlugin::log($feed->name . ': Processing started', LogLevel::Info, true);

        foreach ($nodes as $key => $node) {
            $this->importSingleNode($node, $feed, $settings);

            //echo number_format(memory_get_usage()) . "<br>";
        }

        $time_end = microtime(true);
        $execution_time = number_format(($time_end - $time_start), 2);
        FeedMePlugin::log($feed->name . ': Processing finished in ' . $execution_time . 's', LogLevel::Info, true);

        return true;
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
        foreach ($fields as $itemNode => &$destination) {

            // Fetch the value for the field from the feed node. Deep-search.
            $data = craft()->feedMe_feed->getValueForNode($itemNode, $node);

            // While we're in the loop, lets check for unique data to match existing entries on.
            if (isset($feed['fieldUnique'][$itemNode]) && intval($feed['fieldUnique'][$itemNode]) == 1 && !empty($data)) {
                $criteria->$destination = DbHelper::escapeParam($data);
            }

            //
            // Each field needs special processing, sort that out here
            //

            try {
                // The field handle needs to be modified in some cases (Matrix and Table). Here, we don't override
                // the original handle for future iterations. We use the original handle to identify Matrix/Table fields.
                $handle = $destination;

                // Grab the field's content - formatted specifically for it
                $content = craft()->feedMe_fields->prepForFieldType($data, $handle);

                // Check to see if this is a Matrix field - need to merge any other fields mapped elsewhere in the feed
                // along with fields we've processed already. Involved due to multiple blocks can be defined at once.
                if (substr($destination, 0, 10) == '__matrix__') {
                    $content = craft()->feedMe_fields->handleMatrixData($fieldData, $handle, $content);
                }

                // And another special case for Table data
                if (substr($destination, 0, 9) == '__table__') {
                    $content = craft()->feedMe_fields->handleTableData($fieldData, $handle, $content);
                }

                // And another special case for SuperTable data
                if (substr($destination, 0, 14) == '__supertable__') {
                    $content = craft()->feedMe_fields->handleSuperTableData($fieldData, $handle, $content);
                }

                // Finally - we have our mapped data, formatted for the particular field as required
                $fieldData[$handle] = $content;
            } catch (\Exception $e) {
                FeedMePlugin::log($feed->name . ': FeedMeError: ' . $e->getMessage() . '.', LogLevel::Error, true);

                return false;
            }
        }

        $existingEntry = $criteria->first();


        //
        // Check for Add/Update/Delete for existing entries
        //


        // If there's an existing matching entry
        if ($existingEntry && $feed['duplicateHandle'] != FeedMe_Duplicate::Delete) {

            // If we're updating
            if ($feed['duplicateHandle'] == FeedMe_Duplicate::Update) {

                // Fill new EntryModel with match
                $entry = $existingEntry;

            // If we're adding, make sure not to overwrite existing entry
            } else if ($feed['duplicateHandle'] == FeedMe_Duplicate::Add) {
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

            // Prepare Element model (the default stuff)
            $entry = craft()->feedMe_entry->prepForElementModel($fieldData, $entry);

            // Set our data for this EntryModel (our mapped data)
            $entry->setContentFromPost($fieldData);

            //echo '<pre>';
            //print_r($entry->title);
            //echo '</pre>';

            try {
                // Save the entry!
                if (!craft()->entries->saveEntry($entry)) {
                    FeedMePlugin::log($feed->name . ': ' . json_encode($entry->getErrors()), LogLevel::Error, true);

                    return false;
                } else {

                    // Successfully saved/added entry
                    if ($feed['duplicateHandle'] == FeedMe_Duplicate::Update) {
                        FeedMePlugin::log($feed->name . ': Entry successfully updated: ' . $entry->id, LogLevel::Info, true);
                    } else {
                        FeedMePlugin::log($feed->name . ': Entry successfully added: ' . $entry->id, LogLevel::Info, true);
                    }

                    return true;
                }
            } catch (\Exception $e) {
                FeedMePlugin::log($feed->name . ': Entry FeedMeError: ' . $e->getMessage() . '.', LogLevel::Error, true);

                return false;
            }
        }
    }
}
