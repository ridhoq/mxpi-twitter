<?php

/**
 * Test for message order/created
 */

class Xcom_ChannelOrder_Model_Message_Marketplace_Order_Search_SucceededTest
    extends Xcom_TestCase
{
    /**
     * @var Xcom_ChannelOrder_Model_Message_Marketplace_Order_Search_Succeeded
     */
    protected $_object;

    protected $_instanceOf = 'Xcom_ChannelOrder_Model_Message_Marketplace_Order_Search_Succeeded';

    public function setUp()
    {
        $this->_checkConnection = true;
        parent::setUp();
        $this->_object = Mage::getModel('xcom_channelorder/message_marketplace_order_search_succeeded');
    }

    public function tearDown()
    {
        parent::tearDown();
        $this->_object = null;
    }

    public function testInstanceOf()
    {
        $this->assertInstanceOf($this->_instanceOf, $this->_object);
    }

    public function testTopic()
    {
        $this->assertEquals('marketplace/order/searchSucceeded', $this->_object->getTopic());
    }

    public function testProcessEmptyOrders()
    {
        $mockObject = $this->mockModel('xcom_channelorder/message_marketplace_order_search_succeeded',
            array('logOrder'));
        $mockObject->setBody(array(
            'orders' => ''
        ));
        $mockObject->expects($this->once())
                   ->method('logOrder');

        $result = $mockObject->process();

        $this->assertInstanceOf($this->_instanceOf, $result);
    }

    public function testProcessOrdersUpdate()
    {
        $mockObject = $this->mockModel('xcom_channelorder/message_marketplace_order_search_succeeded',
            array('logOrder'));
        $sellerAccountId = 1;
        $mockObject->setBody(array(
            'orders' => array(
                array('some_data')
            ),
            'searchMarketplaceOrder' => array(
                'sellerAccountId' => $sellerAccountId
            )
        ));

        $mockMessageOrder = $this->mockModel('xcom_channelorder/message_order', array(
            'getChannelOrder', 'getOrderNumber', 'updateOrder'
        ));
        $orderNumber = 'TESTORDER_' . mt_rand(1000, 9999);
        $mockMessageOrder->expects($this->at(0))
                         ->method('getOrderNumber')
                         ->will($this->returnValue($orderNumber));
        $mockMessageOrder->expects($this->at(1))
                         ->method('getOrderNumber')
                         ->will($this->returnValue($orderNumber));
        $mockChannelOrder = $this->mockModel('xcom_channelorder/order', array('save'));
        $mockChannelOrder->setOrderId(true);
        $mockMessageOrder->expects($this->once())
                         ->method('getChannelOrder')
                         ->will($this->returnValue($mockChannelOrder));
        $mockMessageOrder->expects($this->once())
                         ->method('updateOrder')
                         ->with($this->equalTo(array('some_data')))
                         ->will($this->returnValue($mockChannelOrder));

        $this->mockStoreConfig(Xcom_ChannelOrder_Helper_Data::CHANNELORDER_VALIDATION_SETTINGS_SANDBOX_ENABLED, 1);
        $helperMock = $this->mockHelper('xcom_channelorder', array('validateOrderEnvironment'));
        $helperMock->expects($this->once())
            ->method('validateOrderEnvironment')
            ->will($this->returnValue(true));

        $mockObject->expects($this->once())
                   ->method('logOrder');

        $result = $mockObject->process();

        $this->assertInstanceOf($this->_instanceOf, $result);
    }

    public function testProcessOrdersInsert()
    {
        $mockObject = $this->mockModel('xcom_channelorder/message_marketplace_order_search_succeeded',
            array('logOrder'));
        $sellerAccountId = 1;
        $mockObject->setBody(array(
            'orders' => array(
                array('some_data')
            ),
            'searchMarketplaceOrder' => array(
                'sellerAccountId' => $sellerAccountId
            )
        ));

        $this->mockStoreConfig(Xcom_ChannelOrder_Helper_Data::CHANNELORDER_VALIDATION_SETTINGS_SANDBOX_ENABLED, 1);
        $this->mockHelper('xcom_channelorder');

        $mockMessageOrder = $this->mockModel('xcom_channelorder/message_order', array(
            'getChannelOrder', 'getOrderNumber', 'createOrder'
        ));
        $orderNumber = 'TESTORDER_' . mt_rand(1000, 9999);
        $mockMessageOrder->expects($this->at(0))
                         ->method('getOrderNumber')
                         ->will($this->returnValue($orderNumber));
        $mockMessageOrder->expects($this->at(1))
                         ->method('getOrderNumber')
                         ->will($this->returnValue($orderNumber));
        $mockChannelOrder = $this->mockModel('xcom_channelorder/order', array('save'));
        $mockChannelOrder->setOrderId(false);
        $mockMessageOrder->expects($this->once())
                         ->method('getChannelOrder')
                         ->will($this->returnValue($mockChannelOrder));
        $mockMessageOrder->expects($this->once())
                         ->method('createOrder')
                         ->with($this->equalTo(array('some_data')))
                         ->will($this->returnValue($mockChannelOrder));

        $mockObject->expects($this->once())
                   ->method('logOrder');

        $this->mockStoreConfig(Xcom_ChannelOrder_Helper_Data::CHANNELORDER_VALIDATION_SETTINGS_SANDBOX_ENABLED, 1);
        $helperMock = $this->mockHelper('xcom_channelorder', array('validateOrderEnvironment'));
        $helperMock->expects($this->once())
            ->method('validateOrderEnvironment')
            ->will($this->returnValue(true));

        $result = $mockObject->process();

        $this->assertInstanceOf($this->_instanceOf, $result);
    }

    /**
     * @expectedExceptionMessage AccountId is not provided in the search order message
     */
    public function testProcessOrdersException()
    {
        $mockObject = $this->mockModel('xcom_channelorder/message_marketplace_order_search_succeeded',
            array('logOrder'));
        $sellerAccountId = '';
        $mockObject->setBody(array(
            'orders' => array(
                array('some_data')
            ),
            'searchMarketplaceOrder' => array(
                'sellerAccountId' => $sellerAccountId
            )
        ));

        $mockObject->expects($this->once())
                   ->method('logOrder');

        $result = $mockObject->process();

        $this->assertInstanceOf($this->_instanceOf, $result);
    }


    public function testLogOrder()
    {
        $mockObject = $this->mockModel('xcom_channelorder/message_marketplace_order_search_succeeded',
            array('save'));

        $mockLog = $this->mockModel('xcom_log/log', array('save'));
        $mockLog->expects($this->once())
                    ->method('save');

        $result = $mockObject->logOrder('description_text', 'some_type');

        $this->assertInstanceOf($this->_instanceOf, $result);
    }
}
