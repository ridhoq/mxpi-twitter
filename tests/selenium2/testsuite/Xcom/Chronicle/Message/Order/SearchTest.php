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

class Xcom_Chronicle_Model_Message_Order_Search_Outbound extends Xcom_Xfabric_Model_Message_Request
{
    private $_customerData;

    protected function _construct()
    {
        parent::_construct();
        $this->_topic = 'com.x.ordermanagement.v2/OrderSearch.NonSensitive/SearchOrder';
        $this->_schemaVersion  = "2.0.1";
    }


    /**
     * @param null|Varien_Object $dataObject
     * @return Xcom_Xfabric_Model_Message_Request
     */
    public function _prepareData(Varien_Object $dataObject = null)
    {
        $numberItems = null;
        $startItemIndex = null;

        if ($dataObject != null) {
            $numberItems = $dataObject["numberItems"];
            $startItemIndex = $dataObject->getStartItemIndex();
        }

        $data = array(
            'query' => array(
                'numberItems' => $numberItems,
                'numberItemsFound' => null,
                'fields' => null,
                'predicates' => null,
                'ordering' => array(), // TODO: Make sure this becomes optional in contract
                'startItemIndex' => $dataObject["startItemIndex"]
            )
        );
        $this->setMessageData($data);
        return parent::_prepareData($dataObject);
    }
};

