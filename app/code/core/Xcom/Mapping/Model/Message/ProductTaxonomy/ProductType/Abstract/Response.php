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

class Xcom_Mapping_Model_Message_ProductTaxonomy_ProductType_Abstract_Response
    extends Xcom_Xfabric_Model_Message_Response
{
    /**
     * @var string
     */
    protected $_defaultLocaleCode = 'en_US';
    protected $_localeCode;
    protected $_eventPrefix = '';

    /**
     * Message will be processed later to reduce the time on receiving
     * @var bool
     */
    protected $_isProcessLater = true;

    /**
     * Save product types.
     *
     * @return Xcom_Mapping_Model_Message_ProductTaxonomy_ProductType_Abstract_Response
     */
    public function process()
    {
        //since this process is delayed event prefix has to be set here
        $this->_eventPrefix = 'response_message';
        parent::process();
        $data = $this->getBody();
        if (isset($data['productTypes'])) {
            $this->saveProductTypes($data['productTypes']);
        }

        return $this;
    }

    /**
     * Store Product Type data to the database.
     * Calls Attribute persist operation in case 'attributes' parameter has data.
     *
     * @param array $data
     * @return Xcom_Mapping_Model_Message_ProductTaxonomy_ProductType_Abstract_Response
     */
    public function saveProductTypes(array $data)
    {
        $this->_deleteProductTypes($data);
        foreach ($data as $productTypeData) {
            /** @var $productType Xcom_Mapping_Model_Product_Type */
            $productType = Mage::getModel('xcom_mapping/product_type')
                ->load($productTypeData['id'], 'product_type_id');

            $this->saveProductType($productType, $productTypeData);
            if (!empty($productTypeData['attributes'])) {
                $this->saveAttributes($productType, $productTypeData['attributes']);
            }
        }
        return $this;
    }

    /**
     * Clean product types from database which are not present in given $data array.
     *
     * @param array $data
     * @return Xcom_Mapping_Model_Message_ProductTaxonomy_ProductType_Abstract_Response
     */
    protected function _deleteProductTypes(array $data)
    {
        $productTypeIds = $this->_collectIds($data);

        $oldIds = Mage::getSingleton('xcom_mapping/product_type')->getCollection()
            ->addFieldToFilter('product_type_id', array('nin' => $productTypeIds))
            ->setLocaleCode($this->getLocaleCode())
            ->getAllIds();

        Mage::getSingleton('xcom_mapping/product_type')->deleteByIds($oldIds);
        return $this;
    }

    /**
     * Collect all ids from response.
     *
     * @param array $data
     * @param string $indexName
     * @return array
     */
    protected function _collectIds(array $data, $indexName = 'id')
    {
        $result = array();
        foreach ($data as $item) {
            $result[] = $item[$indexName];
        }
        return $result;
    }

    public function saveProductType(Mage_Core_Model_Abstract $productType, array $data)
    {
        $productTypeData = array(
            'product_type_id'   => $data['id'],
            'version'           => $data['version'],
            'name'              => $data['name'],
            'description'       => $data['description'],
            'product_class_ids' => $data['productClassIds'],
            'locale_code'       => $this->getLocaleCode()
        );
        $productType->addData($productTypeData)
            ->save();

        return $this;
    }

    public function saveAttributes(Varien_Object $productType, array $attributes)
    {
        $this->_deleteAttributes($productType, $attributes);
        foreach ($attributes as $attributeData) {
            /** @var $attribute Xcom_Mapping_Model_Attribute */
            $attribute = Mage::getModel('xcom_mapping/attribute');

            $mappingAttributeId = Mage::getModel('xcom_mapping/attribute')->getCollection()
                ->addFieldToFilter('mapping_product_type_id', $productType->getId())
                ->addFieldToFilter('attribute_id', $attributeData['id'])
                ->getFirstItem()
                ->getId();

            if ($mappingAttributeId) {
                $attribute->load($mappingAttributeId);
            }

            $this->saveAttribute($productType, $attribute, $attributeData);
            $this->saveAttributeValues($attribute, $attributeData);
        }

        return $this;
    }

    protected function _deleteAttributes(Varien_Object $productType, array $attributes)
    {
        $attributeIds = $this->_collectIds($attributes);

        $oldIds = Mage::getSingleton('xcom_mapping/attribute')->getCollection()
            ->addFieldToFilter('attribute_id', array('nin' => $attributeIds))
            ->addFieldToFilter('mapping_product_type_id', $productType->getId())
            ->setLocaleCode($this->getLocaleCode())
            ->getAllIds();

        Mage::getSingleton('xcom_mapping/attribute')->deleteByIds($oldIds);
        return $this;
    }

    public function saveAttribute(Varien_Object $productType, Varien_Object $attribute, $data)
    {
        $channelDecoration = array();
        if (!is_null($data['channelAttributeDecorations'])) {
            foreach ($data['channelAttributeDecorations'] as $decoration) {
                $channelDecoration[] = array(
                    'channel_code'    => $decoration['channelId'],
                    'is_required'   => $decoration['required'],
                    'is_variation'  =>
                        !is_null ($decoration['supportsVariation']) ? $decoration['supportsVariation'] : false
                );
            }
        }

        $info = array(
            'attribute_id'         => $data['id'],
            'mapping_product_type_id' => (int)$productType->getId(),
            'name'                 => $data['name'],
            'channel_decoration'   => $channelDecoration,
            'description'          => $data['description'],
            'is_multiselect'       => isset($data['allowMultipleValues']) ? (bool)$data['allowMultipleValues'] : null,
            'default_value_ids'    => $data['defaultValue'],
            'locale_code'          => $this->getLocaleCode(),
            'is_restricted'        => $this->isStringValues($data) ? 0 : 1
        );

        if ($this->isStringValues($data)) {
            $info['attribute_type'] = Xcom_Mapping_Model_Attribute::ATTR_TYPE_STRING;
        } elseif ($this->isEnumerationValues($data)) {
            $info['attribute_type'] = Xcom_Mapping_Model_Attribute::ATTR_TYPE_ENUM;
        } elseif ($this->isBooleanValues($data)) {
            $info['attribute_type'] = Xcom_Mapping_Model_Attribute::ATTR_TYPE_BOOL;
        }

        $attribute->addData($info)
            ->save();

        return $this;
    }

    /**
     * @param Varien_Object $attribute
     * @param array $attributeData
     * @return Xcom_Mapping_Model_Message_ProductTaxonomy_ProductType_Abstract_Response
     */
    public function saveAttributeValues(Varien_Object $attribute, array $attributeData)
    {
        if ($this->isStringValues($attributeData)) {
            $values = $attributeData['recommendedValues'];
            $nameKey = 'localizedValue';
            $valueIdKey = 'valueId';
        } elseif ($this->isEnumerationValues($attributeData)) {
            $values = $attributeData['enumerators'];
            $nameKey = 'name';
            $valueIdKey = 'id';
        } elseif ($this->isBooleanValues($attributeData)) {
            $values = array(
                array('valueId' => -1, 'name' => 'True', 'channelId' => null),
                array('valueId' => -2, 'name' => 'False', 'channelId' => null),
            );
            $nameKey = 'name';
            $valueIdKey = 'id';
        }

        if (isset($values) && isset($nameKey) && isset($valueIdKey)) {
            $this->_deleteAttributeValues($attribute, $values, $valueIdKey);
            foreach ($values as $attrValues) {
                $this->saveAttributeValueData($attribute, $attrValues, $valueIdKey, $nameKey);
            }
        }

        return $this;
    }

    /**
     * Delete all attribute values from database which aren't in the $values array.
     *
     * @param Varien_Object $attribute
     * @param array $values
     * @param string $valueIdKey
     * @return Xcom_Mapping_Model_Message_ProductTaxonomy_ProductType_Abstract_Response
     */
    protected function _deleteAttributeValues(Varien_Object $attribute, array $values, $valueIdKey)
    {
        $attributeValueIds = $this->_collectIds($values, $valueIdKey);

        $oldIds = Mage::getSingleton('xcom_mapping/attribute_value')->getCollection()
            ->addFieldToFilter('value_id', array('nin' => $attributeValueIds))
            ->addFieldToFilter('mapping_attribute_id', $attribute->getId())
            ->setLocaleCode($this->getLocaleCode())
            ->getAllIds();

        Mage::getSingleton('xcom_mapping/attribute_value')->deleteByIds($oldIds);
        return $this;
    }

    /**
     * Save attribute values to the database.
     *
     * @param Varien_Object $attribute
     * @param array $data
     * @param string $valueIdKey
     * @param string $nameKey
     * @return Xcom_Mapping_Model_Message_ProductTaxonomy_ProductType_Abstract_Response
     */
    public function saveAttributeValueData(Varien_Object $attribute, array $data, $valueIdKey = 'id', $nameKey = 'name')
    {
        $channelCodes = array();
        if (!is_null($data['channelValueDecorations'])) {
            foreach ($data['channelValueDecorations'] as $decoration) {
                $channelCodes[] = $decoration['channelId'];
            }
        }
        $attributeValueData = array(
            'mapping_attribute_id'  => $attribute->getId(),
            'channel_codes'         => $channelCodes,
            'value_id'              => $data[$valueIdKey], // <-- string !!!
            'name'                 => $data[$nameKey],
            'locale_code'           => $this->getLocaleCode(),
        );

        $attributeValue = Mage::getModel('xcom_mapping/attribute_value');

        $mappingAttributeValueId = $attributeValue->getCollection()
            ->addFieldToFilter('mapping_attribute_id', $attribute->getId())
            ->addFieldToFilter('value_id', $data[$valueIdKey])
            ->getFirstItem()
            ->getId();

        if ($mappingAttributeValueId) {
            $attributeValue->load($mappingAttributeValueId);
        }

        $attributeValue->addData($attributeValueData)
            ->save();

        return $this;
    }

    /**
     * @param array $data
     * @return bool
     */
    public function isStringValues(array &$data)
    {
        return isset($data['recommendedValues']);
    }

    /**
     * @param array $data
     * @return bool
     */
    public function isEnumerationValues(array &$data)
    {
        return !empty($data['enumerators']);
    }

    /**
     * @param array $data
     * @return bool
     */
    public function isBooleanValues(array &$data)
    {
        return is_bool($data['defaultValue']);
    }

    /**
     * Returns locale code received from message.
     *
     * @return string
     */
    public function getLocaleCode()
    {
        if (null === $this->_localeCode) {
            $this->_prepareLocaleCode();
        }
        return $this->_localeCode;
    }

    /**
     * @return Xcom_Mapping_Model_Message_ProductTaxonomy_ProductType_Abstract_Response
     */
    protected function _prepareLocaleCode()
    {
        $data = $this->getBody();
        if (!empty($data['locale']['country']) && !empty($data['locale']['language'])) {
            $this->_localeCode = $data['locale']['language'] . '_' . $data['locale']['country'];
        } else {
            $this->_localeCode = $this->_defaultLocaleCode;
        }
        return $this;
    }
}
