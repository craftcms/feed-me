<?php

namespace craft\feedme\elements;

use Cake\Utility\Hash;
use Craft;
use craft\base\ElementInterface;
use craft\db\Query;
use craft\elements\Asset as AssetElement;
use craft\elements\User as UserElement;
use craft\errors\VolumeException;
use craft\feedme\base\Element;
use craft\feedme\helpers\AssetHelper;
use craft\helpers\UrlHelper;
use craft\records\User as UserRecord;
use Throwable;
use yii\base\Exception;

/**
 *
 * @property-read string $mappingTemplate
 * @property-read bool $groups
 * @property-write mixed $model
 * @property-read string $groupsTemplate
 * @property-read string $columnTemplate
 */
class User extends Element
{
    // Properties
    // =========================================================================

    /**
     * @var string
     */
    public static string $name = 'User';

    /**
     * @var string
     */
    public static string $class = UserElement::class;

    /**
     * @var
     */
    public mixed $status = null;

    // Templates
    // =========================================================================

    /**
     * @inheritDoc
     */
    public function getGroupsTemplate(): string
    {
        return 'feed-me/_includes/elements/user/groups';
    }

    /**
     * @inheritDoc
     */
    public function getColumnTemplate(): string
    {
        return 'feed-me/_includes/elements/user/column';
    }

    /**
     * @inheritDoc
     */
    public function getMappingTemplate(): string
    {
        return 'feed-me/_includes/elements/user/map';
    }

    // Public Methods
    // =========================================================================

    /**
     * @inheritDoc
     */
    public function getGroups(): array
    {
        $result = [];

        // User are only allowed for Craft Pro
        if (Craft::$app->getEdition() == Craft::Pro) {
            $groups = Craft::$app->userGroups->getAllGroups();

            $result = count($groups) ? $groups : [];
        }

        return $result;
    }

    /**
     * @inheritDoc
     */
    public function getQuery($settings, array $params = []): mixed
    {
        $query = UserElement::find()
            ->status(null)
            ->siteId(Hash::get($settings, 'siteId'));
        Craft::configure($query, $params);
        return $query;
    }

    /**
     * @inheritDoc
     */
    public function setModel($settings): ElementInterface
    {
        $this->element = new UserElement();

        $this->status = null;

        $siteId = Hash::get($settings, 'siteId');

        if ($siteId) {
            $this->element->siteId = $siteId;
        }

        return $this->element;
    }

    /**
     * @inheritDoc
     */
    public function afterSave($data, $settings): void
    {
        $groupsIds = Hash::get($data, 'groups');

        // User status can't be set on the element anymore, only directly on the record.
        if ($this->status) {
            $record = UserRecord::findOne($this->element->id);

            // Reset all states - default to active
            $record->locked = false;
            $record->suspended = false;
            $record->pending = false;

            switch ($this->status) {
                case 'locked':
                    $record->locked = true;
                    break;
                case 'suspended':
                    $record->suspended = true;
                    break;
                case 'pending':
                    $record->pending = true;
                    break;
            }

            $record->save(false);
        }

        if ($groupsIds) {
            Craft::$app->users->assignUserToGroups($this->element->id, $groupsIds);
        }
    }

    /**
     * @inheritDoc
     */
    public function disable($elementIds): bool
    {
        foreach ($elementIds as $elementId) {
            // User status can't be set on the element anymore, only directly on the record.
            $record = UserRecord::findOne($elementId);
            $record->suspended = true;
            $record->save(false);
        }

        return true;
    }

    // Protected Methods
    // =========================================================================

    /**
     * @param $feedData
     * @param $fieldInfo
     * @return array
     */
    protected function parseGroups($feedData, $fieldInfo): array
    {
        $value = $this->fetchArrayValue($feedData, $fieldInfo);

        $newGroupsIds = [];

        foreach ($value as $dataValue) {
            if (is_numeric($dataValue)) {
                $newGroupsIds[] = $dataValue;

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

            $newGroupsIds[] = $result['id'];
        }

        $removeFromExisting = Hash::get($fieldInfo, 'options.removeFromExisting');
        $existingGroupsIds = Hash::extract($this->element->groups, '{n}.id');

        if ($removeFromExisting) {
            $groupIds = $newGroupsIds;
        } else {
            $groupIds = array_unique(array_merge($newGroupsIds, $existingGroupsIds));
        }

        // Dealt with in `afterSave` as we need to combine permissions
        return $groupIds;
    }

    /**
     * @param $feedData
     * @param $fieldInfo
     * @return int|mixed|string|null
     * @throws Exception
     * @throws Throwable
     * @throws VolumeException
     */
    protected function parsePhotoId($feedData, $fieldInfo): mixed
    {
        $value = $this->fetchSimpleValue($feedData, $fieldInfo);

        $upload = Hash::get($fieldInfo, 'options.upload');
        $conflict = Hash::get($fieldInfo, 'options.conflict');

        // Try to find an existing element
        $urlToUpload = null;

        // If we're uploading files, this will need to be an absolute URL. If it is, save until later.
        // We also don't check for existing assets here, so break out instantly.
        if ($upload && is_string($value) && UrlHelper::isAbsoluteUrl($value)) {
            $urlToUpload = $value;

            // If we're opting to use the already uploaded asset, we can check here
            if ($conflict === AssetElement::SCENARIO_INDEX) {
                $value = AssetHelper::getRemoteUrlFilename($value);
            }
        }

        // See if it's a default asset
        if (is_array($value) && isset($value[0])) {
            return $value[0];
        }

        $folderId = $this->_prepareUserPhotosFolder($this->element);

        // Search anywhere in Craft
        $foundElement = AssetElement::find()
            ->filename($value)
            ->folderId($folderId)
            ->one();

        // Do we want to match existing elements, and was one found?
        if ($foundElement && $conflict === AssetElement::SCENARIO_INDEX) {
            // If so, we still need to make a copy temporarily, as the Users service needs to add it in properly
            return $foundElement->id;
        }

        // We can't find an existing asset, we need to download it, or plain ignore it
        if ($urlToUpload) {
            $uploadedElementIds = AssetHelper::fetchRemoteImage([$urlToUpload], $fieldInfo, $this->feed, null, $this->element, $folderId);

            if ($uploadedElementIds) {

                // We still need to make a copy temporarily, as the Users service needs to add it in properly
                return $uploadedElementIds[0];
            }
        }

        return null;
    }

    /**
     * @param $feedData
     * @param $fieldInfo
     * @return void
     */
    protected function parseStatus($feedData, $fieldInfo): void
    {
        $value = $this->fetchSimpleValue($feedData, $fieldInfo);

        $this->status = $value;
    }

    // Private Methods
    // =========================================================================

    /**
     * @param $user
     * @return int|null
     * @throws Exception
     * @throws Throwable
     * @throws VolumeException
     */
    private function _prepareUserPhotosFolder($user): ?int
    {
        $assetsService = Craft::$app->getAssets();
        $volumes = Craft::$app->getVolumes();

        $volumeUid = Craft::$app->getProjectConfig()->get('users.photoVolumeUid');
        $volume = $volumes->getVolumeByUid($volumeUid);

        $subpath = (string)Craft::$app->getProjectConfig()->get('users.photoSubpath');

        if ($subpath) {
            $subpath = Craft::$app->getView()->renderObjectTemplate($subpath, $user);
        }

        return $assetsService->ensureFolderByFullPathAndVolume($subpath, $volume)->id ?? null;
    }
}
