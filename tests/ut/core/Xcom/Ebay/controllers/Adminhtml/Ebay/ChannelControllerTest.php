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
require_once 'Xcom/Ebay/controllers/Adminhtml/Ebay/ChannelController.php';
class Xcom_Ebay_Controllers_Adminhtml_Ebay_ChannelControllerTest extends Xcom_TestCase
{
    /** @var Xcom_Ebay_Adminhtml_Ebay_ChannelController */
    protected $_object;

    public function setUp()
    {
        $this->_checkConnection = true;
        parent::setUp();
        $request = new Varien_Object();
        $response = new Varien_Object();
        $this->_object = new Xcom_Ebay_Adminhtml_Ebay_ChannelController($request, $response);
    }

    public function tearDown()
    {
        parent::tearDown();
        $this->_object = null;
    }

    /**
     * @dataProvider dataProvideForSaveAction
     * @param $redirectBack
     * @param $isAuthorized
     */
    public function testSaveAction($redirectBack, $isAuthorized)
    {
        $response = new Varien_Object();
        $channelId = mt_rand(1000, 9999);
        $channelData = array('channel_data' => 'test');
        $mrequest = new Mage_Core_Controller_Request_Http();
        $mrequest->setParam('back', $redirectBack);
        $mrequest->setParam('channel_id', $channelId);
        $mrequest->setPost($channelData);

        $mockController = $this->_getControllerMock($mrequest ,$response,
            array('_getSession', '_redirect', '_redirectToGrid', 'setChannelData'));

        $accountMock = new Varien_Object(array('user_id' => $isAuthorized));
        $channel = $this->mockModel('xcom_ebay/channel',
            array('addData', 'getAccount', 'validate', 'save', 'load'));
        $channel->expects($this->once())
            ->method('addData')
            ->with($channelData);
        $channel->expects($this->once())
            ->method('getAccount')
            ->will($this->returnValue($accountMock));
        $channel->expects($this->once())
            ->method('load')
            ->with($channelId)
            ->will($this->returnSelf());

        $sessionMock = $this->mockModel('adminhtml/session',
            array('addSuccess', 'setXcomEbayData', 'addError'));

        $sessionMock->expects($this->any())
                    ->method('setXcomEbayData');
        $sessionMock->expects($this->any())
                    ->method('addError')
                    ->will($this->returnValue($mockController));

        $mockController->expects($this->any())
                       ->method('_getSession')
                       ->will($this->returnValue($sessionMock));

        if($isAuthorized) {
            $channel->expects($this->once())
                    ->method('validate');
            $channel->expects($this->once())
                    ->method('save');
        }

        if($redirectBack && false !== $isAuthorized) {
            $mockController->expects($this->once())
                           ->method('_redirect');
        } elseif(false !== $isAuthorized) {
            $mockController->expects($this->once())
                           ->method('_redirectToGrid');
        }
        $mockController->saveAction();
    }


    public function dataProvideForSaveAction()
    {
        return array(
            array(true, true),
            array(false, false),
        );
    }

    public function testDeleteAction()
    {
        $response = new Varien_Object();

        $mrequest = $this->getMock('Varien_Object', array('getParam'));

        $mrequest->expects($this->at(0))
                 ->method('getParam')
                 ->with('channel_id')
                 ->will($this->returnValue(1));

        $sessionMock = $this->getMock('Mage_Adminhtml_Model_Session', array('addSuccess',
            'addError'
        ));


        $mockController = $this->_getControllerMock($mrequest ,$response, array('_initChannel',
            '_getSession', '_redirectToGrid'));


        $mockChannel = $this->mockModel('xcom_ebay/channel', array('load', 'delete'));
        $mockChannel->expects($this->once())
                    ->method('load')
                    ->will($this->returnValue($mockChannel));

        $mockChannel->expects($this->once())
                    ->method('delete')
                    ->will($this->returnValue($mockChannel));

        $registryKey = '_singleton/' . 'adminhtml/session';
        Mage::register($registryKey, $sessionMock);

        $mockController->expects($this->once())
                    ->method('_redirectToGrid')
                    ->will($this->returnValue($mockChannel));
        $mockController->expects($this->once())
                    ->method('_getSession')
                    ->will($this->returnValue($sessionMock));

        $mockController->deleteAction();
    }

