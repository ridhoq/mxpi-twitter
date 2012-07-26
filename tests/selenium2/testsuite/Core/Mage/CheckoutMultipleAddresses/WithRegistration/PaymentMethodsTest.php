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
 * Tests for payment methods. Frontend
 *
 * @package     selenium
 * @subpackage  tests
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class Core_Mage_CheckoutMultipleAddresses_WithRegistration_PaymentMethodsTest extends Mage_Selenium_TestCase
{
    protected function assertPreConditions()
    {
        $this->loginAdminUser();
    }

    protected function tearDownAfterTest()
    {
        $this->logoutCustomer();
        $this->shoppingCartHelper()->frontClearShoppingCart();
    }

    protected function tearDownAfterTestClass()
    {
        $this->loginAdminUser();
        $this->systemConfigurationHelper()->useHttps('frontend', 'no');
        $this->paypalHelper()->paypalDeveloperLogin();
        $this->paypalHelper()->deleteAllAccounts();
    }

    /**
     * @return array
     * @test
     */
    public function preconditionsForTests()
    {
        //Data
        $simple = $this->loadDataSet('Product', 'simple_product_visible');
        //Steps and Verification
        $this->navigate('manage_products');
        $this->productHelper()->createProduct($simple);
        $this->assertMessagePresent('success', 'success_saved_product');

        $this->paypalHelper()->paypalDeveloperLogin();
        $accountInfo = $this->paypalHelper()->createPreconfiguredAccount('paypal_sandbox_new_pro_account');
        $api = $this->paypalHelper()->getApiCredentials($accountInfo['email']);
        $accounts = $this->paypalHelper()->createBuyerAccounts('visa');

        return array('simple' => $simple['general_name'],
                     'api'    => $api,
                     'visa'   => $accounts['visa']['credit_card']);
    }

    /**
     * <p>Payment methods without 3D secure.</p>
     * <p>Preconditions:</p>
     * <p>1.Product is created.</p>
     * <p>Steps:</p>
     * <p>1. Open product page.</p>
     * <p>2. Add product to Shopping Cart.</p>
     * <p>3. Click "Checkout with Multiple Addresses".</p>
     * <p>4. Select Checkout Method with Registering</p>
     * <p>5. Fill in Select Addresses page.</p>
     * <p>6. Click 'Continue to Shipping Information' button.</p>
     * <p>7. Fill in Shipping Information page</p>
     * <p>8. Click 'Continue to Billing Information' button.</p>
     * <p>9. Select Payment Method(by data provider).</p>
     * <p>10. Click 'Continue to Review Your Order' button.</p>
     * <p>11. Verify information into "Place Order" page</p>
     * <p>12. Place order.</p>
     * <p>Expected result:</p>
     * <p>Checkout is successful.</p>
     *
     * @param string $payment
     * @param array $testData
     *
     * @test
     * @dataProvider paymentsWithout3dDataProvider
     * @depends preconditionsForTests
     * @TestlinkId	TL-MAGE-3183
     */
    public function paymentsWithout3d($payment, $testData)
    {
        //Data
        $paymentData = $this->loadDataSet('OnePageCheckout', 'front_payment_' . $payment);
        $checkoutData = $this->loadDataSet('MultipleAddressesCheckout', 'multiple_payment_methods_register',
                                           array('payment_data' => $paymentData,
                                                 'general_name' => $testData['simple']));
        if ($payment != 'checkmoney') {
            $payment .= '_without_3Dsecure';
        }
        $paymentConfig = $this->loadDataSet('PaymentMethod', $payment);
        if ($payment == 'paypaldirect_without_3Dsecure') {
            $checkoutData = $this->overrideArrayData($testData['visa'], $checkoutData, 'byFieldKey');
            $paymentConfig = $this->overrideArrayData($testData['api'], $paymentConfig, 'byFieldKey');
        }
        //Steps
        $this->navigate('system_configuration');
        $this->systemConfigurationHelper()->configure($paymentConfig);
        $this->frontend();
        $this->checkoutMultipleAddressesHelper()->frontCreateMultipleCheckout($checkoutData);
        //Verification
        $this->assertMessagePresent('success', 'success_checkout');

    }

    public function paymentsWithout3dDataProvider()
    {
        return array(
            array('paypaldirect'),
            array('savedcc'),
            array('paypaldirectuk'),
            array('checkmoney'),
            array('payflowpro'),
            array('authorizenet')
        );
    }

    /**
     * <p>Payment methods with 3D secure.</p>
     * <p>Preconditions:</p>
     * <p>1.Product is created.</p>
     * <p>Steps:</p>
     * <p>1. Open product page.</p>
     * <p>2. Add product to Shopping Cart.</p>
     * <p>3. Click "Checkout with Multiple Addresses".</p>
     * <p>4. Select Checkout Method with Registering</p>
     * <p>5. Fill in Select Addresses page.</p>
     * <p>6. Click 'Continue to Shipping Information' button.</p>
     * <p>7. Fill in Shipping Information page</p>
     * <p>8. Click 'Continue to Billing Information' button.</p>
     * <p>9. Select Payment Method(by data provider).</p>
     * <p>10. Click 'Continue to Review Your Order' button.</p>
     * <p>11. Enter 3D security code.</p>
     * <p>12. Verify information into "Place Order" page</p>
     * <p>13. Place order.</p>
     * <p>Expected result:</p>
     * <p>Checkout is successful.</p>
     *
     * @param string $payment
     * @param array $testData
     *
     * @test
     * @dataProvider paymentsWith3dDataProvider
     * @depends preconditionsForTests
     * @TestlinkId	TL-MAGE-3182
     */
    public function paymentsWith3d($payment, $testData)
    {
        //Data
        $paymentData = $this->loadDataSet('OnePageCheckout', 'front_payment_' . $payment);
        $checkoutData = $this->loadDataSet('MultipleAddressesCheckout', 'multiple_payment_methods_register',
                                           array('payment_data' => $paymentData,
                                                 'general_name' => $testData['simple']));
        $paymentConfig = $this->loadDataSet('PaymentMethod', $payment . '_with_3Dsecure');
        //Steps
        if ($payment == 'paypaldirect') {
            $this->systemConfigurationHelper()->useHttps('frontend', 'yes');
            $paymentConfig = $this->overrideArrayData($testData['api'], $paymentConfig, 'byFieldKey');
        }
        $this->navigate('system_configuration');
        $this->systemConfigurationHelper()->configure($paymentConfig);
        $this->frontend();
        $this->checkoutMultipleAddressesHelper()->frontCreateMultipleCheckout($checkoutData);
        //Verification
        $this->assertMessagePresent('success', 'success_checkout');
    }

    public function paymentsWith3dDataProvider()
    {
        return array(
            array('paypaldirect'),
            array('savedcc'),
            array('paypaldirectuk'),
            array('payflowpro'),
            array('authorizenet')
        );
    }
}