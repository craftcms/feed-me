<?php

namespace craft\feedme\fields;

use Cake\Utility\Hash;
use craft\feedme\base\Field;
use craft\feedme\base\FieldInterface;
use craft\feedme\helpers\DataHelper;
use ether\simplemap\models\Map;
use ether\simplemap\services\MapService;
use ether\simplemap\SimpleMap as SimpleMapPlugin;

class SimpleMap extends Field implements FieldInterface
{
    // Properties
    // =========================================================================

    public static $name = 'SimpleMap';
    public static $class = 'ether\simplemap\fields\MapField';


    // Templates
    // =========================================================================

    public function getMappingTemplate()
    {
        return 'feed-me/_includes/fields/simple-map';
    }


    // Public Methods
    // =========================================================================

    public function parseField()
    {
        $preppedData = [];

        $fields = Hash::get($this->fieldInfo, 'fields');

        if (!$fields) {
            return null;
        }

        foreach ($fields as $subFieldHandle => $subFieldInfo) {
            $preppedData[$subFieldHandle] = DataHelper::fetchValue($this->feedData, $subFieldInfo);
        }

        // In order to full-fill any empty gaps in data (lng/lat/address), we check to see if we have any data missing
        // then, request that data through Google's geocoding API - making for a hands-free import.

        // Check for empty Address
        if (!isset($preppedData['address'])) {
            if ((isset($preppedData['lat']) && !empty($preppedData['lat'])) && (isset($preppedData['lng']) && !empty($preppedData['lng']))) {
                $addressInfo = $this->_getAddressFromLatLng($preppedData['lat'], $preppedData['lng']);
                $preppedData['address'] = $addressInfo['formatted_address'];

                // Populate address parts
                if (isset($addressInfo['address_components'])) {
                    foreach ($addressInfo['address_components'] as $component) {
                        $preppedData['parts'][$component['types'][0]] = $component['long_name'];
                        $preppedData['parts'][$component['types'][0] . '_short'] = $component['short_name'];
                    }
                }
            }
        }

        // Check for empty Longitude/Latitude
        if (!isset($preppedData['lat']) || !isset($preppedData['lng'])) {
            if (isset($preppedData['address'])) {
                $latlng = MapService::getLatLngFromAddress($preppedData['address']);
                $preppedData['lat'] = $latlng['lat'];
                $preppedData['lng'] = $latlng['lng'];
            }
        }

        if (isset($preppedData['parts'])) {
            $preppedData['parts'] = json_encode($preppedData['parts']);
        }

        // Protect against sending an empty array
        if (!$preppedData) {
            return null;
        }

        return $preppedData;
    }

    // Private Methods
    // =========================================================================

    private function _getAddressFromLatLng($lat, $lng)
    {
        $apiKey = SimpleMapPlugin::getInstance()->getSettings()->geoToken;

        if (!$apiKey) {
            $apiKey = SimpleMapPlugin::getInstance()->getSettings()->mapToken;
        }

        if (!$apiKey) {
            return null;
        }

        $url = 'https://maps.googleapis.com/maps/api/geocode/json?latlng=' . rawurlencode($lat) . ',' . rawurlencode($lng) . '&key=' . $apiKey;

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        $resp = json_decode(curl_exec($ch), true);

        if (empty($resp['results'])) {
            return null;
        }

        return $resp['results'][0];
    }

}
