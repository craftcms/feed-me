<?php

namespace craft\feedme\fields;

use craft\feedme\base\Field;
use craft\feedme\base\FieldInterface;
use craft\helpers\Json;

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
    public static string $name = 'Default';

    /**
     * @var string
     */
    public static string $class = 'craft\fields\Default';

    // Templates
    // =========================================================================

    /**
     * @inheritDoc
     */
    public function getMappingTemplate(): string
    {
        return 'feed-me/_includes/fields/default';
    }

    // Public Methods
    // =========================================================================

    /**
     * @inheritDoc
     */
    public function parseField(): mixed
    {
        $value = $this->fetchValue();

        // Default fields expect strings, if it's an array for an odd reason, serialise it
        if (is_array($value)) {
            if (empty($value)) {
                $value = '';
            } else {
                $value = Json::encode($value);
            }
        }

        // If it's exactly an empty string, that's okay and allowed. Normalising this will set it to
        // null, which means it won't get imported. Sometimes we want to have empty strings
        if ($value !== '') {
            $value = $this->field->normalizeValue($value);
        }

        // Lastly, get each field to prepare values how they should
        return $this->field->serializeValue($value);
    }
}
