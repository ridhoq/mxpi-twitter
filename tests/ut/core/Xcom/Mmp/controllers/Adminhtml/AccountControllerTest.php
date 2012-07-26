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
 * @package     Xcom_Mmp
 * @copyright   Copyright (c) 2010 Magento Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

require_once 'Xcom/Mmp/controllers/Adminhtml/AccountController.php';

class Xcom_Mmp_Controllers_Adminhtml_AccountControllerTest extends Xcom_TestCase
{
    public function tearDown()
    {
        parent::tearDown();
        $this->_object = null;
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
        $this->_object = $this->getMock('Xcom_Mmp_Adminhtml_AccountController', $methods, array($request, $response));
        return $this->_object;
    }

    public function testMassEnableAction()
    {
        $accountMock = $this->mockModel('xcom_mmp/account', array('load', 'save'));
        $accountMock->expects($this->any())
            ->method('load')
            ->will($this->returnSelf());

        $request = new Mage_Core_Controller_Request_Http();
        $request->setParam('account', array(1,2,3));

        $sessionMock = $this->mockModel('adminhtml/session', array('addSuccess'));

        $sessionMock->expects($this->once())
            ->method('addSuccess')
            ->will($this->returnSelf());
        $objectMock = $this->_getControllerMock($request, new Varien_Object(), array('_redirect'));
        $objectMock->expects($this->once())
            ->method('_redirect')
            ->with($this->equalTo('*/account/'))
            ->will($this->returnSelf());
        $objectMock->massEnableAction();
    }

    public function testMassEnableActionMageCoreException()
    {
        $request = new Mage_Core_Controller_Request_Http();
        $request->setParam('account', array(1,2,3));

        $sessionMock = $this->mockModel('adminhtml/session', array('addError'));
        $sessionMock->expects($this->once())
            ->method('addError')
            ->with($this->equalTo("Test message"))
            ->will($this->returnSelf());

        $objectMock = $this->_getControllerMock($request, new Varien_Object(),
            array('_redirect', '_massChangeAccountAction'));
        $objectMock->expects($this->once())
            ->method('_redirect')
            ->with($this->equalTo('*/account/'))
            ->will($this->returnSelf());
        $objectMock->expects($this->once())
            ->method('_massChangeAccountAction')
            ->will($this->throwException(Mage::exception('Mage_Core', "Test message")));

        $objectMock->massEnableAction();
    }

    public function testMassDisableAction()
    {
        $session = Mage::getModel('adminhtml/session');

        $this->_getControllerMock(new Varien_Object(),
                                  new Varien_Object(),
                                  array('_getSession', '_redirect'));
        $this->_object->expects($this->any())
            ->method('_getSession')
            ->will($this->returnValue($session));
        $this->_object->expects($this->any())
            ->method('_redirect')
            ->will($this->returnValue($this->_object));

        // Mock account
        $account = $this->mockModel('xcom_mmp/account', array('load', 'save'));
        $account->expects($this->any())
            ->method('load')
            ->will($this->returnValue($account));
        $account->expects($this->any())
            ->method('save')
            ->will($this->returnValue($account));

        $this->_object->getRequest()->setData('param', array('account' => array('1')));
        $this->_object->massDisableAction();
        $errors = $session->getMessages()->getErrors();
        $this->assertEquals(0, count($errors));
    }

    public function testMassDisableValidationActionNoAjax()
    {
        $object = new Xcom_Mmp_Adminhtml_AccountController(
            new Mage_Core_Controller_Request_Http(),
            new Varien_Object()
        );

        $result = $object->massDisableValidationAction();
        $this->assertNull($result);
    }

    public function testMassDisableValidationActionWithNotArrayParam()
    {
        $request = $this->_getRequest(true);
        $request->setParam('account', 'test_string');
        $response = new Varien_Object();

        $object = new Xcom_Mmp_Adminhtml_AccountController($request, $response);

        $object->massDisableValidationAction();
        $this->assertContains('{"message":', $response->getBody());
    }

    public function testMassDisableValidationActionValidateMessage()
    {
        $request = $this->_getRequest(true);
        $request->setParam('account', array(1));
        $response = new Varien_Object();

        $channelResource = $this->mockResource('xcom_mmp/channel', array('validateChannelsByAccountId'));
        $channelResource->expects($this->any())
            ->method('validateChannelsByAccountId')
            ->with($this->equalTo(1), $this->equalTo(true))
            ->will($this->returnValue(true));

        $object = new Xcom_Mmp_Adminhtml_AccountController($request, $response);
        $object->massDisableValidationAction();

        $this->assertContains('{"message":', $response->getBody());
    }

    protected function _getRequest($isAjax = false)
    {
        $request = new Mage_Core_Controller_Request_Http();
        $request->setParam('ajax', $isAjax);
        return $request;
    }

    public function testMassDisableValidationActionValidateException()
    {
        $objectFixture = new Xcom_Mmp_Model_Resource_ChannelTest_Fixture();
        Mage::registerMockResourceModel('xcom_mmp/channel', $objectFixture);

        $request = $this->_getRequest(true);
        $request->setParam('account', array(1));
        $response = new Varien_Object();
        $object = new Xcom_Mmp_Adminhtml_AccountController($request, $response);
        $object->massDisableValidationAction();

        $this->assertContains('{"message":"Test Exception Message"}', $response->getBody());
    }
}

class Xcom_Mmp_Model_Resource_ChannelTest_Fixture
{
    public function validateChannelsByAccountId($param1, $param2)
    {
        throw new Exception('Test Exception Message');
    }
}
