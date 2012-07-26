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
 * Add address tests.
 *
 * @package     selenium
 * @subpackage  tests
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/**
 * Helper class
 *
 * @package     selenium
 * @subpackage  tests
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class Core_Mage_Customer_Helper extends Mage_Selenium_TestCase
{
    /**
     * Verify that address is present.
     *
     * PreConditions: Customer is opened on 'Addresses' tab.
     * @param array $addressData
     */
    public function isAddressPresent(array $addressData)
    {
        $xpath = $this->_getControlXpath('fieldset', 'list_customer_addresses') . '//li';
        $addressCount = $this->getXpathCount($xpath);
        for ($i = $addressCount; $i > 0; $i--) {
            $this->click($xpath . "[$i]");
            $id = $this->getValue($xpath . "[$i]/@id");
            $arrayId = explode('_', $id);
            $id = end($arrayId);
            $this->addParameter('address_number', $id);
            if ($this->verifyForm($addressData, 'addresses')) {
                return $id;
            }
        }
        return 0;
    }

    /**
     * Defining and adding %address_number% for customer Uimap.
     *
     * PreConditions: Customer is opened on 'Addresses' tab.
     */
    public function addAddressNumber()
    {
        $xpath = $this->_getControlXpath('fieldset', 'list_customer_addresses');
        $addressCount = $this->getXpathCount($xpath . '//li') + 1;
        $this->addParameter('address_number', $addressCount);
        return $addressCount;
    }

    /**
     * Add address for customer.
     *
     * PreConditions: Customer is opened.
     * @param array $addressData
     */
    public function addAddress(array $addressData)
    {
        //Open 'Addresses' tab
        $this->openTab('addresses');
        $this->addAddressNumber();
        $this->clickButton('add_new_address', false);
        $this->waitForAjax();
        //Fill in 'Customer's Address' tab
        $this->fillForm($addressData, 'addresses');
    }

    /**
     * Create customer.
     *
     * PreConditions: 'Manage Customers' page is opened.
     * @param array $userData
     * @param array $addressData
     */
    public function createCustomer(array $userData, array $addressData = null)
    {
        //Click 'Add New Customer' button.
        $this->clickButton('add_new_customer');
        // Verify that 'send_from' field is present
        if (array_key_exists('send_from', $userData)) {
            $page = $this->getCurrentUimapPage();
            $tab = $page->findTab('account_information');
            $pattern = preg_quote(' and not(@disabled)');
            $xpath = preg_replace("/$pattern/", '', $tab->findDropdown('send_from'));
            if (!$this->isElementPresent($xpath)) {
                unset($userData['send_from']);
            }
        }
        //Fill in 'Account Information' tab
        $this->fillForm($userData, 'account_information');
        //Add address
        if (isset($addressData)) {
            $this->addAddress($addressData);
        }
        $this->saveForm('save_customer');
    }

    /**
     * Open customer.
     *
     * PreConditions: 'Manage Customers' page is opened.
     * @param array $searchData
     */
    public function openCustomer(array $searchData)
    {
        $this->searchAndOpen($searchData, true, 'customers_grid');
    }

    /**
     * Register Customer on Frontend.
     *
     * PreConditions: 'Login or Create an Account' page is opened.
     * @param array $registerData
     */
    public function registerCustomer(array $registerData)
    {
        $currentPage = $this->getCurrentPage();
        $this->clickButton('create_account');
        // Disable CAPTCHA if present
        if ($this->controlIsPresent('pageelement', 'captcha')) {
            $this->loginAdminUser();
            $this->navigate('system_configuration');
            $this->systemConfigurationHelper()->configure('disable_customer_captcha');
            $this->frontend($currentPage);
            $this->clickButton('create_account');
        }
        $this->fillForm($registerData);
        $this->saveForm('submit');
    }

    /**
     * Log in customer at frontend.
     *
     * @param array $loginData
     */
    public function frontLoginCustomer(array $loginData)
    {
        $this->logoutCustomer();
        $this->clickControl('link', 'log_in');
        $this->fillForm($loginData);
        $this->clickButton('login');
    }
}