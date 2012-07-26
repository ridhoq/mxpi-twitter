<?php
/**
 * Magento
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@magentocommerce.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade Magento to newer
 * versions in the future. If you wish to customize Magento for your
 * needs please refer to http://www.magentocommerce.com for more information.
 *
 * @category    Xcom
 * @package     Xcom_Xfabric
 * @copyright   Copyright (c) 2010 Magento Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/**
 * @method string        getDestinationId()
 * @method string        getCapabilityId()
 * @method string        getCapabilityName()
 * @method string        getFabricUrl()
 * @method Varien_Object getAuthorizations()
 */
class Xcom_Xfabric_Model_Authorization extends Varien_Object implements Xcom_Xfabric_Model_Authorization_Interface
{
    /** Used for references to token part of authorization data */
    const TOKEN = 'token';

    /** Used for references to pseudonym part of authorization data */
    const PSEUDONYM = 'pseudonym';

    /**
     * @var array Allowed values of 'type' field inside 'authorizations' section of authorization file
     */
    protected $_allowedAuthorizationTypes = array('self', 'tenant', 'xfabric');

    /**
     * @var array Required fields for each record inside 'authorizations' section
     */
    protected $_authorizationTypeFields = array(
        'status', 'bearer_token', 'tenant_name', 'tenant_id', 'tenant_pseudonym'
    );

    /**
     * @var array Fields in the first level of authorization array values of which should be string
     */
    protected $_stringFields = array('capability_name', 'capability_id', 'fabric_url', 'destination_id');

    /**
     * @var array These fields allowed to be NULL for XFABRIC authorization record
     */
    protected $_xfabricEmptyFields = array('tenant_id', 'tenant_pseudonym', 'tenant_name');

    /**
     * @var bool Keeps flag whether current model instance has loaded and valid authorization data
     */
    protected $_hasAuthorizationData = false;

    /**
     * Converts camel case into underscore (e.g. tenantId --> tenant_id). Uses a workaround for non-compatible format
     * of field 'fabricURL'.
     *
     * @param string $field
     * @return string
     */
    protected function _underscore($field)
    {
            // _underscore() unable to correctly process strings with several uppercase letters in a row
        return parent::_underscore($field == 'fabricURL' ? 'fabricUrl' : $field);
    }

    /**
     * Returns whether supplied array is valid authorization data
     *
     * @param array $authData
     * @return Xcom_Xfabric_Model_Authorization
     */
    public function validate($authData)
    {
        if (!is_array($authData)) {
            throw Mage::exception('Xcom_Xfabric',
                Mage::helper('xcom_xfabric')->__('Authorization data validation failed: array expected')
            );
        }

        foreach ($this->_stringFields as $key) {
            if (!array_key_exists($key, $authData)) {
                throw Mage::exception('Xcom_Xfabric',
                    Mage::helper('xcom_xfabric')
                        ->__('Authorization data validation failed: expected field does not exist: %s', $key)
                );
            }
        }

        try {
            Zend_Uri_Http::fromString($authData['fabric_url']);
        } catch (Zend_Uri_Exception $e) {
            throw Mage::exception('Xcom_Xfabric',
                Mage::helper('xcom_xfabric')->__('Authorization data validation failed: URI is not valid')
            );
        }

        if (!array_key_exists('authorizations', $authData) || !is_array($authData['authorizations'])) {
            throw Mage::exception('Xcom_Xfabric',
                Mage::helper('xcom_xfabric')
                    ->__('Authorization data validation failed: "authorization" section missing or not an array')
            );
        }

        if (count($authData['authorizations']) != count($this->_allowedAuthorizationTypes)) {
            throw Mage::exception('Xcom_Xfabric',
                Mage::helper('xcom_xfabric')->__(
                    'Authorization data validation failed: quantity of fields inside "authorization" section is wrong'
                )
            );
        }

        foreach ($this->_allowedAuthorizationTypes as $type) {
            if (!array_key_exists($type, $authData['authorizations'])) {
                throw Mage::exception('Xcom_Xfabric',
                    Mage::helper('xcom_xfabric')
                        ->__('Authorization data validation failed: expected authorization record not found: %s', $type)
                );
            }

            $authorization = $authData['authorizations'][$type];

            if (!is_array($authorization) || count($authorization) != count($this->_authorizationTypeFields)) {
                throw Mage::exception('Xcom_Xfabric',
                    Mage::helper('xcom_xfabric')->__(
                        'Authorization data validation failed: \'%s\' authorization record is not an array or has '
                        . 'incorrect number of fields', $type
                    )
                );
            }

            foreach ($this->_authorizationTypeFields as $field) {
                if (empty($authorization[$field])) {
                    if ($type == 'xfabric') {
                        if (in_array($field, $this->_xfabricEmptyFields) && array_key_exists($field, $authorization)) {
                            // These fields allowed to be empty for 'xfabric' type, it's ok
                            continue;
                        }
                    }
                    throw Mage::exception('Xcom_Xfabric',
                        Mage::helper('xcom_xfabric')->__(
                            'Authorization data validation failed: \'%s\' field not found in \'%s\' authorization '
                            . 'section',
                            $field, $type
                        )
                    );
                }
            }
        }

        return $this;
    }

