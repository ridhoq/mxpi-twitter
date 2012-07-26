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
class Xcom_ChannelOrder_Block_Adminhtml_Sales_Order_View_InfoTest extends Xcom_TestCase
{
    /** @var Xcom_ChannelOrder_Helper_Data */
    protected $_object;
    protected $_instanceOf = 'Xcom_ChannelOrder_Block_Adminhtml_Sales_Order_View_Info';

    public function setUp()
    {
        parent::setUp();
        $this->_object = new Xcom_ChannelOrder_Block_Adminhtml_Sales_Order_View_Info();
    }

    public function tearDown()
    {
        $this->_object = null;
    }

    public function testInstanceOf()
    {
        $this->assertInstanceOf($this->_instanceOf, $this->_object);
    }

    public function testGetAdaptedAddressEmpty()
    {
        $address = new Varien_Object(array());
        $result = $this->_object->getAdaptedAddress($address);

        $this->assertTrue(is_string($result));
        $this->assertTrue(empty($result));
    }

    public function testGetAdaptedAddressEmptyStreet()
    {
        $data = array(
            'street' => null
        );

        $address = new Varien_Object(array($data));
        $result = $this->_object->getAdaptedAddress($address);

        $this->assertTrue(is_string($result));
        $this->assertTrue(empty($result));
    }

    public function testGetAdaptedAddressStreetOnly()
    {
        $data = array(
            'street' => 'test_street'
        );

        $mockAddress = $this->mockModel('customer/address', array('getData', 'getCountryModel', 'getRegion'));
        $mockAddress->expects($this->any())
            ->method('getData')
            ->will($this->returnValue($data));
        $mockAddress->expects($this->any())
            ->method('getCountryModel')
            ->will($this->returnValue(new Varien_Object(array('name' => 'US'))));
        $mockAddress->expects($this->once())
            ->method('getRegion')
            ->will($this->returnValue('test_reqion'));

        $result = $this->_object->getAdaptedAddress($mockAddress);

        $this->assertTrue(is_string($result));
        $this->assertFalse(empty($result));
        $this->assertContains('<br />', $result);
    }

    public function testGetAdaptedAddressFirstnameOnly()
    {
        $data = array(
            'firstname' => 'test_firstname'
        );

        $mockAddress = $this->mockModel('customer/address', array('getData'));
        $mockAddress->expects($this->any())
            ->method('getData')
            ->will($this->returnValue($data));

        $result = $this->_object->getAdaptedAddress($mockAddress);

        $this->assertTrue(is_string($result));
        $this->assertFalse(empty($result));
        $this->assertEquals('test_firstname', $result);
    }

    public function testGetAdaptedAddressLastnameOnly()
    {
        $data = array(
            'lastname' => 'test_lastname'
        );

        $mockAddress = $this->mockModel('customer/address', array('getData'));
        $mockAddress->expects($this->any())
            ->method('getData')
            ->will($this->returnValue($data));

        $result = $this->_object->getAdaptedAddress($mockAddress);

        $this->assertTrue(is_string($result));
        $this->assertFalse(empty($result));
        $this->assertEquals('test_lastname', $result);
    }

    public function testGetAdaptedAddressTelephoneOnly()
    {
        $data = array(
            'telephone' => '111-111-11'
        );

        $mockAddress = $this->mockModel('customer/address', array('getData'));
        $mockAddress->expects($this->any())
            ->method('getData')
            ->will($this->returnValue($data));

        $result = $this->_object->getAdaptedAddress($mockAddress);

        $this->assertTrue(is_string($result));
        $this->assertFalse(empty($result));
        $this->assertEquals('T: 111-111-11', $result);
    }

    public function testGetAdaptedAddress()
    {
        $data = array(
            'firstname' => 'test_firstname',
            'lastname' => 'test_lastname',
            'telephone' => '111-111-11',
        );

        $mockAddress = $this->mockModel('customer/address', array('getData'));
        $mockAddress->expects($this->any())
            ->method('getData')
            ->will($this->returnValue($data));

        $result = $this->_object->getAdaptedAddress($mockAddress);

        $this->assertTrue(is_string($result));
        $this->assertFalse(empty($result));
        $this->assertContains('<br />', $result);
        $this->assertContains('test_firstname', $result);
        $this->assertContains('test_lastname', $result);
        $this->assertContains('T: 111-111-11', $result);
    }

    public function testIsValidAddressEmptyStreet()
    {
        $address = new Varien_Object(array());
        $result = $this->_object->isValidAddress($address);
        $this->assertFalse($result);
    }

    public function testIsValidAddressStreetOnly()
    {
        $data = array(
            'street' => 'test_street'
        );

        $mockAddress = $this->mockModel('customer/address', array('getData', 'getCountryModel', 'getRegion'));
        $mockAddress->expects($this->any())
            ->method('getData')
            ->will($this->returnValue($data));
        $mockAddress->expects($this->any())
            ->method('getCountryModel')
            ->will($this->returnValue(new Varien_Object(array('name' => 'US'))));
        $mockAddress->expects($this->once())
            ->method('getRegion')
            ->will($this->returnValue('test_reqion'));

        $result = $this->_object->isValidAddress($mockAddress);
        $this->assertTrue($result);
    }
}
