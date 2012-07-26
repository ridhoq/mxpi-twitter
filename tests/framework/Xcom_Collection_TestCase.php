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
 * @package     Xcom_Ebay
 * @copyright   Copyright (c) 2010 Magento Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class Xcom_Collection_TestCase extends Xcom_TestCase
{
    protected $_resource;

    public function setUp()
    {
        parent::setUp();
        $this->_resource = new Mage_Core_Model_Resource();
    }

    protected function _retrieveColumns($select)
    {
        $data = $select->getPart(Zend_Db_Select::COLUMNS);
        $columns = array();
        foreach($data as $column) {
            $columns[] = ($column[2] !== null) ? $column[2] : $column[1];
        }
        return $columns;
    }

    protected function _retrieveTables($select)
    {
        $data = $select->getPart(Zend_Db_Select::FROM);
        $tables = array();
        foreach($data as $table) {
            $tables[] = $table['tableName'];
        }
        return $tables;
    }

    protected function _retrieveWhere($select)
    {
        return $select->getPart(Zend_Db_Select::WHERE);
    }

    protected function _retrieveGroup($select)
    {
        return $select->getPart(Zend_Db_Select::GROUP);
    }

    protected function _retrieveHaving($select)
    {
        return $select->getPart(Zend_Db_Select::HAVING);
    }

}
