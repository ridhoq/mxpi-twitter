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
 * @package     Xcom_Ebay
 * @copyright   Copyright (c) 2010 Magento Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class Xcom_Listing_Model_Resource_Product_CollectionTest extends Xcom_Collection_TestCase
{
    public function setUp()
    {
        parent::setUp();
        /** @var $this->_object Xcom_Listing_Model_Resource_Product_Collection */
        $this->_object      = new Xcom_Listing_Model_Resource_Product_Collection();
    }

    protected function _getParentSelect()
    {
        $collection = new Mage_Catalog_Model_Resource_Eav_Mysql4_Product_Collection();
        return $collection->getSelect();
    }

    public function testJoinStatusColumn()
    {
        $expectedColumns = array('listing','channels','maxtimestamp');
        $this->_object->joinStatusColumn('test_code');
        $columns = $this->_retrieveColumns($this->_object->getSelect());
        $actualColumns = array_values(array_intersect($columns, $expectedColumns));
        $this->assertEquals($expectedColumns, $actualColumns);
    }

    public function testJoinProductQty()
    {
        $this->_object->joinProductQty();
        $expectedTables = array($this->_resource->getTableName('cataloginventory/stock_item'));
        $tables = $this->_retrieveTables($this->_object->getSelect());
        $actualTables = array_values(array_intersect($tables, $expectedTables));
        $this->assertEquals($expectedTables, $actualTables);
    }

    public function testJoinChannelData()
    {
        $this->_object->joinChannelData(0);
        $expectedColumns = array('channel_listing_status');
        $columns = $this->_retrieveColumns($this->_object->getSelect());
        $actualColumns = array_values(array_intersect($columns, $expectedColumns));
        $this->assertEquals($expectedColumns, $actualColumns);
    }

    public function testPrepareMaxTimestampFilter()
    {
        $maxTimestamp = 'MAX('
            . ' CASE '
                . ' WHEN ccp.channel_product_id IS NOT NULL THEN ccp.created_at'
                . ' ELSE NULL'
            . ' END)';
        $condition = array('from' => '20010101', 'to' => '20010101');
        $this->_object->prepareMaxTimestampFilter($condition);
        $expectedHaving = array('(' . $maxTimestamp . ' >= :from_date)', 'AND (' . $maxTimestamp . ' <= :to_date)');
        $having = $this->_retrieveHaving($this->_object->getSelect());
        $actualHaving = array_values(array_intersect($having, $expectedHaving));
        $this->assertEquals($expectedHaving, $actualHaving);
    }
}
