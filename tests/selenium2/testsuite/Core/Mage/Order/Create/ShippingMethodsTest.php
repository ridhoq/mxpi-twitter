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
 * Creating Order with specific shipment
 *
 * @package     selenium
 * @subpackage  tests
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class Core_Mage_Order_Create_ShippingMethodsTest extends Mage_Selenium_TestCase
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
     * <p>Creating order with different shipment methods</p>
     * <p>Steps:</p>
     * <p>1. Navigate to "Manage Orders" page;</p>
     * <p>2. Create new order for new customer;</p>
     * <p>3. Select simple product and add it to the order;</p>
     * <p>4. Fill in all required information;</p>
     * <p>5. Choose shipping method;</p>
     * <p>6. Choose payment method;</p>
     * <p>6. Click "Submit Order" button;</p>
     * <p>Expected result:</p>
     * <p>Order is created;</p>
     *
     * @param string $shipment
     * @param string $shippingOrigin
     * @param string $shippingDestination
     * @param string $simpleSku
     *
     * @test
     * @dataProvider shipmentDataProvider
     * @depends preconditionsForTests
     * @TestlinkId	TL-MAGE-3267
     */
    public function differentShipmentMethods($shipment, $shippingOrigin, $shippingDestination, $simpleSku)
    {
        //Data
        $shippingData = $this->loadDataSet('SalesOrder', 'shipping_' . $shipment);
        $orderData = $this->loadDataSet('SalesOrder', 'order_newcustomer_checkmoney_flatrate_' . $shippingDestination,
                                        array('filter_sku'    => $simpleSku,
                                              'shipping_data' => $shippingData));
        $shipmentEnable = $this->loadDataSet('ShippingMethod', $shipment . '_enable');
        //Steps And Verifying
        $this->navigate('system_configuration');
        if ($shippingOrigin) {
            $config = $this->loadDataSet('ShippingSettings', 'shipping_settings_' . strtolower($shippingOrigin));
            $this->systemConfigurationHelper()->configure($config);
        }
        $this->systemConfigurationHelper()->configure($shipmentEnable);
        $this->navigate('manage_sales_orders');
        $this->orderHelper()->createOrder($orderData);
        $this->assertMessagePresent('success', 'success_created_order');
        $this->orderInvoiceHelper()->createInvoiceAndVerifyProductQty();
        $this->orderShipmentHelper()->createShipmentAndVerifyProductQty();
        $this->orderCreditMemoHelper()->createCreditMemoAndVerifyProductQty('refund_offline');
        $this->clickButton('reorder');
        $this->orderHelper()->submitOrder();
        $this->assertMessagePresent('success', 'success_created_order');
        $this->clickButtonAndConfirm('cancel', 'confirmation_for_cancel');
        $this->assertMessagePresent('success', 'success_canceled_order');
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