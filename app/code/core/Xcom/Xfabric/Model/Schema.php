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
 * @package    Xcom_Xfabric
 * @copyright   Copyright (c) 2010 Magento Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

class Xcom_Xfabric_Model_Schema implements Xcom_Xfabric_Model_Schema_Interface
{
    /**
     * @var array All records that are mentioned in main schema
     */
    protected $_parts = array();

    /**
     * Array of records that is already loaded
     * @var array
     */
    protected $_loadedRecords = array();

    /**
     * @var array  full decoded schema of the protocol
     */
    protected $_protocolSchema = array();

    /**
     * @var string Name of the message schema
     */
    protected $_schemaName = null;

    /**
     * @var string Namespace of the protocol
     */
    protected $_namespace = null;

    /**
     * @var array Errors happened during validation
     */
    protected $_validationErrors = array();

    /**
     * Simple Avro Types
     * @var array
     */
    protected $_simpleTypes = array('string', 'null', 'int', 'boolean', 'long', 'bytes', 'double');

    /**
     * @var array
     */
    protected $_messageSchema = array();

    protected $_rawSchema = null;

    /**
     * @var string Schema location (uri)
     */
    protected $_schemaUri = '';

    /**
     * @var array Schema location (uri) protocols allowed
     */
    protected $_schemaProtocols = array('http', 'https');

    /**
     * @var string Schema version
     */
    protected $_schemaVersion = '';


    /**
     * Initialize protocol and schema by message topic
     *
     * @param string $schemaRecordName
     * @param string $schemaVersion
     * @param string $schemaUri
     * @param string $namespace
     * @return Xcom_Xfabric_Model_Schema
     */
    public function __construct($options)
    {
        //$schemaRecordName, $schemaVersion = '', $schemaUri = '', $namespace = ''
        /*
         * $schemaOptions = array(
            'schema_uri'
            'topic' => $topic,
            'schema_version' => $schemaVersion,
            'ontology_url' => $this->_ontologyUri
        );
         */
        //$this->_schemaName      = $schemaRecordName;
        //$this->_schemaVersion   = $schemaVersion;
        $scheme = parse_url($options['schema_uri'], PHP_URL_SCHEME);
        if (empty($scheme) || !in_array($scheme, array('http', 'https'))) {
            throw new Exception('Ontology URI is empty or protocol is not http or https: '
                . $options['schema_uri']);
        }
        $this->_schemaUri       = $options['schema_uri'];
        //$this->_namespace       = $namespace;
        $this->_loadProtocol();
        return $this;
    }

    public function getSchemaUri()
    {
        return $this->_schemaUri;
    }


    /**
     * Load the file with protocole and decode it
     *
     * @return Xcom_Xfabric_Model_Schema
     * @throws Mage_Core_Exception
     */
    protected function _loadProtocol()
    {
        $schema = $this->_getSchema();
        if (!empty($schema)) {
            $this->_rawSchema = $schema;
            $this->_protocolSchema = json_decode($this->_rawSchema, true);
            $this->_namespace = $this->_protocolSchema['namespace'];
        } else {
            throw Mage::exception('Xcom_Xfabric',
                Mage::helper('xcom_xfabric')->__("Schema is empty for URI: %s", $this->_schemaUri));
        }
        return $this;
    }

