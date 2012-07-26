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

class Xcom_Chronicle_Model_Message_ShipmentTest extends Xcom_TestCase
{
    /**
     * @var Mage_Sales_Model_Order mock
     */
    protected $_shipmentMock  = null;

    /**
     * @var Mage_Sales_Model_Order mock
     */
    protected $_orderMock  = null;

    /**
     * @var Mage_Sales_Model_Order_Shipment_Track mock
     */
    protected $_track1Mock  = null;

    /**
     * @var Mage_Sales_Model_Order_Shipment_Track mock
     */
    protected $_track2Mock  = null;

    public function setup()
    {
        parent::setUp();
        $this->_shipmentMock = $this->mockModel('Mage_Sales_Model_Order_Shipment',
            array('getIncrementId', 'getAllTracks', 'getOrder'));
        $orderMock =   $this->mockModel('Mage_Sales_Model_Order', array(
            'getRealOrderId',
            'getBillingAddress',
            'getShippingAddress',
            'getPayment'));
        $billingAddressMock = $this->mockModel('Mage_Sales_Model_Order_Address', array('getCountryId'));
        $billingAddressMock->expects($this->once())
            ->method('getCountryId')
            ->will($this->returnValue('US'));

        $paymentMock = $this->mockModel('Mage_Sales_Model_Order_Payment');

        $shippingAddressMock = $this->mockModel('Mage_Sales_Model_Order_Address', array('getCountryId'));
        $shippingAddressMock->expects($this->once())
            ->method('getCountryId')
            ->will($this->returnValue('US'));

        $orderMock->expects($this->once())
            ->method('getBillingAddress')
            ->will($this->returnValue($billingAddressMock));
        $orderMock->expects($this->once())
            ->method('getShippingAddress')
            ->will($this->returnValue($shippingAddressMock));
        $orderMock->expects($this->once())
            ->method('getPayment')
            ->will($this->returnValue($paymentMock));
        $this->_orderMock = $orderMock;


        $this->_track1Mock = $this->mockModel('Mage_Sales_Model_Order_Shipment_Track',
            array('getNumber', 'getTitle'));
        $this->_track2Mock = $this->mockModel('Mage_Sales_Model_Order_Shipment_Track',
            array('getNumber', 'getTitle'));
    }

    public function tearDown()
    {
        $this->_shipmentMock = null;
        $this->_orderMock = null;
        $this->_track1Mock = null;
        $this->_track2Mock = null;
    }

    public function testShipmentWithNoCarrierOrTracking()
    {
        $this->markTestIncomplete('Fix order number');
        //Mock Mage_Sales_Model_Order
        $this->_orderMock->expects($this->atLeastOnce())
            ->method('getRealOrderId')
            ->will($this->returnValue(1234));

        //Mock Mage_Sales_Model_Order_Shipment
        $this->_shipmentMock->expects($this->once())
            ->method('getAllTracks')
            ->will($this->returnValue(null));
        $this->_shipmentMock->expects($this->once())
            ->method('getIncrementId')
            ->will($this->returnValue(5678));
        $this->_shipmentMock->expects($this->atLeastOnce())
            ->method('getOrder')
            ->will($this->returnValue($this->_orderMock));

        $message = new Xcom_Chronicle_Model_Message_Shipment($this->_shipmentMock);

        $msgArray = $message->toArray();

        $this->assertNull($msgArray['accountId']);
        $this->assertNotNull($msgArray['orderNumber']);
        $this->assertNotNull($msgArray['shipmentId']);
        $this->assertNull($msgArray['trackingDetails']);
        $this->assertNull($msgArray['siteCode']);
    }

