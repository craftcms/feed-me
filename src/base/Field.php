<?php

namespace craft\feedme\base;

use ArrayAccess;
use Cake\Utility\Hash;
use Craft;
use craft\base\Component;
use craft\errors\ElementNotFoundException;
use craft\feedme\helpers\DataHelper;
use craft\feedme\models\FeedModel;
use craft\feedme\Plugin;
use Throwable;
use yii\base\Exception;

/**
 *
 * @property-read mixed $name
 * @property-read mixed $fieldClass
 * @property-read mixed $class
 * @property-read mixed $elementType
 */
abstract class Field extends Component
{
    // Properties
    // =========================================================================

    /**
     * @var
     */
    public $feedData;

    /**
     * @var
     */
    public $fieldHandle;

    /**
     * @var
     */
    public $fieldInfo;

    /**
     * @var
     */
    public $field;

    /**
     * @var FeedModel|array
     */
    public $feed;

    /**
     * @var
     */
    public $element;


    // Public Methods
    // =========================================================================

    /**
     * @return mixed
     */
    public function getName()
    {
        /** @phpstan-ignore-next-line */
        return static::$name;
    }

    /**
     * @return string
     */
    public function getClass()
    {
        return get_class($this);
    }

    /**
     * @return mixed
     */
    public function getFieldClass()
    {
        /** @phpstan-ignore-next-line */
        return static::$class;
    }

    /**
     * @return mixed
     */
    public function getElementType()
    {
        /** @phpstan-ignore-next-line */
        return static::$elementType;
    }


    // Templates
    // =========================================================================

    // public function getMappingTemplate()
    // {
    //     return 'feed-me/_includes/fields/default';
    // }


    // Public Methods
    // =========================================================================

    /**
     * @return array|ArrayAccess|mixed|string|null
     */
    public function fetchSimpleValue()
    {
        return DataHelper::fetchSimpleValue($this->feedData, $this->fieldInfo);
    }

    /**
     * @return array|ArrayAccess|mixed
     */
    public function fetchArrayValue()
    {
        return DataHelper::fetchArrayValue($this->feedData, $this->fieldInfo);
    }

    /**
     * @return array|ArrayAccess|mixed|null
     */
    public function fetchValue()
    {
        return DataHelper::fetchValue($this->feedData, $this->fieldInfo, $this->feed);
    }

    // Protected Methods
    // =========================================================================

    /**
     * @param $elementIds
     * @throws Throwable
     * @throws ElementNotFoundException
     * @throws Exception
     */
    protected function populateElementFields($elementIds)
    {
        $elementsService = Craft::$app->getElements();
        $fields = Hash::get($this->fieldInfo, 'fields');

        $fieldData = [];

        foreach ($elementIds as $key => $elementId) {
            foreach ($fields as $fieldHandle => $fieldInfo) {
                $default = Hash::get($fieldInfo, 'default');

                // Because we're dealing with an element which always has array content, we need to fetch our content
                // in the same way, as it'll be parsed as an array, despite the actual values being likely a single value
                // Even if its an array of one size (importing one element), that's fine!
                $fieldValue = DataHelper::fetchArrayValue($this->feedData, $fieldInfo);

                // Arrayed content doesn't provide defaults because its unable to determine how many items it _should_ return
                // This also checks if there was any data that corresponds on the same array index/level as our element
                $value = Hash::get($fieldValue, $key, $default);

                if ($value) {
                    $fieldData[$elementId][$fieldHandle] = $value;
                }
            }
        }

        // Now, for each element, we need to save the contents
        foreach ($fieldData as $elementId => $fieldContent) {
            $element = $elementsService->getElementById($elementId, null, Hash::get($this->feed, 'siteId'));

            $element->setFieldValues($fieldContent);

            Plugin::debug([
                $this->fieldHandle => [
                    $elementId => $fieldContent,
                ],
            ]);

            if (!$elementsService->saveElement($element, true, true, Hash::get($this->feed, 'updateSearchIndexes'))) {
                Plugin::error('`{handle}` - Unable to save sub-field: `{e}`.', ['e' => json_encode($element->getErrors()), 'handle' => $this->fieldHandle]);
            }

            Plugin::info('`{handle}` - Processed {name} [`#{id}`]({url}) sub-fields with content: `{content}`.', ['name' => $element::displayName(), 'id' => $elementId, 'url' => $element->cpEditUrl, 'handle' => $this->fieldHandle, 'content' => json_encode($fieldContent)]);
        }
    }
}
