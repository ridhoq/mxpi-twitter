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
 * @package     Xcom_Xfabric
 * @copyright   Copyright (c) 2010 Magento Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class Xcom_Xfabric_Model_System_Config_Backend_Authorization extends Mage_Core_Model_Config_Data
{
    /**
     * Returns authorization model
     *
     * @return Xcom_Xfabric_Model_Authorization
     */
    public function getAuthModel()
    {
        return Mage::getModel('xcom_xfabric/authorization');
    }

    /**
     * Workaround to disable "delete" checkbox near uploaded file
     * @todo going to be removed after adding functionality to revoke authorization data
     *
     * @return bool
     */
    public function getValue()
    {
        return false;
    }

    /**
     * Take actions to process and save submitted authorization file
     *
     * @return Xcom_Xfabric_Model_System_Config_Backend_Authorization
     */
    protected function _beforeSave()
    {
        if (!isset($_FILES['groups']['tmp_name'][$this->getGroupId()]['fields'][$this->getField()]['value'])) {
            return $this;
        }

        $tmpPath = $_FILES['groups']['tmp_name'][$this->getGroupId()]['fields'][$this->getField()]['value'];

        if ($tmpPath && file_exists($tmpPath)) {
            if (!filesize($tmpPath)) {
                Mage::throwException(Mage::helper('xcom_xfabric')->__('XFabric authorization file is empty.'));
            }

            try {
                $this->getAuthModel()->loadFile($tmpPath)->save();
            } catch (Zend_Json_Exception $e) {
                Mage::throwException('Error decoding XFabric authorization file.');
            } catch (Mage_Core_Exception $e) {
                Mage::throwException($e->getMessage());
            }

            unlink($tmpPath);
        }

        return $this;
    }
}
