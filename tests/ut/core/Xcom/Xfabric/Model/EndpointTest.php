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
class Xcom_Xfabric_Model_EndpointTest extends Xcom_TestCase
{
    protected $_object;

    public function setUp()
    {
        parent::setUp();
    }

    public function tearDown()
    {
        $this->_object = null;
    }

    /**
     * Get controller's mock object
     *
     * @param array $methods
     * @return PHPUnit_Framework_MockObject_MockObject
     */
    protected function _getEndpoint($headers = array())
    {
        $transportStub = $this->getMock('Xcom_Xfabric_Model_Transport_Interface', array('sendMessage', 'send', 'setMessage'));
        $transportStub->expects($this->any())
            ->method('sendMessage')
            ->will($this->returnArgument(0));

        $messageDataStub = $this->getMock('Xcom_Xfabric_Model_Message_Data_Interface', array('getOptions', 'getMessageData'));
        $messageDataStub->expects($this->any())
            ->method('getOptions')
            ->will($this->returnValue($headers));

        $authorizationStub = $this->getMock('Xcom_Xfabric_Model_Authorization_Interface');
        $encoderStub = $this->getMock('Xcom_Xfabric_Model_Encoder_Interface');
        $schemaStub = $this->getMock('Xcom_Xfabric_Model_Schema_Interface', array('getRawSchema', 'getSchemaUri'));

        $schemaStub->expects($this->any())
            ->method('getRawSchema')
            ->will($this->returnValue('rawSchema'));

        $schemaStub->expects($this->any())
            ->method('getSchemaUri')
            ->will($this->returnValue('schemaUri'));

        $this->_object = Mage::getModel('xcom_xfabric/endpoint',
                                            array(
                                                'transport' => $transportStub,
                                                'message_data' => $messageDataStub,
                                                'authorization' => $authorizationStub,
                                                'encoder' => $encoderStub,
                                                'encoding' => 'avro/binary',
                                                'schema' => $schemaStub,
                                            )
                                        );
    }

    public function testMessageGuidContinuationHeaderSet()
    {
        $expectedValue = 'msg_guid_continuation_1234';
        $this->_getEndpoint(array('message_guid_continuation' => $expectedValue));

        $msg = $this->_object->send();
        $actualHeaders = $msg->getHeaders();

        $this->assertEquals($expectedValue, $actualHeaders['X-XC-MESSAGE-GUID-CONTINUATION']);
        $this->assertEquals($expectedValue, $msg->getMessageGuidContinuation());
    }

    public function testMessageWorkflowIdHeaderSet()
    {
        $expectedValue = 'abcd_workflow_id_1234';
        $this->_getEndpoint(array('workflow_id' => $expectedValue));

        $msg = $this->_object->send();
        $actualHeaders = $msg->getHeaders();

        $this->assertEquals($expectedValue, $actualHeaders['X-XC-WORKFLOW-ID']);
        $this->assertEquals($expectedValue, $msg->getWorkflowId());
    }

    public function testTransactionIdHeaderSet()
    {
        $expectedValue = 'abcd_transaction_id_1234';
        $this->_getEndpoint(array('transaction_id' => $expectedValue));

        $msg = $this->_object->send();
        $actualHeaders = $msg->getHeaders();

        $this->assertEquals($expectedValue, $actualHeaders['X-XC-TRANSACTION-ID']);
        $this->assertEquals($expectedValue, $msg->getTransactionId());
    }

    protected function _createResponseMock($messageId)
    {
        $response = $this->mockModel('xcom_xfabric/message_response', array('save', 'getId'));
        $response->expects($this->any())
            ->method('save')
            ->will($this->returnValue($response));
        $response->expects($this->any())
            ->method('getId')
            ->will($this->returnValue($messageId));
    }

    protected function _getMessageDataMock($pseudonym)
    {
        $messageDataOptions = array(
            'topic' => 'com.x.ordermanagement.v2/ProcessSalesChannelOrder/OrderCancelled',
            'headers' => array(Xcom_Xfabric_Model_Message::PUBLISHER_PSEUDONYM_HEADER => $pseudonym),
            'body' => "{'errors' : 'test'}",
            'topic_options' => array()
        );
        return Mage::getModel('xcom_xfabric/message_data_inbound', $messageDataOptions);
    }

