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
 * @package     Xcom_ChannelOrder
 * @copyright   Copyright (c) 2010 Magento Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class Xcom_ChannelOrder_Model_OrderTest extends Xcom_TestCase
{
    /** @var Xcom_ChannelOrder_Model_Order */
    protected $_object;
    protected $_instanceOf = 'Xcom_ChannelOrder_Model_Order';

    public function setUp()
    {
        parent::setUp();
        $this->_object = new Xcom_ChannelOrder_Model_Order();
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

    public function testAfterLoad()
    {
        $resourceMock = $this->mockResource('xcom_channelorder/order', array('_afterLoad'));
        $objectMock = $this->mockModel('xcom_channelorder/order', array('getResource'));
        $objectMock->expects($this->any())
            ->method('getResource')
            ->will($this->returnValue($resourceMock));
        $payment = $this->mockModel('xcom_channelorder/order_payment', array('load'));
        $payment->expects($this->any())
            ->method('load')
            ->will($this->returnValue(new Varien_Object(array())));

        $items = array(array(1), array(2));
        $collectionMock = $this->mockResource('xcom_channelorder/order_item', array('addFieldToFilter'));
        $collectionMock->expects($this->any())
            ->method('addFieldToFilter')
            ->will($this->returnValue($items));

        $itemsMock = $this->mockModel('xcom_channelorder/order_item', array('getCollection'));

        $itemsMock->expects($this->once())
            ->method('getCollection')
            ->will($this->returnValue($collectionMock));
        $objectMock->afterLoad();

        $this->assertInstanceOf('Varien_Object', $objectMock->getPayment());
        $this->assertEquals(2, count($objectMock->getOrderItems()));
    }

    public function testGetOrderNoOrderId()
    {
        $salesModelMock = $this->mockModel('sales/order', array('load'));
        $mockModel = $this->_getChannelOrderMockForGetOrder(false);
        $return = $mockModel->getOrder();
        $this->assertInstanceOf('Mage_Sales_Model_Order', $return);
    }

    public function testGetOrderWithOrderId()
    {
        $orderId = rand(10, 10000);
        $salesModelMock = $this->mockModel('sales/order', array('load'));
        $salesModelMock->expects($this->once())
                       ->method('load')
                       ->with($this->equalTo($orderId));
        $mockModel = $this->_getChannelOrderMockForGetOrder($orderId, true);
        $return = $mockModel->getOrder();
        $this->assertInstanceOf('Mage_Sales_Model_Order', $return);
    }

    protected function _getChannelOrderMockForGetOrder($will, $any = false)
    {
        $mockModel = $this->mockModel('xcom_channelorder/order', array('getOrderId'));
        $mockModel->expects($any ? $this->any() : $this->once())
                  ->method('getOrderId')
                  ->will($this->returnValue($will));
        return $mockModel;
    }
}
