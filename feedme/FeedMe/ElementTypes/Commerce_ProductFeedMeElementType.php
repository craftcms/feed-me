<?php
namespace Craft;

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

                    $variantCriteria->$attribute = DbHelper::escapeParam($data['variants'][$attribute]);

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
    
    public function prepForElementModel(BaseElementModel $element, array &$data, $settings, $options)
    {
        if (isset($settings['locale'])) {
            $element->localeEnabled = true;
        }

        foreach ($data as $handle => $value) {
            if ($value == '' || $value == '__') {
                continue;
            }

            switch ($handle) {
                case 'id';
                case 'taxCategoryId';
                    $element->$handle = $value;
                    break;
                case 'slug';
                    $element->$handle = ElementHelper::createSlug($value);
                    break;
                case 'postDate':
                case 'expiryDate';
                    $element->$handle = $this->_prepareDateForElement($value);
                    break;
                case 'enabled':
                case 'freeShipping':
                case 'promotable':
                    $element->$handle = (bool)$value;
                    break;
                case 'title':
                    $element->getContent()->$handle = $value;
                    break;
                default:
                    break 2;
            }

            // Update the original data in our feed - for clarity in debugging
            $data[$handle] = $element->$handle;
        }

        $this->_populateProductVariantModels($element, $data, $settings, $options);

        return $element;
    }

    public function save(BaseElementModel &$element, $settings)
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

    public function afterSave(BaseElementModel $element, array $fields, $settings)
    {

    }


    // Private Methods
    // =========================================================================

    private function _populateProductModel(Commerce_ProductModel &$product, $data)
    {
        
    }

    private function _populateProductVariantModels(Commerce_ProductModel &$product, $data, $settings, $options)
    {
        $variants = [];
        $count = 1;

        if (!isset($data['variants']) || $data['variants'] == '__') {
            return false;
        } else {
            $variantData = $data['variants'];
        }

        $variantData = $this->_prepProductData($variantData);

        foreach ($variantData as $key => $variant) {
            if ($this->_getVariantBySku($variant['sku'])) {
                $variantModel = $this->_getVariantBySku($variant['sku']);
            } else {
                $variantModel = new Commerce_VariantModel();
            }

            $variantModel->setProduct($product);

            // Check for our default data (if any provided, and if not already set in 'real' data)
            foreach ($settings['fieldDefaults'] as $defaultsHandle => $defaultsValue) {
                if ($defaultsValue) {
                    $variantPreppedHandle = str_replace('variants--', '', $defaultsHandle);

                    $variant[$variantPreppedHandle] = $defaultsValue;
                }
            }

            $variantModel->enabled = $this->_hasValue($variant, 'enabled') ? $variant['enabled'] : 1;
            $variantModel->isDefault = $this->_hasValue($variant, 'isDefault') ? $variant['isDefault'] : 0;
            $variantModel->sku = $this->_hasValue($variant, 'sku') ? $variant['sku'] : '';
            $variantModel->price = $this->_hasValue($variant, 'price') ? LocalizationHelper::normalizeNumber($variant['price']) : null;
            $variantModel->width = $this->_hasValue($variant, 'width') ? LocalizationHelper::normalizeNumber($variant['width']) : null;
            $variantModel->height = $this->_hasValue($variant, 'height') ? LocalizationHelper::normalizeNumber($variant['height']) : null;
            $variantModel->length = $this->_hasValue($variant, 'length') ? LocalizationHelper::normalizeNumber($variant['length']) : null;
            $variantModel->weight = $this->_hasValue($variant, 'weight') ? LocalizationHelper::normalizeNumber($variant['weight']) : null;
            $variantModel->stock = $this->_hasValue($variant, 'stock') ? LocalizationHelper::normalizeNumber($variant['stock']) : null;
            $variantModel->unlimitedStock = $this->_hasValue($variant, 'unlimitedStock') ? $variant['unlimitedStock'] : '';
            $variantModel->minQty = $this->_hasValue($variant, 'minQty') ? LocalizationHelper::normalizeNumber($variant['minQty']) : null;
            $variantModel->maxQty = $this->_hasValue($variant, 'maxQty') ? LocalizationHelper::normalizeNumber($variant['maxQty']) : null;

            $variantModel->sortOrder = $count++;

            // Loop through each field for this Variant model - see if we have data
            $variantContent = array();
            foreach ($variantModel->getFieldLayout()->getFields() as $fieldLayout) {
                $field = $fieldLayout->getField();

                if (isset($variant[$field->handle])) {
                    $data = $variant[$field->handle];
                    $handle = $field->handle;

                    // Grab any additional options for this field
                    $fieldOpts = array();
                    if (isset($options['variants']['options'][$handle])) {
                        $fieldOpts['options'] = $options['variants']['options'][$handle];
                    }

                    $content = craft()->feedMe_fields->prepForFieldType($variantModel, $data, $handle, $fieldOpts);
                    $variantContent[$handle] = $content;
                }
            }

            $variantModel->setContentFromPost($variantContent);

            if ($this->_hasValue($variant, 'title')) {
                $variantModel->getContent()->title = $variant['title'];
            }

            $variants[] = $variantModel;
        }

        $product->setVariants($variants);
    }

    private function _prepProductData($variantData) {
        $variants = array();

        foreach ($variantData as $attribute => $variantCollection) {
            if (!is_array($variantCollection)) {
                $variantCollection = array($variantCollection);
            }

            // A special case for Table fields - because they're annoying...
            $field = craft()->fields->getFieldByHandle($attribute);

            if ($field && $field->type == 'Table') {
                foreach ($variantCollection as $column => $rows) {
                    foreach ($rows as $row => $data) {
                        $variants[$row][$attribute][$column] = $data;
                    }
                }
            } else {
                foreach ($variantCollection as $key => $variant) {
                    $variants[$key][$attribute] = $variant;
                }
            }
        }

        return $variants;
    }

    private function _hasValue($object, $attribute) {
        if (isset($object[$attribute])) {
            if ($object[$attribute] != '__') {
                return true;
            }
        }

        return false;
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
