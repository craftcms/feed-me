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

    public function getMappingTemplate()
    {
        return 'feedme/_includes/elements/user/map';
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
                $feedValue = Hash::get($data, $handle);
                $feedValue = Hash::get($data, $handle . '.data', $feedValue);

                if ($feedValue) {
                    $criteria->$handle = DbHelper::escapeParam($feedValue);
                } else {
                    FeedMePlugin::log('User: no data for `' . $handle . '` to match an existing element on. Is data present for this in your feed?', LogLevel::Error, true);
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
            if (!craft()->users->deleteUser($element)) {
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

    public function prepForElementModel(BaseElementModel $element, array &$data, $settings)
    {
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
                case 'id':
                case 'username':
                case 'firstName':
                case 'lastName':
                case 'email':
                case 'preferredLocale':
                case 'newPassword':
                    $element->$handle = $dataValue;
                    break;
                case 'groups':
                    $this->_handleUserGroups($element, $dataValue);
                    break;
                case 'photo':
                    $this->_handleUserPhoto($element, $dataValue);
                    break;
                case 'status':
                    $this->_setUserStatus($element, $dataValue);
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
        // Because our main processing function checks for locale-only content, the content field won't be
        // prepped with data. However - user profile fields aren't multi-locale, and often validation will fail.
        // So pretty much ignore local-targeting (because there's only one), and put back the content
        $element->setContentFromPost($data);
        
        if (craft()->users->saveUser($element)) {
            // Set user groups, but careful to check if we're actually mapping or using existing ones
            if ($element->groups) {
                if (is_numeric($element->groups[0])) {
                    craft()->userGroups->assignUserToGroups($element->id, $element->groups);
                }
            }
            
            return true;
        }

        return false;
    }

    public function afterSave(BaseElementModel $element, array $data, $settings)
    {
        
    }


    // Private Methods
    // =========================================================================

    private function _handleUserGroups(UserModel $user, $dataValue)
    {
        $groups = array();

        // Get any existing groups for this user
        if ($user->groups) {
            foreach ($user->groups as $group) {
                if (is_numeric($group)) {
                    $groups[] = $group;
                } else {
                    $groups[] = $group->id;
                }
            }
        }

        if (!is_array($dataValue)) {
            $dataValue = array($dataValue);
        }

        foreach ($dataValue as $value) {
            if (!is_numeric($value)) {
                $result = UserGroupRecord::model()->findByAttributes(array('name' => $value));

                if (!$result) {
                    $result = UserGroupRecord::model()->findByAttributes(array('handle' => $value));
                }

                if (!$result) {
                    continue;
                }

                $group = UserGroupModel::populateModel($result);
                $value = $group->id;
            }

            if (!in_array($value, $groups)) {
                $groups[] = $value;
            }
        }

        $user->groups = $groups;

        return $user;
    }

    private function _handleUserPhoto(UserModel $user, $dataValue)
    {
        $photoPath = craft()->path->getUserPhotosPath();

        // Support for remote-download of image for profile
        if (UrlHelper::isAbsoluteUrl($dataValue)) {
            $filename = basename($dataValue);

            $tempPath = craft()->path->getTempPath();

            // Check if the temp path exists first
            if (!IOHelper::getRealPath($tempPath)) {
                IOHelper::createFolder($tempPath, craft()->config->get('defaultFolderPermissions'), true);

                if (!IOHelper::getRealPath($tempPath)) {
                    throw new Exception(Craft::t('Temp folder “{tempPath}” does not exist and could not be created', array('tempPath' => $tempPath)));
                }
            }

            $photo = $tempPath . $filename;

            $defaultOptions = array(
                CURLOPT_FILE => fopen($photo, 'w'),
                CURLOPT_FOLLOWLOCATION => true,
                CURLOPT_URL => $dataValue,
                CURLOPT_FAILONERROR => true,
            );

            $configOptions = craft()->config->get('curlOptions', 'feedMe');

            if ($configOptions) {
                $opts = $configOptions + $defaultOptions;
            } else {
                $opts = $defaultOptions;
            }

            $ch = curl_init();
            curl_setopt_array($ch, $opts);
            $result = curl_exec($ch);

            if (!$result) {
                return false;
            }
        } else {
            $filename = $dataValue;
            $photo = $photoPath . $dataValue;

            if (!IOHelper::fileExists($photo)) {
                return false;
            }
        }

        $image = craft()->images->loadImage($photo);
        $imageWidth = $image->getWidth();
        $imageHeight = $image->getHeight();

        $dimension = min($imageWidth, $imageHeight);
        $horizontalMargin = ($imageWidth - $dimension) / 2;
        $verticalMargin = ($imageHeight - $dimension) / 2;
        $image->crop($horizontalMargin, $imageWidth - $horizontalMargin, $verticalMargin, $imageHeight - $verticalMargin);

        craft()->users->saveUserPhoto($filename, $image, $user);

        // Cleanup any leftover temp image from remote upload
        if (UrlHelper::isAbsoluteUrl($dataValue)) {
            IOHelper::deleteFile($photo, true);
        }
    }

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