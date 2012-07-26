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

class Xcom_Choreography_Model_Message_MessageReceived_InboundTest extends Xcom_Fabric_TestCase
{
    /**
     * Test inbound message MessageReceied.
     */
    public function testProcess()
    {
        $uniqueCorrelationId = Mage::helper('core')->uniqHash();
        $result = $this->_sendResponse('com.x.core.v1/MessageReceived', '1.0.1',
            array(),
            null,
            array(Xcom_Xfabric_Model_Message::CORRELATION_ID_HEADER.': '.$uniqueCorrelationId)
        );

        // asserts
        $this->assertEquals(
            'response_message_received valid response_message_validate response_message_validated response_message_process response_message_process_com_x_core_v1_messagereceived OK',
            $result,
            'All events should be dispatched.'
        );

        $message = Mage::getModel('xcom_xfabric/message_response')->load($uniqueCorrelationId, 'correlation_id');
        $this->assertEquals(
            Xcom_Xfabric_Model_Message::MESSAGE_STATUS_RECEIVED,
            $message->getStatus(),
            'The message should have status "recieved".'
        );
    }
}
