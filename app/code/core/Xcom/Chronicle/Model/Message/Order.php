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
 * @category    Xcom
 * @package     Xcom_Chronicle
 * @copyright   Copyright (c) 2010 Magento Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

class Xcom_Chronicle_Model_Message_Order extends Varien_Object
{
    // Status enum
    const STATUS_NEW               = 'NEW';
    const STATUS_ON_HOLD           = 'ON_HOLD';
    const STATUS_BACKORDERED       = 'BACKORDERED';
    const STATUS_PENDING_PAYMENT   = 'PENDING_PAYMENT';
    const STATUS_READY_TO_SHIP     = 'READY_TO_SHIP';
    const STATUS_PARTIALLY_SHIPPED = 'PARTIALLY_SHIPPED';
    const STATUS_SHIPPED           = 'SHIPPED';
    const STATUS_PROCESSING_RETURN = 'PROCESSING_RETURN'; // Not used
    const STATUS_EXCHANGED         = 'EXCHANGED'; // Not used
    const STATUS_CANCELLED         = 'CANCELLED';
    const STATUS_COMPLETED         = 'COMPLETED';
    const STATUS_PAID              = 'PAID';

    const SHIPPING_SERVICE_MERCHANT = 'MERCHANT_SHIPPED';
    const SHIPPING_SERVICE_DROP     = 'DROP_SHIP';
    const SHIPPING_SERVICE_SERVICE  = 'SHIPPING_SERVICE';
    const SHIPPING_SERVICE_CALL     = 'WILL_CALL';

    const DISCOUNT_SALE     = 'STOREWIDE_SALE';
    const DISCOUNT_SHIPPING = 'SHIPPING_DISCOUNT'; // Not supported
    const DISCOUNT_PRODUCT  = 'PRODUCT_DISCOUNT';
    const DISCOUNT_OTHER    = 'OTHER';

    const TAXABILITY_TAXABLE     = 'TAXABLE';
    const TAXABILITY_NON_TAXABLE = 'NONTAXABLE';
    const TAXABILITY_EXEMPT      = 'EXEMPT';
    const TAXABILITY_DEFERRED    = 'DEFERRED';

    // Payment methods enum
    const PAYMENT_METHOD_AMEX                            = 'AMEX';
    const PAYMENT_METHOD_CASH_ON_DELIVERY                = 'CASH_ON_DELIVERY';
    const PAYMENT_METHOD_CHECK                           = 'CHECK';
    const PAYMENT_METHOD_CREDIT_CARD                     = 'CREDIT_CARD';
    const PAYMENT_METHOD_DINERS                          = 'DINERS';
    const PAYMENT_METHOD_DISCOVER                        = 'DISCOVER';
    const PAYMENT_METHOD_ESCROW                          = 'ESCROW';
    const PAYMENT_METHOD_INTEGRATED_MERCHANT_CREDIT_CARD = 'INTEGRATED_MERCHANT_CREDIT_CARD';
    const PAYMENT_METHOD_MASTERCARD                      = 'MASTERCARD';
    const PAYMENT_METHOD_MONEY_ORDER                     = 'MONEY_ORDER';
    const PAYMENT_METHOD_MONEY_TRANSFER                  = 'MONEY_TRANSFER';
    const PAYMENT_METHOD_MONEYBOOKERS                    = 'MONEYBOOKERS';
    const PAYMENT_METHOD_PAYMATE                         = 'PAYMATE';
    const PAYMENT_METHOD_PAYMENT_ON_PICKUP               = 'PAYMENT_ON_PICKUP';
    const PAYMENT_METHOD_PAYPAL                          = 'PAYPAL';
    const PAYMENT_METHOD_PROPAY                          = 'PROPAY';
    const PAYMENT_METHOD_VISA                            = 'VISA';

    const PAYMENT_STATUS_AUTHORIZED     = 'AUTHORIZED';
    const PAYMENT_STATUS_PAID           = 'PAID';
    const PAYMENT_STATUS_NOT_AUTHORIZED = 'NOT_AUTHORIZED';
    const PAYMENT_STATUS_PARTIAL        = 'PARTIAL';
    const PAYMENT_STATUS_NONE           = 'NONE';

    const TYPE_ORDER         = 'order';
    const TYPE_SIMPLE        = 'simple';
    const TYPE_NON_SENSITIVE = 'non_sensitive';

    const PHONE_HOME    = 'HOME';
    const PHONE_MOBILE  = 'MOBILE';
    const PHONE_WORK    = 'WORK';
    const PHONE_FAX     = 'FAX';
    const PHONE_UNKNOWN = 'UNKNOWN';

    protected $_baseCurrencyCode = null;
    protected $_channelOrder = null;
    protected $_siteCode = null;
    protected $_accountId = null;
    protected $_orderNumber = null;
    protected $_orderType = self::TYPE_ORDER;
    const LINE_ITEM_TYPE_DEFAULT = 'default';
    const LINE_ITEM_TYPE_REFUNDED = 'refunded';
    const LINE_ITEM_TYPE_INVOICED = 'invoiced';
    /* @var Mage_Sales_Model_Order */
    protected $_order;
    /* @var Xcom_Chronicle_Helper_Data */
    protected $_helper;

