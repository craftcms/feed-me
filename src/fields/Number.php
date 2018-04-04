<?php
namespace verbb\feedme\fields;

use verbb\feedme\base\Field;
use verbb\feedme\base\FieldInterface;

use Craft;
use craft\helpers\Localization;

use Cake\Utility\Hash;

class Number extends Field implements FieldInterface
{
    // Properties
    // =========================================================================

    public static $name = 'Number';
    public static $class = 'craft\fields\Number';


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
        $value = $this->fetchSimpleValue();
        
        return Localization::normalizeNumber($value);
    }
    
}