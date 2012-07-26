<?php
class Xcom_Integration_TestCase extends PHPUnit_Framework_TestCase
{
    protected $_rollbackQueries = array();
    protected $_connection;
    protected $_integrationHttpEndpoint = '';

    public function __construct($name = NULL, array $data = array(), $dataName = '')
    {
        parent::__construct($name, $data, $dataName);
        $this->_integrationHttpEndpoint = Mage::getBaseUrl() . '/tests/integration/index.php';
        Mage::setAllowDispatchEvent(true);
    }

    public function setUp()
    {
        Mage::getConfig()->removeCache();
    }

    /**
     * @return Varien_Db_Adapter_Pdo_Mysql
     */
    public function getConnection()
    {
        if (empty($this->_connection)) {
            $this->_connection = Mage::getSingleton('core/resource')->getConnection('xcom_core_write');
        }

        return $this->_connection;
    }

    public function addRollbackQuery($query)
    {
        $this->_rollbackQueries[] = $query;
    }

    public function rollbackableQuery($writeQuery, $rollbackQuery)
    {
        $this->getConnection()->query($writeQuery);
        $this->addRollbackQuery($rollbackQuery);
    }

    public function getHttpEndpoint()
    {
        return $this->_integrationHttpEndpoint;
    }

    /**
     * @param $urn
     * @return Mage_HTTP_Client_Curl
     */
    public function makeHttpRequest($urn)
    {
        $curl = new Mage_HTTP_Client_Curl();
        $curl->get($this->getHttpEndpoint() . $urn);
        return $curl;
    }

    public function tearDown()
    {
        foreach ($this->_rollbackQueries as $rollbackQuery) {
            $this->getConnection()->query($rollbackQuery);
        }
    }
}
