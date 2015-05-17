<?php
namespace Craft;

class FeedMe_EntryService extends BaseApplicationComponent
{
    public function getGroups()
    {
        // Get editable sections for user
        $editable = craft()->sections->getEditableSections();

        // Get sections but not singles
        $sections = array();
        foreach ($editable as $section) {
            if ($section->type != SectionType::Single) {
                $sections[] = $section;
            }
        }

        return $sections;
    }

    public function setModel($settings)
    {
        // Set up new entry model
        $element = new EntryModel();
        $element->sectionId = $settings['section'];
        $element->typeId = $settings['entrytype'];

        return $element;
    }

    public function setCriteria($settings)
    {
        // Match with current data
        $criteria = craft()->elements->getCriteria(ElementType::Entry);
        $criteria->limit = null;
        $criteria->localeEnabled = null;
        $criteria->status = isset($settings['fieldMapping']['status']) ? $settings['fieldMapping']['status'] : null;

        // Look in same section when replacing
        $criteria->sectionId = $settings['section'];
        $criteria->type = $settings['entrytype'];

        return $criteria;
    }

    /*public function delete($elements)
    {
        return craft()->entries->deleteEntry($elements);
    }*/

    // Prepare reserved ElementModel values
    public function prepForElementModel(&$fields, EntryModel $element)
    {

        // Set author
        $author = FeedMe_Element::Author;
        if (isset($fields[$author])) {
            $user = craft()->users->getUserByUsernameOrEmail($fields[$author]);
            $element->$author = (is_numeric($fields[$author]) ? $fields[$author] : ($user ? $user->id : 1));
            //unset($fields[$author]);
        } else {
            $user = craft()->userSession->getUser();
            $element->$author = ($element->$author ? $element->$author : ($user ? $user->id : 1));
        }

        // Set slug
        $slug = FeedMe_Element::Slug;
        if (isset($fields[$slug])) {
            $element->$slug = ElementHelper::createSlug($fields[$slug]);
            //unset($fields[$slug]);
        }

        // Set postdate
        $postDate = FeedMe_Element::PostDate;
        if (isset($fields[$postDate])) {
            $d = date_parse($fields[$postDate]);
            $date_string = date('Y-m-d H:i:s', mktime($d['hour'], $d['minute'], $d['second'], $d['month'], $d['day'], $d['year']));

            $element->$postDate = DateTime::createFromString($date_string, craft()->timezone);
            //unset($fields[$postDate]);
        }

        // Set expiry date
        $expiryDate = FeedMe_Element::ExpiryDate;
        if (isset($fields[$expiryDate])) {
            $d = date_parse($fields[$expiryDate]);
            $date_string = date('Y-m-d H:i:s', mktime($d['hour'], $d['minute'], $d['second'], $d['month'], $d['day'], $d['year']));
            
            $element->$expiryDate = DateTime::createFromString($date_string, craft()->timezone);
            //unset($fields[$expiryDate]);
        }

        // Set enabled
        $enabled = FeedMe_Element::Enabled;
        if (isset($fields[$enabled])) {
            $element->$enabled = (bool) $fields[$enabled];
            //unset($fields[$enabled]);
        }

        // Set title
        $title = FeedMe_Element::Title;
        if (isset($fields[$title])) {
            $element->getContent()->$title = $fields[$title];
            //unset($fields[$title]);
        }

        // Set parent or ancestors
        $parent = FeedMe_Element::Parent;
        $ancestors = FeedMe_Element::Ancestors;

        if (isset($fields[$parent])) {
           $data = $fields[$parent];

           // Don't connect empty fields
           if (!empty($data)) {

               // Find matching element
               $criteria = craft()->elements->getCriteria(ElementType::Entry);
               $criteria->sectionId = $element->sectionId;
               $criteria->search = '"'.$data.'"';

               // Return the first found element for connecting
               if ($criteria->total()) {
                   $element->$parent = $criteria->first()->id;
               }
            }

            //unset($fields[$parent]);
        } elseif (isset($fields[$ancestors])) {
           $data = $fields[$ancestors];

           // Don't connect empty fields
           if (!empty($data)) {

               // Get section data
               $section = new SectionModel();
               $section->id = $element->sectionId;

               // This we append before the slugified path
               $sectionUrl = str_replace('{slug}', '', $section->getUrlFormat());

               // Find matching element by URI (dirty, not all structures have URI's)
               $criteria = craft()->elements->getCriteria(ElementType::Entry);
               $criteria->sectionId = $element->sectionId;
               $criteria->uri = $sectionUrl.craft()->feedMe->slugify($data);
               $criteria->limit = 1;

               // Return the first found element for connecting
               if ($criteria->total()) {
                   $element->$parent = $criteria->first()->id;
               }
           }

            //unset($fields[$ancestors]);
        }

        // Return element
        return $element;
    }

    /*public function save(EntryModel &$element, $settings)
    {
        if (craft()->entries->saveEntry($element)) {
            return true;
        }

        return false;
    }*/

