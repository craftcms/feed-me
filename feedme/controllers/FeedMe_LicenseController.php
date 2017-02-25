<?php
namespace Craft;

class FeedMe_LicenseController extends BaseController
{

    // Public Methods
    // =========================================================================

    public function actionEdit()
    {
        $licenseKey = craft()->feedMe_license->getLicenseKey();

        $this->renderTemplate('feedme/settings/license', [
            'hasLicenseKey' => ($licenseKey !== null)
        ]);
    }

    public function actionGetLicenseInfo()
    {
        $this->requirePostRequest();
        $this->requireAjaxRequest();

        return $this->_sendResponse(craft()->feedMe_license->getLicenseInfo());
    }

    public function actionUnregister()
    {
        $this->requirePostRequest();
        $this->requireAjaxRequest();

        return $this->_sendResponse(craft()->feedMe_license->unregisterLicenseKey());
    }

    public function actionTransfer()
    {
        $this->requirePostRequest();
        $this->requireAjaxRequest();

        return $this->_sendResponse(craft()->feedMe_license->transferLicenseKey());
    }

    public function actionUpdateLicenseKey()
    {
        $this->requirePostRequest();
        $this->requireAjaxRequest();

        $licenseKey = craft()->request->getRequiredPost('licenseKey');

        // Are we registering a new license key?
        if ($licenseKey) {
            // Record the license key locally
            try {
                craft()->feedMe_license->setLicenseKey($licenseKey);
            } catch (InvalidLicenseKeyException $e) {
                $this->returnErrorJson(Craft::t('The license key is invalid.'));
            }

            return $this->_sendResponse(craft()->feedMe_license->registerPlugin($licenseKey));
        } else {
            // Just clear our record of the license key
            craft()->feedMe_license->setLicenseKey(null);
            craft()->feedMe_license->setLicenseKeyStatus(LicenseKeyStatus::Unknown);
            return $this->_sendResponse();

        }
    }


    // Private Methods
    // =========================================================================

    private function _sendResponse($success = true)
    {
        if ($success) {
            $this->returnJson(array(
                'success'          => true,
                'licenseKey'       => craft()->feedMe_license->getLicenseKey(),
                'licenseKeyStatus' => craft()->plugins->getPluginLicenseKeyStatus('FeedMe'),
            ));
        } else {
            //$this->returnErrorJson(craft()->feedMe_license->error);
            $this->returnErrorJson(Craft::t('An unknown error occurred.'));
        }
    }

}
