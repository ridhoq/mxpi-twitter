<?php
class Xcom_Xfabric_Model_Transport_XfabricTest extends Xcom_TestCase
{
    const XFABRIC_HOST = 'http://127.0.0.1';
    const XFABRIC_PORT = 8080;

    /**
     * Test object.
     *
     * @var Xcom_Xfabric_Model_Transport_Xfabric
     */
    protected $_object;

    public function setUp()
    {
        parent::setUp();
        $this->_object = new Xcom_Xfabric_Model_Transport_Xfabric(array('url' => 'http://x.com/'));
//        $this->_object = $this->mockModel('xcom_xfabric/transport_xfabric', array('_getResponseMessage'));
//        $this->_object->expects($this->any())
//          ->method('_getResponseMessage')
//          ->will($this->returnValue(true));
    }

    public function tearDown()
    {
        $this->_object = null;
    }

    /**
     * Test getMessage() method exception.
     *
     * @expectedException Xcom_Xfabric_Exception
     * @expectedExceptionMessage Message object is not defined
     * @return void
     */
    public function testGetMessageException()
    {
        $this->_object->getMessage();
    }

    /**
     * Test setMessage() method with correct input parameter.
     *
     * @return void
     */
    public function testSetMessage()
    {
        $message = new TestXcom_Xfabric_Model_Message_Interface();
        $this->_object->setMessage($message);
        $this->assertInstanceOf('Xcom_Xfabric_Model_Message_Abstract', $this->_object->getMessage());
    }

    /**
     * Test init() method exceptions.
     *
     * @param array $config
     * @dataProvider initExceptionConfigProvider
     * @expectedException Xcom_Xfabric_Exception
     * @return void
     */
    public function testInitException($config)
    {
        new Xcom_Xfabric_Model_Transport_Xfabric(($config));
    }

    public function initExceptionConfigProvider()
    {
        return array(
            array(
                'host' => '',
            ),
            array('')
        );
    }

    /**
     * @param array $config
     * @dataProvider validInitConfigProvider
     * @return void
     */
    public function testInit($config)
    {
        $transport = new Xcom_Xfabric_Model_Transport_Xfabric(($config));
        $this->assertInternalType('string', $transport->getUrl());
        $this->assertEquals($config['url'], $transport->getUrl());
    }

    public function validInitConfigProvider()
    {
        return array(
            array(
                array(
                    'url' => self::XFABRIC_HOST,
                )
            )
        );
    }

    /**
     * @param string $topic
     * @dataProvider topicProvider
     * @return void
     */
    public function testPreparePath($topic)
    {
        $config = array(
            'url' => self::XFABRIC_HOST . ':' . self::XFABRIC_PORT
        );
        $transport = new Xcom_Xfabric_Model_Transport_Xfabric(($config));
        $uri = $transport->prepareUri($topic);
        $this->assertTrue($uri->valid());
    }

    public function topicProvider()
    {
        return array(
            array(
                'topic',
                'topic/name',
                '/topic',
                'topic/',
                '/topic/name',
                '/topic/name/',
                'topic/name/top'
            )
        );
    }

    /**
     * @param array $config
     * @dataProvider validInitConfigProvider
     * @return void
     */
    public function testSend($config)
    {
        $marketSpecifics = 'marketSpecifics_value_1';
        $testData = array(
            'test_key_1' => 'test_data_1',
            'listing'   => array(
                'marketSpecifics' => $marketSpecifics,
            )
        );

        $header = "X-XC-SCHEMA-URI: {$config['url']}/test/topic/1";

        $encoderMock = $this->mockModel('xcom_xfabric/encoder_json', array('decodeText'));
        $encoderMock->expects($this->any())
            ->method('decodeText')
            ->will($this->returnValue($testData));

        $debugMock = $this->mockModel('xcom_xfabric/debug', array('start'));
        $debugMock->expects($this->once())
            ->method('start')
            ->with(
                $this->equalTo('Send Request to ' .
                    $config['url'] . '/test/topic/1'),
                $this->equalTo('test/topic/1'),
                $this->equalTo(serialize($header)),
                $this->equalTo(json_encode($testData))
            );

        $this->_mockResponseMessageModel('test_id_1');

        $methods = array('getCurlHeaders', 'getBody', 'getTopic', 'getEncoder', 'getMessageData');
        $messageMock = $this->getMock('Xcom_Xfabric_Model_Message_Request', $methods);
        $messageMock->expects($this->any())
            ->method('getCurlHeaders')
            ->will($this->returnValue($header));
        $messageMock->expects($this->once())
            ->method('getBody')
            ->will($this->returnValue(json_encode($testData)));
        $messageMock->expects($this->any())
            ->method('getTopic')
            ->will($this->returnValue('test/topic/1'));
        $messageMock->expects($this->any())
            ->method('getEncoder')
            ->will($this->returnValue($encoderMock));
        $messageMock->expects($this->any())
            ->method('getMessageData')
            ->will($this->returnValue($testData));

        $this->_mockResourceResponseMessageModel($messageMock);

        $adapterMock = $this->getMock('Varien_Http_Adapter_Curl', array('write', 'read', 'getErrno', 'getError'));
        $adapterMock->expects($this->once())
            ->method('read')
            ->will($this->returnValue(array('test_response_key' => 'test')));

        $objectMock = $this->_mockObjectAdapter($adapterMock);
        $objectMock->setMessage($messageMock);
        //$objectMock->init($config);

        $result = $objectMock->send();
        $this->assertTrue($result);
    }

