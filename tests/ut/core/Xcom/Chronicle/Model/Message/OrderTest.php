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
 * @package     Xcom_Chronicle
 * @copyright   Copyright (c) 2010 Magento Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

class Xcom_Chronicle_Model_Message_OrderTest extends Xcom_TestCase
{
    /**
     * @var Mage_Sales_Model_Order mock
     */
    protected $_orderMock  = null;


    public function setup()
    {
        parent::setUp();
        //$channelOrdersMock = $this->mockResource('xcom_order/order');
        //$channelOrderMock = $this->mockModel('xcom_order/order');

        //Mock need to be created per-test as far as some mocked methods may not be needed
        /*$this->_shipmentMock = $this->mockModel('Mage_Sales_Model_Order_Shipment',
            array('getIncrementId', 'getAllTracks', 'getOrder'));
        $orderMock =   $this->mockModel('Mage_Sales_Model_Order', array(
            'getRealOrderId',
            'getShippingAddress',
            'getPayment'));

        $paymentMock = $this->mockModel('Mage_Sales_Model_Order_Payment');

        $shippingAddressMock = $this->mockModel('Mage_Sales_Model_Order_Address', array('getCountryId'));
        $shippingAddressMock->expects($this->once())
            ->method('getCountryId')
            ->will($this->returnValue('US'));

        $orderMock->expects($this->once())
            ->method('getShippingAddress')
            ->will($this->returnValue($shippingAddressMock));
        $orderMock->expects($this->once())
            ->method('getPayment')
            ->will($this->returnValue($paymentMock));
        $this->_orderMock = $orderMock;*/


    }

    public function tearDown()
    {
        $this->_orderMock = null;
    }

    public function testBasicCreation()
    {
        $this->markTestIncomplete('Need to be fixed');
        //Mock Mage_Sales_Model_Order
        $this->_orderMock->expects($this->atLeastOnce())
            ->method('getRealOrderId')
            ->will($this->returnValue(1234));


        $message = new Xcom_Chronicle_Model_Message_Order(
            array(
                'order' => $this->_orderMock
            )
        );

        $msgArray = $message->toArray();

        $this->assertNotNull($msgArray['orderNumber']);
        $this->assertEquals('Magento', $msgArray['source']);
        $this->assertNotNull($msgArray['sourceId']);
    }


    public function testNullChannelOrder()
    {
        $this->markTestIncomplete('Need to be fixed');
        //Mock Mage_Sales_Model_Order
        $this->_orderMock->expects($this->atLeastOnce())
            ->method('getRealOrderId')
            ->will($this->returnValue(1234));


        $message = new Test_Xcom_Chronicle_Model_Message_Order($this->_orderMock);

        $msgArray = $message->toArray();

        $this->assertNotNull($msgArray['orderNumber']);
        $this->assertEquals('Magento', $msgArray['source']);
        $this->assertNotNull($msgArray['sourceId']);
    }


    public function testChannelOrder()
    {
        $this->markTestIncomplete('Need to be fixed');
        //Mock Mage_Sales_Model_Order
        // Should get order number from $channelOrderModel order model
        $this->_orderMock->expects($this->never())
            ->method('getRealOrderId')
            ->will($this->returnValue(1234));

        $channelOrderModel = $this->mockModel('Varien_Object', array('getSource', 'getData', 'getOrderNumber'));
        $channelOrderModel->expects($this->atLeastOnce())
            ->method('getSource')
            ->will($this->returnValue('eBay'));
        $channelOrderModel->expects($this->atLeastOnce())
            ->method('getOrderNumber')
            ->will($this->returnValue('42'));
        $channelOrderModel->expects($this->any())
            ->method('getData')
            ->will($this->returnValue(array('source'=>'eBay')));


        $message = new Test_Xcom_Chronicle_Model_Message_Order($this->_orderMock, null, $channelOrderModel);

        $msgArray = $message->toArray();

        $this->assertEquals('42', $msgArray['orderNumber']);
        $this->assertNotEquals('Magento', $msgArray['source']);
        $this->assertNotNull($msgArray['sourceId']);
    }

    public function testCreateGuestCustomerId()
    {
        $orderMock =   $this->mockModel('Mage_Sales_Model_Order', array(
            'getRealOrderId',
            'getCustomerId',
        ));
        $orderMock->expects($this->any())
            ->method('getCustomerId')
            ->will($this->returnValue('1234'));
        $mockAuth = $this->mockModel('xcom_xfabric/authorization', array('getCapabilityId'));
        $mockAuth->expects($this->any())
            ->method('getCapabilityId')
            ->will($this->returnValue('testCapId'));
        $message = new Test_Xcom_Chronicle_Model_Message_Order($orderMock);
        $customerId = $message->createCustomerId();
        $this->assertEquals(array('namespace' => 'testCapId', 'Id' => '1234'), $customerId);
    }

    public function testCreateTax()
    {
        $orderMock = $this->mockModel('Mage_Sales_Model_Order');
        $itemMock = $this->mockModel('Mage_Sales_Model_Order_Item');
        $qty = 1;
        $message = new Test_Xcom_Chronicle_Model_Message_Order($orderMock);
        $data = $message->createTax($itemMock, $qty, Xcom_Chronicle_Model_Message_Order::LINE_ITEM_TYPE_REFUNDED);

        $this->assertTrue(isset($data['taxability']));
        $this->assertTrue(isset($data['taxType']));
        $this->assertTrue(isset($data['taxesCharged']));
    }

}

class Test_Xcom_Chronicle_Model_Message_Order extends Xcom_Chronicle_Model_Message_Order
{
    private $_orderResource = null;
    private $_orderModel = null;

    public function __construct(Mage_Sales_Model_Order $order, $orderResource = null, $orderModel = null)
    {
        $this->_orderResource = $orderResource;
        $this->_orderModel = $orderModel;
        $params = array(
            'order' => $order,
            'type'  => Xcom_Chronicle_Model_Message_Order::TYPE_SIMPLE
        );

        parent::__construct($params);
    }

    protected function _getXcomOrderResource() {
        return $this->_orderResource;
    }

    protected function _getXcomOrderModel($order_id)
    {
        if($this->_orderModel == null) {
            parent::_getXcomOrderModel($order_id);
        } else {
            return $this->_orderModel;
        }
    }

    public function createCustomerId()
    {
        return $this->_createCustomerId();
    }

    /** @param Mage_Sales_Model_Order_Item $item */
    public function createTax($item, $qty, $taxSource)
    {
        return $this->_createTax($item, $qty, $taxSource);
    }
}
