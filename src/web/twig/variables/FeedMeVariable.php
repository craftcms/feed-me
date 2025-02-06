<?php

namespace craft\feedme\web\twig\variables;

use Craft;
use craft\feedme\helpers\FieldHelper;
use craft\feedme\models\FeedModel;
use craft\feedme\Plugin;
use craft\fields\BaseRelationField;
use craft\fields\Categories;
use craft\fields\Checkboxes;
use craft\fields\Color;
use craft\fields\Country;
use craft\fields\Date;
use craft\fields\Dropdown;
use craft\fields\Email;
use craft\fields\Entries;
use craft\fields\Icon;
use craft\fields\Lightswitch;
use craft\fields\Money;
use craft\fields\MultiSelect;
use craft\fields\Number;
use craft\fields\PlainText;
use craft\fields\RadioButtons;
use craft\fields\Time;
use craft\fields\Url;
use craft\fields\Users;
use craft\helpers\DateTimeHelper;
use craft\helpers\Html;
use craft\helpers\UrlHelper;
use craft\models\CategoryGroup;
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
            'utilities' => ['label' => Craft::t('feed-me', 'Utilities'), 'url' => UrlHelper::cpUrl('feed-me/utilities')],
        ];

        if (Craft::$app->getUser()->getIsAdmin()) {
            $tabs['settings'] = ['label' => Craft::t('feed-me', 'Settings'), 'url' => UrlHelper::cpUrl('feed-me/settings')];
        }

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

                $values[$value[$index]] = Html::encode($value[$label]);
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
        return FieldHelper::getAssetSourcesByField($field);
    }

    public function getCategorySourcesByField($field): ?CategoryGroup
    {
        return FieldHelper::getCategorySourcesByField($field);
    }

    public function getEntrySourcesByField($field): ?array
    {
        return FieldHelper::getEntrySourcesByField($field);
    }

    public function getTagSourcesByField($field): ?TagGroup
    {
        return FieldHelper::getTagSourcesByField($field);
    }


    //
    // Helper functions for element fields in getting their inner-element field layouts
    //

    public function getElementLayoutByField($type, $field): ?array
    {
        return FieldHelper::getElementLayoutByField($type, $field);
    }

    public function getUserLayoutByField(): ?array
    {
        return FieldHelper::getUserLayoutByField();
    }

    public function getAssetFolderBySourceId($id): array
    {
        return FieldHelper::getAssetFolderBySourceId($id);
    }

    public function fieldCanBeUniqueId($field): bool
    {
        return FieldHelper::fieldCanBeUniqueId($field);
    }

    public function supportedSubField($class): bool
    {
        $supportedSubFields = [
            Checkboxes::class,
            Color::class,
            Country::class,
            Date::class,
            Dropdown::class,
            Email::class,
            Icon::class,
            Lightswitch::class,
            Money::class,
            MultiSelect::class,
            Number::class,
            PlainText::class,
            RadioButtons::class,
            Time::class,
            Url::class,
            'craft\ckeditor\Field',
            'craft\redactor\Field',
        ];

        return in_array($class, $supportedSubFields, true);
    }

    /**
     * Check if the only sources set for a relation field are custom ones.
     *
     * @param mixed $field
     * @return bool
     */
    public function fieldHasOnlyCustomSources(mixed $field = null): bool
    {
        return FieldHelper::fieldHasOnlyCustomSources($field);
    }

    /**
     * Return an array of custom field by which the relation field elements can be matched.
     *
     * @param string $className
     * @param BaseRelationField|null $field
     * @return array
     */
    public function getRelationFieldMatchOptions(string $className, FeedModel $feed, ?BaseRelationField $field = null): array
    {
        $allowedFields = [];
        $matchAttributes = [];

        $feedMeField = match ($className) {
            Categories::class => \craft\feedme\fields\Categories::class,
            Entries::class => \craft\feedme\fields\Entries::class,
            Users::class => \craft\feedme\fields\Users::class,
            default => null,
        };

        if ($feedMeField !== null) {
            $allowedFields = $feedMeField::getMatchFields($feed, $field);
        }

        foreach ($allowedFields as $allowedField) {
            $matchAttributes[$allowedField->handle] = $allowedField->name;
        }

        return $matchAttributes;
    }
}
