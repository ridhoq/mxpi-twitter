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
class Xcom_Xfabric_Model_ObserverTest extends Xcom_TestCase
{
    protected $_object;

    public function setUp()
    {
        parent::setUp();
        $this->_object = new Xcom_Xfabric_Model_Observer();
    }

    public function tearDown()
    {
        $this->_object = null;
    }

    public function testProceedDelayedProcess()
    {
        //$this->markTestIncomplete("Skipped.");
        $items = array(
            new Varien_Object(array(
                'topic'     => 'test/topic/1',
                'headers'   => serialize('header1'),
                'body'      => serialize('body1')
            )),
            new Varien_Object(array(
                'topic'     => 'test/topic/1',
                'headers'   => serialize('header2'),
                'body'      => serialize('body2')
            ))
        );

        $collectionMock = $this->mockCollection('xcom_xfabric/message_response', $items, array(
            'load', 'setCurPage', 'setPageSize', 'getLastPageNumber',
            'addFieldToFilter', 'setOrder', 'addLimitPage',
            'getIterator', 'clear', '_initSchema'
        ));

        $collectionMock->expects($this->once())
            ->method('setCurPage')
            ->will($this->returnValue($collectionMock));

        $collectionMock->expects($this->once())
            ->method('setPageSize')
            ->will($this->returnValue($collectionMock));

        $collectionMock->expects($this->any())
            ->method('getLastPageNumber')
            ->will($this->returnValue(1));

        $collectionMock->expects($this->once())
            ->method('clear')
            ->will($this->returnValue($collectionMock));

        $collectionMock->expects($this->once())
            ->method('addFieldToFilter')
            ->with($this->equalTo('is_processed'), $this->equalTo(0))
            ->will($this->returnValue($collectionMock));

        $collectionMock->expects($this->once())
            ->method('setOrder')
            ->with($this->equalTo('created_at'), $this->equalTo(Varien_Data_Collection::SORT_ORDER_DESC))
            ->will($this->returnValue($collectionMock));

        $collectionMock->expects($this->once())
            ->method('addLimitPage')
            ->will($this->returnValue($collectionMock));

        $responseMessages = array(
            $this->_mockResponseMessage(array(
                'topic'     => 'test/topic/1',
                'headers'   => serialize('header1'),
                'body'      => serialize('body1')
            )),
            $this->_mockResponseMessage(array(
                'topic'     => 'test/topic/1',
                'headers'   => serialize('header2'),
                'body'      => serialize('body2')
            ))
         );

        $helperMock = $this->mockHelper('xcom_xfabric', array('getMessage'));

        $this->_mockXfabricGetMessageHelper($helperMock, 0, 'test/topic/1', $responseMessages[0]);
        $this->_mockXfabricGetMessageHelper($helperMock, 1, 'test/topic/1', $responseMessages[1]);

        $result = $this->_object->proceedDelayedProcess();

        $this->assertInstanceOf(get_class($this->_object), $result);
    }

    protected function _mockXfabricGetMessageHelper($helperMock, $index, $topic, $responseMessage)
    {
        $helperMock->expects($this->at($index))
            ->method('getMessage')
            ->with($this->equalTo($topic), $this->equalTo(true))
            ->will($this->returnValue($responseMessage));
    }

    protected function _mockResponseMessage(array $messageData)
    {
        $responseMessage = $this->getMock('Xcom_Xfabric_Model_Message_Response', array(
            '_initSchema', 'addData', 'setHeaders', 'setBody', 'isProcessLater', 'process', 'save'
        ));

        $responseMessage->expects($this->any())
            ->method('_initSchema')
            ->will($this->returnValue($responseMessage));

        $responseMessage->expects($this->once())
            ->method('addData')
            ->with($this->equalTo($messageData))
            ->will($this->returnValue($responseMessage));

        $responseMessage->expects($this->once())
            ->method('setHeaders')
            ->with($this->equalTo(unserialize($messageData['headers'])))
            ->will($this->returnValue($responseMessage));

        $responseMessage->expects($this->once())
            ->method('setBody')
            ->with($this->equalTo(unserialize($messageData['body'])))
            ->will($this->returnValue($responseMessage));

        $responseMessage->expects($this->once())
            ->method('isProcessLater')
            ->will($this->returnValue(true));

        $responseMessage->expects($this->once())
            ->method('process')
            ->will($this->returnValue(null));

        $responseMessage->expects($this->once())
           ->method('save')
           ->will($this->returnValue(null));

        return $responseMessage;
    }
}
