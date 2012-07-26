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
class Xcom_Mapping_Model_Resource_Relation extends Mage_Core_Model_Mysql4_Abstract
{
    protected function _construct()
    {
        $this->_setResource('core/resource');
    }

    protected function _getMappedAttributes()
    {
        return $this->getReadConnection()->select()
            ->from(array('tr' => $this->getTable('xcom_mapping/product_type_relation')), array())
            ->join(array('ar' => $this->getTable('xcom_mapping/attribute_relation')),
                'ar.relation_product_type_id = tr.relation_product_type_id', array())
            ->columns(array(
                'attribute_set_id'          => 'tr.attribute_set_id',
                'mapping_product_type_id'   => 'tr.mapping_product_type_id',
                'attribute_id'              => 'ar.attribute_id',
                'mapping_attribute_id'      => 'ar.mapping_attribute_id'
        ));
    }

    /**
     * Add WHERE statement for select only not mapped attributes with mapped value(s)
     *
     * @param Mage_Catalog_Model_Resource_Eav_Mysql4_Product_Collection $collection
     * @param $attributeSetId
     * @return Mage_Catalog_Model_Resource_Eav_Mysql4_Product_Collection
     */
    public function addFilterOnlyMappedAttributes($collection, $attributeSetId)
    {
        $select = $this->_getMappedAttributes()
            ->reset(Zend_Db_Select::COLUMNS)
            ->columns(array('dummy_field' => new Zend_Db_Expr('1')))
            ->where('main_table.attribute_id = ar.attribute_id')
            ->where('tr.attribute_set_id = ?', $attributeSetId);

        $collection->getSelect()
            ->where(sprintf('NOT EXISTS(%s)', $select->assemble()));
        return $collection;
    }

    /**
     * Add WHERE statement for select only not mapped mapping attributes with mapped value(s)
     *
     * @param Xcom_Mapping_Model_Resource_Attribute_Collection $collection
     * @param $attributeSetId
     * @return Xcom_Mapping_Model_Resource_Attribute_Collection
     */
    public function addFilterOnlyMappedMappingAttributes($collection, $attributeSetId)
    {
        $select = $this->_getMappedAttributes()
            ->reset(Zend_Db_Select::COLUMNS)
            ->columns(array('dummy_field' => new Zend_Db_Expr('1')))
            ->where('main_table.mapping_attribute_id = ar.mapping_attribute_id')
            ->where('tr.mapping_product_type_id = main_table.mapping_product_type_id')
            ->where('tr.attribute_set_id = ?', $attributeSetId);

        $collection->getSelect()
            ->where(sprintf('NOT EXISTS(%s)', $select->assemble()));
        return $collection;
    }


    /**
     * Delete all data which are related to taxonomy.
     * The rest dozen of tables will be cleared together by means of foreign keys
     *
     * @return Xcom_Mapping_Model_Resource_Relation
     */
    public function deleteTaxonomy()
    {
        $this->_getWriteAdapter()->delete($this->getTable('xcom_mapping/product_type'));
        $this->_getWriteAdapter()->delete($this->getTable('xcom_mapping/product_class'));
        return $this;
    }

}
