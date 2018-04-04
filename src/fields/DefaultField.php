<?php
namespace verbb\feedme\fields;

use verbb\feedme\base\Field;
use verbb\feedme\base\FieldInterface;

use Craft;

use Cake\Utility\Hash;

class DefaultField extends Field implements FieldInterface
{
    // Properties
    // =========================================================================

    public static $name = 'Default';
    public static $class = 'craft\fields\Default';


    // Templates
    // =========================================================================

    public function getMappingTemplate()
    {
        return 'feed-me/_includes/fields/default';
    }


    // Public Methods
    // =========================================================================

    public function parseField()
    {
        return $this->fetchSimpleValue();
    }
}