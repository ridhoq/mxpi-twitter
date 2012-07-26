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
class Xcom_Chronicle_Model_Observer
{

    const MASS_OFFER_UPDATE_JOB_CODE = 'mass_offer_update';

    /**
     * Triggered when new order is created
     *
     * @param Varien_Event_Observer $observer
     * @return Xcom_Chronicle_Model_Observer
     */
    public function orderAfterCreate(Varien_Event_Observer $observer)
    {
        try {
            if ($order = $observer->getEvent()->getOrder()) {
                //single shipping order
                Mage::helper('xcom_xfabric')->send('order/created', array('order' => $order));
            } else if ($observer->getEvent()->getOrders()) {
                //multi shipping case
                foreach ($observer->getEvent()->getOrders() as $order) {
                    Mage::helper('xcom_xfabric')->send('order/created', array('order' => $order));
                }
            }
        } catch (Exception $exception) {
            $this->_handleException($exception);
        }
        return $this;
    }

    /**
     * Triggered after order cancellation
     *
     * @param Varien_Event_Observer $observer
     * @return Xcom_Chronicle_Model_Observer
     */
    public function orderAfterCancel(Varien_Event_Observer $observer)
    {
        try {
            $order = $observer->getEvent()->getOrder();
            if ($this->_isMarketplaceOrder($order->getId())) {
                Mage::helper('xcom_xfabric')->send('com.x.ordermanagement.v2/ProcessSalesChannelOrder.MarketplaceOrder/MarketplaceOrderCancelled', array('order' => $order));
            } else {
                Mage::helper('xcom_xfabric')->send('com.x.ordermanagement.v2/ProcessSalesChannelOrder/OrderCancelled', array('order' => $order));
            }
            $this->_registerValue('xcom_order_cancelled', $order->getId());
        } catch (Exception $exception) {
            $this->_handleException($exception);
        }
        return $this;
    }

    /**
     * Triggered after order is shipped
     *
     * @param Varien_Event_Observer $observer
     * @return Xcom_Chronicle_Model_Observer
     */
    public function orderAfterShip(Varien_Event_Observer $observer)
    {
        try {
            $shipment = $observer->getEvent()->getShipment();

            if ($this->_isShipmentNew($shipment)) {
                //only publish shipped on new record.  Do not publish shipped again if they added
                //tracking information separately - this will be reflected in order update
                $order = Mage::getModel('xcom_chronicle/message_order', array('order' => $shipment->getOrder()));
                if ($order->isChannelOrder()) {
                    Mage::helper('xcom_xfabric')->send('com.x.ordermanagement.v2/ProcessSalesChannelOrder.MarketplaceOrder/MarketplaceOrderShipped', array('shipment' => $shipment));
                }
                else {
                    Mage::helper('xcom_xfabric')->send('com.x.ordermanagement.v2/ProcessSalesChannelOrder/OrderShipped', array('shipment' => $shipment));
                }
            }
        } catch (Exception $exception) {
            $this->_handleException($exception);
        }
        return $this;
    }
    /**

     * Triggered before order is saved
     *
     * @param Varien_Event_Observer $observer
     * @return Xcom_Chronicle_Model_Observer
     */
    public function orderBeforeSave(Varien_Event_Observer $observer)
    {
        if (!$observer->getEvent()->getOrder()->getId() && !Mage::registry('xcom_order_new')) {
            Mage::register('xcom_order_new', true);
        }
        return $this;
    }

    /**
     * Triggered after order is saved
     *
     * @param Varien_Event_Observer $observer
     * @return Xcom_Chronicle_Model_Observer
     */
    public function orderAfterSave(Varien_Event_Observer $observer)
    {
        try {
            $order = $observer->getEvent()->getOrder();

            $isUpdated = $this->_isValueRegistered('xcom_order_updated', $order->getId());

            if (!Mage::registry('xcom_order_new') && !$isUpdated) {
                $this->_registerValue('xcom_order_updated', $order->getId());
            }
        } catch (Exception $exception) {
            $this->_handleException($exception);
        }
        return $this;
    }

