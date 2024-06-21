<?php

namespace craft\feedme\fieldlayoutelements\users;

use Cake\Utility\Hash;
use Craft;
use craft\base\Element;
use craft\base\ElementInterface;
use craft\elements\Address as AddressElement;
use craft\errors\ElementNotFoundException;
use craft\feedme\base\Field;
use craft\feedme\base\FieldInterface;
use craft\feedme\helpers\DataHelper;
use craft\feedme\Plugin;
use craft\fieldlayoutelements\users\AddressesField as AddressesField;
use craft\helpers\ArrayHelper;
use craft\helpers\Json;

/**
 *
 * @property-read string $mappingTemplate
 */
class Addresses extends Field implements FieldInterface
{
    // Properties
    // =========================================================================

    /**
     * @var string
     */
    public static string $name = 'Addresses';

    /**
     * @var string
     */
    public static string $class = AddressesField::class;

    /**
     * @var string
     */
    public static string $elementType = AddressElement::class;


    // Templates
    // =========================================================================

    /**
     * @inheritDoc
     */
    public function getMappingTemplate(): string
    {
        return 'feed-me/_includes/fieldlayoutelements/users/addresses';
    }

    // Public Methods
    // =========================================================================

    /**
     * @inheritDoc
     */
    public function parseField(): mixed
    {
        $fieldValue = [];

        $fields = Hash::get($this->fieldInfo, 'fields');
        $nativeFields = Hash::get($this->fieldInfo, 'nativeFields');

        // figure out how many addresses we have in the dataset
        $fieldGroups = array_filter(
            ['nativeFields' => $nativeFields, 'fields' => $fields],
            fn($group) => !empty($group)
        );
        $noAddresses = $this->getNumberOfAddresses($fieldGroups);

        for ($i = 0; $i < $noAddresses; $i++) {
            // we have to find/create Address element here
            // todo - should we allow finding an existing address? if so, what should we map by? id and custom fields?
            $element = $this->_createElement($nativeFields, $i);

            // if we were able to save the address, and we have fields to process, go ahead and do that
            if ($element && $fields) {
                $this->populateElementFields([$element->id], $i);
            }

            // returning just the address id for compareElementContent check
            $fieldValue[] = $element->id;
        }

        return $fieldValue;
    }

    private function handleNativeFields(ElementInterface $element, array $nativeFields, int $nodeKey): void
    {
        $attributeValues = [];
        foreach ($nativeFields as $fieldHandle => $fieldInfo) {
            $default = Hash::get($fieldInfo, 'default');
            //$fieldValue = DataHelper::fetchArrayValue($this->feedData, $fieldInfo);
            $fieldValue = DataHelper::fetchValue($this->feedData, $fieldInfo, $this->feed);

            // Find the class to deal with the attribute
            $name = 'parse' . ucwords($fieldHandle);

            // Set a default handler for non-specific attribute classes
            if (!method_exists($this, $name)) {
                $value = Hash::get($fieldValue, $nodeKey, $default);
            } else {
                $value = $this->$name($element, $fieldInfo, $nodeKey);
            }

            if (!empty($value)) {
                $attributeValues[$fieldHandle] = $value;
            }
        }

        if (!empty($attributeValues)) {
            $element->setAttributes($attributeValues, false);
        }
    }

    private function parseAddress($element, $fieldInfo, $nodeKey): void
    {
        $nativeFields = Hash::get($fieldInfo, 'nativeFields');
        $this->handleNativeFields($element, $nativeFields, $nodeKey);
    }

    private function parseLatLong($element, $fieldInfo, $nodeKey): void
    {
        $nativeFields = Hash::get($fieldInfo, 'nativeFields');
        $this->handleNativeFields($element, $nativeFields, $nodeKey);
    }

    private function getNumberOfAddresses(array $fields): int
    {
        $noAddresses = 0;

        foreach ($fields as $fieldTypeGroup) {
            foreach ($fieldTypeGroup as $fieldInfo) {
                $node = Hash::get($fieldInfo, 'node');
                if ($node) {
                    $nodeSegments = explode('/', $node);
                    $regex = str_replace('//', '/', implode('\/\d?\/', $nodeSegments));
                    $matches = preg_grep('/' . $regex . '/', array_keys($this->feedData));
                    if (count($matches) > $noAddresses) {
                        $noAddresses = count($matches);
                    }
                }
            }
        }

        return $noAddresses;
    }

    // Private Methods
    // =========================================================================

    /**
     * @param array $nativeFields
     * @param int $nodeIndex
     * @return ElementInterface|null
     */
    private function _createElement(array $nativeFields, int $nodeIndex): ?ElementInterface
    {
        $element = new AddressElement();
        $element->setScenario(Element::SCENARIO_ESSENTIALS);
        $element->setOwner($this->element);

        // native fields have to go first!
        if ($nativeFields) {
            $this->handleNativeFields($element, $nativeFields, $nodeIndex);
        }

        if (!Craft::$app->getElements()->saveElement($element)) {
            Plugin::error('Address error: Could not create - `{e}`.', ['e' => Json::encode($element->getErrors())]);

            return null;
        }

        Plugin::info('Address `#{id}` added.', ['id' => $element->id]);

        return $element;
    }

    /**
     * Attempt to find User based on search criteria. Return array of found IDs.
     *
     * @param $criteria
     * @return array|int[]
     */
    private function _findAddresses($criteria): array
    {
        $query = AddressElement::find();
        Craft::configure($query, $criteria);

        Plugin::info('Search for existing address with query `{i}`', ['i' => json_encode($criteria)]);

        $ids = $query->ids();

        Plugin::info('Found `{i}` existing addresses: `{j}`', ['i' => count($ids), 'j' => json_encode($ids)]);

        return $ids;
    }
}