    /**
     * @param array $config
     * @dataProvider validInitConfigProvider
     * @expectedException Xcom_Xfabric_Exception
     * @expectedExceptionMessage Unable to complete the request. test_error_message
     */
    public function testSendWithAdapterError($config)
    {
        $header = "X-XC-SCHEMA-URI: {$config['url']}/test/topic/1";

        $methods = array('getHeaders', 'getBody', 'getTopic', 'getEncoder');
        $messageMock = $this->getMock('Xcom_Xfabric_Model_Message_Request', $methods);
        $messageMock->expects($this->any())
            ->method('getHeaders')
            ->will($this->returnValue($header));
        $messageMock->expects($this->any())
            ->method('getTopic')
            ->will($this->returnValue('test/topic/1'));
        $messageMock->expects($this->any())
            ->method('getEncoder')
            ->will($this->returnValue(false));

        $adapterMock = $this->getMock('Varien_Http_Adapter_Curl', array('write', 'read', 'getErrno', 'getError'));
        $adapterMock->expects($this->any())
            ->method('getErrno')
            ->will($this->returnValue(true));
        $adapterMock->expects($this->any())
            ->method('getError')
            ->will($this->returnValue('test_error_message'));
        $adapterMock->expects($this->any())
            ->method('read')
            ->will($this->returnValue('test_read_message'));

        $this->_mockResourceResponseMessageModel($messageMock);

        $objectMock = $this->_mockObjectAdapter($adapterMock);
        $objectMock->setMessage($messageMock);
        //$objectMock->init($config);

        $result = $objectMock->send();
        $this->assertTrue($result);
    }

    /**
     * @param array $config
     * @dataProvider validInitConfigProvider
     * @expectedException Xcom_Xfabric_Exception
     */
    public function testSendException($config)
    {
        $methods = array('getBody', 'getTopic', 'getEncoder');
        $messageMock = $this->getMock('Xcom_Xfabric_Model_Message_Request', $methods);
        $messageMock->expects($this->any())
            ->method('getTopic')
            ->will($this->returnValue('test/topic/1'));
        $messageMock->expects($this->any())
            ->method('getEncoder')
            ->will($this->returnValue(false));

        $this->mockModel('xcom_xfabric/debug', array('start', 'stop'));

        $adapterMock = $this->getMock('Varien_Http_Adapter_Curl', array('write', 'read', 'getErrno', 'getError'));

        $this->_mockResponseMessageModel();
        $this->_mockResourceResponseMessageModel($messageMock);

        $objectMock = $this->_mockObjectAdapter($adapterMock);
        $objectMock->setMessage($messageMock);
        //$objectMock->init($config);
        $result = $objectMock->send();

        $this->assertTrue($result);
    }


    protected function _mockResourceResponseMessageModel($message)
    {
        $resourceMessageResponseMock = $this->mockResource('xcom_xfabric/message_response',
            array('getResponseByRequestId'));
        $resourceMessageResponseMock->expects($this->any())
            ->method('getResponseByRequestId')
            ->will($this->returnValue($message));
    }

    protected function _mockResponseMessageModel($id = null)
    {
        $methods = array('_initSchema','load', 'getId');
        $messageResponseMock = $this->getMock('Xcom_Xfabric_Model_Message_Response', $methods);
        $messageResponseMock->expects($this->any())
            ->method('_initSchema')
            ->will($this->returnValue($messageResponseMock));
        $messageResponseMock->expects($this->any())
            ->method('load')
            ->will($this->returnValue($messageResponseMock));
        $messageResponseMock->expects($this->any())
            ->method('getId')
            ->will($this->returnValue($id));
        return $messageResponseMock;
    }

    protected function _mockObjectAdapter($adapterMock)
    {
        $objectMock = $this->getMock(get_class($this->_object), array('_getAdapter', 'getUrl', '_getHttpCode', '_getHttpMessage',
            '_getHttpBody'), array(), '', false);
        $objectMock->expects($this->once())
            ->method('_getAdapter')
            ->will($this->returnValue($adapterMock));
        $objectMock->expects($this->once())
            ->method('getUrl')
            ->will($this->returnValue(self::XFABRIC_HOST));
        $objectMock->expects($this->any())
            ->method('_getHttpCode')
            ->will($this->returnValue(200));
        return $objectMock;
    }

}


class TestXcom_Xfabric_Model_Message_Interface extends Xcom_Xfabric_Model_Message_Abstract
{
    public function process() {}

    public function getCorrelationId() {}
}