    /**
     * Triggered after shippment is saved
     *
     * @param Varien_Event_Observer $observer
     * @return Xcom_Chronicle_Model_Observer
     */
    public function orderUpdateAfterShip(Varien_Event_Observer $observer)
    {
        try {
            $shipment = $observer->getEvent()->getShipment();
            $order = $shipment->getOrder();

            $isUpdated = $this->_isValueRegistered('xcom_order_updated', $order->getId());

            if (!$isUpdated) {
                $this->_registerValue('xcom_order_updated', $order->getId());
            }
        } catch (Exception $exception) {
            $this->_handleException($exception);
        }

        return $this;
    }

    /**
     * Triggered after order address is saved
     *
     * @param Varien_Event_Observer $observer
     * @return Xcom_Chronicle_Model_Observer
     */
    public function orderAddressAfterSave(Varien_Event_Observer $observer)
    {
        try {
            $order = $observer->getEvent()->getAddress()->getOrder();

            $isCancelled = $this->_isValueRegistered('xcom_order_cancelled', $order->getId());
            $isUpdated = $this->_isValueRegistered('xcom_order_updated', $order->getId());

            if (!Mage::registry('xcom_order_new') && !$isCancelled && !$isUpdated) {
                if (!$order->getCustomerId()) {
                    Mage::helper('xcom_xfabric')->send('customer/updated/guest', array('order' => $order));
                }

                $this->_registerValue('xcom_order_updated', $order->getId());
            }

        } catch (Exception $exception) {
            $this->_handleException($exception);
        }

        return $this;
    }

    protected function _isShipmentNew($shipment)
    {
        $origIdValue = $shipment->getOrigData('id');
        if (isset($origIdValue)) {
            return false;
        }
        return true;
    }

    /**
     * Triggered on event checkout_submit_all_after
     *
     * @param Varien_Event_Observer $observer
     */
    public function customerForGuestOrders(Varien_Event_Observer $observer)
    {
        try {
            if ($order = $observer->getEvent()->getOrder()) {
                //single shipping order
                if (!$order->getCustomerId()) {
                    Mage::helper('xcom_xfabric')->send('customer/created/guest', array('order' => $order));
                }
            } else if ($observer->getEvent()->getOrders()) {
                // for multi-shipping orders, a customer is required to register so it should go through the normal
                // customer event flows
            }
        } catch (Exception $exception) {
            $this->_handleException($exception);
        }
        return $this;
    }

    /**
     * Triggered on event customer_save_before
     *
     * @param Varien_Event_Observer $observer
     */
    public function customerBeforeSave(Varien_Event_Observer $observer)
    {
        try {
            $customer = $observer->getEvent()->getCustomer();
            if ($customer->isObjectNew()) {
                Mage::register('xcom_customer_new', true, true);
            }
        } catch (Exception $exception) {
            $this->_handleException($exception);
        }
        return $this;
    }

    /**
     * Triggered on event customer_save_after
     *
     * @param Varien_Event_Observer $observer
     */
    public function customerAfterSave(Varien_Event_Observer $observer)
    {
        try {
            $customer = $observer->getEvent()->getCustomer();

            if (Mage::registry('xcom_customer_new') && !Mage::registry('xcom_customer_new_sent')) {
                Mage::helper('xcom_xfabric')->send('customer/created', array('customer' => $customer));
                Mage::register('xcom_customer_new_sent', true);
            }
            else {
                if (!Mage::registry('xcom_order_new') && !Mage::registry('xcom_customer_new_sent')) {
                    Mage::helper('xcom_xfabric')->send('customer/updated', array('customer' => $customer));
                }
            }
        } catch (Exception $exception) {
            $this->_handleException($exception);
        }
        return $this;
    }

