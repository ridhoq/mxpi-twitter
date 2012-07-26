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
class Xcom_Mapping_Model_Resource_Product_Type_Collection extends Xcom_Mapping_Model_Resource_Collection_Abstract
{
    /**
     * Prepare model and event prefix name.
     *
     * @return void
     */
    public function _construct()
    {
        $this->_eventPrefix = 'product_type_collection';
        $this->_eventObject = 'collection';
        $this->_init('xcom_mapping/product_type');
    }

    /**
     * Join relation data
     *
     * @return Xcom_Mapping_Model_Resource_Product_Type_Collection
     */
    public function initProductTypeRelations()
    {
        $productEntityTypeId = Mage::getModel('eav/entity')->setType(Mage_Catalog_Model_Product::ENTITY)->getTypeId();
        $select = $this->_joinRelationTable()->getSelect()->reset(Varien_Db_Select::COLUMNS);
        $select->joinRight(array('eas' => $this->getTable('eav/attribute_set')),
                'eas.attribute_set_id = mer.attribute_set_id', array())
            ->where('eas.entity_type_id=?', $productEntityTypeId)
            ->columns(array(
                'attribute_set_id'          => 'eas.attribute_set_id',
                'attribute_set_name'        => 'eas.attribute_set_name',
                'mapping_product_type_id'   => $this->_getMappingProductTypeIdExpr(),
                'product_type_name'         => $this->getProductTypeNameExpr(),
            ));
        $this->setUniqueIdentifier('attribute_set_id');
        return $this;
    }

    /**
     * @return Zend_Db_Expr
     */
    protected function _getMappingProductTypeIdExpr()
    {
        return new Zend_Db_Expr(
            'CASE WHEN ' . $this->getConnection()->quoteIdentifier('main_table.mapping_product_type_id') . ' IS NULL'
                . ' AND ' . $this->getConnection()->quoteIdentifier('mer.relation_product_type_id') . ' IS NOT NULL'
                . ' THEN -1 ELSE '
                . $this->getConnection()->quoteIdentifier('main_table.mapping_product_type_id') . ' END');
    }

    /**
     * @return Zend_Db_Expr
     */
    public function getProductTypeNameExpr()
    {
        return new Zend_Db_Expr(
            'CASE WHEN ' . $this->getEntityLocalNameExpr() . ' IS NULL '
                . 'THEN CASE WHEN '. $this->getConnection()->quoteIdentifier('mer.mapping_product_type_id') . ' IS NULL'
                    . ' AND '. $this->getConnection()->quoteIdentifier('mer.attribute_set_id') . ' IS NOT NULL '
                        . 'THEN ' . $this->getConnection()->quote($this->_getHelper()->__('None'))
                . ' ELSE ' . $this->getConnection()->quote($this->_getHelper()->__('Not mapped')) . ' END '
            . ' ELSE ' . $this->getEntityLocalNameExpr() . ' END'
        );
    }

    /**
     * @return Xcom_Mapping_Helper_Data
     */
    protected function _getHelper()
    {
        return Mage::helper('xcom_mapping');
    }

    /**
     * Init select used for building of the tree
     * @return Xcom_Mapping_Model_Resource_Product_Type_Collection
     */
    public function initTypesTreeSelect()
    {
        $this->getSelect()
            ->reset(Varien_Db_Select::COLUMNS)
            ->columns(
                array(
                    'id' => 'main_table.mapping_product_type_id',
                    'name' => $this->getEntityLocalNameExpr(),
                    'parent' =>  'pct.mapping_product_class_id',
                    'type' => new Zend_Db_Expr('\'type\'')
                )
            )
            ->joinLeft(array('pct' => $this->getTable('xcom_mapping/product_class_type')),
                'main_table.mapping_product_type_id=pct.mapping_product_type_id',
                array()
            );
        return $this;
    }
}
