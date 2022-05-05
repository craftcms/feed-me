<?php

namespace craft\feedme\elements;

use Cake\Utility\Hash;
use Carbon\Carbon;
use Craft;
use craft\base\ElementInterface;
use craft\commerce\elements\Product as ProductElement;
use craft\commerce\elements\Variant as VariantElement;
use craft\commerce\Plugin as Commerce;
use craft\db\Query;
use craft\feedme\base\Element;
use craft\feedme\events\FeedProcessEvent;
use craft\feedme\helpers\BaseHelper;
use craft\feedme\helpers\DataHelper;
use craft\feedme\Plugin;
use craft\feedme\services\Process;
use craft\helpers\Json;
use DateTime;
use Exception;
use yii\base\Event;

/**
 *
 * @property-read string $mappingTemplate
 * @property-read mixed $groups
 * @property-write mixed $model
 * @property-read string $groupsTemplate
 * @property-read string $columnTemplate
 */
class CommerceProduct extends Element
{
    // Properties
    // =========================================================================

    /**
     * @var string
     */
    public static string $name = 'Commerce Product';

    /**
     * @var string
     */
    public static string $class = ProductElement::class;

    // Templates
    // =========================================================================

    /**
     * @inheritDoc
     */
    public function getGroupsTemplate(): string
    {
        return 'feed-me/_includes/elements/commerce-products/groups';
    }

    /**
     * @inheritDoc
     */
    public function getColumnTemplate(): string
    {
        return 'feed-me/_includes/elements/commerce-products/column';
    }

    /**
     * @inheritDoc
     */
    public function getMappingTemplate(): string
    {
        return 'feed-me/_includes/elements/commerce-products/map';
    }

    // Public Methods
    // =========================================================================

    /**
     * @inheritDoc
     */
    public function init(): void
    {
        parent::init();

        // Hook into the process service on each step - we need to re-arrange the feed mapping
        Event::on(Process::class, Process::EVENT_STEP_BEFORE_PARSE_CONTENT, function(FeedProcessEvent $event) {
            if ($event->feed['elementType'] === ProductElement::class) {
                $this->_preParseVariants($event);
            }
        });

        Event::on(Process::class, Process::EVENT_STEP_BEFORE_ELEMENT_MATCH, function(FeedProcessEvent $event) {
            if ($event->feed['elementType'] === ProductElement::class) {
                $this->_checkForVariantMatches($event);
            }
        });

        // Hook into the before element save event, because we need to do lots to prepare variant data
        Event::on(Process::class, Process::EVENT_STEP_BEFORE_ELEMENT_SAVE, function(FeedProcessEvent $event) {
            if ($event->feed['elementType'] === ProductElement::class) {
                $this->_parseVariants($event);
            }
        });
    }

    /**
     * @inheritDoc
     */
    public function getGroups(): array
    {
        if (Commerce::getInstance()) {
            return Commerce::getInstance()->getProductTypes()->getEditableProductTypes();
        }

        return [];
    }

    /**
     * @inheritDoc
     */
    public function getQuery($settings, array $params = []): mixed
    {
        $query = ProductElement::find()
            ->status(null)
            ->typeId($settings['elementGroup'][ProductElement::class])
            ->siteId(Hash::get($settings, 'siteId') ?: Craft::$app->getSites()->getPrimarySite()->id);
        Craft::configure($query, $params);
        
        return $query;
    }

    /**
     * @inheritDoc
     */
    public function setModel($settings): ElementInterface
    {
        $this->element = new ProductElement();
        $this->element->typeId = $settings['elementGroup'][ProductElement::class];

        $siteId = Hash::get($settings, 'siteId');

        if ($siteId) {
            $this->element->siteId = $siteId;
        }

        return $this->element;
    }

    /**
     * @inheritDoc
     */
    public function save($element, $settings): bool
    {
        $this->beforeSave($element, $settings);

        if (!Craft::$app->getElements()->saveElement($this->element, true, true, Hash::get($this->feed, 'updateSearchIndexes'))) {
            $errors = [$this->element->getErrors()];

            if ($this->element->getErrors()) {
                foreach ($this->element->getVariants() as $variant) {
                    if ($variant->getErrors()) {
                        $errors[] = $variant->getErrors();
                    }
                }

                throw new Exception(Json::encode($errors));
            }

            return false;
        }

        return true;
    }


    // Private Methods
    // =========================================================================

