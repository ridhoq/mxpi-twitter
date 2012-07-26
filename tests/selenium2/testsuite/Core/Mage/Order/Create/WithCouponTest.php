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
 * Tests for creating order with applying coupon.
 *
 * @package     selenium
 * @subpackage  tests
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class Core_Mage_Order_Create_WithCouponTest extends Mage_Selenium_TestCase
{
    /**
     * <p>Preconditions:</p>
     *
     * <p>Log in to Backend.</p>
     */
    public function setUpBeforeTests()
    {
        $this->loginAdminUser();
    }

    /**
     * <p>Create Simple Product for tests</p>
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
     * <p>Creating order with coupon. Coupon amount should be less than Grand Total.</p>
     * <p>Steps:</p>
     * <p>1. Navigate to "Manage Orders" page;</p>
     * <p>2. Create new order and select customer coupon can be applied for;</p>
     * <p>3. Select products and add them to the order;</p>
     * <p>4. Apply coupon;</p>
     * <p>5. Fill in all required information</p>
     * <p>6. Click "Submit Order" button;</p>
     * <p>Expected result:</p>
     * <p>Order is created, no error messages appear;</p>
     *
     * @param string $simpleSku
     *
     * @test
     * @depends preconditionsForTests
     * @TestlinkId	TL-MAGE-3269
     */
    public function amountLessThanGrandTotal($simpleSku)
    {
        //Data
        $coupon = $this->loadDataSet('SalesOrder', 'coupon_fixed_amount', array('discount_amount' => 5));
        $orderData = $this->loadDataSet('SalesOrder', 'order_newcustomer_checkmoney_flatrate_usa',
                                        array('filter_sku' => $simpleSku,
                                              'coupon_1'   => $coupon['info']['coupon_code']));
        //Steps
        $this->navigate('manage_shopping_cart_price_rules');
        $this->priceRulesHelper()->createRule($coupon);
        $this->assertMessagePresent('success', 'success_saved_rule');
        $this->navigate('manage_sales_orders');
        $this->orderHelper()->createOrder($orderData);
        $this->assertMessagePresent('success', 'success_created_order');
    }

    /**
     * <p>Creating order with coupon. Coupon amount should be greater than Grand Total.</p>
     * <p>Steps:</p>
     * <p>1. Navigate to "Manage Orders" page;</p>
     * <p>2. Create new order and select customer coupon can be applied for;</p>
     * <p>3. Select products and add them to the order;</p>
     * <p>4. Apply coupon;</p>
     * <p>5. Fill in all required information</p>
     * <p>6. Click "Submit Order" button;</p>
     * <p>Expected result:</p>
     * <p>Order is created, no error messages appear;</p>
     *
     * @param string $simpleSku
     *
     * @test
     * @depends preconditionsForTests
     * @TestlinkId	TL-MAGE-3268
     */
    public function amountGreaterThanGrandTotal($simpleSku)
    {
        //Data
        $coupon = $this->loadDataSet('SalesOrder', 'coupon_fixed_amount', array('discount_amount' => 100));
        $orderData = $this->loadDataSet('SalesOrder', 'order_newcustomer_checkmoney_flatrate_usa',
                                        array('filter_sku' => $simpleSku,
                                              'coupon_1'   => $coupon['info']['coupon_code']));
        unset($orderData['payment_data']);
        //Steps
        $this->navigate('manage_shopping_cart_price_rules');
        $this->priceRulesHelper()->createRule($coupon);
        $this->assertMessagePresent('success', 'success_saved_rule');
        $this->navigate('manage_sales_orders');
        $this->orderHelper()->createOrder($orderData);
        $this->assertMessagePresent('success', 'success_created_order');
    }

    /**
     * <p>Creating order with coupon. Coupon code is invalid.</p>
     * <p>Steps:</p>
     * <p>1. Navigate to "Manage Orders" page;</p>
     * <p>2. Create new order and select customer coupon can be applied for;</p>
     * <p>3. Select products and add them to the order;</p>
     * <p>4. Apply invalid coupon code;</p>
     * <p>Expected result:</p>
     * <p>Message with error appears;</p>
     *
     * @param string $simpleSku
     *
     * @test
     * @depends preconditionsForTests
     * @TestlinkId	TL-MAGE-3270
     */
    public function wrongCode($simpleSku)
    {
        //Data
        $orderData = $this->loadDataSet('SalesOrder', 'order_newcustomer_checkmoney_flatrate_usa',
                                        array('filter_sku' => $simpleSku));
        //Steps
        $this->navigate('manage_sales_orders');
        $this->orderHelper()->navigateToCreateOrderPage(null, $orderData['store_view']);
        $this->orderHelper()->addProductToOrder($orderData['products_to_add']['product_1']);
        $this->orderHelper()->applyCoupon('wrong_code', false);
        $this->addParameter('code', 'wrong_code');
        $this->assertMessagePresent('error', 'invalid_coupon_code');
    }
}