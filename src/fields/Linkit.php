<?php

namespace craft\feedme\fields;

use Cake\Utility\Hash;
use craft\feedme\base\Field;
use craft\feedme\base\FieldInterface;
use craft\feedme\helpers\DataHelper;

class Linkit extends Field implements FieldInterface
{
    // Properties
    // =========================================================================

    public static $name = 'Linkit';
    public static $class = 'fruitstudios\linkit\fields\LinkitField';


    // Templates
    // =========================================================================

    public function getMappingTemplate()
    {
        return 'feed-me/_includes/fields/linkit';
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

        if ($preppedData)
        {
            // Handle Link Type
            $preppedData['type'] = empty($preppedData['type'] ?? '') ? 'fruitstudios\linkit\models\Url' : $preppedData['type'];
            if(strpos($preppedData['type'], '\\') === false)
            {
                $preppedData['type'] = 'fruitstudios\\linkit\\models\\'.ucfirst(strtolower(trim($preppedData['type'])));
            }

            // Handle Link Target
            $preppedData['target'] = trim(empty($preppedData['target'] ?? '') ? '' : $preppedData['target']);
            $preppedData['target'] = $preppedData['target'] && $preppedData['target'] != '_self' ? 1 : '';
        }

        // Protect against sending an empty array
        if (!$preppedData) {
            return null;
        }

        return $preppedData;
    }

}
