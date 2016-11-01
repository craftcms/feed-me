<?php
namespace Craft;

class FeedMe_LicenseModel extends BaseModel
{
    // Public Methods
    // =========================================================================

    public function decode()
    {
        echo JsonHelper::decode($this);
    }


    // Protected Methods
    // =========================================================================

    protected function defineAttributes()
    {
        return array(
            'requestUrl'  => array(AttributeType::String),
            'requestIp'   => array(AttributeType::String),
            'requestTime' => array(AttributeType::String),
            'requestPort' => array(AttributeType::String),

            'craftBuild'   => array(AttributeType::String),
            'craftVersion' => array(AttributeType::String),
            'craftEdition' => array(AttributeType::String),
            'craftTrack'   => array(AttributeType::String),
            'userEmail'    => array(AttributeType::String),

            'licenseKey'      => array(AttributeType::String),
            'licensedEdition' => array(AttributeType::String),
            'requestProduct'  => array(AttributeType::String),
            'requestVersion'  => array(AttributeType::String),
            'data'            => array(AttributeType::Mixed),
            'errors'          => array(AttributeType::Mixed),
        );
    }
}
