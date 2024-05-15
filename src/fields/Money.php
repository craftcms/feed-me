<?php

namespace craft\feedme\fields;

use Cake\Utility\Hash;
use Craft;
use craft\feedme\base\Field;
use craft\feedme\base\FieldInterface;
use craft\fields\Money as MoneyField;
use craft\helpers\Localization;

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
        return 'feed-me/_includes/fields/money';
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

        $localized = Hash::get($this->fieldInfo, 'options.localized');

        if ($localized) {
            // value provided in the feed should be localised (like with the Number field)

            // for example if the site you're importing to is Dutch (nl),
            // you checked the "Data provided for this localized for the site the feed is for" checkbox on the feed mapping screen
            // and your money field is in EUR,
            // the amount of: one thousand two hundred thirty-four euro and fifty-six cents
            // should be: 1.234,56 in your feed;
            $site = Craft::$app->getSites()->getSiteById($this->feed['siteId']);
            $siteLocaleId = $site->getLocale()->id;
        } else {
            // the values in the feed are in a float-like notation

            // for example if the site you're importing to is Dutch (nl),
            // you DIDN'T check the "Data provided for this localized for the site the feed is for" checkbox on the feed mapping screen
            // and your money field is in EUR,
            // one thousand two hundred thirty-four euro and fifty-six cents
            // should be: 1234.56 in your feed;
            $siteLocaleId = 'en';
        }

        return [
            'value' => Localization::normalizeNumber($value, $siteLocaleId),
            'locale' => 'en',
        ];
    }
}
