<?php
namespace Craft;

use Cake\Utility\Hash as Hash;

class EntryFeedMeElementType extends BaseFeedMeElementType
{
    // Properties
    // =========================================================================

    private $_requiredFields = array();


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

    public function getMappingTemplate()
    {
        return 'feedme/_includes/elements/entry/map';
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

        // While we're at it - save a list of required fields for later. We only want to do this once
        // per import, and its vital when importing into specific locales
        /*$entryType = craft()->sections->getEntryTypeById($element->typeId);

        $this->_requiredFields = craft()->db->createCommand()
            ->from('fieldlayoutfields flf')
            ->join('fields f', 'flf.fieldId = f.id')
            ->where('flf.layoutId = :layoutId', array(':layoutId' => $entryType->fieldLayoutId))
            ->andWhere('flf.required = 1')
            ->queryAll();*/

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
                $feedValue = Hash::get($data, $handle . '.data', $data[$handle]);

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
        public function unpublish(array $elements)
        {
            // Mark all as false
            $elementIds = [];
            foreach ($elements as $key => $element) {
                $elementIds[] = $element->id;
            }
            return craft()->db->createCommand()
                ->update(
                    'elements',
                    ['enabled' => false],
                    ['in', 'id', $elementIds]);
        }

    public function prepForElementModel(BaseElementModel $element, array &$data, $settings)
    {
        $checkAncestors = !isset($data['parentId']);

        foreach ($data as $handle => $value) {
            if (is_null($value)) {
                continue;
            }

            if (isset($value['data']) && $value['data'] === null) {
                continue;
            }

            if (is_array($value)) {
                $dataValue = Hash::get($value, 'data', $value);
            } else {
                $dataValue = $value;
            }

            switch ($handle) {
                case 'id';
                    $element->$handle = $dataValue;
                    break;
                case 'authorId';
                    $element->$handle = $this->_prepareAuthorForElement($dataValue);
                    break;
                case 'slug';
                    $element->$handle = ElementHelper::createSlug($dataValue);
                    break;
                case 'postDate':
                case 'expiryDate';
                    $dateValue = $this->_prepareDateForElement($dataValue);

                    // Ensure there's a parsed data - null will auto-generate a new date
                    if ($dateValue) {
                        $element->$handle = $dateValue;
                    }

                    break;
                case 'enabled':
                    $element->$handle = (bool)$dataValue;
                    break;
                case 'title':
                    $element->getContent()->$handle = $dataValue;
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
        if (isset($settings['locale']) && $settings['locale']) {
            // While we want to create a blank primary locale, we need to check for required fields..
            //$this->_populateRequiredFields($element, $data);

            // Save the default locale element empty
            if (craft()->entries->saveEntry($element)) {
                // Now get the successfully saved (empty) element, and set content on that instead
                $elementLocale = craft()->entries->getEntryById($element->id, $settings['locale']);
                $elementLocale->setContentFromPost($data);

                // Save the locale entry
                if (craft()->entries->saveEntry($elementLocale)) {
                    return true;
                } else {
                    if ($elementLocale->getErrors()) {
                        throw new Exception(json_encode($elementLocale->getErrors()));
                    } else {
                        throw new Exception(Craft::t('Unknown Element error occurred.'));
                    }
                }
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

    private function _populateRequiredFields($element, $data)
    {
        $requiredContent = array();

        // This is called when importing into a specific locale. We first save the primary element - but, we need to
        // populate any required fields for the original locale, otherwise it'll fail to save at all...
        foreach ($this->_requiredFields as $row) {
            $handle = $row['handle'];

            // Check if this element already has content for this field - no need to add otherwise
            if (is_null($element->$handle)) {
                $requiredContent[$handle] = $data[$handle];
            }
        }

        if (count($requiredContent)) {
            $element->setContentFromPost($requiredContent);
        }
    }

    private function _prepareDateForElement($date)
    {
        $craftDate = null;

        if (!is_array($date)) {
            $d = date_parse($date);
            $date_string = date('Y-m-d H:i:s', mktime($d['hour'], $d['minute'], $d['second'], $d['month'], $d['day'], $d['year']));

            $craftDate = DateTime::createFromString($date_string, craft()->timezone);
        } else {
            $craftDate = $date;
        }

        return $craftDate;
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