<?php
namespace Craft;

class FeedMeVariable
{
    public function getName()
    {
        $plugin = craft()->plugins->getPlugin('feedMe');
        return $plugin->getName();
    }

    public function getSelectOptions($options, $includeNull = true) {
        if ($includeNull) { $values[null] = 'None'; }

        foreach($options as $key => $value) {
            $values[$value['id']] = $value['name'];
        }
        return $values;
    }

    public function getGroups()
    {
        return craft()->feedMe_entry->getGroups();
    }

    public function logs()
    {
        return craft()->feedMe_logs->show();
    }

    public function log($logs)
    {
        return craft()->feedMe_logs->showLog($logs);
    }

    public function feed($options = array())
    {
        return craft()->feedMe_feeds->getFeedForTemplate($options);
    }
        

    // Helper function for handling Matrix fields
    public function getMatrixBlocks($fieldId)
    {
        return craft()->matrix->getBlockTypesByFieldId($fieldId);
    }

    public function getSuperTableBlocks($fieldId)
    {
        return craft()->superTable->getBlockTypesByFieldId($fieldId);
    }



}
