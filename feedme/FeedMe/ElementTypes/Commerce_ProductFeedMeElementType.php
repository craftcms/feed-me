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

    public function getMappingTemplate()
    {
        return 'feedme/_includes/elements/commerce_product/map';
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

                    // Because a single product can have multiple attached variants - therefore multiple data,
                    // we only really need the first variant value to find the parent product ID.
                    $feedValue = Hash::get($data, 'variants.data.0.' . $attribute);
                    $feedValue = Hash::get($data, 'variants.data.0.' . $attribute . '.data', $feedValue);

                    // Check for single-variant
                    if (!$feedValue) {
                        $feedValue = Hash::get($data, 'variants.data.' . $attribute);
                        $feedValue = Hash::get($data, 'variants.data.' . $attribute . '.data', $feedValue);
                    }

                    if (!$feedValue) {
                        FeedMePlugin::log('Commerce Variants: no data for `' . $attribute . '` to match an existing element on. Is data present for this in your feed?', LogLevel::Error, true);
                        return false;
                    }

                    $variantCriteria->$attribute = DbHelper::escapeParam($feedValue);

                    // Get the variants - interestingly, find()[0] is faster than first()
                    $variants = $variantCriteria->find();

                    // Set the Product ID for the criteria from our found variant - thats what we need to update
                    if (count($variants)) {
                        $criteria->id = $variants[0]->productId;
                    } else {
                        return null;
                    }
                    else {
                        // otherwise we haven't found a variant but we're looking for one, so let's an empty array
                        return array();
                    }
                } else {
                    $feedValue = Hash::get($data, $handle);
                    $feedValue = Hash::get($data, $handle . '.data', $feedValue);

                    if ($feedValue) {
                        $criteria->$handle = DbHelper::escapeParam($feedValue);
                    } else {
                        FeedMePlugin::log('Commerce Products: no data for `' . $handle . '` to match an existing element on. Is data present for this in your feed?', LogLevel::Error, true);
                        return false;
                    }
                }
            }
        }

        // Check to see if an element already exists - interestingly, find()[0] is faster than first()
        $elements = $criteria->find();

        if (count($elements)) {
            return $elements[0];
        }

        return null;
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
        foreach ($data as $handle => $value) {
            if (is_null($value)) {
                continue;
            }

            if (isset($value['data']) && $value['data'] === null) {
                continue;
            }

            if (is_array($value)) {
                $dataValue = Hash::get($value, 'data', null);
            } else {
                $dataValue = $value;
            }

            // Check for any Twig shorthand used
            if (is_string($dataValue)) {
                $objectModel = $this->getObjectModel($data);
                $dataValue = craft()->templates->renderObjectTemplate($dataValue, $objectModel);
            }
            
            switch ($handle) {
                case 'id';
                case 'taxCategoryId';
                case 'shippingCategoryId';
                    $element->$handle = $dataValue;
                    break;
                case 'slug';
                    $element->$handle = ElementHelper::createSlug($dataValue);
                    break;
                case 'postDate':
                case 'expiryDate';
                    $dateValue = FeedMeDateHelper::parseString($dataValue);

                    // Ensure there's a parsed data - null will auto-generate a new date
                    if ($dateValue) {
                        $element->$handle = $dateValue;
                    }

                    break;
                case 'enabled':
                case 'freeShipping':
                case 'promotable':
                    $element->$handle = (bool)$dataValue;
                    break;
                case 'title':
                    $element->getContent()->$handle = $dataValue;
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
        // Are we targeting a specific locale here? If so, we create an essentially blank element
        // for the primary locale, and instead create a locale for the targeted locale
        if (isset($settings['locale']) && $settings['locale']) {
            // Save the default locale element empty
            $result = craft()->commerce_products->saveProduct($element);

            if ($result) {
                // Now get the successfully saved (empty) element, and set content on that instead
                $elementLocale = craft()->commerce_products->getProductById($element->id, $settings['locale']);
                $elementLocale->setContentFromPost($data);

                // Save the locale entry
                $result = craft()->commerce_products->saveProduct($elementLocale);
            }
        } else {
            $result = craft()->commerce_products->saveProduct($element);
        }

        // Because we can have product and variant error, make sure we show them
        if (!$result) {
            if ($element->getErrors()) {
                throw new Exception(json_encode($element->getErrors()));
            } else {
                foreach ($element->getVariants() as $variant) {
                    if ($variant->getErrors()) {
                        throw new Exception(json_encode($variant->getErrors()));
                    }
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

        // Ensure we handle single-variants correctly
        $keys = array_keys($variantData);

        if (!is_numeric($keys[0])) {
            $variantData = array($variantData);
        }

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
            $variantModel->sku = Hash::get($variant, 'sku.data', $variantModel->sku);
            $variantModel->price = Hash::get($variant, 'price.data', $variantModel->price);
            $variantModel->width = LocalizationHelper::normalizeNumber(Hash::get($variant, 'width.data', $variantModel->width));
            $variantModel->height = LocalizationHelper::normalizeNumber(Hash::get($variant, 'height.data', $variantModel->height));
            $variantModel->length = LocalizationHelper::normalizeNumber(Hash::get($variant, 'length.data', $variantModel->length));
            $variantModel->weight = LocalizationHelper::normalizeNumber(Hash::get($variant, 'weight.data', $variantModel->weight));
            $variantModel->stock = LocalizationHelper::normalizeNumber(Hash::get($variant, 'stock.data', $variantModel->stock));
            $variantModel->unlimitedStock = LocalizationHelper::normalizeNumber(Hash::get($variant, 'unlimitedStock.data', $variantModel->unlimitedStock));
            $variantModel->minQty = LocalizationHelper::normalizeNumber(Hash::get($variant, 'minQty.data', $variantModel->minQty));
            $variantModel->maxQty = LocalizationHelper::normalizeNumber(Hash::get($variant, 'maxQty.data', $variantModel->maxQty));

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

    private function _getVariantBySku($sku, $localeId = null)
    {
        return craft()->elements->getCriteria('Commerce_Variant', array('sku' => $sku, 'status' => null, 'locale' => $localeId))->first();
    }
}
