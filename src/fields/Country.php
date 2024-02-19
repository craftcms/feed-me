<?php

namespace craft\feedme\fields;

use Cake\Utility\Hash;
use Craft;
use craft\feedme\base\Field;
use craft\feedme\base\FieldInterface;
use craft\fields\Country as CountryField;

/**
 *
 * @property-read string $mappingTemplate
 */
class Country extends Field implements FieldInterface
{
    // Properties
    // =========================================================================

    /**
     * @var string
     */
    public static string $name = 'Country';

    /**
     * @var string
     */
    public static string $class = CountryField::class;


    // Templates
    // =========================================================================

    /**
     * @inheritDoc
     */
    public function getMappingTemplate(): string
    {
        return 'feed-me/_includes/fields/country';
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

        $value = (string) $value;
        $default = Hash::get($this->fieldInfo, 'default');

        $options = Craft::$app->getAddresses()->getCountryRepository()->getList(Craft::$app->language);

        $match = Hash::get($this->fieldInfo, 'options.match', 'value');

        // if we're matching by value - just look for the key in the $options array or check the default
        if (($match === 'value' && isset($options[$value])) || $default === $value) {
            return $value;
        }

        // if we're looking by label - look for the keys
        if ($match === 'label') {
            $found = array_filter($options, function($option) use ($value) {
                 return strcasecmp($option, $value) === 0;
            });

            if (!empty($found)) {
                return array_key_first($found);
            }
        }

        if (empty($value)) {
            return $value;
        }

        return null;
    }

    /**
     * @inheritDoc
     */
    public function fetchValue(): mixed
    {
        return (string) parent::fetchValue();
    }
}
