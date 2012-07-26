<?php
/**
 * Test for message order/created
 */
class Xcom_ChannelOrder_Messages_Order_UpdatedTest extends Xcom_TestCase
{
    /**
     * @var Xcom_ChannelOrder_Model_Message_Order_Updated
     */
    protected $_object;
    /**
     * @var array
     */
    protected $_messageData = array(
        'order'     => array('orderNumber'  => 'ORDER NUMBER VALUE'),
        'accountId' => 'ACCOUNT ID VALUE'
    );

    /**
     * Class name
     *
     * @var string
     */
    protected $_instanceOf = 'Xcom_ChannelOrder_Model_Message_Order_Updated';

    /**
     * Set work object
     *
     * @return void
     */
    public function setUp()
    {
        $this->_checkConnection = true;
        parent::setUp();
        $this->_object = Mage::getModel('xcom_channelorder/message_order_updated');
    }

    public function tearDown()
    {
        parent::tearDown();
        $this->_object = null;
    }


    public function testProcessFailedMessage()
    {
        $objectMock = $this->getMock(get_class($this->_object), array('logOrder'));

        $messageOrderMock = $this->mockModel('xcom_channelorder/message_order',
            array('setOrderMessageData', 'setAccountId'));

        $messageOrderMock->expects($this->once())
                ->method('setOrderMessageData')
                ->will($this->throwException(new Xcom_ChannelOrder_Exception));
        $messageOrderMock->expects($this->never())
                ->method('setAccountId');

        $objectMock->setBody($this->_messageData);

        $objectMock->expects($this->once())
            ->method('logOrder')
            ->with($this->equalTo(''), $this->equalTo(Xcom_Log_Model_Source_Result::RESULT_ERROR));

        $objectMock->process();
    }

    public function testProcessFailed()
    {
        $objectMock = $this->getMock(get_class($this->_object), array('logOrder'));

        $messageOrderMock = $this->mockModel('xcom_channelorder/message_order',
            array('setOrderMessageData', 'setAccountId'));

        $messageOrderMock->expects($this->once())
                ->method('setOrderMessageData')
                ->will($this->throwException(new Mage_Core_Exception()));
        $messageOrderMock->expects($this->never())
                ->method('setAccountId');

        $objectMock->setBody($this->_messageData);

        $objectMock->expects($this->once())
            ->method('logOrder')
            ->with('Order was not updated for some reason. Please contact your administrator.',
                $this->equalTo(Xcom_Log_Model_Source_Result::RESULT_ERROR));

        $objectMock->process();

    }

    public function testProcess()
    {
        $objectMock = $this->getMock(get_class($this->_object), array('logOrder'));

        $messageOrderMock = $this->mockModel('xcom_channelorder/message_order',
                    array('setOrderMessageData', 'setAccountId', 'updateOrder', 'getOrderNumber'));

        $messageOrderMock->expects($this->once())
            ->method('setOrderMessageData')
            ->with($this->equalTo($this->_messageData['order']))
            ->will($this->returnValue($messageOrderMock));
        $messageOrderMock->expects($this->once())
            ->method('setAccountId')
            ->with($this->equalTo($this->_messageData['accountId']))
            ->will($this->returnValue($messageOrderMock));
        $messageOrderMock->expects($this->once())
            ->method('updateOrder')
            ->with($this->equalTo($this->_messageData['order']))
            ->will($this->returnValue($messageOrderMock));
        $messageOrderMock->expects($this->once())
            ->method('getOrderNumber')
            ->will($this->returnValue($this->_messageData['order']['orderNumber']));

        $objectMock->setBody($this->_messageData);

        $objectMock->expects($this->once())
            ->method('logOrder')
            ->with(sprintf('Order #%s was updated',$this->_messageData['order']['orderNumber']),
                $this->equalTo(Xcom_Log_Model_Source_Result::RESULT_SUCCESS));

        $objectMock->process();

    }


    public function testLogOrder()
    {
        $result         = Xcom_Log_Model_Source_Result::RESULT_SUCCESS;
        $description    = 'description_text';
        $descriptionInternal = sprintf("Topic /%s: %s", $this->_object->getTopic(), $description);

        $mockLog = $this->mockModel('xcom_log/log', array('setAutomaticType', 'setResult', 'setDescription', 'save'));
        $mockLog->expects($this->once())
            ->method('setAutomaticType')
            ->will($this->returnValue($mockLog));
        $mockLog->expects($this->once())
            ->method('setResult')
            ->with($this->equalTo($result))
            ->will($this->returnValue($mockLog));
        $mockLog->expects($this->once())
            ->method('setDescription')
            ->with($this->equalTo($descriptionInternal))
            ->will($this->returnValue($mockLog));
        $mockLog->expects($this->once())
            ->method('save')
            ->will($this->returnValue($mockLog));

        $result = $this->_object->logOrder($description, $result);

        $this->assertInstanceOf($this->_instanceOf, $result);
    }
}