    /**
     * For 'type' in params see TYPE_* constant
     * @param array $params
     * @throws Xcom_Chronicle_Exception
     */
    public function __construct($params)
    {
        if (empty($params['order'])) {
            throw $this->_exception($this->__('Order has not been passed'));
        }

        $this->_order = $params['order'];

        if (isset($params['type'])) {
            if (!in_array($params['type'], array(self::TYPE_ORDER, self::TYPE_NON_SENSITIVE, self::TYPE_SIMPLE))) {
                throw $this->_exception($this->__('Unknown order type: %s', $params['type']));
            }

            $this->_orderType = $params['type'];
        }

        $this->_setupIfChannelOrder();
        // cache the proper order number
        $this->_orderNumber = $this->_getOrderNumber();

        $this->setData($this->_getOrder());
    }

    /**
     * Returns Data helper
     *
     * @return Xcom_Chronicle_Helper_Data
     */
    protected function _getHelper()
    {
        if (!$this->_helper) {
            $this->_helper = Mage::helper('xcom_chronicle');
        }

        return $this->_helper;
    }

    /**
     * Returns translated string
     *
     * @param string $message
     * @param string $token
     * @return string
     */
    public function __($message, $token = '')
    {
        if ($token) {
            return $this->_getHelper()->__($message, $token);
        } else {
            return $this->_getHelper()->__($message);
        }
    }

    /**
     * Returns true if the Mage_Sales_Model_Order used to construct this
     * Order is a channel order
     * @return bool
     */
    public function isChannelOrder()
    {
        if(null === $this->_channelOrder) {
            return false;
        }
        $data = $this->_channelOrder->getData();
        return !empty($data);
    }

    /**
     * Returns a cached account id that will change depending on
     * whether this is a channel order or not.
     * @return null
     */
    public function getAccountId()
    {
        return $this->_accountId;
    }

    /**
     * Returns a cached site code that will changed depending on
     * whether this is a channel order or not.
     * @return null
     */
    public function getSiteCode()
    {
        return $this->_siteCode;
    }

    /**
     * Helper to get resource model from xcom order.
     * Can be used in unit testing to inject data
     * @return Object
     */
    protected function _getXcomOrderResource()
    {
        return Mage::getResourceModel('xcom_channelorder/order');
    }

    /**
     * Queries the xcom order table to see if this order is a channel order.
     * If a corresponding channel order data is found then it stores the
     * channel order model in _channelOrder.
     * @param $order_id
     * @return null
     */
    protected function _getXcomOrderModel($order_id)
    {
        $orders = $this->_getXcomOrderResource();
        if(empty($orders)) {
            return null;
        }
        $this->_channelOrder = Mage::getModel('xcom_channelorder/order');
        $orders->load($this->_channelOrder, $order_id, 'order_id');
        return $this->_channelOrder;
    }

    /**
     * Takes an order in
     * @return mixed
     */
    protected function _setupIfChannelOrder()
    {
        if(null === $this->_channelOrder) {
            $this->_channelOrder = $this->_getXcomOrderModel($this->_order->getId());

            if(null === $this->_channelOrder) {
                return;
            }
            $data = $this->_channelOrder->getData();
            $emptyData = empty($data);

            if(!$emptyData) {
                $channel = Mage::getModel('xcom_mmp/channel')->load($this->_channelOrder->getChannelId());
                $this->_accountId = $channel->getXaccountId();
                $this->_siteCode = $channel->getSiteCode();

            }
        }
    }

    /**
     * Returns a cached order number for this order.  The order number
     * can change if this is a channel order.  Allows for re-use by
     * Shipment object.
     * @return null
     */
    public function getOrderNumber()
    {
        return $this->_orderNumber;
    }

    /**
     * Finds and caches the order number for this order
     * @return string
     */
    protected function _getOrderNumber()
    {
        if($this->isChannelOrder()) {
            return $this->_channelOrder->getOrderNumber(); //getChannelOrderId();
        }
        return $this->_order->getRealOrderId();
    }

    /**
     * Finds and caches the source for the order
     * @return string
     */
    public function getSource()
    {
        if($this->isChannelOrder()) {
            return $this->_channelOrder->getSource();
        }
        return 'Magento';
    }

    /**
     * Finds and caches the sourceId for the order
     * @return string
     */
    protected function _getSourceId()
    {
        if($this->isChannelOrder()) {
            return $this->_channelOrder->getSourceId();
        }
        return Mage::getBaseUrl();
    }

    /**
     * Returns value from TaxabilityType enum
     *
     * @param Mage_Sales_Model_Order_Item $item
     * @return string
     */
    protected function _getTaxabilityType($item)
    {
        $type = $item->getTaxAmount() == 0 ? self::TAXABILITY_EXEMPT : self::TAXABILITY_TAXABLE;
        return $type;
    }

    /**
     * Extracts the tax percent from the item.
     * @param Mage_Sales_Model_Order_Item $item
     * @return float
     */
    protected function _getTaxPercent($item)
    {
        return (float) $item->getTaxPercent();
    }

