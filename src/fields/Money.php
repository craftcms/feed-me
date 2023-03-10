<?php

namespace craft\feedme\fields;

use craft\feedme\base\Field;
use craft\feedme\base\FieldInterface;
use craft\fields\Money as MoneyField;

/**
 *
 * @property-read string $mappingTemplate
 */
class Money extends Field implements FieldInterface
{
    // Properties
    // =========================================================================

    /**
     * @var string
     */
    public static string $name = 'Money';

    /**
     * @var string
     */
    public static string $class = MoneyField::class;

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

        if ($value === null) {
            return null;
        }

        return [
            'value' => $value,
        ];
    }
}
