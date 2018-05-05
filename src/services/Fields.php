<?php
namespace verbb\feedme\services;

use verbb\feedme\FeedMe;
use verbb\feedme\base\FieldInterface;
use verbb\feedme\fields\Assets;
use verbb\feedme\fields\Categories;
use verbb\feedme\fields\Checkboxes;
use verbb\feedme\fields\CommerceProducts;
use verbb\feedme\fields\Date;
use verbb\feedme\fields\DefaultField;
use verbb\feedme\fields\Dropdown;
use verbb\feedme\fields\Entries;
use verbb\feedme\fields\Lightswitch;
use verbb\feedme\fields\Matrix;
use verbb\feedme\fields\MultiSelect;
use verbb\feedme\fields\Number;
use verbb\feedme\fields\RadioButtons;
use verbb\feedme\fields\SmartMap;
use verbb\feedme\fields\Table;
use verbb\feedme\fields\Tags;
use verbb\feedme\fields\Users;
use verbb\feedme\events\RegisterFeedMeFieldsEvent;
use verbb\feedme\events\FieldEvent;

use Craft;
use craft\base\Component;
use craft\db\Query;
use craft\helpers\Component as ComponentHelper;

use Cake\Utility\Hash;

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
    private $_fieldsByHandle = [];


    // Public Methods
    // =========================================================================

    public function init()
    {
        parent::init();

        // Load all fieldtypes once, used for later
        // foreach (Craft::$app->fields->getAllFields() as $field) {
        //     $this->_fieldsByHandle[$field->handle][] = $field;
        // }

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
                // CommerceProducts::class,
                Date::class,
                Dropdown::class,
                Entries::class,
                Lightswitch::class,
                Matrix::class,
                MultiSelect::class,
                Number::class,
                RadioButtons::class,
                SmartMap::class,
                Table::class,
                Tags::class,
                Users::class,
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
        if (is_array($parsedValue) && empty($parsedValue)) {
            $parsedValue = null;
        }

        if ($this->hasEventHandlers(self::EVENT_AFTER_PARSE_FIELD)) {
            $this->trigger(self::EVENT_AFTER_PARSE_FIELD, new FieldEvent([
                'feedData' => $feedData,
                'fieldHandle' => $fieldHandle,
                'fieldInfo' => $fieldInfo,
                'element' => $element,
                'feed' => $feed,
                'parsedValue' => $parsedValue,
            ]));
        }

        return $parsedValue;
    }

}