    /**
     * Grab the tax amount from the line item based on the taxSource input
     * @param Mage_Sales_Model_Order_Item $item
     * @param string $taxSource
     * @return array represents the tax price with currency code
     */
    protected function _getTaxesCharged($item, $qty, $taxSource = self::LINE_ITEM_TYPE_DEFAULT)
    {
        $store          = Mage::app()->getStore($item->getStoreId());
        $tax = 0;
        switch ($taxSource) {
            case self::LINE_ITEM_TYPE_DEFAULT:
                $tax = $item->getTaxAmount() / $qty;
                break;
            case self::LINE_ITEM_TYPE_REFUNDED:
                $tax = $store->roundPrice($item->getTaxRefunded() / $qty);
                break;
            case self::LINE_ITEM_TYPE_INVOICED:
                $tax = $item->getTaxInvoiced() / $qty;
                break;
        }

        return $this->_createCurrencyAmount($tax);
    }

    /**
     * Creates OCL Tax record
     *
     * @param Mage_Sales_Model_Order_Item $item
     * @return array
     */
    protected function _createTax($item, $qty,  $taxSource = self::LINE_ITEM_TYPE_DEFAULT)
    {
        $data = array(
            'taxability' => $this->_getTaxabilityType($item),
            'taxType' => '', // Not used in Magento
            'effectiveRate' => $this->_getTaxPercent($item),
            'taxesCharged' => $this->_getTaxesCharged($item, $qty, $taxSource),
        );

        return $data;
    }

    /**
     * Creates OCL PromotionalDiscount record
     *
     * @param string $code        Discount code (which user enters)
     * @param string $description Description of the discount
     * @param string $type        See Discount_* constants
     * @param float  $amount      Amount of the discount
     * @return array
     * @throws Xcom_Chronicle_Exception
     */
    protected function _createPromotionalDiscount($code, $description, $type, $amount)
    {
        $allowedTypes = array(self::DISCOUNT_SALE, self::DISCOUNT_OTHER, self::DISCOUNT_PRODUCT);

        if (!in_array($type, $allowedTypes)) {
            throw $this->_exception($this->__('Wrong discount type: %s', $type));
        }

        $data = array(
            'code' => (string)$code,
            'promoDescription' => (string)$description,
            'type' => $type,
            'amount' => $this->_createCurrencyAmount($amount),
        );

        return $data;
    }

    /**
     * Get all applied discounts for particular order item
     *
     * @param Mage_Sales_Model_Order_Item $item
     * @return array
     */
    protected function _getAllDiscounts(Mage_Sales_Model_Order_Item $item)
    {
        $discounts = array();
        $order = $item->getOrder();
        /* @var $catalogRule Mage_CatalogRule_Model_Rule */
        $catalogRule = Mage::getModel('catalogrule/rule');
        $catalogRule->setWebsiteIds(Mage::app()->getStore()->getWebsiteId());
        $catalogRule->setProductsFilter($item->getProductId());
        $matchingProductIds = $catalogRule->getMatchingProductIds();

        if (in_array($item->getProductId(), $matchingProductIds)) {
            // @todo what if there's several catalog rules matching product? Work out this situation
            $catalogRule->load($catalogRule->getConditions()->getId());
            $product = $item->getProduct();
            $rulePrice = $catalogRule->calcProductPriceRule($product, $product->getPrice());
            $discountAmount = $product->getPrice() - $rulePrice;

            $discounts[] = $this->_createPromotionalDiscount(
                $catalogRule->getName(), $catalogRule->getDescription(), self::DISCOUNT_PRODUCT, $discountAmount
            );
        }

        if ($item->getDiscountAmount()) {
            $discounts[] = $this->_createPromotionalDiscount(
                $this->_order->getCouponCode(),
                $this->_order->getDiscountDescription(),
                self::DISCOUNT_SALE,
                $item->getDiscountAmount()
            );
        }

        return $discounts;
    }


    /**
     * Calculates the Row Total Price the same way the UI does.
     * @param Mage_Sales_Model_Order_Item $item
     * @return float
     */
    public function calculateRowTotalPrice($item)
    {
        return (
            $item->getBaseRowTotal()
            + $item->getBaseTaxAmount()
            + $item->getBaseHiddenTaxAmount()
            + $item->getBaseWeeeTaxAppliedRowAmount()
            - $item->getBaseDiscountAmount()
        );
    }

    /**
     * Returns OCL OrderLinePrice record
     *
     * @param Mage_Sales_Model_Order_Item $item
     * @return array
     */
    protected function _createOrderLinePrice($item, $qty = 1, $type = self::LINE_ITEM_TYPE_DEFAULT)
    {
        $store          = Mage::app()->getStore($item->getStoreId());

        // Unclear on the consequences of rounding the discount.  Maybe we shouldn't round it to be more accurate.
        $discount = $store->roundPrice($item->getDiscountAmount() / $qty);

        // We won't round the price since it makes for inaccurate calculations when using this data
        $totalPrice = $this->calculateRowTotalPrice($item) / $qty;

        $data = array(
            'totalPrice'          => $this->_createCurrencyAmount($totalPrice),
            'price'               => $this->_createCurrencyAmount($item->getBasePrice()),
            'insuranceCost'       => null, // optional
            'totalDiscountAmount' => $this->_createCurrencyAmount($discount),
            'taxAmount'           => $this->_createTax($item, $qty, $type),
            'allDiscounts'        => null, // optional $this->_getAllDiscounts($item) @todo
            'additionalCosts'     => null, // optional, array<AdditionalCost>
        );

        return $data;
    }

