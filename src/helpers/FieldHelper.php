<?php

namespace craft\feedme\helpers;

use Craft;
use craft\commerce\Plugin as CommercePlugin;
use craft\elements\User as UserElement;
use craft\fields\Checkboxes;
use craft\fields\Color;
use craft\fields\Date;
use craft\fields\Dropdown;
use craft\fields\Email;
use craft\fields\Lightswitch;
use craft\fields\MultiSelect;
use craft\fields\Number;
use craft\fields\PlainText;
use craft\fields\RadioButtons;
use craft\helpers\ArrayHelper;
use craft\models\CategoryGroup;
use craft\models\Section;
use craft\models\TagGroup;
use Illuminate\Support\Collection;

class FieldHelper
{
    // Public Methods
    // =========================================================================

    /**
     * Returns the class name Feed Me knows a field or field layout element type by.
     *
     * On Craft 6, `craftcms/yii2-adapter` provides Craft 5 class names as aliases of native classes, or, for
     * field types, as `craft\fields\*` subclasses of the native classes the fields actually use. This resolves
     * both to the same name, so either can be compared with the names Feed Me registers its handlers for.
     *
     * @param string|object|null $field The field, field layout element, or class name
     * @return string|null
     */
    public static function fieldClass(string|object|null $field): ?string
    {
        $class = is_object($field) ? get_class($field) : $field;

        // Field types are namespaced; leave other values, such as `attribute`, as they are
        if (!is_string($class) || !str_contains($class, '\\') || !class_exists($class)) {
            return $class;
        }

        // Resolve class aliases
        $class = (new \ReflectionClass($class))->getName();

        if (str_starts_with($class, 'CraftCms\\Cms\\Field\\')) {
            $legacyClass = 'craft\\fields\\' . substr($class, strlen('CraftCms\\Cms\\Field\\'));

            if (class_exists($legacyClass) && is_subclass_of($legacyClass, $class)) {
                return $legacyClass;
            }
        }

        return $class;
    }

    //
    // Helper functions for element fields to get their first source. This is tricky as some elements
    // support multiple sources (Entries, Users), whilst others can only have one (Tags, Categories)
    //

    public static function getAssetSourcesByField($field): ?array
    {
        $sources = [];

        if (!$field) {
            return null;
        }

        if (is_array($field->sources)) {
            foreach ($field->sources as $source) {
                [, $uid] = explode(':', $source);

                $sources[] = Craft::$app->volumes->getVolumeByUid($uid);
            }
        } elseif ($field->sources === '*') {
            $sources = Craft::$app->volumes->getAllVolumes();
        }

        return $sources;
    }

    public static function getCategorySourcesByField($field): ?CategoryGroup
    {
        if (!$field) {
            return null;
        }

        [, $uid] = explode(':', $field->source);

        return Craft::$app->categories->getGroupByUid($uid);
    }

    public static function getEntrySourcesByField($field): ?array
    {
        $sources = [];

        if (!$field) {
            return null;
        }

        if (is_array($field->sources)) {
            foreach ($field->sources as $source) {
                if ($source == 'singles') {
                    foreach (Craft::$app->getEntries()->getAllSections() as $section) {
                        if ($section->type == 'single') {
                            $sources[] = $section;
                        }
                    }
                } else {
                    [, $uid] = explode(':', $source);

                    $section = Craft::$app->getEntries()->getSectionByUid($uid);
                    // only add to sources, if this was a section that we were able to retrieve (native section's uid)
                    // https://github.com/craftcms/feed-me/issues/1186
                    if ($section) {
                        $sources[] = $section;
                    }
                }
            }
        } elseif ($field->sources === '*') {
            $sources = Craft::$app->getEntries()->getAllSections();
        }

        return $sources;
    }

    public static function getTagSourcesByField($field): ?TagGroup
    {
        if (!$field) {
            return null;
        }

        [, $uid] = explode(':', $field->source);

        return Craft::$app->tags->getTagGroupByUid($uid);
    }

    public static function getCommerceProductSourcesByField($field): ?array
    {
        $sources = [];

        if (!$field) {
            return null;
        }

        if (is_array($field->sources)) {
            foreach ($field->sources as $source) {
                [, $uid] = explode(':', $source);

                /** @phpstan-ignore-next-line */
                $productType = CommercePlugin::getInstance()->getProductTypes()->getProductTypeByUid($uid);
                // only add to product types, if this was a section that we were able to retrieve (native section's uid)
                // https://github.com/craftcms/feed-me/issues/1186
                if ($productType) {
                    $sources[] = $productType;
                }
            }
        } elseif ($field->sources === '*') {
            /** @phpstan-ignore-next-line */
            $sources = CommercePlugin::getInstance()->getProductTypes()->getAllProductTypes();
        }

        return $sources;
    }

