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
 * @package     Xcom_Stub
 * @copyright   Copyright (c) 2010 Magento Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class Xcom_Stub_Model_MessageTest extends Xcom_TestCase
{
    /** @var Xcom_Stub_Model_Message */
    protected $_object;
    protected $_instanceOf = 'Xcom_Stub_Model_Message';

    public function setUp()
    {
        parent::setUp();
        $this->_object = new Xcom_Stub_Model_Message();
    }

    public function tearDown()
    {
        $this->_object = null;
    }

    public function testPrepare()
    {
        $this->markTestSkipped('Not ready for now');
        $data = array(
            'topic' => 'messageData/test/topic',
            'body' => 'messageData/body',
        );

        $this->_mockResourceGetMessageData($data['topic'], $data['body'], $data);
        $responseMessage = $this->_mockResponseMessage($data);

        $this->_mockXfabricGetMessageHelper($data['topic'], $responseMessage);

        $requestMessage = $this->getMock('Varien_Object', array('decode'));
        $requestMessage->addData($data);
        $result = $this->_object->receive($requestMessage);

        $this->assertInstanceOf(get_class($responseMessage), $result);
    }

    protected function _mockResourceGetMessageData($topic, $body, $expectedResult)
    {
        $objectResourceMock = $this->mockResource('xcom_stub/message', array('getMessageData'));
        $objectResourceMock->expects($this->once())
            ->method('getMessageData')
            ->with($this->equalTo($topic), $this->equalTo($body))
            ->will($this->returnValue($expectedResult));
        return $objectResourceMock;
    }

    protected function _mockResponseMessage(array $messageData)
    {
        $methods = array('_initSchema','setTopic', 'setBody', 'decode', 'isProcessLater', 'process', 'save');
        $responseMessage = $this->getMock('Xcom_Xfabric_Model_Message_Response', $methods);
        $responseMessage->expects($this->any())
            ->method('_initSchema')
            ->will($this->returnValue($responseMessage));
        $responseMessage->expects($this->once())
            ->method('setBody')
            ->with($this->equalTo($messageData['body']))
            ->will($this->returnValue($responseMessage));
        $responseMessage->expects($this->once())
            ->method('isProcessLater')
            ->will($this->returnValue(false));
        $responseMessage->expects($this->once())
            ->method('process')
            ->will($this->returnValue($responseMessage));
        return $responseMessage;
    }

    protected function _mockXfabricGetMessageHelper($topic, $responseMessage)
    {
        $helperMock = $this->mockHelper('xcom_xfabric', array('getMessage'));
        $helperMock->expects($this->any())
            ->method('getMessage')
            ->with($this->equalTo($topic))
            ->will($this->returnValue($responseMessage));
    }
}
