<?php

/**
 * Test class for Xcom_Xfabric_Model_Debug
 */
class Xcom_Xfabric_Model_DebugTest extends PHPUnit_Framework_TestCase
{
    /**
     * @var Xcom_Xfabric_Model_Debug
     */
    protected $object;

    /**
     * Sets up the fixture, for example, opens a network connection.
     * This method is called before a test is executed.
     */
    protected function setUp()
    {
        $this->object = new Xcom_Xfabric_Model_Debug;
    }

    /**
     * Tears down the fixture, for example, closes a network connection.
     * This method is called after a test is executed.
     */
    protected function tearDown()
    {
    }

    /**
     * @todo Implement test_construct().
     */
    public function test_construct()
    {
    }

    /**
     * @dataProvider startEndProvider
     */
    public function testStart($method, $topic, $headers, $body)
    {
    	$result = $this->object->start($method, $topic, $headers, $body);
        $this->assertEquals($this->object, $result);
        $this->offsetExists();
        $this->offsetGet();

	}
	
    public function startEndProvider()
    {
    	return array(
    		array('startmethod1', 'Topic1', 'header1', 'body1'),
    		array('startmethod2', 'Topic2', 'header2', 'body2'),
    		array('startmethod3', 'Topic3', 'header3', 'body3'),
    		array('startmethod4', 'Topic4', 'header4', 'body4'),
    		array('startmethod5', 'Topic5', 'header5', 'body5'),
    	);
    	
    }
    
    /**
     * @dataProvider startEndProvider
     */
    public function testStop($method, $topic, $headers, $body)
    {
    	$result = $this->object->stop($method, $topic, $headers, $body);
        $this->assertEquals($this->object, $result);
    }
	
    public function stopEndProvider()
    {
    	return array(
    		array('endmethod1', 'Topic1', 'header1', 'body1'),
    		array('endmethod2', 'Topic2', 'header2', 'body2'),
    		array('endmethod3', 'Topic3', 'header3', 'body3'),
    		array('endmethod4', 'Topic4', 'header4', 'body4'),
    		array('endmethod5', 'Topic5', 'header5', 'body5'),
    	);
    	
    }

    /**
     * @dataProvider startEndProvider
     * @depends testStop
     */
    public function testStartEndRollback()
    {    	
    	/** @var $resource Mage_Core_Model_Resource */
    	$resource = Mage::getSingleton('core/resource');

    	/** @var $connection Varien_Db_Adapter_Pdo_Mysql */
    	$connection = $resource->getConnection('core_setup');
    	
    	if($connection instanceof Varien_Db_Adapter_Pdo_Mysql){
    		foreach ($this->startEndProvider() as $item){
    			$connection->delete($resource->getTableName('xcom_xfabric/debug'), array("name LIKE '%{$item[0]}%'" ));
    			$connection->delete($resource->getTableName('xcom_xfabric/debug_node'), array("topic LIKE '%{$item[1]}%'" ));
    		}
    	}
	}
    
    /**
     * Testing getRootNode()
     */
    public function testGetRootNode()
    {
    	$rootNode = $this->object->getRootNode();
    	$this->assertInstanceOf('Xcom_Xfabric_Model_Debug_Node', $rootNode);
    }

    /**
     * Testing testGetPointer().
     */
    public function testGetPointer()
    {
    	$stackPointer = $this->object->getPointer();
    	$this->assertInternalType('int', $stackPointer);
    }

    /**
     * @dataProvider getNodeByPointerProvider()
     */
    public function testGetNodeByPointer($testVar)
    {
    	$haystack = $this->object->getNodeByPointer($testVar);
    	if(is_object($haystack)){
    		$this->assertInstanceOf('Xcom_Xfabric_Model_Debug_Node', $haystack);
    	} else {
    		$this->assertInternalType('null', $haystack);
    	}
    }

    /**
     * Provider test method 
     */
    public function getNodeByPointerProvider()
    {    	
    	return array(
    		array(1),
    		array(2),
    		array(3),
    		array(4),
    		array(5.0),
    		array('string'),
    		array(new stdClass()),
    	);
    }
    
    /**
     * Validate ArrayAccess offsetExists() method
     */
    public function offsetExists()
    {
        $this->assertTrue($this->object->offsetExists(0));
    }

    /**
     * @expectedException Mage_Core_Exception
     * @return void
     */
    public function testOffsetUnset()
    {
        $this->object->offsetUnset(0);
    }

    public function offsetGet()
    {
        $this->assertInstanceOf('Xcom_Xfabric_Model_Debug_Node',$this->object->offsetGet(0));
    }

    /**
     * @expectedException Mage_Core_Exception
     * @return void
     */
    public function testOffsetSet()
    {
        $this->object->offsetSet('testoffset', 'testvalue');
    }
}
?>