    /**
     * Creates OCL DateRange record
     *
     * @param string $beginDate
     * @param string $endDate
     * @return array
     */
    protected function _createDateRange($beginDate = null, $endDate = null)
    {
        $data = array(
            'beginDate' => $beginDate,
            'endDate' => $endDate
        );

        return $data;
    }

    /**
     * Creates OCL OrderStatus record
     *
     * @param Mage_Sales_Model_Order_Item $item
     * @return string
     */
    protected function _createOrderItemStatus(Mage_Sales_Model_Order_Item $item)
    {
        switch ($item->getStatusId()) {
            case Mage_Sales_Model_Order_Item::STATUS_SHIPPED:
                return self::STATUS_SHIPPED;
            case Mage_Sales_Model_Order_Item::STATUS_BACKORDERED:
                return self::STATUS_BACKORDERED;
            case Mage_Sales_Model_Order_Item::STATUS_CANCELED:
                return self::STATUS_CANCELLED;
            case Mage_Sales_Model_Order_Item::STATUS_PARTIAL:
                return self::STATUS_PARTIALLY_SHIPPED;
            case Mage_Sales_Model_Order_Item::STATUS_REFUNDED:
            case Mage_Sales_Model_Order_Item::STATUS_RETURNED:
                return self::STATUS_PROCESSING_RETURN;
        }

        $orderState = $item->getOrder()->getState();

        switch ($orderState) {
            case Mage_Sales_Model_Order::STATE_NEW:
                return self::STATUS_NEW;
            case Mage_Sales_Model_Order::STATE_HOLDED:
                return self::STATUS_ON_HOLD;
            case Mage_Sales_Model_Order::STATE_COMPLETE:
            case Mage_Sales_Model_Order::STATE_CLOSED:
                return self::STATUS_COMPLETED;
        }

        if ($orderState == Mage_Sales_Model_Order::STATE_PROCESSING
            && $item->getStatus() == Mage_Sales_Model_Order_Item::STATUS_INVOICED
        ) {
            return $item->getIsVirtual() ? self::STATUS_PAID : self::STATUS_READY_TO_SHIP;
        }

        return self::STATUS_NEW;
    }

    /**
     * @return string
     */
    protected function _createOrderStatus()
    {
        $status = $this->_order->getStatus();
        $state = $this->_order->getState();
        if ($state == Mage_Sales_Model_Order::STATE_NEW && $status == 'pending' ) {
            return Xcom_Chronicle_Model_Message_Order::STATUS_NEW;
        }
        if ($state == Mage_Sales_Model_Order::STATE_HOLDED  && $status == 'holded') {
            return Xcom_Chronicle_Model_Message_Order::STATUS_ON_HOLD;
        }
        if ($state == Mage_Sales_Model_Order::STATE_CANCELED && $status == 'canceled') {
            return Xcom_Chronicle_Model_Message_Order::STATUS_CANCELLED;
        }
        if ($state == Mage_Sales_Model_Order::STATE_PENDING_PAYMENT) {
            return Xcom_Chronicle_Model_Message_Order::STATUS_PENDING_PAYMENT;
        }
        if ($state == Mage_Sales_Model_Order::STATE_COMPLETE) {
            return Xcom_Chronicle_Model_Message_Order::STATUS_COMPLETED;
        }
        if ($state == Mage_Sales_Model_Order::STATE_PROCESSING && $status == 'processing') {
            //if no items shipped == ready to ship
            //if some items shipped == partially shipped
            //if all items shipped == shipped

            if ($this->_order->getIsVirtual()) {
                //If this is a virtual order then it must not be invoiced yet
                return Xcom_Chronicle_Model_Message_Order::STATUS_PENDING_PAYMENT;
            }

            if (count($this->_order->getShipmentsCollection())==0 && $this->_order->getIsNotVirtual()) {
                //No shipments have been made & there are nonVirtual items
                return Xcom_Chronicle_Model_Message_Order::STATUS_READY_TO_SHIP;
            }
            else {
                //Some or potentially all shipments have been made
                foreach ($this->_order->getAllItems() as $item) {
                    if ($item->getQtyToShip()>0 && !$item->getIsVirtual()) {
                        //non virtual items yet to be shipped
                        return Xcom_Chronicle_Model_Message_Order::STATUS_PARTIALLY_SHIPPED;
                    }
                }
            }
            //if we made it here we must have shipped every non virtual item
            return Xcom_Chronicle_Model_Message_Order::STATUS_SHIPPED;
        }
        if ($state == Mage_Sales_Model_Order::STATE_CLOSED ){
            if ($this->_order->getBaseTotalRefunded() == $this->_order->getBaseGrandTotal() ) {
                return Xcom_Chronicle_Model_message_order::STATUS_CANCELLED;
            }
        }

        return Xcom_Chronicle_Model_Message_Order::STATUS_NEW;
    }

