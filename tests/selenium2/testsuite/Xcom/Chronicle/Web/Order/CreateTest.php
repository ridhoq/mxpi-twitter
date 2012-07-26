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
 * Create Orders
 *
 * @package     selenium
 * @subpackage  tests
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

use Xcom_Chronicle_XMessages_WebStore as WebStore;
use Xcom_Chronicle_XMessages_Inventory as Inventory;
use Xcom_Chronicle_XMessages_OrderManagement as OrderManagement;
use Xcom_Chronicle_XMessages_Customer as Customer;

class Xcom_Chronicle_Web_Order_CreateTest extends Xcom_Chronicle_TestCase
{
    /**
     * <p>Preconditions:</p>
     * <p>Log in to Backend.</p>
     */
    public function setUpBeforeTests()
    {
        $this->loginAdminUser();
    }

    protected function assertPreConditions()
    {
        $this->addParameter('id', '0');
    }

    /**
     * @return array
     * @test
     */
    public function preconditionsCreateSimpleProduct(){
        return $this->_createSimpleProduct();
    }

    /**
     * <p>Create Order with New Customer.</p>
     * <p>Preconditions:</p>
     * <p>1.A simple product is created.</p>
     * <p>Steps:</p>
     * <p>Create an order with new customer</p>
     * <p>Expected result:</p>
     * <p>OrderCreated message is sent out.</p>
     * <p>StockItemUpdated message is sent out.</p>
     * <p>WebStoreOfferQuantityUpdated message is sent out.</p>
     * <p>CustomerCreated message is sent out.</p>
     *
     * @depends preconditionsCreateSimpleProduct
     *
     * @test
     */
    public function createOrderWithNewCustomer($productData)
    {

        //Data
        $orderData = $this->loadDataSet('SalesOrder', 'order_newcustomer_checkmoney_flatrate_usa', array('filter_sku' => $productData['general_sku'], 'product_qty' => 10));
        //Steps
        $this->navigate('manage_sales_orders');
        $this->orderHelper()->createOrder($orderData);
        //Verifying
        $this->assertTrue($this->successMessage('success_created_order'), $this->messages);
        $orderId = $this->orderHelper()->defineOrderId();

        //Verify
        $expectedMsgs = array(
            array( 'topic' => Customer::CUSTOMER_CREATED,),
            array( "topic" => OrderManagement::ORDER_CREATED,
                'message.simpleOrder.orderNumber.string' => $orderId,
                'message.simpleOrder.status' => 'NEW',
                'message.simpleOrder.orderLines.0.productSku' => $productData['general_sku'],
                'message.simpleOrder.orderLines.0.quantity' => 10,
                'message.simpleOrder.orderLines.0.status' => 'NEW',
                'message.simpleOrder.orderLines.0.price.amount' => $productData['prices_price'] * 10,
            ),
            array( 'topic' => WebStore::OFFER_QUANTITY_UPDATED,
                'message.sku' => $productData['general_sku'],
            ),
            array( 'topic' => Inventory::STOCK_ITEM_UPDATED,
                'message.stockItems.0.sku' => $productData['general_sku'],
            ),
        );
        $msgs = $this->_get2dXMessages();
        $this->verifyXMessage($expectedMsgs, $msgs);
    }

    /**
     * @return array
     *
     * @test
     */
    public function preconditionsCreateCustomer()
    {
        $userData = $this->loadDataSet('Customers', 'generic_customer_account');
        $addressData = $this->loadDataSet('Customers', 'all_fields_address');
        //Steps
        $this->navigate('manage_customers');
        $this->customerHelper()->createCustomer($userData, $addressData);
        //Verifying
        $this->assertMessagePresent('success', 'success_saved_customer');

        return $userData;
    }

