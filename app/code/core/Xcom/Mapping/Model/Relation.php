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
class Xcom_Mapping_Model_Relation extends Mage_Core_Model_Abstract
{
    const DIRECT_MAPPING = -1;

    /**
     * Init resource model
     */
    public function _construct()
    {
        $this->_init('xcom_mapping/relation');
    }

    /**
     * Return product type model
     *
     * @return Mage_Core_Model_Abstract
     */
    protected function _getProductType()
    {
        return Mage::getSingleton('xcom_mapping/product_type');
    }

    /**
     * Return attribute model
     *
     * @return Xcom_Mapping_Model_Attribute
     */
    protected function _getAttribute()
    {
        return Mage::getSingleton('xcom_mapping/attribute');
    }

    /**
     * Return attribute value model
     *
     * @return Xcom_Mapping_Model_Attribute_Value
     */
    protected function _getAttributeValue()
    {
        return Mage::getSingleton('xcom_mapping/attribute_value');
    }

    /**
     * Save relations for values of the attribute.
     *
     * @param $attributeSetId
     * @param $attributeId
     * @param $mappingAttributeId
     * @param $values
     * @return Xcom_Mapping_Model_Relation
     */
    public function saveValuesRelation($attributeSetId, $attributeId, $mappingAttributeId, array $values)
    {
        $mappingAttributeId = ($mappingAttributeId == self::DIRECT_MAPPING) ? null : $mappingAttributeId;

        $relationAttributeId = $this->_getAttribute()->getRelationAttributeId($attributeSetId,
            $attributeId, $mappingAttributeId);
        $attributeType       = Mage::helper('xcom_mapping')->getAttributeType($attributeId);

        $bind = array();
        for ($i = 0; isset($values['attribute_value_' . $i]); $i++) {
            if (!empty($values['target_attribute_value_' . $i])) {
                $row = array(
                    'relation_attribute_id' => $relationAttributeId,
                    'value_id'              => null,
                    'hash_value'            => null);
                if (in_array($attributeType, array('varchar', 'text', 'int', 'decimal'))) {
                    $row['hash_value'] = $values['attribute_value_' . $i];
                } else {
                    $row['value_id'] = $values['attribute_value_' . $i];
                }
                $row['mapping_value_id'] = ($values['target_attribute_value_' . $i] == self::DIRECT_MAPPING) ? null :
                    $values['target_attribute_value_' . $i];
                $bind[] = $row;
            }
        }

        $this->getResource()->beginTransaction();
        try {
            $this->_getAttributeValue()->saveRelation($relationAttributeId, $bind);
            $this->getResource()->commit();
        } catch (Exception $e) {
            $this->getResource()->rollBack();
            throw Mage::exception("Mage_Core", $e->getMessage());
        }
        return $this;
    }

    /**
     * Save mapping relation. It maps Magento attributes against xFabric attributes.
     * In addition, if $mappingAttributeId variable has value of DIRECT_MAPPING const,
     * all values of the given attribute will be automatically mapped to the Custom Value
     * in the saveRelation method.
     *
     * @param $attributeSetId
     * @param $productTypeId
     * @param $attributeId
     * @param $mappingAttributeId
     * @param array $values
     * @return Xcom_Mapping_Model_Relation
     */
    public function saveRelation($attributeSetId, $productTypeId, $attributeId, $mappingAttributeId, array $values)
    {
        $productTypeId      = ($productTypeId == self::DIRECT_MAPPING) ? null : $productTypeId;
        $mappingAttributeId = ($mappingAttributeId == self::DIRECT_MAPPING) ? null : $mappingAttributeId;

        $this->getResource()->beginTransaction();
        try {
            $relationProductTypeId = $this->_getProductType()->saveRelation($attributeSetId, $productTypeId);
            if ($attributeId) {
                $relationAttributeId    = $this->_getAttribute()
                    ->saveRelation($relationProductTypeId, $attributeId, $mappingAttributeId);
            } else {
                $this->_getProductType()->saveAttributesDirectRelation($attributeSetId, $relationProductTypeId);
            }
            $this->getResource()->commit();
        } catch (Exception $e) {
            $this->getResource()->rollBack();
            throw Mage::exception("Mage_Core", $e->getMessage());
        }
        Mage::dispatchEvent('save_mapping_relation', array(
            'relation_product_type_id'  => $relationProductTypeId,
            'attribute_set_id'          => $attributeSetId,
            'mapping_product_type_id'   => $productTypeId,
            'attribute_id'              => $attributeId,
            'mapping_attribute_id'      => $mappingAttributeId
        ));
        return $this;
    }

    /**
     * Add WHERE statement for select only not mapped attributes with mapped value(s)
     *
     * @param Mage_Catalog_Model_Resource_Eav_Mysql4_Product_Collection $collection
     * @param int $attributeSetId
     * @return Xcom_Mapping_Model_Relation
     */
    public function addFilterOnlyMappedAttributes($collection, $attributeSetId)
    {
        $this->getResource()->addFilterOnlyMappedAttributes($collection, $attributeSetId);
        return $this;
    }

    /**
     * Add WHERE statement for select only not mapped mapping attributes with mapped value(s)
     *
     * @param Xcom_Mapping_Model_Resource_Attribute_Collection $collection
     * @param int $attributeSetId
     * @return Xcom_Mapping_Model_Relation
     */
    public function addFilterOnlyMappedMappingAttributes($collection, $attributeSetId)
    {
        $this->getResource()->addFilterOnlyMappedMappingAttributes($collection, $attributeSetId);
        return $this;
    }
}