    /**
     * Triggered on event catalog_product_save_before
     *
     * @param Varien_Event_Observer $observer
     */
    public function productBeforeSave(Varien_Event_Observer $observer)
    {
        try {
            $product = $observer->getEvent()->getProduct();
            $isSupported = $this->_isSupportedProductType($product);

            if ($isSupported) {
                if (!$product->isObjectNew()) {
                    /* store products that have already existed */
                    $this->_registerValue('xcom_product_old', $product->getId());
                    $this->_registerKeyValue('xcom_product_change', $product->getId(), $this->_getProductMessage($product));
                }

                /*
                    needs to register if the sku has changed in case future observer
                    event methods like inventoryAfterSave need it
                */
                $originalSku = $product->getOrigData('sku');
                $newSku = $product->getSku();
                if ($originalSku != $newSku) {
                    $this->_registerValue('xcom_product_changed_sku', $product->getId());
                }

                /* Need to store the old websites(stores) for this product */
                $c = $product->getIsChangedWebsites()?'true':'false';

                $dbProduct = Mage::getModel('catalog/product')->load($product->getId());
                $this->_registerKeyValue('xcom_offer_old_stores', $product->getId(), $dbProduct->getStoreIds());
            }
        } catch (Exception $exception) {
            $this->_handleException($exception);
        }
        return $this;
    }

    /**
     * Triggered on event catalog_product_save_after
     *
     * @param Varien_Event_Observer $observer
     */
    public function productAfterSave(Varien_Event_Observer $observer)
    {
        try {
            /** @var $product Mage_Catalog_Model_Product */
            $product = $observer->getEvent()->getProduct();

            if ($this->_isSupportedProductType($product)) {
                if (!$this->_isValueRegistered('xcom_product_old', $product->getId())) {
                    Mage::helper('xcom_xfabric')->send('com.x.pim.v1/ProductCreation/ProductCreated', array('product' => $product));
                }
                else {
                    $prevProductAsArray = $this->_getRegisterValueForKey('xcom_product_change', $product->getId());
                    if (!empty($prevProductAsArray)) {
                        $curProductAsArray =  Mage::getModel('xcom_chronicle/message_product', $product)->toArray();
                        if ($this->_arrayRecursiveDiff($curProductAsArray, $prevProductAsArray) || $this->_arrayRecursiveDiff($prevProductAsArray, $curProductAsArray)) {
                            Mage::helper('xcom_xfabric')->send('com.x.pim.v1/ProductUpdate/ProductUpdated', array('product' => $product));
                        }
                    }
                }

                // Decide if new offer or update or cancelled
                $this->_sendOfferMessages($product);
            }
        } catch (Exception $exception) {
            $this->_handleException($exception);
        }
        return $this;
    }


    /**
     * Triggered on event catalog_product_delete_before
     *
     * @param Varien_Event_Observer $observer
     */
    public function productBeforeDelete(Varien_Event_Observer $observer)
    {
        try {
            $product = $observer->getEvent()->getProduct();

            $dbProduct = Mage::getModel('catalog/product')->load($product->getId());
            $this->_registerKeyValue('xcom_offer_old_stores', $product->getId(), $dbProduct->getStoreIds());

            // Need to store the product for a store so we can send an Offer message
            foreach ($dbProduct->getStoreIds() as $storeId) {
                $storeProduct = $this->_getStoreProduct($storeId, $product->getId());

                $this->_registerKeyValue('xcom_offer_old_stores_products', $product->getId() . '_' . $storeId, $storeProduct);
            }
        } catch (Exception $exception) {
            $this->_handleException($exception);
        }
        return $this;
    }

    private function _getStoreProduct($storeId, $productId)
    {
        return Mage::getModel('catalog/product')
            ->setStoreId($storeId)
            ->load($productId);
    }

    /**
     * Triggered on event catalog_product_delete_after
     *
     * @param Varien_Event_Observer $observer
     */
    public function productAfterDelete(Varien_Event_Observer $observer)
    {
        try {
            $product = $observer->getEvent()->getProduct();
            if ($this->_isSupportedProductType($product)) {
                Mage::helper('xcom_xfabric')->send('com.x.pim.v1/ProductDeletion/ProductDeleted', array('product' => $product));

                $this->_sendOfferMessages($product, true);
            }
        } catch (Exception $exception) {
            $this->_handleException($exception);
        }
        return $this;
    }

    protected function _isProductNew($product) {
        $origIdValue = $product->getOrigData('id');
        if (isset($origIdValue)) {
            return true;
        }
        return false;
    }

