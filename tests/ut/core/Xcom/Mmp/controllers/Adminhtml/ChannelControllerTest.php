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

require_once 'Xcom/Mmp/controllers/Adminhtml/ChannelController.php';

class Xcom_Mmp_Controllers_Adminhtml_ChannelControllerTest extends Xcom_TestCase
{
    public function setUp()
    {
    }

    public function tearDown()
    {
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
        $this->_object = $this->getMock('Xcom_Mmp_Adminhtml_ChannelController', $methods, array($request, $response));
    }

    public function testMassEnableAction()
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

        // Mock channel
        $channel = $this->mockModel('xcom_mmp/channel', array('load', 'save'));
        $channel->expects($this->any())
            ->method('load')
            ->will($this->returnValue($channel));
        $channel->expects($this->any())
            ->method('save')
            ->will($this->returnValue($channel));

        $this->_object->getRequest()->setData('param', array('selected_channels' => array('1')));
        $this->_object->massEnableAction();
        $errors = $session->getMessages()->getErrors();
        $this->assertEquals(0, count($errors));
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

        // Mock channel
        $channel = $this->mockModel('xcom_mmp/channel', array('load', 'save'));
        $channel->expects($this->any())
            ->method('load')
            ->will($this->returnValue($channel));
        $channel->expects($this->any())
            ->method('save')
            ->will($this->returnValue($channel));

        $this->_object->getRequest()->setData('param', array('selected_channels' => array('1')));
        $this->_object->massDisableAction();
        $errors = $session->getMessages()->getErrors();
        $this->assertEquals(0, count($errors));
    }


    public function testMassDisableValidationActionNoAjax()
    {
        $object = new Xcom_Mmp_Adminhtml_ChannelController(
            new Mage_Core_Controller_Request_Http(),
            new Varien_Object()
        );

        $result = $object->massDisableValidationAction();
        $this->assertNull($result);
    }

    public function testMassDisableValidationActionWithNotArrayParam()
    {
        $request = $this->_getRequest(true);
        $request->setParam('selected_channels', 'test_string');
        $response = new Varien_Object();

        $object = new Xcom_Mmp_Adminhtml_ChannelController($request, $response);

        $object->massDisableValidationAction();
        $this->assertContains('{"message":', $response->getBody());
    }

    public function testMassDisableValidationActionValidateMessage()
    {
        $request = $this->_getRequest(true);
        $request->setParam('selected_channels', array(1));
        $response = new Varien_Object();

        $channelResource = $this->mockResource('xcom_mmp/channel', array('isProductPublishedInChannels'));
        $channelResource->expects($this->any())
            ->method('isProductPublishedInChannels')
            ->with($this->equalTo(array(1)))
            ->will($this->returnValue(true));

        $object = new Xcom_Mmp_Adminhtml_ChannelController($request, $response);
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
        $objectFixture = new Xcom_Mmp_Model_Resource_ChannelControllerTest_Fixture();
        Mage::registerMockResourceModel('xcom_mmp/channel', $objectFixture);

        $request = $this->_getRequest(true);
        $request->setParam('selected_channels', array(1));
        $response = new Varien_Object();
        $object = new Xcom_Mmp_Adminhtml_ChannelController($request, $response);
        $object->massDisableValidationAction();

        $this->assertContains('{"message":"Test Exception Message"}', $response->getBody());
    }
}

class Xcom_Mmp_Model_Resource_ChannelControllerTest_Fixture
{
    public function isProductPublishedInChannels($param1, $param2)
    {
        throw new Exception('Test Exception Message');
    }
}
