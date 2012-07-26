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
 * @copyright   Copyright (c) 2012 Magento Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/**
 * Helper class
 *
 * @package     selenium
 * @subpackage  tests
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class Core_Mage_Paypal_Helper extends Mage_Selenium_TestCase
{
    public static $monthMap = array('1'  => '01 - January',
                                    '2'  => '02 - February',
                                    '3'  => '03 - March',
                                    '4'  => '04 - April',
                                    '5'  => '05 - May',
                                    '6'  => '06 - June',
                                    '7'  => '07 - July',
                                    '8'  => '08 - August',
                                    '9'  => '09 - September',
                                    '10' => '10 - October',
                                    '11' => '11 - November',
                                    '12' => '12 - December');

    ################################################################################
    #                                                                              #
    #                                   PayPal Developer                           #
    #                                                                              #
    ################################################################################
    /**
     * Validate paypal Page
     *
     * @param string $page
     */
    public function validatePaypalPage($page = '')
    {
        if ($page) {
            $this->assertTrue($this->checkCurrentPage($page), $this->getMessagesOnPage());
        } else {
            $page = $this->_findCurrentPageFromUrl();
        }
        $expectedTitle = $this->getUimapPage($this->_configHelper->getArea(), $page)->getTitle($this->_paramsHelper);
        $this->assertSame($expectedTitle, $this->getTitle(), 'Title is unexpected for "' . $page . '" page');
        $this->setCurrentPage($page);
    }

    /**
     * Open paypal tab
     *
     * @param string $tabName
     */
    public function openPaypalTab($tabName = '')
    {
        $page = $this->getUimapPage('paypal_developer', 'paypal_developer_logged_in');
        $this->click($this->_getControlXpath('tab', $tabName, $page));
        $this->waitForNewPage();
        $this->validatePaypalPage();
    }

    /**
     * Log into Paypal developer's site
     */
    public function paypalDeveloperLogin()
    {
        $this->goToArea('paypal_developer', 'paypal_developer_home');
        $loginData = array('login_email'     => $this->_configHelper->getDefaultLogin(),
                           'login_password'  => $this->_configHelper->getDefaultPassword());
        if ($this->controlIsPresent('button', 'button_login')) {
            $this->fillForm($loginData);
            $this->clickButton('button_login', false);
            $this->waitForNewPage();
            $this->waitForElementPresent("//*[@id='nav-menu']");
            $this->validatePaypalPage();
        }
    }

    /**
     * Creates preconfigured Paypal Sandbox account
     *
     * @param string|array $parameters
     *
     * @return array
     */
    public function createPreconfiguredAccount($parameters)
    {
        if (is_string($parameters)) {
            $parameters = $this->loadDataSet('Paypal', $parameters);
        }
        $this->openPaypalTab('test_accounts');
        $this->clickControl('link', 'create_preconfigured_account', false);
        $this->waitForNewPage();
        $this->validatePaypalPage();
        $this->fillForm($parameters);
        $this->clickButton('create_account', false);
        $this->waitForNewPage();
        $this->validatePaypalPage('developer_created_test_account_us');

        return $this->getPaypalSandboxAccountInfo($parameters);
    }

    /**
     * Gets the email for newly created sandbox account
     *
     * @param array $parameters
     *
     * @return array
     */
    public function getPaypalSandboxAccountInfo(array $parameters)
    {
        $this->addParameter('accountEmail', $parameters['login_email']);
        $detailTable = $this->_getControlXpath('fieldset', 'account_details');
        $countRows = $this->getXpathCount($detailTable . '//tr');
        for ($i = 0; $i < $countRows; $i++) {
            $key = $this->getTable($detailTable . '.' . $i . '.0');
            $key = preg_replace('/ /', '_', strtolower(trim($key, ':')));
            $value = $this->getTable($detailTable . '.' . $i . '.2');
            if ($key == 'credit_card') {
                $cardData = explode(':', $value);
                $number = preg_replace('/\D/', '', $cardData[0]);
                list($expMonth, $expYear) = explode('/', $cardData[1]);
                $data[$key] = array('card_type'        => $parameters['add_credit_card'],
                                    'card_number'      => $number,
                                    'expiration_month' => self::$monthMap[trim($expMonth)],
                                    'expiration_year'  => $expYear);
            } else {
                $data[$key] = $value;
            }
        }
        $data['email'] = trim($this->getText($this->_getControlXpath('pageelement', 'email_account')));
        return $data;
    }

    /**
     * Gets API Credentials for account
     *
     * @param string $email
     *
     * @return array
     */
    public function getApiCredentials($email)
    {
        $this->addParameter('accountEmail', $email);
        $this->openPaypalTab('api_credentials');
        $apiCredentials = array();
        $detailTable = $this->_getControlXpath('fieldset', 'account_api_credentials');
        $countRows = $this->getXpathCount($detailTable . '//tr');
        for ($i = 0; $i < $countRows; $i++) {
            $key = $this->getTable($detailTable . '.' . $i . '.0');
            $key = preg_replace('/ /', '_', strtolower(trim($key, ':')));
            $value = $this->getTable($detailTable . '.' . $i . '.1');
            if ($key == 'test_account') {
                $apiCredentials['email_associated_with_paypal_merchant_account'] = trim($value);
            } elseif ($key == 'signature') {
                $apiCredentials['api_signature'] = trim($value);
            } else {
                $apiCredentials[$key] = trim($value);
            }
        }
        return $apiCredentials;
    }

    /**
     * Deletes all accounts at PayPal sandbox
     */
    public function deleteAllAccounts()
    {
        $this->openPaypalTab('test_accounts');
        while ($this->controlIsPresent('button', 'delete_account')) {
            $this->clickButtonAndConfirm('delete_account', 'confirmation_to_delete_account', false);
            $this->waitForNewPage();
        }
    }

    /**
     * Deletes account at PayPal sandbox
     *
     * @param string $email
     */
    public function deleteAccount($email)
    {
        $this->addParameter('accountEmail', $email);
        $this->openPaypalTab('test_accounts');
        if ($this->controlIsPresent('checkbox', 'select_account')) {
            $this->fillForm(array('select_account' => 'Yes'));
            $this->clickButtonAndConfirm('delete_account', 'confirmation_to_delete_account', false);
            $this->waitForNewPage();
        }
    }

    /**
     * Create Buyers Accounts on PayPal sandbox
     *
     * @param array|string $cards mastercard, visa, discover, amex
     *
     * @return array $accounts
     * @test
     */
    public function createBuyerAccounts($cards)
    {
        if (is_string($cards)) {
            $cards = explode(',', $cards);
            $cards = array_map('trim', $cards);
        }
        $accounts = array();
        foreach ($cards as $card) {
            $info = $this->loadDataSet('Paypal', 'paypal_sandbox_new_buyer_account_' . $card);
            $accounts[$card] = $this->createPreconfiguredAccount($info);
            if ($card != 'amex') {
                $accounts[$card]['credit_card']['card_verification_number'] = '111';
            } else {
                $accounts[$card]['credit_card']['card_verification_number'] = '1234';
            }
        }
        return $accounts;
    }

    ################################################################################
    #                                                                              #
    #                 PayPal Sandbox(@TODO check and rewrite)                      #
    #                                                                              #
    ################################################################################
    /**
     * Login using sandbox account
     * Function has not been verified and is not used right now
     * @TODO check and rewrite
     *
     * @param $parameters
     */
    public function paypalSandboxLogin($parameters)
    {
        if (is_string($parameters)) {
            $parameters = $this->loadData($parameters);
        }
        $xpath = $this->getUimapPage('paypal_sandbox', 'paypal_sandbox')->findButton('button_login');
        if ($this->isElementPresent($xpath)) {
            $this->addParameter('pageTitle', $parameters['page_title']);
            $this->validatePaypalPage();
            $this->fillForm($parameters['credentials']);
            $this->clickControl('button', 'button_login');
        }
    }

    /**
     * Configure sandbox account
     * Function has not been verified and is not used right now
     * @TODO check and rewrite
     *
     * @param $parameters
     */
    public function paypalSandboxConfigure($parameters)
    {
        if (is_string($parameters)) {
            $parameters = $this->loadData($parameters);
        }
        $this->addParameter('pageTitle', $parameters['page_title']);
        $this->validatePaypalPage();
        $this->fillForm($parameters['credentials']);
        $this->clickControl('button', 'button_login');
        $this->clickControl('button', 'button_iagree');
    }

    /**
     * Pays the order using paypal sandbox account
     * Function has not been verified and is not used right now
     * @TODO check and rewrite
     *
     * @param $parameters
     */
    public function paypalPayOrder($parameters)
    {
        if (is_string($parameters)) {
            $parameters = $this->loadData($parameters);
        }
        $xpath = $this->getUimapPage('paypal_sandbox', 'paypal_sandbox')->findButton('button_login');
        if (!$this->isElementPresent($xpath)) {
            $this->addParameter('pageTitle', $parameters['page_title_pay_with']);
            $this->validatePaypalPage();
            $this->addParameter('pageTitle', $parameters['page_title']);
            $this->clickControl('link', 'have_paypal_account');
        } else {
            $this->addParameter('pageTitle', $parameters['page_title']);
            $this->validatePaypalPage();
        }
        $this->fillForm($parameters['credentials']);
        $this->addParameter('pageTitle', $parameters['page_title_review_info']);
        $this->clickControl('button', 'button_login');
        $this->clickControl('button', 'button_continue');
    }
}