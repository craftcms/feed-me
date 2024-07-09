<?php

namespace craft\feedme\helpers;

use ArrayAccess;
use Cake\Utility\Hash;
use Craft;
use craft\feedme\models\FeedModel;
use craft\feedme\Plugin;
use craft\helpers\DateTimeHelper;
use craft\helpers\Db;
use craft\helpers\Json;
use DateTime;
use Throwable;

class DataHelper
{
    // Public Methods
    // =========================================================================

    /**
     * Check if provided value is not set or empty or an array of empties
     *
     * @param $value
     * @param $allowZero bool Whether to treat zero as an empty value or not
     * @return bool
     */
    public static function isArrayValueEmpty($value, $allowZero = false): bool
    {
        return (!$value ||
            (is_array($value) && empty(array_filter($value, function($item) use ($allowZero): bool {
                if ($allowZero) {
                    return (!empty($item) || is_numeric($item));
                }

                return !empty($item);
            })))
        );
    }

    /**
     * @param $feedData
     * @param $fieldInfo
     * @return array|ArrayAccess|mixed|string|null
     */
    public static function fetchSimpleValue($feedData, $fieldInfo): mixed
    {
        $node = Hash::get($fieldInfo, 'node');
        $default = Hash::get($fieldInfo, 'default');

        // We can't use Hash::get because the node path might contain a dot - that'll mess things up
        // $value = Hash::get($feedData, $node);
        $value = $feedData[$node] ?? null;

        // Use the default value for the field-mapping (if defined)
        if (($value === null || $value === '') && !empty($default)) {
            $value = $default;
        }

        if (is_string($value)) {
            $value = trim($value);
        }

        return $value;
    }

    /**
     * @param $feedData
     * @param $fieldInfo
     * @return array|ArrayAccess|mixed
     */
    public static function fetchArrayValue($feedData, $fieldInfo, $nodeName = 'node'): mixed
    {
        $value = null;

        $node = Hash::get($fieldInfo, $nodeName);

        $dataDelimiter = Plugin::$plugin->service->getConfig('dataDelimiter');

        // Some fields require array, or multiple values like Elements, Checkboxes, etc., and we need to parse them differently.
        // Firstly, field mapping is set up like `MatrixBlock/Images` but actual feed is structured like `MatrixBlock/0/Images/0`.
        // We strip out the numbers to first find the node we've mapped to, then iterate over possible multiple values in the feed.
        foreach ($feedData as $nodePath => $nodeValue) {
            // Strip out array numbers in the feed path like: MatrixBlock/0/Images/0. We use this to get the field
            // it's supposed to match up with, which is stored in the DB like MatrixBlock/Images
            $feedPath = preg_replace('/(\/\d+\/)/', '/', $nodePath);
            $feedPath = preg_replace('/^(\d+\/)|(\/\d+)/', '', $feedPath);

            if ($feedPath == $node || $nodePath == $node) {
                // Allow pipes '|' to denote multiple items, but even if it doesn't contain one, explode will create
                // an array, so ensure to merge with the current results.
                if (is_string($nodeValue) && str_contains($nodeValue, $dataDelimiter)) {
                    $delimitedValues = explode($dataDelimiter, $nodeValue);

                    // Trim values in case whitespace was used between delimiter
                    $delimitedValues = array_map('trim', $delimitedValues);

                    // we need the value to start as null for setEmptyValues
                    // but we also need it to be an array at this point so that array_merge doesn't freak out
                    // so if it's null so far, change it to an empty array as we're about to populate it with data
                    if ($value === null) {
                        $value = [];
                    }
                    $value = array_merge($value, $delimitedValues);
                } else {
                    $value[] = $nodeValue;
                }
            }
        }

        // Check if not importing, just using default
        if ($node === 'usedefault' && !$value) {
            $value = self::fetchDefaultArrayValue($fieldInfo);
        }

        return $value;
    }

    /**
     * @param $fieldInfo
     * @return array|\ArrayAccess|mixed
     */
    public static function fetchDefaultArrayValue($fieldInfo)
    {
        $default = Hash::get($fieldInfo, 'default');

        if (!is_array($default)) {
            if (empty($default)) {
                $default = [];
            } else {
                $default = [$default];
            }
        }

        if (!empty($default) && !is_array($default)) {
            $default = [$default];
        }

        return $default;
    }

