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
class Xcom_ChannelOrder_Model_ObserverTest extends Xcom_TestCase
{
    /**
     * Tested object
     *
     * @var Xcom_ChannelOrder_Model_Observer
     */
    protected $_object;

    /**
     * Set up object
     *
     * @return void
     */
    public function setUp()
    {
        parent::setUp();
        $this->_object = new Xcom_ChannelOrder_Model_Observer();
    }

    /**
     * Reset object
     *
     * @return void
     */
    public function tearDown()
    {
        parent::tearDown();
        $this->_object = null;
    }

    public function testCheckAvailableReorder()
    {
        $orderMock = $this->mockModel('sales/order', array('load'));
        $id = 1;
        $incrementId = 10001;
        $orderMock
            ->setId($id)
            ->setData('increment_id' , $incrementId);
        $orderMock->expects($this->any())
            ->method('load')
            ->will($this->returnValue($orderMock));

        $helperMock = $this->mockHelper('xcom_channelorder', array('isChannelOrder'));
        $helperMock->expects($this->once())
            ->method('isChannelOrder')
            ->will($this->returnValue(true));

        //mock request model for controller
        $requestMock = $this->getMock('stdClass', array('getParam', 'setParam'));
        $requestMock->expects($this->any())
            ->method('getParam')
            ->with($this->equalTo('order_id'))
            ->will($this->returnValue($id));
        $requestMock->expects($this->any())
            ->method('setParam')
            ->with($this->equalTo('order_id'))
            ->will($this->returnValue($id));

        //mock controller for observer
        $controllerMock = $this->getMock('stdClass', array('getRequest'));
        $controllerMock->expects($this->any())
            ->method('getRequest')
            ->will($this->returnValue($requestMock));

        $observer = new Varien_Event_Observer();
        $observer->setData('controller_action', $controllerMock);

        //process
        $this->_object->checkAvailableReorder($observer);

        /** @var $session Mage_Adminhtml_Model_Session_Quote */
        $session = Mage::getSingleton('adminhtml/session');
        $errors = $session->getMessages()->getErrors();

        //test errors count
        $countErrors = 1;
        $this->assertEquals($countErrors, count($errors));
        /** @var $error Mage_Core_Model_Message_Error */
        $error = $errors[0];
        $this->assertEquals(
            'Order #' . $incrementId . ' cannot be reordered because it is from external marketplace.',
            $error->getText());
        //clean errors
        $session->getMessages(true);

        /**
         * Test skipping order which is not external
         */
        $helperMock = $this->mockHelper('xcom_channelorder', array('isChannelOrder'));
        $helperMock->expects($this->once())
            ->method('isChannelOrder')
            ->will($this->returnValue(false));

        //process
        $this->_object->checkAvailableReorder($observer);
        //test errors count
        $errors = $session->getMessages()->getErrors();
        $countErrors = 0;
        $this->assertEquals($countErrors, count($errors));
        //clean errors
        $session->getMessages(true);

        /**
         * Test skipping order which not found
         */
        $orderMock->setId(null);
        /**
         * Process
         * NOTE: Method Xcom_ChannelOrder_Helper_Data::isChannelOrder() must not run
         */
        $this->_object->checkAvailableReorder($observer);
        //test errors count
        $errors = $session->getMessages()->getErrors();
        $countErrors = 0;
        $this->assertEquals($countErrors, count($errors));
        //clean errors
        $session->getMessages(true);
    }
}
