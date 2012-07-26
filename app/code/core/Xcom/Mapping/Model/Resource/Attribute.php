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
class Xcom_Mapping_Model_Resource_Attribute extends Xcom_Mapping_Model_Resource_Abstract
{
    /**
     * Prepare table name and identifier.
     *
     * @return void
     */
    public function _construct()
    {
        $this->_init('xcom_mapping/attribute', 'mapping_attribute_id');
    }

    /**
     * Perform actions after object save
     *
     * @param Mage_Core_Model_Abstract $object
     * @return Xcom_Mapping_Model_Resource_Attribute
     */
    protected function _afterSave(Mage_Core_Model_Abstract $object)
    {
        parent::_afterSave($object);
        $channelDecoration = $object->getChannelDecoration();
        if ($channelDecoration) {
            $data = array();
            foreach($channelDecoration as $channel) {
                $data[] = array_merge($channel, array($this->getIdFieldName() => $object->getId()));
            }
            $adapter = $this->_getWriteAdapter();

            $adapter->insertOnDuplicate($this->getTable('xcom_mapping/attribute_channel'), $data,
            array('is_required', 'is_variation'));
        }
        return $this;
    }

    /**
     * Save direct relation for attribute and values
     *
     * @param $attributeId
     * @param $relationAttributeId
     * @return Xcom_Mapping_Model_Resource_Attribute
     */
    protected function _saveValuesDirectRelation($attributeId, $relationAttributeId)
    {
        $adapter        = $this->_getWriteAdapter();
        $attribute      = Mage::helper('xcom_mapping')->getAttribute($attributeId);
        $attributeType  = Mage::helper('xcom_mapping')->getAttributeType($attribute);
        $valueIds       = array_keys(Mage::helper('xcom_mapping')->getAttributeOptionsHash($attributeId));
        $valueMapping   = array();
        $bind           = array();
        foreach ($valueIds as $id) {
            $valueMapping['mapping_value_id']       = null;
            $valueMapping['relation_attribute_id']  = $relationAttributeId;
            if ($attributeType == 'select') {
                $valueMapping['value_id']   = $id;
                $valueMapping['hash_value'] = null;
            } else {
                $valueMapping['value_id']   = null;
                $valueMapping['hash_value'] = $id;
            }
            $bind[] = $valueMapping;
        }
        if (!empty($bind)) {
            $adapter->insertOnDuplicate(
                $this->getTable('xcom_mapping/attribute_value_relation'), $bind,
                    array('relation_attribute_id', 'mapping_value_id', 'value_id', 'hash_value'));
        }
        return $this;
    }

    protected function _getRelationId($relationProductTypeId, $attributeId, $mappingAttributeId)
    {
        $relationTable = $this->getTable('xcom_mapping/attribute_relation');
        $adapter = $this->_getWriteAdapter();
        $select = $adapter->select()
            ->from(array('rel' => $relationTable), array('relation_attribute_id'))
            ->where('rel.attribute_id = ?', $attributeId)
            ->where($mappingAttributeId ? 'rel.mapping_attribute_id = ?' : 'rel.mapping_attribute_id IS NULL',
                $mappingAttributeId)
            ->where('rel.relation_product_type_id = ?', $relationProductTypeId);
        return $adapter->fetchOne($select);
    }
    /**
     * Save relation
     *
     * @param  $relationProductTypeId
     * @param  $attributeId
     * @param  $mappingAttributeId
     * @return int
     */
    public function saveRelation($relationProductTypeId, $attributeId, $mappingAttributeId = null)
    {
        $relationTable = $this->getTable('xcom_mapping/attribute_relation');
        $data = array(
            'relation_product_type_id'  => $relationProductTypeId,
            'attribute_id'              => $attributeId,
            'mapping_attribute_id'      => $mappingAttributeId
        );
        $adapter = $this->_getWriteAdapter();
        $relationAttributeId = $this->_getRelationId($relationProductTypeId, $attributeId, $mappingAttributeId);
        if (!$relationAttributeId) {
            $adapter->insertOnDuplicate($relationTable, $data);
            $relationAttributeId = $this->_getRelationId($relationProductTypeId, $attributeId, $mappingAttributeId);
            if (!$mappingAttributeId || !Mage::helper('xcom_mapping')->isMappingValueAuto($mappingAttributeId)) {
                $this->_saveValuesDirectRelation($attributeId, $relationAttributeId);
            }
        }
        return $relationAttributeId;
    }

    /**
     * Get Relation Attribute Id
     *
     * @param $attributeSetId
     * @param $attributeId
     * @param $mappingAttributeId
     * @return string
     */
    public function getRelationAttributeId($attributeSetId, $attributeId, $mappingAttributeId)
    {
        $relationTable = $this->getTable('xcom_mapping/product_type_relation');
        $adapter = $this->_getWriteAdapter();
        $select = $adapter->select()
            ->from(array('rel' => $relationTable), 'relation_product_type_id')
            ->where('rel.attribute_set_id = ?', $attributeSetId);
        $relationProductTypeId = $adapter->fetchOne($select);

        $relationId = $this->_getRelationId($relationProductTypeId, $attributeId, $mappingAttributeId);
        return $relationId;
    }
    /**
     * Delete attributes relation
     *
     * @param $relationAttributeId
     * @return Xcom_Mapping_Model_Resource_Attribute
     */
    public function deleteRelation($relationAttributeIds)
    {
        $this->_getWriteAdapter()->delete($this->getTable('xcom_mapping/attribute_relation'),
            array('relation_attribute_id IN (?)' => $relationAttributeIds));
        return $this;
    }

    /**
     * Retrieve all distinct text values for attribute;
     *
     * @param $attributeId
     * @return array
     */
    public function getEavValuesByAttribute($attributeId)
    {
        $union      = array();
        $tables     = Mage::getModel('xcom_mapping/mapper')->getEavTables();
        $adapter    = $this->_getWriteAdapter();
        foreach($tables as $table) {
            $union[] = $adapter->select()->from(array('t' => $table), array())
                ->distinct(true)
                ->where('t.attribute_id = ?', $attributeId)
                ->where('t.value != \'\'')
                ->where('t.value IS NOT NULL')
                ->columns(array(
                    'hash_value'    => new Zend_Db_Expr('SHA1(CONCAT(t.attribute_id, t.value))'),
                    'value'         => 't.value'
            ));
        }
        $select = $adapter->select()->union($union);
        return $adapter->fetchPairs($select);
    }
}