    /**
     * @param $feedData
     * @param $fieldInfo
     * @param array|FeedModel|null $feed
     * @return array|ArrayAccess|mixed|null
     */
    public static function fetchValue($feedData, $fieldInfo, $feed = null): mixed
    {
        // $feed will be a FeedModel when calling `fetchValue` from an element
        if ($feed instanceof FeedModel) {
            $feed = $feed->toArray();
        }

        $value = null;

        $node = Hash::get($fieldInfo, 'node');
        $default = Hash::get($fieldInfo, 'default');
        $dataDelimiter = Plugin::$plugin->service->getConfig('dataDelimiter');

        // Some fields require array, or multiple values like Elements, Checkboxes, etc., and we need to parse them differently.
        // Firstly, field mapping is set up like `MatrixBlock/Images` but actual feed is structured like `MatrixBlock/0/Images/0`.
        // We strip out the numbers to first find the node we've mapped to, then iterate over possible multiple values in the feed.
        foreach ($feedData as $nodePath => $nodeValue) {
            // Strip out array numbers in the feed path like: MatrixBlock/0/Images/0. We use this to get the field
            // it's supposed to match up with, which is stored in the DB like MatrixBlock/Images
            $feedPath = preg_replace('/(\/\d+\/)/', '/', $nodePath);
            $feedPath = preg_replace('/^(\d+\/)|(\/\d+)/', '', $feedPath);

            if ($feedPath == $node || $nodePath == $node) {
                if ($nodeValue === null || $nodeValue === '') {
                    $nodeValue = $default;
                }

                // Allow pipes '|' to denote multiple items, but even if it doesn't contain one, explode will create
                // an array, so ensure to merge with the current results.
                if (is_string($nodeValue) && str_contains($nodeValue, $dataDelimiter)) {
                    $delimitedValues = explode($dataDelimiter, $nodeValue);

                    // Trim values in case whitespace was used between delimiter
                    $delimitedValues = array_map('trim', $delimitedValues);

                    // we need the value to start as null for setEmptyValues
                    // but we also need it to be an array at this point so that array_merge doesn't freak out
                    // so if it's null so far, change it to an empty array as we're about to populate it with data
                    if ($value === null) {
                        $value = [];
                    }
                    $value = array_merge($value, $delimitedValues);
                } else {
                    $value[] = $nodeValue;
                }
            }
        }

        // Check if not importing, just using default
        if ($node === 'usedefault' && !$value) {
            $value = $default;
        }

        // if value is still null - return
        if ($value === null) {
            return null;
        }

        // Help to normalise things if an array with only one item. Probably a better idea to offload this to each
        // attribute of field definition, as its quite an assumption at this point...
        if (is_array($value) && count($value) === 1) {
            $value = $value[0];
        }

        // If setEmptyValues is enabled allow overwriting existing data
        if ($feed !== null && $value === "" && $feed['setEmptyValues']) {
            return $value;
        }

        // We want to preserve 0 and '0', but if it's empty, return null.
        // https://github.com/craftcms/feed-me/issues/779
        if (!is_numeric($value) && !is_bool($value) && empty($value)) {
            return null;
        }

        return $value;
    }

    /**
     * @param $value
     * @param $element
     * @return mixed|string
     */
    public static function parseFieldDataForElement($value, $element): mixed
    {
        if (is_string($value) && str_contains($value, '{')) {
            // Make sure to wrap in try/catch, as if this is a literal '{' in content somewhere
            // it won't be a field handle tag, causing the Twig Lexer to freak out. We ignore those errors
            try {
                $value = Craft::$app->getView()->renderObjectTemplate($value, $element);
            } catch (Throwable $e) {
            }
        }

        return $value;
    }

