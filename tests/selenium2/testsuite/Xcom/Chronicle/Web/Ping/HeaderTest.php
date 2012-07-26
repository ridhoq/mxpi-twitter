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
 * @category    tests
 * @package     selenium
 * @subpackage  tests
 * @author      Magento Core Team <core@magentocommerce.com>
 * @copyright   Copyright (c) 2010 Magento Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/**
 * Tests the sending of headers.
 *
 * @package     selenium
 * @subpackage  tests
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

class Xcom_Chronicle_Web_Ping_Headers extends Xcom_Xfabric_Model_Message_Ping_Request
{
    public function __construct($data)
    {
        parent::__construct($data);
        foreach ($data as $key => $value) {
            $this->_options[$key] = $value;
        }
    }
}

class Xcom_Chronicle_Web_Ping_HeaderTest extends Xcom_Chronicle_TestCase
{
     /**
     * Create Configurable Product
     *
     * @test
     */
    public function sendMessageWithHeaders()
    {
        $authModel = Mage::getModel('xcom_xfabric/authorization');                                                      
        $options = array(
            'destination_id' => $authModel->load()->getDestinationId(),
            'workflow_id' => 'workflow_id_here_123',
            'transaction_id' => 'transaction_id_here_456',
            'message_guid_continuation' => 'message_guid_continuation_789',
        );

        $expectedOutgoingHeaders = array(
            'X-XC-TRANSACTION-ID' => 'transaction_id_here_456',
            'X-XC-WORKFLOW-ID' => 'workflow_id_here_123',
            'X-XC-MESSAGE-GUID-CONTINUATION' => 'message_guid_continuation_789',
        );

        $expectedFabricMsgHeaders = array(
            'X-XC-TRANSACTION-ID' => 'transaction_id_here_456',
            'X-XC-WORKFLOW-ID' => 'workflow_id_here_123',
        );

        $debugNodeCollection = Mage::getResourceModel('xcom_xfabric/debug_node_collection')->setOrder('node_id');
        $originalCount = $debugNodeCollection->count();

        $messageDataModel = new Xcom_Chronicle_Web_Ping_Headers($options);

        $xfabricHelper = Mage::helper('xcom_xfabric');
        $messageOptions = $messageDataModel->getOptions();

        $options = array(
            'message_data' => $messageDataModel,
            'transport' => $xfabricHelper->getTransport(),
            'authorization' => $xfabricHelper->getAuthModel(),
            'schema' => $xfabricHelper->getSchema(
                $xfabricHelper->getSchemaUri($messageOptions['topic'], $messageOptions['schema_version'])),
            'encoder' => $xfabricHelper->getEncoder(),
            'encoding' => $xfabricHelper->getEncoding(),
        );

        $result = Mage::getModel('xcom_xfabric/endpoint', $options)->send();

        /*  Going to assume the next 3 nodes are for message/ping
            1) Outgoing ping message
            2) Reply from fabric headers for the outgoing message
            3) Incoming ping message from fabric (since it was sent to itself)
        */
        $debugNodeCollection = Mage::getResourceModel('xcom_xfabric/debug_node_collection')
            ->setOrder('debug_id', 'asc');
        $debugNodeCollection->getSelect()->limit(3, $originalCount);
        $debugNodeCollection->load();

        $nodes = array();
        foreach ($debugNodeCollection as $node) {
            $nodes[] = $node;
        }
            
        $outgoingMsg = $nodes[0];
        $this->_verifyMsgHeaders($expectedOutgoingHeaders, unserialize($outgoingMsg->getHeaders()));

        $outgoingMsgReply = $nodes[1];
        $this->_verifyMsgHeaders($expectedFabricMsgHeaders, unserialize($outgoingMsgReply->getHeaders()));

        $incomingMsg = $nodes[2];
        $this->_verifyMsgHeaders($expectedFabricMsgHeaders, unserialize($incomingMsg->getHeaders()));
    }

    protected function _verifyMsgHeaders($expectedHeaders, $actualHeaders)
    {
        foreach ($expectedHeaders as $name => $expectedValue) {
            $actualValue = $actualHeaders[$name];
            $this->assertEquals($expectedValue, $actualValue,
                "Header ${name} expected value '${expectedValue}' was '${actualValue}'"
            );
        }
    }
}