    /**
     * Returns SimpleOrderLineData record
     *
     * @param Mage_Sales_Model_Order_Item $item
     * @return array
     */
    protected function _createSimpleOrderLineData(Mage_Sales_Model_Order_Item $item)
    {
        /** @var $product Mage_Catalog_Model_Product */
        $product = Mage::getModel('catalog/product')
            ->setStoreId($item->getStoreId())
            ->load($item->getProductId());
        $data = array(
            'orderLineId'     => null, // Optional
            'itemId'          => (string)$item->getId(), // Optional
            'orderNumber'     => (string)$this->getOrderNumber(), // Optional
            'quantity'        => (float)$item->getQtyOrdered(),
            'productSku'      => (string)$item->getSku(),
            'itemDescription' => (string)(
                $product->getShortDescription() ? $product->getShortDescription() : $product->getDescription()
            ),
            'unitPrice'       => $this->_createOrderLinePrice($item, $item->getQtyOrdered()),
            'totalTaxAmount'  => $this->_createCurrencyAmount($item->getTaxAmount()),
            'status'          => $this->_createOrderItemStatus($item),
            'price'           => $this->_createCurrencyAmount($item->getBaseRowTotal()),
            'allDiscounts'    => null, // optional, array<PromotionalDiscount>
            // @todo implement date fields
            'dateCancelled'   => null, // optional
            'dateDelivered'   => null, // optional
            'dateShipped'     => null, // optional
            'dateReturned'    => null, // optional
            'dateInvoiced'    => null, // optional
            'destination'     => $this->_createShipTo($item->getOrder()->getShippingAddress()), // @todo multishipping
            'shipmentId'      => null, // optional
            'offerId'         => null, // optional
            'offerUrl'        => null, // optional
        );

        return $data;
    }


    /**
     * Creates OCL OrderLineData record
     *
     * @param Mage_Sales_Model_Order_Item $item
     * @return array
     */
    protected function _createOrderLineData(Mage_Sales_Model_Order_Item $item)
    {
        $data = $this->_createSimpleOrderLineData($item);

        $data += array(
            'lineMessage'      => null, // optional
            'bundle'           => null, // optional
            'expandBundle'     => null, // optional
            'soloShip'         => null, // optional
            'autoShip'         => null, // optional
            'autoSubstitution' => null, // optional
        );

        return $data;
    }

    /**
     * Returns array of SimpleOrderLineData records
     *
     * @return array
     */
    protected function _getSimpleOrderLines()
    {
        $orderLines = array();

        /* @var $orderItem Mage_Sales_Model_Order_Item */
        foreach ($this->_order->getItemsCollection() as $orderItem) {
            if ($orderItem->getPrice() == 0) {
                continue;
            }
            $orderLines[] = $this->_createSimpleOrderLineData($orderItem);
        }

        return $orderLines;
    }

    /**
     * Returns array of OCL OrderLineData records
     *
     * @return array
     */
    protected function _getOrderLines()
    {
        $orderLines = array();


        /* @var $orderItem Mage_Sales_Model_Order_Item */
        foreach ($this->_order->getItemsCollection() as $orderItem) {
            if ($orderItem->getPrice() == 0) {
                continue;
            }
            $orderLines[] = $this->_createOrderLineData($orderItem);
        }

        return $orderLines;
    }

    /**
     * Returns related order numbers (e.g. ID of canceled order after order edit)
     *
     * @return array
     */
    protected function _getRelatedOrderNumbers()
    {
        $relatedOrders = array();

        if ($this->_order->getRelationParentRealId()) {
            $relatedOrders[] = $this->_order->getRelationParentRealId();
        }

        return $relatedOrders;
    }

    /**
     * Returns order's GUID
     *
     * @return string
     */
    public function getOrderGuid()
    {
        $guid = $this->_getHelper()->getNamespace() . '-' . $this->_order->getRealOrderId();
        return $guid;
    }

    /**
     * Returns TrackingDetail record
     *
     * @return array
     */
    protected function _createTrackingDetail()
    {
        // @todo
        $data = array(
            'trackingNumbers' => null, // optional, array<string>
            'carrier'         => null, // optional
            'service'         => null, // optional
            'serviceType'     => null, // optional
        );

        return $data;
    }

    /**
     * Returns array of TrackingDetail
     *
     * @return null
     */
    protected function _getTrackingDetails()
    {
        // @todo
        return null;
    }

    /**
     * Returns Shipment record
     *
     * @param string $shipmentId Unique to the order
     * @return array
     */
    protected function _createShipment($shipmentId)
    {
        $shipping = $this->_order->getShippingMethod(true);

        $data = array(
            'shipmentId'            => (string)$shipmentId,
            'shippingFees'          => $this->_createCurrencyAmount(0), // @todo
            'discountAmount'        => null, // optional @todo
            'discounts'             => null, // optional, array<PromotionalDiscount> @todo
            'additionalCost'        => null, // optional
            'packagingHandlingCost' => null, // optional @todo
            'surcharge'             => null, // optional
            'trackingDetails'       => $this->_getTrackingDetails(), // optional @todo
            'shippingMethod'        => $shipping->getMethod(), // next day, ground, etc.
            'deliveryWindow'        => null, // optional
            'shippingWindow'        => null, // optional
            'shippingMessage'       => null, // optional
        );

        return $data;
    }

