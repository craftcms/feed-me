<?php

namespace craft\feedme\fields;

use Cake\Utility\Hash;
use craft\feedme\base\Field;
use craft\feedme\base\FieldInterface;
use craft\feedme\helpers\DataHelper;

/**
 *
 * @property-read string $mappingTemplate
 */
class TypedLink extends Field implements FieldInterface
{
    // Properties
    // =========================================================================

    /**
     * @var string
     */
    public static string $name = 'TypedLink';

    /**
     * @var string
     */
    public static string $class = 'typedlinkfield\fields\LinkField';

    // Templates
    // =========================================================================

    /**
     * @inheritDoc
     */
    public function getMappingTemplate(): string
    {
        return 'feed-me/_includes/fields/typed-link';
    }

    // Public Methods
    // =========================================================================

    /**
     * @inheritDoc
     */
    public function parseField(): mixed
    {
        $preppedData = [];

        $fields = Hash::get($this->fieldInfo, 'fields');

        if (!$fields) {
            return null;
        }

        foreach ($fields as $subFieldHandle => $subFieldInfo) {
            $preppedData[$subFieldHandle] = DataHelper::fetchValue($this->feedData, $subFieldInfo);
        }

        // Protect against sending an empty array
        if (!$preppedData) {
            return null;
        }

        return $preppedData;
    }
}
