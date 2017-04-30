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

    public function getRegisteredElementTypes()
    {
        $elementTypes = array();

        // Check for third-party element type support
        $elementsToLoad = craft()->plugins->call('registerFeedMeElementTypes');

        if (!craft()->feedMe_license->isProEdition()) {
            $elementsToLoad = array('FeedMe' => $elementsToLoad['FeedMe']);
        }

        foreach ($elementsToLoad as $plugin => $elementClasses) {
            foreach ($elementClasses as $elementClass) {
                if ($elementClass && $elementClass instanceof BaseFeedMeElementType) {
                    $elementType = $elementClass->getElementType();
                    $elementTypes[$elementType] = $elementClass;
                }
            }
        }

        return $elementTypes;
    }

    public function getRegisteredDataTypes()
    {
        $dataTypes = array();

        // Check for third-party data type support
        $dataToLoad = craft()->plugins->call('registerFeedMeDataTypes');

        if (!craft()->feedMe_license->isProEdition()) {
            $dataToLoad = array('FeedMe' => $dataToLoad['FeedMe']);
        }

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

    public function getRegisteredFieldTypes()
    {
        $fieldTypes = array();

        // Check for third-party field type support
        $fieldsToLoad = craft()->plugins->call('registerFeedMeFieldTypes');

        foreach ($fieldsToLoad as $plugin => $fieldClasses) {
            foreach ($fieldClasses as $fieldClass) {
                if ($fieldClass && $fieldClass instanceof BaseFeedMeFieldType) {
                    $fieldType = $fieldClass->getFieldType();
                    $fieldTypes[$fieldType] = $fieldClass;
                }
            }
        }

        return $fieldTypes;
    }

    public function getDataTypeService($dataType)
    {
        $dataTypes = $this->getRegisteredDataTypes();

        if (isset($dataTypes[$dataType])) {
            return $dataTypes[$dataType];
        }

        return false;
    }

    public function getElementTypeService($elementType)
    {
        $elementTypes = $this->getRegisteredElementTypes();

        if (isset($elementTypes[$elementType])) {
            return $elementTypes[$elementType];
        }

        return false;
    }

    public function getFieldTypeService($fieldType)
    {
        $fieldTypes = $this->getRegisteredFieldTypes();

        if (isset($fieldTypes[$fieldType])) {
            return $fieldTypes[$fieldType];
        }

        // Return default handling for a field
        return new DefaultFeedMeFieldType();
    }

    public function getRegisteredDataTypesDisplayNames($suffix = '')
    {
        $displayNames = array();

        $dataTypes = craft()->feedMe->getRegisteredDataTypes();

        foreach ($dataTypes as $dataType => $dataTypeClass) {
            $displayNames[$dataType] = $dataTypeClass->getDisplayName() . $suffix;
        }

        return $displayNames;
    }
    
}
