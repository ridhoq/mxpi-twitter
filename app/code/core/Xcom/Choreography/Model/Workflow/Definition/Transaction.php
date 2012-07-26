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
 * @package     Xcom_Choreography
 * @copyright   Copyright (c) 2010 Magento Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class Xcom_Choreography_Model_Workflow_Definition_Transaction
{


    protected $_id;
    /* @var Xcom_Choreography_Model_Workflow_Definition_Transaction_Message */
    protected $_failureMessage = null;
    /* @var Xcom_Choreography_Model_Workflow_Definition_Transaction_Message */
    protected $_receiverMessage = null;
    /* @var Xcom_Choreography_Model_Workflow_Definition_Transaction_Message */
    protected $_senderMessage = null;
    protected $_role;
    protected $_transactionType;
    protected $_transmissionMode;
    protected $_failurePostconditions;
    protected $_preconditions;
    protected $_successPostconditions;

    /**

     */
    public function __construct($options)
    {
        /** @var $parser Xcom_Choreography_Model_Workflow_Definition_Transaction_Parser */
        $parser = $options['parser'];

        $this->_id = $parser->getTransactionId();
        switch ($parser->getRole()) {
            case Xcom_Choreography_Model_Workflow_Definition_Transaction_Parser::TRANSACTION_ROLE_RECEIVER:
                $this->_role = Xcom_Choreography_Model_Workflow_Constants::TRANSACTION_ROLE_RECEIVER;
                break;
            case Xcom_Choreography_Model_Workflow_Definition_Transaction_Parser::TRANSACTION_ROLE_SENDER:
                $this->_role = Xcom_Choreography_Model_Workflow_Constants::TRANSACTION_ROLE_SENDER;
                break;
            default:
                $this->_role = null;
        }
        switch ($parser->getType()) {
            case Xcom_Choreography_Model_Workflow_Definition_Transaction_Parser::TRANSACTION_TYPE_INFORM:
                $this->_transactionType = Xcom_Choreography_Model_Workflow_Constants::TRANSACTION_INFORM;
                break;
            case Xcom_Choreography_Model_Workflow_Definition_Transaction_Parser::TRANSACTION_TYPE_NOTIFY:
                $this->_transactionType = Xcom_Choreography_Model_Workflow_Constants::TRANSACTION_NOTIFY;
                break;
            case Xcom_Choreography_Model_Workflow_Definition_Transaction_Parser::TRANSACTION_TYPE_QUERY:
                $this->_transactionType = Xcom_Choreography_Model_Workflow_Constants::TRANSACTION_QUERY;
                break;
            case Xcom_Choreography_Model_Workflow_Definition_Transaction_Parser::TRANSACTION_TYPE_RESPONSE:
                $this->_transactionType = Xcom_Choreography_Model_Workflow_Constants::TRANSACTION_REQUEST_RESPONSE;
                break;
            default:
                $this->_transactionType = null;
        }
        switch ($parser->getPublishMode()) {
            case Xcom_Choreography_Model_Workflow_Definition_Transaction_Parser::TRANSACTION_PUBLISH_MODE_BROADCAST:
                $this->_transmissionMode = Xcom_Choreography_Model_Workflow_Constants::TRANSACTION_COMMUNICATION_BROADCAST;
                break;
            case Xcom_Choreography_Model_Workflow_Definition_Transaction_Parser::TRANSACTION_PUBLISH_MODE_UNICAST:
                $this->_transmissionMode = Xcom_Choreography_Model_Workflow_Constants::TRANSACTION_COMMUNICATION_UNICAST;
                break;
            default:
                $this->_transmissionMode = null;
        }

        $this->_failureMessage =
            new Xcom_Choreography_Model_Workflow_Definition_Transaction_Message(
                array('transaction_action' => $parser->getReceiverFailureAction())
            );
        $this->_receiverMessage =
            new Xcom_Choreography_Model_Workflow_Definition_Transaction_Message(
                array('transaction_action' => $parser->getReceiverSuccessAction())
            );
        $this->_senderMessage =
            new Xcom_Choreography_Model_Workflow_Definition_Transaction_Message(
                array('transaction_action' => $parser->getSenderAction())
            );
        $this->_failurePostconditions = $parser->getFailurePostconditions();
        $this->_preconditions = $parser->getPreconditions();
        $this->_successPostconditions = $parser->getSuccessPostconditions();
    }

    /**
     *
     */
    public function getId()
    {
        return $this->_id;
    }

    /**
     * @return Xcom_Choreography_Model_Workflow_Definition_Transaction_Message The message def that a receiver in the transaction is suppose to send when there is a failure condition
     * or null if this transaction has no receiver message.
     */
    public function getFailureMessage()
    {
        return $this->_failureMessage;
    }

    /**
     * @return Xcom_Choreography_Model_Workflow_Definition_Transaction_Message The message def that a receiver in the transaction is suppose to send or null if this transaction has no
     * receiver message.
     */
    public function getReceiverMessage()
    {
        return $this->_receiverMessage;
    }

    /**
     * @return Xcom_Choreography_Model_Workflow_Definition_Transaction_Message The message def that a sender in the transaction is suppose to send.
     */
    public function getSenderMessage()
    {
        return $this->_senderMessage;
    }

    /**
     * @return string Whether the parent workflow role is the sender or receiver of this transaction
     */
    public function getRole()
    {
        return $this->_role;
    }

    /**
     *
     */
    public function getType()
    {
        return $this->_transactionType;
    }

    /**
     *
     */
    public function isBroadcast()
    {
        return strcmp(Xcom_Choreography_Model_Workflow_Constants::TRANSACTION_COMMUNICATION_BROADCAST, $this->_transmissionMode) == 0;
    }

    /**
     *
     */
    public function isUnicast()
    {
        return strcmp(Xcom_Choreography_Model_Workflow_Constants::TRANSACTION_COMMUNICATION_UNICAST, $this->_transmissionMode) == 0;
    }

    /**
     *
     */
    public function getPostconditionsForFailure()
    {
        return $this->_failurePostconditions;
    }

    /**
     *
     */
    public function getPostconditionsForSuccess()
    {
        return $this->_successPostconditions;
    }

    /**
     *
     */
    public function getPreconditions()
    {
        return $this->_preconditions;
    }

    /**
     *
     */
    public function getAllTopics()
    {
        $topics = array();
        if (!is_null($this->_receiverMessage)) {
            $topics[] = $this->_receiverMessage->getTopic();
        }
        if (!is_null($this->_failureMessage)) {
            $topics[] = $this->_failureMessage->getTopic();
        }
        if (!is_null($this->_senderMessage)) {
            $topics[] = $this->_senderMessage->getTopic();
        }


        return $topics;
    }

    /**
     *
     */
    public function getSubscribedTopics()
    {
        $topics = array();
        if (strcmp($this->_role, Xcom_Choreography_Model_Workflow_Constants::TRANSACTION_ROLE_SENDER) == 0) {
            if (!is_null($this->_receiverMessage)) {
                $topics[] = $this->_receiverMessage->getTopic();
            }
            if (!is_null($this->_failureMessage)) {
                $topics[] = $this->_failureMessage->getTopic();
            }
        }
        else if (strcmp($this->_role, Xcom_Choreography_Model_Workflow_Constants::TRANSACTION_ROLE_RECEIVER) == 0) {
            $topics[] = $this->_senderMessage->getTopic();
        }

        return $topics;
    }
}