    /**
     * Returns array of Shipment
     *
     * @return null
     */
    protected function _getShipments()
    {
        // @todo
        return null;
    }

    /**
     * Returns OrderDetails record
     *
     * @return array
     */
    protected function _createOrderDetails()
    {
        $data = array(
            'orderNumber'         => $this->getOrderNumber(), // optional
            'status'              => $this->_createOrderStatus(),
            'dateOrdered'         => date('c', strtotime($this->_order->getCreatedAt())),
            'grandTotal'          => $this->_createCurrencyAmount($this->_order->getBaseGrandTotal()),
            'netMerchandiseValue' => null, // optional
            'itemPriceTotal'      => $this->_createCurrencyAmount($this->_order->getBaseSubtotal()),
            'totalInsuranceCost'  => null, // optional
            'totalDiscountAmount' => $this->_createCurrencyAmount($this->_order->getBaseDiscountAmount()), // optional
            'additionalCosts'     => null, // optional
            'totalTaxAmount'      => $this->_createCurrencyAmount($this->_order->getBaseTaxAmount()), // optional
            'totalShippingFees'   => $this->_createCurrencyAmount($this->_order->getBaseShippingAmount()), // optional
            'discounts'           => null, // optional
            'sourceId'            => $this->_getSourceId(),
            'sourceGroupId'       => '',
            'relatedOrderNumbers' => $this->_getRelatedOrderNumbers(), // optional
            'shipments'           => null, // optional
            'referralSource'      => null, // optional
            'buyerComments'       => null,
        );

        return $data;
    }

    /**
     * Returns NonSensitiveOrderData record
     *
     * @return array
     */
    protected function _createNonSensitiveOrderData()
    {
        $data = $this->_createOrderDetails();

        $data += array(
            'orderLines' => $this->_getOrderLines(),
        );

        return $data;
    }

    /**
     * Returns PaymentMethod record
     *
     * @return array
     */
    protected function _createPaymentMethod()
    {
        $data = array(
            'method' => $this->_getCommonPaymentMethod($this->_order->getPayment()->getMethod()),
        );

        return $data;
    }

    /**
     * Returns one of the values of PaymentStatus enum
     *
     * @return string
     */
    protected function _getPaymentStatus()
    {
        // @todo
        return self::PAYMENT_STATUS_NONE;
    }

    /**
     * Returns PaymentInfo record
     *
     * @return array
     */
    protected function _createPaymentInfo()
    {
        $transaction = $this->_order->getPayment()->getTransaction(false);

        $data = array(
            'method' => $this->_createPaymentMethod(),
            'datePaid' => date('c', strtotime($this->_order->getPayment()->getCreatedAt())),
            'transactionId' => $transaction ? $transaction->getTxnId() : null, // optional
            'transactionStatus' => $transaction ? $transaction->getTxnType() : null, // optional
            'processingFee' => null, // optional
            'paymentStatus' => $this->_getPaymentStatus(),
        );

        return $data;
    }

    /**
     * Returns array of PaymentInfo records
     *
     * @return array
     */
    protected function _getPaymentMethods()
    {
        // @todo use getPaymentsCollection()
        $paymentMethods[] = array($this->_createPaymentInfo());

        return $paymentMethods;
    }

    /**
     * Returns OrderData record
     *
     * @return array
     */
    protected function _createOrderData()
    {
        $data = $this->_createNonSensitiveOrderData();

        $data += array(
            'customer'       => $this->_createCustomerInfo(),
            'billingAddress' => $this->_getHelper()->createAddress($this->_order->getBillingAddress(), array(Xcom_Chronicle_Helper_Data::ADDRESS_TAG_BILLING)),
            'paymentMethods' => $this->_getPaymentMethods(),
            'destination'    => $this->_createShipTo($this->_order->getShippingAddress()),
            'purchaseOrder'  => null, // optional
        );

        return $data;
    }

    /**
     * Returns Order record
     *
     * @return array
     */
    protected function _createOrder()
    {
        $data = $this->_createOrderData();

        $data += array(
            'id' => $this->getOrderGuid(),
        );

        return $data;
    }

    /**
     * Returns NonSensitiveOrder record
     *
     * @return array
     */
    protected function _createNonSensitiveOrder()
    {
        $data = $this->_createNonSensitiveOrderData();

        $data += array(
            'id' => $this->getOrderGuid(),
        );

        return $data;
    }

    /**
     * Returns SimpleOrder record
     *
     * @return array
     */
    protected function _createSimpleOrder()
    {
        $data = $this->_createOrderDetails();
        $data['customerId'] = $this->_createCustomerId();
        $data['orderLines'] = $this->_getSimpleOrderLines();
        return $data;
    }

