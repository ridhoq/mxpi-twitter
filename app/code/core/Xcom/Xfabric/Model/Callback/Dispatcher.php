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
 */
class Xcom_Xfabric_Model_Callback_Dispatcher implements Xcom_Xfabric_Model_Callback_Interface
{

    /**
     * Called when inbound message is received
     * Dispatches Magento event
     *
     * @param $id
     * @param Xcom_Xfabric_Model_Message $message
     * @return Xcom_Xfabric_Model_Callback_Dispatcher
     */
    public function invokeInboundReceived(Xcom_Xfabric_Model_Message $message)
    {
        Mage::dispatchEvent('response_message_received', array('message' => $message));

        return $this;
    }

    /**
     * Called when inbound message need to be validated
     *
     * @param $id
     * @param Xcom_Xfabric_Model_Message $message
     * @return Xcom_Xfabric_Model_Callback_Dispatcher
     */
    public function invokeInboundValidate(Xcom_Xfabric_Model_Message $message)
    {
        Mage::dispatchEvent('response_message_validate', array('message' => $message));

        return $this;
    }

    /**
     * Called when inbound message is already validated
     *
     * @param $id
     * @param Xcom_Xfabric_Model_Message $message
     * @return Xcom_Xfabric_Model_Callback_Dispatcher
     */
    public function invokeMessageValidated(Xcom_Xfabric_Model_Message $message)
    {
        Mage::dispatchEvent('response_message_validated', array('message' => $message));

        return $this;
    }

    /**
     * Called when inbound message is ready to be processed. Generic callback function for all messages
     *
     * @param $id
     * @param Xcom_Xfabric_Model_Message $message
     * @return Xcom_Xfabric_Model_Callback_Dispatcher
     */
    public function invokeInboundProcessGeneric(Xcom_Xfabric_Model_Message $message)
    {
        Mage::dispatchEvent('response_message_process', array('message' => $message));

        return $this;
    }


    /**
     * Called when inbound message is ready to be processed. Callback is parametrized by topic
     *
     * @param $topic
     * @param $id
     * @param Xcom_Xfabric_Model_Message $message
     * @return Xcom_Xfabric_Model_Callback_Dispatcher
     */
    public function invokeInboundProcessByTopic(Xcom_Xfabric_Model_Message $message)
    {
        $eventSuffix =  Mage::helper('xcom_xfabric')
            ->getEventSuffix($message->getTopic());

        Mage::dispatchEvent('response_message_process_' . $eventSuffix, array('message' => $message));

        return $this;
    }

    /**
     * Called when sent message is received by the system
     *
     * @param $topic
     * @param $id
     * @param Xcom_Xfabric_Model_Message $message
     * @return Xcom_Xfabric_Model_Callback_Dispatcher
     */
    public function invokeInboundLoopedProcess(Xcom_Xfabric_Model_Message $message)
    {
        $eventSuffix =  Mage::helper('xcom_xfabric')
            ->getEventSuffix($message->getTopic());

        Mage::dispatchEvent('response_message_looped_process_' . $eventSuffix, array('message' => $message));

        return $this;
    }

}
