<?php

namespace semabit\feedme\services;

use Cake\Utility\Hash;
use Craft;
use craft\base\Component;
use craft\base\ComponentInterface;
use craft\errors\MissingComponentException;
use semabit\feedme\base\FieldInterface;
use semabit\feedme\events\FieldEvent;
use semabit\feedme\events\RegisterFeedMeFieldsEvent;
use semabit\feedme\fields\Assets;
use semabit\feedme\fields\CalendarEvents;
use semabit\feedme\fields\Categories;
use semabit\feedme\fields\Checkboxes;
use semabit\feedme\fields\CommerceProducts;
use semabit\feedme\fields\CommerceVariants;
use semabit\feedme\fields\Date;
use semabit\feedme\fields\DefaultField;
use semabit\feedme\fields\DigitalProducts;
use semabit\feedme\fields\Dropdown;
use semabit\feedme\fields\Entries;
use semabit\feedme\fields\EntriesSubset;
use semabit\feedme\fields\GoogleMaps;
use semabit\feedme\fields\Lightswitch;
use semabit\feedme\fields\Linkit;
use semabit\feedme\fields\Matrix;
use semabit\feedme\fields\MissingField;
use semabit\feedme\fields\Money;
use semabit\feedme\fields\MultiSelect;
use semabit\feedme\fields\Number;
use semabit\feedme\fields\RadioButtons;
use semabit\feedme\fields\SimpleMap;
use semabit\feedme\fields\SmartMap;
use semabit\feedme\fields\SuperTable;
use semabit\feedme\fields\Table;
use semabit\feedme\fields\Tags;
use semabit\feedme\fields\TypedLink;
use semabit\feedme\fields\Users;
use craft\helpers\Component as ComponentHelper;
use yii\base\InvalidConfigException;

/**
 *
 * @property-read array $registeredFields
 */
class Fields extends Component
{
    // Constants
    // =========================================================================

    public const EVENT_REGISTER_FEED_ME_FIELDS = 'registerFeedMeFields';
    public const EVENT_BEFORE_PARSE_FIELD = 'onBeforeParseField';
    public const EVENT_AFTER_PARSE_FIELD = 'onAfterParseField';


    // Properties
    // =========================================================================

    /**
     * @var array
     */
    private array $_fields = [];

    // Public Methods
    // =========================================================================

    /**
     * @inheritDoc
     */
    public function init(): void
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

    /**
     * @param $handle
     * @return ComponentInterface|MissingDataType|mixed
     * @throws InvalidConfigException
     */
    public function getRegisteredField($handle): mixed
    {
        return $this->_fields[$handle] ?? $this->createField(DefaultField::class);
    }

    /**
     * @return array
     */
    public function fieldsList(): array
    {
        $list = [];

        foreach ($this->_fields as $handle => $field) {
            $list[$handle] = $field::$name;
        }

        return $list;
    }

    /**
     * @return array
     */
    public function getRegisteredFields(): array
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
                Money::class,
                RadioButtons::class,
                Table::class,
                Tags::class,
                Users::class,

                // Third-Party
                CalendarEvents::class,
                DigitalProducts::class,
                EntriesSubset::class,
                GoogleMaps::class,
                Linkit::class,
                SimpleMap::class,
                SmartMap::class,
                SuperTable::class,
                TypedLink::class,
            ],
        ]);

        $this->trigger(self::EVENT_REGISTER_FEED_ME_FIELDS, $event);

        return $event->fields;
    }

    /**
     * @param $config
     * @return FieldInterface
     * @throws InvalidConfigException
     */
    public function createField($config): FieldInterface
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

            $field = new MissingField($config);
        }

        /** @var FieldInterface $field */
        return $field;
    }

    /**
     * @param $feed
     * @param $element
     * @param $feedData
     * @param $fieldHandle
     * @param $fieldInfo
     * @return mixed
     */
    public function parseField($feed, $element, $feedData, $fieldHandle, $fieldInfo): mixed
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

        $fieldClassHandle = Hash::get($fieldInfo, 'field');

        // if category groups or tag groups have been entrified, the fields for them could have been entrified too;
        // get the field by handle, check if the type hasn't changed since the feed was last saved;
        // if it hasn't changed - proceed as before
        // if it has changed - assume that we've entrified and adjust the $fieldClassHandle
        $field = Craft::$app->getFields()->getFieldByHandle($fieldHandle);
        if (!$field instanceof $fieldClassHandle) {
            $fieldClassHandle = \craft\fields\Entries::class;
        }

        // Find the class to deal with the attribute
        $class = $this->getRegisteredField($fieldClassHandle);
        $class->feedData = $feedData;
        $class->fieldHandle = $fieldHandle;
        $class->fieldInfo = $fieldInfo;
        $class->field = $field;
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
