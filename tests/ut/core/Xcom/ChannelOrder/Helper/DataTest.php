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
class Xcom_ChannelOrder_Helper_DataTest extends Xcom_TestCase
{
    /** @var Xcom_ChannelOrder_Helper_Data */
    protected $_object;
    protected $_instanceOf = 'Xcom_ChannelOrder_Helper_Data';

    public function setUp()
    {
        parent::setUp();
        $this->_object = new Xcom_ChannelOrder_Helper_Data();
    }

    public function tearDown()
    {
        $this->_object = null;
    }

    public function testInstanceOf()
    {
        $this->assertInstanceOf($this->_instanceOf, $this->_object);
    }

    /**
     * @param $data
     * @param $result
     * @dataProvider isChannelOrderProvider
     */
    public function testIsChannelOrder($data, $result)
    {
        /** @var $objectMock Xcom_ChannelOrder_Helper_Data */
        $objectMock = $this->getMock($this->_instanceOf, array('getChannelOrder'));
        $channelOrder = new Varien_Object($data);
        $objectMock->expects($this->once())
            ->method('getChannelOrder')
            ->will($this->returnValue($channelOrder));

        $this->assertEquals($result, $objectMock->isChannelOrder());
    }

    public function isChannelOrderProvider()
    {
        return array(
            array(array('id' => 0), false),
            array(array('id' => 1), true),
        );
    }

    public function testGetChannelOrderEmpty()
    {
        $model = $this->mockModel('xcom_channelorder/order');
        $this->assertInstanceOf(get_class($model), $this->_object->getChannelOrder());
        $this->assertEmpty($this->_object->getChannelOrder()->getData());
    }

    public function testGetChannelOrder()
    {
        /** @var $objectMock Xcom_ChannelOrder_Helper_Data */
        $objectMock = $this->getMock($this->_instanceOf, array('getOrder'));
        $objectMock->expects($this->exactly(2))
            ->method('getOrder')
            ->will($this->returnValue(new Varien_Object()));

        $model = $this->mockModel('xcom_channelorder/order', array('load'));
        $model->expects($this->once())
            ->method('load')
            ->will($this->returnValue(true));
        $this->assertInstanceOf(get_class($model), $objectMock->getChannelOrder());
    }

    public function testGetChannelOrderByInvoice()
    {
        $invoice = new Varien_Object(array(
            'order' => new Varien_Object()
        ));

        $objectMock = $this->getMock($this->_instanceOf, array('getInvoice'));
        $objectMock->expects($this->exactly(2))
            ->method('getInvoice')
            ->will($this->returnValue($invoice));

        $model = $this->mockModel('xcom_channelorder/order', array('load'));
        $model->expects($this->once())
            ->method('load')
            ->will($this->returnValue(true));
        $this->assertInstanceOf(get_class($model), $objectMock->getChannelOrder());
    }

    public function testGetChannelOrderByCreditmemo()
    {
        $creditmemo = new Varien_Object(array(
            'order' => new Varien_Object()
        ));

        $objectMock = $this->getMock($this->_instanceOf, array('getCreditmemo'));
        $objectMock->expects($this->exactly(2))
            ->method('getCreditmemo')
            ->will($this->returnValue($creditmemo));

        $model = $this->mockModel('xcom_channelorder/order', array('load'));
        $model->expects($this->once())
            ->method('load')
            ->will($this->returnValue(true));
        $this->assertInstanceOf(get_class($model), $objectMock->getChannelOrder());
    }

    /**
     * @param $itemValue
     * @param $channelItemValue
     * @param $result
     * @dataProvider getChannelOrderItemProvider
     */
    public function testGetChannelOrderItem($itemValue, $channelItemValue, $result)
    {
        $itemMock = $this->getMock('Mage_Sales_Model_Order_Item', array('getProductId'));
        $itemMock->expects($this->once())
            ->method('getProductId')
            ->will($this->returnValue($itemValue));
        /** @var $objectMock Xcom_ChannelOrder_Helper_Data */
        $objectMock = $this->getMock($this->_instanceOf, array('getChannelOrder'));
        $channelOrder = new Varien_Object(array(
            'order_items' => array(new Varien_Object(array('order_item_id' => $channelItemValue)))
        ));
        $objectMock->expects($this->once())
            ->method('getChannelOrder')
            ->will($this->returnValue($channelOrder));
        if ($result) {
            $this->assertEquals($result, $objectMock->getChannelOrderItem($itemMock)->getOrderItemId());
        } else {
            $this->assertEquals($result, $objectMock->getChannelOrderItem($itemMock));
        }
    }

    public function getChannelOrderItemProvider()
    {
        return array(
            array('test', 'test', 'test'),
            array('test', '123', false),
            array('123', '', false),
        );
    }