    /**
     * Triggered on event cataloginventory_stock_item_save_after
     *
     * @param Varien_Event_Observer $observer
     */
    public function inventoryAfterSave(Varien_Event_Observer $observer)
    {
        try {
            $stockItem = $observer->getEvent()->getItem();

            $productId = $stockItem->getProductId();
            $product = Mage::getModel('catalog/product')->load((int)$productId);

            if (isset($product) && $this->_isSimpleProduct($product)) {
                $sku = $product->getSku();
                if (!$this->_isValueRegistered('xcom_product_old', $productId)) {
                    Mage::helper('xcom_xfabric')->send('com.x.inventory.v1/StockItemUpdate/StockItemUpdated',
                        array('stock_item' => $stockItem,  'product_sku' => $sku)
                    );
                    $this->_registerValue('xcom_inventory_updated', $productId);
                }
                else {
                    /* only send data if it is changed */
                    $originalQty = $stockItem->getOrigData('qty');
                    $newQty = $stockItem->getQty();

                    /* set in the productAfterSave */
                    $isSkuChanged = $this->_isValueRegistered('xcom_product_changed_sku', $stockItem->getProductId());

                    if (($newQty != $originalQty) || ($isSkuChanged)) {
                        Mage::helper('xcom_xfabric')->send('com.x.inventory.v1/StockItemUpdate/StockItemUpdated',
                            array('stock_item' => $stockItem,  'product_sku' => $sku)
                        );
                        $this->_registerValue('xcom_inventory_updated', $productId);
                    }
                }

                $inStockOrig = $stockItem->getOrigData('is_in_stock');
                $inStockNew = $stockItem->getIsInStock();
                if ((!$stockItem->verifyStock())
                    || ($inStockOrig != $inStockNew && !$inStockNew)) {
                    /* if it is Out of Stock and Stock Availability was a value that was changed */
                    Mage::helper('xcom_xfabric')->send('com.x.inventory.v1/StockItemUpdate/OutOfStock',
                        array('stock_item' => $stockItem, 'product_sku' => $sku)
                    );
                }
            }
        } catch (Exception $exception) {
            $this->_handleException($exception);
        }
        return $this;
    }

    /**
     * Decide if we support sending events out for this product type
     * @param $product Mage_Catalog_Model_Product
     * @return bool
     */
    protected function _isSupportedProductType($product)
    {
        return $this->_isSimpleProduct($product) || $product->isConfigurable();
    }

    protected function _isSimpleProduct(Mage_Catalog_Model_Product $product)
    {
        return $product->getTypeId() == 'simple';
    }

    protected function _getProductMessage(Mage_Catalog_Model_Product $product)
    {
        $products = Mage::getResourceModel('catalog/product_collection')
            ->addFieldToFilter('entity_id',$product->getEntityId());
        $products->load();
        $origProduct = Mage::getModel('catalog/product')->load((int)$product->getEntityId());
        return Mage::getModel('xcom_chronicle/message_product', $origProduct)->toArray();
    }

    /**
     * Determines if there is a difference from the srcArray to the destArray.  If there is a difference
     * then these differences will be returned in an array.  If there is no difference an empty array will
     * be returned
     *
     * @param $srcArray
     * @param $destArray
     * @return array
     */
    protected function _arrayRecursiveDiff($srcArray, $destArray) {
        $aReturn = array();

        foreach ($srcArray as $mKey => $mValue) {
            if (!empty($destArray) && array_key_exists($mKey, $destArray)) {
                if (is_array($mValue)) {
                    $aRecursiveDiff = $this->_arrayRecursiveDiff($mValue, $destArray[$mKey]);
                    if (count($aRecursiveDiff)) {
                        $aReturn[$mKey] = $aRecursiveDiff;
                    }
                }
                else {
                    if ($mValue != $destArray[$mKey]) {
                        $aReturn[$mKey] = $mValue;
                    }
                }
            }
            else {
                $aReturn[$mKey] = $mValue;
            }
        }
        return $aReturn;
    }

