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
class Xcom_Choreography_Model_Workflow_Definition_Transaction_Action
{

    /**
     * @var $_messageName string
     */
    protected $_messageName;
    /**
     * @var $_topic string
     */
    protected $_topic;
    /**
     * @var $_timeouts array
     */
    protected $_timeouts;
    /**
     * @var $_retries int
     */
    protected $_retries;

    /**
     * @param string $messageName
     */
    public function setMessageName($messageName)
    {
        $this->_messageName = $messageName;
    }

    /**
     * @return string
     */
    public function getMessageName()
    {
        return $this->_messageName;
    }

    /**
     * @param int $retries
     */
    public function setRetries($retries)
    {
        $this->_retries = $retries;
    }

    /**
     * @return int
     */
    public function getRetries()
    {
        return $this->_retries;
    }

    /**
     * @param array $timeouts
     */
    public function setTimeouts($timeouts)
    {
        $this->_timeouts = $timeouts;
    }

    /**
     * @return array
     */
    public function getTimeouts()
    {
        return $this->_timeouts;
    }

    /**
     * @return int seconds
     */
    public function getReceiptTimeoutSeconds()
    {
        return $this->_timeouts[Xcom_Choreography_Model_Workflow_Definition_Transaction_Parser::RECEIPT_TIMEOUT];
    }

    /**
     * @return int seconds
     */
    public function getResponseTimeoutSeconds()
    {
        return $this->_timeouts[Xcom_Choreography_Model_Workflow_Definition_Transaction_Parser::RESPONSE_TIMEOUT];
    }

    /**
     * @return int seconds
     */
    public function getValidationTimeoutSeconds()
    {
        return $this->_timeouts[Xcom_Choreography_Model_Workflow_Definition_Transaction_Parser::VALIDATION_TIMEOUT];
    }

    /**
     * @param string $topic
     */
    public function setTopic($topic)
    {
        $this->_topic = $topic;
    }

    /**
     * @return string
     */
    public function getTopic()
    {
        return $this->_topic;
    }
}