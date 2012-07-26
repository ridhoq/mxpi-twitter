<?php
class Xcom_ChannelOrder_Messages_Marketplace_Order_Search_FailedTest
        extends Xcom_TestCase
{
    protected $_object;

    protected $_instanceOf = 'Xcom_ChannelOrder_Model_Message_Marketplace_Order_Search_Failed';

    public function setUp()
    {
        parent::setUp();
        $this->_object = Mage::getModel('xcom_channelorder/message_marketplace_order_search_failed');
    }

    public function tearDown()
    {
        parent::tearDown();
        $this->_object = null;
    }

    public function testInstanceOf()
    {
        $this->assertInstanceOf($this->_instanceOf, $this->_object);
    }

    public function testGetSchemaRecordName()
    {
        $this->assertEquals('SearchMarketplaceOrderFailed', $this->_object->getSchemaRecordName());
    }

    public function testTopic()
    {
        $this->assertEquals('marketplace/order/searchFailed', $this->_object->getTopic());
    }

    public function testProcess()
    {
        $data = array(
            'errors'     => array(
                'code'      => '123',
                'message'   => 'Test error message'
            )
        );

        $objectMock = $this->getMock(get_class($this->_object), array('logOrder'));
        $objectMock->expects($this->any())
            ->method('logOrder')
            ->will($this->returnValue($objectMock));


        $objectMock->setBody($data);
        $result = $objectMock->process();
        $this->assertInstanceOf($this->_instanceOf, $result);
    }

    public function testLogOrder()
    {
        $objectMock = $this->getMock('Xcom_Log_Model_Log', array('save'));
        $objectMock->expects($this->any())
            ->method('save')
            ->will($this->returnValue($objectMock));

        $result = $this->_object->logOrder('test_description', Xcom_Log_Model_Source_Result::RESULT_ERROR);
        $this->assertInstanceOf($this->_instanceOf, $result);
    }
}
