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

class Xcom_Xfabric_Model_Message
{
    const AVRO_BINARY_ENCODING          = 'binary';
    const AVRO_JSON_ENCODING            = 'json';

    const SCHEMA_VERSION_HEADER         = 'X-XC-SCHEMA-VERSION';
    const SCHEMA_URI_HEADER             = 'X-XC-SCHEMA-URI';
    const CORRELATION_ID_HEADER         = 'X-XC-RESULT-CORRELATION-ID';
    const DESTINATION_ID_HEADER         = 'X-XC-DESTINATION-ID';
    const PUBLISHER_PSEUDONYM_HEADER    = 'X-XC-PUBLISHER-PSEUDONYM';
    const CONTENT_TYPE_HEADER           = 'Content-Type';
    const AUTHORIZATION_HEADER          = 'Authorization';

    const MESSAGE_STATUS_NEW        = 0;
    const MESSAGE_STATUS_SENT       = 1;
    const MESSAGE_STATUS_RECEIVED   = 2;
    const MESSAGE_STATUS_VALID      = 3;
    const MESSAGE_STATUS_INVALID    = 4;

    const DIRECTION_OUTBOUND = 0;
    const DIRECTION_INBOUND  = 1;

    /** @var headers */
    protected $_headers;

    /** @var topic */
    protected $_topic;

    /** @var encoded message body */
    protected $_body;

    /** @var array decoded message data */
    protected $_messageData;

    /** @var int direction of the message: INBOUND or OUTBOUND */
    protected $_direction = null;

    /** @var null */
    protected $_dbAdapter = null;

    /** @var int Status*/
    protected $_status;


    /**
     * Constructor. Initialize class properties from options
     * @param $options
     */
    public function __construct($options)
    {
        if (isset($options['db_adapter'])) {
            $this->_dbAdapter = $options['db_adapter'];
            $this->_initFromAdapter();
        }
        if (isset($options['headers'])) {
            foreach ($options['headers'] as $key => $value) {
                $this->_headers[strtoupper($key)] = $value;
            }
        }
        if (isset($options['body'])) {
            $this->_body = $options['body'];
        }
        if (isset($options['topic'])) {
            $this->_topic = $options['topic'];
        }
        if (isset($options['message_data'])) {
            $this->_messageData = $options['message_data'];
        }
        if (isset($options['direction'])) {
            $this->_direction = $options['direction'];
        }
        if (isset($options['status'])) {
            $this->_status = $options['status'];
        }
        $this->_setAdapterData();
    }

    /**
     * Initializes Message with data from db adapter
     * @return Xcom_Xfabric_Model_Message
     */
    protected function _initFromAdapter()
    {
        if (!is_null($this->_getDbAdapter()->getId())) {
            $data = $this->_getDbAdapter()->getData();
            $body = unserialize($data['body']);
            $this->_body = $body;
            $this->_direction = $data['direction'];
            $this->_status = $data['status'];
            $this->_headers = unserialize($data['headers']);
            $this->_messageData = $body;
            $this->_topic = $data['topic'];
        }
        return $this;
    }

    /**
     * initialize database adapter with data
     * @return Xcom_Xfabric_Model_Message
     */
    protected function _setAdapterData()
    {
        $this->_getDbAdapter()
            ->setBody($this->getMessageData())
            ->setHeaders($this->getHeaders())
            ->setTopic($this->getTopic())
            ->setCorrelationId($this->getCorrelationId())
            ->setDirection($this->_direction)
            ->setStatus($this->_status);

        return $this;
    }

    /**
     * Retrieve encoded body
     *
     * @return binary string|json
     */
    public function getBody()
    {
        return $this->_body;
    }

    /**
     * Retrieve all headers
     *
     * @return headers
     */
    public function getHeaders()
    {
        return $this->_headers;
    }

    /**
     * Retrieve particular header
     *
     * @param $headerName
     * @return null|string
     */
    public function getHeader($headerName)
    {
        if (!empty($this->_headers[strtoupper($headerName)])) {
            return trim($this->_headers[strtoupper($headerName)]);
        }
        return null;
    }

    /**
     * retrieve topic
     * @return topic
     */
    public function getTopic()
    {
        return $this->_topic;
    }

    /**
     * retrieve correlation id
     * @return null|string
     */
    public function getCorrelationId()
    {
        return $this->getHeader(self::CORRELATION_ID_HEADER);
    }

    /**
     * Retrieve uri of the schema
     * @return null|string
     */
    public function getSchemaUri()
    {
        return $this->getHeader(self::SCHEMA_URI_HEADER);
    }

    /**
     * Retrieve decoded message data
     * @return array
     */
    public function getMessageData()
    {
        return $this->_messageData;
    }

    /**
     * Retrieve authorization token
     * @return null|string
     */
    public function getAuthorization()
    {
        return $this->getHeader(self::AUTHORIZATION_HEADER);
    }

    /**
     * save message to database
     * @return Xcom_Xfabric_Model_Message
     */
    public function save()
    {
        $this->_getDbAdapter()
            ->setDataChanges(true)
            ->save();
        return $this;
    }

    /**
     * Retrieve id of the message from database
     * @return int
     */
    public function getId()
    {
        return $this->_getDbAdapter()->getId();
    }

    /**
     * Update status of the message in database
     * @param $status
     * @return Xcom_Xfabric_Model_Message
     */
    protected function _updateStatus($status)
    {
        $this->_status = $status;
        $this->_getDbAdapter()
            ->setStatus($status)
            ->save();
        return $this;
    }

    /**
     * set result of validation to the message and save
     * @param bool $isValid
     */
    public function setValidated($isValid = true)
    {
        switch ($isValid) {
            case true:
                $status = self::MESSAGE_STATUS_VALID;
                break;
            case false:
                $status = self::MESSAGE_STATUS_INVALID;
                break;
        }
        return $this->_updateStatus($status);
    }

    /**
     * update status of the message once it's received
     * @return Xcom_Xfabric_Model_Message
     */
    public function setReceived()
    {
        return $this->_updateStatus(self::MESSAGE_STATUS_RECEIVED);
    }

    /**
     * update status of the message once it's sent
     * @return Xcom_Xfabric_Model_Message
     */
    public function setSent()
    {
        return $this->_updateStatus(self::MESSAGE_STATUS_SENT);
    }

    /**
     * retrieve database adapter
     * @return null
     */
    protected function _getDbAdapter()
    {
        return $this->_dbAdapter;
    }
}
