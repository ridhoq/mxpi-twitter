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
 * @package     Xcom_Mapping
 * @copyright   Copyright (c) 2010 Magento Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

class Test_Eventstest_Model_Observer
{
    public function testMessageReceived(Varien_Event_Observer $observer)
    {
        /* @deprecated Backward compatibility with Messaging Framework 0.0.1 */
        $message = $observer->getEvent()->getMessage();
        echo 'response_message_received ';
    }

    public function testMessageValidate(Varien_Event_Observer $observer)
    {
        /* @deprecated Backward compatibility with Messaging Framework 0.0.1 */
        $message = $observer->getEvent()->getMessage();
        $message->setValidated(true);
        $status = Mage::getModel('xcom_xfabric/message_response')
            ->load($message->getId())
            ->getStatus();
        if ($status == Xcom_Xfabric_Model_Message::MESSAGE_STATUS_VALID) {
            echo 'valid ';
        }
        echo 'response_message_validate ';
    }

    public function testMessageValidated(Varien_Event_Observer $observer)
    {
        /* @deprecated Backward compatibility with Messaging Framework 0.0.1 */
        $message = $observer->getEvent()->getMessage();
        echo 'response_message_validated ';
    }

    public function testProcessMessages(Varien_Event_Observer $observer)
    {
        /* @deprecated Backward compatibility with Messaging Framework 0.0.1 */
        $message = $observer->getEvent()->getMessage();
        echo 'response_message_process ';
    }

    public function testProcessMessagePing(Varien_Event_Observer $observer)
    {
        /* @deprecated Backward compatibility with Messaging Framework 0.0.1 */
        $message = $observer->getEvent()->getMessage();
        echo 'response_message_process_ping ';
    }

    public function testProcessLoopMessagePing(Varien_Event_Observer $observer)
    {
        /* @deprecated Backward compatibility with Messaging Framework 0.0.1 */
        $message = $observer->getEvent()->getMessage();
        echo 'response_message_looped_process_ping ';
    }

    public function testProcessMessagePostponed(Varien_Event_Observer $observer)
    {
        /* @deprecated Backward compatibility with Messaging Framework 0.0.1 */
        $message = $observer->getEvent()->getMessage();
        Mage::register('observer_called', 'response_message_process_postponed_message_ping');
    }

    public function testProcessMessageComXCoreV1MessageReceived(Varien_Event_Observer $observer)
    {
        $message = $observer->getEvent()->getMessage();
        echo 'response_message_process_com_x_core_v1_messagereceived ';
    }

    public function testProcessMessageComXCoreV1MessageValidated(Varien_Event_Observer $observer)
    {
        $message = $observer->getEvent()->getMessage();
        echo 'response_message_process_com_x_core_v1_messagevalidated ';
    }
}
