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
class Xcom_Mapping_Model_Resource_Product_Type extends Xcom_Mapping_Model_Resource_Abstract
{
    /**
     * Prepare table name and identifier.
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init('xcom_mapping/product_type', 'mapping_product_type_id');
    }

    /**
     * Get auto-incremented class ids by xFabric class ids
     * @param $classIds
     * @return array
     */
    protected function _getMappingClassIdsByClassIds(array $classIds)
    {
        $select = $this->_getReadAdapter()->select()
            ->from($this->getTable('xcom_mapping/product_class'), array('mapping_product_class_id'))
            ->where('product_class_id IN (?)', $classIds);

        return $this->_getReadAdapter()->fetchAll($select);
    }

    /**
     * Perform actions after object save
     *
     * @param Mage_Core_Model_Abstract $object
     * @return Xcom_Mapping_Model_Resource_Product_Type
     */
    protected function _afterSave(Mage_Core_Model_Abstract $object)
    {
        parent::_afterSave($object);
        $productClasses = $object->getProductClassIds();
        if (!empty($productClasses)) {
            $productClassesMappingIds = $this->_getMappingClassIdsByClassIds($productClasses);
            if (!empty($productClassesMappingIds)) {
                $data = array();
                foreach($productClassesMappingIds as $productClassId) {
                    $data[] = array_merge($productClassId, array('mapping_product_type_id' => $object->getId()));
                }
                $this->_getWriteAdapter()
                    ->delete($this->getTable('xcom_mapping/product_class_type'), array(
                        $this->_getWriteAdapter()->quoteInto('mapping_product_type_id = ?', $object->getId()),
                    ));
                $this->_getWriteAdapter()
                    ->insertOnDuplicate($this->getTable('xcom_mapping/product_class_type'), $data,
                        array('mapping_product_class_id', 'mapping_product_type_id'));
            }
        }
        return $this;
    }

    /**
     * Save direct relation for all attributes on attribute set
     *
     * @param $attributeSetId
     * @param $relationProductTypeId
     * @return Xcom_Mapping_Model_Resource_Product_Type
     */
    public function saveAttributesDirectRelation($attributeSetId, $relationProductTypeId)
    {
        $adapter    = $this->_getWriteAdapter();
        $attributeModel = Mage::getModel('xcom_mapping/attribute');
        $select = $adapter->select()
            ->from(array('eat' => $this->getTable('eav/attribute')), array())
            ->join(array('eea' => $this->getTable('eav/entity_attribute')),
                'eea.attribute_id = eat.attribute_id', array())
            ->where('eea.attribute_set_id = ?', $attributeSetId)
            ->where('eat.is_user_defined = 1')
            ->columns(array(
                'attribute_id'  => 'eat.attribute_id'
        ));
        $attributes = $adapter->fetchCol($select);
        foreach ($attributes as $attributeId) {
            $attributeModel->saveRelation($relationProductTypeId, $attributeId);
        }
        return $this;
    }

    /**
     * @param int $attributeSetId
     * @return string
     */
    protected function _getRelationIds($attributeSetId)
    {
        $select = $this->_getWriteAdapter()->select()
            ->from(array('rel' => $this->getTable('xcom_mapping/product_type_relation')),
                array('relation_product_type_id', 'mapping_product_type_id'))
            ->where('rel.attribute_set_id = ?', $attributeSetId);
        return $this->_getWriteAdapter()->fetchRow($select);
    }

    /**
     * Save relation
     *
     * @param $attributeSetId
     * @param $mappingProductTypeId
     * @return string
     */
    public function saveRelation($attributeSetId, $mappingProductTypeId)
    {
        $relationTable = $this->getTable('xcom_mapping/product_type_relation');
        $data = array(
            'attribute_set_id'          => $attributeSetId,
            'mapping_product_type_Id'   => $mappingProductTypeId
        );
        $adapter = $this->_getWriteAdapter();
        $relationIds = $this->_getRelationIds($attributeSetId);

        if (empty($relationIds)) {
            $adapter->insertOnDuplicate($relationTable, $data, array_keys($data));
            $relationIds = $this->_getRelationIds($attributeSetId, $mappingProductTypeId);
            $relationProductTypeId = $relationIds['relation_product_type_id'];
        } else {
            $relationProductTypeId = $relationIds['relation_product_type_id'];
            if ($relationIds['mapping_product_type_id'] != $mappingProductTypeId) {
                $this->deleteRelation($relationProductTypeId);
            }
        }
        return $relationProductTypeId;
    }

    /**
     * Delete relation
     *
     * @param int $relationProductTypeId
     * @return Xcom_Mapping_Model_Resource_Product_Type
     */
    public function deleteRelation($relationProductTypeId)
    {
        $where = $this->_getWriteAdapter()->quoteInto('relation_product_type_id=?', $relationProductTypeId);
        $this->_getWriteAdapter()->delete($this->getTable('xcom_mapping/product_type_relation'), $where);
        return $this;
    }

    /**
     * @param int $attributeSetId
     * @return Xcom_Mapping_Model_Resource_Product_Type
     */
    public function deleteAttributeSetMappingRelation($attributeSetId)
    {
        $relationIds = $this->_getRelationIds($attributeSetId);
        if (!empty($relationIds['relation_product_type_id'])) {
            $this->deleteRelation($relationIds['relation_product_type_id']);
        }
        return $this;
    }

    /**
     * @return array
     */
    public function getTypesClassesTree()
    {
        $typesSelect = Mage::getResourceModel('xcom_mapping/product_type_collection')
            ->initTypesTreeSelect()
            ->getSelect();

        $classesSelect = Mage::getResourceModel('xcom_mapping/product_class_collection')
            ->initClassesTreeSelect()
            ->getSelect();

        $select = $this->_getReadAdapter()->select()
            ->union(array($classesSelect, $typesSelect));

        $result = $this->_getReadAdapter()->fetchAll($select);

        return $result;
    }

    /**
     * Retrieve mapping product type id by attribute set
     *
     * @param int $attributeSetId
     * @return string
     */
    public function getMappingProductTypeId($attributeSetId)
    {
        $select = $this->_getReadAdapter()->select()
            ->from(array($this->getTable('xcom_mapping/product_type_relation')), array('mapping_product_type_id'))
            ->where('attribute_set_id = ?', $attributeSetId);
        return $this->_getReadAdapter()->fetchOne($select);
    }

    /**
     * Retrieve product type id by attribute set
     *
     * @param int $attributeSetId
     * @return string
     */
    public function getProductTypeId($attributeSetId)
    {
        $select = $this->_getReadAdapter()->select()
            ->from(array('main_table' => $this->getMainTable()), array('product_type_id'))
            ->joinRight(array('ptr' => $this->getTable('xcom_mapping/product_type_relation')),
                'ptr.mapping_product_type_id = main_table.mapping_product_type_id', array())
            ->where('ptr.attribute_set_id = ?', $attributeSetId);
        return $this->_getReadAdapter()->fetchOne($select);
    }
}
