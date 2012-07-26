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
 * @category    tests
 * @package     selenium
 * @subpackage  Mage_Selenium
 * @author      Magento Core Team <core@magentocommerce.com>
 * @copyright   Copyright (c) 2010 Magento Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

class Core_Mage_Installation_Helper extends Mage_Selenium_TestCase
{
    /**
     * Delete installation files
     *
     * @return null
     */
    public function removeInstallData()
    {
        $basePath = $this->_configHelper->getBaseUrl();

        $localXml = rtrim($basePath, DIRECTORY_SEPARATOR)
                    . DIRECTORY_SEPARATOR . 'app'
                    . DIRECTORY_SEPARATOR . 'etc'
                    . DIRECTORY_SEPARATOR . 'local.xml';

        $cacheDir = rtrim($basePath, DIRECTORY_SEPARATOR)
                    . DIRECTORY_SEPARATOR . 'var'
                    . DIRECTORY_SEPARATOR . 'cache';

        if (file_exists($localXml)) {
            unlink($localXml);
        }

        $this->_rmRecursive($cacheDir);
    }

    /**
     * Remove fs element with nested elements
     *
     * @param string $dir
     * @return null
     */
    protected function _rmRecursive($dir)
    {
        if (is_dir($dir)) {
            foreach (glob($dir . DIRECTORY_SEPARATOR . '*') as $object) {
                if (is_dir($dir . DIRECTORY_SEPARATOR . $object)) {
                    $this->_rmRecursive($dir . DIRECTORY_SEPARATOR . $object);
                } else {
                    unlink($dir . DIRECTORY_SEPARATOR . $object);
                }
            }
            reset($objects);
            rmdir($dir);
        } else {
            unlink($dir);
        }
    }
}