/**
 * Search Orders
 *
 * @package     selenium
 * @subpackage  tests
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class Xcom_Chronicle_Message_Order_SearchTest extends Mage_Selenium_TestCase
{
    protected $_fabricUrl;

    protected $_xfabricBearerToken;

    protected $_baseUrl = "http://xcom.loc/magento/ee-1.11/1.11";

    protected $_avroEncoding;

    protected $_productSku;

    protected $_customer;

    protected $_subscriberTestBaseURL = "http://eric.magentosubscribertest.xcommerce.net";

    /**
     * <p>Preconditions:</p>
     * <p>Setup x.fabric</p>
     */
    public function setUp()
    {
        $this->_subscriberTestBaseURL = "http://eric.magentosubscribertest.xcommerce.net";
        // $this->_subscriberTestBaseURL = "http://127.0.0.1:9091";

        /*
        $this->_cleanUp();
        $this->_registerAsCapability("order_searched test subscriber");
        $authorization = $this->_createUutAsClone(
            "Magento Store Front",
            "Magente Store Cloned - order_searched capability"
        );

        $authModel = Mage::getModel('xcom_xfabric/authorization');
        $authModel->importFile($authorization);
        $authModel->save();
        */

        /* read the config to setup the tokens and base urls */
        $this->_xfabricBearerToken = Mage::getConfig()
            ->getNode("default/xfabric/connection_settings/authorizations/xfabric/bearer_token");

        $this->_baseUrl = Mage::getBaseUrl(Mage_Core_Model_Store::URL_TYPE_WEB);

        $this->_avroEncoding = Mage::getConfig()->getNode("default/xfabric/connection_settings/encoding");

//        $this->loginAdminUser();
//
//        /* create the product to use in orders */
//        $simpleSku = $this->loadData('simple_product_required', NULL, array('general_name', 'general_sku'));
//        $simpleSku["inventory_qty"] = 100000;
//        $this->_productSku = $simpleSku['general_sku'];
//
//        $this->navigate('manage_products');
//        $this->assertTrue($this->checkCurrentPage('manage_products'), $this->messages);
//        $this->productHelper()->createProduct($simpleSku);
//        $this->assertMessagePresent('success', 'success_saved_product');
//
//        /* create the customer to use in orders */
//        $userData = $this->loadData('generic_customer_account', null, 'email');
//        // $userData["email"] = "somebody@example.com";
//        $this->navigate('manage_customers');
//        $this->customerHelper()->createCustomer($userData);
//
//        $this->_customer = Mage::getModel("customer/customer")->setWebsiteId(Mage_Core_Model_App::DISTRO_STORE_ID)
//            ->loadByEmail($userData["email"]);
    }

    protected function assertPreConditions()
    {
        parent::assertPreConditions();
        $this->addParameter('id', '0');
    }

    /**
     * Issues an /order/search for all of the orders in the system.
     *
     * @test
     */
    public function testSearchOrdersNoQuery()
    {
        $this->markTestIncomplete();
        $initialOrderCount = $this->_getOrderCount();

        if ($initialOrderCount > 5000) {
             $this->markTestSkipped("Skipping test in case there are too many orders initially");
        }

        $latestMessageId = $this->_getLatestFabricMessageId();

        /* issue the search */
        $this->_postSearch(
            $this->_baseUrl .  "/index.php/xfabric/endpoint",
            $this->_xfabricBearerToken,
            null,
            null
        );

        $orderSearchMsg = $this->_getFabricMessages($latestMessageId);

        $this->_findOrderSearchMessage($latestMessageId, null, null, $initialOrderCount);
    }

    /**
     * Issues an /order/search to discover the first order.
     *
     * @test
     */
    public function testSearchOrdersWithMaximumAndOffsetSet()
    {
        $this->markTestIncomplete('Failing with message: "Curl error: <url> malformed"');
        $initialOrderCount = $this->_getOrderCount();

        if ($initialOrderCount == 0) {
            $itemsToCreate = 1;
            $this->_createOrders($itemsToCreate);
        }

        $startMessageId = $this->_getLatestFabricMessageId();

        /* issue the search */
        $this->_postSearch(
            $this->_baseUrl .  "/index.php/xfabric/endpoint",
            $this->_xfabricBearerToken,
            1,
            0
        );

        $this->_findOrderSearchMessage($startMessageId, 1, 0, 1);
    }

    /**
     * Issues an /order/search for a large number of orders.
     *
     * @test
     */
    public function testSearchLargeOrderSearch()
    {
        $this->markTestIncomplete('Failing with message: "Fatal error: Call to a member function getEntityId() on a non-object in D:\magento\xfabric\tests\selenium2\testsuite\Xcom\Chronicle\Order\SearchTest.php on line 285"');
        $itemsToCreate = 1000;

        $initialOrderCount = $this->_getOrderCount();

        $this->_createOrders($itemsToCreate);

        $startMessageId = $this->_getLatestFabricMessageId();

        /* issue the search */
        $this->_postSearch(
            $this->_baseUrl .  "/index.php/xfabric/endpoint",
            $this->_xfabricBearerToken,
            $itemsToCreate,
            $initialOrderCount
        );

        $this->_findOrderSearchMessage($startMessageId, $itemsToCreate, $initialOrderCount, $itemsToCreate);
    }

    /**
     * Issues an /order/search and page through.
     *
     * @test
     */
    public function testSearchLargeOrdersWithClientSidePagination()
    {
        $this->markTestIncomplete('Failing with message: "Fatal error: Call to a member function getEntityId() on a non-object in D:\magento\xfabric\tests\selenium2\testsuite\Xcom\Chronicle\Order\SearchTest.php on line 285"');
        $itemsToCreate = 1000;
        $iterationCount = 99;

        $initialOrderCount = $this->_getOrderCount();

        $this->_createOrders($itemsToCreate);

        $total = $itemsToCreate + $initialOrderCount;

        for ($offset = $initialOrderCount; $offset < $total; $offset += $iterationCount) {

            $startMessageId = $this->_getLatestFabricMessageId();

            /* issue the search */
            $this->_postSearch(
                $this->_baseUrl .  "/index.php/xfabric/endpoint",
                $this->_xfabricBearerToken,
                $iterationCount,
                $offset
            );

            $expectedCount = min($iterationCount, $total - $offset);

            $this->_findOrderSearchMessage($startMessageId, $iterationCount, $offset, $expectedCount);
        }
    }

    protected function _createOrders($ordersToCreateCount)
    {
        $initialOrderCount = $this->_getOrderCount();

        $this->_prepareQuote($this->_customer, $this->_productSku);

        for ($c = 0; $c < $ordersToCreateCount; $c++) {
            $this->_getQuote()
                ->setGrandTotal("10")
                ->setBaseCurrencyCode("USD")
                ->reserveOrderId()
                ->setStoreId(Mage_Core_Model_App::ADMIN_STORE_ID);

            $this->_getQuote()
                ->collectTotals()
                ->save();

            $order = $this->_saveOrder($this->_customer->getEntityId());
        }

        $finalOrderCount = $this->_getOrderCount();

        $this->assertEquals($ordersToCreateCount, $finalOrderCount - $initialOrderCount);
    }

    protected function _findOrderSearchMessage($startMessageId,
        $numberItemsInQuery,
        $startItemIndexInQuery,
        $expectedNumberItemsFound)
    {
        $found = false;
        $msgId = $startMessageId;
        $retries = 0;

        while (!$found && $retries < 10) {
            $latestMsgId = $this->_getLatestFabricMessageId();

            $receivedMsgs = $this->_getFabricMessages($msgId);
            for ($c = 0; $msgId <= $latestMsgId && !$found; $c++, $msgId++) {
                $found = $this->_verifyOrderSearchSuccessMsg($receivedMsgs[$c],
                    $numberItemsInQuery,
                    $startItemIndexInQuery,
                    $expectedNumberItemsFound);
            }

            if (!$found) {
                sleep(2);
            }
            $retries++;
        }

        if (!$found) {
            $this->fail("Could not find /order/searchSucceeded message");
        }
    }

    protected function _verifyOrderSearchSuccessMsg($msg,
        $numberItemsInQuery,
        $startItemIndexInQuery,
        $expectedNumberItemsFound)
    {
        $topic = $msg->{'topic'};
        if ($topic != "/com.x.ordermanagement.v2/OrderSearch.NonSensitive/SearchNonSensitiveOrderSucceeded") {
            return false;
        }

//        $query = $msg->{'message'}->{'query'};
//
//        if ($numberItemsInQuery != $query->{'numberItems'}->{'long'}) {
//            return false;
//        }
//
//        if ($expectedNumberItemsFound != $query->{'numberItemsFound'}->{'long'}) {
//            return false;
//        }
//
//        if ($startItemIndexInQuery != $query->{'startItemIndex'}->{'long'}) {
//            return false;
//        }
//
//        $orders = $msg->{'message'}->{'orders'};
//
//        $this->assertEquals($expectedNumberItemsFound, count($orders));
//
//        $ordersInDB = $this->_getOrders($numberItemsInQuery, $startItemIndexInQuery);
//
//        $c = 0;
//        foreach ($ordersInDB as $expectedOrderInDB) {
//            $orderId = $expectedOrderInDB->getIncrementId();
//
//            $o = $orders[$c];
//
//            $this->assertEquals($orderId, $o->{'orderNumber'}->{'string'});
//
//            $c++;
//        }

        return true;
    }

    protected function _getQuote()
    {
        return Mage::getSingleton('adminhtml/session_quote')->getQuote();
    }

    /**
     * Prepare quote item
     *
     * @return Xcom_Order_Model_Message_Order_Created
     */
    protected function _prepareQuote($customerData, $productSku)
    {
        $this->_addCustomerToQuote($customerData)
            ->_addBillingAddressToQuote($customerData)
            ->_addShippingAddressToQuote($customerData)
            ->_addItemsToQuote($productSku);

        return $this;
    }

    /**
     * Add customer to quote
     *
     * @return Xcom_Order_Model_Message_Order_Created
     */
    protected function _addCustomerToQuote($customerData)
    {
        $this->_customerData['email']      = "somebody@example.com";
        $this->_customerData['firstname']  = "some";
        $this->_customerData['middlename'] = "";
        $this->_customerData['lastname']   = "body";
        $this->_customerData['prefix']     = "";
        $this->_customerData['suffix']     = "";

        $customer = Mage::getModel('customer/customer');
        $customer->addData($this->_customerData);
        $this->_getQuote()->setCustomer($customer);
        return $this;
    }

    /**
     * Add billing address to quote
     *
     * @return Xcom_Order_Model_Message_Order_Created
     */
    protected function _addBillingAddressToQuote($customerData)
    {
        $billing['address_id'] = null;
        $billing['region_id']  = null;
        $billing['country_id'] = "USA";
        $billing['firstname']  = "some";
        $billing['lastname']   = "body";
        $billing['email']      = "somebody@example.com";

        $billing['street'][0]  = "7700 W Parmer Lane";
        $billing['street'][1]  = "";
        $billing['city']       = "Austin";
        $billing['region']     = "TX";
        $billing['postcode']   = "78705";
        $billing['telephone']  = "512-123-4567";

        /** @var $billingAddress Mage_Sales_Model_Quote_Address */
        $billingAddress = Mage::getModel('sales/quote_address');
        $billingAddress->setData($billing);
        $billingAddress->implodeStreetAddress();

        $this->_getQuote()->setBillingAddress($billingAddress);
        return $this;
    }

    /**
     * Add shipping address to quote
     *
     * @return Xcom_Order_Model_Message_Order_Created
     */
    protected function _addShippingAddressToQuote($customerData)
    {
        $shipping['address_id'] = null;
        $shipping['region_id']  = null;
        $shipping['country_id'] = "USA";
        $shipping['firstname']  = "some";
        $shipping['lastname']   = "body";
        $shipping['street'][0]  = "7700 W Parmer Lane";
        $shipping['street'][1]  = "";
        $shipping['city']       = "Austin";
        $shipping['region']     = "TX";
        $shipping['postcode']   = "78705";
        $shipping['telephone']  = "512-987-6543";

        $shippingAddress = Mage::getModel('sales/quote_address');
        $shippingAddress->setData($shipping);
        $shippingAddress->implodeStreetAddress();
        $this->_getQuote()->setShippingAddress($shippingAddress);
        return $this;
    }

    /**
     * Add order items to quote
     *
     * @param array $data
     * @return Xcom_Order_Model_Message_Order_Created
     * @throws Mage_Core_Exception
     */
    protected function _addItemsToQuote($productSku)
    {
        /** @var $quoteItem Mage_Sales_Model_Quote_Item */
        $quoteItem = Mage::getModel('sales/quote_item');
        $quoteItem->setQty(1);
        $quoteItem->setPrice("1.00");
        /** @var $product Mage_Catalog_Model_Product */
        $product = $this->_getProductBySku($productSku);
        $product->setPrice("1.00");
        $product->setFinalPrice("1.00");
        $quoteItem->setProduct($product);
        $this->_getQuote()->addItem($quoteItem);

        return $this;
    }

    /**
     * @param string $sku
     * @return Mage_Catalog_Model_Product
     */
    protected function _getProductBySku($sku)
    {
        /** @var $product Mage_Catalog_Model_Product */
        $product = Mage::getModel('catalog/product');
        $productId = $product->getIdBySku($sku);
        if ($productId) {
            return Mage::getModel('catalog/product')->load((int)$productId);
        }
        return $product;
    }

    /**
     * Save order via Mage_Sales_Model_Order
     *
     * @return Mage_Sales_Model_Order
     * @throws Exception
     */
    protected function _saveOrder($customerId)
    {
        /** @var $order Mage_Sales_Model_Order */
        $order = Mage::getModel('sales/order');

        /** @var $helper Mage_Core_Helper_Data */
        $helper = Mage::helper('core');
        $helper->copyFieldset('customer_account', 'to_quote',
            $this->_customerData, $order);

        $quote = $this->_getQuote();

        $order->setIncrementId($quote->getReservedOrderId())
            ->setStoreId(Mage_Core_Model_App::ADMIN_STORE_ID)
            ->setCustomerId($customerId)
            ->setCustomerGroupId(Mage_Customer_Model_Group::NOT_LOGGED_IN_ID)
            ->setState(Mage_Sales_Model_Order::STATE_NEW)
            ->setStatus('pending')
            ->setShippingDescription('Flat Rate - Fixed')
            ->setQuoteId($quote->getEntityId());

        /** @var $converter Mage_Sales_Model_Convert_Quote */
        $converter  = Mage::getModel('sales/convert_quote');
        $order->setBillingAddress(
            $converter->addressToOrderAddress($quote->getBillingAddress())
        );
        $order->setShippingAddress(
            $converter->addressToOrderAddress($quote->getShippingAddress())
        );
        $order->setPayment(
            $converter->paymentToOrderPayment($quote->getPayment()->setMethod('free'))
        );

        foreach ($quote->getAllItems() as $item) {
            $orderItem = $converter->itemToOrderItem($item);
            $order->addItem($orderItem);
        }

        $order->setGrandTotal($quote->getGrandTotal());
        $order->setBaseGrandTotal($quote->getBaseGrandTotal());
        $order->setBaseCurrencyCode($quote->getBaseCurrencyCode());
        $order->setGlobalCurrencyCode($quote->getBaseCurrencyCode());
        $order->setStoreCurrencyCode($quote->getBaseCurrencyCode());
        $order->setOrderCurrencyCode($quote->getBaseCurrencyCode());
        $order->setQuote($quote);

        $transaction = Mage::getModel('core/resource_transaction');
        $transaction->addObject($order);
        $transaction->addCommitCallback(array($order, 'save'));

        try {
            $transaction->save();
        } catch (Exception $e) {
            //reset order ID's on exception, because order not saved
            $order->setId(null);
            /** @var $item Mage_Sales_Model_Order_Item */
            foreach ($order->getItemsCollection() as $item) {
                $item->setOrderId(null);
                $item->setItemId(null);
            }
            throw $e;
        }
        return $order;
    }

    protected function _getOrderCount()
    {
        $orders = Mage::getResourceModel('sales/order_collection')
            ->addFieldToSelect('increment_id')
            ->setOrder('created_at', 'astc');

        return $orders->getSize();
    }

    protected function _getOrders($limit = null, $offset = null)
    {
        $orders = Mage::getResourceModel('sales/order_collection')
            ->addFieldToSelect('*')
            ->setOrder('created_at', 'asc');
        if ($limit != null || $offset != null) {
            $orders->getSelect()->limit($limit,$offset);
        }
        $orders->load();

        return $orders;
    }

    protected function _postSearch($fabricURL, $authorizationToken, $numberItems = null, $startItemIndex = null)
    {
        $c = curl_init($fabricURL . "/com.x.ordermanagement.v2/OrderSearch.NonSensitive/SearchOrder");
        curl_setopt($c, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($c, CURLOPT_POST, 1);
        curl_setopt($c, CURLOPT_SSL_VERIFYHOST, 0);
        curl_setopt($c, CURLOPT_SSL_VERIFYPEER, 0);

        $contentType = "application/json";
        if ($this->_avroEncoding == "binary") {
            $contentType = "avro/binary";
        }
        curl_setopt($c, CURLOPT_HTTPHEADER, array(
            "Authorization: " . $authorizationToken,
            "X-XC-SCHEMA-VERSION: 2.0.1",
            "X-XC-SCHEMA-URI: https://api.x.com/ocl/com.x.ordermanagement.v2/OrderSearch.NonSensitive/SearchOrder/2.0.1",
            'X-XC-RESULT-CORRELATION-ID: cid',
            "Content-Type: " . $contentType
        ));

        $body = new Xcom_Chronicle_Model_Message_Order_Search_Outbound();
        $body = $body->process(new Varien_Object(array( "numberItems" => $numberItems,
            "startItemIndex" => $startItemIndex )))->getBody();
        curl_setopt($c, CURLOPT_POSTFIELDS, $body);

        $data = curl_exec($c);

        if (curl_errno($c)) {
            $this->fail("Received error code after executing cURL: " . curl_errno($c) . ' \t curl: ' . print_r($data, true) . ' \t fabricUrl: ' . $fabricURL);
        }

        $responseCode = curl_getinfo($c, CURLINFO_HTTP_CODE);
        $this->assertEquals(200, $responseCode);

        return $data;
    }

    protected function _getLatestFabricMessageId()
    {
        $response = $this->_getLatestFabricMessage($this->_subscriberTestBaseURL . "/messages/latest");
        $json = json_decode($response);
        return $json->{'id'};
    }

    protected function _getLatestFabricMessage()
    {
        return $this->_executeCurl($this->_subscriberTestBaseURL . "/messages/latest");
    }

    protected function _getFabricMessages($offset = null)
    {
        $finalURL = $this->_subscriberTestBaseURL . "/messages";
        if ($offset != null) {
            $finalURL .= "?offset=$offset";
        }
        $msgs = json_decode($this->_executeCurl($finalURL));

        $len = count($msgs);
        for ($c = 0; $c < $len; $c++) {
            $msg = $msgs[$c];
            $this->_verifyFabricMessageHeaders($msg);
        }
        return $msgs;
    }

    protected function _verifyFabricMessageHeaders($msg)
    {
        $this->assertEquals("Successful", $msg->{'decodingStatus'}, "Decoding status was not successful.");
        $headers = $msg->{'headers'};
        $contentType = $headers->{"CONTENT-TYPE"};
        if ($this->_avroEncoding == "binary") {
            $expectedContentType = "avro/binary";
        } else {
            $expectedContentType = "application/json";
        }
        $this->assertEquals($expectedContentType, $contentType, "Encoding is not the expected type.");
    }

    protected function _registerAsCapability($name)
    {
        $finalURL = $this->_subscriberTestBaseURL . "/command/registerAsCapability" . "?name=" . urlencode($name);
        return $this->_executeCurl($finalURL);
    }

    protected function _createUutAsClone($origName, $cloneName)
    {
        $finalURL = $this->_subscriberTestBaseURL . "/command/createUutAsClone";
        $finalURL .= "?origName=" . urlencode($origName) . "&cloneName=" . urlencode($cloneName);
        return $this->_executeCurl($finalURL);
    }

    protected function _cleanUp()
    {
        return $this->_executeCurl($this->_subscriberTestBaseURL . "/command/cleanUp");
    }

    protected function _executeCurl($url, $expectedResponse = 200)
    {
        $c = curl_init($url);
        curl_setopt($c, CURLOPT_RETURNTRANSFER, 1);

        $data = curl_exec($c);
        $errno = curl_errno($c);

        if (6 == $errno) {
            $this->fail("Unable to reach host. Make sure you have this line in your hosts file:\n"
                . '50.56.43.112 ' . $this->_subscriberTestBaseURL);
        } else if ($errno) {
            $this->fail('Curl error: ' . curl_error($c) . '\t url: ' . $url);
        }

        $responseCode = curl_getinfo($c, CURLINFO_HTTP_CODE);
        $this->assertEquals($expectedResponse, $responseCode, "Response code did not match expected.");

        return $data;
    }
}

