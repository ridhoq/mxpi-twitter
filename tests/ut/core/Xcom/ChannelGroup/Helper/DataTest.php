<?php
/**
 * Test of default helper of module Channel Group
 */
class Xcom_ChannelGroup_Helper_DataTest extends Xcom_TestCase
{
    /**
     * Test object
     *
     * @var Xcom_ChannelGroup_Helper_Data
     */
    protected $_object;

    /**
     * App website, store collections
     *
     * @var array
     */
    protected $_collections = array();

    /**
     * Set test object
     *
     * @return void
     */
    public function setUp()
    {
        $this->_checkConnection = true;
        parent::setUp();
        $this->_object = new Xcom_ChannelGroup_Helper_Data;
    }

    /**
     * Test getting first store in ordered stores
     *
     * @return void
     */
    public function testGetFirstStoreId()
    {
        $data = $this->_getFixtureData();
        foreach ($data['stores'] as &$item) {
            $dataItem = $item;
            $item = $this->getMock('Varien_Object', array('initConfigCache'));
            $item->setData($dataItem);
            $item->expects($this->any())
                    ->method('initConfigCache')
                    ->will($this->returnValue($item));
        }
        unset($item);

        $list = array(
            'websites' => 'core/website',
            'groups'   => 'core/store_group',
            'stores'   => 'core/store',
        );

        $callback = array(
            'websites' => 'getWebsiteById',
            'groups'   => 'getStoreGroupById',
            'stores'   => 'getStoreById',
        );
        foreach ($list as $key => $model) {
            $collection = $this->mockCollection(
                $model,
                $data[$key],
                array('initCache', 'count', 'load', 'getItemById')
            );
            $collection->expects($this->any())
                ->method('initCache')
                ->will($this->returnValue($collection));
            $collection->expects($this->any())
                ->method('count')
                ->will($this->returnValue(count($data[$key])));
            $collection->expects($this->any())
                ->method('load')
                ->will($this->returnValue($collection));
            $collection->expects($this->any())
                ->method('getItemById')
                ->will($this->returnCallback(array($this, $callback[$key])));
            $this->_collections[$key] = $collection;
        }

        Mage::app()->reinitStores();
        $result = 100;
        $this->assertEquals($result, $this->_object->getFirstStoreId());

        //TODO remove reinit store and DB usage
        Mage::clearRegistry();
        Mage::app()->reinitStores();
    }

    /**
     * Get fixture data
     *
     * @return mixed
     */
    protected function _getFixtureData()
    {
        return require dirname(__FILE__) . '/_fixtures/storesStructure.php';
    }

    /**
     * Get website by id
     *
     * @param int $id
     * @return Varien_Object|null
     */
    public function getWebsiteById($id)
    {
        /** @var $collection Mage_Core_Model_Mysql4_Website_Collection */
        $collection = $this->_collections['websites'];
        $items = $collection->getItems();
        if (isset($items[$id])) {
            return $items[$id];
        }
        return null;
    }

    /**
     * Get store group by id
     *
     * @param int $id
     * @return Varien_Object|null
     */
    public function getStoreGroupById($id)
    {
        /** @var $collection Mage_Core_Model_Mysql4_Store_Group_Collection */
        $collection = $this->_collections['groups'];
        $items = $collection->getItems();
        if (isset($items[$id])) {
            return $items[$id];
        }
        return null;
    }

    /**
     * Get store by id
     *
     * @param int $id
     * @return Varien_Object|null
     */
    public function getStoreById($id)
    {
        /** @var $collection Mage_Core_Model_Mysql4_Store_Collection */
        $collection = $this->_collections['stores'];
        $items = $collection->getItems();
        if (isset($items[$id])) {
            return $items[$id];
        }
        return null;
    }
}
