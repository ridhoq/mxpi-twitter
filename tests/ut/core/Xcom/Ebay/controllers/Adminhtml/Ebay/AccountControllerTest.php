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

require_once 'Xcom/Ebay/controllers/Adminhtml/Ebay/AccountController.php';

class Xcom_Ebay_Controllers_Adminhtml_Ebay_AccountControllerTest extends Xcom_TestCase
{
    public function setUp()
    {
        $this->_checkConnection = true;
        parent::setUp();
    }

    public function tearDown()
    {
        $this->_object = null;
        parent::tearDown();
    }

    /**
     * Get controller's mock object
     *
     * @param $request
     * @param $response
     * @param array $methods
     * @return PHPUnit_Framework_MockObject_MockObject
     */
    protected function _getControllerMock($request, $response, array $methods = array())
    {
        $this->_object = $this->getMock('Xcom_Ebay_Adminhtml_Ebay_AccountController',
                                        $methods,
                                        array($request, $response));
    }

    public function testDeleteActionNoAccountId()
    {
        $session = Mage::getModel('adminhtml/session');

        $accountId = 0;

        $this->_getControllerMock(new Varien_Object(), new Varien_Object(), array('_getSession', '_redirect'));
        $this->_object->expects($this->any())
            ->method('_getSession')
            ->will($this->returnValue($session));
        $this->_object->expects($this->any())
            ->method('_redirect')
            ->with($this->equalTo('*/account/'))
            ->will($this->returnValue($this->_object));

        $this->_object->getRequest()->setData('param', array('id' => $accountId));
        $this->_object->deleteAction();

        $messages = $session->getMessages();
        $this->assertInstanceOf('Mage_Core_Model_Message_Collection', $messages);

        $error = $messages->getLastAddedMessage();
        $this->assertInstanceOf('Mage_Core_Model_Message_Error', $error);
        $this->assertEquals('Unable to find a account to delete.', $error->getCode());
    }

    public function testDeleteActionFalseValidation()
    {
        $session = Mage::getModel('adminhtml/session');

        $accountId = 1;

        $this->_getControllerMock(new Varien_Object(), new Varien_Object(), array('_getSession', '_redirect'));
        $this->_object->expects($this->any())
            ->method('_getSession')
            ->will($this->returnValue($session));
        $this->_object->expects($this->any())
            ->method('_redirect')
            ->with($this->equalTo('*/*/edit'), $this->equalTo(array('id' => $accountId)))
            ->will($this->returnValue($this->_object));

        $mmpChannelMock = $this->mockResource('xcom_mmp/channel', array('validateChannelsByAccountId'));
        $mmpChannelMock->expects($this->any())
            ->method('validateChannelsByAccountId')
            ->with($this->equalTo($accountId))
            ->will($this->returnValue(true));

        $this->_object->getRequest()->setData('param', array('id' => $accountId));
        $this->_object->deleteAction();

        $messages = $session->getMessages();
        $this->assertInstanceOf('Mage_Core_Model_Message_Collection', $messages);

        $error = $messages->getLastAddedMessage();
        $this->assertInstanceOf('Mage_Core_Model_Message_Error', $error);
        $this->assertEquals('You have one or more Channel(s) associated with this Account. ' .
            'Your associated channel, Policies and Listings will be deleted.', $error->getCode());
    }

    public function testDeleteActionSuccess()
    {
        $session = Mage::getModel('adminhtml/session');

        $accountId = 1;

        $this->_getControllerMock(new Varien_Object(), new Varien_Object(), array('_getSession', '_redirect'));
        $this->_object->expects($this->any())
            ->method('_getSession')
            ->will($this->returnValue($session));
        $this->_object->expects($this->any())
            ->method('_redirect')
            ->with($this->equalTo('*/account/'))
            ->will($this->returnValue($this->_object));

        $mmpChannelMock = $this->mockResource('xcom_mmp/channel', array('validateChannelsByAccountId'));
        $mmpChannelMock->expects($this->any())
            ->method('validateChannelsByAccountId')
            ->with($this->equalTo($accountId))
            ->will($this->returnValue(false));

        $mmpAccountMock = $this->mockModel('xcom_mmp/account', array('load', 'delete'));
        $mmpAccountMock->expects($this->any())
            ->method('load')
            ->with($this->equalTo($accountId))
            ->will($this->returnValue($mmpAccountMock));
        $mmpAccountMock->expects($this->any())
            ->method('delete')
            ->will($this->returnValue($mmpAccountMock));

        $this->_object->getRequest()->setData('param', array('id' => $accountId));
        $this->_object->deleteAction();

        $messages = $session->getMessages();
        $this->assertInstanceOf('Mage_Core_Model_Message_Collection', $messages);

        $success = $messages->getLastAddedMessage();
        $this->assertInstanceOf('Mage_Core_Model_Message_Success', $success);
        $this->assertEquals('The account has been deleted.', $success->getCode());
    }

    public function testCompleteAction()
    {
        $session = Mage::getModel('adminhtml/session');

        // Mock controller
        $this->_getControllerMock(new Varien_Object(), new Varien_Object(), array('_getSession'));
        $this->_object->expects($this->any())
            ->method('_getSession')
            ->will($this->returnValue($session));

        $this->_object->getRequest()->setData('param', array(
            'authId' => 'test_authId',
            'environment' => 'test_environment'));

        $responseObject = new Varien_Object(array('response_data' =>
            array('userMarketplaceId' => 'test_user_id',
                'authorizationExpiration' => '201112312')));
        $helperMock = $this->mockHelper('xcom_xfabric', array('send'));
        $helperMock->expects($this->any())
            ->method('send')
            ->will($this->returnValue($responseObject));

            // Mock account
        $account = $this->mockModel('xcom_mmp/account',
            array('load', 'loadAccount', 'save', 'validate'));
        $account->expects($this->any())
            ->method('load')
            ->will($this->returnValue($account));
        $account->expects($this->any())
            ->method('loadAccount')
            ->will($this->returnValue($account));
        $account->expects($this->any())
            ->method('save')
            ->will($this->returnValue($account));
        $account->expects($this->any())
            ->method('validate')
            ->will($this->returnValue(true));

        $this->_object->completeAction();
        $response = Mage::helper('core')->jsonDecode($this->_object->getResponse()->getBody());

//        $this->assertArrayHasKey('userMarketplaceId', $response);
//        $this->assertEquals('test_user_id', $response['userMarketplaceId']);

//        $this->assertArrayHasKey('validatedAt', $response);
//        $this->assertEquals('1970-01-03', $response['validatedAt']);
    }
}
