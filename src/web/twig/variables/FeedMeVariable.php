<?php
namespace verbb\feedme\web\twig\variables;

use verbb\feedme\FeedMe;

use Craft;
use craft\elements\User;
use craft\helpers\DateTimeHelper;

use yii\di\ServiceLocator;

use Cake\Utility\Hash;

class FeedMeVariable extends ServiceLocator
{
    public $config;

    public function __construct($config = [])
    {
        $config['components'] = FeedMe::$plugin->getComponents();

        parent::__construct($config);
    }

    public function getPluginName()
    {
        return FeedMe::$plugin->getPluginName();
    }

    public function getSelectOptions($options, $label = 'name', $index = 'id', $includeNone = true)
    {
        $values = [];

        if ($includeNone) {
            $values[''] = 'None';
        }

        if (is_array($options)) {
            foreach ($options as $key => $value) {
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
        return FeedMe::$plugin->data->getFeedForTemplate($options);
    }

    public function feedHeaders($options = [])
    {
        $options['headers'] = true;

        return FeedMe::$plugin->data->getFeedForTemplate($options);
    }


    //
    // Fields + Field Mapping
    //

    public function formatDateTime($dateTime)
    {
        return DateTimeHelper::toDateTime($dateTime);
    }


    //
    // Helper functions for element fields in getting their inner-element field layouts
    //
    public function getAssetLayoutByField($field)
    {
        if (!$field) {
            return;
        }

        if (is_array($field->sources)) {
            $sourceId = str_replace('folder:', '', $field->sources[0]);
        } else if ($field->sources == '*') {
            $sourceId = Craft::$app->volumes->getAllVolumeIds()[0] ?? null;
        }

        $source = Craft::$app->volumes->getVolumeById($sourceId);

        if (!$source) {
            return;
        }

        return Craft::$app->fields->getFieldsByLayoutId($source->fieldLayoutId);
    }

    public function getCategoryLayoutByField($field)
    {
        if (!$field) {
            return;
        }

        if (is_array($field->sources)) {
            $sourceId = str_replace('group:', '', $field->sources[0]);
        } else if ($field->sources == '*') {
            $sourceId = Craft::$app->categories->getAllGroupIds()[0] ?? null;
        }

        $source = Craft::$app->categories->getGroupById($sourceId);

        if (!$source) {
            return;
        }

        return Craft::$app->fields->getFieldsByLayoutId($source->fieldLayoutId);
    }

    public function getEntryLayoutByField($field)
    {
        if (!$field) {
            return;
        }

        if (is_array($field->sources)) {
            $sourceId = str_replace('section:', '', $field->sources[0]);
        } else if ($field->sources == '*') {
            $sourceId = Craft::$app->sections->getAllSectionIds()[0] ?? null;
        }

        $source = Craft::$app->sections->getEntryTypeById($sourceId);

        if (!$source) {
            return;
        }

        return Craft::$app->fields->getFieldsByLayoutId($source->fieldLayoutId);
    }

    public function getTagLayoutByField($field)
    {
        if (!$field) {
            return;
        }

        if (is_array($field->sources)) {
            $sourceId = str_replace('group:', '', $field->sources[0]);
        } else if ($field->sources == '*') {
            $sourceId = Craft::$app->sections->getAllSectionIds()[0] ?? null;
        }

        $source = Craft::$app->sections->getEntryTypeById($sourceId);

        if (!$source) {
            return;
        }

        return Craft::$app->fields->getFieldsByLayoutId($source->fieldLayoutId);
    }

    public function getUserLayoutByField($field)
    {
        $layoutId = Craft::$app->fields->getLayoutByType(UserElement::class)->id;

        if (!$layoutId) {
            return null;
        }

        return Craft::$app->fields->getFieldsByLayoutId($layoutId);
    }

    public function getAssetFolderBySourceId($id)
    {
        $folders = Craft::$app->assets->getFolderTreeByVolumeIds(array($id));

        $return = array();

        $return[''] = Craft::t('feed-me', 'Don\'t Import');

        if (is_array($folders)) {
            foreach ($folders as $folder) {
                $return[] = array(
                    'value' => 'root',
                    'label' => Craft::t('feed-me', 'Root Folder'),
                );

                $children = $folder->getChildren();

                if ($children) {
                    foreach ($children as $childFolder) {
                        $return[] = array(
                            'value' => $childFolder['id'],
                            'label' => $childFolder['name'],
                        );
                    }
                }
            }
        }

        return $return;
    }

}
