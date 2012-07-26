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
 * @deprecated Messaging Extension 0.0.2
 */
abstract class Xcom_Xfabric_Model_Message_Abstract
    extends Mage_Core_Model_Abstract
{
    const AVRO_BINARY = 'binary';
    const AVRO_JSON = 'json';

    const SCHEMA_VER_SRV_HDR = 'HTTP_X_XC_SCHEMA_VERSION';
    const SCHEMA_URI_HEADER = 'X-XC-SCHEMA-URI';
    const CORRELATION_ID_HEADER = 'X-XC-RESULT-CORRELATION-ID';
    const DESTINATION_ID_HEADER = 'X-XC-DESTINATION-ID';
    const PUBLISHER_PSEUDONYM_HEADER = 'X-XC-PUBLISHER-PSEUDONYM';


    /** @var array */
    protected $_body;

    /** @var string */
    protected $_topic; //TODO

    /** @var Xcom_Xfabric_Model_Schema */
    protected $_schema;

    /** @var string */
    protected $_schemaFile;

    /** @var string */
    protected $_schemaRecordName;

    /** @var string */
    protected $_encoding;

    /** @var Xcom_Xfabric_Model_Encoder_Interface */
    protected $_encoder;

    /** @var array */
    protected $_allowedEncoding = array(self::AVRO_BINARY, self::AVRO_JSON);

    /** @var array */
    protected $_headers = array();

    /** @var string */
    protected $_namespace = '';

    /**
     * Schema version of the message (via X-XC-SCHEMA-VERSION)
     * @var string
     */
    protected $_schemaVersion = '1.0.0';

    /**
     * Schema URI (via X-XC-SCHEMA-URI)
     * @var string
     */
    protected $_schemaUri = '';

    static public $topic = NULL;

    /**
     * Correlation ID (unique identificator)
     *
     * The value of this property used for
     * request and response headers.
     *
     * @var string
     */
    protected $_correlationId;


    abstract public function process();

    public function getHeaders()
    {
        return $this->_headers;
    }

    /**
     * Retrieve headers for curl CURLOPT_HTTPHEADER option
     *
     * @return array
     */
    public function getCurlHeaders()
    {
        $headers = array();
        foreach ($this->getHeaders() as $headerName => $headerValue) {
            $headers[] = sprintf("%s: %s", $headerName, $headerValue);
        }
        return $headers;
    }

    public function getHeader($headerName)
    {
        $headers = $this->getHeaders();
        if (!empty($headers[strtolower($headerName)])) {
            return trim($headers[strtolower($headerName)]);

        }
        if (!empty($headers[strtoupper($headerName)])) {
            return trim($headers[strtoupper($headerName)]);
        }
        return null;
    }

    /**
     * Add header
     *
     * Add header. If header with the same name already exists,
     * override it.
     * If header value is empty, then unset header.
     *
     * @param $headerName
     * @param $headerValue
     * @return Xcom_Xfabric_Model_Message_Abstract
     */
    public function addHeader($headerName, $headerValue)
    {
        if (empty($headerValue)) {
            unset($this->_headers[$headerName]);
        }
        else {
            $this->_headers[$headerName] = $headerValue;
        }
        return $this;
    }

    /**
     * Set headers for Request and Response objects.
     *
     * @param array $headers
     * @return Xcom_Xfabric_Model_Message_Abstract
     */
    public function setHeaders(array $headers)
    {
        $this->_headers = $headers;
        return $this;
    }

    public function getBody()
    {
        return $this->_body;
    }

    /**
     * @param $body
     * @return Xcom_Xfabric_Model_Message_Abstract
     */
    public function setBody($body)
    {
        $this->_body = $body;
        return $this;
    }

    public function getTopic()
    {
        return $this->_topic;
    }

    public function getSchemaRecordName()
    {
        return $this->_schemaRecordName;
    }

    public function getSchema()
    {
        if (!$this->_schema) {
            $this->_initSchema();
        }
        return $this->_schema;
    }

    /**
     * @param $name
     * @return Xcom_Xfabric_Model_Message_Abstract
     */
    public function setTopic($name)
    {
        $this->_topic = (string)$name;
        return $this;
    }

    /**
     * @param $version
     * @return Xcom_Xfabric_Model_Message_Abstract
     */
    public function setSchemaVersion($version)
    {
        $this->_schemaVersion = $version;
        return $this;
    }

    /**
     * Set the schema URI.
     *
     * @param $uri
     * @return Xcom_Xfabric_Model_Message_Abstract
     */
    public function setSchemaUri($uri)
    {
        $this->_schemaUri = $uri;
        return $this;
    }

    public function setSchemaRecordName($recordName)
    {
        $this->_schemaRecordName = (string)$recordName;
        return $this;
    }

    public function setEncoding($encoding = null)
    {
        if (!$encoding) {
            $encoding = Mage::getStoreConfig('xfabric/connection_settings/encoding');
        }

        if (!$this->isEncodingAllowed($encoding)) {
            $this->_throwException("Encoding is not supported by the message: %s", $encoding);
        }
        $this->_encoding = (string)$encoding;

        return $this;
    }

    public function getEncoder()
    {
        if (!$this->_encoder) {
            $this->setEncoder();
        }
        return $this->_encoder;
    }

    public function setEncoder($encoding = null)
    {
        if (!$this->_encoding || $encoding) {
            $this->setEncoding($encoding);
        }
        switch ($this->_encoding) {
            case self::AVRO_BINARY:
                $this->_encoder = Mage::getModel('xcom_xfabric/encoder_avro');
                break;
            case self::AVRO_JSON:
                $this->_encoder = Mage::getModel('xcom_xfabric/encoder_json');
                break;
        }
        return $this;
    }

    /**
     * Get the schema version
     * @return string
     */
    public function getSchemaVersion()
    {
        return $this->_schemaVersion;
    }

    /**
     * Get the schema URI
     * @return string
     */
    public function getSchemaUri()
    {
        return $this->_schemaUri;
    }

    public function isEncodingAllowed($encoding)
    {
        return in_array($encoding, $this->_allowedEncoding);
    }

    public function resetHeaders()
    {
        $this->_headers = array();
        return $this;
    }

    protected function _initSchema()
    {
        $options = array(
            'schema_uri' => $this->getSchemaUri()
        );
        $this->_schema = Mage::getModel('xcom_xfabric/schema', $options);
        return $this;
    }

    protected function _throwException($message)
    {
        throw Mage::exception('Xcom_Xfabric', Mage::helper('xcom_xfabric')->__($message));
    }

    public function fakeBeforeSend()
    {
        return $this;
    }

    /**
     * Generate UID (unique identificator)
     *
     * @return string
     */
    final public function uid()
    {
        return md5(uniqid(mt_rand(), true));
    }

    abstract public function getCorrelationId();

    /**
     * Set Correlation ID value
     *
     * @param $value
     * @return Xcom_Xfabric_Model_Message_Request
     */
    public function setCorrelationId($value)
    {
        $this->_correlationId = $value;
        return $this;
    }
}
