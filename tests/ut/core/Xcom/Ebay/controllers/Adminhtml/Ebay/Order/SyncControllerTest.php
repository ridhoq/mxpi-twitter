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
require_once 'Xcom/Ebay/controllers/Adminhtml/Ebay/Order/SyncController.php';
class Xcom_Ebay_Adminhtml_Ebay_Order_SyncControllerTest extends Xcom_TestCase
{
    /** @var Xcom_Ebay_Adminhtml_Ebay_Order_SyncController */
    protected $_object;
    protected $_instanceOf = 'Xcom_Ebay_Adminhtml_Ebay_Order_SyncController';

    public function setUp()
    {
        $this->_checkConnection = true;
        parent::setUp();
        $this->_object = new Xcom_Ebay_Adminhtml_Ebay_Order_SyncController(
            new Varien_Object(),
            new Varien_Object()
        );
    }

    public function tearDown()
    {
        $this->_object = null;
    }

    /**
     * @param $params
     * @param $result
     * @dataProvider dataProviderAjaxActionTest1
     * @expectedExceptionMessage All fields should be filled.
     */
    public function testAjaxActionTest1($params, $result)
    {
        $controller = new Xcom_Ebay_Adminhtml_Ebay_Order_SyncController(
                      new Varien_Object(array('params' => $params)),
                      new Varien_Object());
        $mockModel = $this->_getSyncMockModel(array('setParams'));
        $mockModel->expects($this->once())
                  ->method('setParams')
                  ->with($this->equalTo($params));
        $helperMock = $this->mockHelper('core', array('jsonEncode'));
        $helperMock->expects($this->once())
                     ->method('jsonEncode')
                     ->will($this->returnValue('{"error": "true", "message":"All fields should be filled."}'));
        $this->assertEquals($result, $controller->ajaxAction());
    }

    public function dataProviderAjaxActionTest1()
    {
        $data = array(
            'WRONG_account'      => 2,
            'start_date'         => '03/4/12',
            'start_time_hour'    => 1,
            'start_time_minute'  => 1,
            'start_time_seconds' => 2,
            'end_date'           => '03/21/12',
            'end_time_hour'      => 0,
            'end_time_minute'    => 0,
            'end_time_seconds'   => 0
        );
        return array(
            array($data, null)
        );
    }

    /**
     * @param $params
     * @param $result
     * @dataProvider dataProviderAjaxActionTest2
     */
    public function testAjaxActionTest2($params, $result)
    {
        $controller = new Xcom_Ebay_Adminhtml_Ebay_Order_SyncController(
                      new Varien_Object(array('params' => $params)),
                      new Varien_Object());
        $mockModel = $this->_getSyncMockModel(array('setParams'));
        $mockModel->expects($this->once())
                  ->method('setParams')
                  ->with($this->equalTo($params));
        $helperMock = $this->mockHelper('core', array('jsonEncode'));
        $helperMock->expects($this->once())
                     ->method('jsonEncode')
                     ->will($this->returnValue('{"error": "true", "message":"Error"}'));
        $this->assertEquals($result, $controller->ajaxAction());
    }

    /**
     * @param $params
     * @param $result
     * @dataProvider dataProviderAjaxActionTest3
     */
    public function testAjaxActionTestException($params, $result)
    {
        $controller = new Xcom_Ebay_Adminhtml_Ebay_Order_SyncController(
                      new Varien_Object(array('params' => $params)),
                      new Varien_Object());
        $mockModel = $this->_getSyncMockModel(array('setParams', 'send'));
        $mockModel->expects($this->once())
                  ->method('setParams')
                  ->with($this->equalTo($params));
        $mockModel->expects($this->once())
                  ->method('send')
                  ->will($this->throwException(new Exception()));

        $controller->ajaxAction();
        $this->assertEquals($result, $controller->getResponse()->getBody());
    }

    public function dataProviderAjaxActionTest2()
    {
        $data = array(
            'account'            => 2,
            'start_date'         => '03/4/12',
            'start_time_hour'    => 1,
            'start_time_minute'  => 1,
            'start_time_seconds' => 2,
            'end_date'           => '03/21/12',
            'end_time_hour'      => 0,
            'end_time_minute'    => 0,
            'end_time_seconds'   => 0
        );
        return array(
            array($data, false),
        );
    }

    public function dataProviderAjaxActionTest3()
    {
        $data = array(
            'account'            => 2,
            'start_date'         => '03/4/12',
            'start_time_hour'    => 1,
            'start_time_minute'  => 1,
            'start_time_seconds' => 2,
            'end_date'           => '03/21/12',
            'end_time_hour'      => 0,
            'end_time_minute'    => 0,
            'end_time_seconds'   => 0
        );
        return array(
            array($data, '{"message":"An error occurred on sending sync order message request.","error":true}'),
        );
    }

    protected function _getSyncMockModel($methods)
    {
        return $this->mockModel('xcom_ebay/order_sync', $methods);
    }
}
