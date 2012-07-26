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
 * An extended test case implementation that adds useful helper methods
 *
 * @package     selenium
 * @subpackage  Xcom_Chronicle
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
use Xcom_Chronicle_XMessages_WebStore as WebStore;
use Xcom_Chronicle_XMessages_Pim as Pim;
use Xcom_Chronicle_XMessages_Inventory as Inventory;

class Xcom_Chronicle_TestCase extends Mage_Selenium_TestCase
{
    protected $_testSubscriberBaseURL;
    protected $_xfabricBearerToken;
    protected $_baseUrl;
    protected $_avroEncoding;
    protected static $_lastXMessageId;

    /**
     * Constructs a test case with the given name and browser to test execution
     *
     * @param  string $name Test case name(by default = null)
     * @param  array  $data Test case data array(by default = array())
     * @param  string $dataName Name of Data set(by default = '')
     * @param  array  $browser Array of browser configuration settings: 'name', 'browser', 'host', 'port', 'timeout',
     * 'httpTimeout' (by default = array())
     */
    public function __construct($name = null, array $data = array(), $dataName = '', array $browser = array()) {

        parent::__construct($name, $data, $dataName, $browser);
        $this->_testSubscriberBaseURL = getenv("TEST_SUBSCRIBER_BASE_URL");

        /* read the config to setup the tokens and base urls */
        $this->_xfabricBearerToken = Mage::getConfig()
            ->getNode("default/xfabric/connection_settings/authorizations/xfabric/bearer_token");

        $this->_baseUrl = Mage::getBaseUrl(Mage_Core_Model_Store::URL_TYPE_WEB);

        $this->_avroEncoding = Mage::getConfig()->getNode("default/xfabric/connection_settings/encoding");
    }

    public function setUp()
    {
        parent::setUp();
        $this->setUpBeforeEachTest();
        self::$_lastXMessageId = $this->_getLatestXMessageId();
    }

    /**
     * Function is called before each test in a test class
     * and can be used for some precondition(s) for each test
     */
    public function setUpBeforeEachTest()
    {
    }

    protected function _getLatestXMessageId()
    {
        $response = $this->_getLatestXMessage($this->_testSubscriberBaseURL . "/messages/latest");
        $json = json_decode($response);
        if (!isset($json->{'id'})){
            return -1;
        }
        return $json->{'id'};
    }

    protected function _getLatestXMessage()
    {
        return $this->_executeCurl($this->_testSubscriberBaseURL . "/messages/latest");
    }

    protected function _executeCurl($url, $expectedResponse = 200)
    {
        $c = curl_init($url);
        curl_setopt($c, CURLOPT_RETURNTRANSFER, 1);
        $data = curl_exec($c);
        $errno = curl_errno($c);

        if (6 == $errno) {
            $this->fail("Unable to reach host. Make sure you have this line in your hosts file:\n"
                . '50.56.43.112 ' . $this->_testSubscriberBaseURL);
        } else if ($errno) {
            $this->fail('Curl error: ' . curl_error($c) . ' - URL: ' . $url);
        }

        $responseCode = curl_getinfo($c, CURLINFO_HTTP_CODE);
        $this->assertEquals($expectedResponse, $responseCode, "Response code did not match expected.");
        Mage::log("_executeCurl: " . print_r($data, true), null, 'debug.log', true);

        return $data;
    }

    protected function _get2dXMessages($offset = null)
    {
        $finalURL = $this->_testSubscriberBaseURL . "/messages";
        if ($offset == null) {
            $offset = self::$_lastXMessageId + 1;
        }
        $finalURL .= "?offset=$offset";
        $msgs = json_decode($this->_executeCurl($finalURL));
        $d_msgs = $this->_convertTo2DArray($msgs);
        Mage::log("_get2dXMessages-" . $offset . ": " . print_r($msgs, true), null, 'debug.log', true);
        Mage::log("2d messages: " . print_r($d_msgs, true), null, 'debug.log', true);

        $len = count($d_msgs);
        for ($c = 0; $c < $len; $c++) {
            $msg = $d_msgs[$c];
            $this->_verifyXMessageHeaders($msg);
        }

        return $d_msgs;
    }

