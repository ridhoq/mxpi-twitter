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
abstract class Xcom_Mapping_Model_Resource_Collection_Abstract extends Mage_Core_Model_Mysql4_Collection_Abstract
{
    /**
     * @var string Table that store many-to-many relations for current collection
     */
    protected $_relationTable;

    /**
     * @var string Table that store locations for current collection
     */
    protected $_localeTable;

    /**
     * @var Unique identifier for current table
     */
    protected $_uniqueIdentifier;

    /**
     * Prepare base select with locale
     *
     * @return Xcom_Mapping_Model_Resource_Collection_Abstract
     */
    protected function _initSelect()
    {
        $select = $this->getSelect();
        $select->from(array('main_table' => $this->getMainTable()), array())
            ->join(array('mec' => $this->_localeTable),
                sprintf('main_table.%1$s = mec.%1$s AND mec.locale_code = \'%2$s\'',
                $this->getResource()->getIdFieldName(), Xcom_Mapping_Model_Resource_Abstract::CANONICAL_LOCALE_CODE),
                array())
            ->columns(array($this->getResource()->getIdFieldName()));
        if (Xcom_Mapping_Model_Resource_Abstract::CANONICAL_LOCALE_CODE !== $this->getLocaleCode()) {
            $select->joinLeft(array('mel' => $this->_localeTable),
                sprintf('main_table.%1$s = mel.%1$s AND mel.locale_code = \'%2$s\'',
                    $this->getResource()->getIdFieldName(),
                $this->getLocaleCode()), array())
            ->columns(array(
                'name' => $this->getEntityLocalNameExpr()));
        } else {
            $select->columns(array('mec.name'));
        }
        return $this;
    }

    protected function _joinLocaleTable()
    {
        $select = $this->getSelect();
        $select->joinLeft(array('mec' => $this->_localeTable),
                sprintf('main_table.%1$s = mec.%1$s AND mec.locale_code = \'%2$s\'',
                $this->getResource()->getIdFieldName(), Xcom_Mapping_Model_Resource_Abstract::CANONICAL_LOCALE_CODE),
                array());
        if (Xcom_Mapping_Model_Resource_Abstract::CANONICAL_LOCALE_CODE !== $this->getLocaleCode()) {
            $select->joinLeft(array('mel' => $this->_localeTable),
                sprintf('main_table.%1$s = mel.%1$s AND mel.locale_code = \'%2$s\'',
                    $this->getResource()->getIdFieldName(),
                $this->getLocaleCode()), array())
            ->columns(array(
                'name' => $this->getEntityLocalNameExpr()));
        } else {
            $select->columns(array('mec.name'));
        }
        return $this;
    }

    /**
     * Join relation table to collection
     *
     * @return Xcom_Mapping_Model_Resource_Collection_Abstract
     */
    protected function _joinRelationTable()
    {
        $this->getSelect()
            ->joinRight(array('mer' => $this->_relationTable), sprintf('main_table.%1$s = mer.%1$s',
                $this->getResource()->getIdFieldName()))
            ->columns();
        return $this;
    }

    /**
     * Standard resource collection initalization
     *
     * @param string $model
     * @return Mage_Core_Model_Mysql4_Collection_Abstract
     */
    protected function _init($model, $resourceModel = null)
    {
        parent::_init($model, $resourceModel);
        $this->_relationTable   = $this->getMainTable() . '_relation';
        $this->_localeTable     = $this->getMainTable() . '_locale';
        return $this;
    }

    /**
     * TODO Where do we use this method?
     * Retrieve item id
     *
     * @param Varien_Object $item
     * @return mixed
     */
    protected function _getItemId(Varien_Object $item)
    {
        if ($item->hasData($this->_uniqueIdentifier)) {
            $identifier = $item->getData($this->_uniqueIdentifier);
        } else {
            $identifier = $item->getId();
        }
        return $identifier;
    }

    protected function _removeFieldsFromSelect(array $fields)
    {
        $new = array();
        $old = $this->getSelect()->getPart(Zend_Db_Select::COLUMNS);
        foreach ($old as $column) {
            list($table, $statement, $alias) = $column;
            $field = $alias ? $alias : $statement;
            if (!in_array($field, $fields)) {
                if ($statement instanceof Zend_Db_Expr) {
                    $new[$field] = $statement;
                } else {
                    $new[$field] = $table . '.' . $statement;
                }
            }
        }
        $this->getSelect()->reset(Zend_Db_Select::COLUMNS)
            ->columns($new);
        return $this;
    }

    /**
     * Convert items array to array for select options
     *
     * return items array
     * array(
     *      $index => array(
     *          'value' => mixed
     *          'label' => mixed
     *      )
     * )
     *
     * @param   string $valueField
     * @param   string $labelField
     * @return  array
     */
    public function toOptionArray($valueField = null, $labelField = 'name')
    {
        if ($valueField == null) {
            $valueField = $this->_resource->getIdFieldName();
        }
        return $this->_toOptionArray($valueField, $labelField);
    }

    /**
     * Return key value array from collection data
     *
     * @param null $valueField
     * @param string $labelField
     * @return array
     */
    public function toOptionHash($valueField = null, $labelField = 'name')
    {
        if ($valueField == null) {
            $valueField = $this->_resource->getIdFieldName();
        }
        return $this->_toOptionHash($valueField, $labelField);
    }

    /**
     * Return array with collection data
     *
     * @return array
     */
    public function getCollectionData()
    {
        //TODO:: open discussion
        return $this->getConnection()->fetchAll($this->getSelect(), $this->_bindParams);
    }

    /**
     * Set unique identifier for collection
     *
     * @param $field
     */
    public function setUniqueIdentifier($field)
    {
        $this->_uniqueIdentifier = $field;
        return $this;
    }

    /**
     * Get locale code from resource
     *
     * @return string
     */
    public function getLocaleCode()
    {
        return $this->getResource()->getLocaleCode();
    }

    /**
     * Set locale code in resource
     *
     * @param $localeCode
     * @return Xcom_Mapping_Model_Resource_Collection_Abstract
     */
    public function setLocaleCode($localeCode)
    {
        $this->getResource()->setLocaleCode($localeCode);
        return $this;
    }

    /**
     * Return statement for retrieve localized value
     *
     * @return string|Zend_Db_Expr
     */
    public function getEntityLocalNameExpr()
    {
        if ($this->getResource()->getLocaleCode() !== Xcom_Mapping_Model_Resource_Abstract::CANONICAL_LOCALE_CODE) {
            return new Zend_Db_Expr('CASE WHEN mel.locale_id IS NULL THEN mec.name ELSE mel.name END');
        } else {
            return 'mec.name';
        }
    }

    public function removeIdFieldFromSelect()
    {
        $this->_removeFieldsFromSelect(array($this->getResource()->getIdFieldName()));
        return $this;
    }

    /**
     * Retrive all ids for collection
     *
     * @return array
     */
    public function getAllIds()
    {
        $column = $this->_uniqueIdentifier ? $this->_uniqueIdentifier :
            'main_table.' . $this->getResource()->getIdFieldName();
        $idsSelect = clone $this->getSelect();
        $idsSelect->reset(Zend_Db_Select::ORDER);
        $idsSelect->reset(Zend_Db_Select::LIMIT_COUNT);
        $idsSelect->reset(Zend_Db_Select::LIMIT_OFFSET);
        $idsSelect->reset(Zend_Db_Select::COLUMNS);
        $idsSelect->columns(new Zend_Db_Expr($column));
        return $this->getConnection()->fetchCol($idsSelect, $this->_bindParams);
    }
}
