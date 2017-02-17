<?php
namespace Craft;

use Cake\Utility\Hash as Hash;

class Commerce_ProductFeedMeElementType extends BaseFeedMeElementType
{
    // Templates
    // =========================================================================

    public function getGroupsTemplate()
    {
        return 'feedme/_includes/elements/commerce_product/groups';
    }

    public function getColumnTemplate()
    {
        return 'feedme/_includes/elements/commerce_product/column';
    }


    // Public Methods
    // =========================================================================

    public function getGroups()
    {
        return craft()->commerce_productTypes->getEditableProductTypes();
    }

    public function setModel($settings)
    {
        $element = new Commerce_ProductModel();
        $element->typeId = $settings['elementGroup']['Commerce_Product'];

        if ($settings['locale']) {
            $element->locale = $settings['locale'];
        }

        return $element;
    }

    public function setCriteria($settings)
    {
        $criteria = craft()->elements->getCriteria('Commerce_Product');
        $criteria->status = null;
        $criteria->limit = null;
        $criteria->localeEnabled = null;

        $criteria->typeId = $settings['elementGroup']['Commerce_Product'];
        
        if ($settings['locale']) {
            $criteria->locale = $settings['locale'];
        }

        return $criteria;
    }

    public function matchExistingElement(&$criteria, $data, $settings)
    {
        foreach ($settings['fieldUnique'] as $handle => $value) {
            if (intval($value) == 1 && ($data != '__')) {
                if (strstr($handle, 'variants--')) {
                    $attribute = str_replace('variants--', '', $handle);

                    // If we're matching existing elements via a Variant property, we don't want to use the 
                    // Commerce_Product element criteria
                    $variantCriteria = craft()->elements->getCriteria('Commerce_Variant');
                    $variantCriteria->status = null;
                    $variantCriteria->limit = null;
                    $variantCriteria->localeEnabled = null;

                    $variantCriteria->$attribute = DbHelper::escapeParam($data['variants']['data'][$attribute]['data']);

                    // Get the variants - interestingly, find()[0] is faster than first()
                    $variants = $variantCriteria->find();

                    // Set the Product ID for the criteria from our found variant - thats what we need to update
                    if (isset($variants[0])) {
                        $criteria->id = $variants[0]->productId;
                    }
                } else {
                    $criteria->$handle = DbHelper::escapeParam($data[$handle]);
                }
            }
        }

        // Check to see if an element already exists - interestingly, find()[0] is faster than first()
        return $criteria->find();
    }

    public function delete(array $elements)
    {
        $return = true;

        foreach ($elements as $element) {
            if (!craft()->commerce_products->deleteProduct($element)) {
                $return = false;
            }
        }

        return $return;
    }
    
    public function prepForElementModel(BaseElementModel $element, array &$data, $settings)
    {
        if (isset($settings['locale'])) {
            $element->localeEnabled = true;
        }

        foreach ($data as $handle => $value) {
            switch ($handle) {
                case 'id';
                case 'taxCategoryId';
                case 'shippingCategoryId';
                    $element->$handle = $value['data'];
                    break;
                case 'slug';
                    $element->$handle = ElementHelper::createSlug($value['data']);
                    break;
                case 'postDate':
                case 'expiryDate';
                    $element->$handle = $this->_prepareDateForElement($value['data']);
                    break;
                case 'enabled':
                case 'freeShipping':
                case 'promotable':
                    $element->$handle = (bool)$value['data'];
                    break;
                case 'title':
                    $element->getContent()->$handle = $value['data'];
                    break;
                default:
                    continue 2;
            }

            // Update the original data in our feed - for clarity in debugging
            $data[$handle] = $element->$handle;
        }

        $this->_populateProductVariantModels($element, $data, $settings);

        return $element;
    }

    public function save(BaseElementModel &$element, array $data, $settings)
    {
        $result = craft()->commerce_products->saveProduct($element);

        // Because we can have product and variant error, make sure we show them
        if (!$result) {
            foreach ($element->getVariants() as $variant) {
                if ($variant->getErrors()) {
                    throw new Exception(json_encode($variant->getErrors()));
                }
            }
        }

        return $result;
    }

    public function afterSave(BaseElementModel $element, array $data, $settings)
    {

    }


    // Private Methods
    // =========================================================================

    private function _populateProductModel(Commerce_ProductModel &$product, $data)
    {
        
    }