    public function saveEntry(EntryModel $entry)
    {
        $isNewEntry = !$entry->id;

        $hasNewParent = $this->_checkForNewParent($entry);

        if ($hasNewParent)
        {
            if ($entry->parentId)
            {
                $parentEntry = craft()->entries->getEntryById($entry->parentId, $entry->locale);

                if (!$parentEntry)
                {
                    throw new Exception(Craft::t('No entry exists with the ID “{id}”.', array('id' => $entry->parentId)));
                }
            }
            else
            {
                $parentEntry = null;
            }

            $entry->setParent($parentEntry);
        }

        // Get the entry record
        if (!$isNewEntry)
        {
            $entryRecord = EntryRecord::model()->findById($entry->id);

            if (!$entryRecord)
            {
                throw new Exception(Craft::t('No entry exists with the ID “{id}”.', array('id' => $entry->id)));
            }
        }
        else
        {
            $entryRecord = new EntryRecord();
        }

        // Get the section
        $section = craft()->sections->getSectionById($entry->sectionId);

        if (!$section)
        {
            throw new Exception(Craft::t('No section exists with the ID “{id}”.', array('id' => $entry->sectionId)));
        }

        // Verify that the section is available in this locale
        $sectionLocales = $section->getLocales();

        if (!isset($sectionLocales[$entry->locale]))
        {
            throw new Exception(Craft::t('The section “{section}” is not enabled for the locale {locale}', array('section' => $section->name, 'locale' => $entry->locale)));
        }

        // Set the entry data
        $entryType = $entry->getType();

        $entryRecord->sectionId  = $entry->sectionId;

        if ($section->type == SectionType::Single)
        {
            $entryRecord->authorId   = $entry->authorId = null;
            $entryRecord->expiryDate = $entry->expiryDate = null;
        }
        else
        {
            $entryRecord->authorId   = $entry->authorId;
            $entryRecord->postDate   = $entry->postDate;
            $entryRecord->expiryDate = $entry->expiryDate;
            $entryRecord->typeId     = $entryType->id;
        }

        if ($entry->enabled && !$entryRecord->postDate)
        {
            // Default the post date to the current date/time
            $entryRecord->postDate = $entry->postDate = DateTimeHelper::currentUTCDateTime();
        }

        $entryRecord->validate();
        $entry->addErrors($entryRecord->getErrors());

        if ($entry->hasErrors())
        {
            return false;
        }

        if (!$entryType->hasTitleField)
        {
            $entry->getContent()->title = craft()->templates->renderObjectTemplate($entryType->titleFormat, $entry);
        }

        $transaction = craft()->db->getCurrentTransaction() === null ? craft()->db->beginTransaction() : null;

        try
        {

                // Save the element
                $success = craft()->elements->saveElement($entry);

                // If it didn't work, rollback the transaction in case something changed in onBeforeSaveEntry
                if (!$success)
                {
                    if ($transaction !== null)
                    {
                        $transaction->rollback();
                    }

                    // If "title" has an error, check if they've defined a custom title label.
                    if ($entry->getError('title'))
                    {
                        // Grab all of the original errors.
                        $errors = $entry->getErrors();

                        // Grab just the title error message.
                        $originalTitleError = $errors['title'];

                        // Clear the old.
                        $entry->clearErrors();

                        // Create the new "title" error message.
                        $errors['title'] = str_replace('Title', $entryType->titleLabel, $originalTitleError);

                        // Add all of the errors back on the model.
                        $entry->addErrors($errors);
                    }

                    return false;
                }

                // Now that we have an element ID, save it on the other stuff
                if ($isNewEntry)
                {
                    $entryRecord->id = $entry->id;
                }

                // Save the actual entry row
                $entryRecord->save(false);

                if ($section->type == SectionType::Structure)
                {
                    // Has the parent changed?
                    if ($hasNewParent)
                    {
                        if (!$entry->parentId)
                        {
                            craft()->structures->appendToRoot($section->structureId, $entry);
                        }
                        else
                        {
                            craft()->structures->append($section->structureId, $entry, $parentEntry);
                        }
                    }

                    // Update the entry's descendants, who may be using this entry's URI in their own URIs
                    //craft()->elements->updateDescendantSlugsAndUris($entry);
                }

                // Save a new version
                if (craft()->getEdition() >= Craft::Client && $section->enableVersioning)
                {
                    //craft()->entryRevisions->saveVersion($entry);
                }

            // Commit the transaction regardless of whether we saved the entry, in case something changed
            // in onBeforeSaveEntry
            if ($transaction !== null)
            {
                $transaction->commit();
            }
        }
        catch (\Exception $e)
        {
            if ($transaction !== null)
            {
                $transaction->rollback();
            }

            throw $e;
        }

        return $success;
    }

    private function _checkForNewParent(EntryModel $entry)
    {
        // Make sure this is a Structure section
        if ($entry->getSection()->type != SectionType::Structure)
        {
            return false;
        }

        // Is it a brand new entry?
        if (!$entry->id)
        {
            return true;
        }

        // Was a parentId actually submitted?
        if ($entry->parentId === null)
        {
            return false;
        }

        // Is it set to the top level now, but it hadn't been before?
        if ($entry->parentId === '' && $entry->level != 1)
        {
            return true;
        }

        // Is it set to be under a parent now, but didn't have one before?
        if ($entry->parentId !== '' && $entry->level == 1)
        {
            return true;
        }

        // Is the parentId set to a different entry ID than its previous parent?
        $criteria = craft()->elements->getCriteria(ElementType::Entry);
        $criteria->ancestorOf = $entry;
        $criteria->ancestorDist = 1;
        $criteria->status = null;
        $criteria->localeEnabled = null;

        $oldParent = $criteria->first();
        $oldParentId = ($oldParent ? $oldParent->id : '');

        if ($entry->parentId != $oldParentId)
        {
            return true;
        }

        // Must be set to the same one then
        return false;
    }
}
