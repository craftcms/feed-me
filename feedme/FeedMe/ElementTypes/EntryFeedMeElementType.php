<?php
namespace Craft;

use Cake\Utility\Hash as Hash;

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
        $criteria->type = $settings['elementGroup']['Entry']['entryType'];

        if ($settings['locale']) {
            $criteria->locale = $settings['locale'];
        }

        return $criteria;
    }

    public function matchExistingElement(&$criteria, $data, $settings)
    {
        foreach ($settings['fieldUnique'] as $handle => $value) {
            if ((int)$value === 1) {
                $feedValue = Hash::get($data, $handle . '.data', $handle);

                // Special-case for Title which can be dynamic
                if ($handle == 'title') {
                    $entryTypeId = $settings['elementGroup']['Entry']['entryType'];
                    $entryType = craft()->sections->getEntryTypeById($entryTypeId);

                    // Its dynamically generated
                    if (!$entryType->hasTitleField) {
                        $feedValue = craft()->templates->renderObjectTemplate($entryType->titleFormat, $data);
                    }
                }

                if ($feedValue) {
                    $criteria->$handle = DbHelper::escapeParam($feedValue);
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

    public function prepForElementModel(BaseElementModel $element, array &$data, $settings)
    {
        $checkAncestors = !isset($data['parentId']);

        if (isset($settings['locale'])) {
            $element->localeEnabled = true;
        }

        foreach ($data as $handle => $value) {
            switch ($handle) {
                case 'id';
                    $element->$handle = $value['data'];
                    break;
                case 'authorId';
                    $element->$handle = $this->_prepareAuthorForElement($value['data']);
                    break;
                case 'slug';
                    $element->$handle = ElementHelper::createSlug($value['data']);
                    break;
                case 'postDate':
                case 'expiryDate';
                    $element->$handle = $this->_prepareDateForElement($value['data']);
                    break;
                case 'enabled':
                    $element->$handle = (bool)$value['data'];
                    break;
                case 'title':
                    $element->getContent()->$handle = $value['data'];
                    break;
                case 'parent':
                    $element->parentId = $this->_prepareParentForElement($value, $element->sectionId);
                    break;
                default:
                    continue 2;
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

    public function save(BaseElementModel &$element, array $data, $settings)
    {
        // Are we targeting a specific locale here? If so, we create an essentially blank element
        // for the primary locale, and instead create a locale for the targeted locale
        if (isset($settings['locale'])) {
            // Save the default locale element empty
            if (craft()->entries->saveEntry($element)) {
                // Now get the successfully saved (empty) element, and set content on that instead
                $elementLocale = craft()->entries->getEntryById($element->id, $settings['locale']);
                $elementLocale->setContentFromPost($data);

                // Save the locale entry
                return craft()->entries->saveEntry($elementLocale);
            } else {
                if ($element->getErrors()) {
                    throw new Exception(json_encode($element->getErrors()));
                } else {
                    throw new Exception(Craft::t('Unknown Element error occurred.'));
                }
            }

            return false;
        } else {
            return craft()->entries->saveEntry($element);
        }
    }

    public function afterSave(BaseElementModel $element, array $data, $settings)
    {

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

    private function _prepareParentForElement($fieldData, $sectionId)
    {
        $parentId = null;

        $data = Hash::get($fieldData, 'data');
        $attribute = Hash::get($fieldData, 'options.match', 'id');

        if (!empty($data)) {
            $criteria = craft()->elements->getCriteria(ElementType::Entry);
            $criteria->sectionId = $sectionId;
            $criteria->$attribute = DbHelper::escapeParam($data);
            $criteria->limit = 1;

            if ($criteria->total()) {
                $parentId = $criteria->ids()[0];
            }
        }

        return $parentId;
    }
}