    protected function _verifyXMessageHeaders($msg)
    {
        $this->assertEquals("Successful", $msg['decodingStatus'], "Decoding status was not successful in message:" . print_r($msg, true));
        $contentType = $msg['headers.CONTENT-TYPE'];
        if ($this->_avroEncoding == "binary") {
            $expectedContentType = "avro/binary";
        } else {
            $expectedContentType = "application/json";
        }
        $this->assertEquals($expectedContentType, $contentType, "Encoding is not the expected type.");
    }

    protected function _findXMessageByTopic($msgs, $topic){
        $len = count($msgs);
        $foundCtr = 0;
        $foundMsgs = array();
        for ($c = 0; $c < $len; $c++) {
            if ($msgs[$c]['topic'] == $topic) {
                $foundMsgs[$foundCtr] = $msgs[$c];
                $foundCtr++;
            }
        }
        Mage::log("_findXMessageByTopic-" . $topic . ": " . print_r($foundMsgs, true), null, 'debug.log', true);
        return $foundMsgs;
    }

    public function verifyXMessage($expectedMsgs, $msgs) {

        Mage::log("verifyXMessage-expected messages: " . print_r($expectedMsgs, true), null, 'debug.log', true);
        Mage::log("verifyXMessage-actural messages: " . print_r($msgs, true), null, 'debug.log', true);

        $this->assertEquals(count($expectedMsgs), count($msgs), "Should have " . count($expectedMsgs) . " messages generated.");
        $result = array();
        foreach ($expectedMsgs as $expMsg) {
            $foundMsgs = $this->_findXMessageByTopic($msgs, $expMsg['topic']);
            if ( empty($foundMsgs)){
                $result[] = $expMsg;
            } else {
                $diff = array();
                $miniDiff = array();
                foreach ($foundMsgs as $foundMsg) {
                    $diff = array_udiff_uassoc($expMsg, $foundMsg, array($this, "_data_compare_func"), array($this, "_key_compare_func"));
                    if (empty($diff)){
                        break;
                    } else {
                        if (empty($miniDiff) || (!empty($miniDiff) && (count($diff) < count($miniDiff)))){
                            $miniDiff = $diff;
                        }
                    }
                }
                if (!empty($miniDiff)){
                    $miniDiff['topic'] = $expMsg['topic'];
                    $result[] = $miniDiff;
                }
            }
        }
        $this->assertEquals(0, count($result), "Missed the following messages:" . print_r($result, true));
    }

    function _data_compare_func($a, $b){
        // $a is empty
        if (empty($a)){
            if (empty($b)){
                return 0;
            } else {
                return -1;
            }           
        }

        // $a is numeric
        if (is_numeric($a)) {
            if (is_numeric($b)) {
                return ($a == $b ? 0 : -1);
            } else {
                return -1;
            }
        }

        // $a is regular expression
        if (preg_match('/^!.*!$/', $a) > 0 ){
            $a = str_ireplace('!', "", $a);
        } else {
            $a = preg_quote($a, "/");
        }

        // $a is regular string
        $pattern = "/^" . $a . "$/";
        if (preg_match($pattern, $b) > 0) {
            return 0;
        } else {
            return -1;
        }
    }
    
    function _key_compare_func($a, $b){
        // $a is regular expression
        if (preg_match('/^!.*!$/', $a) > 0 ){
            $a = str_ireplace('!', "", $a);
        } else {
            $a = preg_quote($a, "/");
        }
        
        // $a is regular string
        $pattern = "/^" . $a . "$/";
        if (preg_match($pattern, $b) > 0) {
            return 0;
        } else {
            return -1;
        }

    }

