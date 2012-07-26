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
 * Return Orders
 *
 * @package     selenium
 * @subpackage  tests
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

use Xcom_Chronicle_XMessages_OrderManagement as OrderManagement;
use Xcom_Chronicle_XMessages_Inventory as Inventory;

class ReturnToStock
{
    const YES = 'Yes';
    const NO  = "No";
}

class Xcom_Chronicle_Web_Order_CreditMemoTest extends Xcom_Chronicle_TestCase
{
    /**
     * Data in test preconditions
     * @var Varien_Object
     */
    protected static $_preconditionsData;

    /**
     * The quantity of a product in the order
     */
    const FULL_ORDER_QTY = 10;

    /**
     * <p>Preconditions:</p>
     * <p>Log in to Backend.</p>
     *
     */
    public function setUpBeforeTests()
    {
        $this->loginAdminUser();
    }

    /**
     * <p>Preconditions:</p>
     * <p>Create 2 simple products
     * <p>An order is created.</p>
     * <p>Invoice of the order is created.</p>
     */
    public function setUpBeforeEachTest()
    {
        self::$_preconditionsData = new Varien_Object();
        $productData1 = $this->loadDataSet('Product', 'simple_product_visible', array('prices_tax_class' => 'Taxable Goods'));
        $this->_createProduct($productData1, 'simple');
        self::$_preconditionsData->setProduct1($productData1);
        $productData2 = $this->loadDataSet('Product', 'simple_product_visible',
            array('prices_price' => '5.99'));
        $this->_createProduct($productData2, 'simple');
        self::$_preconditionsData->setProduct2($productData2);

        //Data
        $product1 = self::$_preconditionsData->getProduct1();
        $sku1 = $product1['general_sku'];
        $product2 = self::$_preconditionsData->getProduct2();
        $sku2 = $product2['general_sku'];
        $orderData = $this->loadDataSet('SalesOrder', 'order_newcustomer_checkmoney_flatrate_usa',
            array(
                'filter_sku' => $sku1,
                'product_qty' => self::FULL_ORDER_QTY,
                'product_2' => array(
                    'filter_sku' => $sku2,
                    'product_qty' => self::FULL_ORDER_QTY,
                )
            )
        );
        //Steps - create an order
        $this->navigate('manage_sales_orders');
        $this->orderHelper()->createOrder($orderData);
        //Verifying
        $this->assertTrue($this->successMessage('success_created_order'), $this->messages);
        self::$_preconditionsData->setOrder($orderData);
        self::$_preconditionsData->setOrderId($this->orderHelper()->defineOrderId());
        //Steps - Create Invoice
        $this->orderInvoiceHelper()->createInvoiceAndVerifyProductQty();
    }

    protected function assertPreConditions()
    {
        $this->addParameter('id', '0');
    }

    /**
     * <p>Credit Memo Partial Order Not Return To Stock.</p>
     * <p>Preconditions:</p>
     * <p>1.Order is created.</p>
     * <p>2.Invoice is created
     * <p>3.The invoice of the order is open in admin
     * <p>Steps:</p>
     * <p>1. Click 'Credit Memo' button.</p>
     * <p>2. Fill product and quantity to credit for.</p>
     * <p>3. Click 'Refund Offline' button.</p>
     * <p>Expected result:</p>
     * <p>Order Update message is sent out.</p>
     *
     * @test
     *
     */
    public function creditMemoPartialOrderNotReturnToStock()
    {
        //Data
        $product1 = self::$_preconditionsData->getProduct1();
        $sku1 = $product1['general_sku'];
        $qty1 = self::FULL_ORDER_QTY - 2;
        $product2 = self::$_preconditionsData->getProduct2();
        $sku2 = $product2['general_sku'];
        $qty2 = self::FULL_ORDER_QTY - 3;
        $orderId = self::$_preconditionsData->getOrderId();
        $creditMemoData = array(
            'product_1' => array(
                'return_filter_sku' =>$sku1,
                'return_to_stock' => ReturnToStock::NO,
                'qty_to_refund' => $qty1,
            ),
            'product_2' => array(
                'return_filter_sku' =>$sku2,
                'return_to_stock' => ReturnToStock::NO,
                'qty_to_refund' => $qty2,
            ),
        );
        //Steps
        $this->_refundOffline($creditMemoData);

        //Verify
        $expectedMsgs = array(
            array( "topic" => OrderManagement::PARTIAL_ORDER_RETURN,
                'message.orderNumber' => $orderId,
                'message.returnedItems.0.productSku' => $sku1,
                'message.returnedItems.0.quantity' => $qty1,
                'message.returnedItems.0.price.price.amount' => $product1['prices_price'],
                'message.returnedItems.1.productSku' => $sku2,
                'message.returnedItems.1.quantity' => $qty2,
                'message.returnedItems.1.price.price.amount' => $product2['prices_price'],
                'message.customerId.com.x.core.v1.EntityId.Id' => '!\d+!',
                'message.dateOrdered.string' => '!.+!',
            ),
        );
        $msgs = $this->_get2dXMessages();
        $this->verifyXMessage($expectedMsgs, $msgs);
    }

