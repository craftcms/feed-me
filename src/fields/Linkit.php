<?php

namespace semabit\feedme\fields;

use Cake\Utility\Hash;
use semabit\feedme\base\Field;
use semabit\feedme\base\FieldInterface;
use semabit\feedme\helpers\DataHelper;

/**
 *
 * @property-read string $mappingTemplate
 */
class Linkit extends Field implements FieldInterface
{
    // Properties
    // =========================================================================

    /**
     * @var string
     */
    public static string $name = 'Linkit';

    /**
     * @var string
     */
    public static string $class = 'presseddigital\linkit\fields\LinkitField';


    // Templates
    // =========================================================================

    /**
     * @inheritDoc
     */
    public function getMappingTemplate(): string
    {
        return 'feed-me/_includes/fields/linkit';
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
            $preppedData[$subFieldHandle] = DataHelper::fetchValue($this->feedData, $subFieldInfo, $this->feed);
        }

        if (empty(
            array_filter($preppedData, function($val) {
                return $val !== null;
            })
        )) {
            return null;
        }

        if ($preppedData) {
            // Handle Link Type
            $preppedData['type'] = empty($preppedData['type'] ?? '') ? 'presseddigital\linkit\models\Url' : $preppedData['type'];
            if (!str_contains($preppedData['type'], '\\')) {
                $preppedData['type'] = 'presseddigital\\linkit\\models\\' . ucfirst(strtolower(trim($preppedData['type'])));
            }

            // Handle Link Target
            $preppedData['target'] = trim(empty($preppedData['target'] ?? '') ? '' : $preppedData['target']);
            $preppedData['target'] = $preppedData['target'] && $preppedData['target'] != '_self' ? 1 : '';
        }

        // Protect against sending an empty array
        if (!$preppedData) {
            return null;
        }

        return $preppedData;
    }
}
