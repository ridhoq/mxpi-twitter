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
abstract class Xcom_Mapping_Model_Resource_Abstract extends Mage_Core_Model_Mysql4_Abstract
{

    const CANONICAL_LOCALE_CODE = 'en_US';
    protected $_localeTable;
    protected $_localeTableColumns;
    /**
     * @var array Locales supported by Xcom_Mapping
     */
    protected $_acceptedLocales = array('en_US', 'en_UK', 'fr_FR', 'de_DE', 'en_AU');

    /**
     * @var string Current locale; default is 'en_US'
     */
    protected $_localeCode = 'en_US';

    /**
     * Constructor
     */
    protected function _construct()
    {
        parent::_construct();

    }

    /**
     * Init default locale for mapping
     * @return Xcom_Mapping_Model_Resource_Abstract
     */
    protected function _initLocale()
    {
        $locale = Mage::app()->getLocale()->getLocaleCode();
        if (in_array($locale, $this->_acceptedLocales)) {
            $this->_localeCode = $locale;
        }
        return $this;
    }

    /**
     * Standard resource model initialization
     *
     * @param string $mainTable
     * @param string $idFieldName
     * @return Mage_Core_Model_Mysql4_Abstract
     */
    protected function _init($mainTable, $idFieldName)
    {
        $this->_initLocale();
        $this->_setMainTable($mainTable, $idFieldName);
        $this->_localeTable = $this->getMainTable() . '_locale';
        $adapter = $this->_getReadAdapter();
        $this->_localeTableColumns = array_keys($adapter->describeTable($this->_localeTable));
    }
    /**
     * Retrieve select object for load object data
     *
     * @param   string $field
     * @param   mixed $value
     * @return  Zend_Db_Select
     */
    protected function _getLoadSelect($field, $value, $object)
    {
        $select = parent::_getLoadSelect($field, $value, $object);
        $tables = $select->getPart(Zend_Db_Select::FROM);
        $table  = $tables[$this->getMainTable()];
        if ($table) {
            $localeCode = $object->getResource()->getLocaleCode();
            if ($localeCode == null) {
                $localeCode = $this->_localeCode;
            }
            if ($localeCode !== self::CANONICAL_LOCALE_CODE) {
                $this->_joinTranslatedColumns($select, $localeCode);
            } else  {
                $this->_joinCanonicalColumns($select);
            }
        }
        return $select;
    }

    /**
     * Join canonical values
     * @param  $select
     * @return Xcom_Mapping_Model_Resource_Abstract
     */
    protected function _joinCanonicalColumns($select)
    {
        $select->join(array($this->_localeTable => $this->_localeTable ), $this->getMainTable() . '.'
            . $this->getIdFieldName() . ' = ' . $this->_localeTable . '.' . $this->getIdFieldName() . ' AND '
            . $this->_localeTable . '.locale_code = \'' . self::CANONICAL_LOCALE_CODE . '\'',
            $this->_localeTableColumns);
        return $this;
    }

    /**
     * Join translated values
     * @param  $select
     * @param  $localeCode
     * @return Xcom_Mapping_Model_Resource_Abstract
     */
    protected function _joinTranslatedColumns($select, $localeCode)
    {
        $select->join(array($this->_localeTable => $this->_localeTable ), $this->getMainTable() . '.'
            . $this->getIdFieldName() . ' = ' . $this->_localeTable . '.' . $this->getIdFieldName() . ' AND '
            . $this->_localeTable . '.locale_code = \'' . self::CANONICAL_LOCALE_CODE . '\'', array())
            ->joinLeft(array($this->_localeTable . $localeCode => $this->_localeTable ),
        $this->getMainTable() . '.' . $this->getIdFieldName() . ' = ' . $this->_localeTable . $localeCode
            . '.' . $this->getIdFieldName() . ' AND '
            . $this->_localeTable . $localeCode . '.locale_code = \'' . $localeCode . '\'',
            array());
        $columns = array();
        foreach ($this->_localeTableColumns as $column) {
            $columns[$column] = new Zend_Db_Expr('CASE WHEN ' . $this->_localeTable . $localeCode . '.locale_id'
            . ' IS NULL THEN ' . $this->_localeTable . '.' . $column . ' ELSE ' . $this->_localeTable
            . $localeCode . '.' . $column . ' END');
        }
        $select->columns($columns);
        return $this;
    }

    /**
     * Perform actions after object save
     *
     * @param Varien_Object $object
     */
    protected function _afterSave(Mage_Core_Model_Abstract $object)
    {
        $data = array();
        foreach($this->_localeTableColumns as $column) {
            if ($object->getData($column) != null) {
               $data[$column] =  $object->getData($column);
            }
        }
        $adapter = $this->_getWriteAdapter();
        $adapter->insertOnDuplicate($this->_localeTable, $data, $this->_localeTableColumns);
        return $this;
    }

    /**
     * @param Mage_Core_Model_Abstract $object
     * @param  $index
     * @return null|string
     */
    protected function _checkIndexUnique(Mage_Core_Model_Abstract $object, $index)
    {
        $id = null;
        $adapter = $this->_getReadAdapter();
        if ($index['type'] == 'unique') {
            $select = $adapter->select()
                ->from(array($this->getMainTable()), array($this->getIdFieldName()));
            $diff = array_diff($index['fields'], array_keys($object->getData()));
            if (empty($diff)) {
                foreach ($index['fields'] as $field) {
                    $select->where($this->getMainTable() . '.' . $field . ' = ?', $object->getData($field));
                }
                $id = $adapter->fetchOne($select);
            }
        }
        return $id;
    }

    /**
     * Check for unique values existence
     *
     * @param   Varien_Object $object
     * @return  Mage_Core_Model_Mysql4_Abstract
     */
    protected function _checkUnique(Mage_Core_Model_Abstract $object)
    {
        $adapter = $this->_getReadAdapter();
        $indexes = $adapter->getIndexList($this->getMainTable());
        foreach($indexes as $index) {
            $id = $this->_checkIndexUnique($object, $index);
            if($id) {
                $object->setId($id);
            }
        }
        return $this;
    }

    /**
     * Get locale code
     *
     * @return string
     */
    public function getLocaleCode()
    {
        return $this->_localeCode;
    }

    /**
     * Set locale code
     *
     * @param $localeCode
     * @return Xcom_Mapping_Model_Resource_Abstract
     */
    public function setLocaleCode($localeCode)
    {
        if ($localeCode) {
            $this->_localeCode = $localeCode;
        } else {
            $this->_localeCode = self::CANONICAL_LOCALE_CODE;
        }
        return $this;
    }

    /**
     * Return Id by values
     *
     * @param  $values
     * @return int
     */
    public function getIdByValues($values)
    {
        $adapter = $this->_getReadAdapter()->select();
        $select = $adapter->select()
            ->from(array($this->getMainTable()), array($this->getIdFieldName()))
            ->join(array($this->_localeTable), spintf('%s.%s = %s.%s', $this->getMainTable(), $this->getIdFieldName(),
                $this->_localeTable, $this->getIdFieldName()), array());
            foreach($values as $field => $value) {
                $select->where(sprintf('% = ?', $field), $value);
            }
        return $adapter->fetchOne($select);
    }

    /**
     * Delete records by given primary keys.
     *
     * @param array $ids
     * @return Xcom_Mapping_Model_Resource_Abstract
     */
    public function deleteByIds(array $ids)
    {
        $where = array($this->getIdFieldName() . ' IN (?)' => $ids);
        $this->_getWriteAdapter()->delete($this->getMainTable(), $where);
        return $this;
    }
}