    /**
     * <p>Credit Memo Full Order Not Return To Stock.</p>
     * <p>Preconditions:</p>
     * <p>1.Order is created.</p>
     * <p>2.Invoice is created
     * <p>3.The invoice of the order is open in admin
     * <p>Steps:</p>
     * <p>1. Click 'Credit Memo' button.</p>
     * <p>2. Fill product and quantity to credit for.</p>
     * <p>3. Click 'Refund Offline' button.</p>
     * <p>Expected result:</p>
     * <p>Order Update message is sent out.</p>
     *
     * @test
     *
     */
    public function creditMemoFullOrderNotReturnToStock()
    {
        //Data
        $product1 = self::$_preconditionsData->getProduct1();
        $sku1 = $product1['general_sku'];
        $qty1 = self::FULL_ORDER_QTY;
        $product2 = self::$_preconditionsData->getProduct2();
        $sku2 = $product2['general_sku'];
        $qty2 = self::FULL_ORDER_QTY;
        $orderId = self::$_preconditionsData->getOrderId();
        $creditMemoData = array(
            'product_1' => array(
                'return_filter_sku' =>$sku1,
                'return_to_stock' => ReturnToStock::NO,
                'qty_to_refund' => $qty1,
            ),
            'product_2' => array(
                'return_filter_sku' =>$sku2,
                'return_to_stock' => ReturnToStock::NO,
                'qty_to_refund' => $qty2,
            ),
        );
        //Steps
        $this->_refundOffline($creditMemoData);

        //Verify
        $expectedMsgs = array(
            array( "topic" => OrderManagement::ORDER_RETURN,
                'message.orderNumber' => $orderId,
                'message.orderLines.array.0.quantity' => $qty1,
                'message.orderLines.array.0.productSku' => $sku1,
                'message.orderLines.array.0.price.price.amount' => $product1['prices_price'],
                'message.orderLines.array.1.quantity' => $qty2,
                'message.orderLines.array.1.productSku' => $sku2,
                'message.orderLines.array.1.price.price.amount' => $product2['prices_price'],
                'message.dateOrdered.string' => '!.+!',
            ),
        );
        $msgs = $this->_get2dXMessages();
        $this->verifyXMessage($expectedMsgs, $msgs);
    }