    public function testPolicyActionWithPolicyData()
    {
        $mockChannel = $this->mockModel('xcom_ebay/channel', array('load'));
        $mockChannel->expects($this->once())
            ->method('load')
            ->will($this->returnSelf());

        $response = new Varien_Object();

        $mrequest = new Mage_Core_Controller_Request_Http();
        $mrequest->setParam('channel_id', 1);

        $sessionMock = $this->mockModel('adminhtml/session',
            array('getXcomEbayPolicyData', 'unsXcomEbayPolicyData'));
        $sessionMock->expects($this->once())
                    ->method('getXcomEbayPolicyData')
                    ->will($this->returnValue(true));

        $mockController = $this->_getControllerMock($mrequest ,$response,
            array('_getSession', 'loadLayout', 'renderLayout'));
        $mockController->expects($this->any())
                    ->method('_getSession')
                    ->will($this->returnValue($sessionMock));

        $mockPolicy = $this->mockModel('xcom_ebay/policy', array('addData', 'setEditFlag'));
        $mockPolicy->expects($this->once())
                   ->method('addData')
                   ->with($this->equalTo(true))
                   ->will($this->returnSelf());
        $mockPolicy->expects($this->once())
                   ->method('setEditFlag')
                   ->with($this->equalTo(true));
        $sessionMock->expects($this->any())
                    ->method('unsXcomEbayPolicyData');

        $mockController->expects($this->once())
           ->method('loadLayout')
           ->with($this->equalTo(false))
           ->will($this->returnSelf());
        $mockController->expects($this->once())
                       ->method('renderLayout');

        $mockController->policyAction();
        $this->assertInstanceOf(get_class($mockPolicy), Mage::registry('current_policy'));
    }

    public function testPolicyActionWithoutPolicyData()
    {
        $this->mockModel('xcom_ebay/channel');

        $response = new Varien_Object();
        $mrequest = new Mage_Core_Controller_Request_Http();
        $mrequest->setParam('site_code', true);
        $mrequest->setParam('edit', true);
        $mrequest->setParam('policy_id', 999);

        $sessionMock = $this->mockModel('adminhtml/session', array('getXcomEbayPolicyData'));
        $sessionMock->expects($this->once())
            ->method('getXcomEbayPolicyData')
            ->will($this->returnValue(false));

        $mockController = $this->_getControllerMock($mrequest ,$response,
            array('_getSession', 'loadLayout', 'renderLayout'));
        $mockController->expects($this->exactly(2))
            ->method('_getSession')
            ->will($this->returnValue($sessionMock));

        $mockController->expects($this->once())
            ->method('loadLayout')
            ->with($this->equalTo(false))
            ->will($this->returnSelf());
        $mockController->expects($this->once())
            ->method('renderLayout');

        $mockPolicy = $this->mockModel('xcom_ebay/policy', array('setEditFlag', 'load'));
        $mockPolicy->expects($this->once())
            ->method('setEditFlag')
            ->with($this->equalTo(true));

        $mockController->policyAction();
        $this->assertInstanceOf(get_class($mockPolicy), Mage::registry('current_policy'));
    }

    public function testSavePolicyWithoutPostDataAndWithoutRedirectParam()
    {
        $channelTypeCode = 'test_code';
        $this->mockModel('xcom_ebay/channel');
        $helperMock = $this->mockHelper('xcom_ebay', array('getChanneltypeCode'));
        $helperMock->expects($this->once())
            ->method('getChanneltypeCode')
            ->will($this->returnValue($channelTypeCode));
        $mockController = $this->_getControllerMock(new Varien_Object(), new Varien_Object(),
            array('_redirect')
        );
        $mockController->expects($this->once())
            ->method('_redirect')
            ->with($this->equalTo('*/channel/'), $this->equalTo(array('type' => $channelTypeCode)))
            ->will($this->returnValue('test_code'));

        $mockController->saveAction();
    }

    public function testSavePolicyWithoutPostDataAndWitRedirectParam()
    {
        $channelMock = $this->mockModel('xcom_ebay/channel', array('getId'));
        $channelMock->expects($this->once())
            ->method('getId')
            ->will($this->returnValue('test_id'));
        $request = new Mage_Core_Controller_Request_Http();
        $request->setParam('back', true);
        $mockController = $this->_getControllerMock($request, new Varien_Object(),
            array('_redirect')
        );
        $mockController->expects($this->once())
            ->method('_redirect')
            ->with($this->equalTo('*/*/edit'), $this->equalTo(array('channel_id' => 'test_id', '_current' => true)))
            ->will($this->returnValue('test_code'));

        $mockController->saveAction();
    }

