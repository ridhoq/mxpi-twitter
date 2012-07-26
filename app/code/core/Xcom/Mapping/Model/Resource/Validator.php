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
class Xcom_Mapping_Model_Resource_Validator extends Mage_Core_Model_Mysql4_Abstract
{
    public function _construct()
    {
        $this->_setResource('core/resource');
    }

    public function validateIsRequiredAttributeHasMappedValue($productTypeId, $mappingAttributeId = null,
        $attributeSetId = null, $attributeId = null)
    {
        $adapter = $this->_getReadAdapter();
        $select = $adapter->select()
            ->from(array('xma' => $this->getTable('xcom_mapping/attribute')), array())
            ->join(array('mac' => $this->getTable('xcom_mapping/attribute_channel')),
                'mac.mapping_attribute_id = xma.mapping_attribute_id', array())
            ->joinLeft(array('ptr' => $this->getTable('xcom_mapping/product_type_relation')),
                'ptr.mapping_product_type_id = xma.mapping_product_type_id'
                . (($attributeSetId) ? $adapter->quoteInto(' AND ptr.attribute_set_id = ?', $attributeSetId) : ''),
                array())
            ->joinLeft(array('mar' => $this->getTable('xcom_mapping/attribute_relation')),
                'mar.relation_product_type_id = ptr.relation_product_type_id AND '
                . 'mar.mapping_attribute_id = xma.mapping_attribute_id'
                . (($attributeId) ? $adapter->quoteInto(' AND mar.attribute_id = ?', $attributeId) : ''), array())
            ->joinLeft(array('avr' => $this->getTable('xcom_mapping/attribute_value_relation')),
                'avr.relation_attribute_id = mar.relation_attribute_id', array())
            ->where('mac.is_required = 1')
            ->where('avr.relation_value_id IS NULL')
            ->where('xma.mapping_product_type_id = ?', $productTypeId)
            ->columns(array(
                'exists_not_mapped_required_value'  => new Zend_Db_Expr('COUNT(1)')
        ));
        if ($mappingAttributeId) {
            $select->where('xma.mapping_attribute_id = ?', $mappingAttributeId);
        }

        return $adapter->fetchOne($select);
    }
}
