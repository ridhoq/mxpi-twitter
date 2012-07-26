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
class Xcom_Xfabric_Model_MessageTest extends Xcom_TestCase
{
    protected $_object;

    public function tearDown()
    {
        $this->_object = null;
    }

    public function testObject()
    {
        $this->_object = new Xcom_Xfabric_Model_Message(array());
        $this->assertInstanceOf('Xcom_Xfabric_Model_Message', $this->_object);
    }

    public function testSave()
    {
        $headers = array(
            'HEADER1' => 'test',
            'AUTHORIZATION' => 'test_2lkj4234'
        );

        $topic = 'com.x.ordermanagement.v2/ProcessSalesChannelOrder/OrderCancelled';

        $body = "{'test':'test'}";

        $messageData = "{'test1':'test1'}";

        $response = $this->_createResponseMock($topic, $headers, $body, $messageData);
        $options = array(
            'db_adapter' => $response,
            'headers' => $headers,
            'topic' => $topic,
            'body' => $body,
            'message_data' => $messageData
        );

        $response->expects($this->once())
            ->method('save')
            ->will($this->returnValue($response));

        $this->_object = Mage::getModel('xcom_xfabric/message', $options);
        $this->_object->save();


    }

    public function testGetId()
    {
        $testId = 2;
        $response = $this->mockModel('xcom_xfabric/message_response', array('getId'));
        $response->expects($this->once())
            ->method('getId')
            ->will($this->returnValue($testId));

        $options = array(
            'db_adapter' => $response,
        );

        $this->_object = Mage::getModel('xcom_xfabric/message', $options);
        $id = $this->_object->getId();
        $this->assertEquals($testId, $id);
    }

    protected function _createResponseMock($topic, $headers, $messageData, $messageData)
    {
        $response = $this->mockModel('xcom_xfabric/message_response', array('save', 'getId', 'setHeaders',
            'setBody', 'setTopic', 'setDirection', 'setStatus', 'setCorrelationId' ));
        $response->expects($this->once())
            ->method('setHeaders')
            ->with($this->equalTo($headers))
            ->will($this->returnValue($response));
        $response->expects($this->once())
            ->method('setBody')
            ->with($this->equalTo($messageData))
            ->will($this->returnValue($response));
        $response->expects($this->once())
            ->method('setTopic')
            ->with($this->equalTo($topic))
            ->will($this->returnValue($response));
        $response->expects($this->once())
            ->method('setDirection')
            ->with($this->equalTo(Xcom_Xfabric_Model_Message::DIRECTION_INBOUND))
            ->will($this->returnValue($response));
        $response->expects($this->once())
            ->method('setStatus')
            ->with($this->equalTo(Xcom_Xfabric_Model_Message::MESSAGE_STATUS_RECEIVED))
            ->will($this->returnValue($response));
        $response->expects($this->once())
            ->method('setCorrelationId')
            ->will($this->returnValue($response));
        return $response;
    }
}
