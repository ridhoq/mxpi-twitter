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
 * @package     Xcom_Mapping
 * @copyright   Copyright (c) 2010 Magento Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class Xcom_Mapping_Model_Resource_Attribute_CollectionTest extends Xcom_Collection_TestCase
{
    /**
     * @var Xcom_Mapping_Model_Resource_Product_Type_Collection
     */
    protected $_object;

    public function setUp()
    {
        parent::setUp();
        $this->_object = new Xcom_Mapping_Model_Resource_Attribute_Collection();
    }

    public function tearDown()
    {
        $this->_object = null;
    }

    public function testConstruct()
    {
        $result = $this->_object->getModelName();
        $this->assertEquals('xcom_mapping/attribute', $result);
    }

    public function testGetAttributeRelations()
    {
        $this->_object->initAttributeRelations();
        $expectedTables = array($this->_resource->getTableName('xcom_mapping/attribute',
            $this->_resource->getTableName('eav/attribute'),
            $this->_resource->getTableName('xcom_mapping/attribute_locale'),
            $this->_resource->getTableName('xcom_mapping/attribute_relation'),
            $this->_resource->getTableName('xcom_mapping/product_type_relation')
        ));
        $tables = $this->_retrieveTables($this->_object->getSelect());
        $actualTables = array_values(array_intersect($tables, $expectedTables));
        $this->assertEquals($expectedTables, $actualTables);

        $expectedColumns = array('attribute_set_id', 'mapping_product_type_id', 'attribute_id', 'attribute_name',
            'mapping_attribute_id', 'mapping_attribute_name');
        $columns = $this->_retrieveColumns($this->_object->getSelect());
        $actualColumns = array_values(array_intersect($columns, $expectedColumns));
        $this->assertEquals($expectedColumns, $actualColumns);
    }
}
