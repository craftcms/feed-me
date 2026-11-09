<?php

namespace craft\feedme\fields;

use Cake\Utility\Hash;
use Craft;
use craft\db\Table;
use craft\errors\InvalidSubpathException;
use craft\elements\Asset as AssetElement;
use craft\elements\Category as CategoryElement;
use craft\elements\Entry as EntryElement;
use craft\feedme\base\Field;
use craft\feedme\base\FieldInterface;
use craft\feedme\helpers\AssetHelper;
use craft\feedme\helpers\DataHelper;
use craft\feedme\helpers\FieldHelper;
use craft\feedme\Plugin;
use craft\fields\Link as LinkField;
use craft\fields\linktypes\Asset as AssetLinkType;
use craft\fields\linktypes\BaseElementLinkType;
use craft\helpers\Assets as AssetsHelper;
use craft\helpers\Db;
use craft\helpers\Json;
use craft\helpers\UrlHelper;
use Throwable;

/**
 *
 * @property-read string $mappingTemplate
 */
class Link extends Field implements FieldInterface
{
    // Properties
    // =========================================================================

    /**
     * @var string
     */
    public static string $name = 'Link';

    /**
     * @var string
     */
    public static string $class = LinkField::class;

    // Templates
    // =========================================================================

    /**
     * @inheritDoc
     */
    public function getMappingTemplate(): string
    {
        return 'feed-me/_includes/fields/link';
    }

    // Static Methods
    // =========================================================================

    /**
     * Returns the field's element link types (e.g. Asset, Entry), with the options for matching their elements,
     * and for assets, the volumes new assets can be uploaded to.
     *
     * @param LinkField $field
     * @return array
     */
    public static function getElementLinkTypes(LinkField $field): array
    {
        $linkTypes = [];

        foreach ($field->getLinkTypes() as $typeId => $linkType) {
            if (!$linkType instanceof BaseElementLinkType) {
                continue;
            }

            $elementType = self::_elementType($linkType);

            $matchOptions = [
                ['value' => 'id', 'label' => Craft::t('feed-me', 'ID')],
                ['value' => 'title', 'label' => Craft::t('feed-me', 'Title')],
            ];

            if ($linkType instanceof AssetLinkType) {
                $matchOptions[] = ['value' => 'filename', 'label' => Craft::t('feed-me', 'Filename')];
            } elseif ($elementType::hasUris()) {
                $matchOptions[] = ['value' => 'slug', 'label' => Craft::t('feed-me', 'Slug')];
            }

            foreach (self::_getMatchFields($linkType) as $matchField) {
                $matchOptions[] = ['value' => $matchField->handle, 'label' => $matchField->name];
            }

            $volumeOptions = [];

            if ($linkType instanceof AssetLinkType) {
                foreach (self::_getVolumeIds($linkType) ?? Craft::$app->getVolumes()->getAllVolumeIds() as $volumeId) {
                    $volume = Craft::$app->getVolumes()->getVolumeById($volumeId);

                    if ($volume) {
                        $volumeOptions[] = ['value' => $volume->uid, 'label' => $volume->name];
                    }
                }
            }

            $linkTypes[$typeId] = [
                'name' => $linkType::displayName(),
                'matchOptions' => $matchOptions,
                'volumeOptions' => $volumeOptions,
            ];
        }

        return $linkTypes;
    }

    // Public Methods
    // =========================================================================

    /**
     * @inheritDoc
     */
    public function parseField(): mixed
    {
        $preppedData = [];

        $fields = Hash::get($this->fieldInfo, 'fields');

        if (!$fields) {
            return $this->parseLegacyMapping();
        }

        foreach ($fields as $subFieldHandle => $subFieldInfo) {
            $preppedData[$subFieldHandle] = DataHelper::fetchValue($this->feedData, $subFieldInfo, $this->feed);
        }

        if (empty(
            array_filter($preppedData, function($val) {
                return $val !== null;
            })
        )) {
            return null;
        }

        if (empty($preppedData['type'] ?? '')) {
            $preppedData['type'] = 'url';
        }

        // Protect against sending an empty array
        if (!$preppedData) {
            return null;
        }

        return $this->_parseLinkedElement($preppedData);
    }

    // Private Methods
    // =========================================================================

    /**
     * Parses a flat mapping saved before Link fields had a dedicated parser.
     */
    private function parseLegacyMapping(): ?string
    {
        $value = $this->fetchValue();

        // see DefaultField::parseField() - allow empty strings when setting empty values
        if ($value === '' && $this->feed['setEmptyValues']) {
            return '';
        }

        if ($value === null || $value === '' || !is_scalar($value)) {
            return null;
        }

        // return a string (rather than an array) so that the field can auto-detect the link type
        return (string)$value;
    }

