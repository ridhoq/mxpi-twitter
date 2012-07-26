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
 * @package     Xcom_Ebay
 * @copyright   Copyright (c) 2010 Magento Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class Xcom_Ebay_Model_Order_SyncTest extends Xcom_TestCase
{
    /** @var Xcom_Ebay_Model_Order_Sync */
    protected $_object;
    protected $_instanceOf = 'Xcom_Ebay_Model_Order_Sync';

    public function setUp()
    {
        $this->_checkConnection = true;
        parent::setUp();
        $this->_object = new Xcom_Ebay_Model_Order_Sync();
    }

    public function tearDown()
    {
        parent::tearDown();
        $this->_object = null;
    }

    public function testSetParams()
    {
        $this->assertInstanceOf($this->_instanceOf, $this->_object->setParams(array('test')));
    }

    /**
     * @expectedException Mage_Core_Exception
     */
    public function testValidate()
    {
        $this->_object->setParams(array('wrong_data'));
        $this->_object->validate();
    }

    /**
     * @expectedException Mage_Core_Exception
     */
    public function testValidate2()
    {
        $data = array(
            'account'            => 2,
            'start_date'         => '03/4/12',
            'start_time_hour'    => 1,
            'start_time_minute'  => 1,
            'start_time_seconds' => 2,
            'end_date'           => '',
            'end_time_hour'      => 0,
            'end_time_minute'    => 0,
            'end_time_seconds'   => 0
        );
        $this->_object->setParams($data);
        $this->_object->validate();
    }

    /**
     * First date bigger then end date
     * @expectedException Mage_Core_Exception
     */
    public function testValidate3()
    {
        $data = array(
            'account'            => 2,
            'start_date'         => '04/5/12',
            'start_time_hour'    => 1,
            'start_time_minute'  => 1,
            'start_time_seconds' => 2,
            'end_date'           => '03/4/12',
            'end_time_hour'      => 0,
            'end_time_minute'    => 0,
            'end_time_seconds'   => 0
        );
        $this->_object->setParams($data);
        $this->_object->validate();
    }

    /**
     * @expectedException Mage_Core_Exception
     */
    public function testValidate4()
    {
        $data = array(
            'account'            => 2,
            'start_date'         => '03/5/12',
            'start_time_hour'    => 1,
            'start_time_minute'  => 1,
            'start_time_seconds' => 2,
            'end_date'           => '03/3/12',
            'end_time_hour'      => 0,
            'end_time_minute'    => 0,
            'end_time_seconds'   => 0
        );
        $this->_object->setParams($data);
        $this->_object->validate();
    }

    public function testValidate5()
    {
        $data = array(
            'account'            => 2,
            'start_date'         => '03/1/12',
            'start_time_hour'    => 1,
            'start_time_minute'  => 1,
            'start_time_seconds' => 2,
            'end_date'           => '03/3/12',
            'end_time_hour'      => 0,
            'end_time_minute'    => 0,
            'end_time_seconds'   => 0
        );
        $this->_object->setParams($data);
        $this->assertTrue($this->_object->validate());
    }

    /**
     * @expectedException Mage_Core_Exception
     */
    public function testValidate6()
    {
        $data = array(
            'account'            => 2,
            'start_date'         => '03/5/12',
            'start_time_hour'    => 1,
            'start_time_minute'  => 1,
            'start_time_seconds' => 2,
            'end_date'           => date('m/d/Y', strtotime('+1 week')),
            'end_time_hour'      => 0,
            'end_time_minute'    => 0,
            'end_time_seconds'   => 0
        );
        $this->_object->setParams($data);
        $this->_object->validate();
    }

    public function testSend()
    {
        $data = array(
            'account'            => 2,
            'start_date'         => '03/1/12',
            'start_time_hour'    => 1,
            'start_time_minute'  => 1,
            'start_time_seconds' => 2,
            'end_date'           => '03/3/12',
            'end_time_hour'      => 0,
            'end_time_minute'    => 0,
            'end_time_seconds'   => 0
        );

        $this->_object->setParams($data);

        $mockAccountModel = $this->mockModel('xcom_mmp/account', array('load', 'getXaccountId'));
        $mockAccountModel->expects($this->once())
                  ->method('load')
                  ->will($this->returnSelf());
        $mockAccountModel->expects($this->once())
                  ->method('getXaccountId')
                  ->will($this->returnValue(1));

        $items = array(array('site_code' => 'test_site_code'));
        $mockCollection = $this->mockCollection('xcom_mmp/channel', $items, array(
            'addChanneltypeCodeFilter', 'load', 'addFieldToFilter'));
        $mockCollection->expects($this->once())
               ->method('addFieldToFilter')
               ->will($this->returnSelf());
        $mockCollection->expects($this->any())
                ->method('addChanneltypeCodeFilter')
                ->will($this->returnSelf());

        $mockHelper = $this->mockHelper('xcom_xfabric', array('send'));
        $mockHelper->expects($this->once())
                   ->method('send');

        $this->_object->send();
    }

    public function testInstanceOf()
    {
        $this->assertInstanceOf($this->_instanceOf, $this->_object);
    }
}
