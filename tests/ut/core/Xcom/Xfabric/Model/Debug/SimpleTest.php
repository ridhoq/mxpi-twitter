<?php
class Xcom_Xfabric_Model_Debug_SimpleTest extends Xcom_TestCase
{
    /**
     * @var Xcom_Xfabric_Model_Debug_Simple
     */
    protected $_object;

    public function setUp()
    {
        parent::setUp();
        $this->_object = $this->getMockBuilder('Xcom_Xfabric_Model_Debug_Simple')
                              ->setMethods(array('save'))
                              ->getMock();
         Mage::registerMockModel('xcom_xfabric/debug_simple', $this->_object);
    }

    public function testStart()
    {
        $saveResource = $this->mockResource('xcom_xfabric/debug_node')
            ->expects($this->exactly(4))
            ->method('insert')
            ->withAnyParameters();

        $this->_object->setId(1);
        $this->_object->start('method_1', 'topic_1', 'headers_1', 'body_1');

        $this->_object->setId(2);
        $this->_object->start('method_2', 'topic_2', 'headers_2', 'body_2');
    }

    public function testEnd()
    {
        $saveResource = $this->mockResource('xcom_xfabric/debug_node')
            ->expects($this->exactly(2))
            ->method('update')
            ->withAnyParameters();

        $this->_object->setId(1);
        $this->_object->start('method_1', 'topic_1', 'headers_1', 'body_1');

        $this->_object->stop('method_1', 'topic_1', 'headers_1', 'body_1');

    }

}