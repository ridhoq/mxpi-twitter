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
class XCom_Mmp_OrderManualPullTest extends Mage_Selenium_TestCase {

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
        $this->addParameter('debugid', 0);
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
     * <p> Input test data </p>
     * <p> Press "Manual: Pull Orders from eBay" button </p>
     */
    public function test_ManualOrderPull() {
        $this->Precondition();
        if ($this->_initializer_in_progress == true) {
            $this->markTestSkipped('Initializer work is not complete');
            return;
        }
        $this->navigate('system_configuration');
        $this->mmpHelper()->openSystemTab('xcommerce_channels');
        $this->assertElementPresent($this->_getControlXpath('button', 'manual_pull_orders'));
        $this->assertElementPresent($this->_getControlXpath('field', 'start_date'));
        $this->assertElementPresent($this->_getControlXpath('field', 'end_date'));
        $this->assertElementPresent($this->_getControlXpath('dropdown', 'start_time_hh'));
        $this->assertElementPresent($this->_getControlXpath('dropdown', 'start_time_ss'));
        $this->assertElementPresent($this->_getControlXpath('dropdown', 'end_time_hh'));
        $this->assertElementPresent($this->_getControlXpath('dropdown', 'end_time_mm'));
        $this->assertElementPresent($this->_getControlXpath('dropdown', 'end_time_ss'));
        $this->assertElementPresent($this->_getControlXpath('dropdown', 'end_time_ss'));
        $order_manual_pull_data = $this->loadData('order_manual_pull');
        $this->fillForm($order_manual_pull_data);
        $this->clickControl('button', 'manual_pull_orders', false);
        $this->pleaseWait();
        $this->assertMessagePresent('success', 'request_was_sent');
    }

    public function test_VerifyInDebug() {
        $this->navigate('xcommerce_debug');
        $this->assertElementPresent($this->_getControlXpath('field', 'debug_id'));
        $this->assertElementPresent($this->_getControlXpath('field', 'name'));
        $this->assertElementPresent($this->_getControlXpath('field', 'started_from'));
        $this->assertElementPresent($this->_getControlXpath('field', 'started_to'));
        $this->assertElementPresent($this->_getControlXpath('field', 'completed_from'));
        $this->assertElementPresent($this->_getControlXpath('field', 'completed_to'));

        /** @var $resource Mage_Core_Model_Resource */
        $resource = Mage::getSingleton('core/resource');
        $select = Mage::getSingleton('core/resource')->getConnection()
                        ->select()->from($resource->getTableName('xcom_xfabric/debug'), $cols = 'MAX(debug_id)');
        $this->debugid = Mage::getSingleton('core/resource')->getConnection()->fetchOne($select);

        $this->addParameter('debugid', $this->debugid);
        $this->clickControl('link', 'marketplace_order_search');
    }

    public function test_VerifyDebugNode() {
        $startDate = Mage::app()->getLocale()->utcDate(null, '03/15/12 02:55:15', true, 'MM/dd/YY HH:mm:ss');
        $endDate = Mage::app()->getLocale()->utcDate(null, '04/02/12 02:55:15', true, 'MM/dd/YY HH:mm:ss');

        $this->assertTextPresent('{"siteCode":"US","sellerAccountId":"123qwe","query":{"fields":null,' .
            '"predicates":[{"field":"dateOrdered","operator":"GREATER_THAN_EQUALS","values":' .
            '["' . $startDate->toString(Zend_Date::ATOM) . '"]},{"field":"dateOrdered","operator":"LESS_THAN_EQUALS","values":' .
            '["' . $endDate->toString(Zend_Date::ATOM) . '"]},{"field":"sourceId","operator":"EQUALS","values":["eBay"]}],' .
            '"ordering":null,"numberItems":null,"startItemIndex":null,"numberItemsFound":null}}');
    }
}
