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

/**
 * Cache helper
 *
 * @package     lib
 * @subpackage  Mage_Selenium
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class Mage_Selenium_Helper_Cache extends Mage_Selenium_Helper_Abstract
{
    /**
     * Path to cash config
     */
    const XPATH_CACHE = 'framework/cache';

    /**
     * Default dir fo cash files
     */
    const DEFAULT_CACHE_DIR = 'var/cache';

    /**
     * Instance of cache
     * @var Zend_Cache_Core|Zend_Cache_Frontend
     */
    protected $_cache;

    /**
     * Returns path to cash dir
     *
     * @param array $options
     *
     * @return string
     */
    protected function _getCacheDir($options)
    {
        $cacheDir = isset($options['cache_dir']) ? $options['cache_dir'] : self::DEFAULT_CACHE_DIR;
        $cacheDir = SELENIUM_TESTS_BASEDIR . DIRECTORY_SEPARATOR . $cacheDir;
        if (!is_dir($cacheDir)) {
            $io = new Varien_Io_File();
            if (!$io->mkdir($cacheDir, 0777, true)) {
                throw new Exception('Cache dir is not defined');
            }
        }
        return $cacheDir;
    }

    /**
     * Retrieve cache instance
     * @return Zend_Cache_Core|Zend_Cache_Frontend
     */
    public function getCache()
    {
        if (!$this->_cache) {
            $config = $this->getConfig()->getHelper('config')->getConfigValue(self::XPATH_CACHE);
            if (isset($config)) {
                $frontend = $config['frontend']['name'];
                $backend = $config['backend']['name'];

                $frontendOption = $config['frontend']['options'];
                $backendOption = $config['backend']['options'];

                $backendOption['cache_dir'] = $this->_getCacheDir($backendOption);

                $this->_cache = Zend_Cache::factory($frontend, $backend, $frontendOption, $backendOption);
            }
        }
        return $this->_cache;
    }
}