    /**
     * Triggered on event checkout_submit_all_after
     *
     * @param Varien_Event_Observer $observer
     */
    public function publishInventoryForQuoteEvent(Varien_Event_Observer $observer)
    {
        try {
            /* after a checkout */
            $quote = $observer->getEvent()->getQuote();
            $items = $quote->getAllItems();

            foreach ($items as $item) {
                $product = Mage::getModel('catalog/product')->load((int)$item->getProductId());
                if (isset($product) && $this->_isSimpleProduct($product)) {
                    $sku = $product->getSku();
                    if (!$this->_isValueRegistered('xcom_inventory_updated', $item->getProductId())) {
                        Mage::helper('xcom_xfabric')->send('com.x.inventory.v1/StockItemUpdate/StockItemUpdated',
                            array('stock_item' => $product->getStockItem(),  'product_sku' => $sku)
                        );
                        $this->_registerValue('xcom_inventory_updated', $item->getProductId());
                    }
                    $stockItem = $item->getProduct()->getStockItem();

                    $inStockOrig = $stockItem->getOrigData('is_in_stock');
                    $inStockNew = $stockItem->getIsInStock();
                    if ((!$stockItem->verifyStock())
                        || ($inStockOrig != $inStockNew && !$inStockNew)) {
                        /* if it is Out of Stock and Stock Availability was a value that was changed */
                        Mage::helper('xcom_xfabric')->send('com.x.inventory.v1/StockItemUpdate/OutOfStock',
                            array('stock_item' => $stockItem, 'product_sku' => $sku)
                        );
                    }

                    $this->_sendOfferMessages($product);
                }
            }
        } catch (Exception $exception) {
            $this->_handleException($exception);
        }
        return $this;
    }

    /**
     * Triggered on event sales_order_item_cancel
     *
     * @param Varien_Event_Observer $observer
     */
    public function inventoryAfterOrderItemCancel(Varien_Event_Observer $observer)
    {
        try {
            $item = $observer->getEvent()->getItem();
            $product = Mage::getModel('catalog/product')->load((int)$item->getProductId());

            if (isset($product) && $this->_isSimpleProduct($product)) {
                if (!$this->_isValueRegistered('xcom_inventory_updated', $item->getProductId())) {
                    Mage::helper('xcom_xfabric')->send('com.x.inventory.v1/StockItemUpdate/StockItemUpdated',
                        array('stock_item' => $product->getStockItem(),  'product_sku' => $product->getSku())
                    );
                    $this->_registerValue('xcom_inventory_updated', $item->getProductId());
                }

                $this->_sendOfferMessages($product);
            }
        } catch (Exception $exception) {
            $this->_handleException($exception);
        }
        return $this;
    }

    public function afterReindexCatalogUrl(Varien_Event_Observer $observer)
    {
        try {
            $this->_sendUpdatedOfferMessageForURL(true);
        } catch (Exception $exception) {
            $this->_handleException($exception);
        }
    }

    public function afterCatalogProductSaveEvent(Varien_Event_Observer $observer)
    {
        try {
            $this->_sendUpdatedOfferMessageForURL(false);
        } catch (Exception $exception) {
            $this->_handleException($exception);
        }
    }

    protected function _sendUpdatedOfferMessageForURL($shouldAlwaysDelete = false)
    {
        $urlsToUpdate = Mage::getResourceModel('xcom_chronicle/product_url_update_collection')->load();
        foreach ($urlsToUpdate as $urlToUpdate) {
            $isDeleted = false;
            $sid = $urlToUpdate->getStoreId();
            $product = Mage::getModel('catalog/product')
                ->setStoreId($sid)
                ->load($urlToUpdate->getProductId());
            if ($product->getUrlPath() != $urlToUpdate->getUrlPath()) {
                $offerInputData = array('product'  => $product, 'store_id' => $sid);
                Mage::helper('xcom_xfabric')->send(
                    'com.x.webstore.v1/WebStoreOfferUpdate/WebStoreOfferUpdated', $offerInputData);

                $isDeleted = true;
                $urlToUpdate->delete();
            }

            if (!$isDeleted && $shouldAlwaysDelete) {
                $urlToUpdate->delete();
            }
        }
    }

