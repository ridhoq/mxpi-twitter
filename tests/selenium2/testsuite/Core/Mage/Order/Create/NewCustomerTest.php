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
 * Creating order for new customer
 *
 * @package     selenium
 * @subpackage  tests
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class Core_Mage_Order_Create_NewCustomerTest extends Mage_Selenium_TestCase
{
    /**
     * <p>Preconditions:</p>
     * <p>Log in to Backend.</p>
     */
    public function setUpBeforeTests()
    {
        $this->loginAdminUser();
    }

    /**
     * <p>Creating Simple product</p>
     *
     * @return string
     * @test
     */
    public function preconditionsForTests()
    {
        //Data
        $simple = $this->loadDataSet('Product', 'simple_product_visible');
        //Steps
        $this->navigate('manage_products');
        $this->productHelper()->createProduct($simple);
        //Verification
        $this->assertMessagePresent('success', 'success_saved_product');

        return $simple['general_name'];
    }

    /**
     * <p>Create customer via 'Create order' without saving address</p>
     * <p>Create order.</p>
     * <p>Steps:</p>
     * <p>1.Go to Sales->Orders;</p>
     * <p>2.Press "Create New Order" button;</p>
     * <p>3.Press "Create New Customer" button;</p>
     * <p>4.Choose Store View;</p>
     * <p>5.Press 'Add Products' button;</p>
     * <p>6.Add product;</p>
     * <p>7.Fill in billing address(not check 'save in address book');</p>
     * <p>8.Choose in shipping address the same as billing
     *      (not check 'save in address book');</p>
     * <p>9.Check shipping method;</p>
     * <p>10.Check payment method 'Check / Money order';</p>
     * <p>11. Submit order;</p>
     * <p>Expected result:</p>
     * <p>New customer successfully created without address.
     *    Order is created for the new customer</p>
     *
     * @param string $simpleSku
     *
     * @test
     * @depends preconditionsForTests
     * @TestlinkId	TL-MAGE-3265
     * @group skip_due_to_bug
     */
    public function newCustomerWithoutAddress($simpleSku)
    {
        //Data
        $orderData = $this->loadDataSet('SalesOrder', 'order_physical',
                                        array('filter_sku'     => $simpleSku,
                                              'customer_email' => $this->generate('email', 32, 'valid')));
        $param = $orderData['billing_addr_data']['billing_first_name']
            . ' ' . $orderData['billing_addr_data']['billing_last_name'];
        $this->addParameter('customer_first_last_name', $param);
        $searchCustomer = $this->loadDataSet('Customers', 'search_customer',
                                             array('email' => $orderData['account_data']['customer_email']));
        //Steps
        $this->navigate('manage_sales_orders');
        $this->orderHelper()->createOrder($orderData);
        //Verifying
        $this->assertMessagePresent('success', 'success_created_order');
        //Steps
        $this->navigate('manage_customers');
        $this->customerHelper()->openCustomer($searchCustomer);
        $this->openTab('addresses');
        $xpath = $this->_getControlXpath('fieldset', 'list_customer_addresses') . '//li';
        $addressCount = $this->getXpathCount($xpath);
        $this->assertEquals(0, $addressCount, 'Customer should not have address, but have ' . $addressCount);
    }

    /**
     * <p>Create customer via 'Create order' with saving address</p>
     * <p>Create order.</p>
     * <p>Steps:</p>
     * <p>1.Go to Sales->Orders;</p>
     * <p>2.Press "Create New Order" button;</p>
     * <p>3.Press "Create New Customer" button;</p>
     * <p>4.Choose Store View;</p>
     * <p>5.Press 'Add Products' button;</p>
     * <p>6.Add product;</p>
     * <p>7.Fill in billing address(check 'save in address book');</p>
     * <p>8.Choose in shipping address the same as billing(check 'save in address book');</p>
     * <p>9.Check shipping method;</p>
     * <p>10.Check payment method 'Check / Money order';</p>
     * <p>11. Submit order;</p>
     * <p>Expected result:</p>
     * <p>New customer is created with addresses. Order is created for the new customer;</p>
     *
     * @param string $simpleSku
     *
     * @test
     * @depends preconditionsForTests
     * @TestlinkId	TL-MAGE-3260
     * @group skip_due_to_bug
     */
    public function newCustomerWithAddress($simpleSku)
    {
        //Data
        $orderData = $this->loadDataSet('SalesOrder', 'order_physical',
                                     array('filter_sku'     => $simpleSku,
                                           'customer_email' => $this->generate('email', 32, 'valid')));
        $orderData['billing_addr_data'] = $this->loadDataSet('SalesOrder', 'billing_address_all');
        $orderData['shipping_addr_data'] = $this->loadDataSet('SalesOrder', 'shipping_address_all');
        $param = $orderData['billing_addr_data']['billing_first_name']
            . ' ' . $orderData['billing_addr_data']['billing_last_name'];
        $this->addParameter('customer_first_last_name', $param);
        $searchCustomer = $this->loadDataSet('Customers', 'search_customer',
                                          array('email' => $orderData['account_data']['customer_email']));
        $addressVerify[] = $this->loadDataSet('SalesOrder', 'billing');
        $addressVerify[] = $this->loadDataSet('SalesOrder', 'shipping');
        //Steps
        $this->navigate('manage_sales_orders');
        $this->orderHelper()->createOrder($orderData);
        //Verifying
        $this->assertMessagePresent('success', 'success_created_order');
        //Steps
        $this->navigate('manage_customers');
        $this->customerHelper()->openCustomer($searchCustomer);
        $this->openTab('addresses');
        foreach ($addressVerify as $value) {
            $addressNumber = $this->customerHelper()->isAddressPresent($value);
            $this->assertNotEquals(0, $addressNumber, 'The specified address is not present.');
        }
    }

    /**
     * <p>Create customer via 'Create order' form (use exist email).</p>
     * <p>Create order.</p>
     * <p>Steps:</p>
     * <p>1.Go to Sales->Orders;</p>
     * <p>2.Press "Create New Order" button;</p>
     * <p>3.Press "Create New Customer" button;</p>
     * <p>4.Choose Store View;</p>
     * <p>5.Press 'Add Products' button;</p>
     * <p>6.Add product;</p>
     * <p>7.Fill in billing address;</p>
     * <p>8.Choose in shipping address the same as billing;</p>
     * <p>9.Check shipping method;</p>
     * <p>10.Check payment method 'Check / Money order';</p>
     * <p>11. Submit order;</p>
     * <p>Expected result:</p>
     * <p>New customer is not created. Order is not created for the new customer;</p>
     *
     * @param string $simpleSku
     *
     * @test
     * @depends preconditionsForTests
     * @TestlinkId	TL-MAGE-3261
     */
    public function newCustomerWithExistEmail($simpleSku)
    {
        //Data
        $userData = $this->loadDataSet('Customers', 'generic_customer_account');
        $orderData = $this->loadDataSet('SalesOrder', 'order_newcustomer_checkmoney_flatrate_usa',
                                     array('filter_sku'     => $simpleSku,
                                           'customer_email' => $userData['email']));
        //Steps
        $this->navigate('manage_customers');
        $this->customerHelper()->createCustomer($userData);
        //Verifying
        $this->assertMessagePresent('success', 'success_saved_customer');
        //Steps
        $this->navigate('manage_sales_orders');
        $this->orderHelper()->createOrder($orderData);
        //Verifying
        $this->assertMessagePresent('error', 'customer_email_already_exists');
    }

    /**
     * <p>Create customer via 'Create order' form (use long email).</p>
     * <p>Create order.</p>
     * <p>Steps:</p>
     * <p>1.Go to Sales->Orders;</p>
     * <p>2.Press "Create New Order" button;</p>
     * <p>3.Press "Create New Customer" button;</p>
     * <p>4.Choose Store View;</p>
     * <p>5.Press 'Add Products' button;</p>
     * <p>6.Add product;</p>
     * <p>7.Fill in billing address;</p>
     * <p>8.Choose in shipping address the same as billing;</p>
     * <p>9.Check shipping method;</p>
     * <p>10.Check payment method 'Check / Money order';</p>
     * <p>11. Submit order;</p>
     * <p>Expected result:</p>
     * <p>New customer is not created. Order is not created for the new customer;</p>
     *
     * @param string $simpleSku
     *
     * @test
     * @depends preconditionsForTests
     * @TestlinkId	TL-MAGE-3262
     */
    public function newCustomerWithLongEmail($simpleSku)
    {
        //Data
        $email = $this->generate('string', 129, ':alnum:') . '@example.com';
        $orderData = $this->loadDataSet('SalesOrder', 'order_newcustomer_checkmoney_flatrate_usa',
                                     array('filter_sku'     => $simpleSku,
                                           'customer_email' => $email));
        //Steps
        $this->navigate('manage_sales_orders');
        $this->orderHelper()->createOrder($orderData);
        //Verifying
        $this->assertMessagePresent('error', 'email_exceeds_allowed_length');
    }

    /**
     * <p>Create customer via 'Create order' form (not correct email).</p>
     * <p>Create order.</p>
     * <p>Steps:</p>
     * <p>1.Go to Sales->Orders;</p>
     * <p>2.Press "Create New Order" button;</p>
     * <p>3.Press "Create New Customer" button;</p>
     * <p>4.Choose Store View;</p>
     * <p>5.Press 'Add Products' button;</p>
     * <p>6.Add product;</p>
     * <p>7.Fill in billing address;</p>
     * <p>8.Choose in shipping address the same as billing;</p>
     * <p>9.Check shipping method;</p>
     * <p>10.Check payment method 'Check / Money order';</p>
     * <p>11. Submit order;</p>
     * <p>Expected result:</p>
     * <p>New customer is not created. Order is not created for the new customer;</p>
     *
     * @param string $simpleSku
     *
     * @test
     * @depends preconditionsForTests
     * @TestlinkId TL-MAGE-3263
     */
    public function newCustomerWithNotCorrectEmail($simpleSku)
    {
        //Data
        $email = $this->generate('string', 23, ':alnum:') . '@'
            . $this->generate('string', 65, ':alnum:') . '.org';
        $orderData = $this->loadDataSet('SalesOrder', 'order_newcustomer_checkmoney_flatrate_usa',
                                     array('filter_sku'     => $simpleSku,
                                           'customer_email' => $email));
        //Steps
        $this->navigate('manage_sales_orders');
        $this->orderHelper()->createOrder($orderData);
        //Verifying
        $this->assertMessagePresent('error', 'email_is_not_valid_hostname');
        $this->assertMessagePresent('error', 'not_valid_hostname');
        $this->assertMessagePresent('error', 'hostname_not_valid');
    }

    /**
     * <p>Create customer via 'Create order' form</p>
     * <p>Create order.</p>
     * <p>Steps:</p>
     * <p>1.Go to Sales->Orders;</p>
     * <p>2.Press "Create New Order" button;</p>
     * <p>3.Press "Create New Customer" button;</p>
     * <p>4.Choose Store View;</p>
     * <p>5.Press 'Add Products' button;</p>
     * <p>6.Add product;</p>
     * <p>7.Fill in billing address;</p>
     * <p>8.Choose in shipping address the same as billing;</p>
     * <p>9.Check shipping method;</p>
     * <p>10.Check payment method 'Check / Money order';</p>
     * <p>11. Submit order;</p>
     * <p>Expected result:</p>
     * <p>New customer is not created. Order is not created for the new customer;</p>
     *
     * @param string $simpleSku
     *
     * @test
     * @depends preconditionsForTests.
     * @TestlinkId	TL-MAGE-3264
     */
    public function newCustomerWithNotValidEmail($simpleSku)
    {
        //Data
        $orderData = $this->loadDataSet('SalesOrder', 'order_newcustomer_checkmoney_flatrate_usa',
                                        array('filter_sku'     => $simpleSku,
                                              'customer_email' => $this->generate('email', 20, 'invalid')));
        //Steps
        $this->navigate('manage_sales_orders');
        $this->orderHelper()->createOrder($orderData);
        //Verifying
        $this->assertMessagePresent('validation', 'not_valid_email');
        $this->assertTrue($this->verifyMessagesCount(), $this->getParsedMessages());
    }

    /**
     * <p>Create customer via 'Create order' form.</p>
     * <p>Create order(all fields are filled).</p>
     * <p>Steps:</p>
     * <p>1.Go to Sales->Orders;</p>
     * <p>2.Press "Create New Order" button;</p>
     * <p>3.Press "Create New Customer" button;</p>
     * <p>4.Choose Store View</p>
     * <p>5.Press 'Add Products' button;</p>
     * <p>6.Add product;</p>
     * <p>7.Fill in billing address(use long values);</p>
     * <p>8.Fill in shipping address(use long values);</p>
     * <p>9.Check shipping method;</p>
     * <p>10.Check payment method 'Check / Money order';</p>
     * <p>11. Submit order;</p>
     * <p>Expected result:</p>
     * <p>New customer is not created. Order is not created for the new customer;</p>
     *
     * @param string $simpleSku
     *
     * @test
     * @depends preconditionsForTests
     * @TestlinkId	TL-MAGE-3266
     */
    public function orderCompleteReqFields($simpleSku)
    {
        //Data
        $orderData = $this->loadDataSet('SalesOrder', 'order_newcustomer_checkmoney_flatrate_usa',
                                     array('filter_sku' => $simpleSku));
        $orderData['billing_addr_data'] = $this->orderHelper()->customerAddressGenerator(':alnum:', 'billing', 255);
        $orderData['shipping_addr_data'] = $this->orderHelper()->customerAddressGenerator(':alnum:', 'shipping', 255);
        //Steps
        $this->navigate('manage_sales_orders');
        $this->orderHelper()->createOrder($orderData);
        //Verifying
        $this->assertMessagePresent('success', 'success_created_order');
    }
}