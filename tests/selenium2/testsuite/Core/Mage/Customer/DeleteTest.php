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
 * Test deletion customer.
 *
 * @package     selenium
 * @subpackage  tests
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class Core_Mage_Customer_DeleteTest extends Mage_Selenium_TestCase
{
    /**
     * <p>Log in to Backend.</p>
     */
    public function setUpBeforeTests()
    {
        $this->loginAdminUser();
    }

    /**
     * <p>Preconditions:</p>
     * <p>Navigate to System -> Manage Customers</p>
     */
    protected function assertPreConditions()
    {
        $this->navigate('manage_customers');
        $this->addParameter('id', '0');
    }

    /**
     * <p>Delete customer.</p>
     * <p>Preconditions: Create Customer</p>
     * <p>Steps:</p>
     * <p>1. Search and open customer.</p>
     * <p>2. Click 'Delete Customer' button.</p>
     * <p>Expected result:</p>
     * <p>Customer is deleted.</p>
     * <p>Success Message is displayed.</p>
     *
     * @test
     * @TestlinkId	TL-MAGE-3237
     */
    public function single()
    {
        //Data
        $userData = $this->loadData('generic_customer_account', null, 'email');
        $searchData = $this->loadData('search_customer', array('email' => $userData['email']));
        //Preconditions
        $this->customerHelper()->createCustomer($userData);
        $this->assertMessagePresent('success', 'success_saved_customer');
        //Steps
        $param = $userData['first_name'] .' '.$userData['last_name'];
        $this->addParameter('customer_first_last_name', $param);
        $this->customerHelper()->openCustomer($searchData);
        $this->clickButtonAndConfirm('delete_customer', 'confirmation_for_delete');
        //Verifying
        $this->assertMessagePresent('success', 'success_deleted_customer');
    }

    /**
     * <p>Delete customers.</p>
     * <p>Preconditions: Create several customers</p>
     * <p>Steps:</p>
     * <p>1. Search and choose several customers.</p>
     * <p>3. Select 'Actions' to 'Delete'.</p>
     * <p>2. Click 'Submit' button.</p>
     * <p>Expected result:</p>
     * <p>Customers are deleted.</p>
     * <p>Success Message is displayed.</p>
     *
     * @test
     * @TestlinkId	TL-MAGE-3238
     */
    public function throughMassAction()
    {
        $customerQty = 2;
        for ($i = 1; $i <= $customerQty; $i++) {
            //Data
            $userData = $this->loadData('generic_customer_account', null, 'email');
            ${'searchData' . $i} = $this->loadData('search_customer', array('email' => $userData['email']));
            //Steps
            $this->customerHelper()->createCustomer($userData);
            $this->assertMessagePresent('success', 'success_saved_customer');
        }
        for ($i = 1; $i <= $customerQty; $i++) {
            $this->searchAndChoose(${'searchData' . $i});
        }
        $this->addParameter('qtyDeletedCustomers', $customerQty);
        $xpath = $this->_getControlXpath('dropdown', 'grid_massaction_select');
        $this->select($xpath, 'Delete');
        $this->clickButtonAndConfirm('submit', 'confirmation_for_massaction_delete');
        //Verifying
        $this->assertMessagePresent('success', 'success_deleted_customer_massaction');
    }
}