    /**
     * Whether current model instance has loaded and valid authorization data
     *
     * @return bool
     */
    public function hasAuthorizationData()
    {
        return $this->_hasAuthorizationData;
    }

    /**
     * Recursively convert all array keys from camel case to underscore
     *
     * @param mixed $data
     * @return mixed
     */
    protected function _convertKeysToUnderscore($data)
    {
        if (is_array($data)) {
            $newArray = array();

            foreach ($data as $key => $value) {
                $newArray[is_string($key) ? $this->_underscore($key) : $key] = $this->_convertKeysToUnderscore($value);
            }

            return $newArray;
        }

        return $data;
    }

    /**
     * Import structure from authorization file in JSON format
     *
     * @param string $content Contents of the file
     * @return Xcom_Xfabric_Model_Authorization
     */
    public function importFile($content)
    {
        if (is_string($content)) {
            try {
                $authData = $this->_convertKeysToUnderscore(Mage::helper('core')->jsonDecode($content));
            } catch (Zend_Json_Exception $e) {
                throw Mage::exception('Xcom_Xfabric',
                    Mage::helper('xcom_xfabric')->__('Submitted content doesn\'t look like valid JSON string')
                );
            }

            if (isset($authData['authorizations']) && is_array($authData['authorizations'])) {
                $authorizations = array();

                // Convert structure of 'authorizations' section
                foreach ($authData['authorizations'] as $authorization) {
                    if (array_key_exists('type', $authorization)) {
                        $type = strtolower($authorization['type']);
                        unset($authorization['type']);
                        $authorizations[$type] = $authorization;
                    } else {
                        throw Mage::exception('Xcom_Xfabric',
                            Mage::helper('xcom_xfabric')->__(
                                'Error parsing structure: no "type" section inside one of the authorization records'
                            )
                        );
                    }
                }

                $authData['authorizations'] = $authorizations;
                $this->setAuthorizationData($authData);
            } else {
                throw Mage::exception('Xcom_Xfabric',
                    Mage::helper('xcom_xfabric')->__('Error parsing structure: no "authorizations" section found')
                );
            }
        } else {
            throw Mage::exception('Xcom_Xfabric',
                Mage::helper('xcom_xfabric')->__('Submitted content doesn\'t look like valid JSON string')
            );
        }

        return $this;
    }

    /**
     * Checks and records (if valid) authorization data into model
     *
     * @param array $authData
     * @return Xcom_Xfabric_Model_Authorization
     */
    public function setAuthorizationData($authData)
    {
        $this->validate($authData);
        $authorizations = array();

        foreach ($authData['authorizations'] as $type => $authorization) {
            $authorizations[$type] = new Varien_Object($authorization);
        }

        $authData['authorizations'] = new Varien_Object($authorizations);
        $this->addData($authData);
        $this->_hasAuthorizationData = true;

        return $this;
    }

    /**
     * Saves authorization data into config
     *
     * @return Xcom_Xfabric_Model_Authorization
     */
    public function save()
    {
        if ($this->hasAuthorizationData()) {
            $configModel = Mage::app()->getConfig();
            $configPath = 'xfabric/connection_settings/';

            foreach ($this->_stringFields as $key) {
                $configModel->saveConfig($configPath . $key, $this->getData($key));
            }

            $configPath .= 'authorizations/';

            foreach ($this->getAuthorizations()->getData() as $type => $authObj) {
                /* @var $authObj Varien_Object */
                foreach ($authObj->getData() as $key => $value) {
                    $configModel->saveConfig($configPath . $type . '/' . $key, is_null($value) ? '' : $value);
                }
            }
        }

        return $this;
    }

