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

class Xcom_Chronicle_Model_Message_Order_Shipped_OutboundTest extends Xcom_TestCase
{
    /** @var Xcom_Chronicle_Model_Message_Order_Cancelled_Outbound */
    protected $_object;
    protected $_instanceOf = 'Xcom_Chronicle_Model_Message_Order_Shipped_Outbound';

    public function setUp()
    {
        $this->markTestIncomplete("setUp fails");
        parent::setUp();
        $this->_object = Mage::helper('xcom_xfabric')->getMessage('order/shipment/shipped');
        $this->_object->setEncoding(Xcom_Xfabric_Model_Message_Abstract::AVRO_JSON);
    }

    public function tearDown()
    {
        $this->_object = null;
        parent::tearDown();
    }

    public function testInstanceOf()
    {
        $this->assertInstanceOf($this->_instanceOf, $this->_object);
    }

    public function testSchema()
    {
        $this->_disableSchema();

        $shipmentMock = $this->_createShipmentMock();

        $mockDataObj = $this->_createProcessDataObjectMock($shipmentMock);

        $this->_object->process($mockDataObj);
        $messageData = $this->_object->getMessageData();

        $this->assertArrayHasKey('shipmentId', $messageData);
        $this->assertArrayHasKey('orderNumber', $messageData);
    }

    protected function _createProcessDataObjectMock($shipmentMock)
    {
        $mockDataObj = $this->getMock('Varien_Object', array('getShipment'));
        $mockDataObj->expects($this->once())
            ->method('getShipment')
            ->will($this->returnValue($shipmentMock));

        return $mockDataObj;
    }

    protected function _createShipmentMock()
    {
        //Mock out Mage_Sales_Model_Order
        $ORDER_NUMBER = 1023;
        $orderMock =   $this->mockModel('Mage_Sales_Model_Order', array(
            'getRealOrderId',
            'getShippingAddress',
            'getPayment'));
        $orderMock->expects($this->atLeastOnce())
            ->method('getRealOrderId')
            ->will($this->returnValue(1234));

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

        //Mock out Mage_Sales_Model_Order_Shipment_Track
        $trackMock = $this->mockModel('Mage_Sales_Model_Order_Shipment_Track', array('getNumber', 'getTitle'));
        $trackMock->expects($this->any())
            ->method('getNumber')
            ->will($this->returnValue('track123'));
        $trackMock->expects($this->any())
            ->method('getTitle')
            ->will($this->returnValue('ups'));

        //Mock out Mage_Sales_Model_Order_Shipment
        $shipmentMock = $this->mockModel('Mage_Sales_Model_Order_Shipment',
            array('getIncrementId', 'getOrder', 'getAllTracks'));
        $shipmentMock->expects($this->once())
            ->method('getIncrementId')
            ->will($this->returnValue(4567));
        $shipmentMock->expects($this->atLeastOnce())
            ->method('getOrder')
            ->will($this->returnValue($orderMock));
        $shipmentMock->expects($this->once())
            ->method('getAllTracks')
            ->will($this->returnValue(array($trackMock)));

        return $shipmentMock;
    }

    protected function _disableSchema()
    {
        $objectMock = $this->getMock(get_class($this->_object), array('_initSchema', 'encode', 'setEncoder'));
        $this->_object = $objectMock;
    }
}
