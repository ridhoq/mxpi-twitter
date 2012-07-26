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
class Xcom_Chronicle_Model_Message_Order_Cancelled_OutboundTest extends Xcom_TestCase
{
    /** @var Xcom_Chronicle_Model_Message_Order_Cancelled_Outbound */
    protected $_object;
    protected $_instanceOf = 'Xcom_Chronicle_Model_Message_Order_Cancelled_Outbound';

    public function setUp()
    {
        $this->markTestIncomplete("setUp() fails");
        parent::setUp();
        $this->_object = Mage::helper('xcom_xfabric')->getMessage('order/cancelled');
        $this->_object
            ->setEncoding(Xcom_Xfabric_Model_Message_Abstract::AVRO_JSON);
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

    public function testConstructorSetFields()
    {
        $this->assertEquals('order/cancelled', $this->_object->getTopic());
        $this->assertEquals('OrderCancelled', $this->_object->getSchemaRecordName());
        // _schemaFile is also set but there is no magical getter
    }

    public function testSchema()
    {
        $this->_disableSchema();

        $orderMock = $this->_createOrderMock();

        $mockDataObj = $this->_createProcessDataObjectMock($orderMock);

        $this->_object->process($mockDataObj);
        $messageData = $this->_object->getMessageData();

        $this->assertArrayHasKey('order', $messageData);
    }

    public function testSchemaOrderNumber()
    {
        $this->_disableSchema();

        $ORDER_NUMBER = 1023;

        $orderMock = $this->_createOrderMock(array('getShippingAddress',
                                                   'getPayment',
                                                   'getIncrementId'));
        $orderMock->expects($this->atLeastOnce())
            ->method('getIncrementId')
            ->will($this->returnValue($ORDER_NUMBER));

        $mockDataObj = $this->_createProcessDataObjectMock($orderMock);

        $this->_object->process($mockDataObj);
        $messageData = $this->_object->getMessageData();

        $this->assertArrayHasKey('order', $messageData);
        $this->assertArrayHasKey('orderNumber', $messageData['order']);
        $this->assertEquals($ORDER_NUMBER, $messageData['order']['orderNumber']);
    }

    protected function _createProcessDataObjectMock($orderMock)
    {
        $mockDataObj = $this->getMock('Varien_Object', array('getOrder'));
        $mockDataObj->expects($this->once())
            ->method('getOrder')
            ->will($this->returnValue($orderMock));

        return $mockDataObj;
    }

    protected function _createOrderMock($orderMethodsToMock = array(
                                                                    'getPayment',
                                                                    'getShippingAddress'))
    {

        $paymentMock = $this->mockModel('Mage_Sales_Model_Order_Payment');

        $shippingAddressMock = $this->mockModel('Mage_Sales_Model_Order_Address', array('getCountryId'));
        $shippingAddressMock->expects($this->once())
            ->method('getCountryId')
            ->will($this->returnValue('US'));

        $orderMock = $this->mockModel('Mage_Sales_Model_Order', $orderMethodsToMock);
        $orderMock->expects($this->once())
            ->method('getPayment')
            ->will($this->returnValue($paymentMock));
        $orderMock->expects($this->once())
            ->method('getShippingAddress')
            ->will($this->returnValue($shippingAddressMock));

        return $orderMock;
    }

    protected function _disableSchema()
    {
        $objectMock = $this->getMock(get_class($this->_object), array('_initSchema', 'encode', 'setEncoder'));
        $this->_object = $objectMock;
    }
}
