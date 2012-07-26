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
 * Cancel Orders
 *
 * @package     selenium
 * @subpackage  tests
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

use Xcom_Chronicle_XMessages_WebStore as WebStore;
use Xcom_Chronicle_XMessages_Inventory as Inventory;
use Xcom_Chronicle_XMessages_OrderManagement as OrderManagement;

class Xcom_Chronicle_Web_Order_CancelTest extends Xcom_Chronicle_TestCase
{
    /**
     * sku of the product in the order
     * @var string
     */
    protected $_simpleSku;

    /**
     * order id
     * @var string
     */
    protected $_orderId;

    /**
     * Minimum number of products in Magento
     */
    const MINIMUM_PRODUCT_COUNT = 5;


    /**
     * <p>Preconditions:</p>
     * <p>Log in to Backend.</p>
     */
    public function setUpBeforeTests()
    {
        $this->loginAdminUser();
    }

    /**
     * <p>Preconditions:</p>
     * <p>Make sure there are at least 5 simple products in Magento
     * <p>An order is created.</p>
     */
    public function setUpBeforeEachTest()
    {

        $initialProductCount = $this->_getSimpleProductCount();
        if ($initialProductCount < self::MINIMUM_PRODUCT_COUNT) {
            for($i = $initialProductCount; $i < self::MINIMUM_PRODUCT_COUNT; $i++) {
                $this->_createSimpleProduct();
            }
            $initialProductCount = $i;
        }
        $products = $this->_getSimpleProducts(5, rand(0, $initialProductCount - 5));
        $productItems =  $products->getItems();
        $skus = array();
        foreach ($productItems as $k => $item) {
            $skus[] = $item->getSku();
        }

        //Data
        $this->_simpleSku = $skus['0'];
        $orderData = $this->loadDataSet('SalesOrder', 'order_newcustomer_checkmoney_flatrate_usa', array('filter_sku' => $this->_simpleSku, 'product_qty' => 10));
        //Steps
        $this->navigate('manage_sales_orders');
        $this->orderHelper()->createOrder($orderData);
        //Verifying
        $this->assertTrue($this->successMessage('success_created_order'), $this->messages);
        $this->_orderId = $this->orderHelper()->defineOrderId();
    }

    protected function assertPreConditions()
    {
        $this->addParameter('id', '0');
    }

    /**
     * <p>Cancel.</p>
     * <p>Preconditions:</p>
     * <p>1.Order is created.</p>
     * <p>2.The order is open in admin
     * <p>Steps:</p>
     * <p>Click 'Cancel' button.</p>
     * <p>Expected result:</p>
     * <p>OrderCanceled message is sent out.</p>
     * <p>StockItemUpdated message is sent out.</p>
     * <p>WebStoreOfferQuantityUpdated message is sent out.</p>
     *
     * @test
     */
    public function cancelOrder()
    {

        //Steps
        $this->clickButtonAndConfirm('cancel', 'confirmation_for_cancel');
        //Verifying
        $this->assertTrue($this->successMessage('success_canceled_order'), $this->messages);

        //Verify
        $expectedMsgs = array(
            array( "topic" => OrderManagement::ORDER_CANCELLED,
                'message.cancelledOrders.0.orderNumber.string' => $this->_orderId,
                'message.cancelledOrders.0.status' => 'CANCELLED',
                'message.cancelledOrders.0.orderLines.0.productSku' => $this->_simpleSku,
                'message.cancelledOrders.0.orderLines.0.quantity' => 10,
                'message.cancelledOrders.0.orderLines.0.status' => 'CANCELLED',
            ),
            array( 'topic' => WebStore::OFFER_QUANTITY_UPDATED,
                'message.sku' => $this->_simpleSku,
            ),
            array( 'topic' => Inventory::STOCK_ITEM_UPDATED,
                'message.stockItems.0.sku' => $this->_simpleSku,
            ),
        );
        $msgs = $this->_get2dXMessages();
        $this->verifyXMessage($expectedMsgs, $msgs);
    }
}

