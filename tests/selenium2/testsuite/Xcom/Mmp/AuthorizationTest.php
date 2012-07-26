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
class XCom_Mmp_AuthorizationTest extends Mage_Selenium_TestCase {

    protected $_initializer_in_progress = false;

    /**
     * <p>Log in to Backend.</p>
     */
    public function setUpBeforeTests() {
        $this->loginAdminUser();
    }

    /**
     * <p> Navigate to Channels->Manage->Accounts</p>
     * Verify Initializer
     */
    protected function Precondition() {
        $this->navigate('account_channels');
        $init_message1 = $this->isTextPresent('Updating Channel information from X.commerce');
        $init_message2 = $this->isTextPresent('You will not be able to use the Channel functionality ' .
                        'until this process is complete.');
        $init_message3 = $this->isTextPresent('This process may take some time.');
        if ($init_message1 == true and $init_message2 == true and $init_message3 == true) {
            $this->_initializer_in_progress = true;
        }
    }

    /**
     * <p> Verify grid elements and buttons  </p>
     * <p> Press "Add Account" button </p>
     */
    public function test_Authorization_Accounts_eBay()
    {
        $this->Precondition();
        if ($this->_initializer_in_progress == true) {
            $this->markTestSkipped('Initializer work is not complete');
            return;
        }
        $this->assertElementPresent($this->_getControlXpath('button', 'add_account'));
        $this->assertElementPresent($this->_getControlXpath('button', 'search'));
        $this->assertElementPresent($this->_getControlXpath('button', 'reset_filter'));
        $this->assertElementPresent($this->_getControlXpath('button', 'search'));
        $this->assertElementPresent($this->_getControlXpath('button', 'submit'));
        $this->assertElementPresent($this->_getControlXpath('field', 'ebay_user_id'));
        $this->assertElementPresent($this->_getControlXpath('field', 'validate_from'));
        $this->assertElementPresent($this->_getControlXpath('field', 'validate_to'));
        $this->assertElementPresent($this->_getControlXpath('dropdown', 'view_per_page'));
        $this->assertElementPresent($this->_getControlXpath('dropdown', 'filter_environment'));
        $this->assertElementPresent($this->_getControlXpath('dropdown', 'massaction'));
        $this->assertElementPresent($this->_getControlXpath('dropdown', 'filter_status'));
        $this->clickButton('add_account');
    }

    /**
     * <p> "Add Account" page opens</p>
     * <p> Select "Sandbox" environment </p>
     * <p> Press "Authoriize eBay Account" button</p>
     * <p> Close pop up </p>
     * <p> Run script to call authorization complete message(this trick only for selenium) </p>
     * <p> Click "Save" button </p>
     * <p> Expected result: Account was added. Succes message presents </p>
     *
     *   @depends test_Authorization_Accounts_eBay
     */
    public function test_Authorization_eBay() {
        $this->assertElementPresent($this->_getControlXpath('button', 'authorize_ebay'));
        $this->assertElementPresent($this->_getControlXpath('dropdown', 'environment'));
        $this->assertElementPresent($this->_getControlXpath('button', 'back'));
        $ebay_account_data = $this->loadData('account_data');
        $this->fillForm($ebay_account_data);
        $this->clickControl('button', 'authorize_ebay', false);
        $this->waitForPopUp(null);
        $this->selectPopUp(null);
        $this->close();
        $this->selectWindow(null);
        $this->windowFocus();
        $script = "var seleniumVarAuthorization = selenium.browserbot.getCurrentWindow().varAuthorization;"
                . "selenium.browserbot.getCurrentWindow().clearInterval(seleniumVarAuthorization.intervalID);"
                . "seleniumVarAuthorization.sendCompleteAuthorizationMessage();";
        $this->getEval($script);
        $this->pleaseWait();
        $this->assertElementPresent($this->_getControlXpath('field', 'ebay_user_id'));
        $this->clickControl('button', 'back');
        $this->assertElementPresent($this->_getControlXpath('link', 'edit_ebay_account'));
    }

    /**
     * <p> Select checkbox against account </p>
     * <p> Select in Actions dropdown "Disable" </p>
     * <p> Press "Submit" button</p>
     * <p> Close pop up </p>
     * <p> Expected result: Account was disabled. Success message presents </p>
     *
     *  @depends test_Authorization_eBay
     */
    public function test_Authorization_Disable_eBay() {
        $this->clickControl('checkboxe', 'chbx', false);
        $this->select('class=required-entry select absolute-advice local-validation', 'label=Disable');
        /**
         * select account with user_id=testuser_xcommerce
         * check whether channel with such user_id exists
         * if channel exists then checking special confirmation
         * else checking simple confirmation
         */
        $account_id = Mage::getModel('xcom_mmp/account')->load('testuser_xcommerce', 'user_id')->getId();
        $this->channel_id = Mage::getModel('xcom_mmp/channel')->load($account_id, 'account_id')->getId();
        if ($this->channel_id) {
            $this->clickButtonAndConfirm('submit', 'confirmation_for_disable_with_channel');
        } else {
            $this->clickButtonAndConfirm('submit', 'confirmation_for_disable');
        }
        $this->assertMessagePresent('success', 'success_account_disabled');
    }

    /**
     * <p> Select checkbox against account </p>
     * <p> Select in Actions dropdown "Enable" </p>
     * <p> Press "Submit" button</p>
     * <p> Close pop up </p>
     * <p> Expected result: Account was Eabled. Success message presents </p>
     *
     *  @depends test_Authorization_Disable_eBay
     */
    public function test_Authorization_Enable_eBay() {
        $this->clickControl('checkboxe', 'chbx', false);
        $this->select('class=required-entry select absolute-advice local-validation', 'label=Enable');
        $this->clickButtonAndConfirm('submit', 'confirmation_for_enable');
        $this->assertMessagePresent('success','success_account_enabled');
    }

}
