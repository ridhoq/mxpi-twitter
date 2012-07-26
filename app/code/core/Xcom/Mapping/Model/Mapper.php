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
class Xcom_Mapping_Model_Mapper extends Mage_Core_Model_Abstract
{
    /**
     * Options array for mapped product values
     *
     * @var array
     */
    protected $_options = array();

    /**
     * List of custom attribute codes which are required in mapped options.
     *
     * {@internal If one of these attributes was not mapped
     * it must be added to mapped attribute list anyway.}
     *
     * @var array
     */
    protected $_requiredCustomAttributes = array(
        'xcom_condition'
    );

    /**
     * Init resource model
     */
    public function _construct()
    {
        $this->_init('xcom_mapping/mapper');
    }

    /**
     * Retrieve values mapping
     *
     * @param Mage_Catalog_Model_Product $product
     * @param $mappedAttribute
     * @param $mappedValue
     * @return Xcom_Mapping_Model_Mapper
     */
    protected function _retrieveAttributeValueMapping($product, $mappedAttribute, $mappedValue)
    {
        if (!empty($mappedValue)) {
            // if value was mapped as custom retrieve its own value
            if ($mappedValue['origin_value_id'] == null) {
                $attributeCode = (!$mappedAttribute['origin_attribute_id']) ? $mappedAttribute['attribute_code'] :
                    $mappedAttribute['origin_attribute_id'];
                $this->_options[$attributeCode] = $product->getAttributeText($mappedAttribute['attribute_code']);
            } else {
                // retrieve mapped attribute value with canonical locale
                $this->_options[$mappedAttribute['origin_attribute_id']] = $mappedValue['origin_value_id'];
            }
        }
        return $this;
    }

    /**
     * Retrieve attribute mapping
     *
     * @param Mage_Catalog_Model_Product $product
     * @param $mappedAttribute
     * @param $attributeSetId
     * @return Xcom_Mapping_Model_Mapper
     */
    protected function _retrieveAttributeMapping($product, $mappedAttribute, $attributeSetId)
    {
        $valueModel = Mage::getModel('xcom_mapping/attribute_value');
        $value      = $product->getData($mappedAttribute['attribute_code']);
        // if attributed was mapped as custom retrieve its own value
        if ($mappedAttribute['mapping_attribute_id'] == null) {
            //hack to get store depended attribute labels
            $defaultStore = Mage::app()->getStore()->getId();
            Mage::app()->getStore()->setId($product->getStoreId());
            $value = $product->getAttributeText($mappedAttribute['attribute_code']);
            if (!empty($value)) {
                $this->_options[$mappedAttribute['attribute_code']] = $value;
            }
            Mage::app()->getStore()->setId($defaultStore);
        // apply stored mapping for attribute value
        } elseif ($value) {
            // retrieve mapped attributes value by product attribute value
            $mappedValues = $valueModel->getSelectValuesMapping($attributeSetId,
                $mappedAttribute['attribute_id'], $value);
            $this->_retrieveAttributeValueMapping($product, $mappedAttribute, reset($mappedValues));
        }
        return $this;
    }

    /**
     * Load attribute mapping target attributes with their values.
     *
     * @param  Mage_Catalog_Model_Product $product
     * @return array
     */
    public function getMappingOptions(Mage_Catalog_Model_Product $product)
    {
        $this->_options = $this->getMappedEavValues($product);
        $attributeSetId = $product->getAttributeSetId();
        $attributeModel = Mage::getModel('xcom_mapping/attribute');

        // retrieve all mapped select attributes for attribute set
        $mappedAttributes = $attributeModel->getSelectAttributesMapping($attributeSetId);
        if (!empty($mappedAttributes)) {
            foreach ($mappedAttributes as $attribute) {
                $this->_retrieveAttributeMapping($product, $attribute, $attributeSetId);
            }
        }
        $this->_addRequiredCustomAttributes($mappedAttributes, $product);
        return $this->_options;
    }

    /**
     * Add required custom attributes which were not mapped
     *
     * @param array $mappedAttributes
     * @param Mage_Catalog_Model_Product $product
     * @return Xcom_Mapping_Model_Mapper
     */
    protected function _addRequiredCustomAttributes($mappedAttributes, $product)
    {
        $requiredCustomAttributes   = $this->_requiredCustomAttributes;
        // except all required custom attributes which were already mapped
        foreach ($mappedAttributes as $attribute) {
            $key    = array_search($attribute['attribute_code'], $requiredCustomAttributes);
            if ($key !== false) {
                unset($requiredCustomAttributes[$key]);
            }
        }
        // add all not mapped required custom attributes to options
        foreach ($requiredCustomAttributes as $attributeCode) {
            $value  = $product->getAttributeText($attributeCode);
            if (!empty($value)) {
                $this->_options[$attributeCode] = $value;
            }
        }
        return $this;
    }

    /**
     * Retrieve mapping for text attributes
     *
     * @param $product
     * @return array
     */
    public function getMappedEavValues($product)
    {
        return $this->getResource()->getMappedEavValues($product);
    }

    /**
     * Retrieve eav tables
     *
     * @return array
     */
    public function getEavTables()
    {
        return $this->getResource()->getEavTables();
    }
}
