<?php
class Xcom_Xfabric_Helper_DataTest extends Xcom_TestCase
{

    public function setUp()
    {
        $this->_object = new Xcom_Xfabric_Helper_Data();
        return parent::setUp();
    }

    public function tearDown()
    {
        $this->_object = null;
        return parent::tearDown();
    }

    public function testTruncateTopic()
    {
        $this->markTestIncomplete('Incomplete');
        $message = $this->_object->getMessage('/marketplace/site/searchSucceeded');
        $this->assertTrue(is_object($message), "Truncate topic does not work");
    }

    public function testGetTransport()
    {
        Mage::registerStoreConfigMock('xfabric/connection_settings/adapter', 'xcom_xfabric/transport_xfabric');
        $authModel = $this->mockModel('xcom_xfabric/authorization', array('getFabricUrl'));
        $authModel->expects($this->once())
            ->method('getFabricUrl')
            ->will($this->returnValue('test_host'));

        $helper = $this->mockHelper('xcom_xfabric', array('getAuthModel'));
        $helper->expects($this->any())
            ->method('getAuthModel')
            ->will($this->returnValue($authModel));

        $helperTransport = Mage::helper('xcom_xfabric')->getTransport();
        $this->assertEquals($helperTransport->getUrl(), 'test_host', "Transport Host is wrong");
    }

    public function testGetMessage()
    {
        $this->markTestIncomplete('Incomplete');
        $topic = 'marketplace/site/search';

        $message = $this->mockModel('xcom_chronicle/message_order_shipped_outbound');

        $helperMessage = Mage::helper('xcom_xfabric')
          ->getMessage($topic);

        $this->assertEquals($helperMessage, $message);
    }

    public function testGetInboundMessage()
    {
        $topic = 'marketplace/site/search';

        $message = Mage::getModel('catalog/product');

        $helper = $this->mockHelper('xcom_xfabric', array('getNodeByXpath'));
        $helper->expects($this->once())
            ->method('getNodeByXpath')
            ->with($this->equalTo(".//*[name='marketplace/site/search']/message"),
            $this->equalTo("/inbound"))
            ->will($this->returnValue(array('catalog/product')));

        $helperMessage = Mage::helper('xcom_xfabric')
            ->getMessage($topic, true);

        $this->assertEquals($helperMessage, $message);
    }


    /**
     * @expectedException Xcom_Xfabric_Exception
     */
    public function testGetMessageFailed()
    {
        $helperMessage = Mage::helper('xcom_xfabric')
          ->getMessage('topic/that/does/not/exist');
    }

    public function testSend()
    {
        $topic = 'marketplace/site/find';
        $url =  'https://api.x.com/fabric:443';

        $result = array(
            array(
                'name' => 'test_site',
                'id'   => 'test_id'
            )
        );

        $httpAdapter = $this->_getMock(
            'Varien_Http_Adapter_Curl',
            array('write', 'read', 'getInfo', 'getErrno', 'getError')
        );

        $httpAdapter->expects($this->once())
            ->method('getErrno')
            ->will($this->returnValue(0));

        $httpAdapter->expects($this->once())
            ->method('getInfo')
            ->will($this->returnValue(200));

        $headers = array(
            'Content-Type: application/json',
            'X-XC-SCHEMA-VERSION:1.0.1',
            'X-XC-SCHEMA-URI: http:api.x.com/ocl',
            'X-XC-RESULT-CORRELATION-ID: tetstes'
        );
        $body = json_encode($result);

        $uri = Zend_Uri_Http::fromString($url . '/' . $topic);

        $httpAdapter->expects($this->once())
            ->method('write')
            ->with(
                $this->equalTo(Zend_Http_Client::POST),
                $this->equalTo($uri),
                $this->equalTo('1.1'),
                $this->equalTo($headers),
                $this->equalTo($body)
            )
            ->will($this->returnValue(true));

        $httpAdapter->expects($this->once())
            ->method('read')
            ->will($this->returnValue('Everything is fine'));

        $transportClassName = Mage::getConfig()->getModelClassName('xcom_xfabric/transport_xfabric');
        $transport = $this->getMock($transportClassName, array('_getAdapter', 'getUrl', '_getHttpCode'), array(), '', FALSE);
        $transport->expects($this->once())
            ->method('_getAdapter')
            ->will($this->returnValue($httpAdapter));
        $transport->expects($this->once())
            ->method('getUrl')
            ->will($this->returnValue($url));
        $transport->expects($this->once())
            ->method('_getHttpCode')
            ->will($this->returnValue(200));

        $schema = $this->mockModel('xcom_xfabric/schema',
            array('getRawSchema'), FALSE);

        $message = $this->mockModel('xcom_xfabric/message_request',
            array('getTopic', 'getCurlHeaders', '_initSchema', 'getBody', 'getSchema', 'process'));
        $message->expects($this->any())
            ->method('getTopic')
            ->will($this->returnValue($topic));
        $message->expects($this->any())
            ->method('getCurlHeaders')
            ->will($this->returnValue($headers));
        $message->expects($this->any())
            ->method('getBody')
            ->will($this->returnValue($body));
        $message->expects($this->any())
            ->method('getSchema')
            ->will($this->returnValue($schema));

        $helper = $this->mockHelper('xcom_xfabric', array('getMessage', 'getTransport'));
        $helper->expects($this->once())
            ->method('getMessage')
            ->will($this->returnValue($message));

        $helper->expects($this->once())
            ->method('getTransport')
            ->will($this->returnValue($transport));

        Mage::helper('xcom_xfabric')
          ->send($topic, $result);
    }

