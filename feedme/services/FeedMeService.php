<?php
namespace Craft;

class FeedMeService extends BaseApplicationComponent
{
    // Public Methods
    // =========================================================================

    public function getPlugin()
    {
        return craft()->plugins->getPlugin('feedMe');
    }

    public function getSettings()
    {
        return $this->getPlugin()->getSettings();
    }

    public function throwError($feedName, $message)
    {
        FeedMePlugin::log($feedName . ': ' . $message, LogLevel::Error, true);

        throw new Exception(Craft::t($message));
    }

    public function getElementTypeService($elementType)
    {
        // Check for third-party element type support
        $elementsToLoad = craft()->plugins->call('registerFeedMeElementTypes');

        foreach ($elementsToLoad as $plugin => $elementClasses) {
            foreach ($elementClasses as $elementClass) {
                if ($elementClass && $elementClass instanceof BaseFeedMeElementType) {
                    if ($elementClass->getElementType() == $elementType) {
                        return $elementClass;
                    }
                }
            }
        }

        return false;
    }

    public function getDataTypeService($dataType)
    {
        $dataTypes = $this->getRegisteredDataTypes();

        if (isset($dataTypes[$dataType])) {
            return $dataTypes[$dataType];
        }

        return false;
    }

    public function getFieldTypeService($fieldType)
    {
        // Check for third-party field type support
        $fieldsToLoad = craft()->plugins->call('registerFeedMeFieldTypes');

        foreach ($fieldsToLoad as $plugin => $fieldClasses) {
            foreach ($fieldClasses as $fieldClass) {
                if ($fieldClass && $fieldClass instanceof BaseFeedMeFieldType) {
                    if ($fieldClass->getFieldType() == $fieldType) {
                        return $fieldClass;
                    }
                }
            }
        }

        // Return default handling for a field
        return new DefaultFeedMeFieldType();
    }

    /**
     * @return BaseFeedMeDataType[]
     */
    public function getRegisteredDataTypes()
    {
        $dataTypes = [];

        // Check for third-party data type support
        $dataToLoad = craft()->plugins->call('registerFeedMeDataTypes');

        foreach ($dataToLoad as $plugin => $dataClasses) {
            foreach ($dataClasses as $dataClass) {
                if ($dataClass && $dataClass instanceof BaseFeedMeDataType) {
                    $dataType = $dataClass->getDataType();

                    $dataTypes[StringHelper::toLowerCase($dataType)] = $dataClass;
                }
            }
        }

        return $dataTypes;
    }

    /**
     * @param string $suffix Optional suffix for each display name.
	 *
     * @return array
     */
    public function getRegisteredDataTypesDisplayNames($suffix = '')
    {
        $displayNames = [];

        $dataTypes = craft()->feedMe->getRegisteredDataTypes();

        foreach ($dataTypes as $dataType => $dataTypeClass) {
            $displayNames[$dataType] = $dataTypeClass->getDisplayName() . $suffix;
        }

        return $displayNames;
    }

}
