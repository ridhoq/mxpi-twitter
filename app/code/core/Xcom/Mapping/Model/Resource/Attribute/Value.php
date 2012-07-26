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
class Xcom_Mapping_Model_Resource_Attribute_Value extends Xcom_Mapping_Model_Resource_Abstract
{
    /**
     * Prepare table name and identifier.
     *
     * @return void
     */
    public function _construct()
    {
        $this->_init('xcom_mapping/attribute_value', 'mapping_value_id');
    }

    /**
     * Save relation
     *
     * @param $relationAttributeId
     * @param $bind
     * @return Xcom_Mapping_Model_Resource_Attribute_Value
     */
    public function saveRelation($relationAttributeId, array $bind)
    {
        $relationTable = $this->getTable('xcom_mapping/attribute_value_relation');
        $adapter = $this->_getWriteAdapter();
        $adapter->delete($relationTable, $adapter->quoteInto('relation_attribute_id = ?', $relationAttributeId));
        if (!empty($bind)) {
            $adapter->insertArray($relationTable, array_keys(reset($bind)), $bind);
        }
        return $this;
    }

    /**
     * Get data by mapping_attribute_id value from main table
     *
     * @param $mappingAttributeId
     * @return string
     */
    public function getByAttributeId($mappingAttributeId)
    {
        $adapter = $this->_getReadAdapter();
        $select = $adapter->select();
        $select->from($this->getMainTable())
               ->where('mapping_attribute_id = ?', (int)$mappingAttributeId);
        return $adapter->fetchRow($select);
    }

    /**
     * @param Mage_Core_Model_Abstract $object
     * @return Mage_Core_Model_Mysql4_Abstract
     */
    protected function _beforeSave(Mage_Core_Model_Abstract $object)
    {
        $channelCodes = $object->getChannelCodes();
        if (is_array($channelCodes)) {
            $object->setChannelCodes(implode(',', $channelCodes));
        }
        return parent::_beforeSave($object);
    }

    /**
     * @param Mage_Core_Model_Abstract $object
     * @return Mage_Core_Model_Mysql4_Abstract
     */
    protected function _afterLoad(Mage_Core_Model_Abstract $object)
    {
        $channelCodes = $object->getChannelCodes();
        if (false !== strpos($channelCodes, ',')) {
            $object->setChannelCodes(explode(',', $channelCodes));
        }
        return parent::_beforeSave($object);
    }
}
