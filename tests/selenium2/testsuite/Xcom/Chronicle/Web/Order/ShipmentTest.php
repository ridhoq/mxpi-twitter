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
 * Ship Orders
 *
 * @package     selenium
 * @subpackage  tests
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

use Xcom_Chronicle_XMessages_OrderManagement as OrderManagement;

class Xcom_Chronicle_Web_Order_ShipmentTest extends Xcom_Chronicle_TestCase
{
    /**
     * Data in test preconditions
     * @var Varien_Object
     */
    protected static $_preconditionsData;

    /**
     * Refers to the tracking carrier for this shipment test
     */
    const TRACKING_CARRIER = 'Federal Express';

    /**
     * Refers to the tracking number for this shipment test
     */
    const TRACKING_NUMBER =  '123456124';

    /**
     * Minimum number of products in Magento
     */
    const MINIMUM_PRODUCT_COUNT = 5;

    /**
     * The quantity of a product in the order
     */
    const FULL_ORDER_QTY = 10;

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
     * <p>Make sure create 2 products: one with tax, one without tax
     * <p>An order is created.</p>
     */
    public function setUpBeforeEachTest()
    {
        if (!isset(self::$_preconditionsData)) {
            self::$_preconditionsData = new Varien_Object();
            $productData1 = $this->loadDataSet('Product', 'simple_product_visible', array('prices_tax_class' => 'Taxable Goods'));
            $this->_createProduct($productData1, 'simple');
            self::$_preconditionsData->setProduct1($productData1);
            $productData2 = $this->loadDataSet('Product', 'simple_product_visible',
                array('prices_price' => '5.99'));
            $this->_createProduct($productData2, 'simple');
            self::$_preconditionsData->setProduct2($productData2);
        }

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
    }

    protected function assertPreConditions()
    {
        $this->addParameter('id', '0');
    }

    /**
     * <p>Ship Partial Order Without Tracking.</p>
     * <p>Preconditions:</p>
     * <p>1.Order is created.</p>
     * <p>2.The order is open in admin
     * <p>Steps:</p>
     * <p>1. Click 'Ship' button.</p>
     * <p>2. Fill products and quantity to ship.</p>
     * <p>3. Click 'Sumbit Shipment' button.</p>
     * <p>Expected result:</p>
     * <p>OrderShipped message is sent out.</p>
     *
     * @test
     */
    public function shipPartialOrderWithoutTracking()
    {
        //Data
        $product1 = self::$_preconditionsData->getProduct1();
        $sku1 = $product1['general_sku'];
        $shipQty1 = self::FULL_ORDER_QTY - 4;
        $product2 = self::$_preconditionsData->getProduct2();
        $sku2 = $product2['general_sku'];
        $shipQty2 = self::FULL_ORDER_QTY - 3;
        $order = self::$_preconditionsData->getOrder();
        $shipment = $this->loadDataSet('SalesOrder', 'products_to_ship',
            array(
                'ship_product_sku' => $sku1,
                'ship_product_qty' => $shipQty1,
                'product_2' => array(
                    'ship_product_sku' => $sku2,
                    'ship_product_qty' => $shipQty2,
                )
            )
        );
        $trackingData = array();
        //Steps createShipmentAndAddTracking()
        $this->_createShipmentAndAddTracking($trackingData, $shipment);

        //Verify
        $expectedMsgs = array(
            array( "topic" => OrderManagement::ORDER_SHIPPED,
                'message.orderNumber' => self::$_preconditionsData->getOrderId(),
                'message.shipment.shipmentId' => '!\d+!',
                'message.shipment.shippingFees.amount' => '0',
                'message.shipment.trackingDetails' => null,
                'message.shipment.shippingMethod.string' => 'Flat Rate - Fixed',
                'message.sourceId' => 'Magento',
                'message.referralSource' => null,
                'message.orderLines.array.0.orderNumber.string' => self::$_preconditionsData->getOrderId(),
                'message.orderLines.array.0.quantity' => $shipQty1,
                'message.orderLines.array.0.productSku' => $sku1,
                'message.orderLines.array.0.unitPrice.price.amount' => $product1['prices_price'],
                'message.orderLines.array.0.price.amount' => $product1['prices_price'] * $shipQty1,
                'message.orderLines.array.0.destination.name.firstName' => $order['billing_addr_data']['billing_first_name'],
                'message.orderLines.array.0.destination.name.lastName' => $order['billing_addr_data']['billing_last_name'],
                'message.orderLines.array.0.destination.address.street1' => $order['billing_addr_data']['billing_street_address_1'],
                'message.orderLines.array.0.destination.address.city' => $order['billing_addr_data']['billing_city'],
                'message.orderLines.array.0.destination.address.postalCode' => $order['billing_addr_data']['billing_zip_code'],
                'message.orderLines.array.0.destination.address.stateOrProvince.string' => $order['billing_addr_data']['billing_state'],
                'message.orderLines.array.0.status' => 'PARTIALLY_SHIPPED',
                'message.orderLines.array.1.orderNumber.string' => self::$_preconditionsData->getOrderId(),
                'message.orderLines.array.1.quantity' => $shipQty2,
                'message.orderLines.array.1.productSku' => $sku2,
                'message.orderLines.array.1.unitPrice.price.amount' => $product2['prices_price'],
                'message.orderLines.array.1.price.amount' => $product2['prices_price'] * $shipQty2,
                'message.orderLines.array.1.status' => 'PARTIALLY_SHIPPED',
                'message.orderLines.array.1.destination.name.firstName' => $order['billing_addr_data']['billing_first_name'],
                'message.orderLines.array.1.destination.name.lastName' => $order['billing_addr_data']['billing_last_name'],
                'message.orderLines.array.1.destination.address.street1' => $order['billing_addr_data']['billing_street_address_1'],
                'message.orderLines.array.1.destination.address.city' => $order['billing_addr_data']['billing_city'],
                'message.orderLines.array.1.destination.address.postalCode' => $order['billing_addr_data']['billing_zip_code'],
                'message.orderLines.array.1.destination.address.stateOrProvince.string' => $order['billing_addr_data']['billing_state'],
            ),
        );
        $msgs = $this->_get2dXMessages();
        $this->verifyXMessage($expectedMsgs, $msgs);
    }

