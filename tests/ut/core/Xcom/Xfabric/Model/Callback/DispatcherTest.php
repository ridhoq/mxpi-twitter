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
class Xcom_Xfabric_Model_Callback_DispatcherTest extends Xcom_TestCase
{
    protected $_object;

    public function setUp()
    {
        $this->_object = new Xcom_Xfabric_Model_Callback_Dispatcher();
        parent::setUp();
    }

    public function tearDown()
    {
        $this->_object = null;
    }

    protected function _getMessageMock()
    {
        $messageDataOptions = array(
            'topic' => 'com.x.ordermanagement.v2/ProcessSalesChannelOrder/OrderCancelled',
            'headers' => array(Xcom_Xfabric_Model_Message::PUBLISHER_PSEUDONYM_HEADER => $pseudonym),
            'body' => "{'errors' : 'test'}",
            'topic_options' => array()
        );
        $messageOptions = Mage::getModel('xcom_xfabric/message_data_inbound', $messageDataOptions);
        return Mage::getModel('xcom_xfabric/message', array($messageOptions));
    }

    public function testInvokeInboundReceived()
    {
        $messageId = 2;
        $message = $this->_getMessageMock();

        $this->_object->invokeInboundReceived($messageId, $message);

        $eventData = array('message' => $message,
            'message_id' => $messageId);

        $this->assertEventCalledTimes('response_message_received', 1);
        $this->assertEventCalledAtWithData('response_message_received', 0, $eventData);

        $this->assertInstanceOf('Xcom_Xfabric_Model_Callback_Dispatcher', $this->_object);
    }

    public function testInvokeInboundValidate()
    {
        $messageId = 2;
        $message = $this->_getMessageMock();

        $this->_object->invokeInboundValidate($messageId, $message);

        $eventData = array('message' => $message,
            'message_id' => $messageId);

        $this->assertEventCalledTimes('response_message_validate', 1);
        $this->assertEventCalledAtWithData('response_message_validate', 0, $eventData);

        $this->assertInstanceOf('Xcom_Xfabric_Model_Callback_Dispatcher', $this->_object);
    }

    public function testInvokeMessageValidated()
    {
        $messageId = 2;
        $message = $this->_getMessageMock();

        $this->_object->invokeMessageValidated($messageId, $message);

        $eventData = array('message' => $message,
            'message_id' => $messageId);

        $this->assertEventCalledTimes('response_message_validated', 1);
        $this->assertEventCalledAtWithData('response_message_validated', 0, $eventData);

        $this->assertInstanceOf('Xcom_Xfabric_Model_Callback_Dispatcher', $this->_object);
    }

    public function testInvokeInboundProcessGeneric()
    {
        $messageId = 2;
        $message = $this->_getMessageMock();

        $this->_object->invokeInboundProcessGeneric($messageId, $message);

        $eventData = array('message' => $message,
            'message_id' => $messageId);

        $this->assertEventCalledTimes('response_message_process', 1);
        $this->assertEventCalledAtWithData('response_message_process', 0, $eventData);

        $this->assertInstanceOf('Xcom_Xfabric_Model_Callback_Dispatcher', $this->_object);
    }

    public function testInvokeInboundProcessByTopic()
    {
        $messageId = 2;
        $message = $this->_getMessageMock();
        $topic = 'com.x.ordermanagement.v2/ProcessSalesChannelOrder/OrderCancelled';

        $this->_object->invokeInboundProcessByTopic($topic, $messageId, $message);

        $eventData = array('message' => $message,
            'message_id' => $messageId);
        $topicSuffix = 'com_x_ordermanagement_v2_processsaleschannelorder_ordercancelled';

        $this->assertEventCalledTimes('response_message_process_' . $topicSuffix, 1);
        $this->assertEventCalledAtWithData('response_message_process_' . $topicSuffix, 0, $eventData);

        $this->assertInstanceOf('Xcom_Xfabric_Model_Callback_Dispatcher', $this->_object);
    }
}