    /**
     * Links to an element (e.g. an asset or entry), if the link's type is mapped to one. The element is matched by
     * the mapped attribute or field, and for assets, can be uploaded if there isn't one. Otherwise, the link falls
     * back to its value as a URL.
     *
     * @param array $preppedData
     * @return array|null
     */
    private function _parseLinkedElement(array $preppedData): ?array
    {
        $typeId = $preppedData['type'];
        $elementInfo = Hash::get($this->fieldInfo, 'elements.' . $typeId);
        $linkType = $this->field?->getLinkTypes()[$typeId] ?? null;

        if (!$elementInfo || !$linkType instanceof BaseElementLinkType) {
            return $preppedData;
        }

        $identifier = DataHelper::fetchValue($this->feedData, $elementInfo, $this->feed);
        $elementName = self::_elementType($linkType)::lowerDisplayName();

        if ($identifier !== null && $identifier !== '' && is_scalar($identifier)) {
            $elementId = $this->_findLinkedElementId($linkType, $identifier, $elementInfo);

            if (!$elementId && $linkType instanceof AssetLinkType && Hash::get($elementInfo, 'options.upload')) {
                $elementId = $this->_uploadLinkedAsset($linkType, $identifier, $elementInfo);
            }

            if ($elementId) {
                $preppedData['value'] = $elementId;

                return $preppedData;
            }
        }

        $value = $preppedData['value'] ?? null;

        if (is_string($value) && UrlHelper::isAbsoluteUrl($value) && isset($this->field->getLinkTypes()['url'])) {
            Plugin::info('`{handle}` - No {type} found for `{identifier}`, linking to `{url}` instead.', [
                'handle' => $this->fieldHandle,
                'type' => $elementName,
                'identifier' => Json::encode($identifier),
                'url' => $value,
            ]);

            $preppedData['type'] = 'url';

            return $preppedData;
        }

        Plugin::info('`{handle}` - No {type} found for `{identifier}`.', [
            'handle' => $this->fieldHandle,
            'type' => $elementName,
            'identifier' => Json::encode($identifier),
        ]);

        return null;
    }

    /**
     * @param BaseElementLinkType $linkType
     * @param mixed $identifier
     * @param array $elementInfo
     * @return int|null
     */
    private function _findLinkedElementId(BaseElementLinkType $linkType, mixed $identifier, array $elementInfo): ?int
    {
        $elementType = self::_elementType($linkType);
        $match = Hash::get($elementInfo, 'options.match') ?: 'id';

        $criteria = [
            'status' => null,
            $match => DataHelper::prepValueForElementMatch($identifier),
        ];

        if ($linkType instanceof AssetLinkType) {
            $criteria['volumeId'] = self::_getVolumeIds($linkType);
        } else {
            $sourceCriteria = self::_getSourceCriteria($linkType);

            if ($sourceCriteria) {
                $criteria += $sourceCriteria;
            }
        }

        if (Craft::$app->getIsMultiSite()) {
            $criteria['siteId'] = Hash::get($this->feed, 'siteId') ?: Craft::$app->getSites()->getCurrentSite()->id;
        }

        $query = $elementType::find();
        Craft::configure($query, $criteria);

        Plugin::info('Search for existing {type} with query `{i}`', ['type' => $elementType::lowerDisplayName(), 'i' => Json::encode($criteria)]);

        $id = $query->ids()[0] ?? null;

        return $id ? (int)$id : null;
    }

    /**
     * Uploads the asset a link points to, and sets the field it's matched by, so later imports find it.
     *
     * @param AssetLinkType $linkType
     * @param mixed $identifier
     * @param array $elementInfo
     * @return int|null
     */
    private function _uploadLinkedAsset(AssetLinkType $linkType, mixed $identifier, array $elementInfo): ?int
    {
        $url = DataHelper::fetchValue($this->feedData, Hash::get($elementInfo, 'url', []), $this->feed);

        if (!is_string($url) || !UrlHelper::isAbsoluteUrl($url)) {
            return null;
        }

        $volumeUid = Hash::get($elementInfo, 'options.volume');
        $volume = $volumeUid ? Craft::$app->getVolumes()->getVolumeByUid($volumeUid) : null;

        // Default to the first volume the link type allows
        if (!$volume) {
            $volumeId = (self::_getVolumeIds($linkType) ?? Craft::$app->getVolumes()->getAllVolumeIds())[0] ?? null;
            $volume = $volumeId ? Craft::$app->getVolumes()->getVolumeById($volumeId) : null;
        }

        // The folder path can be dynamic like an Assets field's upload location (e.g. `{slug}`), rendered against
        // the element being imported. The folder is created if it doesn't exist.
        $folder = null;

        if ($volume) {
            try {
                [$folderPath, $folder] = AssetsHelper::resolveSubpath($volume, trim((string)Hash::get($elementInfo, 'options.folderPath'), '/'), $this->element);
                $folder ??= Craft::$app->getAssets()->ensureFolderByFullPathAndVolume($folderPath, $volume);
            } catch (InvalidSubpathException $e) {
                Plugin::error('`{handle}` - Invalid folder path `{path}`.', ['handle' => $this->fieldHandle, 'path' => $e->subpath]);
            }
        }

        if (!$folder) {
            Plugin::error('`{handle}` - No folder to upload `{url}` to.', ['handle' => $this->fieldHandle, 'url' => $url]);

            return null;
        }

        $fieldInfo = ['options' => ['conflict' => Hash::get($elementInfo, 'options.conflict') ?: AssetElement::SCENARIO_INDEX]];
        $assetIds = AssetHelper::fetchRemoteImage([$url], $fieldInfo, $this->feed, null, $this->element, $folder->id);
        $assetId = $assetIds[0] ?? null;

        if (!$assetId) {
            return null;
        }

        $match = Hash::get($elementInfo, 'options.match') ?: 'id';
        $asset = AssetElement::find()->id($assetId)->status(null)->one();

        // Set the field the asset is matched by, so it's found next time
        if ($asset && $asset->getFieldLayout()?->getFieldByHandle($match)) {
            try {
                $asset->setFieldValue($match, $identifier);
                Craft::$app->getElements()->saveElement($asset, true, true, Hash::get($this->feed, 'updateSearchIndexes'));
            } catch (Throwable $e) {
                Plugin::error('`{handle}` - Unable to set `{match}` on asset `#{id}`: `{e}`.', [
                    'handle' => $this->fieldHandle,
                    'match' => $match,
                    'id' => $assetId,
                    'e' => $e->getMessage(),
                ]);
            }
        }

        return (int)$assetId;
    }

