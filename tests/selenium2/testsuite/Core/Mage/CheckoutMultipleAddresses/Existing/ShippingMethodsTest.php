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
 * Tests for shipping methods. Frontend
 *
 * @package     selenium
 * @subpackage  tests
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class Core_Mage_CheckoutMultipleAddresses_Existing_ShippingMethodsTest extends Mage_Selenium_TestCase
{
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

    protected function tearDownAfterTest()
    {
        $this->frontend();
        $this->shoppingCartHelper()->frontClearShoppingCart();
        $this->logoutCustomer();
    }

    /**
     * @return array
     * @test
     */
    public function preconditionsForTests()
    {
        //Data
        $simple1 = $this->loadDataSet('Product', 'simple_product_visible');
        $simple2 = $this->loadDataSet('Product', 'simple_product_visible');
        $virtual = $this->loadDataSet('Product', 'virtual_product_visible');
        $userData = $this->loadDataSet('Customers', 'customer_account_register');
        //Steps and Verification
        $this->navigate('manage_products');
        $this->productHelper()->createProduct($simple1);
        $this->assertMessagePresent('success', 'success_saved_product');
        $this->productHelper()->createProduct($simple2);
        $this->assertMessagePresent('success', 'success_saved_product');
        $this->productHelper()->createProduct($virtual, 'virtual');
        $this->assertMessagePresent('success', 'success_saved_product');
        $this->frontend();
        $this->navigate('customer_login');
        $this->customerHelper()->registerCustomer($userData);
        $this->assertMessagePresent('success', 'success_registration');
        return array('simple1'   => $simple1['general_name'],
                     'simple2'   => $simple2['general_name'],
                     'virtual'   => $virtual['general_name'],
                     'email'     => $userData['email'],
                     'password'  => $userData['password']);
    }

    /**
     * <p>Steps:</p>
     * <p>1. Configure settings in System->Configuration</p>
     * <p>2. Login as a customer. Clear shopping cart</p>
     * <p>3. Logout as the customer</p>
     * <p>4. Add 2 simple products to the shopping cart</p>
     * <p>5. Checkout with multiple addresses</p>
     * <p>6. Add default shipping address when needed. Add new shipping address</p>
     * <p>7. Set each product to be delivered to a separate address</p>
     * <p>8. Continue with default billing address,
     *       Check/Money payment method and appropriate shipping method</p>
     * <p>9. Place the order</p>
     * <p>Expected result:</p>
     * <p>Two new orders are successfully created.</p>
     * @TODO change to create shipping addresses once for all tests
     *
     * @param string $shipment
     * @param string $shippingOrigin
     * @param string $shippingDestination
     * @param array $testData
     *
     * @test
     * @dataProvider shipmentDataProvider
     * @depends preconditionsForTests
     * @TestlinkId TL-MAGE-5232
     */
    public function withSimpleProducts($shipment, $shippingOrigin, $shippingDestination, $testData)
    {
        //Data
        $shippingMethod = $this->loadDataSet('MultipleAddressesCheckout',
                                             'multiple_front_shipping_' . $shipment);
        $checkoutData = $this->loadDataSet('MultipleAddressesCheckout',
                                           'multiple_shipping_methods_existing_' . $shippingDestination,
                                           array('shipping_method' => $shippingMethod,
                                                 'email'           => $testData['email'],
                                                 'password'        => $testData['password']),
                                           array('product_1' => $testData['simple1'],
                                                 'product_2' => $testData['simple2']));
        $shippingSettings = $this->loadDataSet('ShippingMethod', $shipment . '_enable');
        //Setup
        $this->navigate('system_configuration');
        if ($shippingOrigin) {
            $config = $this->loadDataSet('ShippingSettings',
                                         'shipping_settings_' . strtolower($shippingOrigin));
            $this->systemConfigurationHelper()->configure($config);
        }
        $this->systemConfigurationHelper()->configure($shippingSettings);
        //Steps and Verify
        $orderNumbers = $this->checkoutMultipleAddressesHelper()->frontCreateMultipleCheckout($checkoutData);
        $this->assertTrue(count($orderNumbers) == 2, $this->getMessagesOnPage());
    }

    /**
     * <p>Steps:</p>
     * <p>1. Configure settings in System->Configuration</p>
     * <p>2. Login as a customer. Clear shopping cart</p>
     * <p>3. Logout as the customer</p>
     * <p>4. Add 1 simple product and 1 virtual to the shopping cart</p>
     * <p>5. Checkout with multiple addresses</p>
     * <p>6. Add default shipping address when needed. Add new shipping address</p>
     * <p>7. Set each product to be delivered to a separate address</p>
     * <p>8. Continue with default billing address, Check/Money payment method and appropriate shipping method</p>
     * <p>9. Place the order</p>
     * <p>Expected result:</p>
     *
     * @param string $shipment
     * @param string $shippingOrigin
     * @param string $shippingDestination
     * @param array $testData
     *
     * @test
     * @dataProvider shipmentDataProvider
     * @depends preconditionsForTests
     * @TestlinkId TL-MAGE-5233
     */
    public function withSimpleAndVirtualProducts($shipment, $shippingOrigin, $shippingDestination, $testData)
    {
        //Data
        $shippingMethod = $this->loadDataSet('MultipleAddressesCheckout',
                                             'multiple_front_shipping_' . $shipment);
        $checkoutData = $this->loadDataSet('MultipleAddressesCheckout',
                                           'multiple_shipping_methods_existing_' . $shippingDestination,
                                           array('shipping_method'  => $shippingMethod,
                                                 'email'            => $testData['email'],
                                                 'password'         => $testData['password'],
                                                 'address_2'        => '%noValue%',
                                                 'address_to_add_2' => '%noValue%'),
                                           array('product_1' => $testData['simple1'],
                                                 'product_2' => $testData['virtual']));
        $shippingSettings = $this->loadDataSet('ShippingMethod', $shipment . '_enable');
        //Setup
        $this->navigate('system_configuration');
        if ($shippingOrigin) {
            $config = $this->loadDataSet('ShippingSettings',
                                         'shipping_settings_' . strtolower($shippingOrigin));
            $this->systemConfigurationHelper()->configure($config);
        }
        $this->systemConfigurationHelper()->configure($shippingSettings);
        //Steps and Verify
        $orderNumbers = $this->checkoutMultipleAddressesHelper()->frontCreateMultipleCheckout($checkoutData);
        $this->assertTrue(count($orderNumbers) == 2, $this->getMessagesOnPage());
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