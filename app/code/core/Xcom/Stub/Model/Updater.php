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
 * @package     Xcom_Stub
 * @copyright   Copyright (c) 2010 Magento Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/**
 * Stub Message Model
 *
 * @category   Xcom
 * @package    Xcom_Stub
 */

class Xcom_Stub_Model_Updater extends Mage_Core_Model_Abstract
{
    /**
     * stub export
     * @return Xcom_Stub_Model_Updater
     */
    public function saveStub()
    {
        $collection = Mage::getResourceModel('xcom_stub/message_collection');
        $dirPath = Mage::getBaseDir('var') . DS . 'stub';
        if (!file_exists($dirPath)) {
            mkdir($dirPath);
        }
        $filePath = $dirPath .  DS . 'stub.txt';
        $data = serialize($collection->getData());
        $file = fopen($filePath, 'wb');
        try {
            fwrite($file, $data);
        } catch (Exception $e){
            Mage::throwException($e->getMessage());
        }
        fclose($file);
        return $this;
    }
    /**
     * stub import
     * @return Xcom_Stub_Model_Updater
     */
    public function loadStub()
    {
        $dirPath = Mage::getModuleDir('etc', 'Xcom_Stub');
        $filePath = $dirPath .  DS . 'stub.txt';
        $file = fopen($filePath, 'rb');
        $stub = Mage::getModel('xcom_stub/message');
        try {
            $stub->getResource()->clearStubTable();
            if (filesize($filePath)) {
                $data = unserialize(fread($file, filesize($filePath)));
                foreach ($data as $row) {
                    unset($row[$stub->getIdFieldName()]);
                    foreach ($row as $key => $value) {
                        $stub->setData($key, $value);
                    }
                    $stub->save();
                    $stub->unsetData();
                }
            }
            fclose($file);
        } catch (Exception $e) {
            fclose($file);
            Mage::throwException($e->getMessage());
        }
        return $this;
    }
}
