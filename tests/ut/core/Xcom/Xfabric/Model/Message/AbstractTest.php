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

class Xcom_Xfabric_Model_Message_AbstractTest extends Xcom_TestCase
{
    /** @var Xcom_Xfabric_Model_Message_New_Abstract */
    protected $_object;
    protected $_instanceOf = 'Xcom_Xfabric_Model_Message_Abstract';
    protected $_encodingConfigNode = 'xfabric/connection_settings/encoding';
    protected $_binaryConstant = Xcom_Xfabric_Model_Message_Abstract::AVRO_BINARY;
    protected $_jsonConstant = Xcom_Xfabric_Model_Message_Abstract::AVRO_JSON;

    public function setUp()
    {
        parent::setUp();
        $this->_object = new TestConcreteMessageClass();

    }
    public function tearDown()
    {
        $this->_object = null;
    }

    public function testInterface()
    {
        $this->assertInstanceOf($this->_instanceOf, $this->_object);
    }

    public function testMessageEncodingConstant()
    {
        $this->assertEquals('binary', $this->_binaryConstant);
        $this->assertEquals('json', $this->_jsonConstant);
    }

    public function testGetBody()
    {
        $this->assertEquals(null, $this->_object->getBody());
    }

    public function testSetBody()
    {
        $body = 'body';
        $this->assertInstanceOf($this->_instanceOf, $this->_object->setBody($body));
    }

    public function testGetTopic()
    {
        $this->assertEquals(null, $this->_object->getTopic());
    }

    public function testGetSchemaMessageName()
    {
        $this->assertEquals(null, $this->_object->getSchemaRecordName());
    }

    public function testSetSchemaRecordName()
    {
        $recordName = 'test';
        $this->assertInstanceOf($this->_instanceOf, $this->_object->setSchemaRecordName($recordName));
        $this->assertEquals($recordName, $this->_object->getSchemaRecordName());
    }

    public function testGetHeaders()
    {
        $this->assertEquals(array(), $this->_object->getHeaders());
    }

    public function testAddHeader()
    {
        $header = 'header';
        $this->assertInstanceOf($this->_instanceOf, $this->_object->addHeader($header));
    }

    public function testSetHeaders()
    {
        $headers = array(
            'header1_name' => 'header1_value',
            'header2_name' => 'header2_value',
        );
        $this->assertInstanceOf($this->_instanceOf, $this->_object->setHeaders($headers));
        $this->assertEquals(2, count($this->_object->getHeaders()));
    }

    public function testAddGetHeaders()
    {
        $this->_object->addHeader('Content-Type', 'header_value');
        $headers = $this->_object->getHeaders();

        $this->assertEquals(1, count($headers));
        $this->assertTrue(isset($headers['Content-Type']));
        $this->assertEquals('header_value', $headers['Content-Type']);
    }


    public function testGetSchema()
    {
        $schemaUri = 'http://test.loc/message';
        $schemaVersion = '1.0.0';
        $schemaRecordName = 'RecordName';
        $this->_object->setSchemaUri($schemaUri);
        $this->_object->setSchemaVersion($schemaVersion);
        $this->_object->setSchemaRecordName($schemaRecordName);

        $class = Mage::getConfig()->getModelClassName('xcom_xfabric/schema');
        $options = array(
            'schema_uri' => $schemaUri
        );
        $modelMock = $this->getMock($class, array('_loadProtocol'), array($options));
        Mage::registerMockModel('xcom_xfabric/schema', $modelMock);

        $this->assertEquals($schemaUri, $this->_object->getSchema()->getSchemaUri());
    }

    public function testGetSchemaFile()
    {
        $this->assertEquals(null, $this->_object->getSchemaFile());
    }

    public function testSetSchemaFile()
    {
        $file = 'test_file';
        $this->assertInstanceOf($this->_instanceOf, $this->_object->setSchemaFile($file));
        $this->assertEquals($file, $this->_object->getSchemaFile());
    }

    public function testSetTopic()
    {
        $name = 'name';
        $this->assertInstanceOf($this->_instanceOf, $this->_object->setTopic($name));
        $this->assertEquals($name, $this->_object->getTopic());
    }

    public function testIsEncodingAllowed()
    {
        $encodingJson = $this->_jsonConstant;
        $this->assertTrue($this->_object->isEncodingAllowed($encodingJson));

        $encodingBinary = $this->_binaryConstant;
        $this->assertTrue($this->_object->isEncodingAllowed($encodingBinary));

        $wrongEncoding = 'test_encoding';
        $this->assertFalse($this->_object->isEncodingAllowed($wrongEncoding));
    }

    /**
     * @expectedException Xcom_Xfabric_Exception
     */
    public function testSetEncodingCustomFalse()
    {
        $encoding = 'wrong_encoding';
        $this->_object->setEncoding($encoding);
    }

    public function testSetEncodingEmpty()
    {
        $this->mockStoreConfig($this->_encodingConfigNode, $this->_jsonConstant);
        $this->assertInstanceOf($this->_instanceOf, $this->_object->setEncoding());
    }

    public function testSetEncodingWithParameter()
    {
        $this->assertInstanceOf($this->_instanceOf, $this->_object->setEncoding($this->_binaryConstant));
    }

    public function testSetEncoder()
    {
        $this->assertInstanceOf($this->_instanceOf, $this->_object->setEncoder());
    }

    public function testSetEncoderUsingEncoding()
    {
        $encoderMock = $this->mockModel('xcom_xfabric/encoder_json');
        $this->_object->setEncoder($this->_jsonConstant);
        $this->assertInstanceOf(get_class($encoderMock), $this->_object->getEncoder());
    }

    public function testGetEncoderCase1()
    {
        $this->mockStoreConfig($this->_encodingConfigNode, $this->_binaryConstant);
        $mockModel = $this->mockModel('xcom_xfabric/encoder_avro');
        $this->assertInstanceOf(get_class($mockModel), $this->_object->getEncoder());
    }

    public function testGetEncoderCase2()
    {
        $this->mockStoreConfig($this->_encodingConfigNode, $this->_jsonConstant);
        $mockModel = $this->mockModel('xcom_xfabric/encoder_json');
        $this->assertInstanceOf(get_class($mockModel), $this->_object->getEncoder());
        $this->mockStoreConfig($this->_encodingConfigNode, $this->_binaryConstant);

        $this->assertInstanceOf(get_class($mockModel), $this->_object->getEncoder());
    }

    public function testResetHeaders()
    {
        $this->_object->addHeader('headerName', 'headerValue');
        $headers = $this->_object->getHeaders();
        $this->assertEquals(1, count($headers));

        $this->_object->resetHeaders();
        $resetHeaders = $this->_object->getHeaders();
        $this->assertEquals(0, count($resetHeaders));
    }
}

class TestConcreteMessageClass extends Xcom_Xfabric_Model_Message_Abstract
{
    public function process()
    {
    }

    public function getCorrelationId()
    {
    }
}
