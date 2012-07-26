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
 * One page Checkout tests
 *
 * @package     selenium
 * @subpackage  tests
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class Core_Mage_CheckoutOnePage_LoggedIn_CheckingValidationTest extends Mage_Selenium_TestCase
{
    /**
     * <p>Creating Simple product and customer</p>
     *
     * @return array
     * @test
     */
    public function preconditionsForTests()
    {
        //Data
        $simple = $this->loadDataSet('Product', 'simple_product_visible');
        $userData = $this->loadDataSet('Customers', 'generic_customer_account');
        //Steps and Verification
        $this->loginAdminUser();
        $this->navigate('manage_products');
        $this->productHelper()->createProduct($simple);
        $this->assertMessagePresent('success', 'success_saved_product');
        $this->navigate('manage_customers');
        $this->customerHelper()->createCustomer($userData);
        $this->assertMessagePresent('success', 'success_saved_customer');

        return array('sku'      => $simple['general_name'],
                     'customer' => array('email'    => $userData['email'],
                                         'password' => $userData['password']));
    }

    /**
     * <p>Empty required fields in billing address tab</p>
     * <p>Preconditions:</p>
     * <p>1.Product is created.</p>
     * <p>2.Customer without address is registered.</p>
     * <p>3.Customer signed in at the frontend.</p>
     * <p>Steps:</p>
     * <p>1. Open product page.</p>
     * <p>2. Add product to Shopping Cart.</p>
     * <p>3. Click "Proceed to Checkout".</p>
     * <p>4. Fill in Billing Information tab. Leave one required field empty</p>
     * <p>5. Click 'Continue' button.</p>
     * <p>Expected result:</p>
     * <p>Error message for field appears</p>
     *
     * @param string $field
     * @param string $message
     * @param array $data
     *
     * @test
     * @dataProvider addressEmptyFieldsDataProvider
     * @depends preconditionsForTests
     * @TestlinkId	TL-MAGE-3195
     */
    public function emptyRequiredFieldsInBillingAddress($field, $message, $data)
    {
        //Preconditions
        $this->customerHelper()->frontLoginCustomer($data['customer']);
        $this->shoppingCartHelper()->frontClearShoppingCart();
        //Data
        $checkoutData = $this->loadDataSet('OnePageCheckout', 'signedin_flatrate_checkmoney_different_address',
                                           array('general_name'      => $data['sku'],
                                                 'billing_' . $field => ''));
        try {
            //Steps
            $this->checkoutOnePageHelper()->frontCreateCheckout($checkoutData);
            $this->fail('Expected message is not displayed: [' . $message . ']');
        } catch (PHPUnit_Framework_AssertionFailedError $e) {
            //Verification
            $this->assertSame($message, $e->toString());
            $this->clearMessages('verification');
        }
    }

    /**
     * <p>Empty required fields in shipping address tab</p>
     * <p>Preconditions:</p>
     * <p>1.Product is created.</p>
     * <p>2.Customer without address is registered.</p>
     * <p>3.Customer signed in at the frontend.</p>
     * <p>Steps:</p>
     * <p>1. Open product page.</p>
     * <p>2. Add product to Shopping Cart.</p>
     * <p>3. Click "Proceed to Checkout".</p>
     * <p>4. Fill in Billing Information tab. Leave one required field empty</p>
     * <p>5. Click 'Continue' button.</p>
     * <p>Expected result:</p>
     * <p>Error message for field appears</p>
     *
     * @param string $field
     * @param string $message
     * @param array $data
     *
     * @test
     * @dataProvider addressEmptyFieldsDataProvider
     * @depends preconditionsForTests
     * @TestlinkId	TL-MAGE-3196
     */
    public function emptyRequiredFieldsInShippingAddress($field, $message, $data)
    {
        //Preconditions
        $this->customerHelper()->frontLoginCustomer($data['customer']);
        $this->shoppingCartHelper()->frontClearShoppingCart();
        //Data
        $checkoutData = $this->loadDataSet('OnePageCheckout', 'signedin_flatrate_checkmoney_different_address',
                                           array('general_name'       => $data['sku'],
                                                 'shipping_' . $field => ''));
        try {
            //Steps
            $this->checkoutOnePageHelper()->frontCreateCheckout($checkoutData);
            $this->fail('Expected message is not displayed: [' . $message . ']');
        } catch (PHPUnit_Framework_AssertionFailedError $e) {
            //Verification
            $this->assertSame($message, $e->toString());
            $this->clearMessages('verification');
        }
    }

    public function addressEmptyFieldsDataProvider()
    {
        return array(
            array('first_name', '"First Name": This is a required field.'),
            array('last_name', '"Last Name": This is a required field.'),
            array('street_address_1', '"Address": This is a required field.'),
            array('city', '"City": This is a required field.'),
            array('state', '"State/Province": Please select an option.'),
            array('zip_code', '"Zip/Postal Code": This is a required field.'),
            array('country', '"Country": Please select an option.'),
            array('telephone', '"Telephone": This is a required field.')
        );
    }

    /**
     * @param string $dataName
     * @param array $data
     *
     * @test
     * @dataProvider specialDataDataProvider
     * @depends preconditionsForTests
     */
    public function specialValuesForAddressFields($dataName, $data)
    {
        //Data
        $checkoutData = $this->loadDataSet('OnePageCheckout', $dataName, array('general_name' => $data['sku']));
        $userData = $this->loadDataSet('Customers', 'customer_account_register');
        //Steps
        $this->logoutCustomer();
        $this->navigate('customer_login');
        $this->customerHelper()->registerCustomer($userData);
        //Verifying
        $this->assertMessagePresent('success', 'success_registration');
        //Steps
        $this->checkoutOnePageHelper()->frontCreateCheckout($checkoutData);
        //Verification
        $this->assertMessagePresent('success', 'success_checkout');
    }

    public function specialDataDataProvider()
    {
        return array(
            array('signedin_flatrate_checkmoney_long_address'),
            array('signedin_flatrate_checkmoney_special_address')
        );
    }

    /**
     * <p>Verifying "Use Billing Address" checkbox functionality</p>
     * <p>Preconditions</p>
     * <p>1. Add product to Shopping Cart</p>
     * <p>2. Click "Proceed to Checkout"</p>
     * <p>Steps</p>
     * <p>1. Fill in Checkout Method tab</p>
     * <p>2. Click 'Continue' button.</p>
     * <p>3. Fill in Billing Information tab</p>
     * <p>4. Select "Ship to different address" option</p>
     * <p>5. Click 'Continue' button.</p>
     * <p>6. Check "Use Billing Address" checkbox</p>
     * <p>7. Verify data used for filling form</p>
     * <p>8. Click 'Continue' button.</p>
     * <p>Expected result:</p>
     * <p>Data must be the same as billing address</p>
     * <p>Customer successfully redirected to the next page, no error massages appears</p>
     *
     * @param array $data
     *
     * @test
     * @depends preconditionsForTests
     * @TestlinkId	TL-MAGE-3198
     */
    public function frontShippingAddressUseBillingAddress($data)
    {
        //Data
        $checkoutData = $this->loadDataSet('OnePageCheckout', 'signedin_flatrate_checkmoney_use_billing_in_shipping',
                                           array('general_name' => $data['sku']));
        $userData = $this->loadDataSet('Customers', 'customer_account_register');
        //Steps
        $this->logoutCustomer();
        $this->navigate('customer_login');
        $this->customerHelper()->registerCustomer($userData);
        //Verifying
        $this->assertMessagePresent('success', 'success_registration');
        //Steps
        $this->checkoutOnePageHelper()->frontCreateCheckout($checkoutData);
        //Verification
        $this->assertMessagePresent('success', 'success_checkout');
    }

    /**
     * <p>Shipping method not defined</p>
     * <p>Preconditions</p>
     * <p>1. Add product to Shopping Cart</p>
     * <p>2. Click "Proceed to Checkout"</p>
     * <p>Steps</p>
     * <p>1. Fill in Checkout Method tab</p>
     * <p>2. Click 'Continue' button.</p>
     * <p>3. Fill in Billing Information tab</p>
     * <p>4. Select "Ship to this address" option</p>
     * <p>5. Click 'Continue' button.</p>
     * <p>6. Leave Shipping Method options empty</p>
     * <p>7. Click 'Continue' button.</p>
     * <p>Expected result:</p>
     * <p>Information window appears "Please specify shipping method."</p>
     *
     * @param array $data
     *
     * @test
     * @depends preconditionsForTests
     * @TestlinkId	TL-MAGE-3199
     */
    public function shippingMethodNotDefined($data)
    {
        //Preconditions
        $this->loginAdminUser();
        $this->navigate('system_configuration');
        $config = $this->loadDataSet('ShippingMethod', 'free_enable');
        $this->systemConfigurationHelper()->configure($config);
        //Data
        $checkoutData = $this->loadDataSet('OnePageCheckout', 'signedin_flatrate_checkmoney_different_address',
                                           array('general_name'   => $data['sku'],
                                                 'shipping_data'  => '%noValue%'));
        $userData = $this->loadDataSet('Customers', 'customer_account_register');
        //Steps
        $this->logoutCustomer();
        $this->navigate('customer_login');
        $this->customerHelper()->registerCustomer($userData);
        //Verifying
        $this->assertMessagePresent('success', 'success_registration');
        $message = $this->getUimapPage('frontend', 'onepage_checkout')->findMessage('shipping_alert');
        try {
            //Steps
            $this->checkoutOnePageHelper()->frontCreateCheckout($checkoutData);
            $this->fail('Expected message is not displayed: [' . $message . ']');
        } catch (PHPUnit_Framework_AssertionFailedError $e) {
            //Verification
            $this->assertSame($message, $e->toString());
            $this->clearMessages('verification');
        }
    }

    /**
     * <p>Payment method not defined</p>
     * <p>Preconditions</p>
     * <p>1. Add product to Shopping Cart</p>
     * <p>2. Click "Proceed to Checkout"</p>
     * <p>Steps</p>
     * <p>1. Fill in Checkout Method tab</p>
     * <p>2. Click 'Continue' button.</p>
     * <p>3. Fill in Billing Information tab</p>
     * <p>4. Select "Ship to this address" option</p>
     * <p>5. Click 'Continue' button.</p>
     * <p>6. Select Shipping Method option</p>
     * <p>7. Click 'Continue' button.</p>
     * <p>8. Leave Payment Method options empty</p>
     * <p>9. Click 'Continue' button.</p>
     * <p>Expected result:</p>
     * <p>Information window appears "Please specify payment method."</p>
     *
     * @param array $data
     *
     * @test
     * @depends preconditionsForTests
     * @TestlinkId	TL-MAGE-3197
     */
    public function frontPaymentMethodNotDefined($data)
    {
        //Preconditions
        $this->loginAdminUser();
        $this->navigate('system_configuration');
        $config = $this->loadDataSet('PaymentMethod', 'savedcc_without_3Dsecure');
        $this->systemConfigurationHelper()->configure($config);
        //Data
        $checkoutData = $this->loadDataSet('OnePageCheckout', 'signedin_flatrate_checkmoney_different_address',
                                           array('general_name'   => $data['sku'],
                                                 'payment_data'   => '%noValue%'));
        $userData = $this->loadDataSet('Customers', 'customer_account_register');
        //Steps
        $this->logoutCustomer();
        $this->navigate('customer_login');
        $this->customerHelper()->registerCustomer($userData);
        //Verifying
        $this->assertMessagePresent('success', 'success_registration');
        $message = $this->getUimapPage('frontend', 'onepage_checkout')->findMessage('payment_alert');
        try {
            //Steps
            $this->checkoutOnePageHelper()->frontCreateCheckout($checkoutData);
            $this->fail('Expected message is not displayed: [' . $message . ']');
        } catch (PHPUnit_Framework_AssertionFailedError $e) {
            //Verification
            $this->assertSame($message, $e->toString());
            $this->clearMessages('verification');
        }
    }
}