    protected function _createCustomerId()
    {
        $customerId = $this->_order->getCustomerId();
        if (!isset($customerId)) {
            $customerId = 'guest' . $this->_order->getRealOrderId();
        }
        return $this->_getHelper()->createEntityId($customerId);
    }

    /**
     * Returns Chronicle exception object. Method created for IDE type checks.
     *
     * @param string $message
     * @return Xcom_Chronicle_Exception
     */
    protected function _exception($message)
    {
        return Mage::exception('Xcom_Chronicle', $message);
    }

    /**
     * Returns an order record depending on type
     *
     * @throws Xcom_Chronicle_Exception
     * @return array
     */
    protected function _getOrder()
    {
        switch ($this->_orderType) {
            case self::TYPE_ORDER:
                $data = $this->_createOrder();
                break;
            case self::TYPE_NON_SENSITIVE:
                $data = $this->_createNonSensitiveOrder();
                break;
            case self::TYPE_SIMPLE:
                $data = $this->_createSimpleOrder();
                break;
            default:
                throw $this->_exception($this->__("Unknown order type: %s", $this->_orderType));
        }

        return $data;
    }

    /**
     * Returns ShipTo record
     *
     * @param Mage_Sales_Model_Order_Address $address
     * @return array
     */
    protected function _createShipTo(Mage_Sales_Model_Order_Address $address)
    {
        $data = array(
            'name'      => $this->_getHelper()->createName($address),
            'address'   => $this->_getHelper()->createAddress($address, array(Xcom_Chronicle_Helper_Data::ADDRESS_TAG_SHIPPING)),
            'giftTag'   => null, // optional
        );
        return $data;
    }

    /**
     * Create OCL CurrencyAmount object from base currency amount
     *
     * @param float $amount
     * @return array
     */
    protected function _createCurrencyAmount($amount)
    {
        if (!$this->_baseCurrencyCode) {
            $this->_baseCurrencyCode = Mage::app()->getStore()->getBaseCurrencyCode();
        }

        return array(
            'amount' => (string)$amount,
            'code'   => $this->_baseCurrencyCode,
        );
    }

    /**
     * Get CommonPaymentMethod enum value out of Magento payment method
     *
     * @param string $paymentMethod Payment method, e.g. 'paypaluk_express'
     * @return string
     */
    protected function _getCommonPaymentMethod($paymentMethod)
    {
        $type = strtok($paymentMethod, '_');

        switch (strtolower($type)) {
            case 'ccsave':
            case 'authorizenet':
                $oclType = self::PAYMENT_METHOD_CREDIT_CARD;
                break;
            case 'checkmo':
                $oclType = self::PAYMENT_METHOD_CHECK;
                break;
            case 'paypal':
            case 'paypaluk':
            case 'payflow':
            case 'hosted':
                $oclType = self::PAYMENT_METHOD_PAYPAL;
                break;
            case 'moneybookers':
                $oclType = self::PAYMENT_METHOD_MONEYBOOKERS;
                break;
            default:
                $oclType = strtoupper($type);
        }

        return $oclType;
    }

    /**
     * Returns Email record
     *
     * @param string $email Email address
     * @return array
     */
    protected function _createEmail($email)
    {
        $data = array(
            'emailAddress' => (string)$email,
        );

        return $data;
    }

    /**
     * @param string $number
     * @param string $type
     * @throws Xcom_Chronicle_Exception
     * @return array
     */
    protected function _createPhone($number, $type)
    {
        $allowedPhoneTypes = array(
            self::PHONE_UNKNOWN,
            self::PHONE_FAX,
            self::PHONE_HOME,
            self::PHONE_MOBILE,
            self::PHONE_WORK
        );

        if (!in_array($type, $allowedPhoneTypes)) {
            throw $this->_exception($this->__('Invalid phone type: %s', $type));
        }

        $data = array(
            'number' => (string)$number,
            'type'   => $type,
        );

        return $data;
    }

    /**
     * Returns CustomerInfo record
     *
     * @return array
     */
    protected function _createCustomerInfo()
    {
        $phone = null;

        if ($this->_order->getBillingAddress()) {
            $phone = $this->_order->getBillingAddress()->getTelephone();
        }

        $email = $this->_order->getCustomerEmail();
        $data = array(
            'id'    => $this->_createCustomerId(),
            'name'  => $this->_helper->createName($this->_order->getBillingAddress()),
            'email' =>  $email ? $this->_createEmail($email) : null, // optional
            'phone' =>  $phone ? $this->_createPhone($phone, self::PHONE_UNKNOWN) : null, // optional
        );

        return $data;
    }

    /**
     * Returns OCL OrderReturn record
     *
     * @return array
     */
    protected function _createOrderReturn(Mage_Sales_Model_Order_Creditmemo $creditMemo)
    {
        $data = array(
            'orderNumber'  => $this->_order->getRealOrderId(),
            'dateReturned' => date('c'), // optional
            'customerId'   => $this->_createCustomerId(),
            'dateOrdered'  => date('c', strtotime($this->_order->getCreatedAt())),
            'orderLines'   => $this->_getReturnedItems($creditMemo),
            'totalAmountOfReturn'    => $this->_createCurrencyAmount($creditMemo->getGrandTotal()),
            'totalTaxAmountOfReturn' => $this->_createCurrencyAmount($creditMemo->getTaxAmount())
        );

        return $data;
    }

