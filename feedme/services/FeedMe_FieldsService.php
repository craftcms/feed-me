<?php
namespace Craft;

use Cake\Utility\Hash as Hash;

class FeedMe_FieldsService extends BaseApplicationComponent
{
    // Properties
    // =========================================================================

    private $_fields = array();
    private $_services = array();


    // Public Methods
    // =========================================================================

    public function __construct()
    {
        // Load all fieldtypes once
        $this->_fields = craft()->fields->getAllFields('handle');
    }

    public function prepForFieldType($element, $data, $handle, $options = array())
    {
        $field = null;

        if (isset($options['field'])) {
            $field = $options['field'];
        } else {
            if (isset($this->_fields[$handle])) {
                $field = $this->_fields[$handle];
            }
        }

        if ($field) {
            $service = $this->_getService($field->type);

            // Give the field some context of the owning element
            $field->getFieldType()->element = $element;

            // Get data for the field we're mapping to - can be all sorts of logic here
            return $service->prepFieldData($element, $field, $data, $handle, $options);
        }

        return $data;
    }

    public function postForFieldType($element, &$data, $handle, $field = null)
    {
        if (isset($this->_fields[$handle])) {
            $field = $this->_fields[$handle];
        }

        if ($field) {
            $service = $this->_getService($field->type);

            // Give the field some context of the owning element
            $field->getFieldType()->element = $element;

            // Get data for the field we're mapping to - can be all sorts of logic here
            if (method_exists($service, 'postFieldData')) {
                return $service->postFieldData($element, $field, $data, $handle);
            }
        }
    }

    public function checkExistingFieldData($element, &$data, $handle, $field = null)
    {
        if (isset($this->_fields[$handle])) {
            $field = $this->_fields[$handle];
        }

        if ($field) {
            $service = $this->_getService($field->type);

            // Give the field some context of the owning element
            $field->getFieldType()->element = $element;

            // Get data for the field we're mapping to - can be all sorts of logic here
            if (method_exists($service, 'checkExistingFieldData')) {
                return $service->checkExistingFieldData($element, $field, $data, $handle);
            }
        }
    }

    // Function for third-party plugins to provide custom mapping options for fieldtypes
    public function getFieldMapping($fieldType)
    {
        return $this->_getService($fieldType)->getMappingTemplate();
    }



    // Private Methods
    // =========================================================================

    private function _getService($fieldType)
    {
        if (isset($this->_services[$fieldType])) {
            return $this->_services[$fieldType];
        }

        // Get the service for the Field Type we're dealing with
        if (!$service = craft()->feedMe->getFieldTypeService($fieldType)) {
            throw new Exception(Craft::t('Unknown FieldType Service called.'));
        } else {
            $this->_services[$fieldType] = $service;
        }

        return $service;
    }
}
