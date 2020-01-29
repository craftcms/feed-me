<?php

namespace craft\feedme\elements;

use Cake\Utility\Hash;
use Craft;
use craft\commerce\elements\Order as OrderElement;
use craft\feedme\base\Element;

class CommerceOrder extends Element
{
    // Properties
    // =========================================================================

    public static $name = 'Commerce Order';
    public static $class = 'craft\commerce\elements\Order';

    public $element;


    // Templates
    // =========================================================================

    public function getGroupsTemplate()
    {
        return 'feed-me/_includes/elements/commerce_order/groups';
    }

    public function getColumnTemplate()
    {
        return 'feed-me/_includes/elements/commerce_order/column';
    }

    public function getMappingTemplate()
    {
        return 'feed-me/_includes/elements/commerce_order/map';
    }


    // Public Methods
    // =========================================================================

    public function getGroups()
    {
        return [];
    }

    public function getQuery($settings, $params = [])
    {
        $query = OrderElement::find()
            ->anyStatus()
            ->siteId(Hash::get($settings, 'siteId') ?: Craft::$app->getSites()->getPrimarySite()->id);
        Craft::configure($query, $params);
        return $query;
    }

    public function setModel($settings)
    {
        $this->element = new OrderElement();

        $siteId = Hash::get($settings, 'siteId');

        if ($siteId) {
            $this->element->siteId = $siteId;
        }

        return $this->element;
    }


    // public function matchExistingElement(&$criteria, $data, $settings)
    // {
    //     foreach ($settings['fieldUnique'] as $handle => $value) {
    //         if ((int)$value === 1) {
    //             $feedValue = Hash::get($data, $handle);
    //             $feedValue = Hash::get($data, $handle . '.data', $feedValue);

    //             // Special case for order number - we might be generating it!
    //             if ($handle == 'number') {
    //                 if (isset($settings['fieldMapping']['number-options-generate'])) {
    //                     $generate = $settings['fieldMapping']['number-options-generate'];

    //                     if ((int)$generate === 1) {
    //                         return null;
    //                     }
    //                 }
    //             }

    //             if ($handle == 'dateOrdered' || $handle == 'datePaid') {
    //                 $feedValue = FeedMeDateHelper::getDateTimeString($feedValue);
    //             }

    //             if ($feedValue) {
    //                 $criteria->$handle = DbHelper::escapeParam($feedValue);
    //             } else {
    //                 FeedMePlugin::log('Commerce Order: no data for `' . $handle . '` to match an existing element on. Is data present for this in your feed?', LogLevel::Error, true);

    //                 return false;
    //             }
    //         }
    //     }

    //     // Check to see if an element already exists - interestingly, find()[0] is faster than first()
    //     $elements = $criteria->find();

    //     if (count($elements)) {
    //         return $elements[0];
    //     }

    //     return null;
    // }

    // public function prepForElementModel(BaseElementModel $element, array &$data, $settings)
    // {
    //     foreach ($data as $handle => $value) {
    //         if (is_null($value)) {
    //             continue;
    //         }

    //         if (isset($value['data']) && $value['data'] === null) {
    //             continue;
    //         }

    //         if (is_array($value)) {
    //             $dataValue = Hash::get($value, 'data', null);
    //         } else {
    //             $dataValue = $value;
    //         }

    //         // Check for any Twig shorthand used
    //         $this->parseInlineTwig($data, $dataValue);

    //         switch ($handle) {
    //             case 'id';
    //             case 'number';
    //             case 'email';
    //             case 'couponCode';
    //             case 'lastIp';
    //             case 'orderStatusId';
    //             case 'shippingMethod';
    //             case 'paymentMethodId';
    //             case 'customerId';
    //             case 'itemTotal';
    //             case 'baseDiscount';
    //             case 'baseShippingCost';
    //             case 'baseTax';
    //             case 'totalPrice';
    //             case 'totalPaid';
    //             case 'currency';
    //             case 'paymentCurrency';
    //             case 'billingAddressId';
    //             case 'shippingAddressId';
    //                 $element->$handle = $dataValue;
    //                 break;
    //             case 'dateOrdered':
    //             case 'datePaid';
    //                 $dateValue = FeedMeDateHelper::parseString($dataValue);

    //                 // Ensure there's a parsed data - null will auto-generate a new date
    //                 if ($dateValue) {
    //                     $element->$handle = $dateValue;
    //                 }

    //                 break;
    //             case 'isCompleted':
    //                 $element->$handle = FeedMeHelper::parseBoolean($dataValue);
    //                 break;
    //             default:
    //                 continue 2;
    //         }

    //         // Update the original data in our feed - for clarity in debugging
    //         $data[$handle] = $element->$handle;
    //     }

    //     // Special check for number attribute - check if we need to generate one
    //     if (isset($settings['fieldMapping']['number-options-generate'])) {
    //         $generate = $settings['fieldMapping']['number-options-generate'];

    //         if ((int)$generate === 1) {
    //             $element->number = $this->_generateCartNumber();
    //         }
    //     }

    //     return $element;
    // }

    // public function save(BaseElementModel &$element, array $data, $settings)
    // {
    //     // Are we targeting a specific locale here? If so, we create an essentially blank element
    //     // for the primary locale, and instead create a locale for the targeted locale
    //     if (isset($settings['locale']) && $settings['locale']) {
    //         // Save the default locale element empty
    //         if (Craft::$app->commerce_orders->saveOrder($element)) {
    //             // Now get the successfully saved (empty) element, and set content on that instead
    //             $elementLocale = Craft::$app->commerce_orders->getOrderById($element->id);
    //             $elementLocale->setContentFromPost($data);

    //             // Save the locale entry
    //             if (Craft::$app->commerce_orders->saveOrder($elementLocale)) {
    //                 return true;
    //             } else {
    //                 if ($elementLocale->getErrors()) {
    //                     throw new Exception(json_encode($elementLocale->getErrors()));
    //                 } else {
    //                     throw new Exception(Craft::t('feed-me', 'Unknown Element error occurred.'));
    //                 }
    //             }
    //         } else {
    //             if ($element->getErrors()) {
    //                 throw new Exception(json_encode($element->getErrors()));
    //             } else {
    //                 throw new Exception(Craft::t('feed-me', 'Unknown Element error occurred.'));
    //             }
    //         }

    //         return false;
    //     } else {
    //         return Craft::$app->commerce_orders->saveOrder($element);
    //     }
    // }

    // public function afterSave(BaseElementModel $element, array $data, $settings)
    // {

    // }


    // // Private Methods
    // // =========================================================================

    // private function _generateCartNumber()
    // {
    //     $number = md5(uniqid(mt_rand(), true));

    //     if (Craft::$app->commerce_orders->getOrderByNumber($number)) {
    //         return $this->_generateCartNumber();
    //     }

    //     return $number;
    // }


}