    private function _populateProductVariantModels(Commerce_ProductModel $product, &$data, $settings)
    {
        $variants = [];
        $count = 1;

        $variantData = Hash::get($data, 'variants.data');

        if (!$variantData) {
            return false;
        }

        $variantData = $this->_prepProductData($variantData);

        // Update original data
        $data['variants'] = $variantData;

        foreach ($variantData as $key => $variant) {
            $variantModel = $this->_getVariantBySku($variant['sku']['data']);

            if (!$variantModel) {
                $variantModel = new Commerce_VariantModel();
            }

            $variantModel->setProduct($product);

            // Check for our default data (if any provided, and if not already set in 'real' data)
            foreach ($settings['fieldDefaults'] as $defaultsHandle => $defaultsValue) {
                if ($defaultsValue) {
                    $variantPreppedHandle = str_replace('variants--', '', $defaultsHandle);

                    $variant[$variantPreppedHandle]['data'] = $defaultsValue;
                }
            }

            $variantModel->enabled = Hash::get($variant, 'enabled.data', 1);
            $variantModel->isDefault = Hash::get($variant, 'isDefault.data', 0);
            $variantModel->sku = Hash::get($variant, 'sku.data');
            $variantModel->price = Hash::get($variant, 'price.data');
            $variantModel->width = LocalizationHelper::normalizeNumber(Hash::get($variant, 'width.data'));
            $variantModel->height = LocalizationHelper::normalizeNumber(Hash::get($variant, 'height.data'));
            $variantModel->length = LocalizationHelper::normalizeNumber(Hash::get($variant, 'length.data'));
            $variantModel->weight = LocalizationHelper::normalizeNumber(Hash::get($variant, 'weight.data'));
            $variantModel->stock = LocalizationHelper::normalizeNumber(Hash::get($variant, 'stock.data'));
            $variantModel->unlimitedStock = LocalizationHelper::normalizeNumber(Hash::get($variant, 'unlimitedStock.data'));
            $variantModel->minQty = LocalizationHelper::normalizeNumber(Hash::get($variant, 'minQty.data'));
            $variantModel->maxQty = LocalizationHelper::normalizeNumber(Hash::get($variant, 'maxQty.data'));

            $variantModel->sortOrder = $count++;

            // Loop through each field for this Variant model - see if we have data
            $variantContent = array();
            foreach ($variantModel->getFieldLayout()->getFields() as $fieldLayout) {
                $field = $fieldLayout->getField();
                $handle = $field->handle;

                $fieldData = Hash::get($variant, $handle);

                if ($fieldData) {
                    $variantContent[$handle] = craft()->feedMe_fields->prepForFieldType($variantModel, $fieldData, $handle);
                }
            }

            $variantModel->setContentFromPost($variantContent);

            $variantModel->getContent()->title = Hash::get($variant, 'title.data');

            $variants[] = $variantModel;
        }

        $product->setVariants($variants);
    }

    private function _prepProductData($variantData) {
        $variants = array();

        // Check for single Variant - thats easy
        if (Hash::dimensions($variantData) == 2) {
            return array($variantData);
        }

        // We need to parse our variant data, because they're stored in a specific way from field-mapping
        // [title] => Array (
        //     [data] => Array (
        //         [0] => Product 1
        //         [1] => Product 2
        // )
        // Into:
        // [0] => Array (
        //     [data] => Array (
        //          [title] => Product 1
        // )
        // [1] => Array (
        //     [data] => Array (
        //          [title] => Product 2
        // )

        $flatten = Hash::flatten($variantData);

        $optionsArray = array();
        $tempVariants = array();
        foreach ($flatten as $keyedIndex => $value) {
            $tempArray = explode('.', $keyedIndex);

            // Check for a value for this field...
            if (!isset($value) || $value === null) {
                continue;
            }

            if (is_array($value) && empty($value)) {
                continue;
            }

            // Save field options for later - they're a special case
            if (strstr($keyedIndex, '.options.')) {
                FeedMeArrayHelper::arraySet($optionsArray, $tempArray, $value);
            } else {
                // Extract 'data.[number]' - we need the number for which variant we're talking about
                preg_match_all('/data.(\d*)/', $keyedIndex, $variantKeys);
                $fieldHandle = $tempArray[0];
                $variantKey = $variantKeys[1];

                // Remove the index from inside [data], to the front
                array_splice($tempArray, 0, 0, $variantKey);

                // Check for nested data (elements, table)
                if (preg_match('/data.(\d*\.\d*)/', $keyedIndex)) {
                    //array_pop($tempArray);

                    unset($tempArray[count($tempArray) - 2]);
                } else {
                    array_pop($tempArray);
                }

                // Special case for Table field. This will be refactored once again with field-aware-parsing
                $field = craft()->fields->getFieldByHandle($fieldHandle);

                if ($field && $field->type == 'Table') {
                    array_splice($tempArray, 2, 0, 'data');
                }

                FeedMeArrayHelper::arraySet($variants, $tempArray, $value);
            }
        }

        // Put the variants back in place where they should be
        foreach ($variants as $blockOrder => $blockData) {
            foreach ($blockData as $blockHandle => $innerData) {
                $optionData = Hash::get($optionsArray, $blockHandle);

                if ($optionData) {
                    $variants[$blockOrder][$blockHandle] = Hash::merge($innerData, $optionData);
                }
            }
        }

        return $variants;
    }

    private function _getVariantBySku($sku, $localeId = null)
    {
        return craft()->elements->getCriteria('Commerce_Variant', array('sku' => $sku, 'status' => null, 'locale' => $localeId))->first();
    }

    private function _prepareDateForElement($date)
    {
        if (!is_array($date)) {
            $d = date_parse($date);
            $date_string = date('Y-m-d H:i:s', mktime($d['hour'], $d['minute'], $d['second'], $d['month'], $d['day'], $d['year']));

            $date = DateTime::createFromString($date_string, craft()->timezone);
        }

        return $date;
    }
}
