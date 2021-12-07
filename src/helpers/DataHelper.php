<?php

namespace craft\feedme\helpers;

use Cake\Utility\Hash;
use Craft;
use craft\feedme\Plugin;
use craft\helpers\DateTimeHelper;
use craft\helpers\Db;

class DataHelper
{
    // Public Methods
    // =========================================================================

    /**
     * @param $feedData
     * @param $fieldInfo
     * @return array|\ArrayAccess|mixed|string|null
     */
    public static function fetchSimpleValue($feedData, $fieldInfo)
    {
        $node = Hash::get($fieldInfo, 'node');
        $default = Hash::get($fieldInfo, 'default');

        // We can't use Hash::get because the node path might contain a dot - that'll mess things up
        // $value = Hash::get($feedData, $node);
        $value = $feedData[$node] ?? null;

        // Use the default value for the field-mapping (if defined)
        if ($value === null || $value === '') {
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
     * @return array|\ArrayAccess|mixed
     */
    public static function fetchArrayValue($feedData, $fieldInfo)
    {
        $value = [];

        $node = Hash::get($fieldInfo, 'node');
        $default = Hash::get($fieldInfo, 'default');

        $dataDelimiter = Plugin::$plugin->service->getConfig('dataDelimiter');

        // Some fields require array, or multiple values like Elements, Checkboxes, etc, and we need to parse them differently.
        // Firstly, field mapping is setup like `MatrixBlock/Images` but actual feed is structured like `MatrixBlock/0/Images/0`.
        // We strip out the numbers to first find the node we've mapped to, then iterate over possible multiple values in the feed.
        foreach ($feedData as $nodePath => $nodeValue) {
            // Strip out array numbers in the feed path like: MatrixBlock/0/Images/0. We use this to get the field
            // its supposed to match up with, which is stored in the DB like MatrixBlock/Images
            $feedPath = preg_replace('/(\/\d+\/)/', '/', $nodePath);
            $feedPath = preg_replace('/^(\d+\/)|(\/\d+)/', '', $feedPath);

            if ($feedPath == $node || $nodePath == $node) {
                if ($nodeValue === null || $nodeValue === '') {
                    $nodeValue = $default;
                }

                // Allow pipes '|' to denote multiple items, but even if it doesn't contain one, explode will create
                // an array, so ensure to merge with the current results.
                if (is_string($nodeValue) && strpos($nodeValue, $dataDelimiter) !== false) {
                    $delimitedValues = explode($dataDelimiter, $nodeValue);

                    // Trim values in case whitespace was used between delimiter
                    $delimitedValues = array_map('trim', $delimitedValues);

                    $value = array_merge($value, $delimitedValues);
                } else {
                    $value[] = $nodeValue;
                }
            }
        }

        // Check if not importing, just using default
        if ($node === 'usedefault' && !$value) {
            if (!is_array($default)) {
                $default = [$default];
            }
            $value = $default;
        }

        return $value;
    }

    /**
     * @param $feedData
     * @param $fieldInfo
     * @return array|\ArrayAccess|mixed|null
     */
    public static function fetchValue($feedData, $fieldInfo)
    {
        $value = [];

        $node = Hash::get($fieldInfo, 'node');
        $default = Hash::get($fieldInfo, 'default');

        $dataDelimiter = Plugin::$plugin->service->getConfig('dataDelimiter');

        // Some fields require array, or multiple values like Elements, Checkboxes, etc, and we need to parse them differently.
        // Firstly, field mapping is setup like `MatrixBlock/Images` but actual feed is structured like `MatrixBlock/0/Images/0`.
        // We strip out the numbers to first find the node we've mapped to, then iterate over possible multiple values in the feed.
        foreach ($feedData as $nodePath => $nodeValue) {
            // Strip out array numbers in the feed path like: MatrixBlock/0/Images/0. We use this to get the field
            // its supposed to match up with, which is stored in the DB like MatrixBlock/Images
            $feedPath = preg_replace('/(\/\d+\/)/', '/', $nodePath);
            $feedPath = preg_replace('/^(\d+\/)|(\/\d+)/', '', $feedPath);

            if ($feedPath == $node || $nodePath == $node) {
                if ($nodeValue === null || $nodeValue === '') {
                    $nodeValue = $default;
                }

                // Allow pipes '|' to denote multiple items, but even if it doesn't contain one, explode will create
                // an array, so ensure to merge with the current results.
                if (is_string($nodeValue) && strpos($nodeValue, $dataDelimiter) !== false) {
                    $delimitedValues = explode($dataDelimiter, $nodeValue);

                    // Trim values in case whitespace was used between delimiter
                    $delimitedValues = array_map('trim', $delimitedValues);

                    $value = array_merge($value, $delimitedValues);
                } else {
                    $value[] = $nodeValue;
                }
            }
        }

        // Help to normalise things if an array with only one item. Probably a better idea to offload this to each
        // attribute of field definition, as its quite an assumption at this point...
        if (count($value) === 1) {
            $value = $value[0];
        }

        // Check if not importing, just using default
        if ($node === 'usedefault' && !$value) {
            $value = $default;
        }

        // We want to preserve 0 and '0', but if it's empty, return null.
        // https://github.com/craftcms/feed-me/issues/779
        if (!is_numeric($value) && empty($value)) {
            return null;
        }

        return $value;
    }

    /**
     * @param $value
     * @param $element
     * @return mixed|string
     */
    public static function parseFieldDataForElement($value, $element)
    {
        if (is_string($value) && strpos($value, '{') !== false) {
            // Make sure to wrap in try/catch, as if this is a literal '{' in content somewhere
            // it won't be a field handle tag, causing the Twig Lexer to freak out. We ignore those errors
            try {
                $value = Craft::$app->getView()->renderObjectTemplate($value, $element);
            } catch (\Throwable $e) {

            }
        }

        return $value;
    }

    /**
     * @param $content
     * @param $element
     * @return bool
     */
    public static function compareElementContent($content, $element)
    {
        if (!$element) {
            return false;
        }

        $trackedChanges = $content;

        $fields = $element->getSerializedFieldValues();
        $attributes = $element->attributes;

        foreach ($content as $key => $newValue) {
            $existingValue = Hash::get($fields, $key);

            // If date value, make sure to cast it as a string to compare
            if ($newValue instanceof \DateTime || DateTimeHelper::isIso8601($newValue)) {
                $newValue = Db::prepareDateForDb($newValue);
            }

            // If an empty 'date' value, it's the same as null
            if (is_array($newValue) && isset($newValue['date']) && $newValue['date'] === '') {
                $newValue = null;
            }

            // Check for simple fields first
            if (self::_compareSimpleValues($fields, $key, $existingValue, $newValue)) {
                unset($trackedChanges[$key]);
                continue;
            }

            // Then check for simple attributes
            $existingValue = Hash::get($attributes, $key);

            // Check for attribute groups - more than simple asset
            if ($key === 'groups') {
                $groups = $element->getGroups();

                foreach ($groups as $k => $group) {
                    $groups[$k] = $group->id;
                }

                $existingValue = $groups;
            }

            if (self::_compareSimpleValues($fields, $key, $existingValue, $newValue)) {
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

            Plugin::info('Data to update for `{i}`: `{j}`.', ['i' => $key, 'j' => json_encode($newValue)]);
        }

        if (empty($trackedChanges)) {
            return true;
        }
    }

    /**
     * @param $array1
     * @param $array2
     * @return false
     */
    public static function arrayCompare($array1, $array2)
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
        /** @noinspection TypeUnsafeComparisonInspection */
        // String length comparison to take into account "637" and "0637"
        // Should probably do a strict check, but doing this for backwards compatibility.
        if (is_string($firstValue) && is_string($secondValue)) {
            //Only do this when you know you're dealing with strings!
            $strlenCheck = mb_strlen($firstValue) === mb_strlen($secondValue);
        }
        else {
            //Default behaviour before string length checks were introduced (and caused type errors)
            $strlenCheck = true;
        }
        return Hash::check($fields, $key) && ($firstValue == $secondValue) && $strlenCheck;
    }
}
