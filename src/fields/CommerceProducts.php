<?php

namespace craft\feedme\fields;

use Cake\Utility\Hash;
use Craft;
use craft\commerce\elements\Product as ProductElement;
use craft\commerce\fields\Products;
use craft\commerce\fields\Variants;
use craft\commerce\Plugin as CommercePlugin;
use craft\feedme\base\Field;
use craft\feedme\base\FieldInterface;
use craft\feedme\helpers\DataHelper;
use craft\feedme\helpers\FieldHelper;
use craft\feedme\models\FeedModel;
use craft\feedme\Plugin;
use craft\fields\BaseRelationField;
use craft\helpers\Db;
use craft\helpers\Json;
use Illuminate\Support\Collection;

/**
 *
 * @property-read string $mappingTemplate
 */
class CommerceProducts extends Field implements FieldInterface
{
    // Properties
    // =========================================================================

    /**
     * @var string
     */
    public static string $name = 'CommerceProducts';

    /**
     * @var string
     */
    public static string $class = Products::class;

    /**
     * @var string
     */
    public static string $elementType = ProductElement::class;

    // Templates
    // =========================================================================

    /**
     * @inheritDoc
     */
    public function getMappingTemplate(): string
    {
        return 'feed-me/_includes/fields/commerce_products';
    }

    // Public Methods
    // =========================================================================

    /**
     * @inheritDoc
     */
    public function parseField(): mixed
    {
        $value = $this->fetchArrayValue();
        $default = $this->fetchDefaultArrayValue();

        // if the mapped value is not set in the feed
        if ($value === null) {
            return null;
        }

        $match = Hash::get($this->fieldInfo, 'options.match', 'title');
        $specialMatchCase = in_array($match, ['title', 'slug']);

        // if value from the feed is empty and default is not set
        // return an empty array; no point bothering further
        if (empty($default) && DataHelper::isArrayValueEmpty($value, $specialMatchCase)) {
            return [];
        }

        $sources = Hash::get($this->field, 'settings.sources');
        $limit = Hash::get($this->field, 'settings.maxRelations');
        $targetSiteId = Hash::get($this->field, 'settings.targetSiteId');
        $feedSiteId = Hash::get($this->feed, 'siteId');
        $node = Hash::get($this->fieldInfo, 'node');

        $typeIds = [];

        if (is_array($sources)) {
            foreach ($sources as $source) {
                [, $uid] = explode(':', $source);
                $typeIds[] = Db::idByUid('{{%commerce_producttypes}}', $uid);
            }
        } elseif ($sources === '*') {
            $typeIds = null;
        }

        $foundElements = [];

        foreach ($value as $dataValue) {
            // Prevent empty or blank values (string or array), which match all elements
            // but sometimes allow for zeros
            if (empty($dataValue) && empty($default) && ($specialMatchCase && !is_numeric($dataValue))) {
                continue;
            }

            // If we're using the default value - skip, we've already got an id array
            if ($node === 'usedefault') {
                $foundElements = $value;
                break;
            }

            // special provision for falling back on default BaseRelationField value
            // https://github.com/craftcms/feed-me/issues/1195
            if (DataHelper::isArrayValueEmpty($value)) {
                $foundElements = $default;
                break;
            }

            $query = ProductElement::find();

            // In multi-site, there's currently no way to query across all sites - we use the current site
            // See https://github.com/craftcms/cms/issues/2854
            if (Craft::$app->getIsMultiSite()) {
                if ($targetSiteId) {
                    $criteria['siteId'] = Craft::$app->getSites()->getSiteByUid($targetSiteId)->id;
                } elseif ($feedSiteId) {
                    $criteria['siteId'] = $feedSiteId;
                } else {
                    $criteria['siteId'] = Craft::$app->getSites()->getCurrentSite()->id;
                }
            }

            $criteria['status'] = null;
            $criteria['typeId'] = $typeIds;
            $criteria['limit'] = $limit;
            // prep the $dataValue for matching
            $criteria[$match] = DataHelper::prepValueForElementMatch($dataValue);

            Craft::configure($query, $criteria);

            Plugin::info('Search for existing product with query `{i}`', ['i' => Json::encode($criteria)]);

            $ids = $query->ids();

            $foundElements = array_merge($foundElements, $ids);

            Plugin::info('Found `{i}` existing products: `{j}`', ['i' => count($foundElements), 'j' => Json::encode($foundElements)]);
        }

        // Check for field limit - only return the specified amount
        if ($foundElements && $limit) {
            $foundElements = array_chunk($foundElements, $limit)[0];
        }

        $foundElements = array_unique($foundElements);

        // Protect against sending an empty array - removing any existing elements
        if (!$foundElements) {
            return null;
        }

        return $foundElements;
    }

    /**
     * Returns an array of custom fields that can be used when querying for matching products.
     *
     * If a field is passed, use the field layouts linked to the sources allowed by the Commerce Products field.
     * If all the sources are native (product types), then only fields from all those product types' field layouts will be returned.
     * If there's at least one custom source in the mix, the above list will be followed by a list of all the fields.
     * If only custom sources are selected, return all the fields in the installation.
     *
     * @param FeedModel $feed
     * @param BaseRelationField|null $field
     * @return array
     */
    public static function getMatchFields(FeedModel $feed, ?BaseRelationField $field = null): array
    {
        $fieldLayoutParam = 'fieldLayoutId';
        if ($field instanceof Variants) {
            $fieldLayoutParam = 'variantFieldLayoutId';
        }

        // The field will be null e.g. when importing into a structure product type, and there's the option to select a parent
        // the parent is serviced by the field markup too, but it doesn't tie into a custom field per se;
        if ($field === null) {
            $productType = CommercePlugin::getInstance()->getProductTypes()->getProductTypeById($feed->elementGroup[ProductElement::class]['productType']);
            if (!$productType) {
                return FieldHelper::getAllUniqueIdFields();
            }

            $fieldLayout = null;
            if ($productType->$fieldLayoutParam) {
                $fieldLayout = Craft::$app->getFields()->getLayoutById($productType->$fieldLayoutParam);
            }

            if (!$fieldLayout) {
                return FieldHelper::getAllUniqueIdFields();
            }

            return array_filter(
                $fieldLayout->getCustomFields(),
                fn($field) => FieldHelper::fieldCanBeUniqueId($field)
            );
        } else {
            // if the Commerce Products field has only custom sources - we have no choice but return all the field
            if (FieldHelper::fieldHasOnlyCustomSources($field)) {
                return FieldHelper::getAllUniqueIdFields();
            }

            // deal with the native sources - sections
            $productTypes = FieldHelper::getCommerceProductSourcesByField($field);

            $allowedFields = [];
            $productTypes = Collection::make($productTypes)->keyBy('id');

            foreach ($productTypes as $productType) {
                if ($productType->$fieldLayoutParam) {
                    $fieldLayout = Craft::$app->getFields()->getLayoutById($productType->$fieldLayoutParam);
                    if ($fieldLayout) {
                        $allowedFields = [...$allowedFields, ...$fieldLayout->getCustomFields()];
                    }
                }
            }

            // if there's a custom source in the mix, we should add all the fields too
            $customSources = [];
            if (is_array($field['sources'])) {
                $customSources = array_filter($field['sources'], (fn(string $source) => str_starts_with($source, 'custom:')));
            }

            if (!empty($customSources)) {
                $allowedFields = [...$allowedFields, ...Craft::$app->getFields()->getAllFields()];
            }

            return array_filter($allowedFields, fn($field) => FieldHelper::fieldCanBeUniqueId($field));
        }
    }
}
