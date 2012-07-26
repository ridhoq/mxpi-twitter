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

class Xcom_Xfabric_Model_Message_RequestTest extends Xcom_TestCase
{
    /** @var Xcom_Xfabric_Model_Message_Request */
    protected $_object;
    protected $_objectFactoryName = 'xcom_xfabric/message_request';
    protected $_instanceOf = 'Xcom_Xfabric_Model_Message_Request';
    protected $_avroJsonConstant = Xcom_Xfabric_Model_Message_Abstract::AVRO_JSON;

    public function setUp()
    {
        $this->_object = new Xcom_Xfabric_Model_Message_Request();
    }

    public function tearDown()
    {
        $this->_object = null;
    }

    public function testAddCorrelationIdHeaderInSetIsWaitResponse()
    {
        $this->_object->resetHeaders();
        $this->_object->setIsWaitResponse();
        $headers = $this->_object->getHeaders();
        $this->assertContains($this->_object->getCorrelationId(), $headers);
    }

    public function testSetMessageData()
    {
        $messageData = array('test' => 'test_value');
        $this->assertInstanceOf($this->_instanceOf, $this->_object->setMessageData($messageData));
    }

    public function testGetMessageData()
    {
        $this->assertEquals(array(), $this->_object->getMessageData());
    }

    public function testGetMessageDataWithKey()
    {
        $this->assertEquals(null, $this->_object->getMessageData('test'));
    }

    public function testGetMessageDataWithExistedKey()
    {
        $messageData = array('test' => 'test_value');
        $this->_object->setMessageData($messageData);
        $this->assertEquals('test_value', $this->_object->getMessageData('test'));
    }

    public function testGetMessageDataArray()
    {
        $messageData = array('test' => 'test_value');
        $this->_object->setMessageData($messageData);
        $this->assertArrayHasKey('test', $this->_object->getMessageData());
    }

    /**
     * @param  $options
     * @dataProvider processOptionsProvider
     */
    public function testProcess($options)
    {
        $this->mockStoreConfig('xfabric/connection_settings/encoding', $this->_avroJsonConstant);

        $methods = array('prepareHeaders', '_initSchema');
        $mockObject = $this->_getMock('Xcom_Xfabric_Model_Message_Request', $methods);
        $mockObject->expects($this->once())
            ->method('prepareHeaders')
            ->will($this->returnValue($mockObject));
        $mockObject->expects($this->once())
            ->method('_initSchema')
            ->will($this->returnValue($mockObject));

        $mockObject->setMessageData(array(
            'test_item_1' => 'test_value_1',
            'test_item_2' => 'test_value_2',
        ));

        $result = $mockObject->process($options);
        $this->assertInstanceOf($this->_instanceOf, $result);
        $this->assertEquals('{"test_item_1":"test_value_1","test_item_2":"test_value_2"}', $result->getBody());
    }

    public function processOptionsProvider()
    {
        return array(
            array(null),
            array(new Varien_Object())
        );
    }

    protected function _mockResourceForSave($factoryName)
    {
        $resourceMethods = array('beginTransaction', 'save', 'addCommitCallback', 'rollBack');
        $resourceMock = $this->mockResource($factoryName, $resourceMethods);
        foreach ($resourceMethods as $methodName) {
            $resourceMock->expects($this->any())
            ->method($methodName)
            ->will($this->returnValue($resourceMock));
        }
        return $resourceMock;
    }

    protected function _mockResourceModelInObject($modelFactoryName)
    {
        $resourceMock = $this->_mockResourceForSave('xcom_xfabric/message_request');
        $objectMock = $this->mockModel($modelFactoryName, array('_getResource'));
        $objectMock->expects($this->any())
            ->method('_getResource')
            ->will($this->returnValue($resourceMock));

        return $objectMock;
    }

    public function testPrepareHeaders()
    {
        $headersFixture = array(
            array(true, array('header1'=>'test')),
            array(false, array('header2'=>'test'))
        );

        $this->_prepareHelperMock($headersFixture);

        $request = $this->mockModel('xcom_xfabric/message_request', array('isOnBehalfOfTenant'));
        $request->expects($this->at(0))
            ->method('isOnBehalfOfTenant')
            ->will($this->returnValue(true));

        $request->expects($this->at(1))
            ->method('isOnBehalfOfTenant')
            ->will($this->returnValue(false));

        $request->prepareHeaders();
        $headers = $request->getHeaders();
        $this->assertTrue(isset($headers['header1']));

        $request->prepareHeaders();
        $headers = $request->getHeaders();
        $this->assertTrue(isset($headers['header2']));
    }

    /**
     * @param $headersFixture
     * @return PHPUnit_Framework_MockObject_MockObject
     */
    protected function _prepareHelperMock($headersFixture)
    {
        $helperMock = $this->mockHelper('xcom_xfabric', array('getAuthorizationHeader'));
        $helperMock->expects($this->at(0))
            ->method('getAuthorizationHeader')
            ->will($this->returnValue($headersFixture[0][1]));

        $helperMock->expects($this->at(1))
            ->method('getAuthorizationHeader')
            ->will($this->returnValue($headersFixture[1][1]));

        return $helperMock;
    }

    /**
     * @dataProvider encodingProvider
     */
    public function testPrepareHeadersWithJsonHeader($encoding, $header)
    {
        $this->_object->setEncoding($encoding);
        $this->assertInstanceOf($this->_instanceOf, $this->_object->prepareHeaders());
        $headers = $this->_object->getHeaders();

        $this->assertEquals($header, "Content-Type: " . $headers['Content-Type']);
    }

    public function encodingProvider()
    {
        return array(
            array(Xcom_Xfabric_Model_Message_Abstract::AVRO_JSON, 'Content-Type: application/json'),
            array(Xcom_Xfabric_Model_Message_Abstract::AVRO_BINARY, 'Content-Type: avro/binary')
        );
    }

}
