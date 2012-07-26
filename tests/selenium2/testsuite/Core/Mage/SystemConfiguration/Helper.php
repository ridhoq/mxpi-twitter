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
 * @subpackage  tests
 * @author      Magento Core Team <core@magentocommerce.com>
 * @copyright   Copyright (c) 2010 Magento Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/**
 * Helper class
 *
 * @package     selenium
 * @subpackage  tests
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class Core_Mage_SystemConfiguration_Helper extends Mage_Selenium_TestCase
{
    /**
     * System Configuration
     *
     * @param array|string $parameters
     */
    public function configure($parameters)
    {
        if (is_string($parameters)) {
            $parameters = $this->loadData($parameters);
        }
        $parameters = $this->arrayEmptyClear($parameters);
        $chooseScope = (isset($parameters['configuration_scope'])) ? $parameters['configuration_scope'] : null;
        if ($chooseScope) {
            $xpath = $this->_getControlXpath('dropdown', 'current_configuration_scope', $this->getCurrentUimapPage());
            $toSelect = $xpath . '//option[normalize-space(text())="' . $chooseScope . '"]';
            $isSelected = $toSelect . '[@selected]';
            if (!$this->isElementPresent($isSelected)) {
                $this->_defineParameters($toSelect, 'url');
                $this->fillForm(array('current_configuration_scope' => $chooseScope));
                $this->waitForPageToLoad($this->_browserTimeoutPeriod);
                $this->validatePage();
            }
        }
        foreach ($parameters as $value) {
            if (!is_array($value)) {
                continue;
            }
            $tab = (isset($value['tab_name'])) ? $value['tab_name'] : null;
            $settings = (isset($value['configuration'])) ? $value['configuration'] : null;
            if ($tab) {
                $xpath = $this->_getControlXpath('tab', $tab);
                $this->_defineParameters($xpath, 'href');
                $this->clickAndWait($xpath, $this->_browserTimeoutPeriod);
                $this->fillForm($settings, $tab);
                $this->saveForm('save_config');
                $this->assertMessagePresent('success', 'success_saved_config');
            }
        }
    }

    /**
     * Define Url Parameters for System Configuration page
     *
     * @param string $xpath
     * @param string $attribute
     */
    private function _defineParameters($xpath, $attribute)
    {
        $params = $this->getAttribute($xpath . '/@' . $attribute);
        $params = explode('/', $params);
        foreach ($params as $key => $value) {
            if ($value == 'section' && isset($params[$key + 1])) {
                $this->addParameter('tabName', $params[$key + 1]);
            }
            if ($value == 'website' && isset($params[$key + 1])) {
                $this->addParameter('webSite', $params[$key + 1]);
            }
            if ($value == 'store' && isset($params[$key + 1])) {
                $this->addParameter('storeName', $params[$key + 1]);
            }
        }
    }

    /**
     * Enable/Disable option 'Use Secure URLs in Admin/Frontend'
     *
     * @param string $path
     * @param string $useSecure
     */
    public function useHttps($path = 'admin', $useSecure = 'Yes')
    {
        $this->admin('system_configuration');
        $xpath = $this->_getControlXpath('tab', 'general_web');
        $this->addParameter('tabName', 'web');
        $this->clickAndWait($xpath, $this->_browserTimeoutPeriod);
        $secureBaseUrlXpath = $this->_getControlXpath('field', 'secure_base_url');
        $url = preg_replace('/http(s)?/', 'https', $this->getValue($secureBaseUrlXpath));
        $data = array('secure_base_url'             => $url,
                      'use_secure_urls_in_' . $path => ucwords(strtolower($useSecure)));
        $this->fillForm($data, 'general_web');
        $this->clickButton('save_config');
        if ($this->getTitle() == 'Log into Magento Admin Page') {
            $this->loginAdminUser();
            $this->admin('system_configuration');
            $this->clickAndWait($xpath, $this->_browserTimeoutPeriod);
        }
        $this->assertTrue($this->verifyForm($data, 'general_web'), $this->getParsedMessages());
    }
}
