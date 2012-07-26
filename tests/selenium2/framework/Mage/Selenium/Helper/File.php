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
 * @copyright   Copyright (c) 2012 Magento Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

require_once('SymfonyComponents/YAML/sfYaml.php');

/**
 * File helper class
 *
 * @package     selenium
 * @subpackage  Mage_Selenium
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class Mage_Selenium_Helper_File extends Mage_Selenium_Helper_Abstract
{
    /**
     * Loads YAML file and returns parsed data
     *
     * @param string $fullFileName Full file name (including path)
     *
     * @return array|bool
     */
    public function loadYamlFile($fullFileName)
    {
        $data = false;
        if ($fullFileName && file_exists($fullFileName)) {
            $data = sfYaml::load($fullFileName);
        }
        return $data;
    }

    /**
     * Loads multiple YAML files and returns merged data
     *
     * @param string $globExpr File names glob pattern
     *
     * @return array
     */
    public function loadYamlFiles($globExpr)
    {
        $data = array();
        $files = glob($globExpr);
        if (!empty($files)) {
            foreach ($files as $file) {
                $fileData = $this->loadYamlFile($file);
                if ($fileData) {
                    $data = array_replace_recursive($data, $fileData);
                }
            }
        }
        return $data;
    }
}
