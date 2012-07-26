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

class Xcom_Xfabric_Model_Message_ResponseTest extends Xcom_TestCase
{
    /** @var Xcom_Xfabric_Model_Message_Response */
    protected $_object;
    protected $_objectFactoryName = 'xcom_xfabric/message_response';
    protected $_instanceOf = 'Xcom_Xfabric_Model_Message_Response';

    public function setUp()
    {
        parent::setUp();
        $this->_object = new Xcom_Xfabric_Model_Message_Response();
    }

    public function tearDown()
    {
        $this->_object = null;
    }

    public function testInstanceOf()
    {
        $this->assertInstanceOf($this->_instanceOf, $this->_object);
    }

    public function testGetResponseData()
    {
        $body = array('test' => 'test_value');
        $this->_object->setBody($body);
        $responseData = $this->_object->getResponseData();
        $this->assertArrayHasKey('test', $responseData);
    }

    public function testGetCorrelationId()
    {
        $correlationId = $this->_object->getCorrelationId();
        $this->assertNull($correlationId);

        $this->_object->setHeaders(array('X-XC-RESULT-CORRELATION-ID' => 'test'));
        $correlationId = $this->_object->getCorrelationId();
        $this->assertEquals('test', $correlationId);
    }

    public function testGetCorrelationIdInbound()
    {
        $this->_object->resetHeaders();
        $this->_object->setHeaders(array('x-xc-result-correlation-id' => 'test2'));

        $correlationId = $this->_object->getCorrelationId();
        $this->assertEquals('test2', $correlationId);
    }

    public function testSaveEditObject()
    {
        $objectMock = $this->_mockResourceModelInObject($this->_objectFactoryName);
        $objectMock->setTopic('topic/name');
        $objectMock->addHeader('header');

        $objectMock->setDataChanges(true);
        $objectMock->isObjectNew(false);
        $objectMock->setId(1);
        $objectMock->save();

        $data = $objectMock->getData();

        $this->assertArrayHasKey('topic', $data);
        $this->assertArrayHasKey('headers', $data);
        $this->assertArrayHasKey('updated_at', $data);
        $this->assertArrayNotHasKey('created_at', $data);
    }

    public function testSaveNewObject()
    {
        $objectMock = $this->_mockResourceModelInObject($this->_objectFactoryName);
        $objectMock->setTopic('topic/name');
        $objectMock->addHeader('header');

        $objectMock->setDataChanges(true);
        $objectMock->isObjectNew(true);
        $objectMock->save();

        $data = $objectMock->getData();

        $this->assertArrayHasKey('topic', $data);
        $this->assertArrayHasKey('headers', $data);
        $this->assertArrayNotHasKey('updated_at', $data);
        $this->assertArrayHasKey('created_at', $data);
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
        $resourceMock = $this->_mockResourceForSave('xcom_xfabric/message_response');
        $objectMock = $this->mockModel($modelFactoryName, array('_getResource'));
        $objectMock->expects($this->any())
            ->method('_getResource')
            ->will($this->returnValue($resourceMock));

        return $objectMock;
    }
}
