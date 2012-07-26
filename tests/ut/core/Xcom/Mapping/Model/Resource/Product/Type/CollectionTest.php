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
class Xcom_Mapping_Model_Resource_Product_Type_CollectionTest extends Xcom_Collection_TestCase
{
    /**
     * @var Xcom_Mapping_Model_Resource_Product_Type_Collection
     */
    protected $_object;

    public function setUp()
    {
        parent::setUp();
        $this->_object = new Xcom_Mapping_Model_Resource_Product_Type_Collection();
    }

    public function tearDown()
    {
        $this->_object = null;
    }

    public function testConstruct()
    {
        $result = $this->_object->getModelName();
        $this->assertEquals('xcom_mapping/product_type', $result);
    }

    public function testInitSelect()
    {
        $expectedTables = array($this->_resource->getTableName('xcom_mapping/product_type',
            $this->_resource->getTableName('xcom_mapping/product_type_locale')));
        $tables = $this->_retrieveTables($this->_object->getSelect());
        $actualTables = array_values(array_intersect($tables, $expectedTables));
        $this->assertEquals($expectedTables, $actualTables);
    }
}
