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
        $criteria->status = isset($settings['fieldMapping']['status']) ? $settings['fieldMapping']['status'] : null;

        // Look in same section when replacing
        $criteria->sectionId = $settings['section'];
        $criteria->type = $settings['entrytype'];

        return $criteria;
    }

    public function delete($elements)
    {
        return craft()->entries->deleteEntry($elements);
    }

    // Prepare reserved ElementModel values
    public function prepForElementModel(&$fields, EntryModel $element)
    {
        // Set author
        $author = 'authorId';
        if (isset($fields[$author])) {
            $element->$author = intval($fields[$author]);
            unset($fields[$author]);
        } else {
            $element->$author = ($element->$author ? $element->$author : (craft()->userSession->getUser() ? craft()->userSession->getUser()->id : 1));
        }

        // Set slug
        $slug = 'slug';
        if (isset($fields[$slug])) {
            $element->$slug = ElementHelper::createSlug($fields[$slug]);
            unset($fields[$slug]);
        }

        // Set postdate
        $postDate = 'postDate';
        if (isset($fields[$postDate])) {
            $element->$postDate = DateTime::createFromString($fields[$postDate], craft()->timezone);
            unset($fields[$postDate]);
        }

        // Set expiry date
        $expiryDate = 'expiryDate';
        if (isset($fields[$expiryDate])) {
            $element->$expiryDate = DateTime::createFromString($fields[$expiryDate], craft()->timezone);
            unset($fields[$expiryDate]);
        }

        // Set enabled
        $enabled = 'enabled';
        if (isset($fields[$enabled])) {
            $element->$enabled = (bool) $fields[$enabled];
            unset($fields[$enabled]);
        }

        // Set title
        $title = 'title';
        if (isset($fields[$title])) {
            $element->getContent()->$title = $fields[$title];
            unset($fields[$title]);
        }

        // Set parent or ancestors
        $parent = 'parentId';
        $ancestors = 'ancestors';

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

            unset($fields[$parent]);
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

            unset($fields[$ancestors]);
        }

        // Return element
        return $element;
    }

    public function save(EntryModel &$element, $settings)
    {
        if (craft()->entries->saveEntry($element)) {
            return true;
        }

        return false;
    }
}
