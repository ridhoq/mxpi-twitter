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

require_once 'Xcom/Ebay/controllers/Adminhtml/Ebay/RegisterController.php';

class Xcom_Ebay_Controllers_Adminhtml_Ebay_RegisterControllerTest extends Xcom_TestCase
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
        return $this->getMock('Xcom_Ebay_Adminhtml_Ebay_RegisterController', $methods, array($request, $response));
    }

    public function testIndexAction()
    {
        $this->mockStoreConfig(Xcom_Ebay_Helper_Data::XML_PATH_XCOM_CHANNEL_REGISTRATION_EXTENSION_ENABLED, true);
        $configMock = $this->mockModel('core/config', array('saveConfig'));
        $configMock->expects($this->once())
            ->method('saveConfig')
            ->with(
                $this->equalTo(Xcom_Ebay_Helper_Data::XML_PATH_XCOM_CHANNEL_REGISTRATION_EXTENSION_ENABLED),
                $this->equalto(true)
            );

        $sessionMock = $this->mockModel('adminhtml/session', array('addSuccess', 'addError'));
        // Mock controller
        $objectMock = $this->_getControllerMock(new Varien_Object(), new Varien_Object(),
            array('loadLayout', 'renderLayout', '_getSession'));
        $objectMock->expects($this->once())
            ->method('loadLayout');
        $objectMock->expects($this->once())
            ->method('renderLayout');
        $objectMock->expects($this->once())
            ->method('_getSession')
            ->will($this->returnValue($sessionMock));
        $sessionMock->expects($this->never())
            ->method('addError')
            ->will($this->returnValue($sessionMock));
        $sessionMock->expects($this->once())
            ->method('addSuccess');

        $objectMock->indexAction();
        $this->assertTrue(Mage::helper('xcom_ebay')->isExtensionEnabled());

    }

    public function testIndexActionException()
    {
        $this->mockStoreConfig(Xcom_Ebay_Helper_Data::XML_PATH_XCOM_CHANNEL_REGISTRATION_EXTENSION_ENABLED, false);
        $configMock = $this->mockModel('core/config', array('saveConfig'));
        $configMock->expects($this->once())
            ->method('saveConfig')
            ->will($this->throwException(new Exception));

        $sessionMock = $this->mockModel('adminhtml/session', array('addSuccess', 'addError'));
        // Mock controller
        $objectMock = $this->_getControllerMock(new Varien_Object(), new Varien_Object(),
            array('loadLayout', 'renderLayout', '_getSession'));
        $objectMock->expects($this->once())
            ->method('loadLayout');
        $objectMock->expects($this->once())
            ->method('renderLayout');
        $objectMock->expects($this->once())
            ->method('_getSession')
            ->will($this->returnValue($sessionMock));
        $sessionMock->expects($this->once())
            ->method('addError')
            ->will($this->returnValue($sessionMock));
        $sessionMock->expects($this->never())
            ->method('addSuccess');

        $objectMock->indexAction();
        $this->assertFalse(Mage::helper('xcom_ebay')->isExtensionEnabled());
    }
}