    /**
     * Returns OCL ReturnOrderLineData record
     *
     * @param Mage_Sales_Model_Order_Creditmemo_Item $item
     * @return array
     */
    protected function _createReturnOrderLineData(Mage_Sales_Model_Order_Creditmemo_Item $item)
    {
        /** @var $orderItem Mage_Sales_Model_Order_Item */
        $orderItem = $item->getOrderItem();
        /** @var $product Mage_Catalog_Model_Product */
        $product = Mage::getModel('catalog/product')
            ->setStoreId($orderItem->getStoreId())
            ->load($orderItem->getProductId());
        $data = array(
            'orderLineId'     => null, // optional
            'itemId'          => (string)$item->getOrderItemId(), // optional
            'quantity'        => (float)$item->getQty(),
            'productSku'      => $item->getSku(),
            'itemDescription' => (string)(
                $product->getShortDescription() ? $product->getShortDescription() : $product->getDescription()
            ),
            'totalTaxAmount'  => $this->_createCurrencyAmount($item->getTaxAmount()),
            'dateReturned'    => date('c'),
            'price'           => $this->_createOrderLinePrice($item, (float)$item->getQty(), self::LINE_ITEM_TYPE_REFUNDED),
            'shipmentId'      => null, // optional
        );

        return $data;
    }

    /**
     * Returns array of ReturnOrderLineData
     *
     * @param Mage_Sales_Model_Order_Creditmemo $creditMemo
     * @return array
     */
    protected function _getReturnedItems(Mage_Sales_Model_Order_Creditmemo $creditMemo)
    {
        $returnedItems = array();

        /** @var $item Mage_Sales_Model_Order_Creditmemo_Item */
        foreach ($creditMemo->getItemsCollection() as $item) {
            if ($item->getQty() == 0 || $item->getPrice() == 0) {
                continue;
            }
            $returnedItems[] = $this->_createReturnOrderLineData($item);
        }

        return $returnedItems;
    }

    /**
     * Returns OCL PartialOrderReturn record
     *
     * @param Mage_Sales_Model_Order_Creditmemo $creditMemo
     * @return array
     */
    protected function _createPartialOrderReturn(Mage_Sales_Model_Order_Creditmemo $creditMemo)
    {
        $data = array(
            'orderNumber'            => $this->_order->getRealOrderId(),
            'returnedItems'          => $this->_getReturnedItems($creditMemo),
            'customerId'             => $this->_createCustomerId(),
            'dateOrdered'            => date('c', strtotime($this->_order->getCreatedAt())),
            'totalAmountOfReturn'    => $this->_createCurrencyAmount($creditMemo->getGrandTotal()),
            'totalTaxAmountOfReturn' => $this->_createCurrencyAmount($creditMemo->getTaxAmount())
        );

        return $data;
    }

    /**
     * Returns OCL OrderLineData for Shipment record. This should only be used for shipment.
     *
     * @param Mage_Sales_Model_Order_Creditmemo $creditMemo
     * @return array
     */
    protected function _createShipmentOrderLineData(Mage_Sales_Model_Order_Shipment $shipment)
    {
        $orderLineData = array();

        $orderItemToShipmentItem = array();
        foreach ($shipment->getItemsCollection() as $item) {
            $orderItemToShipmentItem[$item->getOrderItemId()] = $item;
        }

        /** @var $orderItem Mage_Sales_Model_Order_Item */
        foreach ($this->_order->getItemsCollection() as $orderItem) {
            if (isset($orderItemToShipmentItem[$orderItem->getItemId()])) {
                $shipItem = $orderItemToShipmentItem[$orderItem->getItemId()];

                $qtyShipped = $shipItem->getQty();

                if ($qtyShipped > 0) {
                    $lineData = $this->_createOrderLineData($orderItem);

                    $lineData['quantity'] = $qtyShipped;

                    // should not include tax according to contract
                    $lineData['price'] = $this->_createCurrencyAmount($qtyShipped * $shipItem->getBasePrice());

                    // set total tax amount to 'null' because cannot guarantee the calculation of tax on a per unit basis
                    // (e.g. no Magento functionality to calculate it)
                    if($orderItem->getQtyOrdered() != $qtyShipped) {
                        $lineData['totalTaxAmount'] = null;
                    }

                    $orderLineData[] = $lineData;
                }
            }
        }

        return $orderLineData;
    }

    /**
     * Returns specific OCL record as array. What the method does is calls the method responsible for generating
     * specified record.
     *
     * @param string $record    OCL record name (e.g. OrderReturn, PartialOrderReturn)
     * @param array  $arguments Arguments for the method called
     * @return array
     * @throws Xcom_Chronicle_Exception
     */
    public function getRecord($record, $arguments = array())
    {
        $methodName = '_create' . $record;

        if (!method_exists($this, $methodName)) {
            throw $this->_exception($this->__('No such method: %s', $methodName));
        }

        return call_user_func_array(array($this, $methodName), $arguments);
    }
}
