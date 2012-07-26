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

class Xcom_Xfabric_Model_Message_Response extends Xcom_Xfabric_Model_Message_Abstract
{
    /** @var  $dependedMessageData collects to be used for sending next requests */
    public $dependedMessageData = array();

    protected $_eventPrefix = 'response_message';

    protected $_responseData;

    /**
     * Message will be processed later to reduce the time on receiving
     * @var bool
     */
    protected $_isProcessLater = false;

    /**
     * Initialization of abstract class
     */
    protected function _construct()
    {
        $this->_init('xcom_xfabric/message_response');
    }

    /**
     * Return isProcessLater value
     * @return bool
     */
    public function isProcessLater()
    {
        return $this->_isProcessLater;
    }

    /**
     * set isProcessLater value
     * @param bool $processLater
     * @return Xcom_Xfabric_Model_Message_Response
     */
    public function setIsProcessLater($processLater = false)
    {
        $this->_isProcessLater = (boolean)$processLater;
        return $this;
    }

    /**
     * Contains all actions that need to be performed once message is received
     * @return Xcom_Xfabric_Model_Message_Response
     */
    public function process()
    {
        $this->setIsProcessed(1);
        return $this;
    }

    /**
     * Get the schema version
     * @return string
     */
    public function getSchemaVersion()
    {
        if (empty($this->_schemaVersion)) {
            $schemaVersionHeader = $this->getHeader(self::SCHEMA_VER_SRV_HDR);
            if (!is_null($schemaVersionHeader)) {
                $this->setSchemaVersion($schemaVersionHeader);
            }
        }
        return $this->_schemaVersion;
    }

    /**
     * Get the schema URI
     * @return string
     */
    public function getSchemaUri()
    {
        if (empty($this->_schemaUri)) {
            $schemaUriHeader = $this->getHeader(self::SCHEMA_URI_HEADER);
            if (!is_null($schemaUriHeader)) {
                $this->setSchemaUri($schemaUriHeader);
            }
        }
        return $this->_schemaUri;
    }

    /**
     * Decode body of the message using decoder
     * @return Xcom_Xfabric_Model_Message_Response
     */
    public function decode()
    {
        $this->_initSchema();
        $this->getEncoder()->decode($this);
        $this->setDataChanges(true);
        return $this;
    }

    /**
     * Retrieve Correlation ID
     *
     * @return mixed
     */
    public function getCorrelationId()
    {
        if (!$this->_correlationId) {
            $this->_correlationId =
                $this->getHeader(Xcom_Xfabric_Model_Message_Abstract::CORRELATION_ID_HEADER);
        }
        return $this->_correlationId;
    }

    /**
     * Retrieve Correlation ID
     *
     * @return mixed
     */
    public function getPublisherPseudonym()
    {
        return $this->getHeader(Xcom_Xfabric_Model_Message_Abstract::PUBLISHER_PSEUDONYM_HEADER);
    }

    /**
     * Prepare data to be saved to database
     * @return Mage_Core_Model_Abstract
     */
    protected function _beforeSave()
    {
        parent::_beforeSave();
        $this->setData('topic', $this->getTopic())
             ->setData('headers', serialize($this->getHeaders()))
             ->setData('body', serialize($this->getBody()))
             ->setData('correlation_id', $this->getCorrelationId());

        if ($this->isObjectNew()) {
            $this->setCreatedAt($this->_getResource()->formatDate(time()));
        } elseif ($this->getId() && !$this->hasData('updated_at')) {
            $this->setUpdatedAt($this->_getResource()->formatDate(time()));
        }
        return $this;
    }

    /**
     * Prepare data after it was loaded from database
     * @return Mage_Core_Model_Abstract
     */
    protected function _afterLoad()
    {
        if ($this->hasData()) {
            $this->setHeaders(unserialize($this->getData('headers')));
            $this->setBody(unserialize($this->getData('body')));
        }
        return parent::_afterLoad();
    }

    /**
     * Retrieve data from message in synchronous context
     * @return mixed
     */
    public function getResponseData()
    {
        $data = $this->getBody() ? $this->getBody() : array();
        if (null === $this->_responseData) {
            $this->_responseData = $this->_prepareResponseData($data);
        }
        return $this->_responseData;
    }

    /**
     * Prepare response data before sending to user
     * @param $data
     * @return mixed
     */
    protected function _prepareResponseData(&$data)
    {
        return $data;
    }

    /**
     * Validate schema
     *
     * @return bool
     */
    protected function _validateSchema()
    {
        return true;
    }
}
