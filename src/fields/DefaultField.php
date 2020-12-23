<?php

namespace craft\feedme\fields;

use craft\feedme\base\Field;
use craft\feedme\base\FieldInterface;

/**
 *
 * @property-read string $mappingTemplate
 */
class DefaultField extends Field implements FieldInterface
{
    // Properties
    // =========================================================================

    /**
     * @var string
     */
    public static $name = 'Default';

    /**
     * @var string
     */
    public static $class = 'craft\fields\Default';

    // Templates
    // =========================================================================

    /**
     * @inheritDoc
     */
    public function getMappingTemplate()
    {
        return 'feed-me/_includes/fields/default';
    }

    // Public Methods
    // =========================================================================

    /**
     * @inheritDoc
     */
    public function parseField()
    {
        $value = $this->fetchValue();

        // Default fields expect strings, if its an array for an odd reason, serialise it
        if (is_array($value)) {
            if (empty($value)) {
                $value = '';
            } else {
                $value = json_encode($value);
            }
        }

        // If its exactly an empty string, that's okay and allowed. Normalising this will set it to
        // null, which means it won't get imported. Some times we want to have empty strings
        if ($value !== '') {
            $value = $this->field->normalizeValue($value);
        }

        // Lastly, get each field to prepare values how they should
        $value = $this->field->serializeValue($value);

        return $value;
    }
}