    private function _flatten_array($array, $return = null, $key_prefix = null) {
        foreach ($array as $key => $value) {
            if (isset($key_prefix)){
                $key = $key_prefix . "." . $key;
            }
            if (is_array($value) || is_object($value)){
                $return = $this->_flatten_array($value, $return, $key);
            } else {
                $return[$key] = $value;
            }
        }
        return $return;
    }

    private function _convertTo2DArray($array){
        if (!isset($array)) {
            return $array;
        }
        $result = null;
        foreach ($array as $value) {
            $result[] = $this->_flatten_array($value);
        }
        return $result;
    }

    protected function dataToString($data){
        return print_r($data, true);
    }

    protected function _mockRequest($requester, $action, $options)
    {
        $fabricURL = $this->_baseUrl .  "/index.php/xfabric/endpoint";
        $c = curl_init($fabricURL . $action['namespace'] . $action['topic']);
        curl_setopt($c, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($c, CURLOPT_POST, 1);
        curl_setopt($c, CURLOPT_SSL_VERIFYHOST, 0);
        curl_setopt($c, CURLOPT_SSL_VERIFYPEER, 0);
        curl_setopt($c, CURLOPT_FOLLOWLOCATION, 1);

        $contentType = "application/json";
        if ($this->_avroEncoding == "binary") {
            $contentType = "avro/binary";
        }
        $authorizationToken = $this->_xfabricBearerToken;
        curl_setopt($c, CURLOPT_HTTPHEADER, array(
            "Authorization: " . $authorizationToken,
            "X-XC-SCHEMA-VERSION: " . $action['version'],
            "X-XC-SCHEMA-URI: https://api.x.com/ocl" . $action['topic'] . "/" . $action['version'],
            "Content-Type: " . $contentType
        ));

        $body = $requester->process(new Varien_Object($options))->getBody();
        curl_setopt($c, CURLOPT_POSTFIELDS, $body);

        $data = curl_exec($c);

        if (curl_errno($c)) {
            $this->fail('Curl error: ' . curl_error($c));
        }

        $responseCode = curl_getinfo($c, CURLINFO_HTTP_CODE);
        $this->assertEquals(200, $responseCode, "Response code did not match expected.");

        return $data;
    }

    
    #*********************************************
    #*                Helper Methods             *
    #*********************************************

    /**
     * <p>Create a new simple product</p>
     *
     * @return array $productData
     */
    protected function _createSimpleProduct() {
        $productData = $this->loadDataSet('Product', 'simple_product_visible');
        $this->_createProduct($productData, 'simple');
        return $productData;
    }

    /**
     * <p>Create a new configurable product</p>
     *
     * @return array $productData
     */
    protected function _createConfigurableProduct() {
        $attrData = $this->_createConfigurableAttribute();
        $simpleData = $this->loadDataSet('Product', 'simple_product_visible');
        $simpleData['general_user_attr']['dropdown'][$attrData['attribute_code']] = $attrData['option_1']['admin_option_name'];
        $productSimple = $this->_createProduct($simpleData, 'simple');
        // Create a configurable product
        $productData = $this->loadDataSet('Product', 'configurable_product_visible',
                                       array('configurable_attribute_title' => $attrData['admin_title']));
        $productData['associated_configurable_data'] = $this->loadDataSet('Product', 'associated_configurable_data',
            array('associated_search_sku' => $simpleData['general_sku']));
        $productData['special_options'] = $this->loadDataSet('Product', 'configurable_options_to_add_to_shopping_cart',
            array('custom_option_dropdown' => $attrData['option_1']['store_view_titles']['Default Store View'],
                  'title'                  => $attrData['admin_title']));
        $productConfigurable = $this->_createProduct($productData, 'configurable');
        return $productData;
    }

    /**
     * <p>Create a new configurable attribute set</p>
     *
     * @return array $attrData
     */
    protected function _createConfigurableAttribute()
    {
        //Data
        $attrData = $this->loadDataSet('ProductAttribute', 'product_attribute_dropdown_with_options');
        $associatedAttributes = $this->loadDataSet('AttributeSet', 'associated_attributes',
                                                array('General' => $attrData['attribute_code']));
        //Steps
        $this->navigate('manage_attributes');
        $this->productAttributeHelper()->createAttribute($attrData);
        //Verifying
        $this->assertMessagePresent('success', 'success_saved_attribute');
        //Steps
        $this->navigate('manage_attribute_sets');
        $this->attributeSetHelper()->openAttributeSet();
        $this->attributeSetHelper()->addAttributeToSet($associatedAttributes);
        $this->saveForm('save_attribute_set');
        //Verifying
        $this->assertMessagePresent('success', 'success_attribute_set_saved');
        return $attrData;
    }

    /**
     * <p>Create a new product of the specified type</p>
     *
     * @param array $productData Product data to fill in backend
     * @param null|string $productType E.g. 'simple'|'configurable' etc.
     *
     * @return array $productData
     */
    protected function _createProduct(array $productData, $productType)
    {
        $this->navigate('manage_products');
        $this->assertTrue($this->checkCurrentPage('manage_products'), $this->messages, "Should be at manage_products page");
        $productData = $this->clearDataArray($productData);
        $this->productHelper()->createProduct($productData, $productType);
        $this->assertMessagePresent('success', 'success_saved_product');

        return $productData;
    }

    /**
     * <p>Update the given product</p>
     *
     * @param null|string $type E.g. 'simple'|'configurable' etc.
     * @param array $updateProductData Update product data to fill in backend
     *
     */
    protected function _updateProduct($searchProductData, $updateProductData) {
        //Update Product
        $productSearch = $this->loadDataSet('Product', 'product_search', $searchProductData);
        $this->productHelper()->openProduct($productSearch);
        $this->productHelper()->fillProductInfo($updateProductData);
        $this->clickButtonAndConfirm('save', 'success_saved_product');
    }

    /**
     * <p>Check existence of the product of the specified type</p>
     *
     * @param 'Simple'|string $type E.g. 'simple'|'configurable' etc.
     *
     * @return boolean
     */
    protected function anyExistingProduct($type = "simple"){
        $productSearch = $this->loadDataSet('Product', 'product_search', array('product_type' =>  ucfirst($type) . " Product"));
        $this->_prepareDataForSearch($productSearch);
        $xpathTR = $this->search($productSearch, 'product_grid');
        return !empty($xpathTR);
    }

    /**
     * <p>Delete a product of the specified type</p>
     *
     * @param null|string $type E.g. 'simple'|'configurable' etc.
     *
     */
    protected function _deleteProduct($type) {

        $this->navigate('manage_products');
        $this->assertTrue($this->checkCurrentPage('manage_products'), $this->messages);
        if (!$this->anyExistingProduct($type)){
            $this->_createSimpleProduct();
        }

        //Delete Product
        $productSearch = $this->loadDataSet('Product', 'product_search', array('product_type' =>  ucfirst($type) . " Product"));
        $this->productHelper()->openProduct($productSearch);
        $this->clickButtonAndConfirm('delete', 'confirmation_for_delete');
        //Verifying
        $this->assertMessagePresent('success', 'success_deleted_product');
    }
    
    #*********************************************
    #*                Helper Methods             *
    #*********************************************
    /**
     * Get Product Count based on given filters.
     * If filters is null, return counts of all products.
     * @param array null $filters
     * @return int
     */
    protected function _getProductCount($filters = null)
    {
        return count($this->_getProducts(null, null, $filters));

    }

    /**
     * Get counts of all simple products
     * @return int
     */
    protected function _getSimpleProductCount()
    {
        $filters = array(
            array(
                'field' => 'type_id',
                'value' => 'simple',
            )
        );
        return $this->_getProductCount($filters);
    }

    /**
     * Get $limit number of products from $offset based on given filters
     *
     * @param int null $limit
     * @param int null $offset
     * @param array null $filters
     * @return mixed
     */
    protected function _getProducts($limit = null, $offset = null, $filters = null)
    {
        $products = Mage::getResourceModel('catalog/product_collection')
            ->setOrder('created_at', 'asc');
        if ($limit != null || $offset != null) {
            $products->getSelect()->limit($limit,$offset);
        }
        if (isset($filters)){
            foreach ($filters as $filter) {
                $products->addFilter($filter['field'], $filter['value'], $filter['type']);
            }
        }
        $products->load();
        return $products;
    }

    /**
     * Get $limit number of simple products from $offset
     *
     * @param int null $limit
     * @param int null $offset
     * @return mixed
     */
    protected function _getSimpleProducts($limit = null, $offset = null)
    {
        $filters = array(
            array(
                'field' => 'type_id',
                'value' => 'simple',
            )
        );
        return $this->_getProducts($limit, $offset, $filters);
    }

    protected function _createProducts($productsToCreateCount)
    {
        $skus = array();
        
        $initialProductCount = $this->_getProductCount();
        $randValues = array(
            'general_name'  =>  'crife_Simple Product Required',
            'general_sku'   =>  'wkdov_simple_sku_req_zuknf'
        );

        // Set an Admin Session
        Mage::app()->setCurrentStore(Mage_Core_Model_App::ADMIN_STORE_ID);
        Mage::getSingleton('core/session', array('name'=>'adminhtml'));
        $userModel = Mage::getModel('admin/user');
        $userModel->setUserId(1);
        $session = Mage::getSingleton('admin/session');
        $session->setUser($userModel);
        $session->setAcl(Mage::getResourceModel('admin/acl')->loadAcl());

        for ($c = 0; $c < $productsToCreateCount; $c++) {
            array_walk_recursive($randValues, array($this, 'randomizeData'), array('general_name', 'general_sku'));
            $newProduct = new Mage_Catalog_Model_Product();
            $newProduct->setTypeId('simple');
            $newProduct->setCategoryIds(array(42));
            $newProduct->setWebsiteIDs(array(1));
            $newProduct->setWeight('1.000');
            $newProduct->setVisibility(Mage_Catalog_Model_Product_Visibility::VISIBILITY_BOTH);
            $newProduct->setStatus(1);
            $newProduct->setSku($randValues['general_sku']);
            $newProduct->setTaxClassId(0);
            $newProduct->setStoreId(Mage::app()->getStore()->getId());
            $newProduct->setAttributeSetId(9);
            $newProduct->setName($randValues['general_name']);
            $newProduct->setDescription('Description');
            $newProduct->setShortDescription('Short Description');
            $newProduct->setPrice(9.99);
            $newProduct->setStockData(array(
                'is_in_stock' => 1,
                'qty' => 99999
            ));
            $newProduct->setCreatedAt(strtotime('now'));
            $newProduct->save();
            
            $skus[] = $randValues['general_sku'];
        }

        $finalProductCount = $this->_getProductCount();

        $this->assertEquals($productsToCreateCount, $finalProductCount - $initialProductCount);
        
        return $skus;
    }
    /*
     * Helper method that creates a customer
     *
     * @return array
     */
    protected function _createCustomer()
    {
        $customerData = $this->loadDataSet('Customer', 'all_fields_customer_account');
        $customerAddressData = $this->loadDataSet('Customer', 'all_fields_address');
        $this->navigate('manage_customers');
        $this->customerHelper()->createCustomer($customerData, $customerAddressData);
        $this->assertMessagePresent('success', 'success_saved_customer');
        return array("customerData" => $customerData,
                     "customerAddressData" => $customerAddressData);
    }
}