    /**
     * <p>Create Order with Existing Customer.</p>
     * <p>Preconditions:</p>
     * <p>1.A Product is created and known.</p>
     * <p>2.A customer is created and known.</p>
     * <p>Steps:</p>
     * <p>Create an order with existing customer</p>
     * <p>Expected result:</p>
     * <p>OrderCreated message is sent out.</p>
     * <p>StockItemUpdated message is sent out.</p>
     * <p>WebStoreOfferQuantityUpdated message is sent out.</p>
     * <p>CustomerUpdated message is sent out.</p>
     *
     * @param array $testData
     *
     * @depends preconditionsCreateSimpleProduct
     * @depends preconditionsCreateCustomer
     *
     * @test
     */
    public function createOrderWithExistingCustomer($productData, $customerData)
    {

        //Data
        $orderData = $this->loadDataSet('SalesOrder', 'order_physical',
            array('filter_sku' => $productData['general_sku'],
                'product_qty' => 10,
                'email'      => $customerData['email']));
        unset($orderData['billing_addr_data']);
        unset($orderData['shipping_addr_data']);
        //Steps And Verifying
        $this->navigate('manage_sales_orders');
        $this->orderHelper()->createOrder($orderData);
        $this->assertMessagePresent('success', 'success_created_order');
        $orderId = $this->orderHelper()->defineOrderId();

        //Verify
        $expectedMsgs = array(
            array( 'topic' => Customer::CUSTOMER_UPDATED,
                'message.data.email.emailAddress' =>  $customerData['email'],
            ),
            array( "topic" => OrderManagement::ORDER_CREATED,
                'message.simpleOrder.orderNumber.string' => $orderId,
                'message.simpleOrder.status' => 'NEW',
                'message.simpleOrder.orderLines.0.productSku' => $productData['general_sku'],
                'message.simpleOrder.orderLines.0.quantity' => 10,
                'message.simpleOrder.orderLines.0.status' => 'NEW',
            ),
            array( 'topic' => WebStore::OFFER_QUANTITY_UPDATED,
                'message.sku' => $productData['general_sku'],
            ),
            array( 'topic' => Inventory::STOCK_ITEM_UPDATED,
                'message.stockItems.0.sku' => $productData['general_sku'],
            ),
        );
        $msgs = $this->_get2dXMessages();
        $this->verifyXMessage($expectedMsgs, $msgs);
    }

    /**
     * <p>Create Order with Updating Existing Customer.</p>
     * <p>Preconditions:</p>
     * <p>1.A Product is created and known.</p>
     * <p>2.A customer is created and known.</p>
     * <p>Steps:</p>
     * <p>Create an order with updating existing customer</p>
     * <p>Expected result:</p>
     * <p>OrderCreated message is sent out.</p>
     * <p>StockItemUpdated message is sent out.</p>
     * <p>WebStoreOfferQuantityUpdated message is sent out.</p>
     * <p>CustomerUpdated message is sent out.</p>
     *
     * @param array $testData
     *
     * @depends preconditionsCreateSimpleProduct
     * @depends preconditionsCreateCustomer
     *
     * @test
     */
    public function createOrderWithUpdatingExistingCustomer($productData, $customerData)
    {

        //Data
        $orderData = $this->loadDataSet('SalesOrder', 'order_physical',
            array('filter_sku' => $productData['general_sku'],
                'product_qty' => 10,
                'email'      => $customerData['email'],
//                'billing_save_in_address_book' => 'No',
            ));
        $orderData['billing_addr_data']['billing_save_in_address_book'] = 'Yes';

        //Steps And Verifying
        $this->navigate('manage_sales_orders');
        $this->orderHelper()->createOrder($orderData);
        $this->assertMessagePresent('success', 'success_created_order');
        $orderId = $this->orderHelper()->defineOrderId();

        //Verify
        $expectedMsgs = array(
            array( 'topic' => Customer::CUSTOMER_UPDATED,
                'message.data.email.emailAddress' =>  $customerData['email'],
            ),
            array( "topic" => OrderManagement::ORDER_CREATED,
                'message.simpleOrder.orderNumber.string' => $orderId,
                'message.simpleOrder.status' => 'NEW',
                'message.simpleOrder.orderLines.0.productSku' => $productData['general_sku'],
                'message.simpleOrder.orderLines.0.quantity' => 10,
                'message.simpleOrder.orderLines.0.status' => 'NEW',
            ),
            array( 'topic' => WebStore::OFFER_QUANTITY_UPDATED,
                'message.sku' => $productData['general_sku'],
            ),
            array( 'topic' => Inventory::STOCK_ITEM_UPDATED,
                'message.stockItems.0.sku' => $productData['general_sku'],
            ),
        );
        $msgs = $this->_get2dXMessages();
        $this->verifyXMessage($expectedMsgs, $msgs);
    }
}