    public function testSavePolicyAction()
    {
        $channelMock = $this->mockModel('xcom_ebay/channel', array('getId', 'load'));
        $channelMock->expects($this->once())
            ->method('load')
            ->will($this->returnSelf());
        $channelMock->expects($this->once())
            ->method('getId')
            ->will($this->returnValue(11));

        $response = new Varien_Object();
        $mrequest = $this->getMock('Varien_Object', array('getParam', 'getPost', 'setParam'));
        $mrequest->expects($this->any())
            ->method('getParam')
            ->will($this->returnValue(10));
        $mrequest->expects($this->any())
            ->method('getPost')
            ->will($this->returnValue(array('test_data')));
        $mrequest->expects($this->once())
            ->method('setParam')
            ->with('policy_id', 0);

        $sessionMock = $this->mockModel('adminhtml/session', array('addError',
            'addSuccess', 'setXcomEbayPolicyData'
        ));
        $sessionMock->expects($this->any())
            ->method('addError');
        $sessionMock->expects($this->any())
            ->method('addSuccess');

        $mockController = $this->_getControllerMock($mrequest, $response, array('_getSession', '_forward'));
        $mockController->expects($this->any())
            ->method('_getSession')
            ->will($this->returnValue($sessionMock));

        $mockController->expects($this->once())
            ->method('_forward')
            ->with($this->equalTo('policy'));

        $mockHelper = $this->mockHelper('xcom_xfabric', array('send'));
        $mockHelper->expects($this->once())
            ->method('send')
            ;//->with($this->equalTo('marketplace/profile/update'));

        $mockPolicy = $this->mockModel('xcom_ebay/policy', array('load',
            'addData', 'prepareShippingData', 'validate', 'getId',
            'save', 'savePolicyShipping'
        ));

        $mockPolicy->expects($this->once())
            ->method('load')
            ->will($this->returnValue($mockPolicy));
        $mockPolicy->expects($this->once())
            ->method('addData')
            ->with(array('test_data'))
            ->will($this->returnValue($mockPolicy));
        $mockPolicy->expects($this->once())
            ->method('prepareShippingData')
            ->will($this->returnValue($mockPolicy));
        $mockPolicy->expects($this->once())
            ->method('validate')
            ->will($this->returnValue($mockPolicy));
        $mockPolicy->expects($this->once())
            ->method('getId')
            ->will($this->returnValue('TEST_POLICY_ID'));
        $mockPolicy->expects($this->once())
            ->method('save');
        $mockPolicy->expects($this->once())
            ->method('savePolicyShipping');

        $mockController->savePolicyAction();
    }

    public function testMassEnablePolicyAction()
    {
        $mresponse = new Varien_Object();

        $mrequest = $this->getMock('Varien_Object', array('getParam'));
        $mrequest->expects($this->at(0))
                 ->method('getParam')
                 ->with('selected_policy', array())
                 ->will($this->returnValue(array(1)));

        $sessionMock = $this->getMock('Mage_Adminhtml_Model_Session', array('addSuccess', 'addError'));
        $sessionMock->expects($this->any())
                    ->method('addSuccess');
        $registryKey = '_singleton/' . 'adminhtml/session';
        Mage::register($registryKey, $sessionMock);

        $mockPolicy = $this->mockModel('xcom_ebay/policy', array('load', 'save'));
        $mockPolicy->expects($this->once())
                   ->method('load')
                   ->will($this->returnValue($mockPolicy));
        $mockPolicy->expects($this->once())
                   ->method('save');

        $mockController = $this->_getControllerMock($mrequest ,$mresponse, array('_forward'));
        $mockController->expects($this->once())
                       ->method('_forward')
                       ->with('policy');

        $mockController->massEnablePolicyAction();
    }

    public function testMassDisablePolicyAction()
    {
        $mresponse = new Varien_Object();

        $mrequest = $this->getMock('Varien_Object', array('getParam'));
        $mrequest->expects($this->at(0))
                 ->method('getParam')
                 ->with('selected_policy', array())
                 ->will($this->returnValue(array(1)));

        $sessionMock = $this->getMock('Mage_Adminhtml_Model_Session', array('addSuccess', 'addError'));
        $sessionMock->expects($this->any())
                    ->method('addSuccess');
        $registryKey = '_singleton/' . 'adminhtml/session';
        Mage::register($registryKey, $sessionMock);

        $mockPolicy = $this->mockModel('xcom_ebay/policy', array('load', 'save'));
        $mockPolicy->expects($this->once())
                   ->method('load')
                   ->will($this->returnValue($mockPolicy));
        $mockPolicy->expects($this->once())
                   ->method('save');

        $mockController = $this->_getControllerMock($mrequest ,$mresponse, array('_forward'));
        $mockController->expects($this->once())
                       ->method('_forward')
                       ->with('policy');

        $mockController->massDisablePolicyAction();
    }