    //
    // Helper functions for element fields in getting their inner-element field layouts
    //

    public static function getElementLayoutByField($type, $field): ?array
    {
        $source = null;
        $type = static::fieldClass($type);

        if ($type === 'craft\fields\Assets') {
            $source = static::getAssetSourcesByField($field)[0] ?? null;
        } elseif ($type === 'craft\fields\Categories') {
            $source = static::getCategorySourcesByField($field);
        } elseif ($type === 'craft\fields\Entries') {
            /** @var Section $section */
            $section = static::getEntrySourcesByField($field)[0] ?? null;

            if ($section) {
                $source = $section->getEntryTypes()[0] ?? null;
            }
        } elseif ($type === 'craft\fields\Tags') {
            $source = static::getTagSourcesByField($field);
        } elseif ($type === 'craft\commerce\fields\Products' || $type === 'craft\commerce\fields\Variants') {
            $source = static::getCommerceProductSourcesByField($field)[0] ?? null;
        }

        $fieldLayoutParam = 'fieldLayoutId';
        if ($type === 'craft\commerce\fields\Variants') {
            $fieldLayoutParam = 'variantFieldLayoutId';
        }

        if (!$source || !$source->$fieldLayoutParam) {
            return null;
        }

        if (($fieldLayout = Craft::$app->getFields()->getLayoutById($source->$fieldLayoutParam)) !== null) {
            return ArrayHelper::merge($fieldLayout->getCustomFields(), $fieldLayout->getAvailableNativeFields());
        }

        return null;
    }

    public static function getUserLayoutByField(): ?array
    {
        $layoutId = Craft::$app->getFields()->getLayoutByType(UserElement::class)->id;

        if (!$layoutId) {
            return null;
        }

        if (($fieldLayout = Craft::$app->getFields()->getLayoutById($layoutId)) !== null) {
            return $fieldLayout->getCustomFields();
        }

        return null;
    }

    public static function getAssetFolderBySourceId($id): array
    {
        $folders = Craft::$app->getAssets()->getFolderTreeByVolumeIds([$id]);

        $return = [];

        $return[''] = Craft::t('feed-me', 'Don\'t Import');

        foreach ($folders as $folder) {
            $return[] = [
                'value' => 'root',
                'label' => Craft::t('feed-me', 'Root Folder'),
            ];

            $children = $folder->getChildren();

            if ($children) {
                foreach ($children as $childFolder) {
                    $return[] = [
                        'value' => $childFolder['id'],
                        'label' => $childFolder['name'],
                    ];
                }
            }
        }

        return $return;
    }

    /**
     * Returns if the field can be used as a unique identifier.
     *
     * @param $field
     * @return bool
     */
    public static function fieldCanBeUniqueId($field): bool
    {
        if (is_object($field)) {
            $type = static::fieldClass($field);
        } else {
            try {
                $type = static::fieldClass($field['type'] ?? 'attribute');
            } catch (\Throwable $e) {
                return false;
            }

            if (isset($field['type']) && $field['handle'] === 'parent') {
                $type = 'parent';
            }
        }

        $supportedFields = [
            Checkboxes::class,
            Color::class,
            Date::class,
            Dropdown::class,
            Email::class,
            Lightswitch::class,
            MultiSelect::class,
            Number::class,
            PlainText::class,
            RadioButtons::class,
        ];

        $supportedValues = [
            'assets',
            'attribute',
            'parent',
        ];

        $supported = array_merge($supportedFields, $supportedValues);

        if (in_array($type, $supported, true)) {
            return true;
        }

        // Include any field types that extend one of the above
        foreach ($supportedFields as $supportedField) {
            if (is_a($type, $supportedField, true)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Check if the only sources set for a relation field are custom ones.
     *
     * @param mixed $field
     * @return bool
     */
    public static function fieldHasOnlyCustomSources(mixed $field = null): bool
    {
        if ($field === null) {
            return false;
        }

        if (!isset($field['sources']) && !isset($field['source'])) {
            return false;
        }

        if (empty($field['source'])) {
            $fieldSources = $field['sources'];
        } else {
            $fieldSources = $field['source'];
        }

        $sources = new Collection($fieldSources);
        $nativeSources = $sources->filter(fn(string $source) => !str_starts_with($source, 'custom:'));

        return $nativeSources->isEmpty();
    }

    /**
     * Returns an array of all the custom fields that can be used as a unique id.
     *
     * @return array
     */
    public static function getAllUniqueIdFields(): array
    {
        return array_filter(
            Craft::$app->getFields()->getAllFields(),
            fn($field) => static::fieldCanBeUniqueId($field)
        );
    }
}
