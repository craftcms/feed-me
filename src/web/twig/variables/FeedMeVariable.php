<?php

namespace craft\feedme\web\twig\variables;

use Craft;
use craft\feedme\Plugin;
use craft\helpers\DateTimeHelper;
use craft\helpers\UrlHelper;
use craft\models\Section;
use yii\di\ServiceLocator;

/**
 *
 * @property-read mixed $pluginName
 * @property-read array[]|array $tabs
 */
class FeedMeVariable extends ServiceLocator
{
    public $config;

    public function __construct($config = [])
    {
        $config['components'] = Plugin::$plugin->getComponents();

        parent::__construct($config);
    }

    public function getPluginName()
    {
        return Plugin::$plugin->getPluginName();
    }

    public function getTabs()
    {
        $settings = Plugin::$plugin->getSettings();
        $enabledTabs = $settings->enabledTabs;

        $tabs = [
            'feeds' => ['label' => Craft::t('feed-me', 'Feeds'), 'url' => UrlHelper::cpUrl('feed-me/feeds')],
            'logs' => ['label' => Craft::t('feed-me', 'Logs'), 'url' => UrlHelper::cpUrl('feed-me/logs')],
            'settings' => ['label' => Craft::t('feed-me', 'Settings'), 'url' => UrlHelper::cpUrl('feed-me/settings')],
        ];

        if ($enabledTabs === '*' || $enabledTabs === 1 || !is_array($enabledTabs)) {
            return $tabs;
        }

        if (!$enabledTabs) {
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

    public function getSelectOptions($options, $label = 'name', $index = 'id', $includeNone = true)
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
            foreach ($options as $key => $value) {
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

    public function formatDateTime($dateTime)
    {
        return DateTimeHelper::toDateTime($dateTime);
    }


    //
    // Helper functions for element fields to get their first source. This is tricky as some elements
    // support multiple sources (Entries, Users), whilst others can only have one (Tags, Categories)
    //

    public function getAssetSourcesByField($field)
    {
        $sources = [];

        if (!$field) {
            return;
        }

        if (is_array($field->sources)) {
            foreach ($field->sources as $source) {
                list(, $uid) = explode(':', $source);

                $sources[] = Craft::$app->volumes->getVolumeByUid($uid);
            }
        } elseif ($field->sources === '*') {
            $sources = Craft::$app->volumes->getAllVolumes();
        }

        return $sources;
    }

    public function getCategorySourcesByField($field)
    {
        if (!$field) {
            return;
        }

        list(, $uid) = explode(':', $field->source);

        return Craft::$app->categories->getGroupByUid($uid);
    }

    public function getEntrySourcesByField($field)
    {
        $sources = [];

        if (!$field) {
            return;
        }

        if (is_array($field->sources)) {
            foreach ($field->sources as $source) {
                if ($source == 'singles') {
                    foreach (Craft::$app->sections->getAllSections() as $section) {
                        if ($section->type == 'single') {
                            $sources[] = $section;
                        }
                    }
                } else {
                    list(, $uid) = explode(':', $source);

                    $sources[] = Craft::$app->sections->getSectionByUid($uid);
                }
            }
        } elseif ($field->sources === '*') {
            $sources = Craft::$app->sections->getAllSections();
        }

        return $sources;
    }

    public function getTagSourcesByField($field)
    {
        if (!$field) {
            return;
        }

        list(, $uid) = explode(':', $field->source);

        return Craft::$app->tags->getTagGroupByUid($uid);
    }


    //
    // Helper functions for element fields in getting their inner-element field layouts
    //

    public function getElementLayoutByField($type, $field)
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
                $source = $section->getEntryTypes()[0] ?? null;
            }
        } elseif ($type === 'craft\fields\Tags') {
            $source = $this->getTagSourcesByField($field);
        }

        if (!$source || !$source->fieldLayoutId) {
            return;
        }

        return Craft::$app->fields->getFieldsByLayoutId($source->fieldLayoutId);
    }

    public function getUserLayoutByField()
    {
        $layoutId = Craft::$app->fields->getLayoutByType(UserElement::class)->id;

        if (!$layoutId) {
            return null;
        }

        return Craft::$app->fields->getFieldsByLayoutId($layoutId);
    }

    public function getAssetFolderBySourceId($id)
    {
        $folders = Craft::$app->assets->getFolderTreeByVolumeIds([$id]);

        $return = [];

        $return[''] = Craft::t('feed-me', 'Don\'t Import');

        if (is_array($folders)) {
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
        }

        return $return;
    }

    public function fieldCanBeUniqueId($field)
    {
        $type = $field['type'] ?? 'attribute';

        if (is_object($field)) {
            $type = get_class($field);
        }

        $supportedFields = [
            'craft\fields\Checkboxes',
            'craft\fields\Color',
            'craft\fields\Date',
            'craft\fields\Dropdown',
            'craft\fields\Email',
            'craft\fields\Lightswitch',
            'craft\fields\MultiSelect',
            'craft\fields\Number',
            'craft\fields\PlainText',
            'craft\fields\RadioButtons',
            'craft\fields\Url',
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

    public function supportedSubField($class)
    {
        $supportedSubFields = [
            'craft\fields\Checkboxes',
            'craft\fields\Color',
            'craft\fields\Date',
            'craft\fields\Dropdown',
            'craft\fields\Lightswitch',
            'craft\fields\Multiselect',
            'craft\fields\Number',
            'craft\fields\PlainText',
            'craft\fields\PositionSelect',
            'craft\fields\Radio',
            'craft\fields\Redactor',
        ];

        return in_array($class, $supportedSubFields, true);
    }
}
