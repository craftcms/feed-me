<?php
namespace verbb\feedme\base;

use verbb\feedme\FeedMe;
use verbb\feedme\helpers\BaseHelper;
use verbb\feedme\helpers\DataHelper;
use verbb\feedme\helpers\DateHelper;

use Craft;
use craft\base\Component;
use craft\base\Element as BaseElement;
use craft\elements\User as UserElement;
use craft\helpers\Db;
use craft\helpers\ElementHelper;
use craft\helpers\StringHelper;

use Cake\Utility\Hash;

abstract class Element extends Component
{
    // Constants
    // =========================================================================

    const EVENT_BEFORE_PARSE_ATTRIBUTE = 'onBeforeParseAttribute';
    const EVENT_AFTER_PARSE_ATTRIBUTE = 'onParseAttribute';


    // Public Methods
    // =========================================================================

    public function getName()
    {
        return $this::$name;
    }

    public function getClass()
    {
        return get_class($this);
    }

    public function getElementClass()
    {
        return $this::$class;
    }

    public function parseAttribute($feedData, $fieldHandle, $fieldInfo)
    {
        if ($this->hasEventHandlers(self::EVENT_BEFORE_PARSE_ATTRIBUTE)) {
            $this->trigger(self::EVENT_BEFORE_PARSE_ATTRIBUTE, new ElementEvent([
                'feedData' => $feedData,
                'fieldHandle' => $fieldHandle,
                'fieldInfo' => $fieldInfo,
            ]));
        }

        // Find the class to deal with the attribute
        $name = 'parse' . ucwords($fieldHandle);

        // Set a default handler for non-specific attribute classes
        if (!method_exists($this, $name)) {
            return $this->fetchSimpleValue($feedData, $fieldInfo);
        }

        $parsedValue = $this->$name($feedData, $fieldInfo);

        if ($this->hasEventHandlers(self::EVENT_AFTER_PARSE_ATTRIBUTE)) {
            $this->trigger(self::EVENT_AFTER_PARSE_ATTRIBUTE, new ElementEvent([
                'feedData' => $feedData,
                'fieldHandle' => $fieldHandle,
                'fieldInfo' => $fieldInfo,
                'parsedValue' => $parsedValue,
            ]));
        }

        return $parsedValue;
    }

    public function fetchSimpleValue($feedData, $fieldInfo)
    {
        return DataHelper::fetchSimpleValue($feedData, $fieldInfo);
    }

    public function fetchArrayValue($feedData, $fieldInfo)
    {
        return DataHelper::fetchArrayValue($feedData, $fieldInfo);
    }


    // Interface Methods
    // =========================================================================

    public function matchExistingElement($data, $settings)
    {
        $criteria = [];

        foreach ($settings['fieldUnique'] as $handle => $value) {
            $feedValue = Hash::get($data, $handle);

            if ($feedValue) {
                if (is_object($feedValue) && get_class($feedValue) === 'DateTime') {
                    $feedValue = $feedValue->format('Y-m-d H:i:s');
                }

                $criteria[$handle] = Db::escapeParam($feedValue);
            }
        }

        // Make sure we have data to match on, otherwise it'll just grab the first found entry
        // without matching against anything. Not what we want at all!
        if (count($criteria) === 0) {
            throw new \Exception('Unable to match an existing element. Have you set a unique identifier for ' . json_encode(array_keys($settings['fieldUnique'])) . '? Make sure you are also mapping this in your feed and it has a value.');
        }

        // Check against elements that may be disabled for site
        $criteria['enabledForSite'] = false;
        
        return $this->getQuery($settings, $criteria)->one();
    }

    public function delete($elementIds)
    {
        $elementsService = Craft::$app->getElements();
        
        foreach ($elementIds as $elementId) {
            $elementsService->deleteElementById($elementId);
        }

        return true;
    }

    public function disable($elementIds)
    {
        $elementsService = Craft::$app->getElements();

        foreach ($elementIds as $elementId) {
            $element = $elementsService->getElementById($elementId);
            $element->enabled = false;

            $elementsService->saveElement($element);
        }

        return true;
    }

    public function save($data, $settings)
    {
        $propogate = isset($settings['siteId']) && $settings['siteId'] ? false : true;

        $this->element->setScenario(BaseElement::SCENARIO_ESSENTIALS);

        if (!Craft::$app->getElements()->saveElement($this->element, true, $propogate)) {
            return false;
        }

        return true;
    }

    public function afterSave($data, $settings)
    {

    }


    // Protected Methods
    // =========================================================================

    protected function parseSlug($feedData, $fieldInfo)
    {
        $value = $this->fetchSimpleValue($feedData, $fieldInfo);

        if (Craft::$app->getConfig()->getGeneral()->limitAutoSlugsToAscii) {
            $value = StringHelper::toAscii($value);
        }

        return ElementHelper::createSlug($value);
    }

    protected function parseEnabled($feedData, $fieldInfo)
    {
        $value = $this->fetchSimpleValue($feedData, $fieldInfo);

        return BaseHelper::parseBoolean($value);
    }

    protected function parseDateAttribute($value, $formatting)
    {
        $dateValue = DateHelper::parseString($value, $formatting);

        if ($dateValue) {
            return $dateValue;
        }
        
        return null;
    }

}