    /**
     * <p>Credit Memo Partial Order Return To Stock.</p>
     * <p>Preconditions:</p>
     * <p>1.Order is created.</p>
     * <p>2.Invoice is created
     * <p>3.The invoice of the order is open in admin
     * <p>Steps:</p>
     * <p>1. Click 'Credit Memo' button.</p>
     * <p>2. Fill product and quantity to credit for.</p>
     * <p>3. Click 'Refund Offline' button.</p>
     * <p>Expected result:</p>
     * <p>Order Update message is sent out.</p>
     * <p>Stock Item Updated message is send out.</p>
     *
     * @test
     *
     */
    public function creditMemoPartialOrderReturnToStock()
    {
        //Data
        $product1 = self::$_preconditionsData->getProduct1();
        $sku1 = $product1['general_sku'];
        $qty1 = self::FULL_ORDER_QTY - 2;
        $product2 = self::$_preconditionsData->getProduct2();
        $sku2 = $product2['general_sku'];
        $qty2 = self::FULL_ORDER_QTY - 3;
        $orderId = self::$_preconditionsData->getOrderId();
        $creditMemoData = array(
            'product_1' => array(
                'return_filter_sku' =>$sku1,
                'return_to_stock' => ReturnToStock::YES,
                'qty_to_refund' => $qty1,
            ),
            'product_2' => array(
                'return_filter_sku' =>$sku2,
                'return_to_stock' => ReturnToStock::NO,
                'qty_to_refund' => $qty2,
            ),
        );

        //Steps
        $this->_refundOffline($creditMemoData);

        //Verify
        $expectedMsgs = array(
            array( "topic" => OrderManagement::PARTIAL_ORDER_RETURN,
                'message.orderNumber' => $orderId,
                'message.returnedItems.0.productSku' => $sku1,
                'message.returnedItems.0.quantity' => $qty1,
                'message.returnedItems.1.productSku' => $sku2,
                'message.returnedItems.1.quantity' => $qty2,
            ),
            array( "topic" => Inventory::STOCK_ITEM_UPDATED,
                'message.stockItems.0.sku' => $sku1,
                'message.stockItems.0.quantity' => ($product1['inventory_qty'] - self::FULL_ORDER_QTY + $qty1),
            ),
        );
        $msgs = $this->_get2dXMessages();
        $this->verifyXMessage($expectedMsgs, $msgs);
    }

    /**
     * <p>Credit Memo Full Order Return To Stock.</p>
     * <p>Preconditions:</p>
     * <p>1.Order is created.</p>
     * <p>2.Invoice is created
     * <p>3.The invoice of the order is open in admin
     * <p>Steps:</p>
     * <p>1. Click 'Credit Memo' button.</p>
     * <p>2. Fill product and quantity to credit for.</p>
     * <p>3. Click 'Refund Offline' button.</p>
     * <p>Expected result:</p>
     * <p>Order Update message is sent out.</p>
     * <p>Stock Item Updated message is send out.</p>
     *
     * @test
     *
     */
    public function creditMemoFullOrderReturnToStock()
    {
        //Data
        $product1 = self::$_preconditionsData->getProduct1();
        $sku1 = $product1['general_sku'];
        $qty1 = self::FULL_ORDER_QTY;
        $product2 = self::$_preconditionsData->getProduct2();
        $sku2 = $product2['general_sku'];
        $qty2 = self::FULL_ORDER_QTY;
        $orderId = self::$_preconditionsData->getOrderId();
        $creditMemoData = array(
            'product_1' => array(
                'return_filter_sku' =>$sku1,
                'return_to_stock' => ReturnToStock::YES,
                'qty_to_refund' => $qty1,
            ),
            'product_2' => array(
                'return_filter_sku' =>$sku2,
                'return_to_stock' => ReturnToStock::NO,
                'qty_to_refund' => $qty2,
            ),
        );

        //Steps
        $this->_refundOffline($creditMemoData);

        //Verify
        $expectedMsgs = array(
            array( "topic" => OrderManagement::ORDER_RETURN,
                'message.orderNumber' => $orderId,
            ),
            array( "topic" => Inventory::STOCK_ITEM_UPDATED,
                'message.stockItems.0.sku' => $sku1,
                'message.stockItems.0.quantity' => $product1['inventory_qty'],
            ),
        );
        $msgs = $this->_get2dXMessages();
        $this->verifyXMessage($expectedMsgs, $msgs);
    }

    /**
     * Helper Method:
     * <p>Refund product offline</p>
     *
     * @param array $creditMemoData
     */
    protected function _refundOffline($creditMemoData)
    {
        $this->orderCreditMemoHelper()->createCreditMemoAndVerifyProductQty("refund_offline", $creditMemoData);
    }
}