    /**
     * Returns the element type a link type links to.
     *
     * @param BaseElementLinkType $linkType
     * @return string
     */
    private static function _elementType(BaseElementLinkType $linkType): string
    {
        // elementType() is protected
        return (fn() => static::elementType())->call($linkType);
    }

    /**
     * Returns the IDs of the volumes an Asset link type allows, or `null` if it allows them all.
     *
     * @param AssetLinkType $linkType
     * @return int[]|null
     */
    private static function _getVolumeIds(AssetLinkType $linkType): ?array
    {
        $criteria = self::_getSourceCriteria($linkType);

        return $criteria['volumeId'] ?? null;
    }

    /**
     * Returns the element query criteria for the sources a link type allows (e.g. `volume:{uid}`, `section:{uid}`),
     * or an empty array if it allows them all.
     *
     * @param BaseElementLinkType $linkType
     * @return array
     */
    private static function _getSourceCriteria(BaseElementLinkType $linkType): array
    {
        $sources = $linkType->sources;

        if (!is_array($sources)) {
            return [];
        }

        $tables = [
            'volume' => [Table::VOLUMES, 'volumeId'],
            'section' => [Table::SECTIONS, 'sectionId'],
            'group' => [Table::CATEGORYGROUPS, 'groupId'],
        ];

        $criteria = [];

        foreach ($sources as $source) {
            [$kind, $uid] = array_pad(explode(':', $source, 2), 2, null);

            if (!isset($tables[$kind]) || !$uid) {
                // A source we can't narrow down by (e.g. singles), so allow them all
                return [];
            }

            [$table, $param] = $tables[$kind];
            $id = Db::idByUid($table, $uid);

            if ($id) {
                $criteria[$param][] = $id;
            }
        }

        return $criteria;
    }

    /**
     * Returns the custom fields that elements can be matched by: those in the field layouts of the sources the
     * link type allows, which can be used as a unique identifier.
     *
     * @param BaseElementLinkType $linkType
     * @return array
     */
    private static function _getMatchFields(BaseElementLinkType $linkType): array
    {
        $criteria = self::_getSourceCriteria($linkType);
        $elementType = self::_elementType($linkType);
        $layouts = [];

        if ($linkType instanceof AssetLinkType) {
            foreach ($criteria['volumeId'] ?? Craft::$app->getVolumes()->getAllVolumeIds() as $volumeId) {
                $layouts[] = Craft::$app->getVolumes()->getVolumeById($volumeId)?->getFieldLayout();
            }
        } elseif ($elementType === EntryElement::class) {
            // Only entries in sections can be linked to, not nested entries
            $sections = isset($criteria['sectionId'])
                ? array_map(fn(int $sectionId) => Craft::$app->getEntries()->getSectionById($sectionId), $criteria['sectionId'])
                : Craft::$app->getEntries()->getAllSections();

            foreach (array_filter($sections) as $section) {
                foreach ($section->getEntryTypes() as $entryType) {
                    $layouts[] = $entryType->getFieldLayout();
                }
            }
        } elseif ($elementType === CategoryElement::class) {
            foreach ($criteria['groupId'] ?? Craft::$app->getCategories()->getAllGroupIds() as $groupId) {
                $layouts[] = Craft::$app->getCategories()->getGroupById($groupId)?->getFieldLayout();
            }
        } else {
            $layouts = Craft::$app->getFields()->getLayoutsByType($elementType);
        }

        $fields = [];

        foreach (array_filter($layouts) as $layout) {
            foreach ($layout->getCustomFields() as $field) {
                if (FieldHelper::fieldCanBeUniqueId($field)) {
                    $fields[$field->handle] = $field;
                }
            }
        }

        return array_values($fields);
    }
}
