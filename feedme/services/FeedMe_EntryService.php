<?php
namespace Craft;

class FeedMe_EntryService extends BaseApplicationComponent
{
    // Public Methods
    // =========================================================================

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

        if ($settings['locale']) {
            $element->locale = $settings['locale'];
        }

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
        
        if ($settings['locale']) {
            $criteria->locale = $settings['locale'];
        }

        return $criteria;
    }

    // Prepare reserved ElementModel values
    public function prepForElementModel(&$fields, EntryModel $element)
    {
        // Set author
        $author = FeedMe_Element::Author;
        if (isset($fields[$author])) {
            $criteria = craft()->elements->getCriteria(ElementType::User);
            $criteria->search = $fields[$author];
            $authorUser = $criteria->first();

            if ($authorUser) {
                $element->$author = $authorUser->id;
            } else {
                $user = craft()->users->getUserByUsernameOrEmail($fields[$author]);
                $element->$author = (is_numeric($fields[$author]) ? $fields[$author] : ($user ? $user->id : 1));
            }
        } else {
            $user = craft()->userSession->getUser();
            $element->$author = ($element->$author ? $element->$author : ($user ? $user->id : 1));
        }

        // Set slug
        $slug = FeedMe_Element::Slug;
        if (isset($fields[$slug])) {
            $element->$slug = ElementHelper::createSlug($fields[$slug]);
        }

        // Set postdate
        $postDate = FeedMe_Element::PostDate;
        if (isset($fields[$postDate])) {
            $d = date_parse($fields[$postDate]);
            $date_string = date('Y-m-d H:i:s', mktime($d['hour'], $d['minute'], $d['second'], $d['month'], $d['day'], $d['year']));

            $element->$postDate = DateTime::createFromString($date_string, craft()->timezone);
        }

        // Set expiry date
        $expiryDate = FeedMe_Element::ExpiryDate;
        if (isset($fields[$expiryDate])) {
            $d = date_parse($fields[$expiryDate]);
            $date_string = date('Y-m-d H:i:s', mktime($d['hour'], $d['minute'], $d['second'], $d['month'], $d['day'], $d['year']));
            
            $element->$expiryDate = DateTime::createFromString($date_string, craft()->timezone);
        }

        // Set enabled
        $enabled = FeedMe_Element::Enabled;
        if (isset($fields[$enabled])) {
            $element->$enabled = (bool) $fields[$enabled];
        }

        // Set title
        $title = FeedMe_Element::Title;
        if (isset($fields[$title])) {
            $element->getContent()->$title = $fields[$title];
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
               //$criteria->uri = $sectionUrl.craft()->feedMe->slugify($data);
               $criteria->limit = 1;

               // Return the first found element for connecting
               if ($criteria->total()) {
                   $element->$parent = $criteria->first()->id;
               }
           }
        }

        // Return element
        return $element;
    }
}
