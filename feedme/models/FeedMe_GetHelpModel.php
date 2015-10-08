<?php
namespace Craft;

class FeedMe_GetHelpModel extends BaseModel
{
    // Public Methods
    // =========================================================================

    public function rules()
    {
        // maxSize is 3MB
        return array_merge(parent::rules(), array(
            array('attachment', 'file', 'maxSize' => 3145728, 'allowEmpty' => true),
        ));
    }

    // Protected Methods
    // =========================================================================

    protected function defineAttributes()
    {
        return array(
            'fromEmail'         => array(AttributeType::Email, 'required' => true, 'label' => 'Your Email'),
            'feedIssue'         => array(AttributeType::String, 'required' => true),
            'message'           => array(AttributeType::String, 'required' => true),
            'attachLogs'        => AttributeType::Bool,
            'attachSettings'    => AttributeType::Bool,
            'attachFeed'        => AttributeType::Bool,
            'attachFields'      => AttributeType::Bool,
            'attachment'        => AttributeType::Mixed,
        );
    }
}
