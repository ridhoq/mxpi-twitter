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
 * @package     Xcom_Xfabric
 * @copyright   Copyright (c) 2010 Magento Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class Xcom_Xfabric_Model_EventsTest extends Xcom_Fabric_TestCase
{

    public function testEvents()
    {
        $result = $this->_sendResponse('message/ping', '1.0.0', array('payload' => null));
        $this->assertEquals('response_message_received valid response_message_validate response_message_validated response_message_process response_message_process_ping OK', $result);
    }

    public function testEventsLooped()
    {
        $pseudonym = Mage::getModel('xcom_xfabric/authorization')
            ->load()
            ->getBearerData(Xcom_Xfabric_Model_Authorization::PSEUDONYM);
        $result = $this->_sendResponse('message/ping', '1.0.0', array('payload' => null), $pseudonym);
        $this->assertEquals('response_message_received valid response_message_validate response_message_validated response_message_process response_message_looped_process_ping OK', $result);
    }

    public function testProcessLater()
    {
        Mage::setAllowDispatchEvent(true);
        $responseMessage = Mage::getModel('xcom_xfabric/message_response')
            ->setDirection(Xcom_Xfabric_Model_Message::DIRECTION_INBOUND)
            ->setStatus(Xcom_Xfabric_Model_Message::MESSAGE_STATUS_RECEIVED)
            ->setBody(array())
            ->addData(array())
            ->setTopic('message/ping')
            ->setHeaders(array())
            ->save();
        Mage::getModel('xcom_xfabric/observer')->proceedDelayedProcess();
        $this->assertEventCalledTimes('response_message_process_postponed_message_ping', 1);
        $this->assertEquals('response_message_process_postponed_message_ping', Mage::registry('observer_called'));
    }
}