    /**
     * @param $event
     */
    private function _preParseVariants($event): void
    {
        $feed = $event->feed;

        // We need to re-arrange the feed-mapping from using variant-* to putting all these in a
        // variants[] array for easy management later. If we don't do this, it'll start processing
        // attributes and fields based on the top-level product, which is incorrect..
        foreach ($feed['fieldMapping'] as $fieldHandle => $fieldInfo) {
            if (str_contains($fieldHandle, 'variant-')) {
                // Add it to variants[]
                $attribute = str_replace('variant-', '', $fieldHandle);
                $feed['fieldMapping']['variants'][$attribute] = $fieldInfo;

                // Remove it from top-level mapping
                unset($feed['fieldMapping'][$fieldHandle]);
            }
        }

        // Save all our changes back to the event model
        $event->feed = $feed;
    }

    /**
     * @param $event
     */
    private function _checkForVariantMatches($event): void
    {
        $feed = $event->feed;
        $feedData = $event->feedData;
        $contentData = $event->contentData;

        // If we're trying to match an existing product element on a variant's content, we're not going to have much
        // luck. So instead, in here, we look up the parent product (if any), and return that. We directly modify the
        // unique content array $contentData, so we don't have to deal with any other shenanigans in core code.
        foreach ($contentData as $handle => $value) {
            if (str_contains($handle, 'variant-')) {
                $sku = null;

                $fieldInfo = Hash::get($feed, 'fieldMapping.variant-sku');
                $node = Hash::get($fieldInfo, 'node');

                // Because we're trying to find the parent product from a child variant, we just need to get the first
                // match - then we've got an SKU for a variant that belongs to the product we want.
                foreach ($feedData as $nodePath => $innerValue) {
                    $feedPath = preg_replace('/(\/\d+\/)/', '/', $nodePath);
                    $feedPath = preg_replace('/^(\d+\/)|(\/\d+)/', '', $feedPath);

                    if ($feedPath === $node) {
                        $sku = $innerValue;
                        break;
                    }
                }

                if (!$sku) {
                    continue;
                }

                $variant = $this->_getVariantBySku($sku);

                // Now, we want to directly modify the unique fields to instead of using the variant SKU, use the
                // product id. Note that we want to force this, even if we haven't found a variant, because trying to import
                // using variant-sku as the unique identifier won't go down so well - it won't create the products like it should
                $feed['fieldUnique']['id'] = '1';
                $contentData['id'] = $variant->productId ?? 0;

                // Cleanup
                unset($feed['fieldUnique'][$handle], $contentData[$handle]);
            }
        }

        // Save all our changes back to the event model
        $event->feed = $feed;
        $event->feedData = $feedData;
        $event->contentData = $contentData;
    }

