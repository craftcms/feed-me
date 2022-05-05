<?php

namespace craft\feedme\web\twig\variables;

use Craft;
use craft\elements\User as UserElement;
use craft\feedme\Plugin;
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
use craft\fields\Url;
use craft\helpers\DateTimeHelper;
use craft\helpers\UrlHelper;
use craft\models\CategoryGroup;
use craft\models\Section;
use craft\models\TagGroup;
use DateTime;
use yii\di\ServiceLocator;

/**
 *
 * @property-read mixed $pluginName
 * @property-read array[]|array $tabs
 */
class FeedMeVariable extends ServiceLocator
{
    public mixed $config = null;

    public function __construct($config = [])
    {
        $config['components'] = Plugin::$plugin->getComponents();

        parent::__construct($config);
    }

    public function getPluginName(): string
    {
        return Plugin::$plugin->getPluginName();
    }

    public function getTabs(): array
    {
        $settings = Plugin::$plugin->getSettings();
        $enabledTabs = $settings->enabledTabs;

        $tabs = [
            'feeds' => ['label' => Craft::t('feed-me', 'Feeds'), 'url' => UrlHelper::cpUrl('feed-me/feeds')],
            'logs' => ['label' => Craft::t('feed-me', 'Logs'), 'url' => UrlHelper::cpUrl('feed-me/logs')],
            'settings' => ['label' => Craft::t('feed-me', 'Settings'), 'url' => UrlHelper::cpUrl('feed-me/settings')],
        ];

        if (!is_array($enabledTabs)) {
            return $tabs;
        }

        if (empty($enabledTabs)) {
            return [];
        }

        $selectedTabs = [];

        foreach ($enabledTabs as $enabledTab) {
            if (isset($tabs[$enabledTab])) {
                $selectedTabs[$enabledTab] = $tabs[$enabledTab];
            }
        }

        return $selectedTabs;
    }

    public function getSelectOptions($options, $label = 'name', $index = 'id', $includeNone = true): array
    {
        $values = [];

        if ($includeNone) {
            if (is_string($includeNone)) {
                $values[''] = $includeNone;
            } else {
                $values[''] = 'None';
            }
        }

        if (is_array($options)) {
            foreach ($options as $value) {
                if (isset($value['optgroup'])) {
                    continue;
                }

                $values[$value[$index]] = $value[$label];
            }
        }

        return $values;
    }


    //
    // Main template tag
    //

    public function feed($options = [])
    {
        return Plugin::$plugin->data->getFeedForTemplate($options);
    }

    public function feedHeaders($options = [])
    {
        $options['headers'] = true;

        return Plugin::$plugin->data->getFeedForTemplate($options);
    }


    //
    // Fields + Field Mapping
    //

    public function formatDateTime($dateTime): DateTime|bool
    {
        return DateTimeHelper::toDateTime($dateTime);
    }


    //
    // Helper functions for element fields to get their first source. This is tricky as some elements
    // support multiple sources (Entries, Users), whilst others can only have one (Tags, Categories)
    //

    public function getAssetSourcesByField($field): ?array
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

    public function getCategorySourcesByField($field): ?CategoryGroup
    {
        if (!$field) {
            return null;
        }

        [, $uid] = explode(':', $field->source);

        return Craft::$app->categories->getGroupByUid($uid);
    }

    public function getEntrySourcesByField($field): ?array
    {
        $sources = [];

        if (!$field) {
            return null;
        }

        if (is_array($field->sources)) {
            foreach ($field->sources as $source) {
                if ($source == 'singles') {
                    foreach (Craft::$app->getSections()->getAllSections() as $section) {
                        if ($section->type == 'single') {
                            $sources[] = $section;
                        }
                    }
                } else {
                    [, $uid] = explode(':', $source);

                    $sources[] = Craft::$app->getSections()->getSectionByUid($uid);
                }
            }
        } elseif ($field->sources === '*') {
            $sources = Craft::$app->getSections()->getAllSections();
        }

        return $sources;
    }

    public function getTagSourcesByField($field): ?TagGroup
    {
        if (!$field) {
            return null;
        }

        [, $uid] = explode(':', $field->source);

        return Craft::$app->tags->getTagGroupByUid($uid);
    }


    //
    // Helper functions for element fields in getting their inner-element field layouts
    //

    public function getElementLayoutByField($type, $field): ?array
    {
        $source = null;

        if ($type === 'craft\fields\Assets') {
            $source = $this->getAssetSourcesByField($field)[0] ?? null;
        } elseif ($type === 'craft\fields\Categories') {
            $source = $this->getCategorySourcesByField($field);
        } elseif ($type === 'craft\fields\Entries') {
            /** @var Section $section */
            $section = $this->getEntrySourcesByField($field)[0] ?? null;

            if ($section) {
                $source = Craft::$app->getSections()->getEntryTypeById($section->id);
            }
        } elseif ($type === 'craft\fields\Tags') {
            $source = $this->getTagSourcesByField($field);
        }

        if (!$source || !$source->fieldLayoutId) {
            return null;
        }

        if (($fieldLayout = Craft::$app->getFields()->getLayoutById($source->fieldLayoutId)) === null) {
            return $fieldLayout->getCustomFields();
        }

        return null;
    }

    public function getUserLayoutByField(): ?array
    {
        $layoutId = Craft::$app->getFields()->getLayoutByType(UserElement::class)->id;

        if (!$layoutId) {
            return null;
        }

        if (($fieldLayout = Craft::$app->getFields()->getLayoutById($layoutId)) === null) {
            return $fieldLayout->getCustomFields();
        }

        return null;
    }

    public function getAssetFolderBySourceId($id): array
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

    public function fieldCanBeUniqueId($field): bool
    {
        $type = $field['type'] ?? 'attribute';

        if (is_object($field)) {
            $type = get_class($field);
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
            Url::class,
        ];

        $supportedValues = [
            'assets',
            'attribute',
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

    public function supportedSubField($class): bool
    {
        $supportedSubFields = [
            Checkboxes::class,
            Color::class,
            Date::class,
            Dropdown::class,
            Lightswitch::class,
            MultiSelect::class,
            Number::class,
            PlainText::class,
            RadioButtons::class,
        ];

        return in_array($class, $supportedSubFields, true);
    }
}
