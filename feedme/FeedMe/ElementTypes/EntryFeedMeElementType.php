<?php
namespace Craft;

class EntryFeedMeElementType extends BaseFeedMeElementType
{
    // Templates
    // =========================================================================

    public function getGroupsTemplate()
    {
        return 'feedme/_includes/elements/entry/groups';
    }

    public function getColumnTemplate()
    {
        return 'feedme/_includes/elements/entry/column';
    }


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
        $element = new EntryModel();
        $element->sectionId = $settings['elementGroup']['Entry']['section'];
        $element->typeId = $settings['elementGroup']['Entry']['entryType'];

        if ($settings['locale']) {
            $element->locale = $settings['locale'];
        }

        return $element;
    }

    public function setCriteria($settings)
    {
        $criteria = craft()->elements->getCriteria(ElementType::Entry);
        $criteria->status = null;
        $criteria->limit = null;
        $criteria->localeEnabled = null;

        $criteria->sectionId = $settings['elementGroup']['Entry']['section'];
        $criteria->typeId = $settings['elementGroup']['Entry']['entryType'];
        
        if ($settings['locale']) {
            $criteria->locale = $settings['locale'];
        }

        return $criteria;
    }

    public function matchExistingElement(&$criteria, $data, $settings)
    {
        foreach ($settings['fieldUnique'] as $handle => $value) {
            if (intval($value) == 1 && ($data != '__')) {
                if (isset($data[$handle])) {
                    $criteria->$handle = DbHelper::escapeParam($data[$handle]);
                } else {
                    throw new Exception(Craft::t('Unable to match against '.$handle.' - no data found.'));
                }
            }
        }

        // Check to see if an element already exists - interestingly, find()[0] is faster than first()
        return $criteria->find();
    }

    public function delete(array $elements)
    {
        return craft()->entries->deleteEntry($elements);
    }

    public function prepForElementModel(BaseElementModel $element, array &$data, $settings, $options)
    {
        $checkAncestors = !isset($data['parentId']);

        if (isset($settings['locale'])) {
            $element->localeEnabled = true;
        }

        foreach ($data as $handle => $value) {
            if ($value == '' || $value == '__') {
                continue;
            }

            switch ($handle) {
                case 'id';
                    $element->$handle = $value;
                    break;
                case 'authorId';
                    $element->$handle = $this->_prepareAuthorForElement($value);
                    break;
                case 'slug';
                    $element->$handle = ElementHelper::createSlug($value);
                    break;
                case 'postDate':
                case 'expiryDate';
                    $element->$handle = $this->_prepareDateForElement($value);
                    break;
                case 'enabled':
                    $element->$handle = (bool)$value;
                    break;
                case 'title':
                    $element->getContent()->$handle = $value;
                    break;
                case 'parentId':
                    $element->$handle = $this->_prepareParentForElement($value, $element->sectionId);
                    break;
                case 'ancestors':
                    if ($checkAncestors) {
                        $element->parentId = $this->_prepareAncestorsForElement($value, $element->sectionId);
                    }
                    break;
                default:
                    break 2;
            }

            // Update the original data in our feed - for clarity in debugging
            $data[$handle] = $element->$handle;
        }

        // Set default author if not set
        if (!$element->authorId) {
            $user = craft()->userSession->getUser();
            $element->authorId = ($element->authorId ? $element->authorId : ($user ? $user->id : 1));

            // Update the original data in our feed - for clarity in debugging
            $data['authorId'] = $element->authorId;
        }

        return $element;
    }

    public function save(BaseElementModel &$element, $settings)
    {
        return craft()->entries->saveEntry($element);
    }

    public function afterSave(BaseElementModel $element, array $data, $settings)
    {
        /*if (isset($settings['locale'])) {
            $entry = craft()->entries->getEntryById($element->id, $settings['locale']);

            return craft()->entries->saveEntry($entryEs);
        }*/
    }


    // Private Methods
    // =========================================================================

    private function _prepareDateForElement($date)
    {
        if (!is_array($date)) {
            $d = date_parse($date);
            $date_string = date('Y-m-d H:i:s', mktime($d['hour'], $d['minute'], $d['second'], $d['month'], $d['day'], $d['year']));

            $date = DateTime::createFromString($date_string, craft()->timezone);
        }

        return $date;
    }

    private function _prepareAuthorForElement($author)
    {
        if (!is_numeric($author)) {
            $criteria = craft()->elements->getCriteria(ElementType::User);
            $criteria->search = $author;
            $authorUser = $criteria->first();
            
            if ($authorUser) {
                $author = $authorUser->id;
            } else {
                $user = craft()->users->getUserByUsernameOrEmail($author);
                $author = $user ? $user->id : 1;
            }
        }

        return $author;
    }

    private function _prepareParentForElement($data, $sectionId)
    {
        $parentId = null;

        // Don't connect empty fields
        if (!empty($data)) {

            // Find matching element
            $criteria = craft()->elements->getCriteria(ElementType::Entry);
            $criteria->sectionId = $sectionId;

            // Exact match
            $criteria->search = '"'.$data.'"';

            // Return the first found element for connecting
            if ($criteria->total()) {
                $parentId = $criteria->ids()[0];
            }
        }

        return $parentId;
    }

    private function _prepareAncestorsForElement($data, $sectionId)
    {
        $parentId = null;

        // Don't connect empty fields
        if (!empty($data)) {

            // Get section data
            $section = new SectionModel();
            $section->id = $sectionId;

            // This we append before the slugified path
            $sectionUrl = str_replace('{slug}', '', $section->getUrlFormat());

            // Find matching element by URI (dirty, not all structures have URI's)
            $criteria = craft()->elements->getCriteria(ElementType::Entry);
            $criteria->sectionId = $sectionId;
            $criteria->uri = $sectionUrl.craft()->import->slugify($data);
            $criteria->limit = 1;

            // Return the first found element for connecting
            if ($criteria->total()) {
                $parentId = $criteria->ids()[0];
            }
        }

        return $parentId;
    }
}