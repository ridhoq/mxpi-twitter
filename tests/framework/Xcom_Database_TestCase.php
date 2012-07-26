<?php
/**
 * Base class for integration tests
 */
class Xcom_Database_TestCase extends Xcom_TestCase_Abstract
{
    /** @var $_conn Varien_Db_Adapter_Pdo_Mysql */
    protected $_conn;

    /** @var $_resource Mage_Core_Model_Resource */
    protected $_resource;

    /**
     * Method setUp start status
     *
     * @var bool
     */
    static protected $_setUp = false;

    /**
     * Set up connections
     *
     * @throws PHPUnit_Framework_Exception
     * @return void
     */
    public function setUp()
    {
        if (self::$_setUp) {
            throw new PHPUnit_Framework_Exception(get_class($this) . '::tearDown() not called in a previous test.');
        }
        self::$_setUp = true;

        $this->_resource = Mage::getSingleton('core/resource');
        $this->_conn = $this->_resource->getConnection('xcom_core_write');
        $this->_conn->beginTransaction();
        $this->_conn->query('SET FOREIGN_KEY_CHECKS = 0;');
    }

    public function tearDown()
    {
        if (isset($this->_conn)) {
            $this->_conn->query('SET FOREIGN_KEY_CHECKS = 1;');
            $this->_conn->rollback();
            $this->_conn = null;
        }

        if (!self::$_setUp) {
            throw new PHPUnit_Framework_Exception(get_class($this) . '::setUp() not called before start test.');
        }
        self::$_setUp = false;
    }

    public function getConnection()
    {
        return $this->_conn;
    }

    protected function _fillTables($data)
    {
        foreach ($data as $table=>$values) {
            $tableName = $this->_resource->getTableName($table);
            $this->_conn->delete($tableName);
            $this->_conn->insertOnDuplicate($tableName, $values);
        }
    }

    protected function _getRow($tableName, $columns, $condition)
    {
        $query = $this->_conn->select()
            ->from($tableName, $columns)
            ->where($condition);
        return $this->_conn->fetchRow($query);
    }
}
