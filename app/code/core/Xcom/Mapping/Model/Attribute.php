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
class Xcom_Mapping_Model_Attribute extends Mage_Core_Model_Abstract
{
    /**
     * Attribute type identifier for 'string' type
     */
    const ATTR_TYPE_STRING  = 'string';

    /**
     * Attribute type identifier for 'enumeration' type
     */
    const ATTR_TYPE_ENUM    = 'enumeration';

    /**
     * Attribute type identifier for 'boolean' type
     */
    const ATTR_TYPE_BOOL    = 'boolean';

    public function _construct()
    {
        $this->_init('xcom_mapping/attribute');
    }

    /**
     * Save relation
     *
     * @param int $relationProductTypeId
     * @param int $attributeId
     * @param int $mappingAttributeId
     * @return int
     */
    public function saveRelation($relationProductTypeId, $attributeId, $mappingAttributeId = null)
    {
        return $this->getResource()
            ->saveRelation((int)$relationProductTypeId, (int)$attributeId, $mappingAttributeId);
    }
    /**
     * Get Relation Attribute Id
     *
     * @param int $relationProductTypeId
     * @param int $attributeId
     * @param int $mappingAttributeId
     * @return int
     */
    public function getRelationAttributeId($relationProductTypeId, $attributeId, $mappingAttributeId = null)
    {
        return $this->getResource()
            ->getRelationAttributeId((int)$relationProductTypeId, (int)$attributeId, $mappingAttributeId);
    }

    /**
     * Delete attributes relation
     *
     * @param array $relationAttributeIds
     * @return Xcom_Mapping_Model_Attribute
     */
    public function deleteRelation($relationAttributeIds)
    {
        $this->getResource()->deleteRelation($relationAttributeIds);
        return $this;
    }

    /**
     * Get mapping array for attributes
     *
     * @param $attributeSetId
     * @param null $localeCode
     * @return mixed
     */
    public function getSelectAttributesMapping($attributeSetId, $localeCode = null)
    {
        return $this->getCollection()
            ->setLocaleCode($localeCode)
            ->initAttributeRelations($attributeSetId)
            ->addSelectOnlyFilter()
            ->getCollectionData();
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
