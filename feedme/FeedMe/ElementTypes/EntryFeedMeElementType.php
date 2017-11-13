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

        $section = craft()->sections->getSectionById($element->sectionId);
        $locale = craft()->i18n->getPrimarySiteLocale();

        if ($settings['locale']) {
            $element->locale = $settings['locale'];

            if (isset($section->locales[$locale->id])) {
                $element->localeEnabled = $section->locales[$locale->id]->enabledByDefault;
            }
        }

        // While we're at it - save a list of required fields for later. We only want to do this once
        // per import, and its vital when importing into specific locales
        $entryType = craft()->sections->getEntryTypeById($element->typeId);

        $this->_requiredFields = craft()->db->createCommand()
            ->select('f.id, f.handle')
            ->from('fieldlayoutfields flf')
            ->join('fields f', 'flf.fieldId = f.id')
            ->where('flf.layoutId = :layoutId', array(':layoutId' => $entryType->fieldLayoutId))
            ->andWhere('flf.required = 1')
            ->queryAll();

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
                $feedValue = Hash::get($data, $handle);
                $feedValue = Hash::get($data, $handle . '.data', $feedValue);

                // Special-case for Title which can be dynamic
                if ($handle == 'title') {
                    $entryTypeId = $settings['elementGroup']['Entry']['entryType'];
                    $entryType = craft()->sections->getEntryTypeById($entryTypeId);

                    // Its dynamically generated
                    if (!$entryType->hasTitleField) {
                        $feedValue = craft()->templates->renderObjectTemplate($entryType->titleFormat, $data);
                    }
                }

                if ($handle == 'postDate' || $handle == 'expiryDate') {
                    $feedValue = FeedMeDateHelper::getDateTimeString($feedValue);
                }

                if ($feedValue) {
                    $criteria->$handle = DbHelper::escapeParam($feedValue);
                } else {
                    FeedMePlugin::log('Entry: no data for `' . $handle . '` to match an existing element on. Is data present for this in your feed?', LogLevel::Error, true);
                    return false;
                }
            }
        }

        // Check to see if an element already exists - interestingly, find()[0] is faster than first()
        $elements = $criteria->find();

        if (count($elements)) {
            return $elements[0];
        }

        return null;
    }

    public function delete(array $elements)
    {
        $success = true;

        foreach ($elements as $element) {
            if (!craft()->entries->deleteEntry($element)) {
                if ($element->getErrors()) {
                    throw new Exception(json_encode($element->getErrors()));
                } else {
                    throw new Exception(Craft::t('Something went wrong while updating elements.'));
                }

                $success = false;
            }
        }

        return $success;
    }

    public function disable(array $elements)
    {
        // Mark all as false
        $elementIds = array();

        foreach ($elements as $element) {
            $elementIds[] = $element->id;
        }

        return craft()->db->createCommand()->update('elements', array('enabled' => 0), array('in', 'id', $elementIds));
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
                $dataValue = Hash::get($value, 'data', null);
            } else {
                $dataValue = $value;
            }

            // Check for any Twig shorthand used
            $this->parseInlineTwig($data, $dataValue);

            switch ($handle) {
                case 'id';
                    $element->$handle = $dataValue;
                    break;
                case 'authorId';
                    $element->$handle = $this->prepareAuthorForElement($dataValue);
                    break;
                case 'slug';
                    if (craft()->config->get('limitAutoSlugsToAscii')) {
                        $dataValue = StringHelper::asciiString($dataValue);
                    }

                    $element->$handle = ElementHelper::createSlug($dataValue);
                    break;
                case 'postDate':
                case 'expiryDate';
                    $dateValue = FeedMeDateHelper::parseString($dataValue);

                    // Ensure there's a parsed data - null will auto-generate a new date
                    if ($dateValue) {
                        $element->$handle = $dateValue;
                    }

                    break;
                case 'enabled':
                case 'localeEnabled':
                    $element->$handle = FeedMeHelper::parseBoolean($dataValue);
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
            $this->_populateRequiredFields($element, $data);

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

    private function _populateRequiredFields($element, $feedData)
    {
        $requiredContent = array();

        // This is called when importing into a specific locale. We first save the primary element - but, we need to
        // populate any required fields for the original locale, otherwise it'll fail to save at all...
        foreach ($this->_requiredFields as $row) {
            $handle = $row['handle'];

            $data = Hash::get($feedData, $handle);

            // Check if this element already has content for this field - no need to add otherwise
            $existingData = $element->getFieldValue($handle);

            // Some special cases for element fields
            if ($existingData instanceof ElementCriteriaModel) {
                $existingData = $existingData->ids();
            }

            // If there's existing data, don't overwrite from our feed, priority is existing content
            if (is_null($existingData) || count($existingData) == 0) {
                $requiredContent[$handle] = $data;
            } else {
                $requiredContent[$handle] = $existingData;
            }
        }

        if (count($requiredContent)) {
            $element->setContentFromPost($requiredContent);
        }
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