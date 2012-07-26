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
 * Tests for Checkout with Multiple Addresses. Frontend
 *
 * @package     selenium
 * @subpackage  tests
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class Core_Mage_CheckoutMultipleAddresses_LoggedIn_InputDataValidationTest extends Mage_Selenium_TestCase
{
    /**
     * <p>Creating Simple product</p>
     *
     * @test
     * @return array $productData
     */
    public function preconditionsCreateProduct()
    {
        //Data
        $productData = $this->loadData('simple_product_visible');
        //Steps
        $this->loginAdminUser();
        $this->navigate('manage_products');
        $this->productHelper()->createProduct($productData);
        //Verification
        $this->assertMessagePresent('success', 'success_saved_product');
        return $productData;
    }

    /**
     * <p>Create Customer</p>
     *
     * @test
     * @return array $userData
     */
    public function preconditionsCreateCustomer()
    {
        //Data
        $userData = $this->loadData('all_fields_customer_account', NULL, 'email');
        $addressData = $this->loadData('all_fields_address');
        //Steps
        $this->loginAdminUser();
        $this->navigate('manage_customers');
        $this->customerHelper()->createCustomer($userData, $addressData);
        //Verifying
        $this->assertMessagePresent('success', 'success_saved_customer');
        return array('email' => $userData['email'], 'password' => $userData['password']);
    }

    /**
     * <p>Shipping Methods Configuration in backend</p>
     *
     * @test
     */
    public function preconditionsConfigureShippingMethods()
    {
        $this->admin();
        $this->navigate('system_configuration');
        $this->systemConfigurationHelper()->configure('free_enable');
        $this->systemConfigurationHelper()->configure('ups_enable');
    }

################################################################################
#                                                                              #
#                     Select Addresses Page                                    #
#                                                                              #
################################################################################

    /**
     * <p>Empty required fields(Select Addresses page)</p>
     * <p>Preconditions:</p>
     * <p>1.Product is created.</p>
     * <p>3.Customer signed in at the frontend.</p>
     * <p>Steps:</p>
     * <p>1. Open product page.</p>
     * <p>2. Add product to Shopping Cart.</p>
     * <p>3. Click "Checkout with Multiple Addresses".</p>
     * <p>4. Click "Enter a New Address" on Select Addresses page.</p>
     * <p>5. Fill in fields except one required.</p>
     * <p>6. Click 'Submit' button</p>
     * <p>Expected result:</p>
     * <p>New address is not added.</p>
     * <p>Error Message is displayed.</p>
     *
     * @param string $emptyField
     * @param string $fieldType
     * @param array $customerData
     * @param array $productData
     *
     * @test
     * @dataProvider createShippingAddressEmptyRequiredFieldsDataProvider
     * @depends preconditionsCreateCustomer
     * @depends preconditionsCreateProduct
     * @TestlinkId TL-MAGE-5269
     */
    public function createShippingAddressEmptyRequiredFields($emptyField, $fieldType, $customerData, $productData)
    {
        //Data
        $generalShippingAddress = $this->loadData('multiple_shipping_new_signedin_req', array ($emptyField => ''));
        //Steps
        $this->frontend();
        $this->customerHelper()->frontLoginCustomer($customerData);
        $this->shoppingCartHelper()->frontClearShoppingCart();
        $this->productHelper()->frontOpenProduct($productData['general_name']);
        $this->productHelper()->frontAddProductToCart();
        $this->clickControl('link', 'checkout_with_multiple_addresses');
        $this->clickButton('add_new_address');
        $currentPage = $this->getCurrentPage();
        if ($currentPage == 'checkout_multishipping_add_new_address' ||
            $currentPage == 'checkout_multishipping_register') {
            $this->fillForm($generalShippingAddress);
            $this->clickButton('save_address', false);
        }
        //Verification
        $this->addFieldIdToMessage($fieldType, $emptyField);
        if ($fieldType == 'dropdown') {
            $this->assertMessagePresent('validation', 'please_select_option');
        } else {
        $this->assertMessagePresent('validation', 'empty_required_field');
        }
    }

    /**
     * Data provider for createShippingAddressEmptyRequiredFields test
     *
     * @return array
     */
    public function createShippingAddressEmptyRequiredFieldsDataProvider()
    {
        return array(
            array('shipping_first_name', 'field'),//First Name
            array('shipping_last_name', 'field'),//Last Name
            array('shipping_telephone', 'field'),//Telephone
            array('shipping_street_address_1', 'field'),//Street Address
            array('shipping_city', 'field'),//City
            array('shipping_state', 'dropdown'),//State/Province
            array('shipping_zip_code', 'field'),//Zip/Postal Code
            array('shipping_country', 'dropdown')//Country
        );
    }

    /**
     * <p>Fill in all required fields by using special characters</p>
     * <p>Preconditions:</p>
     * <p>1.Product is created.</p>
     * <p>3.Customer signed in at the frontend.</p>
     * <p>Steps:</p>
     * <p>1. Open product page.</p>
     * <p>2. Add product to Shopping Cart.</p>
     * <p>3. Click "Checkout with Multiple Addresses".</p>
     * <p>4. Click "Enter a New Address" on Select Addresses page.</p>
     * <p>5. Fill in all required fields by using special characters(except the field "email")</p>
     * <p>6. Click 'Submit' button</p>
     * <p>Expected result:</p>
     * <p>New address is added.</p>
     * <p>Success Message is displayed.(The address has been saved.)</p>
     *
     * @param array $customerData
     * @param array $productData
     *
     * @test
     * @depends preconditionsCreateCustomer
     * @depends preconditionsCreateProduct
     * @TestlinkId TL-MAGE-5271
     */
    public function createShippingAddressSpecialChars($customerData, $productData) //Enter New Address page
    {
        //Data
        $generalShippingAddress = $this->loadData('multiple_shipping_new_signedin_req', array(
            'shipping_first_name' => $this->generate('string', 32, ':punct:'),
            'shipping_last_name' => $this->generate('string', 32, ':punct:'),
            'shipping_company' => $this->generate('string', 32, ':punct:'),
            'shipping_telephone' => $this->generate('string', 32, ':punct:'),
            'shipping_street_address_1' => $this->generate('string', 32, ':punct:'),
            'shipping_street_address_2' => $this->generate('string', 32, ':punct:'),
            'shipping_city' => $this->generate('string', 32, ':punct:'),
            'shipping_zip_code' => $this->generate('string', 32, ':punct:'),
            'shipping_fax' => $this->generate('string', 32, ':punct:'),
            ));
        $checkoutData = $this->loadData('multiple_invalid_data_ship_address');
        $checkoutData['shipping_address_data']['address_to_add_1']['shipping_address'] = $generalShippingAddress;
        $checkoutData['products_to_add']['product_1']['general_name'] = $productData['general_name'];
        //Steps
        $this->frontend();
        $this->customerHelper()->frontLoginCustomer($customerData);
        $this->shoppingCartHelper()->frontClearShoppingCart();
        $this->productHelper()->frontOpenProduct($productData['general_name']);
        $this->productHelper()->frontAddProductToCart();
        $this->clickControl('link', 'checkout_with_multiple_addresses');
        $this->clickButton('add_new_address');
        $currentPage = $this->getCurrentPage();
        if ($currentPage == 'checkout_multishipping_add_new_address' ||
            $currentPage == 'checkout_multishipping_register') {
            $this->fillForm($generalShippingAddress);
            $this->clickButton('save_address');
        }
        //Verification
        $this->assertMessagePresent('success', 'success_saved_address');
    }

    /**
     * <p>Fill in only required fields. Use max long values for fields.</p>
     * <p>Steps:</p>
     * <p>1. Open product page.</p>
     * <p>2. Add product to Shopping Cart.</p>
     * <p>3. Click "Checkout with Multiple Addresses".</p>
     * <p>4. Click "Enter a New Address" on Select Addresses page.</p>
     * <p>5. Fill in required fields by long value alpha-numeric data.</p>
     * <p>6. Click 'Submit' button.</p>
     * <p>Expected result:</p>
     * <p>New address is added.</p>
     * <p>Success Message is displayed.(The address has been saved.)</p>
     *
     * @param array $customerData
     * @param array $productData
     *
     * @test
     * @depends preconditionsCreateCustomer
     * @depends preconditionsCreateProduct
     * @TestlinkId	TL-MAGE-5272
     */
    public function createShippingAddressLongValues($customerData, $productData) //Enter New Address
    {
        //Data
        $generalShippingAddress = $this->loadData('multiple_shipping_new_signedin_req', array(
            'shipping_first_name' => $this->generate('string', 255, ':alnum:'),
            'shipping_last_name' => $this->generate('string', 255, ':alnum:'),
            'shipping_company' => $this->generate('string', 255, ':alnum:'),
            'shipping_telephone' => $this->generate('string', 255, ':alnum:'),
            'shipping_street_address_1' => $this->generate('string', 255, ':alnum:'),
            'shipping_street_address_2' => $this->generate('string', 255, ':alnum:'),
            'shipping_city' => $this->generate('string', 255, ':alnum:'),
            'shipping_zip_code' => $this->generate('string', 255, ':alnum:'),
            'shipping_fax' => $this->generate('string', 255, ':alnum:'),
            ));
        $checkoutData = $this->loadData('multiple_invalid_data_ship_address');
        $checkoutData['shipping_address_data']['address_to_add_1']['shipping_address'] = $generalShippingAddress;
        $checkoutData['products_to_add']['product_1']['general_name'] = $productData['general_name'];
        //Steps
        $this->frontend();
        $this->customerHelper()->frontLoginCustomer($customerData);
        $this->shoppingCartHelper()->frontClearShoppingCart();
        $this->productHelper()->frontOpenProduct($productData['general_name']);
        $this->productHelper()->frontAddProductToCart();
        $this->clickControl('link', 'checkout_with_multiple_addresses');
        $this->clickButton('add_new_address');
        $currentPage = $this->getCurrentPage();
        if ($currentPage == 'checkout_multishipping_add_new_address' ||
            $currentPage == 'checkout_multishipping_register') {
            $this->fillForm($generalShippingAddress);
            $this->clickButton('save_address');
        }
        //Verification
        $this->assertMessagePresent('success', 'success_saved_address');
    }


    /**
     * <p>Fill in only required fields. Use max long values for fields.</p>
     * <p>Steps:</p>
     * <p>1. Open product page.</p>
     * <p>2. Add product to Shopping Cart.</p>
     * <p>3. Click "Checkout with Multiple Addresses".</p>
     * <p>4. Fill in Qty with invalid value(negative, non-integer)</p>
     * <p>6. Click 'Submit' button.</p>
     * <p>Expected result:</p>
     * <p>Product removed from Shoping cart</p>
     * <p>TODO MAGE-5312</p>
     *
     * @param string $invalidQty
     * @param array $customerData
     * @param array $productData
     *
     * @test
     * @dataProvider selectAddressesPageInvalidQtyDataProvider
     * @depends preconditionsCreateCustomer
     * @depends preconditionsCreateProduct
     * @TestlinkId	TL-MAGE-5273
     */
    public function selectAddressesPageInvalidQty($invalidQty, $customerData, $productData) //Enter New Address
    {
        //Data
        $checkoutData = $this->loadData('multiple_exist_flatrate_checkmoney', array('checkout_as_customer' => NULL,
            'qty' => $invalidQty));
        $checkoutData['shipping_address_data']['address_to_ship_1']['qty'] = $invalidQty;
        $checkoutData['products_to_add']['product_1']['general_name'] = $productData['general_name'];
        //Steps
        $this->frontend();
        $this->customerHelper()->frontLoginCustomer($customerData);
        $this->shoppingCartHelper()->frontClearShoppingCart();
        $this->productHelper()->frontOpenProduct($productData['general_name']);
        $this->productHelper()->frontAddProductToCart();
        $this->clickControl('link', 'checkout_with_multiple_addresses');
        $this->fillForm(array('qty' => $invalidQty));
        $this->clickButton('continue_to_shipping_information');
        //Verification
        $this->assertMessagePresent('success', 'shopping_cart_is_empty');
    }

    /**
     * Data provider for selectAddressesPageInvalidQty test
     *
     * @return array
     */
    public function selectAddressesPageInvalidQtyDataProvider()
    {
        return array(
            array('-10'),//negative
            array($this->generate('string', 3, ':alpha:'))//non-integer
        );
    }

################################################################################
#                                                                              #
#                     Shipping Information Page                                #
#                                                                              #
################################################################################

    /**
     * <p>Shipping Method is not selected</p>
     * <p>Steps:</p>
     * <p>1. Open product page.</p>
     * <p>2. Add product to Shopping Cart.</p>
     * <p>3. Click "Checkout with Multiple Addresses".</p>
     * <p>4. Move to the Shipping Information Page</p>
     * <p>5. Leave Shipping Method unselected</p>
     * <p>6. Click 'Continue to Billing Information' button.</p>
     * <p>Expected result:</p>
     * <p>Error Message is displayed.
     * <p>(Please select shipping methods for all addresses)</p>
     *
     * @param array $customerData
     * @param array $productData
     *
     * @test
     * @depends preconditionsCreateCustomer
     * @depends preconditionsCreateProduct
     * @depends preconditionsConfigureShippingMethods
     * @TestlinkId	TL-MAGE-5274
     */
    public function shippingMethodNotSelected($customerData, $productData)
    {
        //Data
        $checkoutData = $this->loadData('multiple_empty_data_ship_address', array('checkout_as_customer' => null));
        $checkoutData['products_to_add']['product_1']['general_name'] = $productData['general_name'];
        $checkoutData['shipping_address_data']['address_to_ship_1']['general_name'] = $productData['general_name'];
        //Steps
        $this->frontend();
        $this->customerHelper()->frontLoginCustomer($customerData);
        $this->shoppingCartHelper()->frontClearShoppingCart();
        $this->checkoutMultipleAddressesHelper()->frontCreateMultipleCheckout($checkoutData, false);
        //Verification
        $this->assertMessagePresent('validation', 'empty_shipping_methods_for_all_addresses');
    }

################################################################################
#                                                                              #
#                     Billing Information Page                                 #
#                                                                              #
################################################################################

    /**
     * <p>Empty required fields(Select Addresses page)</p>
     * <p>Preconditions:</p>
     * <p>1.Product is created.</p>
     * <p>3.Customer signed in at the frontend.</p>
     * <p>Steps:</p>
     * <p>1. Open product page.</p>
     * <p>2. Add product to Shopping Cart.</p>
     * <p>3. Click "Checkout with Multiple Addresses".</p>
     * <p>4. Move To " Billing Information Page".</p>
     * <p>5. Click "Add Address"</p>
     * <p>6. Fill in fields except one required.</p>
     * <p>7. Click 'Submit' button</p>
     * <p>Expected result:</p>
     * <p>New address is not added.</p>
     * <p>Error Message is displayed.</p>
     *
     * @param string $emptyBillingField
     * @param string $fieldType
     * @param array $customerData
     * @param array $productData
     *
     * @test
     * @dataProvider createBillingAddressEmptyRequiredFieldsDataProvider
     * @depends preconditionsCreateCustomer
     * @depends preconditionsCreateProduct
     * @TestlinkId TL-MAGE-5275
     */
    public function createBillingAddressEmptyRequiredFields($emptyBillingField, $fieldType, $customerData, $productData)
    {
        //Data
        $checkoutData = $this->loadData('multiple_invalid_data_billing_address', array ($emptyBillingField => ''));
        $checkoutData['shipping_address_data']['address_to_ship_1']['general_name'] = $productData['general_name'];
        $checkoutData['products_to_add']['product_1']['general_name'] = $productData['general_name'];
        //Steps
        $this->frontend();
        $this->customerHelper()->frontLoginCustomer($customerData);
        $this->shoppingCartHelper()->frontClearShoppingCart();
        $this->checkoutMultipleAddressesHelper()->frontCreateMultipleCheckout($checkoutData, false);
        //Verification
        $this->addFieldIdToMessage($fieldType, $emptyBillingField);
        if ($fieldType == 'dropdown') {
            $this->assertMessagePresent('validation', 'please_select_option');
        } else {
        $this->assertMessagePresent('validation', 'empty_required_field');
        }
    }

    /**
     * Data provider for createBillingAddressEmptyRequiredFields test
     *
     * @return array
     */
    public function createBillingAddressEmptyRequiredFieldsDataProvider()
    {
        return array(
            array('billing_first_name', 'field'),//First Name
            array('billing_last_name', 'field'),//Last Name
            array('billing_telephone', 'field'),//Telephone
            array('billing_street_address_1', 'field'),//Street Address
            array('billing_city', 'field'),//City
            array('billing_state', 'dropdown'),//State/Province
            array('billing_zip_code', 'field'),//Zip/Postal Code
            array('billing_country', 'dropdown')//Country
        );
    }

    /**
     * <p>Fill in all required fields by using special characters</p>
     * <p>Preconditions:</p>
     * <p>1.Product is created.</p>
     * <p>3.Customer signed in at the frontend.</p>
     * <p>Steps:</p>
     * <p>1. Open product page.</p>
     * <p>2. Add product to Shopping Cart.</p>
     * <p>3. Click "Checkout with Multiple Addresses".</p>
     * <p>4. Move To " Billing Information Page".</p>
     * <p>5. Click "Add Address"</p>
     * <p>6. Fill in all required fields by using special characters(except the field "email")</p>
     * <p>7. Click 'Submit' button</p>
     * <p>Expected result:</p>
     * <p>New address is added.</p>
     * <p>Success Message is displayed.(The address has been saved.)</p>
     *
     * @param array $customerData
     * @param array $productData
     *
     * @test
     * @depends preconditionsCreateCustomer
     * @depends preconditionsCreateProduct
     * @TestlinkId	TL-MAGE-5276
     */
    public function createBillingAddressSpecialChars($customerData, $productData) //Enter New Address page
    {
        //Data
        $checkoutData = $this->loadData('multiple_invalid_data_bill_address', array(
                    'checkout_as_customer' => null,
                    'billing_first_name' => $this->generate('string', 255, ':punct:'),
                    'billing_last_name' => $this->generate('string', 255, ':punct:'),
                    'billing_company' => $this->generate('string', 255, ':punct:'),
                    'billing_telephone' => $this->generate('string', 255, ':punct:'),
                    'billing_street_address_1' => $this->generate('string', 255, ':punct:'),
                    'billing_street_address_2' => $this->generate('string', 255, ':punct:'),
                    'billing_city' => $this->generate('string', 255, ':punct:'),
                    'billing_zip_code' => $this->generate('string', 255, ':punct:'),
                    'billing_fax' => $this->generate('string', 255, ':punct:'),
                    ));;
        $checkoutData['shipping_address_data']['address_to_ship_1']['general_name'] = $productData['general_name'];
        $checkoutData['products_to_add']['product_1']['general_name'] = $productData['general_name'];
        //Steps
        $this->frontend();
        $this->customerHelper()->frontLoginCustomer($customerData);
        $this->shoppingCartHelper()->frontClearShoppingCart();
        $this->checkoutMultipleAddressesHelper()->frontCreateMultipleCheckout($checkoutData, false);
        //Verification
        $this->assertMessagePresent('success', 'success_saved_address');
    }

    /**
     * <p>Fill in only required fields. Use max long values for fields.</p>
     * <p>Steps:</p>
     * <p>1. Open product page.</p>
     * <p>2. Add product to Shopping Cart.</p>
     * <p>3. Click "Checkout with Multiple Addresses".</p>
     * <p>4. Move To " Billing Information Page".</p>
     * <p>5. Click "Add Address"</p>
     * <p>6. Fill in required fields by long value alpha-numeric data.</p>
     * <p>7. Click 'Submit' button.</p>
     * <p>Expected result:</p>
     * <p>New address is added.</p>
     * <p>Success Message is displayed.(The address has been saved.)</p>
     *
     * @param array $customerData
     * @param array $productData
     *
     * @test
     * @depends preconditionsCreateCustomer
     * @depends preconditionsCreateProduct
     * @TestlinkId	TL-MAGE-5277
     */
    public function createBillingAddressLongValues($customerData, $productData) //Enter New Address
    {
        //Data
        $checkoutData = $this->loadData('multiple_invalid_data_bill_address', array(
                    'checkout_as_customer' => null,
                    'billing_first_name' => $this->generate('string', 255, ':alnum:'),
                    'billing_last_name' => $this->generate('string', 255, ':alnum:'),
                    'billing_company' => $this->generate('string', 255, ':alnum:'),
                    'billing_telephone' => $this->generate('string', 255, ':alnum:'),
                    'billing_street_address_1' => $this->generate('string', 255, ':alnum:'),
                    'billing_street_address_2' => $this->generate('string', 255, ':alnum:'),
                    'billing_city' => $this->generate('string', 255, ':alnum:'),
                    'billing_zip_code' => $this->generate('string', 255, ':alnum:'),
                    'billing_fax' => $this->generate('string', 255, ':alnum:'),
                    ));;
        $checkoutData['shipping_address_data']['address_to_ship_1']['general_name'] = $productData['general_name'];
        $checkoutData['products_to_add']['product_1']['general_name'] = $productData['general_name'];
        //Steps
        $this->frontend();
        $this->customerHelper()->frontLoginCustomer($customerData);
        $this->shoppingCartHelper()->frontClearShoppingCart();
        $this->checkoutMultipleAddressesHelper()->frontCreateMultipleCheckout($checkoutData, false);
        //Verification
        $this->assertMessagePresent('success', 'success_saved_address');
    }

    /**
     * <p>Payment Method is not selected</p>
     * <p>Steps:</p>
     * <p>1. Open product page.</p>
     * <p>2. Add product to Shopping Cart.</p>
     * <p>3. Click "Checkout with Multiple Addresses".</p>
     * <p>4. Move to the Billing Information Page</p>
     * <p>5. Leave Payment Method unselected</p>
     * <p>6. Click 'Continue to Review Your Order' button.</p>
     * <p>Expected result:</p>
     * <p>Error Message is displayed.
     * <p>(Payment method is not defined)</p>
     *
     * @param array $customerData
     * @param array $productData
     *
     * @test
     * @depends preconditionsCreateCustomer
     * @depends preconditionsCreateProduct
     * @TestlinkId	TL-MAGE-5278
     */
    public function paymentMethodNotSelected($customerData, $productData) //Not selected Payment Method
    {
        $checkoutData = $this->loadData('multiple_undefined_payment_method', array('checkout_as_customer' => null));;
        $checkoutData['shipping_address_data']['address_to_ship_1']['general_name'] = $productData['general_name'];
        $checkoutData['products_to_add']['product_1']['general_name'] = $productData['general_name'];
        //Steps
        $this->frontend();
        $this->customerHelper()->frontLoginCustomer($customerData);
        $this->shoppingCartHelper()->frontClearShoppingCart();
        $this->checkoutMultipleAddressesHelper()->frontCreateMultipleCheckout($checkoutData, false);
        $this->clickButton('continue_to_review_order');
        //Verification
        $this->assertMessagePresent('validation', 'payment_method_not_defined');
    }

    /**
     * <p>Empty Card Info field </p>
     * <p>Steps:</p>
     * <p>1. Open product page.</p>
     * <p>2. Add product to Shopping Cart.</p>
     * <p>3. Click "Checkout with Multiple Addresses".</p>
     * <p>4. Move to the Billing Information Page</p>
     * <p>5. Select Credit Card (saved)</p>
     * <p>6. Fill in fields except one required.</p>
     * <p>7. Click 'Continue to Review Your Order' button.</p>
     * <p>Expected result:</p>
     * <p>Error Message is displayed.
     *
     * @param string $emptyField
     * @param string $fieldType
     * @param array $customerData
     * @param array $productData
     *
     * @test
     * @dataProvider emptyCardInfoDataProvider
     * @depends preconditionsCreateCustomer
     * @depends preconditionsCreateProduct
     * @TestlinkId TL-MAGE-5279
     */
    public function emptyCardInfo($emptyField, $fieldType, $customerData, $productData) //For Credit Card (saved) only
    {
        //Data
        $checkoutData = $this->loadData('multiple_empty_payment', array ($emptyField => ''));
        $checkoutData['shipping_address_data']['address_to_ship_1']['general_name'] = $productData['general_name'];
        $checkoutData['products_to_add']['product_1']['general_name'] = $productData['general_name'];
        //Steps
        $this->frontend();
        $this->customerHelper()->frontLoginCustomer($customerData);
        $this->shoppingCartHelper()->frontClearShoppingCart();
        $this->checkoutMultipleAddressesHelper()->frontCreateMultipleCheckout($checkoutData, false);
        //Verification
        $this->addFieldIdToMessage($fieldType, $emptyField);
        if ($emptyField == 'card_number') {
            $this->assertMessagePresent('validation', 'invalid_credit_card_number');
        } else {
        $this->assertMessagePresent('validation', 'empty_required_field');
        }
    }

    /**
     * Data provider for emptyCardInfo test
     *
     * @return array
     */
    public function emptyCardInfoDataProvider()
    {
        return array(
            array('name_on_card', 'field'),//Name on Card
            array('card_type', 'dropdown'),//Credit Card Type
            array('card_number', 'field'),//Credit Card Number
            array('expiration_month', 'dropdown'),//Expiration Date (Month)
            array('expiration_year', 'dropdown'),//Expiration Date (Year)
            array('card_verification_number', 'field')//Card Verification Number
        );
    }

}