    public function testGetChannelAccountHtml()
    {
        $channelOrder = new Varien_Object(array(
            'xaccount_id' => 'test_xaccount_id'
        ));
        $objectMock = $this->getMock($this->_instanceOf, array('getChannelOrder'));
        $objectMock->expects($this->once())
            ->method('getChannelOrder')
            ->will($this->returnValue($channelOrder));
        $account = new Varien_Object(array(
            'environment_value' => 'test_env',
            'user_id' => 'test_user_id'));
        $accountMock = $this->mockModel('xcom_mmp/account', array('load'));
        $accountMock->expects($this->once())
            ->method('load')
            ->will($this->returnValue($account));

        $this->assertEquals('test_user_id (test_env)', $objectMock->getChannelAccountHtml());
    }

    public function testGetChannelAccountHtmlEmpty()
    {
        $channelOrder = new Varien_Object();
        $objectMock = $this->getMock($this->_instanceOf, array('getChannelOrder'));
        $objectMock->expects($this->once())
            ->method('getChannelOrder')
            ->will($this->returnValue($channelOrder));
        $this->assertEmpty($objectMock->getChannelAccountHtml());
    }

    public function testChannelName()
    {
        $channelOrder = new Varien_Object(array(
            'channel_id' => 'test_channel_id'
        ));
        $objectMock = $this->getMock($this->_instanceOf, array('getChannelOrder'));
        $objectMock->expects($this->once())
            ->method('getChannelOrder')
            ->will($this->returnValue($channelOrder));
        $channel = new Varien_Object(array(
            'name' => 'test_name'
        ));
        $channelMock = $this->mockModel('xcom_mmp/channel', array('load'));
        $channelMock->expects($this->once())
            ->method('load')
            ->will($this->returnValue($channel));
        $this->assertEquals('test_name', $objectMock->getChannelName());
    }

    /**
     * @expectedException Xcom_ChannelOrder_Exception
     */
    public function testValidateOrderEnvironmentEmptyData()
    {
        $this->_object->validateOrderEnvironment('');
    }

    /**
     * @expectedException Xcom_ChannelOrder_Exception
     */
    public function testValidateOrderEnvironmentWrong()
    {
        $xaccountId = 'TEST_ACCOUNT';
        $env = 'Sandbox';
        $this->mockStoreConfig(Xcom_ChannelOrder_Helper_Data::CHANNELORDER_VALIDATION_SETTINGS_SANDBOX_ENABLED, 0);
        $accountMock = $this->mockModel('xcom_mmp/account',
            array('load', 'getEnvironmentValue'));
        $accountMock->expects($this->once())
            ->method('load')
            ->with($this->equalTo($xaccountId), $this->equalTo('xaccount_id'))
            ->will($this->returnValue($accountMock));

        $accountMock->expects($this->once())
            ->method('getEnvironmentValue')
            ->will($this->returnValue($env));

        $this->_object->validateOrderEnvironment($xaccountId);
    }

    public function testValidateOrderEnvironmentSandboxAllowed()
    {
        $xaccountId = 'TEST_ACCOUNT';
        $this->mockStoreConfig(
            Xcom_ChannelOrder_Helper_Data::CHANNELORDER_VALIDATION_SETTINGS_SANDBOX_ENABLED, 1
        );
        $accountMock = $this->mockModel('xcom_mmp/account',
            array('load', 'getEnvironmentValue'));
        $accountMock->expects($this->never())
            ->method('load');

        $accountMock->expects($this->never())
            ->method('getEnvironmentValue');

        $result = $this->_object->validateOrderEnvironment($xaccountId);
        $this->assertTrue($result);

    }

    public function testValidateOrderEnvironment()
    {
        $xaccountId = 'TEST_ACCOUNT';
        $env = 'Production';
        $this->mockStoreConfig(
            Xcom_ChannelOrder_Helper_Data::CHANNELORDER_VALIDATION_SETTINGS_SANDBOX_ENABLED, 0
        );
        $accountMock = $this->mockModel('xcom_mmp/account',
            array('load', 'getEnvironmentValue'));

        $accountMock->expects($this->once())
            ->method('load')
            ->with($this->equalTo($xaccountId), $this->equalTo('xaccount_id'))
            ->will($this->returnValue($accountMock));

        $accountMock->expects($this->once())
            ->method('getEnvironmentValue')
            ->will($this->returnValue($env));

        $result = $this->_object->validateOrderEnvironment($xaccountId);
        $this->assertTrue($result);
    }

    public function testPrepareSourceAmounts()
    {
        $data = array(
            'param_1_1' => 'param_1_2',
            'amount' => '1,234.00',
            'params_2_1' => array(
                'amount' => '2,345.00'
            ),
            'params_3_1' => array(
                'params_3_2' => array(
                    'amount' => '3,456.00'
                )
            ),
        );
        $this->_object->prepareSourceAmounts($data);

        $this->assertTrue(is_array($data));
        $this->assertEquals('1234.00', $data['amount']);
        $this->assertEquals('2345.00', $data['params_2_1']['amount']);
        $this->assertEquals('3456.00', $data['params_3_1']['params_3_2']['amount']);
    }
}
