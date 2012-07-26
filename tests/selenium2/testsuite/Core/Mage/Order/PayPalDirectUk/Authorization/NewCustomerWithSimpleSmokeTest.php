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
 * Cancel orders
 *
 * @package     selenium
 * @subpackage  tests
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class Core_Mage_Order_PayPalDirectUk_Authorization_NewCustomerWithSimpleSmokeTest extends Mage_Selenium_TestCase
{
    protected function assertPreConditions()
    {
        $this->loginAdminUser();
    }

    protected function tearDownAfterTestClass()
    {
        $this->paypalHelper()->paypalDeveloperLogin();
        $this->paypalHelper()->deleteAllAccounts();
    }

    /**
     * <p>Create a Sandbox Test Accounts and configure paypal settings</p>
     *
     * @return array
     * @test
     */
    public function preconditionsForTests()
    {
        //Data
        $settings = $this->loadDataSet('PaymentMethod', 'paypaldirectuk_without_3Dsecure');
        $productData = $this->loadDataSet('SalesOrder', 'simple_product_visible');
        //Steps and Verifying
        $this->navigate('system_configuration');
        $this->systemConfigurationHelper()->configure($settings);
        $this->navigate('manage_products');
        $this->productHelper()->createProduct($productData);
        $this->assertMessagePresent('success', 'success_saved_product');

        $this->paypalHelper()->paypalDeveloperLogin();
        $accounts = $this->paypalHelper()->createBuyerAccounts('visa, mastercard, discover, amex');
        $cards = array();
        foreach ($accounts as $cardName => $info) {
            $cards[$cardName] = $info['credit_card'];
        }

        return array('cards' => $cards,
                     'sku'   => $productData['general_sku']);
    }

    /**
     * <p>Smoke test for order without 3D secure</p>
     *
     * @param array $testData
     *
     * @return array
     * @test
     * @depends preconditionsForTests
     */
    public function orderWithout3DSecureSmoke($testData)
    {
        //Data
        $orderData = $this->loadDataSet('SalesOrder', 'order_newcustomer_paypaldirectuk_flatrate',
                                        array('filter_sku'   => $testData['sku'],
                                              'payment_info' => $testData['cards']['mastercard']));
        //Steps
        $this->navigate('manage_sales_orders');
        $this->orderHelper()->createOrder($orderData);
        //Verifying
        $this->assertMessagePresent('success', 'success_created_order');

        return $orderData;
    }

    /**
     * <p>Create order with PayPal Direct Uk using all types of credit card</p>
     *
     * @param string $card
     * @param array $orderData
     * @param array $testData
     *
     * @test
     * @dataProvider orderWithDifferentCreditCardDataProvider
     * @depends orderWithout3DSecureSmoke
     * @depends preconditionsForTests
     */
    public function orderWithDifferentCreditCard($card, $orderData, $testData)
    {
        //Data
        $this->overrideDataByCondition('payment_info', $testData['cards'][$card], $orderData, 'byFieldKey');
        //Steps
        $this->navigate('manage_sales_orders');
        $this->orderHelper()->createOrder($orderData);
        //Verifying
        $this->assertMessagePresent('success', 'success_created_order');
    }

    public function orderWithDifferentCreditCardDataProvider()
    {
        return array(
            array('amex'),
            array('visa')
        );
    }

    /**
     * <p>PaypalUKDirect. Full Invoice With different types of Capture</p>
     * <p>Steps:</p>
     * <p>1.Go to Sales-Orders.</p>
     * <p>2.Press "Create New Order" button.</p>
     * <p>3.Press "Create New Customer" button.</p>
     * <p>4.Choose 'Main Store' (First from the list of radiobuttons) if exists.</p>
     * <p>5.Fill all fields.</p>
     * <p>6.Press 'Add Products' button.</p>
     * <p>7.Add first two products.</p>
     * <p>8.Choose shipping address the same as billing.</p>
     * <p>9.Check payment method 'PayPalUkDirect'</p>
     * <p>10.Fill in all required fields.</p>
     * <p>11.Choose first from 'Get shipping methods and rates'.</p>
     * <p>12.Submit order.</p>
     * <p>13.Create Invoice.</p>
     * <p>Expected result:</p>
     * <p>New customer is created. Order is created for the new customer. Invoice is created</p>
     *
     * @param string $captureType
     * @param array $orderData
     *
     * @test
     * @dataProvider captureTypeDataProvider
     * @depends orderWithout3DSecureSmoke
     * @TestlinkId	TL-MAGE-3296
     */
    public function fullInvoiceWithDifferentTypesOfCapture($captureType, $orderData)
    {
        //Steps
        $this->navigate('manage_sales_orders');
        $this->orderHelper()->createOrder($orderData);
        //Verifying
        $this->assertMessagePresent('success', 'success_created_order');
        //Steps
        $this->orderInvoiceHelper()->createInvoiceAndVerifyProductQty($captureType);
    }

    public function captureTypeDataProvider()
    {
        return array(
            array('Capture Online'),
            array('Capture Offline'),
            array('Not Capture')
        );
    }

    /**
     * <p>Partial invoice with different types of capture</p>
     *
     * @param string $captureType
     * @param array $orderData
     * @param array $testData
     *
     * @test
     * @dataProvider captureTypeDataProvider
     * @depends orderWithout3DSecureSmoke
     * @depends preconditionsForTests
     */
    public function partialInvoiceWithDifferentTypesOfCapture($captureType, $orderData, $testData)
    {
        //Data
        $orderData['products_to_add']['product_1']['product_qty'] = 10;
        $invoice = $this->loadDataSet('SalesOrder', 'products_to_invoice',
                                      array('invoice_product_sku' => $testData['sku']));
        //Steps
        $this->navigate('manage_sales_orders');
        $this->orderHelper()->createOrder($orderData);
        //Verifying
        $this->assertMessagePresent('success', 'success_created_order');
        //Steps
        $this->orderInvoiceHelper()->createInvoiceAndVerifyProductQty($captureType, $invoice);
    }

    /**
     * <p>PayPalUK Direct. Full Refund</p>
     * <p>Steps:</p>
     * <p>1.Go to Sales-Orders.</p>
     * <p>2.Press "Create New Order" button.</p>
     * <p>3.Press "Create New Customer" button.</p>
     * <p>4.Choose 'Main Store' (First from the list of radiobuttons) if exists.</p>
     * <p>5.Fill all fields.</p>
     * <p>6.Press 'Add Products' button.</p>
     * <p>7.Add first two products.</p>
     * <p>8.Choose shipping address the same as billing.</p>
     * <p>9.Check payment method 'PayPalUkDirect - Visa'</p>
     * <p>10. Fill in all required fields.</p>
     * <p>11.Choose first from 'Get shipping methods and rates'.</p>
     * <p>12.Submit order.</p>
     * <p>13.Invoice order.</p>
     * <p>14.Make refund online.</p>
     * <p>Expected result:</p>
     * <p>New customer is created. Order is created for the new customer. Refund Online is successful</p>
     *
     * @param string $captureType
     * @param string $refundType
     * @param array $orderData
     *
     * @test
     * @dataProvider creditMemoDataProvider
     * @depends orderWithout3DSecureSmoke
     * @TestlinkId	TL-MAGE-3295
     */
    public function fullCreditMemo($captureType, $refundType, $orderData)
    {
        //Steps and Verifying
        $this->navigate('manage_sales_orders');
        $this->orderHelper()->createOrder($orderData);
        $this->assertMessagePresent('success', 'success_created_order');
        $orderId = $this->orderHelper()->defineOrderId();
        $this->orderInvoiceHelper()->createInvoiceAndVerifyProductQty($captureType);
        $this->navigate('manage_sales_invoices');
        $this->orderInvoiceHelper()->openInvoice(array('filter_order_id' => $orderId));
        $this->orderCreditMemoHelper()->createCreditMemoAndVerifyProductQty($refundType);
    }

    /**
     * <p>Partial Credit Memo</p>
     *
     * @param string $captureType
     * @param string $refundType
     * @param array $orderData
     * @param array $testData
     *
     * @test
     * @dataProvider creditMemoDataProvider
     * @depends orderWithout3DSecureSmoke
     * @depends preconditionsForTests
     */
    public function partialCreditMemo($captureType, $refundType, $orderData, $testData)
    {
        //Data
        $orderData['products_to_add']['product_1']['product_qty'] = 10;
        $creditMemo = $this->loadDataSet('SalesOrder', 'products_to_refund',
                                         array('return_filter_sku' => $testData['sku']));
        //Steps and Verifying
        $this->navigate('manage_sales_orders');
        $this->orderHelper()->createOrder($orderData);
        $this->assertMessagePresent('success', 'success_created_order');
        $orderId = $this->orderHelper()->defineOrderId();
        $this->orderInvoiceHelper()->createInvoiceAndVerifyProductQty($captureType);
        $this->navigate('manage_sales_invoices');
        $this->orderInvoiceHelper()->openInvoice(array('filter_order_id' => $orderId));
        $this->orderCreditMemoHelper()->createCreditMemoAndVerifyProductQty($refundType, $creditMemo);
    }

    public function creditMemoDataProvider()
    {
        return array(
            array('Capture Online', 'refund'),
            array('Capture Online', 'refund_offline'),
            array('Capture Offline', 'refund_offline')
        );
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
     * <p>9.Check payment method 'Paypal Direct Uk';</p>
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
     * @TestlinkId	TL-MAGE-3297
     */
    public function fullShipmentForOrderWithoutInvoice($orderData)
    {
        //Steps and Verifying
        $this->navigate('manage_sales_orders');
        $this->orderHelper()->createOrder($orderData);
        $this->assertMessagePresent('success', 'success_created_order');
        $this->orderShipmentHelper()->createShipmentAndVerifyProductQty();
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
     * @TestlinkId	TL-MAGE-3298
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
     * @TestlinkId	TL-MAGE-3299
     * @group skip_due_to_bug
     */
    public function reorderPendingOrder($orderData)
    {
        //Data
        $cardData = $orderData['payment_data']['payment_info'];
        //Steps
        $this->navigate('manage_sales_orders');
        $this->orderHelper()->createOrder($orderData);
        //Verifying
        $this->assertMessagePresent('success', 'success_created_order');
        //Steps
        $this->clickButton('reorder');
        $this->orderHelper()->verifyIfCreditCardFieldsAreEmpty($cardData);
        $this->fillForm($cardData);
        $this->orderHelper()->submitOrder();
        //Verifying
        $this->assertMessagePresent('success', 'success_created_order');
        $this->assertEmptyVerificationErrors();
    }

    /**
     * <p>Void order.</p>
     * <p>Steps:</p>
     * <p>1.Go to Sales-Orders.</p>
     * <p>2.Press "Create New Order" button.</p>
     * <p>3.Press "Create New Customer" button.</p>
     * <p>4.Choose 'Main Store' (First from the list of radiobuttons) if exists.</p>
     * <p>5.Fill all fields.</p>
     * <p>6.Press 'Add Products' button.</p>
     * <p>7.Add first two products.</p>
     * <p>8.Choose shipping address the same as billing.</p>
     * <p>9.Check payment method 'PayPal Direct - Visa'</p>
     * <p>10. Fill in all required fields.</p>
     * <p>11.Choose first from 'Get shipping methods and rates'.</p>
     * <p>12.Submit order.</p>
     * <p>13.Void Order.</p>
     * <p>Expected result:</p>
     * <p>New customer is created. Order is created for the new customer. Void successful</p>
     *
     * @param array $orderData
     *
     * @test
     * @depends orderWithout3DSecureSmoke
     * @TestlinkId	TL-MAGE-3300
     */
    public function voidPendingOrderFromOrderPage($orderData)
    {
        //Steps
        $this->navigate('manage_sales_orders');
        $this->orderHelper()->createOrder($orderData);
        //Verifying
        $this->assertMessagePresent('success', 'success_created_order');
        //Steps
        $this->clickButtonAndConfirm('void', 'confirmation_to_void');
        //Verifying
        $this->assertMessagePresent('success', 'success_voided_order');
    }

    /**
     * <p>Create Orders using paypal direct uk payment method with 3DSecure</p>
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
     * @TestlinkId	TL-MAGE-3294
     */
    public function createOrderWith3DSecure($card, $needSetUp, $orderData)
    {
        //Data
        $cardData = $this->loadDataSet('SalesOrder', $card);
        $this->overrideDataByCondition('payment_info', $cardData, $orderData, 'byFieldKey');
        //Steps
        if ($needSetUp) {
            $this->systemConfigurationHelper()->useHttps('admin', 'yes');
            $settings = $this->loadDataSet('PaymentMethod', 'paypaldirectuk_with_3Dsecure');
            $this->systemConfigurationHelper()->configure($settings);
        }
        $this->navigate('manage_sales_orders');
        $this->orderHelper()->createOrder($orderData);
        //Verifying
        $this->assertMessagePresent('success', 'success_created_order');
    }

    public function createOrderWith3DSecureDataProvider()
    {
        return array(
            array('else_visa_direct', true),
            array('else_mastercard', false)
        );
    }
}