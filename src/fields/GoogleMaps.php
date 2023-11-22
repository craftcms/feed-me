<?php

namespace semabit\feedme\fields;

use Cake\Utility\Hash;
use Craft;
use semabit\feedme\base\Field;
use semabit\feedme\base\FieldInterface;
use semabit\feedme\helpers\DataHelper;

/**
 *
 * @property-read string $mappingTemplate
 */
class GoogleMaps extends Field implements FieldInterface
{
    // Properties
    // =========================================================================

    /**
     * @var string
     */
    public static string $name = 'GoogleMaps';

    /**
     * @var string
     */
    public static string $class = 'doublesecretagency\googlemaps\fields\AddressField';

    // Templates
    // =========================================================================

    /**
     * @inheritDoc
     */
    public function getMappingTemplate(): string
    {
        // By default, assume a modern version
        $pre43 = false;

        // Get plugins service
        $plugins = Craft::$app->getPlugins();

        // If the Google Maps plugin is installed & enabled
        if ($plugins->isPluginEnabled('google-maps')) {
            // Get info for Google Maps plugin
            $info = $plugins->getPluginInfo('google-maps');
            // Whether Google Maps plugin version is earlier than 4.3
            $pre43 = (version_compare($info['version'], '4.3', '<'));
        }

        // If earlier than Google Maps v4.3, return old version of the template
        if ($pre43) {
            return 'feed-me/_includes/fields/google-maps-before-4-3';
        }

        // By default, return the modern version of the template
        return 'feed-me/_includes/fields/google-maps';
    }

    // Public Methods
    // =========================================================================

    /**
     * @inheritDoc
     */
    public function parseField(): mixed
    {
        $preppedData = [];

        $fields = Hash::get($this->fieldInfo, 'fields');

        if (!$fields) {
            return null;
        }

        foreach ($fields as $subFieldHandle => $subFieldInfo) {
            $value = DataHelper::fetchValue($this->feedData, $subFieldInfo, $this->feed);
            if ($value !== null) {
                $preppedData[$subFieldHandle] = $value;
            }
        }

        // Protect against sending an empty array
        if (!$preppedData) {
            return null;
        }

        return $preppedData;
    }
}
