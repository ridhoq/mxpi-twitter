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
 * Tests for shipping methods. Frontend - OnePageCheckout
 * @package     selenium
 * @subpackage  tests
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class Core_Mage_CheckoutOnePage_Existing_ShippingMethodsTest extends Mage_Selenium_TestCase
{
    /**
     * <p>Log in to Backend.</p>
     */
    public function setUpBeforeTests()
    {
        //Data
        $config = $this->loadDataSet('ShippingSettings', 'store_information');
        //Steps
        $this->loginAdminUser();
        $this->navigate('system_configuration');
        $this->systemConfigurationHelper()->configure($config);
    }

    protected function assertPreConditions()
    {
        $this->loginAdminUser();
    }

    protected function tearDownAfterTestClass()
    {
        //Data
        $config = $this->loadDataSet('ShippingMethod', 'shipping_disable');
        $settings = $this->loadDataSet('ShippingSettings', 'shipping_settings_default');
        //Steps
        $this->loginAdminUser();
        $this->navigate('system_configuration');
        $this->systemConfigurationHelper()->configure($config);
        $this->systemConfigurationHelper()->configure($settings);
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
     * <p>Different Shipping Methods.</p>
     * <p>Preconditions:</p>
     * <p>1.Product is created.</p>
     * <p>Steps:</p>
     * <p>1. Open product page.</p>
     * <p>2. Add product to Shopping Cart.</p>
     * <p>3. Click "Proceed to Checkout".</p>
     * <p>4. Select Checkout Method with log in</p>
     * <p>4. Fill in Billing Information tab.</p>
     * <p>5. Select "Ship to this address" option.</p>
     * <p>6. Click 'Continue' button.</p>
     * <p>7. Select Shipping Method(by data provider).</p>
     * <p>8. Click 'Continue' button.</p>
     * <p>9. Select Payment Method.</p>
     * <p>10. Click 'Continue' button.</p>
     * <p>11. Verify information into "Order Review" tab</p>
     * <p>12. Place order.</p>
     * <p>Expected result:</p>
     * <p>Checkout is successful.</p>
     *
     * @param string $shipping
     * @param string $shippingOrigin
     * @param string $shippingDestination
     * @param string $simpleSku
     *
     * @test
     * @dataProvider shipmentDataProvider
     * @depends preconditionsForTests
     * @TestlinkId	TL-MAGE-3187
     */
    public function differentShippingMethods($shipping, $shippingOrigin, $shippingDestination, $simpleSku)
    {
        //Data
        $userData = $this->loadDataSet('Customers', 'customer_account_register');
        $shippingMethod = $this->loadDataSet('ShippingMethod', $shipping . '_enable');
        $shippingData = $this->loadDataSet('OnePageCheckout', 'front_shipping_' . $shipping);
        $checkoutData = $this->loadDataSet('OnePageCheckout',
                                           'exist_flatrate_checkmoney_' . $shippingDestination,
                                           array('general_name'  => $simpleSku,
                                                 'email_address' => $userData['email'],
                                                 'shipping_data' => $shippingData));
        //Steps
        $this->navigate('system_configuration');
        if ($shippingOrigin) {
            $config = $this->loadDataSet('ShippingSettings',
                                         'shipping_settings_' . strtolower($shippingOrigin));
            $this->systemConfigurationHelper()->configure($config);
        }
        $this->systemConfigurationHelper()->configure($shippingMethod);
        $this->logoutCustomer();
        $this->navigate('customer_login');
        $this->customerHelper()->registerCustomer($userData);
        //Verifying
        $this->assertMessagePresent('success', 'success_registration');
        //Steps
        $this->logoutCustomer();
        $this->checkoutOnePageHelper()->frontCreateCheckout($checkoutData);
        //Verification
        $this->assertMessagePresent('success', 'success_checkout');
    }

    public function shipmentDataProvider()
    {
        return array(
            array('flatrate', null, 'usa'),
            array('free', null, 'usa'),
            array('ups', 'usa', 'usa'),
            array('upsxml', 'usa', 'usa'),
            array('usps', 'usa', 'usa'),
            array('fedex', 'usa', 'usa'),
            array('dhl', 'usa', 'france')
        );
    }
}