    /**
     * <p>Ship Full Order Without Tracking.</p>
     * <p>Preconditions:</p>
     * <p>1.Order is created.</p>
     * <p>2.The order is open in admin
     * <p>Steps:</p>
     * <p>1. Click 'Ship' button.</p>
     * <p>2. Fill products and quantity to ship.</p>
     * <p>3. Click 'Sumbit Shipment' button.</p>
     * <p>Expected result:</p>
     * <p>OrderShipped message is sent out.</p>
     *
     * @test
     */
    public function shipFullOrderWithoutTracking()
    {
        //Data
        $product1 = self::$_preconditionsData->getProduct1();
        $sku1 = $product1['general_sku'];
        $product2 = self::$_preconditionsData->getProduct2();
        $sku2 = $product2['general_sku'];
        $shipQty = self::FULL_ORDER_QTY;
        $shipment = $this->loadDataSet('SalesOrder', 'products_to_ship',
            array(
                'ship_product_sku' => $sku1,
                'ship_product_qty' => $shipQty,
                'product_2' => array(
                    'ship_product_sku' => $sku2,
                    'ship_product_qty' => $shipQty,
                )
            )
        );
        $trackingData = array();
        //Steps createShipmentAndAddTracking()
        $this->_createShipmentAndAddTracking($trackingData, $shipment);

        //Verify
        $expectedMsgs = array(
            array( "topic" => OrderManagement::ORDER_SHIPPED,
                'message.orderNumber' => self::$_preconditionsData->getOrderId(),
                'message.shipment.shipmentId' => '!\d+!',
                'message.shipment.shippingFees.amount' => '0',
                'message.shipment.trackingDetails' => null,
                'message.shipment.shippingMethod.string' => 'Flat Rate - Fixed',
                'message.sourceId' => 'Magento',
                'message.referralSource' => null,
                'message.orderLines.array.0.orderNumber.string' => self::$_preconditionsData->getOrderId(),
                'message.orderLines.array.0.quantity' => $shipQty,
                'message.orderLines.array.0.productSku' => $sku1,
                'message.orderLines.array.0.status' => 'SHIPPED',
                'message.orderLines.array.1.orderNumber.string' => self::$_preconditionsData->getOrderId(),
                'message.orderLines.array.1.quantity' => $shipQty,
                'message.orderLines.array.1.productSku' => $sku2,
                'message.orderLines.array.1.status' => 'SHIPPED',
            ),
        );
        $msgs = $this->_get2dXMessages();
        $this->verifyXMessage($expectedMsgs, $msgs);
    }

