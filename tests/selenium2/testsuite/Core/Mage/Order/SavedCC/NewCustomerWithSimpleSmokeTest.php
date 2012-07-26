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
 * Test admin order workflow with SavedCC payment method
 *
 * @package     selenium
 * @subpackage  tests
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class Core_Mage_Order_SavedCC_NewCustomerWithSimpleSmokeTest extends Mage_Selenium_TestCase
{
    /**
     * <p>Preconditions:</p>
     * <p>Log in to Backend.</p>
     */
    public function setUpBeforeTests()
    {
        //Data
        $config = $this->loadDataSet('PaymentMethod', 'savedcc_without_3Dsecure');
        //Steps
        $this->loginAdminUser();
        $this->navigate('system_configuration');
        $this->systemConfigurationHelper()->configure($config);
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
     * <p>Smoke test for order without 3D secure</p>
     *
     * @param string $simpleSku
     *
     * @return array
     * @test
     * @depends preconditionsForTests
     */
    public function orderWithout3DSecureSmoke($simpleSku)
    {
        //Data
        $orderData = $this->loadDataSet('SalesOrder', 'order_newcustomer_savedcc_flatrate',
                                        array('filter_sku' => $simpleSku));
        //Steps
        $this->navigate('manage_sales_orders');
        $this->orderHelper()->createOrder($orderData);
        //Verifying
        $this->assertMessagePresent('success', 'success_created_order');

        return $orderData;
    }

    /**
     * <p>Create order with Saved CC using all types of credit card</p>
     *
     * @param string $card
     * @param array $orderData
     *
     * @test
     * @dataProvider cardSavedCCDataProvider
     * @depends orderWithout3DSecureSmoke
     */
    public function differentCardInSavedCC($card, $orderData)
    {
        //Data
        $orderData['payment_data']['payment_info'] = $this->loadDataSet('SalesOrder', $card);
        //Steps
        $this->navigate('manage_sales_orders');
        $this->orderHelper()->createOrder($orderData);
        //Verifying
        $this->assertMessagePresent('success', 'success_created_order');
    }

    public function cardSavedCCDataProvider()
    {
        return array(
            array('saved_american_express'),
            array('saved_visa'),
            array('saved_mastercard'),
            array('saved_jcb'),
            array('saved_discover'),
            array('saved_solo'),
            array('saved_other')
        );
    }

    /**
     * <p>Invoice for full order</p>
     * <p>Steps:</p>
     * <p>1.Go to Sales-Orders;</p>
     * <p>2.Press "Create New Order" button;</p>
     * <p>3.Press "Create New Customer" button;</p>
     * <p>4.Choose 'Main Store' (First from the list of radiobuttons) if exists;</p>
     * <p>5.Fill all required fields;</p>
     * <p>6.Press 'Add Products' button;</p>
     * <p>7.Add products;</p>
     * <p>8.Choose shipping address the same as billing;</p>
     * <p>9.Check payment method 'Credit Card';</p>
     * <p>10.Choose any from 'Get shipping methods and rates';</p>
     * <p>11. Submit order;</p>
     * <p>12. Invoice order;</p>
     * <p>Expected result:</p>
     * <p>New customer successfully created. Order is created for the new customer;</p>
     * <p>Message "The order has been created." is displayed.</p>
     * <p>Order is invoiced successfully</p>
     *
     * @param array $orderData
     *
     * @test
     * @depends orderWithout3DSecureSmoke
     * @TestlinkId	TL-MAGE-3573
     */
    public function fullInvoiceWithSavedCC($orderData)
    {
        //Steps
        $this->navigate('manage_sales_orders');
        $this->orderHelper()->createOrder($orderData);
        //Verifying
        $this->assertMessagePresent('success', 'success_created_order');
        //Steps
        $this->orderInvoiceHelper()->createInvoiceAndVerifyProductQty();
    }

    /**
     * <p>Invoice for part of order</p>
     * <p>Steps:</p>
     * <p>1.Go to Sales-Orders;</p>
     * <p>2.Press "Create New Order" button;</p>
     * <p>3.Press "Create New Customer" button;</p>
     * <p>4.Choose 'Main Store' (First from the list of radiobuttons) if exists;</p>
     * <p>5.Fill all required fields;</p>
     * <p>6.Press 'Add Products' button;</p>
     * <p>7.Add products;</p>
     * <p>8.Choose shipping address the same as billing;</p>
     * <p>9.Check payment method 'Credit Card';</p>
     * <p>10.Choose any from 'Get shipping methods and rates';</p>
     * <p>11. Submit order;</p>
     * <p>12. Partially invoice order;</p>
     * <p>Expected result:</p>
     * <p>New customer successfully created. Order is created for the new customer;</p>
     * <p>Message "The order has been created." is displayed.</p>
     * <p>Order is invoiced successfully</p>
     *
     * @param array $orderData
     * @param string $sku
     *
     * @test
     * @depends orderWithout3DSecureSmoke
     * @depends preconditionsForTests
     * @TestlinkId	TL-MAGE-3577
     */
    public function partialInvoiceWithCreditCard($orderData, $sku)
    {
        //Data
        $orderData['products_to_add']['product_1']['product_qty'] = 10;
        $invoice = $this->loadDataSet('SalesOrder', 'products_to_refund', array('return_filter_sku' => $sku));
        //Steps
        $this->navigate('manage_sales_orders');
        $this->orderHelper()->createOrder($orderData);
        //Verifying
        $this->assertMessagePresent('success', 'success_created_order');
        //Steps
        $this->orderInvoiceHelper()->createInvoiceAndVerifyProductQty(null, $invoice);
    }

    /**
     * <p>Credit Memo for whole invoice<p>
     * <p>Steps:</p>
     * <p>1.Go to Sales-Orders;</p>
     * <p>2.Press "Create New Order" button;</p>
     * <p>3.Press "Create New Customer" button;</p>
     * <p>4.Choose 'Main Store' (First from the list of radiobuttons) if exists;</p>
     * <p>5.Fill all required fields;</p>
     * <p>6.Press 'Add Products' button;</p>
     * <p>7.Add products;</p>
     * <p>8.Choose shipping address the same as billing;</p>
     * <p>9.Check payment method 'Credit Card';</p>
     * <p>10.Choose any from 'Get shipping methods and rates';</p>
     * <p>11. Submit order;</p>
     * <p>12. Invoice order;</p>
     * <p>13. Refund order;</p>
     * <p>Expected result:</p>
     * <p>New customer successfully created. Order is created for the new customer;</p>
     * <p>Message "The order has been created." is displayed.</p>
     * <p>Order is invoiced and refunded successfully</p>
     *
     * @param array $orderData
     *
     * @test
     * @depends orderWithout3DSecureSmoke
     * @TestlinkId	TL-MAGE-3576
     */
    public function fullCreditMemoWithCreditCard($orderData)
    {
        //Steps
        $this->navigate('manage_sales_orders');
        $this->orderHelper()->createOrder($orderData);
        $this->assertMessagePresent('success', 'success_created_order');
        $this->orderInvoiceHelper()->createInvoiceAndVerifyProductQty();
        $this->orderCreditMemoHelper()->createCreditMemoAndVerifyProductQty('refund_offline');
    }

    /**
     * <p>Credit Memo for part of invoice</p>
     * <p>Steps:</p>
     * <p>1.Go to Sales-Orders;</p>
     * <p>2.Press "Create New Order" button;</p>
     * <p>3.Press "Create New Customer" button;</p>
     * <p>4.Choose 'Main Store' (First from the list of radiobuttons) if exists;</p>
     * <p>5.Fill all required fields;</p>
     * <p>6.Press 'Add Products' button;</p>
     * <p>7.Add two products;</p>
     * <p>8.Choose shipping address the same as billing;</p>
     * <p>9.Check payment method 'Credit Card';</p>
     * <p>10.Choose any from 'Get shipping methods and rates';</p>
     * <p>11. Submit order;</p>
     * <p>12. Invoice order;</p>
     * <p>13. Partially refund order (choose only one product);</p>
     * <p>Expected result:</p>
     * <p>New customer successfully created. Order is created for the new customer;</p>
     * <p>Message "The order has been created." is displayed.</p>
     * <p>Order is invoiced and refunded successfully</p>
     *
     * @param array $orderData
     * @param string $sku
     *
     * @test
     * @depends orderWithout3DSecureSmoke
     * @depends preconditionsForTests.
     * @TestlinkId	TL-MAGE-3576
     */
    public function partialCreditMemoWithCreditCard($orderData, $sku)
    {
        //Data
        $orderData['products_to_add']['product_1']['product_qty'] = 10;
        $creditMemo = $this->loadDataSet('SalesOrder', 'products_to_refund', array('return_filter_sku' => $sku));
        //Steps
        $this->navigate('manage_sales_orders');
        $this->orderHelper()->createOrder($orderData);
        $this->assertMessagePresent('success', 'success_created_order');
        $this->orderInvoiceHelper()->createInvoiceAndVerifyProductQty();
        $this->orderCreditMemoHelper()->createCreditMemoAndVerifyProductQty('refund_offline', $creditMemo);
    }

    /**
     * <p>Shipment for order</p>
     * <p>Steps:</p>
     * <p>1.Go to Sales-Orders;</p>
     * <p>2.Press "Create New Order" button;</p>
     * <p>3.Press "Create New Customer" button;</p>
     * <p>4.Choose 'Main Store' (First from the list of radiobuttons) if exists;</p>
     * <p>5.Fill all required fields;</p>
     * <p>6.Press 'Add Products' button;</p>
     * <p>7.Add products;</p>
     * <p>8.Choose shipping address the same as billing;</p>
     * <p>9.Check payment method 'Credit Card';</p>
     * <p>10.Choose any from 'Get shipping methods and rates';</p>
     * <p>11. Submit order;</p>
     * <p>12. Invoice order;</p>
     * <p>13. Ship order;</p>
     * <p>Expected result:</p>
     * <p>New customer successfully created. Order is created for the new customer;</p>
     * <p>Message "The order has been created." is displayed.</p>
     * <p>Order is invoiced and shipped successfully</p>
     *
     * @param array $orderData
     *
     * @test
     * @depends orderWithout3DSecureSmoke
     * @TestlinkId	TL-MAGE-3574
     */
    public function fullShipmentForOrderWithoutInvoice($orderData)
    {
        //Steps
        $this->navigate('manage_sales_orders');
        $this->orderHelper()->createOrder($orderData);
        //Verifying
        $this->assertMessagePresent('success', 'success_created_order');
        //Steps
        $this->orderShipmentHelper()->createShipmentAndVerifyProductQty();
    }

    /**
     * <p>Shipment for part of order</p>
     * <p>Steps:</p>
     * <p>1.Go to Sales-Orders;</p>
     * <p>2.Press "Create New Order" button;</p>
     * <p>3.Press "Create New Customer" button;</p>
     * <p>4.Choose 'Main Store' (First from the list of radiobuttons) if exists;</p>
     * <p>5.Fill all required fields;</p>
     * <p>6.Press 'Add Products' button;</p>
     * <p>7.Add several products;</p>
     * <p>8.Choose shipping address the same as billing;</p>
     * <p>9.Check payment method 'Credit Card';</p>
     * <p>10.Choose any from 'Get shipping methods and rates';</p>
     * <p>11. Submit order;</p>
     * <p>12. Invoice order;</p>
     * <p>13. Partially ship order;</p>
     * <p>Expected result:</p>
     * <p>New customer successfully created. Order is created for the new customer;</p>
     * <p>Message "The order has been created." is displayed.</p>
     * <p>Order is invoiced and shipped successfully</p>
     *
     * @param array $orderData
     * @param string $sku
     *
     * @test
     * @depends orderWithout3DSecureSmoke
     * @depends preconditionsForTests
     * @TestlinkId	TL-MAGE-3578
     */
    public function partialShipmentForOrderWithoutInvoice($orderData, $sku)
    {
        //Data
        $orderData['products_to_add']['product_1']['product_qty'] = 10;
        $shipment = $this->loadDataSet('SalesOrder', 'products_to_ship', array('ship_product_sku' => $sku));
        //Steps
        $this->navigate('manage_sales_orders');
        $this->orderHelper()->createOrder($orderData);
        //Verifying
        $this->assertMessagePresent('success', 'success_created_order');
        //Steps
        $this->orderShipmentHelper()->createShipmentAndVerifyProductQty($shipment);
    }

    /**
     * <p>Holding and unholding order after creation.</p>
     * <p>Steps:</p>
     * <p>1. Navigate to "Manage Orders" page;</p>
     * <p>2. Create new order for new customer;</p>
     * <p>3. Hold order;</p>
     * <p>Expected result:</p>
     * <p>Order is holden;</p>
     * <p>4. Unhold order;</p>
     * <p>Expected result:</p>
     * <p>Order is unholden;</p>
     *
     * @param array $orderData
     *
     * @test
     * @depends orderWithout3DSecureSmoke
     * @TestlinkId	TL-MAGE-3575
     */
    public function holdAndUnholdPendingOrderViaOrderPage($orderData)
    {
        //Steps and Verifying
        $this->navigate('manage_sales_orders');
        $this->orderHelper()->createOrder($orderData);
        $this->assertMessagePresent('success', 'success_created_order');
        $this->clickButton('hold');
        $this->assertMessagePresent('success', 'success_hold_order');
        $this->clickButton('unhold');
        $this->assertMessagePresent('success', 'success_unhold_order');
    }

    /**
     * <p>Cancel Pending Order From Order Page</p>
     *
     * @param array $orderData
     *
     * @test
     * @depends orderWithout3DSecureSmoke
     */
    public function cancelPendingOrderFromOrderPage($orderData)
    {
        //Steps and Verifying
        $this->navigate('manage_sales_orders');
        $this->orderHelper()->createOrder($orderData);
        $this->assertMessagePresent('success', 'success_created_order');
        $this->clickButtonAndConfirm('cancel', 'confirmation_for_cancel');
        $this->assertMessagePresent('success', 'success_canceled_order');
    }

    /**
     * <p>Reorder.</p>
     * <p>Steps:</p>
     * <p>1.Go to Sales-Orders;</p>
     * <p>2.Press "Create New Order" button;</p>
     * <p>3.Press "Create New Customer" button;</p>
     * <p>4.Choose 'Main Store' (First from the list of radiobuttons) if exists;</p>
     * <p>5.Fill all required fields;</p>
     * <p>6.Press 'Add Products' button;</p>
     * <p>7.Add products;</p>
     * <p>8.Choose shipping address the same as billing;</p>
     * <p>9.Check payment method 'Credit Card';</p>
     * <p>10.Choose any from 'Get shipping methods and rates';</p>
     * <p>11. Submit order;</p>
     * <p>12. Edit order (add products and change billing address);</p>
     * <p>13. Submit order;</p>
     * <p>Expected results:</p>
     * <p>New customer successfully created. Order is created for the new customer;</p>
     * <p>Message "The order has been created." is displayed.</p>
     * <p>New order during reorder is created.</p>
     * <p>Message "The order has been created." is displayed.</p>
     * <p>Bug MAGE-5802</p>
     *
     * @param array $orderData
     *
     * @test
     * @depends orderWithout3DSecureSmoke
     * @TestlinkId	TL-MAGE-3579
     * @group skip_due_to_bug
     */
    public function reorderPendingOrder($orderData)
    {
        //Steps
        $this->navigate('manage_sales_orders');
        $this->orderHelper()->createOrder($orderData);
        //Verifying
        $this->assertMessagePresent('success', 'success_created_order');
        //Steps
        $this->clickButton('reorder');
        $this->orderHelper()->submitOrder();
        //Verifying
        $this->assertMessagePresent('success', 'success_created_order');
    }

    /**
     * <p>Create Orders using Saved CC payment method with 3DSecure</p>
     * <p>Steps:</p>
     * <p>1.Go to Sales-Orders.</p>
     * <p>2.Press "Create New Order" button.</p>
     * <p>3.Press "Create New Customer" button.</p>
     * <p>4.Choose 'Main Store' (First from the list of radiobuttons) if exists.</p>
     * <p>5.Press 'Add Products' button.</p>
     * <p>6.Add simple product.</p>
     * <p>7.Fill all required fields in billing address form.</p>
     * <p>8.Choose shipping address the same as billing.</p>
     * <p>9.Check shipping method</p>
     * <p>10.Check payment method</p>
     * <p>11.Validate card with 3D secure</p>
     * <p>12.Submit order.</p>
     * <p>Expected result:</p>
     * <p>New customer is created. Order is created for the new customer.</p>
     *
     * @param string $card
     * @param bool $needSetUp
     * @param array $orderData
     *
     * @test
     * @dataProvider createOrderWith3DSecureDataProvider
     * @depends orderWithout3DSecureSmoke
     * @TestlinkId TL-MAGE-3572
     */
    public function createOrderWith3DSecure($card, $needSetUp, $orderData)
    {
        //Data
        $orderData['payment_data']['payment_info'] = $this->loadDataSet('SalesOrder', $card);
        //Steps
        if ($needSetUp) {
            $this->systemConfigurationHelper()->useHttps('admin', 'yes');
            $config = $this->loadDataSet('PaymentMethod', 'savedcc_with_3Dsecure');
            $this->systemConfigurationHelper()->configure($config);
        }
        $this->navigate('manage_sales_orders');
        $this->orderHelper()->createOrder($orderData);
        //Verifying
        $this->assertMessagePresent('success', 'success_created_order');
    }

    public function createOrderWith3DSecureDataProvider()
    {
        return array(
            array('saved_jcb', true),
            array('saved_visa', false),
            array('saved_mastercard', false)
        );
    }
}