<?php
/**
 * @link https://craftcms.com/
 * @copyright Copyright (c) Pixel & Tonic, Inc.
 * @license https://craftcms.github.io/license/
 */

namespace craft\feedme\fields;

use craft\base\MissingComponentInterface;
use craft\base\MissingComponentTrait;
use craft\feedme\base\Field;

/**
 * MissingField represents a field with an invalid class.
 *
 * @author Pixel & Tonic, Inc. <support@pixelandtonic.com>
 * @since 4.4.3
 */
class MissingField extends Field implements MissingComponentInterface
{
    use MissingComponentTrait;
}
