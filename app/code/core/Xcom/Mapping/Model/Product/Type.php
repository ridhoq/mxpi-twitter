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
class Xcom_Mapping_Model_Product_Type extends Mage_Core_Model_Abstract
{
    /**
     * Init resource model
     *
     * @return void
     */
    public function _construct()
    {
        $this->_init('xcom_mapping/product_type');
    }

    /**
     * Save direct relation
     *
     * @param $attributeSetId
     * @param $relationProductTypeId
     * @return Xcom_Mapping_Model_Product_Type
     */
    public function saveAttributesDirectRelation($attributeSetId, $relationProductTypeId)
    {
        $this->getResource()->saveAttributesDirectRelation($attributeSetId, $relationProductTypeId);
        return $this;
    }

    /**
     * Save relation and return relation product type id
     *
     * @param $attributeSetId
     * @param $mappingProductTypeId
     * @return mixed
     */
    public function saveRelation($attributeSetId, $mappingProductTypeId)
    {
        return $this->getResource()->saveRelation($attributeSetId, $mappingProductTypeId);
    }

    /**
     * Delete relation of product type
     *
     * @param int $attributeSetId
     * @return mixed
     */
    public function deleteAttributeSetMappingRelation($attributeSetId)
    {
        return $this->getResource()->deleteAttributeSetMappingRelation($attributeSetId);
    }

    /**
     * @param array $ids
     * @return Xcom_Mapping_Model_Product_Class
     */
    public function deleteByIds(array $ids)
    {
        $this->_getResource()->deleteByIds($ids);
        return $this;
    }
}
