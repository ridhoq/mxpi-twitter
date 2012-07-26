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
 * Tests with gift messages
 *
 * @package     selenium
 * @subpackage  tests
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class Core_Mage_Order_Create_WithGiftMessageTest extends Mage_Selenium_TestCase
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
     * <p>Creating order with gift messages for order</p>
     * <p>Steps:</p>
     * <p>1. Navigate to "Manage Orders" page;</p>
     * <p>2. Create new order for new customer;</p>
     * <p>3. Select products and add them to the order;</p>
     * <p>4. Add gift message for the products;</p>
     * <p>5. Fill in all required information</p>
     * <p>6. Click "Submit Order" button;</p>
     * <p>Expected result:</p>
     * <p>Order is created, no error messages appear, gift message added for the order;</p>
     *
     * @param string $simpleSku
     *
     * @test
     * @depends preconditionsForTests
     * @TestlinkId	TL-MAGE-3284
     */
    public function giftMessagePerOrder($simpleSku)
    {
        //Data
        $orderData = $this->loadDataSet('SalesOrder', 'order_newcustomer_checkmoney_flatrate_usa',
                                        array('filter_sku'    => $simpleSku,
                                              'gift_messages' => $this->loadDataSet('SalesOrder',
                                                                                    'gift_messages_per_order')));
        $config = $this->loadDataSet('GiftMessage', 'gift_message_for_order_enable');
        //Steps
        $this->navigate('system_configuration');
        $this->systemConfigurationHelper()->configure($config);
        $this->navigate('manage_sales_orders');
        $this->orderHelper()->createOrder($orderData);
        //Verifying
        $this->assertMessagePresent('success', 'success_created_order');
        $this->orderHelper()->verifyGiftMessage($orderData['gift_messages']);
    }

    /**
     * <p>Creating order with gift messages for products</p>
     * <p>Steps:</p>
     * <p>1. Navigate to "Manage Orders" page;</p>
     * <p>2. Create new order for new customer;</p>
     * <p>3. Select products and add them to the order;</p>
     * <p>4. Add gift message for the products;</p>
     * <p>5. Fill in all required information</p>
     * <p>6. Click "Submit Order" button;</p>
     * <p>Expected result:</p>
     * <p>Order is created, no error messages appear, gift message added for the products;</p>
     *
     * @param string $simpleSku
     *
     * @test
     * @depends preconditionsForTests
     * @TestlinkId	TL-MAGE-3283
     */
    public function giftMessageForProduct($simpleSku)
    {
        //Data
        $gift = $this->loadDataSet('SalesOrder', 'gift_messages_individual', array('sku_product' => $simpleSku));
        $orderData = $this->loadDataSet('SalesOrder', 'order_newcustomer_checkmoney_flatrate_usa',
                                        array('filter_sku'    => $simpleSku,
                                              'gift_messages' => $gift));
        $config = $this->loadDataSet('GiftMessage', 'gift_message_per_item_enable');
        //Steps
        $this->navigate('system_configuration');
        $this->systemConfigurationHelper()->configure($config);
        $this->navigate('manage_sales_orders');
        $this->orderHelper()->createOrder($orderData);
        //Verifying
        $this->assertMessagePresent('success', 'success_created_order');
        $this->orderHelper()->verifyGiftMessage($orderData['gift_messages']);
    }

    /**
     * <p>Creating order with gift messages for products, but with empty fields in message</p>
     * <p>Steps:</p>
     * <p>1. Navigate to "Manage Orders" page;</p>
     * <p>2. Create new order for new customer;</p>
     * <p>3. Select products and add them to the order;</p>
     * <p>4. Add gift message for the products. Do not fill in any fields in message;</p>
     * <p>5. Fill in all required information</p>
     * <p>6. Click "Submit Order" button;</p>
     * <p>Expected result:</p>
     * <p>Order is created, no error messages appear;</p>
     *
     * @param string $simpleSku
     *
     * @test
     * @depends preconditionsForTests
     * @TestlinkId	TL-MAGE-3285
     */
    public function giftMessagesWithEmptyFields($simpleSku)
    {
        //Data
        $gift = $this->loadDataSet('SalesOrder', 'gift_messages_with_empty_fields',
                                   array('sku_product' => $simpleSku));
        $orderData = $this->loadDataSet('SalesOrder', 'order_physical', array('filter_sku'    => $simpleSku,
                                                                              'gift_messages' => $gift));
        $config = $this->loadDataSet('GiftMessage', 'gift_message_all_enable');
        //Steps
        $this->navigate('system_configuration');
        $this->systemConfigurationHelper()->configure($config);
        $this->navigate('manage_sales_orders');
        $this->orderHelper()->createOrder($orderData);
        //Verifying
        $this->assertMessagePresent('success', 'success_created_order');
        $this->orderHelper()->verifyGiftMessage($this->loadDataSet('SalesOrder',
                                                                   'gift_messages_with_empty_fields_expected',
                                                                   array('sku_product' => $simpleSku)));
    }
}