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
class Xcom_Mapping_Model_Resource_Mapper extends Mage_Core_Model_Mysql4_Abstract
{
    protected $_eavSupportedTypes = array('varchar', 'text', 'decimal', 'int');

    public function _construct()
    {
        $this->_setResource('core/resource');
    }

    /**
     * Retrieve eav tables
     *
     * @return array
     */
    public function getEavTables()
    {
        $tables = array();
        foreach($this->_eavSupportedTypes as $typeName) {
            $tables[$typeName] = $this->getTable('catalog/product') . '_' . $typeName;
        }
        return $tables;
    }

    protected function _prepareSelect()
    {
        $adapter = $this->_getReadAdapter();
        return $adapter->select()
                ->from(array('ptr' => $this->getTable('xcom_mapping/product_type_relation')),array())
                ->join(array('mar' => $this->getTable('xcom_mapping/attribute_relation')),
                    'mar.relation_product_type_id = ptr.relation_product_type_id' ,array())
                ->join(array('eat' => $this->getTable('eav/attribute')), 'eat.attribute_id = mar.attribute_id', array())
                ->join(array('avr' => $this->getTable('xcom_mapping/attribute_value_relation')),
                    'avr.relation_attribute_id = mar.relation_attribute_id', array())
                ->joinLeft(array('xma' => $this->getTable('xcom_mapping/attribute')),
                    'xma.mapping_attribute_id = mar.mapping_attribute_id ' ,array())
                ->joinLeft(array('avl' => $this->getTable('xcom_mapping/attribute_value_locale')),
                    'avl.mapping_value_id = avr.mapping_value_id AND avl.locale_code = \''
                    . Xcom_Mapping_Model_Resource_Abstract::CANONICAL_LOCALE_CODE . '\'', array());
    }

    /**
     * Retrieve mapping for text attributes
     *
     * @param $product
     * @return array
     */
    public function getMappedEavValues($product)
    {
        $union = array();
        $adapter = $this->_getReadAdapter();
        $tables = $this->getEavTables();
        foreach($tables as $table) {
            $union[] = $this->_prepareSelect()
                ->join(array('t' => $table), 't.attribute_id = mar.attribute_id'
                    . ' AND SHA1(CONCAT(t.attribute_id, t.value)) = avr.hash_value', array())
                ->where('t.entity_id = ?', $product->getId())
                ->where('ptr.attribute_set_id = ?', $product->getAttributeSetId())
                ->where('((avr.mapping_value_id IS NULL AND t.store_id=?) OR (avr.mapping_value_id IS NOT NULL))',
                    $product->getStoreId())
                ->columns(array(
                    'attribute_id'  => new Zend_Db_Expr('IFNULL(xma.attribute_id, eat.attribute_code)'),
                    'name'          => new Zend_Db_Expr('CASE'
                        . ' WHEN avr.mapping_value_id IS NULL THEN t.value ELSE avl.name END')
            ));
        }
        $select = $adapter->select()->union($union);
        return $adapter->fetchPairs($select);
    }
}
