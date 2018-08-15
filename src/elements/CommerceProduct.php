<?php
namespace verbb\feedme\elements;

use verbb\feedme\FeedMe;
use verbb\feedme\base\Element;
use verbb\feedme\base\ElementInterface;
use verbb\feedme\events\FeedProcessEvent;
use verbb\feedme\helpers\BaseHelper;
use verbb\feedme\helpers\DataHelper;
use verbb\feedme\services\Process;

use Craft;
use craft\db\Query;

use craft\commerce\Plugin as Commerce;
use craft\commerce\elements\Product as ProductElement;
use craft\commerce\elements\Variant as VariantElement;
use craft\commerce\services\Variants;

use yii\base\Event;
use Cake\Utility\Hash;

class CommerceProduct extends Element implements ElementInterface
{
    // Properties
    // =========================================================================

    public static $name = 'Commerce Product';
    public static $class = 'craft\commerce\elements\Product';

    public $element;


    // Templates
    // =========================================================================

    public function getGroupsTemplate()
    {
        return 'feed-me/_includes/elements/commerce_product/groups';
    }

    public function getColumnTemplate()
    {
        return 'feed-me/_includes/elements/commerce_product/column';
    }

    public function getMappingTemplate()
    {
        return 'feed-me/_includes/elements/commerce_product/map';
    }


    // Public Methods
    // =========================================================================

    public function init()
    {
        // Hook into the process service on each step - we need to re-arrange the feed mapping
        Event::on(Process::class, Process::EVENT_STEP_BEFORE_PARSE_CONTENT, function(FeedProcessEvent $event) {
            $this->_preParseVariants($event);
        });

        Event::on(Process::class, Process::EVENT_STEP_BEFORE_ELEMENT_MATCH, function(FeedProcessEvent $event) {
            $this->_checkForVariantMatches($event);
        });

        // Hook into the before element save event, because we need to do lots to prepare variant data
        Event::on(Process::class, Process::EVENT_STEP_BEFORE_ELEMENT_SAVE, function(FeedProcessEvent $event) {
            $this->_parseVariants($event);
        });
    }

    public function getGroups()
    {
        return Commerce::getInstance()->getProductTypes()->getEditableProductTypes();
    }

    public function getQuery($settings, $params = [])
    {
        $query = ProductElement::find();

        $criteria = array_merge([
            'status' => null,
            'typeId' => $settings['elementGroup'][ProductElement::class],
        ], $params);

        $siteId = Hash::get($settings, 'siteId');

        if ($siteId) {
            $criteria['siteId'] = $siteId;
        }

        Craft::configure($query, $criteria);

        return $query;
    }

    public function setModel($settings)
    {
        $this->element = new ProductElement();
        $this->element->typeId = $settings['elementGroup'][ProductElement::class];

        $siteId = Hash::get($settings, 'siteId');

        if ($siteId) {
            $this->element->siteId = $siteId;
        }

        return $this->element;
    }

    public function save($data, $settings)
    {
        $this->element->fieldLayoutId;

        if (!Craft::$app->getElements()->saveElement($this->element)) {
            $errors = [$this->element->getErrors()];

            if ($this->element->getErrors()) {
                foreach ($this->element->getVariants() as $variant) {
                    if ($variant->getErrors()) {
                        $errors[] = $variant->getErrors();
                    }
                }

                throw new \Exception(json_encode($errors));
            }

            return false;
        }

        return true;
    }


    // Private Methods
    // =========================================================================
    
