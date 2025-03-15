<?php

namespace craft\feedme\fields;

use Cake\Utility\Hash;
use craft\feedme\base\Field;
use craft\feedme\base\FieldInterface;
use craft\feedme\helpers\DateHelper;
use craft\fields\Time as TimeField;

/**
 *
 * @property-read string $mappingTemplate
 */
class Time extends Field implements FieldInterface
{
    // Properties
    // =========================================================================

    /**
     * @var string
     */
    public static string $name = 'Time';

    /**
     * @var string
     */
    public static string $class = TimeField::class;

    // Templates
    // =========================================================================

    /**
     * @inheritDoc
     */
    public function getMappingTemplate(): string
    {
        return 'feed-me/_includes/fields/time';
    }

    // Public Methods
    // =========================================================================

    /**
     * @inheritDoc
     */
    public function parseField(): mixed
    {
        $value = $this->fetchValue();

        if ($value === null) {
            return null;
        }

        $formatting = Hash::get($this->fieldInfo, 'options.match') ?? 'auto';

        $timeValue = DateHelper::parseString($value, $formatting);

        if ($timeValue) {
            return $timeValue;
        }

        return $value;
    }
}