    /**
     * @param $content
     * @param $element
     * @return bool|null
     */
    public static function compareElementContent($content, $element): ?bool
    {
        if (!$element) {
            return false;
        }

        $trackedChanges = $content;

        $fields = $element->getSerializedFieldValues();
        $attributes = $element->attributes;
        if (isset($attributes['enabled'])) {
            $attributes['enabledForSite'] = $element->getEnabledForSite();
        }

        foreach ($content as $key => $newValue) {
            $existingValue = Hash::get($fields, $key);

            [$existingValue, $newValue] = self::prepDatesForComparison($existingValue, $newValue);

            // If array key & values are already within the existing array
            if (is_array($newValue) && is_array($existingValue) && Hash::contains($existingValue,$newValue)) {
                unset($trackedChanges[$key]);
                continue;
            }

            // Check for simple fields first
            if (self::_compareSimpleValues($fields, $key, $existingValue, $newValue)) {
                unset($trackedChanges[$key]);
                continue;
            }
            
            // Check for complex fields
            if (self::_compareComplexValues($fields, $key, $existingValue, $newValue)) {
                unset($trackedChanges[$key]);
                continue;
            }

            // Then check for simple attributes
            $existingValue = Hash::get($attributes, $key);

            // If date value, make sure to cast it as a string to compare
            if ($existingValue instanceof \DateTime || DateTimeHelper::isIso8601($existingValue)) {
                $existingValue = Db::prepareDateForDb($existingValue);
            }

            // Check for attribute groups - more than simple asset
            if ($key === 'groups') {
                $groups = $element->getGroups();

                foreach ($groups as $k => $group) {
                    $groups[$k] = $group->id;
                }

                $existingValue = $groups;
            }

            if (self::_compareSimpleValues($attributes, $key, $existingValue, $newValue)) {
                unset($trackedChanges[$key]);
                continue;
            }

            // Check for complicated fields = looking at you Matrix!
            $existingValue = Hash::get($fields, $key);

            // For debugging - clearly see how the data differs
            if (is_array($existingValue) && is_array($newValue)) {
                $diff = self::arrayCompare($existingValue, $newValue);

                Plugin::debug($key . ' - diff');
                Plugin::debug($diff);
            }

            // Now its getting personal. We need to check things per field type
            // Find the resulting value from what Feed Me's field processing would produce
            // $field = Craft::$app->getFields()->getFieldByHandle($key);

            Plugin::debug($key . ' - existing');
            Plugin::debug($existingValue);
            Plugin::debug($key . ' - new');
            Plugin::debug($newValue);

            Plugin::info('Data to update for `{i}`: `{j}`.', ['i' => $key, 'j' => Json::encode($newValue)]);
        }

        return empty($trackedChanges);
    }

    /**
     * Prepare dates for comparison.
     *
     * @param mixed $firstValue
     * @param mixed $secondValue
     * @return array
     */
    public static function prepDatesForComparison(mixed $firstValue, mixed $secondValue): array
    {
        // If date value, make sure to cast it as a string to compare
        if ($firstValue instanceof \DateTime || DateTimeHelper::isIso8601($firstValue)) {
            $firstValue = Db::prepareDateForDb($firstValue);
        }

        // If date value, make sure to cast it as a string to compare
        if ($secondValue instanceof DateTime || DateTimeHelper::isIso8601($secondValue)) {
            $secondValue = Db::prepareDateForDb($secondValue);
        }

        // If an empty 'date' value, it's the same as null
        if (is_array($secondValue) && isset($secondValue['date']) && $secondValue['date'] === '') {
            $secondValue = null;
        }

        return [$firstValue, $secondValue];
    }

    /**
     * @param $array1
     * @param $array2
     * @return bool|array
     */
    public static function arrayCompare($array1, $array2): bool|array
    {
        $diff = false;

        foreach ($array1 as $key => $value) {
            if (!array_key_exists($key, $array2)) {
                $diff[0][$key] = $value;
            } elseif (is_array($value)) {
                if (!is_array($array2[$key])) {
                    $diff[0][$key] = $value;
                    $diff[1][$key] = $array2[$key];
                } else {
                    $new = self::arrayCompare($value, $array2[$key]);

                    if ($new !== false) {
                        if (isset($new[0])) {
                            $diff[0][$key] = $new[0];
                        }

                        if (isset($new[1])) {
                            $diff[1][$key] = $new[1];
                        }
                    }
                }
            } elseif ($array2[$key] !== $value) {
                $diff[0][$key] = $value;
                $diff[1][$key] = $array2[$key];
            }
        }

        foreach ($array2 as $key => $value) {
            if (!array_key_exists($key, $array1)) {
                $diff[1][$key] = $value;
            }
        }

        return $diff;
    }

