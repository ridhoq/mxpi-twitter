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
 * Test data helper class
 *
 * @package     selenium
 * @subpackage  Mage_Selenium
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class Mage_Selenium_Helper_Data extends Mage_Selenium_Helper_Abstract
{
    /**
     * Array of files paths to fixtures
     * @var array
     */
    protected $_configFixtures = array();

    /**
     * Test data array
     * @var array
     */
    protected $_testData = array();

    /**
     * Initialize process
     */
    protected function _init()
    {
        $this->_configFixtures = $this->getConfig()->getConfigFixtures();
        $config = $this->getConfig()->getHelper('config')->getConfigFramework();
        if ($config['load_all_fixtures']) {
            $this->_loadTestData();
        }
    }

    /**
     * Loads and merges DataSet files
     * @return Mage_Selenium_Helper_Data
     */
    protected function _loadTestData()
    {
        if ($this->_testData) {
            return $this;
        }
        foreach ($this->_configFixtures as $codePoolData) {
            if (!array_key_exists('data', $codePoolData)) {
                continue;
            }
            foreach ($codePoolData['data'] as $file) {
                $dataSets = $this->getConfig()->getHelper('file')->loadYamlFile($file);
                if (!$dataSets) {
                    continue;
                }
                foreach ($dataSets as $dataSetKey => $content) {
                    if ($content) {
                        $this->_testData[$dataSetKey] = $content;
                    }
                }
            }
        }

        return $this;
    }

    /**
     * Get test data array
     * @return array
     */
    protected function getTestData()
    {
        if (!$this->_testData) {
            $this->_loadTestData();
        }
        return $this->_testData;
    }

    /**
     * Get value from DataSet by path
     *
     * @param string $path XPath-like path to DataSet value (by default = '')
     *
     * @return mixed
     */
    public function getDataValue($path = '')
    {
        return $this->getConfig()->_descend($this->_testData, $path);
    }

    /**
     * Loads DataSet from specified file.
     *
     * @param string $dataFile - File name or full path to file in fixture folder
     * (for example: 'default\core\Mage\AdminUser\data\AdminUsers') in which DataSet is specified
     * @param string $dataSetName
     *
     * @return array
     * @throws RuntimeException
     */
    public function loadTestDataSet($dataFile, $dataSetName)
    {
        if (preg_match('/(\/)|(\\\)/', $dataFile)) {
            $condition = preg_quote(preg_replace('/(\/)|(\\\)/', DIRECTORY_SEPARATOR, $dataFile));
        } else {
            $condition = 'data' . preg_quote(DIRECTORY_SEPARATOR) . $dataFile;
        }
        if (!preg_match('|\.yml$|', $condition)) {
            $condition .= '\.yml$';
        }

        foreach ($this->_configFixtures as $codePoolData) {
            if (!array_key_exists('data', $codePoolData)) {
                continue;
            }
            foreach ($codePoolData['data'] as $file) {
                if (!preg_match('|' . $condition . '|', $file)) {
                    continue;
                }
                $dataSets = $this->getConfig()->getHelper('file')->loadYamlFile($file);
                if (!$dataSets) {
                    throw new RuntimeException($dataFile . ' file is empty');
                }
                if (array_key_exists($dataSetName, $dataSets)) {
                    $this->_testData[$dataSetName] = $dataSets[$dataSetName];
                    return $this->_testData[$dataSetName];
                }
            }
        }
        throw new RuntimeException('DataSet with name "' . $dataSetName
            . '" is not present in "' . $dataFile . '" file.');
    }
}