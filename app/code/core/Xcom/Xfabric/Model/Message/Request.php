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
class Xcom_Xfabric_Model_Message_Request extends Xcom_Xfabric_Model_Message_Abstract
{
    const SCHEMA_VER_HEADER = 'X-XC-SCHEMA-VERSION';

    /** @var array */
    protected $_messageData = array();

    protected $_correlationId;
    protected $_destinationId = null;
    protected $_isWaitResponse = false;
    protected $_isOnBehalfOfTenant = true;

    public function isWaitResponse()
    {
        return $this->_isWaitResponse;
    }

    public function isOnBehalfOfTenant()
    {
        return $this->_isOnBehalfOfTenant;
    }

    public function setIsWaitResponse($waitResponse = true)
    {
        $this->_isWaitResponse = (bool)$waitResponse;
        $this->addCorrelationId();
        return $this;
    }

    public function process(Varien_Object $options = null)
    {
        $this->setEncoder();
        $this->prepareHeaders();
        $this->_prepareData($options);
        $this->encode();
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
        $host = Mage::helper('xcom_xfabric')->getOntologyBaseUri();
        $this->_schemaUri = $host . $this->_topic . '/' . $this->_schemaVersion;
        return $this->_schemaUri;
    }

    protected function _initSchema()
    {
        parent::_initSchema();
        $this->addHeader(self::SCHEMA_VER_HEADER, $this->_schemaVersion);
        $this->addHeader(self::SCHEMA_URI_HEADER, $this->getSchemaUri());
        return $this;
    }

    /**
     * Encode message data using message encoding and set result to body
     * @return Xcom_Xfabric_Model_Message_Request
     */
    public function encode()
    {
        $this->_initSchema();
        $this->setBody($this->getMessageData());
        $this->getEncoder()->encode($this);
        return $this;
    }

    public function prepareHeaders()
    {
        $this->resetHeaders();
        switch ($this->_encoding) {
            case self::AVRO_BINARY:
                $this->addHeader('Content-Type', 'avro/binary');
                break;
            case self::AVRO_JSON:
                $this->addHeader('Content-Type', 'application/json');
                break;
        }
        //Adding headers from config
        $headers = Mage::helper('xcom_xfabric')->getAuthorizationHeader($this->isOnBehalfOfTenant());
        foreach ($headers as $headerName => $headerValue) {
            $this->addHeader($headerName, $headerValue);
        }

        return $this;
    }

    protected function _prepareData(Varien_Object $options = null)
    {
        $this->_hasDataChanges = true;
        if ($options && $options->hasDestinationId()) {
            $this->_destinationId = $options->getDestinationId();
        }
        if (!is_null($this->getDestinationId())) {
            $this->addHeader(Xcom_Xfabric_Model_Message_Abstract::DESTINATION_ID_HEADER,
                $this->getDestinationId());
        }
        return $this;
    }

    public function addCorrelationId()
    {
        $this->addHeader(
            Xcom_Xfabric_Model_Message_Abstract::CORRELATION_ID_HEADER,
            $this->getCorrelationId()
        );
    }

    /**
     * Retrieve Correlation ID
     *
     * @return mixed
     */
    public function getCorrelationId()
    {
        if (empty($this->_correlationId)) {
            $this->_correlationId = $this->uid();
        }
        return $this->_correlationId;
    }

    public function getDestinationId()
    {
        return $this->_destinationId;
    }

    public function setMessageData(array $messageData = array())
    {
        $this->_messageData = $messageData;
        return $this;
    }

    public function getMessageData($messageKey = null)
    {
        if (null !== $messageKey) {
            if (isset($this->_messageData[$messageKey])) {
                return $this->_messageData[$messageKey];
            } else {
                return null;
            }
        }
        return $this->_messageData;
    }

    /*protected function _beforeSave()
    {
        $this->setData('topic', $this->getTopic())
            ->setData('headers', serialize($this->getHeaders()))
            ->setData('correlation_id', $this->getCorrelationId());
        if ($this->isObjectNew()) {
            $this->setCreatedAt($this->_getResource()->formatDate(time()));
        } elseif ($this->getId() && !$this->hasData('updated_at')) {
            $this->setUpdatedAt($this->_getResource()->formatDate(time()));
        }
        return parent::_beforeSave();
    }

    protected function _afterLoad()
    {
        $this->setTopic($this->getData('topic'));
        $headers = unserialize($this->getData('headers'));
        if ($headers && is_array($headers)) {
            $this->setHeaders($headers);
        }
        return parent::_afterLoad();
    }*/
}
