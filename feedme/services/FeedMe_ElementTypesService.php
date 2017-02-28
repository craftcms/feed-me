<?php
namespace Craft;

use Cake\Utility\Hash as Hash;

class FeedMe_ElementTypesService extends BaseApplicationComponent
{
    // Properties
    // =========================================================================

    private $_services = array();


    // Public Methods
    // =========================================================================

    // Function for third-party plugins to provide custom mapping options for fieldtypes
    public function getElementTypeMapping($elementType)
    {
        return $this->_getService($elementType)->getMappingTemplate();
    }



    // Private Methods
    // =========================================================================

    private function _getService($elementType)
    {
        if (isset($this->_services[$elementType])) {
            return $this->_services[$elementType];
        }

        // Get the service for the Field Type we're dealing with
        if (!$service = craft()->feedMe->getElementTypeService($elementType)) {
            throw new Exception(Craft::t('Unknown ElementType Service called.'));
        } else {
            $this->_services[$elementType] = $service;
        }

        return $service;
    }
}
