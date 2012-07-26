<?php

/**
 * Test for message order/created
 */

class Xcom_ChannelOrder_Messages_Order_CreatedTest
    extends Xcom_TestCase
{
    /**
     * @var Xcom_ChannelOrder_Model_Message_Order_Created
     */
    protected $_object;

    protected $_instanceOf = 'Xcom_ChannelOrder_Model_Message_Order_Created';

    public function setUp()
    {
        $this->_checkConnection = true;
        parent::setUp();
        $this->_object = Mage::getModel('xcom_channelorder/message_order_created');
    }

    public function testProcess()
    {
        $orderNumber = 'TEST_ORDER_NUMBER';
        $orderData = array(
            'order' => array(
                'orderNumber' => $orderNumber,
            ),
            'accountId' => 'TEST_ACCOUNT_ID'
        );

        $objectMock = $this->getMock($this->_instanceOf,
            array('logOrder', 'getBody'));
        $objectMock->expects($this->once())
            ->method('getBody')
            ->will($this->returnValue($orderData));

        $objectMock->expects($this->once())
            ->method('logOrder')
            ->will($this->returnValue($objectMock));

        $helperMock = $this->mockHelper('xcom_channelorder', array('validateOrderEnvironment'));
        $helperMock->expects($this->once())
            ->method('validateOrderEnvironment')
            ->will($this->returnValue(true));

        $orderMock = $this->mockModel('xcom_channelorder/message_order', array('setOrderMessageData',
            'setAccountId', 'createOrder'));
        $orderMock->expects($this->once())
            ->method('setOrderMessageData')
            ->will($this->returnValue($orderMock));
        $orderMock->expects($this->once())
            ->method('setAccountId')
            ->will($this->returnValue($orderMock));
        $orderMock->expects($this->once())
            ->method('createOrder')
            ->will($this->returnValue($orderMock));

        $this->assertInstanceOf($this->_instanceOf, $objectMock->process());
    }

    public function testLogOrder()
    {
        $mockObject = $this->mockModel('xcom_channelorder/message_order_created',
            array('save'));

        $mockLog = $this->mockModel('xcom_log/log', array('save'));
        $mockLog->expects($this->once())
            ->method('save');

        $result = $mockObject->logOrder('description_text', 'some_type');

        $this->assertInstanceOf($this->_instanceOf, $result);
    }
}