    protected function _sendOfferMessages(Mage_Catalog_Model_Product $product, $performThoroughCheck = false)
    {
        if ($this->_isValueRegistered('xcom_offer_messages_sent', $product->getId())) {
            return;
        }
        $this->_registerValue('xcom_offer_messages_sent', $product->getId());

        $cancelledSids = array();
        $createdSids = array();

        if ($performThoroughCheck || $product->getIsChangedWebsites()) {
            $oldWids = $this->_getRegisterValueForKey('xcom_offer_old_stores', $product->getId());
            if (!isset($oldWids)) {
                $oldWids = array();
            }
            $cancelledSids = array_diff($oldWids, $product->getStoreIds());
            $createdSids = array_diff($product->getStoreIds(), $oldWids);
        }

        $updatedSids = array_diff($product->getStoreIds(), $cancelledSids, $createdSids);
        // There is a case where product was duplicated and just now a sku was filled in
        if ($product->getOrigData()) {
            $oldSku = $product->getOrigData('sku');
            $sku = $product->getSku();
            if (empty($oldSku) && !empty($sku)) {
                $createdSids = array_merge($createdSids, $updatedSids);
                $updatedSids = array();
            }
        }

        if ($product->dataHasChangedFor('price')) {
            foreach($updatedSids as $sid) {
                $offerInputData = array('product'  => $product,
                    'store_id' => $sid);

                Mage::helper('xcom_xfabric')->send('com.x.webstore.v1/WebStoreOfferUpdate/WebStoreOfferPriceUpdated', $offerInputData);
            }
        }

        if ($this->_isValueRegistered('xcom_inventory_updated', $product->getId())) {
            foreach($updatedSids as $sid) {
                $offerInputData = array('product'  => $product,
                    'store_id' => $sid);

                Mage::helper('xcom_xfabric')->send('com.x.webstore.v1/WebStoreOfferUpdate/WebStoreOfferQuantityUpdated', $offerInputData);
            }
        }

        if ( $product->dataHasChangedFor('visibility')
            || $product->dataHasChangedFor('status') ) {
            foreach ($updatedSids as $sid) {
                $offerInputData = array('product'  => $product, 'store_id' => $sid);
                Mage::helper('xcom_xfabric')->send('com.x.webstore.v1/WebStoreOfferUpdate/WebStoreOfferUpdated', $offerInputData);
            }
        }

        if ($product->dataHasChangedFor('url_key')) {
            foreach($updatedSids as $sid) {
                Mage::getModel('xcom_chronicle/product_url_update')
                    ->setProductId($product->getEntityId())
                    ->setStoreId($sid)
                    ->setUrlPath($product->getUrlPath())
                    ->save();
            }
        }

        foreach ($createdSids as $sid) {
            $offerInputData = array('product'  => $product,
                'store_id' => $sid);

            Mage::helper('xcom_xfabric')->send('com.x.webstore.v1/WebStoreOfferCreation/WebStoreOfferCreated', $offerInputData);
        }

        foreach ($cancelledSids as $sid) {
            $storeProduct = $this->_getRegisterValueForKey('xcom_offer_old_stores_products', $product->getId() . '_' . $sid);
            if (empty($storeProduct)) {
                $offerInputData = array('product'  => $product,
                    'store_id' => $sid);
            } else {
                $offerInputData = array('product'  => $storeProduct);
            }

            Mage::helper('xcom_xfabric')->send('com.x.webstore.v1/WebStoreOfferDeletion/WebStoreOfferDeleted', $offerInputData);
        }
    }

