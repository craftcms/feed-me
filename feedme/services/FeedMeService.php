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
        // RSS/Atom use XML
        $dataType = ($dataType == 'rss' || $dataType == 'atom') ? 'xml' : $dataType;

        // Check for third-party data type support
        $dataToLoad = craft()->plugins->call('registerFeedMeDataTypes');

        foreach ($dataToLoad as $plugin => $dataClasses) {
            foreach ($dataClasses as $dataClass) {
                if ($dataClass && $dataClass instanceof BaseFeedMeDataType) {
                    if (StringHelper::toLowercase($dataClass->getDataType()) == $dataType) {
                        return $dataClass;
                    }
                }
            }
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
    

}
