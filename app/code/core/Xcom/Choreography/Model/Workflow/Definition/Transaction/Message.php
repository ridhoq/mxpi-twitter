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
class Xcom_Choreography_Model_Workflow_Definition_Transaction_Message
{
    
    protected $_topic;
    protected $_messageTypeName;
    protected $_receiptAckTimeout;
    protected $_validationAckTimeout;
    protected $_responseTimeout;
    protected $_retries;


    function __construct($options)
    {
        /** @var $transactionAction Xcom_Choreography_Model_Workflow_Definition_Transaction_Action */
        $transactionAction = $options['transaction_action'];
        $this->_topic = $transactionAction->getTopic();
        $this->_messageTypeName = $transactionAction->getMessageName();
        $this->_receiptAckTimeout = $transactionAction->getReceiptTimeoutSeconds();
        $this->_validationAckTimeout = $transactionAction->getValidationTimeoutSeconds();
        $this->_responseTimeout = $transactionAction->getResponseTimeoutSeconds();
        $this->_retries = $transactionAction->getRetries();
    }

    /**
     * @return The topic on which this message MUST be sent or receive
     */
    public function getTopic() 
    {
        return $this->_topic;
    }

    /**
     * @return The name of the message type.
     */
    public function getMessageTypeName() 
    {
        return $this->_messageTypeName;
    }

    /**
     * @return The timeout for receipt acknowledgement in seconds or ZERO if not defined
     */
    public function getReceiptAckTimeoutInSeconds() 
    {
        return $this->_receiptAckTimeout;
    }

    /**
     * @return the timeout for validation acknowledgement in seconds or ZERO if not defined
     */
    public function getValidationAckTimeoutInSeconds() 
    {
        return $this->_validationAckTimeout;
    }

    /**
     * @return The timeout for response message in seconds or ZERO if not defined
     */
    public function getResponseTimeoutInSeconds() 
    {
        return $this->_responseTimeout;
    }

    /**
     * @return The number of retries for this message
     */
    public function getRetries() 
    {
        return $this->_retries;
    }

}
