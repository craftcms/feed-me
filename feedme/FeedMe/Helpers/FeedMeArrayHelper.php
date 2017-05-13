<?php
namespace Craft;

use Cake\Utility\Hash as Hash;

class FeedMeArrayHelper
{
    // Public Methods
    // =========================================================================

    public static function multiExplode($delimiters, $string)
    {
        $ready = str_replace($delimiters, '/', $string);
        $launch = explode('/', $ready);
        return $launch;
    }

    public static function arraySet(&$array, $keys, $value)
    {
        while (count($keys) > 1) {
            $key = array_shift($keys);

            // If the key doesn't exist at this depth, we will just create an empty array
            // to hold the next value, allowing us to create the arrays to hold final
            // values at the correct depth. Then we'll keep digging into the array.
            if (! isset($array[$key]) || ! is_array($array[$key])) {
                $array[$key] = [];
            }

            $array = &$array[$key];
        }

        $array[array_shift($keys)] = $value;

        return $array;
    }

    public static function arrayGet($array, $key, $default = null)
    {
        if (is_null($key)) {
            return $array;
        }

        if (isset($array[$key])) {
            return $array[$key];
        }

        // Store resulting array if key contains wildcard.
        $deepArray = array();
        $keys = preg_split('/\^|\./', $key);

        foreach ($keys as $n => $segment) {
            if ($segment == '*') {
                // Get the rest of the keys besides current one.
                $keySlice = array_slice($keys, $n+1);
                // Generate new dot notation key string.
                $innerKey = implode('^', $keySlice);

                if (is_array($array)) {
                    foreach ($array as $d => $item) {
                        // Empty slice - last segment is a wildcard.
                        if (empty($keySlice)) {
                            // Last segment is a wildcard. Put item into deepArray which will be returned
                            // containing all of the items of the current array.
                            $deepArray[] = $item;
                        } else {
                            // Pass current array item deeper.
                            $innerItem = FeedMeArrayHelper::arrayGet($item, $innerKey, $default);

                            if (is_array($innerItem) and count(array_keys($keys, '*')) > 1) {
                                // Multiple wildcards, add each item of inner array to the resulting new array.
                                foreach ($innerItem as $innerItem) {
                                    $deepArray[$d][] = $innerItem;
                                }
                            } else {
                                // Only one wildcard in current key string. Add whole inner array to the resulting array.
                                $deepArray[] = $innerItem;
                            }
                        }
                    }

                    // Return new resulting array.
                    return $deepArray;
                } elseif ($n == count($keys) - 1) {
                    // This is the last key, so we can simply return whole array.
                    return $array;
                } else {
                    // This is not the last key and $array is not really an array
                    // so we can't proceed deeper. Return default.
                    return $default;
                }
            } elseif (!is_array($array) || !array_key_exists($segment, $array)) {
                return $default;
            }

            $array = $array[$segment];
        }

        return $array;
    }

    public static function findByPartialKey($array, $partialKey)
    {
        $results = array();

        foreach ($array as $key => $value) {
            if (strstr($key, $partialKey)) {
                $results[$key] = $value;
            }
        }

        return $results;
    }

    public static function findKeyByValue($array, $findValue)
    {
        $results = array();
        
        foreach ($array as $key => $value) {
            if ($findValue == $value) {
                $results[$key] = $value;
            }
        }

        return $results;
    }
}