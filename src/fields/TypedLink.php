<?php
namespace verbb\feedme\fields;

use verbb\feedme\FeedMe;
use verbb\feedme\base\Field;
use verbb\feedme\base\FieldInterface;
use verbb\feedme\helpers\DataHelper;

use Craft;
use craft\helpers\Localization;

use Cake\Utility\Hash;

class TypedLink extends Field implements FieldInterface
{
    // Properties
    // =========================================================================

    public static $name = 'TypedLink';
    public static $class = 'typedlinkfield\fields\LinkField';


    // Templates
    // =========================================================================

    public function getMappingTemplate()
    {
        return 'feed-me/_includes/fields/typed-link';
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

        // Protect against sending an empty array
        if (!$preppedData) {
            return null;
        }

        return $preppedData;
    }

}