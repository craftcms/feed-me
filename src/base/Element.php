<?php

namespace craft\feedme\base;

use ArrayAccess;
use Cake\Utility\Hash;
use Carbon\Carbon;
use Craft;
use craft\base\Component;
use craft\base\Element as BaseElement;
use craft\base\ElementInterface as CraftElementInterface;
use craft\elements\db\ElementQuery;
use craft\feedme\events\ElementEvent;
use craft\feedme\helpers\BaseHelper;
use craft\feedme\helpers\DataHelper;
use craft\feedme\helpers\DateHelper;
use craft\feedme\models\FeedModel;
use craft\helpers\Db;
use craft\helpers\Json;
use craft\helpers\StringHelper;
use DateTime;
use Exception;

/**
 *
 * @property-read mixed $name
 * @property-read mixed $elementClass
 * @property-read mixed $class
 */
abstract class Element extends Component implements ElementInterface
{
    // Constants
    // =========================================================================

    public const EVENT_BEFORE_PARSE_ATTRIBUTE = 'onBeforeParseAttribute';
    public const EVENT_AFTER_PARSE_ATTRIBUTE = 'onParseAttribute';


    // Properties
    // =========================================================================


    /**
     * @var FeedModel|null
     */
    public ?FeedModel $feed = null;

    /**
     * @var CraftElementInterface
     */
    public $element;


    // Public Methods
    // =========================================================================

    /**
     * @return mixed
     */
    public function getName(): string
    {
        /** @phpstan-ignore-next-line */
        return static::$name;
    }

    /**
     * @return string
     */
    public function getClass(): string
    {
        return get_class($this);
    }

    /**
     * @inheritDoc
     */
    public function getElementClass(): string
    {
        /** @phpstan-ignore-next-line */
        return static::$class;
    }

    /**
     * @param $feedData
     * @param $fieldHandle
     * @param $fieldInfo
     * @return array|ArrayAccess|mixed|string|null
     */
    public function parseAttribute($feedData, $fieldHandle, $fieldInfo): mixed
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

    /**
     * @param $feedData
     * @param $fieldInfo
     * @return array|ArrayAccess|mixed|string|null
     */
    public function fetchSimpleValue($feedData, $fieldInfo): mixed
    {
        return DataHelper::fetchSimpleValue($feedData, $fieldInfo);
    }

    /**
     * @param $feedData
     * @param $fieldInfo
     * @return array|ArrayAccess|mixed
     */
    public function fetchArrayValue($feedData, $fieldInfo): mixed
    {
        return DataHelper::fetchArrayValue($feedData, $fieldInfo);
    }


    // Interface Methods
    // =========================================================================

    /**
     * @inheritDoc
     */
    public function matchExistingElement($data, $settings): mixed
    {
        $criteria = [];

        foreach ($settings['fieldUnique'] as $handle => $value) {
            $feedValue = Hash::get($data, $handle);

            if (!is_null($feedValue)) {
                if (is_object($feedValue) && get_class($feedValue) === 'DateTime') {
                    $feedValue = $feedValue->format('Y-m-d H:i:s');
                }

                // We need a value to check against
                if (is_string($feedValue) && $feedValue === '') {
                    continue;
                }

                $criteria[$handle] = Db::escapeParam($feedValue);
            }
        }

        // Make sure we have data to match on, otherwise it'll just grab the first found entry
        // without matching against anything. Not what we want at all!
        if (empty($settings['singleton']) && count($criteria) === 0) {
            throw new Exception('Unable to match an existing element. Have you set a unique identifier for ' . Json::encode(array_keys($settings['fieldUnique'])) . '? Make sure you are also mapping this in your feed and it has a value.');
        }

        // Check against elements that may be disabled for site
        // $criteria['enabledForSite'] = false;

        return $this->getQuery($settings, $criteria)->one();
    }

    /**
     * @inheritDoc
     */
    public function delete($elementIds): bool
    {
        /** @var CraftElementInterface|string $class */
        $class = $this->getElementClass();
        $elementsService = Craft::$app->getElements();

        foreach ($elementIds as $elementId) {
            $elementsService->deleteElementById($elementId, $class);
        }

        return true;
    }