    /**
     * @param $event
     */
    private function _parseVariants($event): void
    {
        $feed = $event->feed;
        $feedData = $event->feedData;
        $contentData = $event->contentData;
        $element = $event->element;

        $variantMapping = Hash::get($feed, 'fieldMapping.variants');

        // Check to see if there are any variants at all (there really should be...)
        if (!$variantMapping) {
            return;
        }

        $variantData = [];
        $variants = [];
        $complexFields = [];

        // Fetch any existing variants on the product, indexes by their SKU
        if (!empty($element->variants[0]['sku'])) {
            foreach ($element->variants as $value) {
                $variants[$value['sku']] = $value;
            }
        }

        // Weed out any non-variant mapped field
        $variantFieldsByNode = [];

        foreach (Hash::flatten($variantMapping) as $key => $value) {
            if (str_contains($key, 'node') && $value !== 'noimport' && $value !== 'usedefault') {
                $variantFieldsByNode[] = $value;
            }
        }

        // Now we need to find out how many variants we're importing - can even be one, and it's all a little tricky...
        foreach ($feedData as $nodePath => $value) {
            foreach ($variantMapping as $fieldHandle => $fieldInfo) {
                $node = Hash::get($fieldInfo, 'node');

                $feedPath = preg_replace('/(\/\d+\/)/', '/', $nodePath);
                $feedPath = preg_replace('/^(\d+\/)|(\/\d+)/', '', $feedPath);

                if (!in_array($feedPath, $variantFieldsByNode, true)) {
                    continue;
                }

                // Try and determine the index. We need to always be dealing with an array of variant data
                $nodePathSegments = explode('/', $nodePath);
                $variantIndex = Hash::get($nodePathSegments, 1);

                if (!is_numeric($variantIndex)) {
                    // Try to check if its only one-level deep (only importing one block type)
                    // which is particularly common for JSON.
                    $variantIndex = Hash::get($nodePathSegments, 2);

                    if (!is_numeric($variantIndex)) {
                        $variantIndex = 0;
                    }
                }

                $isMatrixField = (Hash::get($fieldInfo, 'field') === 'craft\fields\Matrix');

                if ($isMatrixField) {
                    $complexFields[$variantIndex][$fieldHandle]['info'] = $fieldInfo;
                    $complexFields[$variantIndex][$fieldHandle]['data'][$nodePath] = $value;
                    continue;
                }

                // Find the node in the feed (stripped of indexes) that matches what's stored in field mapping
                if ($feedPath === $node) {
                    // Store this information, so we can parse the field data later
                    if (!isset($variantData[$variantIndex][$fieldHandle])) {
                        $variantData[$variantIndex][$fieldHandle] = $fieldInfo;
                    }

                    $variantData[$variantIndex][$fieldHandle]['data'][$nodePath] = $value;
                }
            }
        }

        // A separate loop to sort out any defaults we might have (they need to be applied to each variant)
        // even though the data supplied for them is only provided once.
        foreach ($variantMapping as $fieldHandle => $fieldInfo) {
            foreach ($variantData as $variantNumber => $variantContent) {
                $node = Hash::get($fieldInfo, 'node');
                $default = Hash::get($fieldInfo, 'default');

                if ($node === 'usedefault') {
                    $variantData[$variantNumber][$fieldHandle] = $fieldInfo;
                    $variantData[$variantNumber][$fieldHandle]['data'][$fieldHandle] = $default;
                }
            }
        }

        foreach ($complexFields as $variantNumber => $complexInfo) {
            foreach ($complexInfo as $fieldHandle => $fieldInfo) {
                $variantNodePathKey = null;

                // Refrain from looking at the whole nodepath, really just want to find the first bits
                foreach ($fieldInfo['data'] as $nodePath => $value) {
                    $nodePathExcerpt = implode('/', array_slice(explode('/', $nodePath), 0, 3));

                    preg_match('/^(.*)\d+\//U', $nodePathExcerpt, $matches);

                    $variantNodePathKey = Hash::get($matches, '1');

                    if ($variantNodePathKey) {
                        break;
                    }
                }

                // Likely, we've only got a single variant in our import, so we'll assume `variants/variant`
                if (!$variantNodePathKey) {
                    foreach ($fieldInfo['data'] as $nodePath => $value) {
                        $variantNodePathKey = implode('/', array_slice(explode('/', $nodePath), 0, 2)) . '/';
                        break;
                    }
                }

                $alteredData = [];

                foreach (Hash::flatten($fieldInfo) as $key => $value) {
                    $key = str_replace([$variantNodePathKey . $variantNumber . '/', $variantNodePathKey], '', $key);

                    $value = str_replace([$variantNodePathKey . $variantNumber . '/', $variantNodePathKey], '', $value);

                    $alteredData[$key] = $value;
                }

                $fieldInfo = Hash::expand($alteredData);

                $variantData[$variantNumber][$fieldHandle] = $fieldInfo['info'];
                $variantData[$variantNumber][$fieldHandle]['data'] = $fieldInfo['data'];
            }
        }

        foreach ($variantData as $variantContent) {
            $attributeData = [];
            $fieldData = [];

            // Parse just the element attributes first. We use these in our field contexts, and need a fully-prepped element
            foreach ($variantContent as $fieldHandle => $fieldInfo) {
                if (Hash::get($fieldInfo, 'attribute')) {
                    $attributeValue = DataHelper::fetchValue(Hash::get($fieldInfo, 'data'), $fieldInfo);

                    $attributeData[$fieldHandle] = $attributeValue;
                }
            }

            // If there's no SKU in the feed to process, we can't go any further, because we can very likely produce
            // errors if we try to import a variant that already has an SKU - instead we need to grab and edit it
            $sku = Hash::get($attributeData, 'sku');

            if (!$sku) {
                continue;
            }

            // Create a new variant, or find an existing one to edit
            if (!isset($variants[$sku])) {
                $variants[$sku] = new VariantElement();
            }

            $variants[$sku]->product = $element;

            // Set the attributes for the element
            $variants[$sku]->setAttributes($attributeData, false);

            // Then, do the same for custom fields. Again, this should be done after populating the element attributes
            foreach ($variantContent as $fieldHandle => $fieldInfo) {
                if (Hash::get($fieldInfo, 'field')) {
                    $data = Hash::get($fieldInfo, 'data');

                    $fieldValue = Plugin::$plugin->fields->parseField($feed, $element, $data, $fieldHandle, $fieldInfo);

                    if ($fieldValue !== null) {
                        $fieldData[$fieldHandle] = $fieldValue;
                    }
                }
            }

            // Do the same with our custom field data
            $variants[$sku]->setFieldValues($fieldData);

            // Add to our contentData variable for debugging
            $contentData['variants'][] = $attributeData + $fieldData;
        }

        // Set the products variants
        $element->setVariants($variants);

        // Save all our changes back to the event model
        $event->feed = $feed;
        $event->feedData = $feedData;
        $event->contentData = $contentData;
        $event->element = $element;
    }