    protected function _getSchemaMock()
    {
        return $this->mockModel('xcom_xfabric/schema', array(), false);
    }

    protected function _getAuthMock($pseudonym)
    {
        $authorization = $this->mockModel('xcom_xfabric/authorization', array('getBearerData'));
        $authorization->expects($this->any())
            ->method('getBearerData')
            ->with($this->equalTo('pseudonym'))
            ->will($this->returnValue($pseudonym));
        return $authorization;
    }

    protected function _getEndpointMock($callback, $pseudonymMessage, $pseudonymAuth)
    {
        $endpointOptions = array(
            'message_data' => $this->_getMessageDataMock($pseudonymMessage),
            'schema'  => $this->_getSchemaMock(),
            'encoder'  => Mage::getModel('xcom_xfabric/encoder_json'),
            'encoding' => 'json',
            'authorization' => $this->_getAuthMock($pseudonymAuth),
            'callback_handler' => $callback,
        );

        return $this->mockModel('xcom_xfabric/endpoint', array('validateAuthorizationHeader'),
            true, array($endpointOptions));
    }

    protected function _getCallbackMock($messageId)
    {
        $callback = $this->mockModel('xcom_xfabric/callback_dispatcher', array('invokeInboundReceived',
            'invokeInboundValidate', 'invokeInboundProcessGeneric', 'invokeInboundLoopedProcess',
            'invokeInboundProcessByTopic'));

        $callback->expects($this->once())
            ->method('invokeInboundReceived')
            ->with($this->equalTo($messageId), $this->isInstanceOf('Xcom_Xfabric_Model_Message'));

        $callback->expects($this->once())
            ->method('invokeInboundValidate')
            ->with($this->equalTo($messageId), $this->isInstanceOf('Xcom_Xfabric_Model_Message'));

        $callback->expects($this->once())
            ->method('invokeInboundProcessGeneric')
            ->with($this->equalTo($messageId), $this->isInstanceOf('Xcom_Xfabric_Model_Message'));

        return $callback;
    }

    /**
     * Tests that all callbacks are called when message is received.
     * Scenario: message is not looped back
     */
    public function testReceive()
    {
        $messageId = 2;

        /* Different pseudonyms */
        $pseudonymMessage = md5(uniqid(mt_rand(), true));
        $pseudonymAuth = md5(uniqid(mt_rand(), true));
        $callback = $this->_getCallbackMock($messageId);
        $this->_createResponseMock($messageId);

        /** Message is not looped back */
        $callback->expects($this->once())
            ->method('invokeInboundProcessByTopic')
            ->with($this->equalTo('com.x.ordermanagement.v2/ProcessSalesChannelOrder/OrderCancelled'),
                $this->equalTo($messageId), $this->isInstanceOf('Xcom_Xfabric_Model_Message'));

        $endpoint = $this->_getEndpointMock($callback, $pseudonymMessage, $pseudonymAuth);
        $endpoint->receive();
        $this->assertInstanceOf('Xcom_Xfabric_Model_Endpoint', $endpoint);
    }

    /**
     * Tests that all callbacks are called when message is received.
     * Scenario: message is not looped back
     */
    public function testReceiveLoopback()
    {
        $messageId = 2;

        /* Same pseudonyms */
        $pseudonymMessage = md5(uniqid(mt_rand(), true));
        $pseudonymAuth = $pseudonymMessage;
        $callback = $this->_getCallbackMock($messageId);
        $this->_createResponseMock($messageId);

        /** Message is looped back */
        $callback->expects($this->once())
            ->method('invokeInboundLoopedProcess')
            ->with($this->equalTo('com.x.ordermanagement.v2/ProcessSalesChannelOrder/OrderCancelled'),
            $this->equalTo($messageId), $this->isInstanceOf('Xcom_Xfabric_Model_Message'));

        $endpoint = $this->_getEndpointMock($callback, $pseudonymMessage, $pseudonymAuth);
        $endpoint->receive();
        $this->assertInstanceOf('Xcom_Xfabric_Model_Endpoint', $endpoint);
    }
}