    public function testShipmentWithSingleCarrierAndSingleTracking()
    {
        $this->markTestIncomplete('Fix order number');
        //Mock Mage_Sales_Model_Order
        $this->_orderMock->expects($this->atLeastOnce())
            ->method('getRealOrderId')
            ->will($this->returnValue(1234));

        //Mock Mage_Sales_Model_Order_Shipment_Track
        $this->_track1Mock->expects($this->any())
            ->method('getNumber')
            ->will($this->returnValue('track123'));
        $this->_track1Mock->expects($this->any())
            ->method('getTitle')
            ->will($this->returnValue('ups'));


        //Mock Mage_Sales_Model_Order_Shipment
        $this->_shipmentMock->expects($this->once())
            ->method('getAllTracks')
            ->will($this->returnValue(array($this->_track1Mock)));
        $this->_shipmentMock->expects($this->once())
            ->method('getIncrementId')
            ->will($this->returnValue(5678));
        $this->_shipmentMock->expects($this->atLeastOnce())
            ->method('getOrder')
            ->will($this->returnValue($this->_orderMock));

        $message = new Xcom_Chronicle_Model_Message_Shipment($this->_shipmentMock);

        $msgArray = $message->toArray();

        $this->assertNull($msgArray['accountId']);
        $this->assertNotNull($msgArray['orderNumber']);
        $this->assertNotNull($msgArray['shipmentId']);
        $this->assertNotNull($msgArray['trackingDetails']);
        $this->assertNull($msgArray['siteCode']);

        $testTracks = $msgArray['trackingDetails'];
        $this->assertTrue(!empty($testTracks));
        $this->assertEquals(1, sizeof($testTracks));
        $testTrack = $testTracks[0];
        $this->assertNotNull($testTrack['carrier']);
        $testTrackingNumber = $testTrack['trackingNumbers'];
        $this->assertNotNull($testTrackingNumber[0]);
    }

    public function testShipmentWithMultiCarrierAndMultiTracking()
    {
        $this->markTestIncomplete('Fix order number');
        //Mock Mage_Sales_Model_Order
        $this->_orderMock->expects($this->atLeastOnce())
            ->method('getRealOrderId')
            ->will($this->returnValue(1234));

        //Mock Mage_Sales_Model_Order_Shipment_Track
        $this->_track1Mock->expects($this->any())
            ->method('getNumber')
            ->will($this->returnValue('track1'));
        $this->_track1Mock->expects($this->any())
            ->method('getTitle')
            ->will($this->returnValue('ups'));

        $this->_track2Mock->expects($this->any())
            ->method('getNumber')
            ->will($this->returnValue('track2'));
        $this->_track2Mock->expects($this->any())
            ->method('getTitle')
            ->will($this->returnValue('fedex'));


        //Mock Mage_Sales_Model_Order_Shipment
        $this->_shipmentMock->expects($this->any())
            ->method('getAllTracks')
            ->will($this->returnValue(array($this->_track1Mock, $this->_track2Mock)));
        $this->_shipmentMock->expects($this->any())
            ->method('getIncrementId')
            ->will($this->returnValue(5678));
        $this->_shipmentMock->expects($this->any())
            ->method('getOrder')
            ->will($this->returnValue($this->_orderMock));

        $message = new Xcom_Chronicle_Model_Message_Shipment($this->_shipmentMock);

        $msgArray = $message->toArray();

        $this->assertNull($msgArray['accountId']);
        $this->assertNotNull($msgArray['orderNumber']);
        $this->assertNotNull($msgArray['shipmentId']);
        $this->assertNotNull($msgArray['trackingDetails']);
        $this->assertNull($msgArray['siteCode']);

        $testTracks = $msgArray['trackingDetails'];
        $this->assertTrue(!empty($testTracks));
        $this->assertEquals(2, sizeof($testTracks));

        $testTrack = $testTracks[0];
        $this->assertNotNull($testTrack['carrier']);
        $testTrackingNumber = $testTrack['trackingNumbers'];
        $this->assertNotNull($testTrackingNumber[0]);

        $testTrack = $testTracks[1];
        $this->assertNotNull($testTrack['carrier']);
        $testTrackingNumber = $testTrack['trackingNumbers'];
        $this->assertNotNull($testTrackingNumber[0]);
    }

}