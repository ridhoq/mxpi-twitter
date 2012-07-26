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
class Xcom_Mapping_Model_Resource_Attribute_Collection extends Xcom_Mapping_Model_Resource_Collection_Abstract
{
    /**
     * Prepare model.
     *
     * @return void
     */
    public function _construct()
    {
        $this->_init('xcom_mapping/attribute');
    }

    /**
     * Join relation data
     *
     * @param $attributeSetId
     * @return Xcom_Mapping_Model_Resource_Attribute_Collection
     */
    public function initAttributeRelations($attributeSetId)
    {
        $helper = Mage::helper('xcom_mapping');
        $select = $this->getSelect()->reset();
        $select->from(array('ptr' => $this->getTable('xcom_mapping/product_type_relation')), array())
            ->join(array('mer' => $this->getTable('xcom_mapping/attribute_relation')),
                'ptr.relation_product_type_id = mer.relation_product_type_id', array())
            ->join(array('eat' => $this->getTable('eav/attribute')), 'eat.attribute_id = mer.attribute_id', array())
            ->joinLeft(array('main_table' => $this->getTable('xcom_mapping/attribute')),
                'main_table.mapping_attribute_id = mer.mapping_attribute_id', array())
            ->where('ptr.attribute_set_id = ?', $attributeSetId)
            ->columns(array(
                'attribute_set_id'          => 'ptr.attribute_set_id',
                'mapping_product_type_id'   => 'ptr.mapping_product_type_id',
                'attribute_id'              => 'eat.attribute_id',
                'attribute_name'            => 'eat.frontend_label',
                'attribute_type'            => 'eat.frontend_input',
                'attribute_code'            => 'eat.attribute_code',
                'mapping_attribute_id'      => 'main_table.mapping_attribute_id',
                'relation_attribute_id'     => 'mer.relation_attribute_id',
                'origin_attribute_id'       => 'main_table.attribute_id',
                'mapping_attribute_name'    => new Zend_Db_Expr('IFNULL(' . $this->getEntityLocalNameExpr()
                    . ', \'' . $helper->__('Custom attribute') . '\')'),
                'is_multiselect'            => 'main_table.is_multiselect',
                'attribute_type'            => 'main_table.attribute_type'
            ));
        $this->_joinLocaleTable();
        $this->setUniqueIdentifier('relation_attribute_id');
        return $this;
    }

    /**
     * Add to collection is attribute required column
     *
     * @return Xcom_Mapping_Model_Resource_Attribute_Collection
     */
    public function addIsAttributeRequiredColumn()
    {
        $select = $this->getConnection()->select()
            ->from(array('mac' => $this->getTable('xcom_mapping/attribute_channel')), array())
            ->where('is_required = 1')
            ->group('mapping_attribute_id')
            ->columns(array(
                'mapping_attribute_id',
                'is_required' => new Zend_Db_Expr('MAX(is_required)')
        ));
        $this->getSelect()->joinLeft(array('req' => new Zend_Db_Expr('(' . $select . ')')),
            'req.mapping_attribute_id = main_table.mapping_attribute_id', array('is_required'));
        return $this;
    }

    /**
     * Add to collection is attribute has mapped values
     *
     * @return Xcom_Mapping_Model_Resource_Attribute_Collection
     */
    public function addIsAttributeHasMappedValues()
    {
        $select = $this->getConnection()->select()
            ->from(array('mav' => $this->getTable('xcom_mapping/attribute_value_relation')), array())
            ->where('mav.relation_attribute_id = mer.relation_attribute_id')
            ->group('mav.relation_attribute_id')
            ->columns(array(
                'is_mapped' => new Zend_Db_Expr('IFNULL(MAX(1),0)')
            ));

        $this->getSelect()->columns(array(
            'is_mapped'  => new Zend_Db_Expr('(' . $select . ')')
        ));
        return $this;
    }

    /**
     * Select only select attributes
     * @return Xcom_Mapping_Model_Resource_Attribute_Collection
     */
    public function addSelectOnlyFilter()
    {
        $this->getSelect()->where('eat.frontend_input IN (\'select\', \'multiselect\')');
        return $this;
    }
}