    /**
     * @inheritDoc
     */
    public function disable($elementIds): bool
    {
        /** @var CraftElementInterface|string $class */
        $class = $this->getElementClass();
        $elementsService = Craft::$app->getElements();

        foreach ($elementIds as $elementId) {
            /** @var BaseElement $element */
            $element = $elementsService->getElementById($elementId, $class);
            $element->enabled = false;
            $elementsService->saveElement($element, true, true, Hash::get($this->feed, 'updateSearchIndexes'));
        }

        return true;
    }

    /**
     * @inheritDoc
     */
    public function disableForSite(array $elementIds): bool
    {
        /** @var CraftElementInterface|string $class */
        $class = $this->getElementClass();

        /** @var ElementQuery $query */
        $query = $class::find()
            ->id($elementIds)
            ->siteId($this->feed->siteId)
            ->status(null);

        $elementsService = Craft::$app->getElements();

        foreach ($query->each() as $element) {
            /** @var BaseElement $element */
            $element->enabledForSite = false;
            $elementsService->saveElement($element, false, false, Hash::get($this->feed, 'updateSearchIndexes'));
        }

        return true;
    }

    /**
     * @inheritDoc
     */
    public function save($element, $settings): bool
    {
        // Setup some stuff before the element saves, and also give a chance to prevent saving
        if (!$this->beforeSave($element, $settings)) {
            return true;
        }

        if (!Craft::$app->getElements()->saveElement($this->element, true, true, Hash::get($this->feed, 'updateSearchIndexes'))) {
            return false;
        }

        return true;
    }

    public function beforeSave($element, $settings): bool
    {
        $this->element = $element;
        $this->element->setScenario(BaseElement::SCENARIO_ESSENTIALS);

        return true;
    }

    /**
     * @inheritDoc
     */
    public function afterSave($data, $settings): void
    {
    }

    // Protected Methods
    // =========================================================================

    /**
     * @param $feedData
     * @param $fieldInfo
     * @return string
     */
    protected function parseTitle($feedData, $fieldInfo): string
    {
        $value = $this->fetchSimpleValue($feedData, $fieldInfo);

        // Truncate if need be
        if (is_string($value) && strlen($value) > 255) {
            $value = StringHelper::safeTruncate($value, 255);
        }

        return $value;
    }

    /**
     * @param $feedData
     * @param $fieldInfo
     * @return string
     */
    protected function parseSlug($feedData, $fieldInfo): string
    {
        $value = $this->fetchSimpleValue($feedData, $fieldInfo);

        if (Craft::$app->getConfig()->getGeneral()->limitAutoSlugsToAscii) {
            $value = $this->_asciiString($value);
        }

        return $this->_createSlug($value);
    }

    /**
     * @param $feedData
     * @param $fieldInfo
     * @return bool
     */
    protected function parseEnabled($feedData, $fieldInfo): bool
    {
        $value = $this->fetchSimpleValue($feedData, $fieldInfo);

        return BaseHelper::parseBoolean($value);
    }

    /**
     * @param $value
     * @param $formatting
     * @return DateTime|null
     * @throws Exception
     */
    protected function parseDateAttribute($value, $formatting): ?DateTime
    {
        $dateValue = DateHelper::parseString($value, $formatting);
        if ($dateValue instanceof Carbon) {
            $dateValue = $dateValue->toDateTime();
        }

        if (!empty($dateValue)) {
            return $dateValue;
        }

        return null;
    }

    /**
     * @param string $str
     * @return string
     */
    private function _createSlug(string $str): string
    {
        // Remove HTML tags
        $str = StringHelper::stripHtml($str);

        // Convert to kebab case
        $glue = Craft::$app->getConfig()->getGeneral()->slugWordSeparator;
        $lower = !Craft::$app->getConfig()->getGeneral()->allowUppercaseInSlug;
        return StringHelper::toKebabCase($str, $glue, $lower);
    }

    /**
     * @param $str
     * @return string
     */
    private function _asciiString($str): string
    {
        $charMap = StringHelper::asciiCharMap(true, Craft::$app->language);

        $asciiStr = '';

        $iMax = mb_strlen($str);
        for ($i = 0; $i < $iMax; $i++) {
            $char = mb_substr($str, $i, 1);
            $asciiStr .= $charMap[$char] ?? $char;
        }

        return $asciiStr;
    }
}