    /**
     * @param $fields
     * @param $key
     * @param $firstValue
     * @param $secondValue
     * @return bool
     */
    private static function _compareSimpleValues($fields, $key, $firstValue, $secondValue): bool
    {
        // When the values are arrays filled with numbers then they most likely represent references to elements.
        // Unfortunately these arrays sometimes have non-matching keys, while the values are the same, i.e. reference
        // the same elements.  In this case, we should determine that the values are the same.
        if (Hash::check($fields, $key)
            && is_array($firstValue)
            && is_array($secondValue)
            && array_reduce($firstValue, static fn($carry, $item) => $carry && is_numeric($item), true)
            && array_reduce($secondValue, static fn($carry, $item) => $carry && is_numeric($item), true)
            && array_values($firstValue) == array_values($secondValue)
        ) {
            return true;
        }

        // When the values are empty arrays we do NOT use the Hash::check below because that will always return false
        if (is_array($firstValue) && is_array($secondValue) && count($firstValue) === 0 && count($secondValue) === 0) {
            return true;
        }

        /** @noinspection TypeUnsafeComparisonInspection */
        // Should probably do a strict check, but doing this for backwards compatibility.
        if (Hash::check($fields, $key) && ($firstValue == $secondValue)) {
            // If this is a string, check the lengths
            if (is_string($firstValue) && is_string($secondValue)) {
                // String length comparison to take into account "637" and "0637"
                return mb_strlen($firstValue) == mb_strlen($secondValue);
            }

            // An array, but loosely equal
            return true;
        }

        // check if both empty
        if (
            Hash::check($fields, $key) &&
            (!is_numeric($firstValue) && !is_bool($firstValue) && empty($firstValue)) &&
            (!is_numeric($secondValue) && !is_bool($secondValue) && empty($secondValue))
        ) {
            return true;
        }

        // Didn't match
        return false;
    }
    
    /**
     * Compare values recursively while ignoring property order and null values
     *
     * @param mixed $firstValue
     * @param mixed $secondValue
     * @return bool
     */
    private static function _recursiveCompare($firstValue, $secondValue): bool
    {
        [$firstValue, $secondValue] = self::prepDatesForComparison($firstValue, $secondValue);

        if (is_array($firstValue) && is_array($secondValue)) {
            // Ignore values that are `null` or empty arrays
            $firstValue = array_filter($firstValue, static function($value) {
                return !($value === null || $value === '' || $value === []);
            });
            $secondValue = array_filter($secondValue, static function($value) {
                return !($value === null || $value === '' || $value === []);
            });

            // Both values must have the same keys (ignoring order)
            $firstKeys = array_keys($firstValue);
            $secondKeys = array_keys($secondValue);
            if (count(array_diff($firstKeys, $secondKeys)) || count(array_diff($secondKeys, $firstKeys))) {
                return false;
            }

            // Each key must be the same value
            foreach ($firstValue as $key => $value) {
                if (!self::_recursiveCompare($value, $secondValue[$key])) {
                    return false;
                }
            }

            return true;
        }

        if (is_object($firstValue) && is_object($secondValue)) {
            // For now this does not seem relevant
            return false;
        }

        return $firstValue === $secondValue;
    }

    private static function _compareComplexValues($fields, $key, $firstValue, $secondValue): bool
    {
        // When the values are nested arrays we are probably comparing matrix elements
        if (Hash::check($fields, $key)
            && is_array($firstValue)
            && is_array($secondValue)
            && array_reduce($firstValue, static function($carry, $item) {
                return $carry && is_array($item);
            }, true)
            && array_reduce($secondValue, static function($carry, $item) {
                return $carry && is_array($item);
            }, true)
        ) {
            // Compare the values recursively while ignoring the keys at the first level,
            // these keys are statically set to `'newX'` and the ids for the values in the database
            // so they have no meaning here
            return self::_recursiveCompare(array_values($firstValue), array_values($secondValue));
        }

        // Didn't match
        return false;
    }
}
