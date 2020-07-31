<?php

namespace craft\feedme\services;

use Cake\Utility\Hash;
use Craft;
use craft\base\Component;
use craft\feedme\base\FieldInterface;
use craft\feedme\events\FieldEvent;
use craft\feedme\events\RegisterFeedMeFieldsEvent;
use craft\feedme\fields\Assets;
use craft\feedme\fields\CalendarEvents;
use craft\feedme\fields\Categories;
use craft\feedme\fields\Checkboxes;
use craft\feedme\fields\CommerceProducts;
use craft\feedme\fields\CommerceVariants;
use craft\feedme\fields\Date;
use craft\feedme\fields\DefaultField;
use craft\feedme\fields\DigitalProducts;
use craft\feedme\fields\Dropdown;
use craft\feedme\fields\Entries;
use craft\feedme\fields\EntriesSubset;
use craft\feedme\fields\Lightswitch;
use craft\feedme\fields\Linkit;
use craft\feedme\fields\Matrix;
use craft\feedme\fields\MultiSelect;
use craft\feedme\fields\Number;
use craft\feedme\fields\RadioButtons;
use craft\feedme\fields\SimpleMap;
use craft\feedme\fields\SmartMap;
use craft\feedme\fields\SuperTable;
use craft\feedme\fields\Table;
use craft\feedme\fields\Tags;
use craft\feedme\fields\TypedLink;
use craft\feedme\fields\Users;
use craft\helpers\Component as ComponentHelper;

use ilkino\cinemaprogram\fields\VersionTags;

class Fields extends Component
{
    // Constants
    // =========================================================================

    const EVENT_REGISTER_FEED_ME_FIELDS = 'registerFeedMeFields';
    const EVENT_BEFORE_PARSE_FIELD = 'onBeforeParseField';
    const EVENT_AFTER_PARSE_FIELD = 'onAfterParseField';


    // Properties
    // =========================================================================

    private $_fields = [];


    // Public Methods
    // =========================================================================

    public function init()
    {
        parent::init();

        foreach ($this->getRegisteredFields() as $fieldClass) {
            $field = $this->createField($fieldClass);

            // Does this field exist in Craft right now?
            if (!class_exists($field::$class)) {
                continue;
            }

            $handle = $field::$class;

            $this->_fields[$handle] = $field;
        }
    }

    public function getRegisteredField($handle)
    {
        if (isset($this->_fields[$handle])) {
            return $this->_fields[$handle];
        } else {
            return $this->createField(DefaultField::class);
        }
    }

    public function fieldsList()
    {
        $list = [];

        foreach ($this->_fields as $handle => $field) {
            $list[$handle] = $field::$name;
        }

        return $list;
    }

    public function getRegisteredFields()
    {
        if (count($this->_fields)) {
            return $this->_fields;
        }

        $event = new RegisterFeedMeFieldsEvent([
            'fields' => [
                Assets::class,
                Categories::class,
                Checkboxes::class,
                CommerceProducts::class,
                CommerceVariants::class,
                Date::class,
                Dropdown::class,
                Entries::class,
                Lightswitch::class,
                Matrix::class,
                MultiSelect::class,
                Number::class,
                RadioButtons::class,
                Table::class,
                Tags::class,
                Users::class,

                // Third-Party
                CalendarEvents::class,
                DigitalProducts::class,
                EntriesSubset::class,
                Linkit::class,
                SimpleMap::class,
                SmartMap::class,
                SuperTable::class,
                TypedLink::class,

                // Custom
                VersionTags::class,
            ],
        ]);

        $this->trigger(self::EVENT_REGISTER_FEED_ME_FIELDS, $event);

        return $event->fields;
    }

    public function createField($config)
    {
        if (is_string($config)) {
            $config = ['type' => $config];
        }

        try {
            $field = ComponentHelper::createComponent($config, FieldInterface::class);
        } catch (MissingComponentException $e) {
            $config['errorMessage'] = $e->getMessage();
            $config['expectedType'] = $config['type'];
            unset($config['type']);

            $field = new MissingDataType($config);
        }

        return $field;
    }

    public function parseField($feed, $element, $feedData, $fieldHandle, $fieldInfo)
    {
        if ($this->hasEventHandlers(self::EVENT_BEFORE_PARSE_FIELD)) {
            $this->trigger(self::EVENT_BEFORE_PARSE_FIELD, new FieldEvent([
                'feedData' => $feedData,
                'fieldHandle' => $fieldHandle,
                'fieldInfo' => $fieldInfo,
                'element' => $element,
                'feed' => $feed,
            ]));
        }

        $parsedValue = null;

        $fieldClassHandle = Hash::get($fieldInfo, 'field');

        // Find the class to deal with the attribute
        $class = $this->getRegisteredField($fieldClassHandle);
        $class->feedData = $feedData;
        $class->fieldHandle = $fieldHandle;
        $class->fieldInfo = $fieldInfo;
        $class->field = Craft::$app->fields->getFieldByHandle($fieldHandle);
        $class->element = $element;
        $class->feed = $feed;

        // Get that sweet data
        $parsedValue = $class->parseField();

        // We don't really want to set an empty array on fields, which is dangerous for existing date (elements)
        // But empty strings and booleans are totally fine, and desirable.
        // if (is_array($parsedValue) && empty($parsedValue)) {
        //     $parsedValue = null;
        // }

        $event = new FieldEvent([
            'feedData' => $feedData,
            'fieldHandle' => $fieldHandle,
            'fieldInfo' => $fieldInfo,
            'element' => $element,
            'feed' => $feed,
            'parsedValue' => $parsedValue,
        ]);
        $this->trigger(self::EVENT_AFTER_PARSE_FIELD, $event);
        return $event->parsedValue;
    }

}
