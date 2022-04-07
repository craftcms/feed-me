<?php
/**
 * @link https://craftcms.com/
 * @copyright Copyright (c) Pixel & Tonic, Inc.
 * @license https://craftcms.github.io/license/
 */

namespace craft\feedme\datatypes;

use craft\base\MissingComponentInterface;
use craft\base\MissingComponentTrait;
use craft\feedme\base\DataType;

/**
 * MissingDataType represents a data type with an invalid class.
 *
 * @author Pixel & Tonic, Inc. <support@pixelandtonic.com>
 * @since 4.4.3
 */
class MissingDataType extends DataType implements MissingComponentInterface
{
    use MissingComponentTrait;
}
