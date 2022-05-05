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
    public static string $class = 'fruitstudios\linkit\fields\LinkitField';


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
            $preppedData[$subFieldHandle] = DataHelper::fetchValue($this->feedData, $subFieldInfo);
        }

        if ($preppedData) {
            // Handle Link Type
            $preppedData['type'] = empty($preppedData['type'] ?? '') ? 'fruitstudios\linkit\models\Url' : $preppedData['type'];
            if (!str_contains($preppedData['type'], '\\')) {
                $preppedData['type'] = 'fruitstudios\\linkit\\models\\' . ucfirst(strtolower(trim($preppedData['type'])));
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
