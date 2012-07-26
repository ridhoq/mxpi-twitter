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
class Xcom_Mapping_Model_Attribute_Value extends Mage_Core_Model_Abstract
{
    public function __construct()
    {
        $this->_init('xcom_mapping/attribute_value');
    }

    /**
     * Save values relation
     *
     * @param int $relationAttributeId
     * @param array $bind
     * @return Xcom_Mapping_Model_Attribute_Value
     */
    public function saveRelation($relationAttributeId, array $bind)
    {
        $this->getResource()->saveRelation($relationAttributeId, $bind);
        return $this;
    }

    /**
     * Get mapping array for values
     *
     * @param int $attributeSetId
     * @param int $attributeId
     * @param int $valueId
     * @param string|null $localeCode
     * @return array
     */
    public function getSelectValuesMapping($attributeSetId ,$attributeId, $valueId, $localeCode = null)
    {
        return $this->getCollection()
            ->setLocaleCode($localeCode)
            ->initValueRelations($attributeSetId, $attributeId)
            ->addFieldToFilter('mar.attribute_id', $attributeId)
            ->addFieldToFilter('mer.value_id', $valueId)
            ->getCollectionData();
    }

    /**
     * Delete records by given primary keys.
     *
     * @param array $ids
     * @return Xcom_Mapping_Model_Attribute_Value
     */
    public function deleteByIds(array $ids)
    {
        $this->_getResource()->deleteByIds($ids);
        return $this;
    }

    /**
     * Retrieve data values by attribute id
     *
     * @param $attributeId
     * @return mixed
     */
    public function getByAttributeId($attributeId)
    {
        return $this->_getResource()->getByAttributeId($attributeId);
    }
}
