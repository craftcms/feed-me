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

        // we want the values in the feed to look like regular numbers,
        // to be independent of the formatting locale
        // for example if your money field is in EUR, the amount of
        // one thousand two hundred thirty-four euro and fifty-six cents
        // should be: 1234.56 in your feed;
        // not 1234,56 or 1,234.56 or 1.234,56 - those values are all locale dependant strings
        // not float-like values
        return [
            'value' => $value,
            'locale' => 'en',
        ];
    }
}
