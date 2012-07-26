<?php
class Xcom_Initializer_Model_JobTest extends Xcom_TestCase
{
    protected $_object;

    public function setUp()
    {
        parent::setUp();
        $this->_object = new Xcom_Initializer_Model_Job();
    }

    public function tearDown()
    {
        $this->_object = null;
    }

    /**
     * @expectedException Xcom_Xfabric_Exception
     * @expectedExceptionMessage Unknown topic: 'test/topic/name'
     */
    public function testProcessWrongTopic()
    {
        $this->_object->setTopic('test/topic/name');
        $this->_object->process();
    }

    /**
     * @expectedException Xcom_Xfabric_Exception
     * @expectedExceptionMessage Message for topic test/topic/name should be created
     */
    public function testProcessNoMessage()
    {
        $helperMock = $this->mockHelper('xcom_xfabric', array('getMessage'));
        $helperMock->expects($this->any())
            ->method('getMessage')
            ->will($this->returnValue(null));

        $this->_object->setTopic('test/topic/name');
        $this->_object->process();
    }

    public function testProcess()
    {
        $this->_mockObject();
        $this->_mockTransport();
        $this->mockStoreConfig('xfabric/connection_settings/encoding', 'json');

        $encoder = $this->mockModel('xcom_xfabric/encoder_avro', array('encode'));

        $helperMock = $this->mockHelper('xcom_xfabric', array('getMessage', 'save'));
        $helperMock->expects($this->any())
            ->method('getMessage')
            ->will($this->returnValue(new Xcom_Xfabric_Model_Message_Request()));

        $messageMock = $this->mockModel('xcom_xfabric/message_request', array('save', 'getEncoder'));
        $messageMock->expects($this->any())
            ->method('save')
            ->will($this->returnValue($messageMock));
        $messageMock->expects($this->any())
            ->method('getEncoder')
            ->will($this->returnValue($encoder));

        $messageParams = array(
            'param_1' => 'test_param_1',
            'param_2' => 'test_param_2',
        );
        $this->_object->setTopic('test/topic/name');
        $this->_object->setMessageParams(json_encode($messageParams));
        $this->mockModel('xcom_xfabric/schema', array(), FALSE);
        $result = $this->_object->process();
        $this->assertInstanceOf('Xcom_Initializer_Model_Job', $result);
    }

    protected  function _mockObject()
    {
        $objectMock = $this->mockModel('xcom_initializer/job', array('load', 'save'));
        $objectMock->expects($this->any())
            ->method('load')
            ->will($this->returnValue($objectMock));
        $objectMock->expects($this->any())
            ->method('save')
            ->will($this->returnValue($objectMock));
    }

    protected function _mockTransport()
    {
        $stubMock = $this->mockModel('xcom_stub/transport_stub', array('send'));
        $stubMock->expects($this->any())
            ->method('send')
            ->will($this->returnValue($stubMock));

        $transportClassName = Mage::getConfig()->getModelClassName('xcom_xfabric/transport_xfabric');
        $xfabricMock = $this->getMock($transportClassName, array('send'), array(), '', FALSE);
        $xfabricMock->expects($this->any())
            ->method('send')
            ->will($this->returnValue($xfabricMock));
        Mage::registerMockModel('xcom_xfabric/transport_xfabric', $xfabricMock);
    }
}
