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
 * UIMap helper class
 *
 * @package     selenium
 * @subpackage  Mage_Selenium
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class Mage_Selenium_Helper_Uimap extends Mage_Selenium_Helper_Abstract
{
    /**
     * Array of files paths to fixtures
     * @var array
     */
    protected $_configFixtures = array();

    /**
     * Uimap data
     * @var array
     */
    protected $_uimapData = array();

    /**
     * Initialize process
     */
    protected function _init()
    {
        $this->_configFixtures = $this->getConfig()->getConfigFixtures();
        $config = $this->getConfig()->getHelper('config')->getConfigFramework();
        if ($config['load_all_fixtures']) {
            $this->_loadUimapData();
        }
    }

    /**
     * Load and merge data files
     * @return Mage_Selenium_Helper_Uimap
     */
    protected function _loadUimapData()
    {
        if ($this->_uimapData) {
            return $this;
        }
        foreach ($this->_configFixtures as $codePoolData) {
            if (!array_key_exists('uimap', $codePoolData)) {
                continue;
            }
            foreach ($codePoolData['uimap'] as $areaKey => $files) {
                foreach ($files as $file) {
                    $pages = $this->getConfig()->getHelper('file')->loadYamlFile($file);
                    if (!$pages) {
                        continue;
                    }
                    foreach ($pages as $pageKey => $content) {
                        if ($content) {
                            $this->_uimapData[$areaKey][$pageKey] = new Mage_Selenium_Uimap_Page($pageKey, $content);
                        }
                    }
                }
            }
        }
        return $this;
    }

    /**
     * Retrieve array with UIMap data
     *
     * @param string $area Application area
     *
     * @return mixed
     * @throws OutOfRangeException
     */
    public function getAreaUimaps($area)
    {
        if (!array_key_exists($area, $this->_uimapData)) {
            throw new OutOfRangeException('UIMaps for "' . $area . '" area do not exist');
        }

        return $this->_uimapData[$area];
    }

    /**
     * Retrieve Page from UIMap data configuration by path
     *
     * @param string $area Application area
     * @param string $pageKey UIMap page key
     * @param null|Mage_Selenium_Helper_Params $paramsDecorator Params decorator instance
     *
     * @return mixed
     * @throws OutOfRangeException
     */
    public function getUimapPage($area, $pageKey, $paramsDecorator = null)
    {
        $areaUimaps = $this->getAreaUimaps($area);
        if (!array_key_exists($pageKey, $areaUimaps)) {
            throw new OutOfRangeException('Cannot find page "' . $pageKey . '" in area "' . $area . '"');
        }
        $page = $areaUimaps[$pageKey];
        if ($paramsDecorator) {
            $page->assignParams($paramsDecorator);
        }
        return $page;
    }

    /**
     * Retrieve Page from UIMap data configuration by MCA
     *
     * @param string $area Application area
     * @param string $mca a part of current URL opened in browser
     * @param null|Mage_Selenium_Helper_Params $paramsDecorator Params decorator instance
     *
     * @return mixed
     * @throws OutOfRangeException
     */
    public function getUimapPageByMca($area, $mca, $paramsDecorator = null)
    {
        $mca = trim($mca, ' /\\');
        $appropriatePages = array();
        foreach ($this->_uimapData[$area] as $page) {
            //Get mca without any modifications
            $pageMca = trim($page->getMca(new Mage_Selenium_Helper_Params()), ' /\\');
            if ($pageMca === false || $pageMca === null) {
                continue;
            }
            if ($paramsDecorator) {
                $pageMca = $paramsDecorator->replaceParametersWithRegexp($pageMca);
            }
            if ($area == 'admin' || $area == 'frontend') {
                if (preg_match(';^' . $pageMca . '$;', $mca)) {
                    $appropriatePages[] = $page;
                }
            } elseif ($this->_compareMcaAndPageMca($mca, $pageMca)) {
                if ($paramsDecorator) {
                    $page->assignParams($paramsDecorator);
                }
                return $page;
            }
        }
        if (!empty($appropriatePages)) {
            if (count($appropriatePages) == 1) {
                return array_shift($appropriatePages);
            }
            foreach ($appropriatePages as $page) {
                //Get mca with actual modifications
                $pageMca = trim($page->getMca($paramsDecorator), ' /\\');
                if ($pageMca === $mca) {
                    $page->assignParams($paramsDecorator);
                    return $page;
                }
            }
        }
        throw new OutOfRangeException('Cannot find page with mca "' . $mca . '" in "' . $area . '" area');
    }

    /**
     * Compares mca from current url and from area mca array
     *
     * @param string $mca
     * @param string $page_mca
     *
     * @return bool
     */
    protected function _compareMcaAndPageMca($mca, $page_mca)
    {
        if (parse_url($page_mca, PHP_URL_PATH) == parse_url($mca, PHP_URL_HOST) . parse_url($mca, PHP_URL_PATH)) {
            parse_str(parse_url($mca, PHP_URL_QUERY), $mca_params);
            parse_str(parse_url($page_mca, PHP_URL_QUERY), $page_mca_params);
            if (array_keys($mca_params) == array_keys($page_mca_params)) {
                foreach ($page_mca_params as $key => $value) {
                    if ($mca_params[$key] != $value && $value != '%anyValue%') {
                        return false;
                    }
                }
                return true;
            }
        }
        return false;
    }

    /**
     * Get URL of the specified page
     *
     * @param string $area Application area
     * @param string $page UIMap page key
     * @param null|Mage_Selenium_Helper_Params $paramsDecorator Params decorator instance
     *
     * @return string
     */
    public function getPageUrl($area, $page, $paramsDecorator = null)
    {
        $baseUrl = $this->getConfig()->getHelper('config')->getBaseUrl();
        return $baseUrl . $this->getPageMca($area, $page, $paramsDecorator);
    }

    /**
     * Get Page Mca
     *
     * @param string $area Application area
     * @param string $page UIMap page key
     * @param null|Mage_Selenium_Helper_Params $paramsDecorator Params decorator instance
     *
     * @return mixed
     */
    public function getPageMca($area, $page, $paramsDecorator = null)
    {
        $pageUimap = $this->getUimapPage($area, $page, $paramsDecorator);
        return $pageUimap->getMca($paramsDecorator);
    }

    /**
     * Get XPath that opens the specified page on click
     *
     * @param string $area Application area
     * @param string $page UIMap page key
     * @param null|Mage_Selenium_Helper_Params $paramsDecorator Params decorator instance
     *
     * @return mixed
     */
    public function getPageClickXpath($area, $page, $paramsDecorator = null)
    {
        $pageUimap = $this->getUimapPage($area, $page, $paramsDecorator);
        return $pageUimap->getClickXpath($paramsDecorator);
    }
}
