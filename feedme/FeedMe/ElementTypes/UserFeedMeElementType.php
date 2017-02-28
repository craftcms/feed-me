<?php
namespace Craft;

use Cake\Utility\Hash as Hash;

class UserFeedMeElementType extends BaseFeedMeElementType
{
    // Templates
    // =========================================================================

    public function getGroupsTemplate()
    {
        return 'feedme/_includes/elements/user/groups';
    }

    public function getColumnTemplate()
    {
        return 'feedme/_includes/elements/user/column';
    }


    // Public Methods
    // =========================================================================

    public function getGroups()
    {
        $result = false;

        // User are only allowed for Craft Pro
        if (craft()->getEdition() == Craft::Pro) {
            $groups = craft()->userGroups->getAllGroups();

            $result = count($groups) ? $groups : true;
        }

        return $result;
    }

    public function setModel($settings)
    {
        $element = new UserModel();

        if ($settings['locale']) {
            $element->locale = $settings['locale'];
        }

        return $element;
    }

    public function setCriteria($settings)
    {
        $criteria = craft()->elements->getCriteria(ElementType::User);
        $criteria->status = null;
        $criteria->limit = null;
        //$criteria->group = null;
        $criteria->localeEnabled = null;
        
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
        $return = true;

        // Delete users
        foreach ($elements as $element) {
            if (!craft()->users->deleteUser($element)) {
                $return = false;
            }
        }

        return $return;
    }

    public function prepForElementModel(BaseElementModel $element, array &$data, $settings)
    {
        foreach ($data as $handle => $value) {
            switch ($handle) {
                case 'id':
                case 'username':
                case 'firstName':
                case 'lastName':
                case 'email':
                case 'prefLocale':
                case 'newPassword':
                case 'photo':
                    $element->$handle = $value['data'];
                    break;
                case 'status':
                    $this->_setUserStatus($element, $value['data']);
                    break;
                default:
                    continue 2;
            }

            // Update the original data in our feed - for clarity in debugging
            $data[$handle] = $element->$handle;
        }

        // Set email as username
        if (craft()->config->get('useEmailAsUsername')) {
            $element->username = $element->email;
        }

        return $element;
    }

    public function save(BaseElementModel &$element, array $data, $settings)
    {
        // Are we targeting a specific locale here? If so, we create an essentially blank element
        // for the primary locale, and instead create a locale for the targeted locale
        if (isset($settings['locale'])) {
            // Save the default locale element empty
            if (craft()->users->saveUser($element)) {
                // Now get the successfully saved (empty) element, and set content on that instead
                $elementLocale = craft()->users->getUserById($element->id, $settings['locale']);
                $elementLocale->setContentFromPost($data);

                // Save the locale entry
                if (craft()->users->saveUser($elementLocale)) {
                    craft()->userGroups->assignUserToGroups($elementLocale->id, $settings['elementGroup']['User']);
                    return true;
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
            if (craft()->users->saveUser($element)) {
                craft()->userGroups->assignUserToGroups($element->id, $settings['elementGroup']['User']);
                return true;
            }
        }
    }

    public function afterSave(BaseElementModel $element, array $data, $settings)
    {
        
    }


    // Private Methods
    // =========================================================================

    private function _setUserStatus(UserModel $user, $status)
    {
        switch ($status) {
            case 'locked';
                $user->locked = true;
                break;
            case 'suspended';
                $user->locked = false;
                $user->suspended = true;
                break;
            case 'archived':
                $user->locked = false;
                $user->suspended = false;
                $user->archived = true;
                break;
            case 'pending':
                $user->locked = false;
                $user->suspended = false;
                $user->archived = false;
                $user->pending = true;
                break;
            case 'active':
                $user->suspended = false;
                $user->locked = false;
                $user->setActive();
                break;
        }

        return $user;
    }
}