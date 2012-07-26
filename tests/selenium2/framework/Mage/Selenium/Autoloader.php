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
 * @copyright   Copyright (c) 2012 Magento Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/**
 * Class, which described method for automatically calling of needed class/interface what you are trying to use,
 * which hasn't been defined yet. Simple autoloader implementation.
 *
 * @package     selenium
 * @subpackage  Mage_Selenium
 * @author      Magento Core Team <core@magentocommerce.com>
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class Mage_Selenium_Autoloader
{
    /**
     * Registers the autoloader handler
     */
    public static function register()
    {
        spl_autoload_register(array(__CLASS__, 'autoload'));
    }

    /**
     * Autoload handler implementation. Performs calling of class/interface, which hasn't been defined yet
     *
     * @param string $className Class name to be loaded, e.g. Mage_Selenium_TestCase
     *
     * @return boolean True if the class was loaded, otherwise False.
     */
    public static function autoload($className)
    {
        $classFile = str_replace(' ', DIRECTORY_SEPARATOR, ucwords(str_replace('_', ' ', $className)));
        $classFile = $classFile . '.php';
        $path = explode(PATH_SEPARATOR, ini_get('include_path'));
        foreach ($path as $possiblePath) {
            if (file_exists($possiblePath . DIRECTORY_SEPARATOR . $classFile)) {
                return include_once $classFile;
            }
        }
        return false;
    }
}