    /**
     * Test for method editAction.
     *
     * @dataProvider providerEditAction
     *
     * @param int|null $postChannelId - edited channel id
     * @param int $channelId - payment ID from request
     * @param bool $sessionError - exception excepted
     */
    public function testEditAction($postChannelId, $channelId, $sessionError = false)
    {
//        $channel = $this->mockModel('xcom_ebay/channel', array('getId'));
//
//        if ($postChannelId) {
//        $channel->expects($this->once())
//            ->method('getId')
//            ->will($this->returnValue($channelId));
//        }
//
//        //parameters for controller constructor
//        $response   = new Mage_Core_Controller_Response_Http();
//        $request    = Mage::app()->getRequest()
//            ->setParam('id', $postChannelId);
//
//        //when try to load non-existent channel
//        if ($sessionError) {
//            $controller = $this->_getControllerMock($request, $response, array('_initChannel', '_redirectToGrid'));
//        } else {
//            $controller = $this->_getControllerMock($request, $response,
//                            array('_initChannel', 'renderLayout', 'loadLayout','_setActiveMenu'));
//            $controller->expects($this->once())
//                ->method('renderLayout');
//            $controller->expects($this->once())
//                ->method('loadLayout')
//                ->will($this->returnValue($controller));
//        }
//        $controller->expects($this->once())
//                ->method('_initChannel')
//                ->will($this->returnValue($channel));
//
//        // act
//        $controller->editAction();
    }

    /**
     * Data provider for testEditAction
     *
     * @return array
     */
    public function providerEditAction()
    {
        return array(
            array(null, null),
            array(111, 1),
            array(1111, null, "This channel no longer exists."),
        );
    }

    /**
     * @dataProvider policyDataProvider
     */
//    public function testSavePoliciesCreateProfile(array $policyData)
//    {
//        $policy = $this->mockModel('xcom_ebay/policy', array('save', 'validateName', 'savePolicyShipping'));
////        $policy->expects($this->once())
////            ->method('validateName')
////            ->will($this->returnValue(true));
//
//        $this->mockStoreConfig('xfabric/message_settings/ebay_dest_id', 'test_dest_id');
//
//        $options = array(
//            'channel'        => new Varien_Object(),
//            'policy'         => $policy,
//            'destination_id' => 'test_dest_id',
//        );
//
//        $this->_mockXfabricHelper('marketplace/profile/create', $options);
//
//        $this->_object->savePolicies(new Varien_Object(), $policyData);
//    }
//
//    /**
//     * @dataProvider policyDataProvider
//     */
//    public function testSavePoliciesUpdateProfile(array $policyData)
//    {
//        $methods = array('save', 'validateName', 'updateMarketplaceProfile', 'getId', 'savePolicyShipping');
//        $policy = $this->mockModel('xcom_ebay/policy', $methods);
//        $policy->expects($this->any())
//                ->method('getId')
//                ->will($this->returnValue('test_id_1'));
////        $policy->expects($this->once())
////            ->method('validateName')
////            ->will($this->returnValue(true));
//
//        $this->mockStoreConfig('xfabric/message_settings/ebay_dest_id', 'test_dest_id');
//
//        $options = array(
//            'channel'        => new Varien_Object(),
//            'policy'         => $policy,
//            'destination_id' => 'test_dest_id'
//        );
//
//        $this->_mockXfabricHelper('marketplace/profile/update', $options);
//
//        $this->_object->savePolicies(new Varien_Object(), $policyData);
//    }


    protected function _mockXfabricHelper($topic, $options)
    {
        $xfabricHelperMock = $this->mockHelper('xcom_xfabric', array('send'));
        $xfabricHelperMock->expects($this->once())
            ->method('send')
            ->with($this->equalTo($topic), $this->equalTo($options))
            ->will($this->returnValue(null));
    }

    public function policyDataProvider()
    {
        return array(
            array(array(array(
                'payment_name' => 'test_1',
                'shipping_data' => 'test_2',
                'location' => 'test_3',
                'postal_code' => '12345'
            )))
        );
    }



    /**
     * get controller's mock object
     *
     * @param $request
     * @param $response
     * @param array $methods
     * @return PHPUnit_Framework_MockObject_MockObject
     */
    protected function _getControllerMock($request, $response, array $methods = array())
    {
        $controller = $this->getMock('Xcom_Ebay_Adminhtml_Ebay_ChannelController',$methods, array($request, $response));
        return $controller;
    }
}
