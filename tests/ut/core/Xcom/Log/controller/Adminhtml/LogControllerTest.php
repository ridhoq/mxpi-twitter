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

require_once 'Xcom/Log/controllers/Adminhtml/LogController.php';

class Xcom_Log_Adminhtml_LogControllerTest extends Xcom_TestCase
{
    /** @var Xcom_Log_Adminhtml_LogController */
    protected $_object;

    public function setUp()
    {
        parent::setUp();
        $request = new Varien_Object();
        $response = new Varien_Object();
        $this->_object = new Xcom_Log_Adminhtml_LogController($request, $response);
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
        return $this->getMock('Xcom_Log_Adminhtml_LogController', $methods, array($request, $response));
    }

    public function testClearAction()
    {
        $logResourceMock    = $this->mockResource('xcom_log/log', array('clearAll'));
        $logResourceMock->expects($this->once())
            ->method('clearAll')
            ->will($this->returnValue($logResourceMock));

        $sessionMock = $this->mockModel('adminhtml/session', array('addSuccess'));
        $objectMock = $this->_getControllerMock(new Varien_Object(), new Varien_Object(),
            array('_getSession', '_redirect'));
        $objectMock->expects($this->once())
            ->method('_getSession')
            ->will($this->returnValue($sessionMock));
        $sessionMock->expects($this->once())
            ->method('addSuccess')
            ->will($this->returnValue($sessionMock));
        $objectMock->expects($this->once())
            ->method('_redirect')
            ->will($this->returnValue($objectMock));

        $objectMock->clearAction();
    }

    public function testIndexAction()
    {
        $objectMock = $this->_getControllerMock(new Varien_Object(), new Varien_Object(),
            array('_title', 'loadLayout', '_setActiveMenu', 'getLayout', '_addContent', 'renderLayout'));
        $layoutMock = $this->mockModel('core/layout', array('createBlock'));

        $objectMock->expects($this->at(0))
            ->method('_title')
            ->will($this->returnValue($objectMock));
        $objectMock->expects($this->at(1))
            ->method('loadLayout')
            ->will($this->returnValue($objectMock));
        $objectMock->expects($this->at(2))
            ->method('_setActiveMenu')
            ->with($this->equalTo('system/xfabric'))
            ->will($this->returnValue($objectMock));
        $objectMock->expects($this->at(3))
            ->method('getLayout')
            ->will($this->returnValue($layoutMock));

        $layoutMock->expects($this->once())
            ->method('createBlock')
            ->with($this->equalTo('xcom_log/adminhtml_log'))
            ->will($this->returnValue(null));

        $objectMock->expects($this->at(4))
            ->method('_addContent')
            ->will($this->returnValue($objectMock));
        $objectMock->expects($this->at(5))
            ->method('renderLayout')
            ->will($this->returnValue($objectMock));

        $objectMock->indexAction();
    }

    public function testThrowExceptionsClearAll()
    {
        $logResourceMock = $this->mockResource('xcom_log/log', array('clearAll'));
        $logResourceMock->expects($this->once())
            ->method('clearAll')
            ->will($this->throwException(new Exception));

        $sessionMock = $this->mockModel('adminhtml/session', array('addError', 'addSuccess'));
        $objectMock = $this->_getControllerMock(new Varien_Object(), new Varien_Object(),
            array('_getSession', '_redirect'));
        $objectMock->expects($this->any())
            ->method('_getSession')
            ->will($this->returnValue($sessionMock));
        $sessionMock->expects($this->once())
            ->method('addError')
            ->will($this->returnValue($sessionMock));
        $sessionMock->expects($this->never())
            ->method('addSuccess');
        $objectMock->expects($this->once())
            ->method('_redirect')
            ->will($this->returnValue($objectMock));

        $objectMock->clearAction();
    }
}
