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

class Xcom_Xfabric_Model_Endpoint
{
    /** @var Xcom_Xfabric_Model_Message_Interface */
    protected $_messageData = array();

    /** @var Xcom_Xfabric_Model_Transport_Interface */
    protected $_transport = null;

    /** @var Xcom_Xfabric_Model_Authorization_Interface */
    protected $_authorization = null;

    /** @var Xcom_Xfabric_Model_Schema_Interface */
    protected $_schema = null;

    /** @var Xcom_Xfabric_Model_Encoder_Interface */
    protected $_encoder = null;

    /** @var Xcom_Xfabric_Model_Message */
    protected $_message = null;

    /** @var string */
    protected $_encoding = null;

    /** @var Xcom_Xfabric_Callback_Interface */
    protected $_callbackHandler = null;

    /**
     * Initialize necessary properties from options
     * @param $options
     */
    public function __construct($options)
    {
        if (isset($options['message_data'])) {
            if (!$options['message_data'] instanceof Xcom_Xfabric_Model_Message_Data_Interface) {
                throw new Exception('Message data should be an instance of Xcom_Xfabric_Model_Message_Data_Interface');
            }
            $this->_messageData = $options['message_data'];
        }

        if (isset($options['transport'])) {
            if (!$options['transport'] instanceof Xcom_Xfabric_Model_Transport_Interface) {
                throw new Exception('Transport should be an instance of Xcom_Xfabric_Model_Transport_Interface');
            }
            $this->_transport = $options['transport'];
        }

        if (isset($options['authorization'])) {
            if (!$options['authorization'] instanceof Xcom_Xfabric_Model_Authorization_Interface) {
                throw new Exception('Authorization should be an instance of '
                    . 'Xcom_Xfabric_Model_Authorization_Interface');
            }
            $this->_authorization = $options['authorization'];
        } else {
            throw new Exception('Authorization should be set');
        }

        if (isset($options['schema'])) {
            if (!$options['schema'] instanceof Xcom_Xfabric_Model_Schema_Interface) {
                throw new Exception('Schema should be an instance of '
                    . 'Xcom_Xfabric_Model_Schema_Interface');
            }
            $this->_schema = $options['schema'];
        }

        if (isset($options['encoder'])) {
            if (!$options['encoder'] instanceof Xcom_Xfabric_Model_Encoder_Interface) {
                throw new Exception('Encoder should be an instance of '
                    . 'Xcom_Xfabric_Model_Encoder_Interface');
            }
            $this->_encoder = $options['encoder'];
        } else {
            throw new Exception('Encoder should be set');
        }

        if (isset($options['encoding'])) {
            $this->_encoding = $options['encoding'];
        } else {
            throw new Exception('Encoding should be set');
        }

        if (isset($options['callback_handler'])) {
            if (!$options['callback_handler'] instanceof Xcom_Xfabric_Model_Callback_Interface) {
                throw new Exception('Callback Handler should implement Xcom_Xfabric_Model_Callback_Interface');
            }
            $this->_callbackHandler = $options['callback_handler'];
        }
    }

    /**
     * Return callback handler
     *
     * @return null|Xcom_Xfabric_Callback_Interface
     */
    public function getCallbackHandler()
    {
        return $this->_callbackHandler;
    }

    /**
     * Process message sending
     * @return mixed
     * @throws Exception
     */
    public function send()
    {
        if (!$this->_messageData instanceof Xcom_Xfabric_Model_Message_Data_Interface) {
            throw new Exception('Message data should be set');
        }
        $options = $this->_messageData->getOptions();
        $topic = $options['topic'];

        $messageData = $this->_messageData->getMessageData();
        $messageBody = $this->_encoder->encodeText($messageData, $this->_schema->getRawSchema());
        $messageOptions = array(
            'body' => $messageBody,
            'headers' => $this->_getMessageHeaders(),
            'topic' => $topic,
            'db_adapter' => Mage::getModel('xcom_xfabric/message_response'),
            'direction' => Xcom_Xfabric_Model_Message::DIRECTION_OUTBOUND,
            'status' => Xcom_Xfabric_Model_Message::MESSAGE_STATUS_NEW
        );

        /** @var $message Xcom_Xfabric_Model_Message */
        $message = Mage::getModel('xcom_xfabric/message', $messageOptions);
        $message->save();

        $messageOptions = $this->_messageData->getOptions();
        $options = array(
            'message_data' => json_encode($messageData),
            'synchronous' => !empty($messageOptions['synchronous']) ? true : false
        );

        try {
            $this->_transport->sendMessage($message, $options);
            $message->setSent();
        }
        catch (Exception $e) {
            throw $e;
        }
    }

