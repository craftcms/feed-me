<?php
namespace verbb\feedme\fields;

use verbb\feedme\FeedMe;
use verbb\feedme\base\Field;
use verbb\feedme\base\FieldInterface;
use verbb\feedme\helpers\DataHelper;

use Craft;
use craft\helpers\Localization;

use Cake\Utility\Hash;

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

        foreach ($fields as $subFieldHandle => $subFieldInfo) {
            $preppedData[$subFieldHandle] = DataHelper::fetchValue($this->feedData, $subFieldInfo);
        }

        $preppedData['type'] = 'fruitstudios\linkit\models\Url';

        // if (isset($preppedData['custom'])) {
        //     $preppedData['values']['fruitstudios\linkit\models\Url'] = $preppedData['custom'];
        //     unset($preppedData['custom']);
        // }

        return $preppedData;
    }

}