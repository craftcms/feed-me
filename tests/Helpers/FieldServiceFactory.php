<?php
/**
 * @link https://craftcms.com/
 * @copyright Copyright (c) Pixel & Tonic, Inc.
 * @license https://craftcms.github.io/license/
 */

namespace craft\feedme\tests\Helpers;

use craft\base\Field as CraftField;
use craft\feedme\base\Field as FeedMeField;

/**
 * Builds a Feed Me field service ready for {@see FeedMeField::parseField()}, with the `feed`
 * property populated the same way every relational-field test needs it.
 */
class FieldServiceFactory
{
    public static function create(string $serviceClass, CraftField $field, array $feed = ['id' => 1, 'siteId' => null]): FeedMeField
    {
        /** @var FeedMeField $service */
        $service = new $serviceClass();
        $service->field = $field;
        $service->feed = $feed;

        return $service;
    }
}
