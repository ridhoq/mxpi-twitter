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
class Xcom_Mapping_Model_Validator extends Mage_Core_Model_Abstract
{
    /**
     * Init resource model
     */
    public function _construct()
    {
        $this->_init('xcom_mapping/validator');
    }

    /**
     * Validate whether each required attribute has at least one mapped value
     * In case there's at least one required attr. with no mapped values the method returns false
     * In case at least one value is mapped for each required attribute the method returns true
     *
     * @param int $mappingProductTypeId
     * @param int|null $mappingAttributeId
     * @param int|null $attributeSetId
     * @param int|null $attributeId
     * @return bool
     */
    public function validateIsRequiredAttributeHasMappedValue($mappingProductTypeId,
        $mappingAttributeId = null, $attributeSetId = null, $attributeId = null)
    {
        $isNotMapped = (bool)$this->getResource()->validateIsRequiredAttributeHasMappedValue(
            $mappingProductTypeId, $mappingAttributeId, $attributeSetId, $attributeId
        );
        //TODO Why true is false?
        return $isNotMapped ? false : true;
    }
}
