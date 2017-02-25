<?php
namespace Craft;

class FeedMe_License
{
    private $etConnectFailureKey = 'sgroupConnectFailure';
    private $etRecentPhoneHome = 'sgroupPhonedHome';
    private $requestProduct;
    private $requestVersion;
    private $requestLicenseKey;

    // Properties
    // =========================================================================

    private $_endpoint;
    private $_timeout = 30;
    private $_model;
    private $_allowRedirects = true;
    private $_userAgent;
    private $_connectTimeout = 30;

    // Public Methods
    // =========================================================================

    public function __construct($endpoint, $product, $productVersion, $licenseKey = '')
    {
        $this->requestProduct = $product;
        $this->requestVersion = $productVersion;

        $endpoint .= craft()->config->get('endpointSuffix');
        $this->_endpoint = $endpoint;
        $userEmail = craft()->userSession->getUser() ? craft()->userSession->getUser()->email : '';

        // Cater for pre-Craft 2.6.2951
        if (version_compare(craft()->getVersion(), '2.6.2951', '<')) {
            $version = craft()->getVersion() . '.' . craft()->getBuild();
        } else {
            $version = craft()->getVersion();
        }

        $attributes = array(
            'requestUrl'  => craft()->request->getHostInfo() . craft()->request->getUrl(),
            'requestIp'   => craft()->request->getIpAddress(),
            'requestTime' => DateTimeHelper::currentTimeStamp(),
            'requestPort' => craft()->request->getPort(),

            'craftVersion' => $version,
            'craftEdition' => craft()->getEdition(),
            'userEmail'    => $userEmail,

            'requestProduct' => $this->requestProduct,
            'requestVersion' => $this->requestVersion,
            'licenseKey'     => $licenseKey
        );

        $this->_model = new FeedMe_LicenseModel($attributes);
        
        $this->_userAgent = 'Craft/' . $version;
    }

    public function getTimeout()
    {
        return $this->_timeout;
    }

    public function getConnectTimeout()
    {
        return $this->_connectTimeout;
    }

    public function setAllowRedirects($allowRedirects)
    {
        $this->_allowRedirects = $allowRedirects;
    }

    public function getAllowRedirects()
    {
        return $this->_allowRedirects;
    }

    public function getModel()
    {
        return $this->_model;
    }

    public function setData($data)
    {
        $this->_model->data = $data;
    }

    public function setHandle($handle)
    {
        $this->_model->handle = $handle;
    }

    public function phoneHome($force = false)
    {
        if ($force) {
            if (craft()->cache->get($this->etConnectFailureKey)) {
                craft()->cache->delete($this->etConnectFailureKey);
            }
            if (craft()->cache->get($this->etRecentPhoneHome)) {
                craft()->cache->delete($this->etRecentPhoneHome);
            }
        }

        try {

            if (!craft()->cache->get($this->etConnectFailureKey)) {
                $data = $this->_model->getAttributes(null, true);

                $client = new \Guzzle\Http\Client();
                $client->setUserAgent($this->_userAgent, true);

                $options = array(
                    'timeout'         => $this->getTimeout(),
                    'connect_timeout' => $this->getConnectTimeout(),
                    'allow_redirects' => $this->getAllowRedirects(),
                );

                $request = $client->post($this->_endpoint, null, $data, $options);

                // Potentially long-running request, so close session to prevent session blocking on subsequent requests.
                craft()->session->close();

                $response = $request->send();

                if ($response->isSuccessful()) {

                    // Clear the connection failure cached item if it exists.
                    if (craft()->cache->get($this->etConnectFailureKey)) {
                        craft()->cache->delete($this->etConnectFailureKey);
                    }

                    // Clear the connection failure cached item if it exists.
                    craft()->cache->set($this->etRecentPhoneHome, true, 300);

                    $etModel = craft()->feedMe_license->decodeEtModel($response->getBody());

                    if ($etModel) {
                        return $etModel;
                    } else {
                        FeedMePlugin::log('Error in calling ' . $this->_endpoint . ' Response: ' . $response->getBody(), LogLevel::Warning);

                        if (craft()->cache->get($this->etConnectFailureKey)) {
                            // There was an error, but at least we connected.
                            craft()->cache->delete($this->etConnectFailureKey);
                        }
                    }
                } else {
                    FeedMePlugin::log('Error in calling ' . $this->_endpoint . ' Response: ' . $response->getBody(), LogLevel::Warning);

                    if (craft()->cache->get($this->etConnectFailureKey)) {
                        // There was an error, but at least we connected.
                        craft()->cache->delete($this->etConnectFailureKey);
                    }
                }
            }
        } // Let's log and rethrow any EtExceptions.
        catch (EtException $e) {
            FeedMePlugin::log('Error in ' . __METHOD__ . '. Message: ' . $e->getMessage(), LogLevel::Error);

            if (craft()->cache->get($this->etConnectFailureKey)) {
                // There was an error, but at least we connected.
                craft()->cache->delete($this->etConnectFailureKey);
            }

            throw $e;
        } catch (\Exception $e) {
            FeedMePlugin::log('Error in ' . __METHOD__ . '. Message: ' . $e->getMessage(), LogLevel::Error);

            // Cache the failure for 5 minutes so we don't try again.
            craft()->cache->set($this->etConnectFailureKey, true, 300);
        }

        return null;
    }

}
