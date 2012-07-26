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
     * @package     Xcom_Initializer
     * @copyright   Copyright (c) 2010 Magento Inc. (http://www.magentocommerce.com)
     * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
     */
class Xcom_Initializer_Model_Resource_Extension extends Mage_Core_Model_Mysql4_Abstract
{

    /**
     * Initialize resource model.
     */
    protected function _construct()
    {
        $this->_init('xcom_initializer/job', 'param_id');
    }
    /**
     * Used to delete all XCOM installation data
     *
     */
    public function cleanExtensionData()
    {
        $adapter   = $this->_getWriteAdapter();

        $tableCounter = 0;
        $adapter->query("SET FOREIGN_KEY_CHECKS = 0");
        $config = Mage::getConfig()->getNode('global/models');
        foreach ($config->children() as $item) {
            if (strpos($item->getName(), 'xcom_') === 0 &&
                strpos($item->getName(), '_resource') === false) {
                $module = $item->getName();
            }
            if (!empty($module) && $item->entities) {
                foreach ($item->entities->children() as $entity) {
                    $table = $this->getTable($module . '/' . $entity->getName());
                    $adapter->query("DROP TABLE IF EXISTS $table");
                    $tableCounter++;
                }
            }
        }
        $adapter->query("SET FOREIGN_KEY_CHECKS = 1");
        $adapter->delete($this->getTable('core/resource'), "code LIKE 'xcom_%'");
        $this->_getSession()->addNotice("$tableCounter tables were dropped");

        Mage::app()->cleanCache();
    }

    /**
     * Retrieve adminhtml session model object
     *
     * @return Mage_Adminhtml_Model_Session
     */
    protected function _getSession()
    {
        return Mage::getSingleton('adminhtml/session');
    }
}