    private function _preParseVariants($event)
    {
        $feed = $event->feed;

        // We need to re-arrange the feed-mapping from using variant-* to putting all these in a
        // variants[] array for easy management later. If we don't do this, it'll start processing
        // attributes and fields based on the top-level product, which is incorrect..
        foreach ($feed['fieldMapping'] as $fieldHandle => $fieldInfo) {
            if (strpos($fieldHandle, 'variant-') !== false) {
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

    private function _checkForVariantMatches($event)
    {
        $feed = $event->feed;
        $feedData = $event->feedData;
        $contentData = $event->contentData;

        // If we're trying to match an existing product element on a variant's content, we're not going to have much
        // luck. So instead, in here, we look up the parent product (if any), and return that. We directly modify the 
        // unique content array $contentData so we don't have to deal with any other shenanigans in core code.
        foreach ($contentData as $handle => $value) {
            if (strpos($handle, 'variant-') !== false) {
                $sku = null;

                $attribute = str_replace('variant-', '', $handle);

                $fieldInfo = Hash::get($feed, 'fieldMapping.variant-sku');
                $node = Hash::get($fieldInfo, 'node');

                // Because we're trying to find the parent product from a child variant, we just need to get the first
                // match - then we've got an SKU for a variant that belongs to the product we want.
                foreach ($feedData as $nodePath => $value) {
                    $feedPath = preg_replace('/(\/\d+\/)/', '/', $nodePath);
                    $feedPath = preg_replace('/^(\d+\/)|(\/\d+)/', '', $feedPath);

                    if ($feedPath === $node) {
                        $sku = $value;
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
                $contentData['id'] = $variant->productId ?? 'placeholder';

                // Cleanup
                unset($feed['fieldUnique'][$handle]);
                unset($contentData[$handle]);
            }
        }

        // Save all our changes back to the event model
        $event->feed = $feed;
        $event->feedData = $feedData;
        $event->contentData = $contentData;
    }

    private function _parseVariants($event)
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

        // Now we need to find out how many variants we're importing - can even be one, and its all a little tricky...
        foreach ($feedData as $nodePath => $value) {
            foreach ($variantMapping as $fieldHandle => $fieldInfo) {
                $node = Hash::get($fieldInfo, 'node');

                $feedPath = preg_replace('/(\/\d+\/)/', '/', $nodePath);
                $feedPath = preg_replace('/^(\d+\/)|(\/\d+)/', '', $feedPath);

                // Find the node in the feed (stripped of indexes) that matches what's stored in field mapping
                if ($feedPath === $node) {
                    // Try and determine the index. We need to always be dealing with an array of variant data
                    preg_match('/\/(\d+)\//', $nodePath, $matches);
                    $count = Hash::get($matches, '1', '0');

                    // Store this information so we can parse the field data later
                    if (!isset($variantData[$count][$fieldHandle])) {
                        $variantData[$count][$fieldHandle] = $fieldInfo;
                    }

                    $variantData[$count][$fieldHandle]['data'][$nodePath] = $value;
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

        foreach ($variantData as $variantNumber => $variantContent) {
            $attributeData = [];
            $fieldData = [];

            // Parse the just the element attributes first. We use these in our field contexts, and need a fully-prepped element
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
            $variant = $this->_getVariantBySku($sku);
            $variant->product = $element;

            // Set the attributes for the element
            $variant->setAttributes($attributeData, false);

            // Then, do the same for custom fields. Again, this should be done after populating the element attributes
            foreach ($variantContent as $fieldHandle => $fieldInfo) {
                if (Hash::get($fieldInfo, 'field')) {
                    $data = Hash::get($fieldInfo, 'data');

                    $fieldValue = FeedMe::$plugin->fields->parseField($feed, $element, $data, $fieldHandle, $fieldInfo);;

                    if ($fieldValue !== null) {
                        $fieldData[$fieldHandle] = $fieldValue;
                    }
                }
            }

            // Do the same with our custom field data
            $variant->setFieldValues($fieldData);

            // Add to our contentData variable for debugging
            $contentData['variants'][] = $attributeData + $fieldData;

            $variants[] = $variant;
        }

        // Set the products variants
        $element->setVariants($variants);

        // Save all our changes back to the event model
        $event->feed = $feed;
        $event->feedData = $feedData;
        $event->contentData = $contentData;
        $event->element = $element;
    }

    private function _getVariantBySku($sku, $siteId = null)
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

    protected function parsePostDate($feedData, $fieldInfo)
    {
        $value = $this->fetchSimpleValue($feedData, $fieldInfo);
        $formatting = Hash::get($fieldInfo, 'options.match');

        return $this->parseDateAttribute($value, $formatting);
    }

    protected function parseExpiryDate($feedData, $fieldInfo)
    {
        $value = $this->fetchSimpleValue($feedData, $fieldInfo);
        $formatting = Hash::get($fieldInfo, 'options.match');

        return $this->parseDateAttribute($value, $formatting);
    }

    protected function parseAvailableForPurchase($feedData, $fieldInfo)
    {
        $value = $this->fetchSimpleValue($feedData, $fieldInfo);

        return BaseHelper::parseBoolean($value);
    }

    protected function parseFreeShipping($feedData, $fieldInfo)
    {
        $value = $this->fetchSimpleValue($feedData, $fieldInfo);

        return BaseHelper::parseBoolean($value);
    }

    protected function parsePromotable($feedData, $fieldInfo)
    {
        $value = $this->fetchSimpleValue($feedData, $fieldInfo);

        return BaseHelper::parseBoolean($value);
    }

    protected function parseTaxCategoryId($feedData, $fieldInfo)
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

    protected function parseShippingCategoryId($feedData, $fieldInfo)
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