    /**
     * @param $sku
     * @param null $siteId
     * @return VariantElement
     */
    private function _getVariantBySku($sku, $siteId = null): VariantElement
    {
        $variant = VariantElement::find()
            ->sku($sku)
            ->status(null)
            ->limit(null)
            ->typeId($this->element->typeId)
            ->siteId($siteId)
            ->one();

        if ($variant) {
            return $variant;
        }

        return new VariantElement();
    }


    // Protected Methods
    // =========================================================================

    /**
     * @param $feedData
     * @param $fieldInfo
     * @return array|Carbon|DateTime|false|string|null
     * @throws Exception
     */
    protected function parsePostDate($feedData, $fieldInfo): DateTime|bool|array|Carbon|string|null
    {
        $value = $this->fetchSimpleValue($feedData, $fieldInfo);
        $formatting = Hash::get($fieldInfo, 'options.match');

        return $this->parseDateAttribute($value, $formatting);
    }

    /**
     * @param $feedData
     * @param $fieldInfo
     * @return array|Carbon|DateTime|false|string|null
     * @throws Exception
     */
    protected function parseExpiryDate($feedData, $fieldInfo): DateTime|bool|array|Carbon|string|null
    {
        $value = $this->fetchSimpleValue($feedData, $fieldInfo);
        $formatting = Hash::get($fieldInfo, 'options.match');

        return $this->parseDateAttribute($value, $formatting);
    }

    /**
     * @param $feedData
     * @param $fieldInfo
     * @return bool|mixed|void
     */
    protected function parseAvailableForPurchase($feedData, $fieldInfo)
    {
        $value = $this->fetchSimpleValue($feedData, $fieldInfo);

        return BaseHelper::parseBoolean($value);
    }

    /**
     * @param $feedData
     * @param $fieldInfo
     * @return bool|mixed|void
     */
    protected function parseFreeShipping($feedData, $fieldInfo)
    {
        $value = $this->fetchSimpleValue($feedData, $fieldInfo);

        return BaseHelper::parseBoolean($value);
    }

    /**
     * @param $feedData
     * @param $fieldInfo
     * @return bool|mixed|void
     */
    protected function parsePromotable($feedData, $fieldInfo)
    {
        $value = $this->fetchSimpleValue($feedData, $fieldInfo);

        return BaseHelper::parseBoolean($value);
    }

    /**
     * @param $feedData
     * @param $fieldInfo
     * @return false|mixed
     */
    protected function parseTaxCategoryId($feedData, $fieldInfo): mixed
    {
        $value = $this->fetchSimpleValue($feedData, $fieldInfo);

        $query = (new Query())
            ->select(['*'])
            ->from(['{{%commerce_taxcategories}}']);

        // Find by ID
        $result = $query->where(['id' => $value])->one();

        // Find by Name
        if (!$result) {
            $result = $query->where(['name' => $value])->one();
        }

        // Find by Handle
        if (!$result) {
            $result = $query->where(['handle' => $value])->one();
        }

        if ($result) {
            return $result['id'];
        }

        return false;
    }

    /**
     * @param $feedData
     * @param $fieldInfo
     * @return false|mixed
     */
    protected function parseShippingCategoryId($feedData, $fieldInfo): mixed
    {
        $value = $this->fetchSimpleValue($feedData, $fieldInfo);

        $query = (new Query())
            ->select(['*'])
            ->from(['{{%commerce_shippingcategories}}']);

        // Find by ID
        $result = $query->where(['id' => $value])->one();

        // Find by Name
        if (!$result) {
            $result = $query->where(['name' => $value])->one();
        }

        // Find by Handle
        if (!$result) {
            $result = $query->where(['handle' => $value])->one();
        }

        if ($result) {
            return $result['id'];
        }

        return false;
    }
}