    /**
     * Process message receiving
     * @return Xcom_Xfabric_Model_Endpoint
     */
    public function receive()
    {
        $messageDataOptions = $this->_messageData->getOptions();
        $messageOptions = array(
            'body' => $messageDataOptions['body'],
            'headers' => $messageDataOptions['headers'],
            'topic' => $messageDataOptions['topic'],
            'message_data' => $this->_encoder->decodeText($messageDataOptions['body'],
                $this->_schema->getRawSchema()),
            'db_adapter' => Mage::getModel('xcom_xfabric/message_response'),
            'direction' => Xcom_Xfabric_Model_Message::DIRECTION_INBOUND,
            'status' => Xcom_Xfabric_Model_Message::MESSAGE_STATUS_RECEIVED
        );

        $this->_message = Mage::getModel('xcom_xfabric/message', $messageOptions);
        $this->validateAuthorizationHeader($this->getMessage()->getAuthorization());

        $this->_message->save();

        /* Notify callback logic that message is received, decoded and saved to database */
        $this->getCallbackHandler()->invokeInboundReceived($this->getMessage());

        /* Invoke callback logic which will validate the message */
        $this->getCallbackHandler()->invokeInboundValidate($this->getMessage());

        /* Invoke callback logic which will validate the message */
        $this->getCallbackHandler()->invokeMessageValidated($this->getMessage());

        /* Generic event common for all messages */
        $this->getCallbackHandler()->invokeInboundProcessGeneric($this->getMessage());

        if ($this->_message->getHeader(Xcom_Xfabric_Model_Message::PUBLISHER_PSEUDONYM_HEADER) ==
            $this->_authorization->getBearerData(Xcom_Xfabric_Model_Authorization::PSEUDONYM)
        ) {
            /* If the sender of the message is the same as receiver. Message is "looped back"
             Nobody will receive this message unless subscribed on it on purpose */
            $this->getCallbackHandler()->invokeInboundLoopedProcess($this->getMessage());
        } else {
            /* Notify callback logic that it's a time to process message with specified topic */
            $this->getCallbackHandler()->invokeInboundProcessByTopic($this->getMessage());
        }

        return $this;
    }

    /**
     * Returns object of the message
     * @return null|Xcom_Xfabric_Model_Message
     */
    public function getMessage()
    {
        return $this->_message;
    }

    public function validateAuthorizationHeader($authorization)
    {
        if (empty($authorization)) {
            throw Mage::exception('Xcom_Xfabric', 'Response Message does not have Authorization header');
        }
        if (!$this->_authorization->hasAuthorizationData()) {
            throw Mage::exception('Xcom_Xfabric',
                $this->__('X.commerce Fabric Bearer Token must be filled in system configurations'));
        }
        if ($this->_authorization->getFabricData('token') !== $authorization) {
            throw Mage::exception('Xcom_Xfabric', 'Authorization header is wrong');
        }
        return true;
    }

    protected function _getMessageHeaders()
    {
        $messageOptions = $this->_messageData->getOptions();
        $headers = array();
        if (isset($messageOptions['correlation_id'])) {
            $headers[Xcom_Xfabric_Model_Message::CORRELATION_ID_HEADER] = $messageOptions['correlation_id'];
        } else if (isset($messageOptions['synchronous'])) {
            $headers[Xcom_Xfabric_Model_Message::CORRELATION_ID_HEADER] = $this->_getUid();
        }
        if (isset($messageOptions['destination_id'])) {
            $headers[Xcom_Xfabric_Model_Message::DESTINATION_ID_HEADER] = $messageOptions['destination_id'];
        }
        if ($this->_encoding == Xcom_Xfabric_Model_Message::AVRO_BINARY_ENCODING) {
            $headers[Xcom_Xfabric_Model_Message::CONTENT_TYPE_HEADER] = 'avro/binary';
        } else if ($this->_encoding == Xcom_Xfabric_Model_Message::AVRO_JSON_ENCODING) {
            $headers[Xcom_Xfabric_Model_Message::CONTENT_TYPE_HEADER] = 'application/json';
        }
        if (isset($messageOptions['on_behalf_of_tenant']) && !$messageOptions['on_behalf_of_tenant']) {
            $headers[Xcom_Xfabric_Model_Message::AUTHORIZATION_HEADER] = $this->_authorization
                ->getSelfData(Xcom_Xfabric_Model_Authorization::TOKEN);
        } else {
            $headers[Xcom_Xfabric_Model_Message::AUTHORIZATION_HEADER] = $this->_authorization
                ->getBearerData(Xcom_Xfabric_Model_Authorization::TOKEN);
        }
        if (isset($messageOptions['schema_version'])) {
            $headers[Xcom_Xfabric_Model_Message::SCHEMA_VERSION_HEADER] = $messageOptions['schema_version'];
        }
        $headers[Xcom_Xfabric_Model_Message::SCHEMA_URI_HEADER] = $this->_schema->getSchemaUri();

        if (isset($messageOptions['message_guid_continuation'])) {
            $headers[Xcom_Xfabric_Model_Message::MESSAGE_GUID_CONTINUATION] = $messageOptions['message_guid_continuation'];
        }
        if (isset($messageOptions['workflow_id'])) {
            $headers[Xcom_Xfabric_Model_Message::WORKFLOW_ID] = $messageOptions['workflow_id'];
        }
        if (isset($messageOptions['transaction_id'])) {
            $headers[Xcom_Xfabric_Model_Message::TRANSACTION_ID] = $messageOptions['transaction_id'];
        }

        return $headers;
    }

    /**
     * Generates unique id
     * @return string
     */
    protected final function _getUid()
    {
        return md5(uniqid(mt_rand(), true));
    }
}
