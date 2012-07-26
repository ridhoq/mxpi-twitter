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

class Xcom_Mapping_Helper_Data extends Mage_Catalog_Helper_Data
{

    /**
     * Prepare attribute breadcrumb.
     * Example:
     *     Attribute Set: Color Set ~ Custom attribute set
     *
     * @return string
     */
    public function getAttributeSetBreadcrumb()
    {
        $attributeSetId = $this->getAttributeSetId();
        $mappingProductTypeId = $this->getMappingProductTypeId();

        if ($mappingProductTypeId == Xcom_Mapping_Model_Relation::DIRECT_MAPPING) {
            $productTypeName = $this->__('None');
        } else {
            $productTypeName = $this->getProductTypeName($mappingProductTypeId);
        }

        return $this->__('Attribute Set: %s ~ %s',
            $this->getAttributeSetName($attributeSetId), $productTypeName);
    }

    public function getAttributeSetId()
    {
        return (int) Mage::app()->getRequest()->getParam('attribute_set_id');
    }

    public function getMappingProductTypeId()
    {
        return (int) Mage::app()->getRequest()->getParam('mapping_product_type_id');
    }

    /**
     * Prepare attribute breadcrumb.
     * Example:
     *     Attribute: Color ~ Custom attribute
     *
     * @return string
     */
    public function getAttributeBreadcrumb()
    {
        $relationRecord = Mage::getResourceModel('xcom_mapping/attribute_collection')
            ->initAttributeRelations($this->getAttributeSetId())
        ->addFieldToFilter('eat.attribute_id', $this->getAttributeId())
        ->getFirstItem();

        return $this->__('Attribute: %s ~ %s',
            $relationRecord->getAttributeName(), $relationRecord->getMappingAttributeName());
    }

    public function getAttributeId()
    {
        return (int)Mage::app()->getRequest()->getParam('attribute_id');
    }

    /**
     * Retrieve attribute instance.
     *
     * @param int $attributeId
     * @return Mage_Eav_Model_Entity_Attribute_Abstract
     */
    public function getAttribute($attributeId)
    {
        /** @var $config Mage_Eav_Model_Config */
        $config = Mage::getSingleton('eav/config');
        return $config->getAttribute(Mage_Catalog_Model_Product::ENTITY, $attributeId);
    }

    /**
     * @param int $mappingAttributeId
     * @return Xcom_Mapping_Model_Attribute
     */
    public function getProductTypeAttribute($mappingAttributeId)
    {
        return Mage::getModel('xcom_mapping/attribute')->load((int)$mappingAttributeId);
    }

    public function getAttributeSetName($attributeSetId)
    {
        $object = $this->getAttributeSet($attributeSetId);
        if ($object->getAttributeSetName()) {
            return $object->getAttributeSetName();
        }
        return false;
    }

    public function getProductTypeName($targetAttributeSetId)
    {
        $object = $this->getProductType($targetAttributeSetId);
        if ($object->getName()) {
            return $object->getName();
        }
        return false;
    }

    /**
     * Returns attribute set object.
     *
     * @param int $attributeSetId
     * @return Mage_Core_Model_Abstract
     */
    public function getAttributeSet($attributeSetId)
    {
        return Mage::getModel('eav/entity_attribute_set')->load((int) $attributeSetId);
    }

    /**
     * Returns target attribute set object.
     *
     * @param int $mappingProductTypeId
     * @return Xcom_Mapping_Model_Target_Attribute_Set
     */
    public function getProductType($mappingProductTypeId)
    {
        return Mage::getModel('xcom_mapping/product_type')->load((int) $mappingProductTypeId);
    }

    /**
     * Get Attribute Mapping validate before continue url.
     *
     * @return string
     */
    public function getAttributeMappingValidateBeforeContinueUrl()
    {
        return $this->_getUrl('*/map_attribute/validateBeforeContinue');
    }

    /**
     * Returns Attribute Mapping continue button url.
     *
     * @return string
     */
    public function getAttributeMappingContinueUrl()
    {
        return $this->_getUrl('*/*/value', array(
            '_current'  => true,
            'attribute_id'        => '{{attribute_id}}',
            'mapping_attribute_id' => '{{mapping_attribute_id}}'
        ));
    }

    /**
     * Retrieve options hash-table for attribute
     *
     * @param  $attribute
     * @return array
     */
    public function getAttributeOptionsHash($attribute)
    {
        if (!is_object($attribute)) {
            $attribute = $this->getAttribute($attribute);
        }
        $hashTable = array();
        if (in_array($attribute->getFrontendInput(), array('select', 'multiselect'))) {
            $options = $attribute->getSource()->getAllOptions(false);
            foreach($options as $option) {
                if (!empty($option['value']) && !empty($option['label'])) {
                    $hashTable[$option['value']] = $option['label'];
                }
            }
        } elseif (in_array($attribute->getBackendType(), array('varchar', 'text', 'int', 'decimal'))) {
            $hashTable = Mage::getResourceModel('xcom_mapping/attribute')
                ->getEavValuesByAttribute($attribute->getAttributeId());
        }
        return $hashTable;
    }

    /**
     * Get attribute type for mapping
     *
     * @param mixed $attribute
     * @return mixed
     */
    public function getAttributeType($attribute)
    {
        if (!is_object($attribute)) {
            $attribute = $this->getAttribute($attribute);
        }
        if (in_array($attribute->getFrontendInput(), array('select', 'multiselect'))) {
            return 'select';
        }
        return $attribute->getBackendType();
    }

    /**
     * Check if attribute have values
     *
     * @param $attributeId
     * @return bool|array
     */
    public function isMappingValueAuto($attributeId)
    {
        $attributeValues = Mage::getModel('xcom_mapping/attribute_value')
            ->getByAttributeId($attributeId);
        return $attributeValues ? $attributeValues : false;
    }

    /**
     * @return array
     */
    public function getSupportedLocales()
    {
        return array(
            array('country' => 'US',  'language'=> 'en'),
            array('country' => 'GB',  'language'=> 'en'),
            array('country' => 'DE',  'language'=> 'de'),
            array('country' => 'FR',  'language'=> 'fr'),
            array('country' => 'AU',  'language'=> 'en'),
        );
    }

    /**
     * Returns hardcoded topics for initializer
     *
     * @return array
     */
    public function getHardcodedTopics()
    {
        return array("productTaxonomy/get", "productTaxonomy/productType/get");
    }
}