    /**
     * @expectedException Xcom_Xfabric_Exception
     * @expectedExceptionMessage Unable to complete the request. Request is forbidden. You have big problems
     */
    public function testSendFails()
    {
        $topic = 'marketplace/site/find';
        $uri =  'https://api.x.com/fabric:443';
        $uri = Zend_Uri_Http::fromString($uri . '/' . $topic);

        $result = array(
            array(
                'name' => 'test_site',
                'id'   => 'test_id'
            )
        );

        $httpAdapter = $this->_getMock(
            'Varien_Http_Adapter_Curl',
            array('write', 'read', 'getInfo', 'getErrno', 'getError')
        );

        $httpAdapter->expects($this->once())
            ->method('getErrno')
            ->will($this->returnValue(1));

        $httpAdapter->expects($this->once())
            ->method('getError')
            ->will($this->returnValue('You have big problems'));

        $httpAdapter->expects($this->once())
            ->method('getInfo')
            ->will($this->returnValue(403));

        $body = json_encode($result);

        $httpAdapter->expects($this->once())
            ->method('read')
            ->will($this->returnValue('Something happened'));

        $transportClassName = Mage::getConfig()->getModelClassName('xcom_xfabric/transport_xfabric');
        $transport = $this->getMock($transportClassName, array('_getAdapter', 'getUrl'), array(), '', FALSE);
        $transport->expects($this->once())
            ->method('_getAdapter')
            ->will($this->returnValue($httpAdapter));
        $transport->expects($this->once())
            ->method('getUrl')
            ->will($this->returnValue($uri));

        $schema = $this->mockModel('xcom_xfabric/schema',
            array('getRawSchema'), false);

        $message = $this->mockModel('xcom_xfabric/message_request',
            array('getTopic', 'getCurlHeaders', '_initSchema', 'getBody', 'getSchema', 'process'));
        $message->expects($this->any())
            ->method('getSchema')
            ->will($this->returnValue($schema));

        $helper = $this->mockHelper('xcom_xfabric', array('getMessage', 'getTransport'));
        $helper->expects($this->once())
            ->method('getMessage')
            ->will($this->returnValue($message));

        $helper->expects($this->once())
            ->method('getTransport')
            ->will($this->returnValue($transport));


        Mage::helper('xcom_xfabric')
            ->send($topic, $result);
    }

    public function testMessageProcessDuringSend()
    {
        $this->markTestIncomplete('Incomplete');
        $topic = 'marketplace/site/search';
        $transport = $this->mockModel('xcom_xfabric/transport_xfabric', array('send'), FALSE);
        $transportClassName = Mage::getConfig()->getModelClassName('xcom_xfabric/transport_xfabric');
        $transport = $this->getMock($transportClassName, array('getUrl', '_getHttpCode', '_getHttpMessage',
            '_getHttpBody'), array(), '', FALSE);
        $transport->expects($this->once())
            ->method('getUrl')
            ->will($this->returnValue('https://api.x.com/fabric:443'));
        $transport->expects($this->once())
            ->method('_getHttpCode')
            ->will($this->returnValue(200));

        $helper = $this->mockHelper('xcom_xfabric', array('getTransport'));
        $helper->expects($this->once())
            ->method('getTransport')
            ->will($this->returnValue($transport));

        $message = $this->mockModel('xcom_chronicle/message_order_cancelled_outbound');

        $message->expects($this->once())
          ->method('process')
          ->will($this->returnValue($message));
        $helperMessage = Mage::helper('xcom_xfabric')
          ->send($topic);

    }


    public function testGetAuthorizationHeader()
    {
        $helper = Mage::helper('xcom_xfabric');
        $headers = $helper->getAuthorizationHeader();

        $this->assertInternalType(PHPUnit_Framework_Constraint_IsType::TYPE_ARRAY, $headers);
        $this->assertTrue(array_key_exists('Authorization', $headers));
    }

    /**
     * * @dataProvider provideGetEventTopicSuffix
     */
    public function testGetEventSuffix($topic, $suffix)
    {
        $suffixByTopic = $this->_object->getEventSuffix($topic);
        $this->assertEquals($suffix, $suffixByTopic);
    }

    public function provideGetEventTopicSuffix()
    {
        return array(
            array('/com.x.ordermanagement.v2/ProcessSalesChannelOrder/OrderCancelled',
                'com_x_ordermanagement_v2_processsaleschannelorder_ordercancelled'),
            array('marketplace/category/search', 'marketplace_category_search')
        );
    }
}
