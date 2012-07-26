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

class Xcom_Choreography_Model_Message_MessageReceived_InboundTest extends Xcom_TestCase
{
    /** @var Xcom_Choreography_Model_Message_MessageReceived_Outbound */
    protected $_object;
    protected $_instanceOf = 'Xcom_Choreography_Model_Message_MessageReceived_Inbound';

    public function setUp()
    {
        parent::setUp();
        $this->_object = new Xcom_Choreography_Model_Message_MessageReceived_Inbound();
    }

    public function tearDown()
    {
        $this->_object = null;
        parent::tearDown();
    }

    public function testInstanceOf()
    {
        $this->assertInstanceOf($this->_instanceOf, $this->_object);
    }

    public function testProcess()
    {
        $headers = array(
            'HEADER1' => 'test',
            'AUTHORIZATION' => 'test_2lkj4234',
            'X-XC-RESULT-CORRELATION-ID' => 'qwerqwerwq'
        );
        $topic = 'com.x.ordermanagement.v2/ProcessSalesChannelOrder/OrderCancelled';
        $body = array('test'=>'test');
        $messageData = array('test1'=>'test1');

        $options = array(
            'db_adapter' => Mage::getModel('xcom_xfabric/message_response'),
            'headers' => $headers,
            'topic' => $topic,
            'body' => $body,
            'message_data' => $messageData,
            'direction' => Xcom_Xfabric_Model_Message::DIRECTION_OUTBOUND,
            'status' => Xcom_Xfabric_Model_Message::MESSAGE_STATUS_INVALID
        );

        $requestMock = $this->mockModel('xcom_xfabric/message_response', array('save', 'getId'));
        $messageMock = $this->mockModel('xcom_xfabric/message', array('setReceived'), true, array(array('db_adapter' => $requestMock)));
        $messageMock->expects($this->once())
            ->method($this->equalTo('setReceived'));

        $message = Mage::getModel('xcom_xfabric/message_response', $options);
        $this->_object->process(new Varien_Event_Observer(array('event' => new Varien_Object(array('message' => $message)))));
    }

}