    /**
     * Triggered after credit memo is saved
     *
     * @param Varien_Event_Observer $observer Contains Order and CreditMemo objects
     */
    public function creditmemoSaveAfter(Varien_Event_Observer $observer)
    {
        try {
            /* @var $creditMemo Mage_Sales_Model_Order_Creditmemo */
            $creditMemo = $observer->getCreditmemo();
            /* @var $order Mage_Sales_Model_Order */
            $order = Mage::getModel('sales/order');
            $order->load($creditMemo->getOrderId());

            $inputData = array(
                'credit_memo' => $creditMemo,
                'order'       => $order,
            );

            foreach ($creditMemo->getItemsCollection() as $item) {
                /* @var $item Mage_Sales_Model_Order_Creditmemo_Item */
                if ($item->getOrderItem()->getQtyToRefund()) {
                    $isPartial = true;
                }
                //If the item is sent back to stock send an update to stock message
                //We have a dependency on the Mage_CatalogInventory module to
                //ensure that the observer responsible for returning the qty to stock
                //has already been called.
                if ($item->hasBackToStock()) {
                    if ($item->getBackToStock() && $item->getQty()) {
                        $productId = $item->getProductId();
                        $sku = $item->getSku();
                        $stockItem = Mage::getModel('cataloginventory/stock_item')->load((int)$productId);
                        Mage::helper('xcom_xfabric')->send('com.x.inventory.v1/StockItemUpdate/StockItemUpdated',
                            array('stock_item' => $stockItem,  'product_sku' => $sku)
                        );
                    }
                }
            }

            if (!empty($isPartial) && $isPartial) {
                Mage::helper('xcom_xfabric')->send(
                    'com.x.ordermanagement.v2/ProcessSalesChannelOrder/PartialOrderReturn', $inputData
                );
            }  else {
                Mage::helper('xcom_xfabric')->send(
                    'com.x.ordermanagement.v2/ProcessSalesChannelOrder/OrderReturn', $inputData
                );
            }
        } catch(Exception $exception) {
            $this->_handleException($exception);
        }
        return $this;
    }

    protected $_listOfConfigPathsThatAffectOfferUrl = array(
        'web/url/use_store',
        'web/seo/use_rewrites',
        'web/unsecure/base_url',
        'web/unsecure/base_link_url',
        'web/secure/base_url',
        'web/secure/base_link_url',
    );

    /**
     * Hangs on core_config_data_after_commit_save and runs a mass offer update job (with 5 minute delay) if there's
     * a change to any tenant's URL. Runs via a cron job because the process may take very long time, so need to be
     * ran in a different system process.
     *
     * @param Varien_Event_Observer $observer
     * @return Xcom_Chronicle_Model_Observer
     */
    public function coreConfigAfterCommit(Varien_Event_Observer $observer)
    {
        try {
            /* @var $configData Mage_Core_Model_Config_Data */
            $configData = $observer->getEvent()->getConfigData();
            $configPath = $configData->getPath();

            if(in_array($configPath, $this->_listOfConfigPathsThatAffectOfferUrl)) {
                if ($configData->isValueChanged()) {
                    $this->massOfferUpdateJobSetup();
                }
            }

        } catch (Exception $exception) {
            $this->_handleException($exception);
        }
        return $this;
    }

    /**
     * Does the mass offer update in case any of tenant's URL has changed
     *
     * @param Mage_Cron_Model_Schedule $schedule
     * @return Xcom_Chronicle_Model_Observer
     */
    public function massOfferUpdateCronJob(Mage_Cron_Model_Schedule $schedule)
    {
        try {
            $this->updateAllOffers();
        } catch (Exception $exception) {
            $this->_handleException($exception);
        }

        return $this;
    }

    /**
     * Sets up a cron job to run mass offer update within 5 minutes
     *
     * @return Xcom_Chronicle_Model_Observer
     */
    public function massOfferUpdateJobSetup()
    {
        try {
            $alreadySetupStatuses = array(
                Mage_Cron_Model_Schedule::STATUS_PENDING,
                Mage_Cron_Model_Schedule::STATUS_RUNNING
            );

            /* @var $schedule Mage_Cron_Model_Schedule */
            $schedule = Mage::getModel('cron/schedule');
            /* @var $schedules Mage_Cron_Model_Resource_Schedule_Collection */
            $schedules = $schedule->getCollection()
                ->addFieldToFilter('job_code', self::MASS_OFFER_UPDATE_JOB_CODE)
                ->addFieldToFilter('status', array('in' => $alreadySetupStatuses))
                ->load();

            if (!$schedules->count()) {
                // Not added yet - setup the mass offer update cron job 5 minutes later
                $schedule
                    ->setJobCode(self::MASS_OFFER_UPDATE_JOB_CODE)
                    ->setCreatedAt(strftime('%Y-%m-%d %H:%M:%S', time()))
                    ->setScheduledAt(strftime('%Y-%m-%d %H:%M:%S', time() + 5 * 60))
                    ->save();
            } else {
                Mage::log(
                    Mage::helper('xcom_chronicle')->__('Mass offer update job already added, skipping'),
                    Zend_Log::WARN
                );
            }
        } catch (Exception $exception) {
            $this->_handleException($exception);
        }

        return $this;
    }