    /**
     * Load authorization data from config
     *
     * @return Xcom_Xfabric_Model_Authorization
     */
    public function load()
    {
        if (Mage::app()->getConfig()->getNode('default/xfabric/connection_settings/authorizations')) {
            $connectionSettings = Mage::app()->getConfig()->getNode('default/xfabric/connection_settings')->asArray();

            try {
                $this->setAuthorizationData($connectionSettings);
            } catch (Xcom_Xfabric_Exception $e) {
                throw Mage::exception('Xcom_Xfabric',
                    Mage::helper('xcom_xfabric')->__('XFabric: loaded authorization data is not valid')
                );
            }
        }

        return $this;
    }

    /**
     * Load file and import JSON authorization data
     *
     * @param string $path Path to file
     * @throws Mage_Core_Exception
     * @return Xcom_Xfabric_Model_Authorization
     */
    public function loadFile($path)
    {
        if (!file_exists($path) || !is_readable($path) || is_dir($path)) {
            throw Mage::exception('Xcom_Xfabric', Mage::helper('xcom_xfabric')->__('Unable to read from %s', $path));
        }

        $this->importFile(file_get_contents($path));
        return $this;
    }

    /**
     * Revoke authorization data from storage
     *
     * @return Xcom_Xfabric_Model_Authorization
     */
    public function revoke()
    {
        $configPath = 'xfabric/connection_settings/';
        $configModel = Mage::app()->getConfig();

        foreach ($this->_stringFields as $key) {
            $configModel->deleteConfig($configPath . $key);
        }

        $configPath .= 'authorizations/';

        foreach ($this->_allowedAuthorizationTypes as $type) {
            foreach ($this->_authorizationTypeFields as $field) {
                $configModel->deleteConfig($configPath . $type . '/' . $field);
            }
        }

        return $this;
    }

    public function getSelfData($param)
    {
        if ($param == self::TOKEN) {
            return  $this->getAuthorizations()->getSelf()->getBearerToken();
        }
    }

    public function getBearerData($param)
    {
        if ($param == self::TOKEN) {
            return  $this->getAuthorizations()->getTenant()->getBearerToken();
        }
        if ($param == self::PSEUDONYM) {
            return  $this->getAuthorizations()->getTenant()->getTenantPseudonym();
        }
    }

    public function getFabricData($param)
    {
        if ($param == self::TOKEN) {
            return  $this->getAuthorizations()->getXfabric()->getBearerToken();
        }
    }

    public function getDestinationId()
    {
        return $this->getData('destination_id');
    }
    public function getCapabilityId()
    {
        return $this->getData('capability_id');
    }

    public function getCapabilityName()
    {
        return $this->getData('capability_name');
    }

    public function getFabricUrl()
    {
        return $this->getData('fabric_url');
    }

    /**
     * Returns onboarding URI (merchant is registered through it)
     *
     * @return string
     */
    public function getOnboardingUri()
    {
        return (string)Mage::app()->getConfig()->getNode('default/xfabric/connection_settings/onboarding_uri');
    }

    public function getEndpointUrl()
    {
        return str_replace('http://', 'https://', Mage::getUrl('xfabric/endpoint'));
    }

    /**
     * Returns config which is supposed to be sent to the onboarding URI
     *
     * @return array
     */
    public function getFabricConfigInfo()
    {
        return array(
            'store_instance_name' => (string)Mage::app()->getConfig()->getNode(
                'default/xfabric/connection_settings/onboarding_store_instance_name'
            ),
            'store_endpoint_url' => $this->getEndpointUrl(),
            'is_registered' => $this->hasAuthorizationData(),
        );
    }

    /**
     * Returns true if it can successfully ping url; otherwise it will return false
     *
     * @param string $fabricUrl
     * @throws Mage_Core_Exception
     */
    public function pingConnection($url)
    {
        $c = curl_init($url);
        curl_setopt($c, CURLOPT_NOBODY, true);
        curl_setopt($c, CURLOPT_FOLLOWLOCATION, true);
        curl_setopt($c, CURLOPT_CONNECTTIMEOUT, 2);
        curl_setopt($c, CURLOPT_SSL_VERIFYPEER, false);
        curl_exec($c);
        $code = curl_getinfo($c, CURLINFO_HTTP_CODE);
        curl_close($c);
        return 200 == $code;
    }
}
