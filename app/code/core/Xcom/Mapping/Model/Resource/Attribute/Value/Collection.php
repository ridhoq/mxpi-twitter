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
class Xcom_Mapping_Model_Resource_Attribute_Value_Collection extends Xcom_Mapping_Model_Resource_Collection_Abstract
{
    /**
     * Prepare model.
     *
     * @return void
     */
    public function _construct()
    {
        $this->_init('xcom_mapping/attribute_value');
    }

    /**
     * Join relation data
     *
     * @param int $attributeSetId
     * @param int $attributeId
     * @return Xcom_Mapping_Model_Resource_Product_Type_Collection
     */
    public function initValueRelations($attributeSetId, $attributeId)
    {
        $select = $this->getSelect()->reset();
        $select->from(array('ptr' => $this->getTable('xcom_mapping/product_type_relation')), array())
            ->join(array('mar' => $this->getTable('xcom_mapping/attribute_relation')),
                'ptr.relation_product_type_id = mar.relation_product_type_id', array())
            ->join(array('mer' => $this->getTable('xcom_mapping/attribute_value_relation')),
                'mer.relation_attribute_id = mar.relation_attribute_id', array())
            ->joinLeft(array('main_table' => $this->getMainTable()),
                'main_table.mapping_value_id = mer.mapping_value_id', array())
            ->where('ptr.attribute_set_id = ?', $attributeSetId)
            ->where('mar.attribute_id = ?', $attributeId)
            ->columns(array(
                'attribute_set_id'          => 'ptr.attribute_set_id',
                'mapping_product_type_id'   => 'ptr.mapping_product_type_id',
                'attribute_id'              => 'mar.attribute_id',
                'mapping_attribute_id'      => 'mar.mapping_attribute_id',
                'value_id'                  => new Zend_Db_Expr('IFNULL(mer.value_id, mer.hash_value)'),
                'mapping_value_id'          => 'mer.mapping_value_id',
                'origin_value_id'           => 'main_table.value_id',
                'mapping_value_form_id'     => new Zend_Db_Expr('IFNULL(mer.mapping_value_id, -1)'),
                'mapping_attribute_value'   => $this->getEntityLocalNameExpr()
            ));
        $this->_joinLocaleTable();
        return $this;
    }
}
