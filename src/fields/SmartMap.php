<?php
namespace verbb\feedme\fields;

use verbb\feedme\base\Field;
use verbb\feedme\base\FieldInterface;
use verbb\feedme\helpers\DataHelper;

use Cake\Utility\Hash;

class SmartMap extends Field implements FieldInterface
{
    // Properties
    // =========================================================================

    public static $name = 'SmartMap';
    public static $class = 'doublesecretagency\smartmap\fields\Address';


    // Templates
    // =========================================================================

    public function getMappingTemplate()
    {
        return 'feed-me/_includes/fields/smart-map';
    }


    // Public Methods
    // =========================================================================

    public function parseField()
    {
        $preppedData = [];

        $fields = Hash::get($this->fieldInfo, 'fields');

        foreach ($fields as $subFieldHandle => $subFieldInfo) {
            $preppedData[$subFieldHandle] = DataHelper::fetchSimpleValue($this->feedData, $subFieldInfo);
        }

        return $preppedData;
    }

}