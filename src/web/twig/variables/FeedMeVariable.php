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
                list($type, $id) = explode(':', $source);

                $sources[] = Craft::$app->volumes->getVolumeById($id);
            }
        } else if ($field->sources === '*') {
            $sources = Craft::$app->volumes->getAllVolumes();
        }
        
        return $sources;
    }

    public function getCategorySourcesByField($field)
    {
        if (!$field) {
            return;
        }

        list($type, $id) = explode(':', $field->source);

        return Craft::$app->categories->getGroupById($id);
    }

    public function getEntrySourcesByField($field)
    {
        $sources = [];

        if (!$field) {
            return;
        }

        if (is_array($field->sources)) {
            foreach ($field->sources as $source) {
                list($type, $id) = explode(':', $source);

                $sources[] = Craft::$app->sections->getSectionById($id);
            }
        } else if ($field->sources === '*') {
            $sources = Craft::$app->sections->getAllSections();
        }

        return $sources;
    }

    public function getTagSourcesByField($field)
    {
        if (!$field) {
            return;
        }

        list($type, $id) = explode(':', $field->source);

        return Craft::$app->tags->getTagGroupById($id);
    }


    //
    // Helper functions for element fields in getting their inner-element field layouts
    //

    public function getElementLayoutByField($type, $field)
    {
        $source = null;

        if ($type === 'craft\fields\Assets') {
            $source = $this->getAssetSourcesByField($field)[0] ?? null;
        } else if ($type === 'craft\fields\Categories') {
            $source = $this->getCategorySourcesByField($field) ?? null;
        } else if ($type === 'craft\fields\Entries') {
            $section = $this->getEntrySourcesByField($field)[0] ?? null;
            $source = Craft::$app->sections->getEntryTypeById($section->id);
        } else if ($type === 'craft\fields\Tags') {
            $source = $this->getCategorySourcesByField($field) ?? null;
        }

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
