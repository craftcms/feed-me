<?php
namespace Craft;

class FeedMe_LicenseService extends BaseApplicationComponent
{
    // Properties
    // =========================================================================

    const Ping = 'https://verbb.io/actions/licensor/edition/ping';
    const GetLicenseInfo = 'https://verbb.io/actions/licensor/edition/getLicenseInfo';
    const RegisterPlugin = 'https://verbb.io/actions/licensor/edition/registerPlugin';
    const UnregisterPlugin = 'https://verbb.io/actions/licensor/edition/unregisterPlugin';
    const TransferPlugin = 'https://verbb.io/actions/licensor/edition/transferPlugin';

    private $plugin;
    private $pingStateKey = 'feedMePhonedHome';
    private $pingCacheTime = 86400;
    private $pluginHandle = 'FeedMe';
    private $pluginVersion;
    private $licenseKey;
    private $edition;


    // Public Methods
    // =========================================================================

    public function init()
    {
        $this->plugin = craft()->plugins->getPlugin('feedMe');
        $this->pluginVersion = $this->plugin->getVersion();
        $this->licenseKey = $this->getLicenseKey();

        $this->edition = $this->plugin->getSettings()->edition;
    }

    public function ping()
    {
        if (craft()->request->isCpRequest()) {
            if (!craft()->cache->get($this->pingStateKey)) {
                $et = new FeedMe_License(static::Ping, $this->pluginHandle, $this->pluginVersion, $this->licenseKey);
                $etResponse = $et->phoneHome();
                craft()->cache->set($this->pingStateKey, true, $this->pingCacheTime);

                return $this->_handleEtResponse($etResponse, false);
            }
        }

        return null;
    }

    public function isProEdition()
    {
        if ($this->getEdition() == 1) {
            return true;
        }

        return false;
    }

    public function getEdition()
    {
        $edition = 0;
        if ($this->edition !== null) {
            if ($this->edition == 1) {
                $edition = 1;
            }
        }

        return $edition;
    }

    public function setEdition($edition)
    {
        $settings = array('edition' => $edition);
        craft()->plugins->savePluginSettings($this->plugin, $settings);
        $this->edition = $edition;
    }

    public function getLicenseKey()
    {
        return craft()->plugins->getPluginLicenseKey('FeedMe');
    }

    public function setLicenseKey($licenseKey)
    {
        craft()->plugins->setPluginLicenseKey('FeedMe', $licenseKey);
        $this->licenseKey = $licenseKey;
    }

    public function getLicenseKeyStatus()
    {
        return craft()->plugins->getPluginLicenseKeyStatus('FeedMe');
    }

    public function setLicenseKeyStatus($licenseKeyStatus)
    {
        craft()->plugins->setPluginLicenseKeyStatus('FeedMe', $licenseKeyStatus);
    }

    public function getLicenseInfo()
    {
        $et = new FeedMe_License(static::GetLicenseInfo, $this->pluginHandle, $this->pluginVersion, $this->licenseKey);
        $etResponse = $et->phoneHome(true);

        return $this->_handleEtResponse($etResponse);
    }

    public function decodeEtModel($attributes)
    {
        if ($attributes) {
            $attributes = JsonHelper::decode($attributes);

            if (is_array($attributes)) {
                $etModel = new FeedMe_LicenseModel($attributes);

                // Make sure it's valid. (At a minimum, localBuild and localVersion should be set.)
                if ($etModel->validate()) {
                    return $etModel;
                }
            }
        }
        return null;
    }

    public function unregisterLicenseKey()
    {
        $et = new FeedMe_License(static::UnregisterPlugin, $this->pluginHandle, $this->pluginVersion, $this->licenseKey);
        $et->phoneHome(true);

        $this->setLicenseKey(null);
        $this->setLicenseKeyStatus(LicenseKeyStatus::Unknown);
        $this->setEdition('0');

        return true;
    }

    public function transferLicenseKey()
    {
        $et = new FeedMe_License(static::TransferPlugin, $this->pluginHandle, $this->pluginVersion, $this->licenseKey);
        $etResponse = $et->phoneHome(true);

        return $this->_handleEtResponse($etResponse);
    }

    public function registerPlugin($licenseKey)
    {
        $et = new FeedMe_License(static::RegisterPlugin, $this->pluginHandle, $this->pluginVersion, $licenseKey);
        $etResponse = $et->phoneHome(true);

        return $this->_handleEtResponse($etResponse);
    }



    // Private Methods
    // =========================================================================

    private function _handleEtResponse($etResponse, $log = true)
    {
        if (!empty($etResponse->data['success'])) {
            // Set the local details
            $this->setEdition('1');
            $this->setLicenseKeyStatus(LicenseKeyStatus::Valid);
            return true;
        } else {
            $this->setEdition('0');

            if (!empty($etResponse->errors)) {
                switch ($etResponse->errors[0]) {
                    case 'nonexistent_plugin_license':
                        $this->setLicenseKeyStatus(LicenseKeyStatus::Invalid);
                        break;
                    case 'plugin_license_in_use':
                        $this->setLicenseKeyStatus(LicenseKeyStatus::Mismatched);
                        break;
                    default:
                        $this->setLicenseKeyStatus(LicenseKeyStatus::Unknown);
                }

                if ($log) {
                    FeedMePlugin::log('License error: ' . $etResponse->errors[0], LogLevel::Error, true);
                }
            } else {
                return false;
            }

            return true;
        }
    }
}
