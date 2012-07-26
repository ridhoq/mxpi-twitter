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
 * Creating Admin User
 *
 * @package     selenium
 * @subpackage  tests
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class XCom_Mmp_ManageChannelTest extends Mage_Selenium_TestCase {

    protected $_initializer_in_progress=false;
    /**
     * <p>Log in to Backend.</p>
     */
    public function setUpBeforeTests() {
        /** @var $resource Mage_Core_Model_Resource */
        $resource = Mage::getSingleton('core/resource');
        $select = Mage::getSingleton('core/resource')->getConnection()
         ->select()->from($resource->getTableName('xcom_mmp/channel'), $cols = 'MAX(channel_id)');
        $this->channelId = Mage::getSingleton('core/resource')->getConnection()->fetchOne($select);
        $resource->getConnection('core_write')->delete($resource->getTableName('xcom_mmp/channel'));
        $this->loginAdminUser();
    }

    protected function assertPreConditions()
    {
        $this->addParameter('url_new_channel', 0);
        $this->addParameter('url_new_channel', $this->channelId+1);

    }


      /**
       * <p> Navigate to Channels->ManageChannels->Settings</p>
       * Verify Initializer
       */
    protected function Precondition() {
        $this->navigate('settings_channels');
        $init_message1 = $this->isTextPresent('Updating Channel information from X.commerce');
        $init_message2 = $this->isTextPresent('You will not be able to use the Channel functionality ' .
                'until this process is complete.');
        $init_message3 = $this->isTextPresent('This process may take some time.');
        if ($init_message1 == true and $init_message2 == true and $init_message3 == true){
            $this->_initializer_in_progress=true;
        }
    }
    /**
     * <p> Verify grid elements and buttons  </p>
     */
    public function test_manageChannels()
    {
        $this->Precondition();
        if ($this->_initializer_in_progress == true) {
            $this->markTestSkipped('Initializer work is not complete');
            return;
        }
        $this->assertElementPresent($this->_getControlXpath('field', 'filter_ebay_id'));
        $this->assertElementPresent($this->_getControlXpath('field', 'filter_policy'));
        $this->assertElementPresent($this->_getControlXpath('dropdown', 'filter_view'));
        $this->assertElementPresent($this->_getControlXpath('field', 'filter_channel'));
        $this->assertElementPresent($this->_getControlXpath('dropdown', 'filter_status'));
        $this->assertElementPresent($this->_getControlXpath('dropdown', 'view_per_page'));
        $this->assertElementPresent($this->_getControlXpath('button', 'search'));
        $this->assertElementPresent($this->_getControlXpath('button', 'reset_filter'));
        $this->assertElementPresent($this->_getControlXpath('button', 'add_a_channel'));
        $this->clickButton('add_a_channel');
    }
    /**
     * <p>Navigate to New Channel(eBay) page.</p>
     * @depends test_manageChannels
     */
    public function test_addeBayChannel() {
    /**
     * <p>Steps:</p>
     * <p>Press "Add a channel" button </p>
     * <p>Fill fields with data </p>
     */
        $this->assertElementPresent($this->_getControlXpath('field', 'name'));
        $this->assertElementPresent($this->_getControlXpath('dropdown', 'ebay_account'));
        $this->assertElementPresent($this->_getControlXpath('dropdown', 'store_view'));
        $this->assertElementPresent($this->_getControlXpath('dropdown', 'ebay_site'));
        $this->assertElementPresent($this->_getControlXpath('dropdown', 'status'));
        $this->assertElementPresent($this->_getControlXpath('button', 'save'));
        $ebay_channel_data = $this->loadData('ebay_channel');
        $this->fillForm($ebay_channel_data);
        $this->clickControl('button','save_and_continue_edit');
        $this->assertTrue($this->successMessage('success_channel_saved'));
    }
    /**
     *<p>Navigate to policy tab</p>
     *
     * @depends test_addeBayChannel
     */
    public function test_addeBayPolicy() {
        /**
         * <p>Steps:</p>
         * <p>Press "Add Policy" button <-p>
         * <p>Fill fields with data <-p>
         * <p>Press "Save policy" button<-p>
         * <p>Press "Save" button<-p>
         */
        $this->clickControl('tab', 'policy', false);
        $this->pleaseWait();
        $this->clickButton('add_policy', false);
        $this->assertElementPresent($this->_getControlXpath('field', 'name_policy'));
        $this->assertElementPresent($this->_getControlXpath('field', 'zip_location'));
        $this->assertElementPresent($this->_getControlXpath('dropdown', 'country'));
        $this->assertElementPresent($this->_getControlXpath('dropdown', 'currency'));
        $this->assertElementPresent($this->_getControlXpath('dropdown', 'handling_time'));
        $this->assertElementPresent($this->_getControlXpath('dropdown', 'return_accepted'));
        $this->assertElementPresent($this->_getControlXpath('dropdown', 'policy_status'));
        $this->assertElementPresent($this->_getControlXpath('checkboxe', 'payment_methods_amex'));
        $this->assertElementPresent($this->_getControlXpath('checkboxe', 'tax_rule'));
        $this->assertElementPresent($this->_getControlXpath('button', 'add_shipping'));
        $this->clickControl('link','general_tab', false);
        $this->clickControl('link','payment_tab', false);
        $this->clickControl('link','shipping_tab', false);
        $this->clickControl('link','return_tab', false);
        $ebay_policy_data = $this->loadData('ebay_policy');
        $this->fillForm($ebay_policy_data);
        $this->clickControl('button','add_shipping', false);
        $ebay_shipping_method = $this->loadData('shipping_method');
        $this->fillForm($ebay_shipping_method);
        $ebay_shipping_method_cost = $this->loadData('shipping_cost');
        $this->fillForm($ebay_shipping_method_cost);
        $this->clickControl('button','save_policy', false);
        $this->pleaseWait();
        $this->assertTrue($this->successMessage('success_policy_saved'));
        $this->saveForm('save');
        $this->assertTrue($this->successMessage('success_channel_saved'));
    }
}