    /**
     * <p>Ship Order Without Tracking.</p>
     * <p>Preconditions:</p>
     * <p>1.Order is created.</p>
     * <p>2.The order is open in admin
     * <p>Steps:</p>
     * <p>1. Click 'Ship' button.</p>
     * <p>2. Fill products and quantity to ship.</p>
     * <p>3. Fill tracking information.</p>
     * <p>4. Click 'Sumbit Shipment' button.</p>
     * <p>Expected result:</p>
     * <p>OrderShipped message is sent out.</p>
     *
     * @test
     */
    public function shipFullOrderWithTracking()
    {

        //Data
        $product1 = self::$_preconditionsData->getProduct1();
        $sku1 = $product1['general_sku'];
        $product2 = self::$_preconditionsData->getProduct2();
        $sku2 = $product2['general_sku'];
        $shipQty = self::FULL_ORDER_QTY;
        $shipment = $this->loadDataSet('SalesOrder', 'products_to_ship',
            array(
                'ship_product_sku' => $sku1,
                'ship_product_qty' => $shipQty,
                'product_2' => array(
                    'ship_product_sku' => $sku2,
                    'ship_product_qty' => $shipQty,
                )
            ));
        $trackingData = array( 'carrier' => self::TRACKING_CARRIER,
            'number' => self::TRACKING_NUMBER);
        //Steps createShipmentAndAddTracking()
        $this->_createShipmentAndAddTracking($trackingData, $shipment);

        //Verify
        $expectedMsgs = array(
            array( "topic" => OrderManagement::ORDER_SHIPPED,
                'message.orderNumber' => self::$_preconditionsData->getOrderId(),
                'message.shipment.shipmentId' => '!\d+!',
                'message.shipment.shippingFees.amount' => '0',
                'message.shipment.trackingDetails.array.0.trackingNumbers.array.0' => self::TRACKING_NUMBER,
                'message.shipment.trackingDetails.array.0.carrier.string' => self::TRACKING_CARRIER,
                'message.shipment.trackingDetails.array.0.service' => null,
                'message.shipment.trackingDetails.array.0.serviceType.com.x.ordermanagement.v2.ShippingServiceType' => 'MERCHANT_SHIPPED',
                'message.shipment.shippingMethod.string' => 'Flat Rate - Fixed',
                'message.sourceId' => 'Magento',
                'message.referralSource' => null,
                'message.orderLines.array.0.orderNumber.string' => self::$_preconditionsData->getOrderId(),
                'message.orderLines.array.0.quantity' => $shipQty,
                'message.orderLines.array.0.productSku' => $sku1,
                'message.orderLines.array.0.status' => 'SHIPPED',
                'message.orderLines.array.1.orderNumber.string' => self::$_preconditionsData->getOrderId(),
                'message.orderLines.array.1.quantity' => $shipQty,
                'message.orderLines.array.1.productSku' => $sku2,
                'message.orderLines.array.1.status' => 'SHIPPED',
            ),
        );
        $msgs = $this->_get2dXMessages();
        $this->verifyXMessage($expectedMsgs, $msgs);
    }

    /**
     * Helper Method:
     * <p>Create Shipment with given shipment data and with given tracking data</p>
     *
     * @param array $trackingData
     * @param array $shipmentData
     */
    protected function _createShipmentAndAddTracking(array $trackingData, array $shipmentData)
    {
        $shipmentData = $this->clearDataArray($shipmentData);
        $verify = array();

        $this->clickButton('ship');

        foreach ($shipmentData as $product => $options) {
            if (is_array($options)) {
                $sku = (isset($options['ship_product_sku'])) ? $options['ship_product_sku'] : NULL;
                $productQty = (isset($options['ship_product_qty'])) ? $options['ship_product_qty'] : '%noValue%';
                if ($sku) {
                    $verify[$sku] = $productQty;
                    $this->addParameter('sku', $sku);
                    $this->fillForm(array('qty_to_ship' => $productQty));
                }
            }
        }
        if (!$verify) {
            $setXpath = $this->_getControlXpath('fieldset', 'product_line_to_ship');
            $skuXpath = $this->_getControlXpath('field', 'product_sku');
            $qtyXpath = $this->_getControlXpath('field', 'product_qty');
            $productCount = $this->getXpathCount($setXpath);
            for ($i = 1; $i <= $productCount; $i++) {
                $prod_sku = $this->getText($setXpath . "[$i]" . $skuXpath);
                $prod_sku = trim(preg_replace('/SKU:|\\n/', '', $prod_sku));
                $prod_qty = $this->getAttribute($setXpath . "[$i]" . $qtyXpath . '/@value');
                $verify[$prod_sku] = $prod_qty;
            }
        }

        if (!empty($trackingData)) {
            $this->clickButton('add_tracking_number', false);
            $this->addParameter('id', 1);
            $this->fillForm($trackingData);
        }

        $this->clickButton('submit_shipment');
        $this->assertTrue($this->successMessage('success_creating_shipment'), $this->messages);
    }
}

