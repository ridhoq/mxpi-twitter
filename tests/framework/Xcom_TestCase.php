<?php
/**
 * Base test case for XCom
 */
class Xcom_TestCase extends Xcom_TestCase_Abstract
{
    /**
     * Tested object
     *
     * @var Mage_Catalog_Model_Abstract
     */
    protected $_object;

    /**
     * Check db connection in test
     *
     * @var bool
     */
    protected $_checkConnection = false;

    /**
     * Clean up Magento registry. Set default object by name
     *
     * @return void
     */
    public function setUp()
    {
        /**
         * Clear all registry for empty all data
         */
        Mage::clearRegistry();

        /**
         * Mock connection to DB for catch it and throw exception
         */
        if ($this->_checkConnection) {
            $resource = $this->mockModel('core/resource', array('getConnectionTypeInstance'));
            $resource->expects($this->any())
                ->method('getConnectionTypeInstance')
                ->will($this->returnValue(new Db_Adapter_Mock_Type_Db));
            Mage::register('_singleton/core/resource', $resource);
            Mage::register('unit_test', true);
        }
        parent::setUp();
    }

    /**
     * Clear registry on end test
     *
     * @return void
     */
    public function tearDown()
    {
        /**
         * Clear all registry with mock data
         */
        Mage::clearRegistry();
        parent::tearDown();
    }

    /**
     * Mock Magento model
     *
     * @param string $factoryString     Class name or class path
     * @param array $methods
     * @return PHPUnit_Framework_MockObject_MockObject
     * @throws PHPUnit_Framework_Exception
     */
    public function mockModel($factoryString, $methods = array(), $callOriginalConstructor = TRUE, $params = array())
    {
        $class = Mage::getConfig()->getModelClassName($factoryString);
        if (!$class) {
            throw new PHPUnit_Framework_Exception(
                sprintf('Model "%s" not found.', $factoryString));
        }
        $modelMock = $this->getMock($class, $methods, $params, '',  $callOriginalConstructor);

        Mage::registerMockModel($factoryString, $modelMock);
        return $modelMock;
    }

    /**
     * Mock Magento helper model
     *
     * @param string $factoryString
     * @param array $methods
     * @return PHPUnit_Framework_MockObject_MockObject
     * @throws PHPUnit_Framework_Exception
     */
    public function mockHelper($factoryString, $methods = array())
    {
        $model = Mage::helper($factoryString);
        if (!$model) {
            throw new PHPUnit_Framework_Exception(
                sprintf('Helper "%s" not found.', $factoryString));
        }
        $class = get_class($model);
        $modelMock = $this->_getMock($class, $methods);

        Mage::registerMockHelper($factoryString, $modelMock);
        return $modelMock;
    }

    /**
     * Mock resource model
     *
     * @param string $factoryString
     * @param array $methods
     * @return PHPUnit_Framework_MockObject_MockObject
     * @throws PHPUnit_Framework_Exception
     */
    public function mockResource($factoryString, $methods = array())
    {
        $model = Mage::getResourceModel($factoryString);
        if (!$model) {
            throw new PHPUnit_Framework_Exception(
                sprintf('Resource model "%s" not found.', $factoryString));
        }
        $class = get_class($model);
        $modelMock = $this->_getMock($class, $methods);

        Mage::registerMockResourceModel($factoryString, $modelMock);
        return $modelMock;
    }

    /**
     * Mock collection resource model
     *
     * @param string $factoryString
     * @param ArrayIterator|array|null $items
     * @param array $methods
     * @return PHPUnit_Framework_MockObject_MockObject
     * @throws PHPUnit_Framework_Exception
     */
    public function mockCollection($factoryString, $items = null, $methods = array())
    {
        //$model = Mage::getModel($factoryString)->getCollection();
        $model = Mage::getResourceModel($factoryString . '_collection');
        if (!$model) {
            throw new PHPUnit_Framework_Exception(
                sprintf('Resource collection model "%s" not found.', $factoryString));
        }

        $class = get_class($model);
        $collectionMockBuilder = $this->getMockBuilder($class)
            ->disableOriginalConstructor();
        if ($methods) {
            if ($items) {
                foreach (array('getIterator', 'getItems') as $method) {
                    if (false === array_search($method, $methods)) {
                        $methods[] = $method;
                    }
                }
            }
            $collectionMockBuilder->setMethods($methods);
        }
        $collection = $collectionMockBuilder->getMock();

        if ($items) {
            $collectionIterator = new Varien_Data_Collection();
            foreach ($items as &$item) {
                if (!$item instanceof Varien_Object) {
                    $item = new Varien_Object($item);
                }
                $collectionIterator->addItem($item);
            }
            $collection->expects($this->any())
              ->method('getIterator')
              ->will($this->returnValue($collectionIterator));

            $collection->expects($this->any())
              ->method('getItems')
              ->will($this->returnValue($items));
        }

        Mage::registerMockResourceModel($factoryString . '_collection', $collection);
        return $collection;
    }

    /**
     * Mock store config
     *
     * @param string $node
     * @param string|int|bool $value
     * @return void
     */
    public function mockStoreConfig($node, $value)
    {
        Mage::registerStoreConfigMock($node, $value);
    }

    /**
     * Get mock object
     *
     * @param string $class
     * @param array $methods
     * @return PHPUnit_Framework_MockObject_MockObject
     */
    protected function _getMock($class, $methods = array())
    {
        if ($methods)
            return $this->getMock($class, $methods);
        return $this->getMock($class);
    }
}

/**
 * Unit test DB adapter mock
 */
class Db_Adapter_Mock extends Varien_Db_Adapter_Pdo_Mysql
{
    /**
     * Rewrite query method for protect running sql queries
     *
     * @param mixed|string|\Zend_Db_Select $sql
     * @param array|mixed $bind
     * @return void|Zend_Db_Pdo_Statement|Zend_Db_Statement_Interface|Zend_Db_Statement_Pdo
     * @throws Exception
     */
    public function query($sql, $bind = array())
    {
        throw new Exception('Unit Test cannot perform SQL queries in "ut" area');
    }

}

/**
 * Mock DB type model
 */
class Db_Adapter_Mock_Type_Db extends Mage_Core_Model_Resource_Type_Db_Pdo_Mysql
{
    /**
     * Retrieve DB adapter class name
     *
     * @return string
     */
    protected function _getDbAdapterClassName()
    {
        return 'Db_Adapter_Mock';
    }

    /**
     * Enter description here...
     *
     * @param array $config Connection config
     * @return Varien_Db_Adapter_Pdo_Mysql
     */
    public function getConnection($config)
    {
        $configArr = (array) $config;
        return $this->_getDbAdapterInstance($configArr);
    }

}