    /**
     * Return JSON of the schemaUri specified
     * The schema will be downloaded or got from cache
     *
     * @return string
     */
    protected function _getSchema()
    {
        $scheme = parse_url($this->_schemaUri, PHP_URL_SCHEME);
        if (empty($scheme) || !in_array($scheme, $this->_schemaProtocols)) {
            throw Mage::exception('Xcom_Xfabric',
                Mage::helper('xcom_xfabric')->__("Schema URI is empty or not allowed (allowed types: %s) [%s]",
                    implode(',', $this->_schemaProtocols), $this->_schemaUri));
        } else {
            $cache_id = $this->_getSchemaCacheId();
            /* try to fetch schema from cache */
            if (($json = Mage::app()->loadCache($cache_id))) {
                return $json;
            }

            /* Since the use of the Varien http client requires parsing out headers, we use raw curl here*/
            $curl = curl_init($this->_schemaUri);
            curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
            curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, 0);
            curl_setopt($curl, CURLOPT_SSL_VERIFYHOST ,0);
            curl_setopt($curl, CURLOPT_TIMEOUT, 10);
            $result = curl_exec($curl);
            $http_code = curl_getinfo($curl, CURLINFO_HTTP_CODE);
            if ($http_code == 200 || $http_code == 201) {
                if (Mage::app()->useCache('config')) {
                    Mage::app()->saveCache($result, $cache_id);
                }
                return $result;
            } else {
                throw Mage::exception('Xcom_Xfabric',
                    Mage::helper('xcom_xfabric')
                        ->__("Schema with URI %s was failed to retrieve with http_code: %s",
                        $this->_schemaUri, $http_code));
            }
        }
    }

    /**
     * Generate schema cacheId
     *
     * @return string
     */
    protected function _getSchemaCacheId()
    {
        return 'Xcom_' . $this->_schemaName . '_'. sha1($this->_schemaName . $this->_schemaUri . $this->_schemaVersion);
    }


    /**
     * Get unprocessed schema
     * @return null
     */
    public function getRawSchema()
    {
        return $this->_rawSchema;
    }

    /**
     * Get name of the schema
     * @return null|string
     */
    public function getSchemaName()
    {
        return $this->_schemaName;
    }

    /**
     * Get schema of te protocol
     * @return array
     */
    public function getProtocolSchema()
    {
        return $this->_protocolSchema;
    }

    /**
     * Namespace of the protocol
     * @return null|string
     */
    public function getNamespace()
    {
        return $this->_namespace;
    }

    /**
     * Validate data passed to the message against message schema
     * @throws Xcom_Xfabric_Exception
     * @param $messageData
     * @return bool
     */
    public function validate($messageData)
    {
        $result = $this->_validateRecord($this->_schemaName, $this->_schemaName, $messageData);
        if (count($this->_validationErrors) == 0 && $result) {
            return true;
        } else {
            $errorsList = implode("<br>\n", $this->_validationErrors);
            throw Mage::exception('Xcom_Xfabric', $errorsList);
        }
    }

    /**
     * Validate part of message data against type of the part
     * @param $name
     * @param $type
     * @param $data
     * @return bool
     */
    protected function _validate($name, $type, $data)
    {
        if (is_array($type)) {
            if (isset($type['items'])) {
                return $this->_validateArray($name, $type, $data);
            } else {
                return $this->_validateUnion($name, $type, $data);
            }
        } else {
            if (in_array($type, $this->_simpleTypes)) {
                return $this->_validateSimple($name, $type, $data);
            } else if ($type == 'map') {
                $this->_validateMap($name, $type, $data);
            } else {
                return $this->_validateRecord($name, $type, $data);
            }
        }
        return true;
    }

    /**
     * Validate part of the message with type Record
     *
     * @param $name
     * @param $type
     * @param $data
     * @return bool
     */
    protected function _validateRecord($name, $type, $data)
    {
        $schema = $this->getPart($type);
        if (!isset($schema['fields']) && $schema['type'] == 'enum') {
            if (in_array($data, $schema['symbols'])) {
                return true;
            } else {
                $this->_validationErrors[] = Mage::helper('xcom_xfabric')->__('Error in enum ');
                return false;
            }
            /**@TODO Check Enum fields */
        }
        if (is_array($data)) {
            foreach ($schema['fields'] as $field) {
                if (in_array($field['name'], array_keys($data))) {
                    $result = $this->_validate($field['name'], $field['type'], $data[$field['name']]);
                    if (!$result) {
                        $this->_validationErrors[] = Mage::helper('xcom_xfabric')
                            ->__('Field value should be ') . $field['type'] . ': ' . $field['name'];
                    }
                }  else {
                    $this->_validationErrors[] = Mage::helper('xcom_xfabric')->__('No such field in data: ') . $field['name'];
                }
            }
        } else {
            $this->_validationErrors[] = Mage::helper('xcom_xfabric')->__('Record is not complete ') . $type;
        }
        return count($this->_validationErrors) ? false : true;
    }

    /**
     * Validate part of the message with type Map
     *
     * @param $name
     * @param $type
     * @param $data
     * @return bool
     */
    protected function _validateMap($name, $type, $data)
    {
        /**@TODO add validation for MAP type ~ php assoc array */
        return true;
    }

    /**
     * Validate part of the message with type Union
     *
     * @param $name
     * @param $type
     * @param $data
     * @return bool
     */
    protected function _validateUnion($name, $type, $data)
    {
        $unionResult = false;
        foreach ($type as $unionPart) {
            if (is_array($unionPart) && isset($unionPart['type'])) {
                if ($unionPart['type'] == 'array') {
                    $unionResult = $this->_validateArray($name, $unionPart, $data);
                }
            } else if (is_string($unionPart)) {
                $unionResult = $this->_validate($name, $unionPart, $data);
            }
            if ($unionResult) {
                return true;
            }
        }
        if (!$unionResult) {
            $this->_validationErrors[] = Mage::helper('xcom_xfabric')
                ->__("Field doesn't match any of appropriate UNION types: ") . $name;
            return true;
        }
    }

    /**
     * Validate part of the message with type Array
     *
     * @param $name
     * @param $type
     * @param $data
     * @return bool
     */
    protected function _validateArray($name, $type, $data)
    {
        $result = true;
        if (is_array($data)) {
            foreach ($data as $value) {
                if (is_array($type['items'])) {
                    $arrayResult = false;
                    $errors = $this->_validationErrors;
                    foreach ($type['items'] as $singleType) {
                        $valid = $this->_validate($name, $singleType, $value);
                        if ($arrayResult != true && $valid != $arrayResult) {
                            $arrayResult = true;
                            break;
                        }
                    }
                    if (!$arrayResult) {
                        $this->_validationErrors[] = Mage::helper('xcom_xfabric')
                            ->__("Element of the array doesn't match item's type: ") . $name . ', ' . $type['items'];
                        $result = false;
                    } else {
                        $this->_validationErrors = $errors;
                    }
                } else {
                    if (!$this->_validate($name, $type['items'], $value)) {
                        $this->_validationErrors[] = Mage::helper('xcom_xfabric')
                            ->__("Element of the array doesn't match item's type: ") . $name . ', ' . $type['items'];
                        $result = false;
                    }
                }
            }
        } else {
            $this->_validationErrors[] = Mage::helper('xcom_xfabric')->__('Field value should be an array ') . $name;
            return false;
        }
        return $result;
    }

    /**
     * Validate part of the message with simple PHP type
     *
     * @param $name
     * @param $type
     * @param $data
     * @return bool
     */
    protected function _validateSimple($name, $type, $data)
    {
        switch ($type) {
            case 'string':
                if (!is_string($data)) {
                    return false;
                }
                break;
            case 'null':
                if (!is_null($data)) {
                    return false;
                }
                break;
            case 'int':
                if (!is_int($data)) {
                    return false;
                }
                break;
            case 'boolean':
                if (!is_bool($data)) {
                    return false;
                }
                break;
            case 'long':
                if (!is_long($data)) {
                    return false;
                }
                break;
            case 'bytes':
                if (!is_string($data)) {
                    return false;
                }
                break;
            case 'double':
                if (!is_double($data)) {
                    return false;
                }
                break;
        }
        return true;
    }

    /**
     * Get particular part of the schema by its name
     * @throws Mage_Core_Exception
     * @param $partName
     * @return bool
     */
    public function getPart($partName)
    {
        if (isset($this->_parts[$partName])) {
            return $this->_parts[$partName];
        }
        throw Mage::exception('Xcom_Xfabric', Mage::helper('xcom_xfabric')
            ->__("The schema for the type has not been loaded: %s", $partName));
    }

    /**
     * Get all parts of message schema
     * @return array
     */
    public function getParts()
    {
        return $this->_parts;
    }
}
