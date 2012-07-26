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
class Xcom_Mapping_Model_Resource_Product_Class_Collection extends Xcom_Mapping_Model_Resource_Collection_Abstract
{
    /**
     * Prepare model and event prefix name.
     *
     * @return void
     */
    public function _construct()
    {
        $this->_eventPrefix = 'product_class_collection';
        $this->_eventObject = 'collection';
        $this->_init('xcom_mapping/product_class');
    }

    /**
     * Init select used for building of the tree
     * @return Xcom_Mapping_Model_Resource_Product_Class_Collection
     */
    public function initClassesTreeSelect()
    {
        $this->getSelect()
            ->reset(Varien_Db_Select::COLUMNS)
            ->columns(array(
                'id' => 'main_table.mapping_product_class_id',
                'name' => $this->getEntityLocalNameExpr(),
                'parent' => 'main_table.parent_product_class_id',
                'type' => new Zend_Db_Expr('\'class\'')
            ))
        ;
        return $this;
    }
}
