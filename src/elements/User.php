<?php
namespace verbb\feedme\elements;

use verbb\feedme\base\Element;
use verbb\feedme\base\ElementInterface;
use verbb\feedme\helpers\AssetHelper;

use Craft;
use craft\db\Query;
use craft\elements\Asset as AssetElement;
use craft\elements\User as UserElement;
use craft\helpers\FileHelper;
use craft\helpers\UrlHelper;

use Cake\Utility\Hash;

class User extends Element implements ElementInterface
{
    // Properties
    // =========================================================================

    public static $name = 'User';
    public static $class = 'craft\elements\User';

    public $element;


    // Templates
    // =========================================================================

    public function getGroupsTemplate()
    {
        return 'feed-me/_includes/elements/user/groups';
    }

    public function getColumnTemplate()
    {
        return 'feed-me/_includes/elements/user/column';
    }

    public function getMappingTemplate()
    {
        return 'feed-me/_includes/elements/user/map';
    }


    // Public Methods
    // =========================================================================

    public function getGroups()
    {
        $result = false;

        // User are only allowed for Craft Pro
        if (Craft::$app->getEdition() == Craft::Pro) {
            $groups = Craft::$app->userGroups->getAllGroups();

            $result = count($groups) ? $groups : true;
        }

        return $result;
    }

    public function getQuery($settings, $params = [])
    {
        $query = UserElement::find();

        $criteria = array_merge([
            'status' => null,
        ], $params);

        $siteId = Hash::get($settings, 'siteId');

        if ($siteId) {
            $criteria['siteId'] = $siteId;
        }

        Craft::configure($query, $criteria);

        return $query;
    }

    public function setModel($settings)
    {
        $this->element = new UserElement();

        $siteId = Hash::get($settings, 'siteId');

        if ($siteId) {
            $this->element->siteId = $siteId;
        }

        return $this->element;
    }

    public function afterSave($data, $settings)
    {
        $newGroupsIds = Hash::get($data, 'groups');
        $profilePhoto = Hash::get($data, 'photo');

        if ($newGroupsIds) {
            $existingGroupsIds = Hash::extract($this->element->groups, '{n}.id');

            $groupIds = array_unique(array_merge($newGroupsIds, $existingGroupsIds));

            Craft::$app->users->assignUserToGroups($this->element->id, $groupIds);
        }

        if ($profilePhoto) {
            $filename = basename($profilePhoto);

            Craft::$app->users->saveUserPhoto($profilePhoto, $this->element, $filename);
        } 
    }


    // Protected Methods
    // =========================================================================

    protected function parseGroups($feedData, $fieldInfo)
    {
        $value = $this->fetchArrayValue($feedData, $fieldInfo);

        $groups = [];

        foreach ($value as $key => $dataValue) {
            if (is_numeric($dataValue)) {
                $groups[] = $dataValue;

                continue;
            }

            // Try to get via name
            $result = (new Query())
                ->select(['id', 'name', 'handle'])
                ->from(['{{%usergroups}}'])
                ->where(['name' => $dataValue])
                ->orWhere(['handle' => $dataValue])
                ->one();

            if (!$result) {
                continue;
            }

            $groups[] = $result['id'];
        }

        // Dealt with in `afterSave` as we need to combine permissions
        return $groups;
    }

    protected function parsePhoto($feedData, $fieldInfo)
    {
        $value = $this->fetchSimpleValue($feedData, $fieldInfo);

        $upload = Hash::get($fieldInfo, 'options.upload');
        $conflict = Hash::get($fieldInfo, 'options.conflict');

        // Try to find an existing element
        $urlToUpload = null;

        // If we're uploading files, this will need to be an absolute URL. If it is, save until later.
        // We also don't check for existing assets here, so break out instantly.
        if ($upload && UrlHelper::isAbsoluteUrl($value)) {
            $urlToUpload = $value;

            // If we're opting to use the already uploaded asset, we can check here
            if ($conflict === AssetElement::SCENARIO_INDEX) {
                $value = AssetHelper::getRemoteUrlFilename($value);
            }
        }

        // Search anywhere in Craft
        $foundElement = AssetElement::findOne(['filename' => $value]);

        // Do we want to match existing elements, and was one found?
        if ($foundElement && $conflict === AssetElement::SCENARIO_INDEX) {
            // If so, we still need to make a copy temporarily, as the Users service needs to add it in properly
            return $foundElement->getCopyOfFile();
        }

        // We can't find an existing asset, we need to download it, or plain ignore it
        if ($urlToUpload) {
            $folderId = $this->_prepareUserPhotosFolder($this->element);

            $uploadedElementIds = AssetHelper::fetchRemoteImage([$urlToUpload], $fieldInfo, null, $this->element, $folderId);

            if ($uploadedElementIds) {
                $uploadedAsset = AssetElement::findOne(['id' => $uploadedElementIds[0]]);

                // We still need to make a copy temporarily, as the Users service needs to add it in properly
                return $uploadedAsset->getCopyOfFile();
            }
        }
    }

    protected function parseStatus($feedData, $fieldInfo)
    {
        $value = $this->fetchSimpleValue($feedData, $fieldInfo);

        switch ($value) {
            case 'locked';
                $this->element->locked = true;
                break;
            case 'suspended';
                $this->element->locked = false;
                $this->element->suspended = true;
                break;
            case 'archived':
                $this->element->locked = false;
                $this->element->suspended = false;
                $this->element->archived = true;
                break;
            case 'pending':
                $this->element->locked = false;
                $this->element->suspended = false;
                $this->element->archived = false;
                $this->element->pending = true;
                break;
            case 'active':
                $this->element->suspended = false;
                $this->element->locked = false;
                $this->element->setActive();
                break;
        }
    }



    // Private Methods
    // =========================================================================

    private function _prepareUserPhotosFolder($user)
    {
        $assetsService = Craft::$app->getAssets();
        $volumes = Craft::$app->getVolumes();

        $volumeId = Craft::$app->getSystemSettings()->getSetting('users', 'photoVolumeId');
        $volume = $volumes->getVolumeById($volumeId);

        $subpath = (string)Craft::$app->getSystemSettings()->getSetting('users', 'photoSubpath');

        if ($subpath) {
            $subpath = Craft::$app->getView()->renderObjectTemplate($subpath, $user);
        }

        return $assetsService->ensureFolderByFullPathAndVolume($subpath, $volume);
    }

}