    /**
     * Send an WebStoreOfferUpdated event for all offers in the system.
     */
    public function updateAllOffers()
    {
        try {
            $products = Mage::getResourceModel('catalog/product_collection');

            /** @var $product Mage_Catalog_Model_Product **/
            foreach ($products as $product) {
                if ($this->_isSupportedProductType($product)) {
                    foreach ($product->getStoreIds() as $storeId) {
                        $offerInputData = array('product'  => $product, 'store_id' => $storeId);
                        Mage::helper('xcom_xfabric')->send(
                            'com.x.webstore.v1/WebStoreOfferUpdate/WebStoreOfferUpdated',
                            $offerInputData
                        );
                    }
                }
            }
        } catch (Exception $exception) {
            $this->_handleException($exception);
        }

        return $this;
    }

    /**
     * Method observes core_abstract_save_after event
     * as far as Enterprise_Rma_Model_Rma doesn't have own event prefix
     *
     * @param Varien_Event_Observer $observer
     */
    public function rmaSaveAfter(Varien_Event_Observer $observer)
    {
        try {
            /* will listen to Enterprise_Rma_Model_Rma only*/
            $dataObject = $observer->getDataObject();
            if (get_class($dataObject) === 'Enterprise_Rma_Model_Rma') {
                if ($dataObject->getStatus() == 'closed') {
                    //send partial order return message
                }
            }
        } catch (Exception $exception) {
            $this->_handleException($exception);
        }

    }

    /**
     * Sets a value in the Mage::registry with a key based on registerName and key.
     *
     * @param $registerName Mage::register name to use
     * @param $key The key in the Mage::register array used
     * @param $value the value to set
     */
    protected function _registerKeyValue($registerName, $key, $value)
    {
        $array = Mage::registry($registerName);

        if (!isset($array)) {
            $array = array();
        } else {
            /* yes, unregister it so that we can push it back on */
            Mage::unregister($registerName);
        }

        $array[$key] = $value;

        Mage::register($registerName, $array);
    }

    /**
     * Gets the value registered in the Mage::registry and the key (e.g. order id).
     *
     * @param $registerName Mage::register name to use
     * @param $key The key in the Mage::register array used
     * @return the value registered, null if no value
     */
    protected function _getRegisterValueForKey($registerName, $key)
    {
        $array = Mage::registry($registerName);

        if (isset($array)) {
            if (isset($array[$key])) {
                return $array[$key];
            }
        }

        return null;
    }

    /**
     * Registers a value (such as an order or product id) in an array acting
     * as a set.
     *
     * @param $registerName Mage::register name to use
     * @param $value The value (e.g. id) to put in the set
     */
    protected function _registerValue($registerName, $value)
    {
        $set = Mage::registry($registerName);

        if (!isset($set)) {
            $set = array();
        } else {
            /* yes, unregister it so that we can push it back on */
            Mage::unregister($registerName);
        }

        if (!in_array($value, $set)) {
            array_push($set, $value);
        }

        Mage::register($registerName, $set);
    }

    /**
     * Determines if a value has been registered
     *
     * @param $registerName Mage::register name to use
     * @param $value The value (e.g. id) to put in the set
     * @return true if the value is in the set, false otherwise
     */
    protected function _isValueRegistered($registerName, $value)
    {
        $set = Mage::registry($registerName);
        if (isset($set) && in_array($value, $set)) {
            return true;
        }

        return false;
    }

    protected function _handleException(Exception $exception)
    {
        Mage::logException($exception);
    }

    protected function _isMarketplaceOrder($order_id)
    {
        $orders = Mage::getResourceModel('xcom_channelorder/order');
        if (empty($orders)) {
            return false;
        }
        $channelOrder = Mage::getModel('xcom_channelorder/order');
        $orders->load($channelOrder, $order_id, 'order_id');
        return $channelOrder == null;
    }
}
