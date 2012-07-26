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

    class Xcom_Chronicle_Model_Message_Webstore_Get_Outbound extends Xcom_Xfabric_Model_Message_Request
    {
        protected function _construct()
        {
            parent::_construct();
            $this->_topic = 'com.x.webstore.v1/WebStoreMetadataProvision/GetAllWebStore';
            $this->_schemaRecordName = 'GetAllWebStore';
            $this->_schemaFile = 'Xcom_Chronicle/com.x.webstore.v1.avpr';
            $this->_schemaVersion  = '1.0.0';
        }

        /**
         * @param null|Varien_Object $dataObject
         * @return Xcom_Xfabric_Model_Message_Request
         */
        public function _prepareData(Varien_Object $dataObject = null)
        {
            $data = array();
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
class Xcom_Chronicle_Message_Webstore_GetTest extends Mage_Selenium_TestCase
{
    protected $_fabricUrl;

    protected $_xfabricBearerToken;

    protected $_baseUrl;

    protected $_avroEncoding;

    protected $_subscriberTestBaseURL;

    const DEFAULT_WEBSTORE = 'Default Store View';

    /**
     * <p>Preconditions:</p>
     * <p>Setup x.fabric</p>
     */
    public function setUpBeforeTests()
    {
        $this->_subscriberTestBaseURL = "http://testsub.magentosubscribertest.xcommerce.net/user/lshabbot";
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
    }

    protected function assertPreConditions()
    {
        parent::assertPreConditions();
        $this->addParameter('id', '0');
    }

    /**
     * Issues an /com.x.webstore.v1/WebStoreMetadataProvision/GetAllWebStore
     * to get all sites for a Magento instance.
     *
     *  @test
     */
    public function testGetWebstoresAllEnabled()
    {
        //create an additional enabled webstore
        $this->loginAdminUser();
        $this->navigate('manage_stores');
        //create website
        //Data
        $websiteData = $this->loadDataSet('Website', 'generic_website');
        //Steps
        $this->storeHelper()->createStore($websiteData, 'website');
        //Verifying
        $this->assertMessagePresent('success', 'success_saved_website');
        $website = $websiteData['website_name'];

        //create store
        //Data
        $webstore = $this->loadDataSet('Store', 'generic_store');
        $webstore['website'] = $website;
        //Steps
        $this->storeHelper()->createStore($webstore, 'store');
        //Verifying
        $this->assertMessagePresent('success', 'success_saved_store');
        $store = $webstore['store_name'];

        //create view
        //Data
        $webview = $this->loadDataSet('StoreView', 'generic_store_view');
        $webview['store_name'] = $store;
        //Steps
        $this->storeHelper()->createStore($webview, 'store_view');
        //Verifying
        $this->assertMessagePresent('success', 'success_saved_store_view');

        /* issue the search */
        $this->_postSearch(
            $this->_baseUrl .  "/index.php/xfabric/endpoint",
            $this->_xfabricBearerToken
        );

        $webStoreNames = array(Xcom_Chronicle_Webstore_GetTest::DEFAULT_WEBSTORE, $store);

        $latestMessageId = $this->_getLatestFabricMessageId();

        $this->_findGetWebstoreMessage($latestMessageId, $webStoreNames);

        //Data
        $deleteWebsiteData = array('website_name' => $website);
        //Steps
        $this->storeHelper()->deleteStore($deleteWebsiteData);
    }


    /**
     * Issues an /com.x.webstore.v1/WebStoreMetadataProvision/GetAllWebStore
     * to get all sites for a Magento instance.
     *
     *  @test
     */
    public function testGetWebstoresDisabled()
    {
        //create an additional enabled webstore
        $this->loginAdminUser();
        $this->navigate('manage_stores');
        //create website
        //Data
        $websiteData = $this->loadDataSet('Website', 'generic_website');
        //Steps
        $this->storeHelper()->createStore($websiteData, 'website');
        //Verifying
        $this->assertMessagePresent('success', 'success_saved_website');
        $website = $websiteData['website_name'];

        //create store
        //Data
        $webstore = $this->loadDataSet('Store', 'generic_store');
        $webstore['website'] = $website;
        //Steps
        $this->storeHelper()->createStore($webstore, 'store');
        //Verifying
        $this->assertMessagePresent('success', 'success_saved_store');
        $store = $webstore['store_name'];

        //create view
        //Data
        $webview = $this->loadDataSet('StoreView', 'generic_store_view');
        $webview['store_name'] = $store;
        $webview['store_view_status'] = 'Disabled';
        //Steps
        $this->storeHelper()->createStore($webview, 'store_view');
        //Verifying
        $this->assertMessagePresent('success', 'success_saved_store_view');

        /* issue the search */
        $this->_postSearch(
            $this->_baseUrl .  "/index.php/xfabric/endpoint",
            $this->_xfabricBearerToken
        );

        $webStoreNames = array(Xcom_Chronicle_Webstore_GetTest::DEFAULT_WEBSTORE);

        $latestMessageId = $this->_getLatestFabricMessageId();

        $this->_findGetWebstoreMessage($latestMessageId, $webStoreNames);

        //Data
        $deleteWebsiteData = array('website_name' => $website);
        //Steps
        $this->storeHelper()->deleteStore($deleteWebsiteData);
    }

    protected function _findGetWebstoreMessage($startMessageId, array $webStoreNames)
    {
        $found = false;
        $msgId = $startMessageId;
        $retries = 0;

        while (!$found && $retries < 10) {
            $latestMsgId = $this->_getLatestFabricMessageId();

            $receivedMsgs = $this->_getFabricMessages($msgId);
            for ($c = 0; $msgId <= $latestMsgId && !$found; $c++, $msgId++) {
                $found = $this->_verifyGetWebstoreMessage($receivedMsgs[$c], $webStoreNames);
            }

            if (!found) {
                sleep(2);
            }
            $retries++;
        }

        if (!found) {
            $this->fail("Could not find /com.x.webstore.v1/WebStoreMetadataProvision/* message");
        }
    }

    protected function _verifyGetWebstoreMessage($msg, array $webStoreNames)
    {
        $topic = $msg->{'topic'};

        if (strcmp($topic, '/com.x.webstore.v1/WebStoreMetadataProvision/GetAllWebStoreSucceeded') == 0) {
            return $this->_verifyGetWebstoreSuccessMessage($msg, $webStoreNames);
        }
        else if (strcmp($topic, '/com.x.webstore.v1/WebStoreMetadataProvision/GetAllWebStoreFailed') == 0) {
            return $this->_verifyGetWebstoreFailedMessage($msg);
        }
        else{
            return false;
        }
    }

    protected function _verifyGetWebstoreSuccessMessage($msg, array $webStoreNames) {
        $stores = $msg->{'message'}->{'stores'}->{'com.x.webstore.v1.WebStore'};

        $c = 0;
        foreach ($stores as $store) {
            $storeName = $store['webStoreName'];
            $expectedName = $webStoreNames[$c++];
            $this->assertEquals($expectedName, $storeName , "Webstore Name was not expected");
        }
        return true;
    }

    protected function _verifyGetWebstoreFailedMessage($msg) {
       return true;
    }

    protected function _postSearch($fabricURL, $authorizationToken)
    {
        $c = curl_init($fabricURL . "/com.x.webstore.v1/WebStoreMetadataProvision/GetAllWebStore");
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
            "X-XC-SCHEMA-VERSION: 1.0.0",
            "X-XC-SCHEMA-URI: https://api.x.com/ocl/com.x.webstore.v1/WebStoreMetadataProvision/GetAllWebStore/1.0.0",
            "Content-Type: " . $contentType
        ));

        $body = new Xcom_Chronicle_Model_Message_Webstore_Get_Inbound();
        $body = $body->process(new Varien_Object(array()))->getBody();
        curl_setopt($c, CURLOPT_POSTFIELDS, $body);

        $data = curl_exec($c);

        if (curl_errno($c)) {
            $this->fail("Received error code after executing cURL: " . curl_errno($c));
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
            $this->fail('Curl error: ' . curl_error($c));
        }

        $responseCode = curl_getinfo($c, CURLINFO_HTTP_CODE);
        $this->assertEquals($expectedResponse, $responseCode, "Response code did not match expected.");

        return $data;
    }


}