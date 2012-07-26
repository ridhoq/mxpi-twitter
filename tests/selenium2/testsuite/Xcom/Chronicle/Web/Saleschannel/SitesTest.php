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

class Xcom_Chronicle_Model_Message_Saleschannel_Sites_Inbound extends Xcom_Xfabric_Model_Message_Request
{

    protected function _construct()
    {
        parent::_construct();
        $this->_topic = 'salesChannel/site/search';
        $this->_schemaRecordName = 'SearchSite';
        $this->_schemaFile = 'Xcom_Chronicle/ProductInformationManagementCapability.avpr';
        $this->_schemaVersion  = "1.0.0";
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
class Xcom_Chronicle_Web_Saleschannel_SitesTest extends Mage_Selenium_TestCase
{
    protected $_fabricUrl;

    protected $_xfabricBearerToken;

    protected $_baseUrl;

    protected $_avroEncoding;

    protected $_productSku;

    protected $_customer;

    protected $_subscriberTestBaseURL;

    /**
     * <p>Preconditions:</p>
     * <p>Setup x.fabric</p>
     */
    public function setUpBeforeTests()
    {
        //$this->_subscriberTestBaseURL = "http://testsub.magentosubscribertest.xcommerce.net";
        $this->_subscriberTestBaseURL = "http://lshabbot.magentosubscribertest.xcommerce.net";
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

        $config = json_decode($authorization);
        $this->_fabricUrl =  $config->{"fabricURL"};
        */

        /* read the config to setup the tokens and base urls */
        $this->_xfabricBearerToken = "Bearer YthK4vYFddjkVob56ReJcyizEbBm0N2N4mdL73dfBT8i5kfQy1da0JbPz9u/Zyjsc7LAqe0Z";
//        foreach ($config->{"authorizations"} as $auth) {
//            if ($auth->{"type"} == "XFABRIC") {
//                $this->_xfabricBearerToken = $auth->{"bearerToken"};
//            }
//        }

        //$this->_baseUrl = Mage::getBaseUrl(Mage_Core_Model_Store::URL_TYPE_WEB);
        $this->_baseUrl = 'http://xcom.loc/magento';

        $this->_avroEncoding = Mage::getConfig()->getNode("default/xfabric/connection_settings/encoding");
    }

    protected function assertPreConditions()
    {
        parent::assertPreConditions();
        $this->addParameter('id', '0');
    }


    /**
     * Issues an /salesChannel/site/search to get all sites for a Magento instance.
     *
     *  @test
     */
    public function testSearchSites()
    {
        $this->markTestIncomplete();
        /* issue the search */
        $this->_postSearch(
            $this->_baseUrl .  "/index.php/xfabric/endpoint",
            $this->_xfabricBearerToken
        );

        $latestMessageId = $this->_getLatestFabricMessageId();
        $productGetMsg = $this->_getFabricMessages($latestMessageId);

        $this->_verifyProductGetSuccessMsg($productGetMsg[0]);
    }


    protected function _verifyProductGetSuccessMsg($msg)
    {
        $topic = $msg->{'topic'};
        $this->assertEquals("/salesChannel/site/searchSucceeded", $topic, "Topic was not expected");
    }

    protected function _postSearch($fabricURL, $authorizationToken)
    {
        $c = curl_init($fabricURL . "/salesChannel/site/search");
        curl_setopt($c, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($c, CURLOPT_POST, 1);
        curl_setopt($c, CURLOPT_SSL_VERIFYHOST, 0);
        curl_setopt($c, CURLOPT_SSL_VERIFYPEER, 0);
        curl_setopt($c, CURLOPT_FOLLOWLOCATION, 1);

        $contentType = "application/json";
        if ($this->_avroEncoding == "binary") {
            $contentType = "avro/binary";
        }
        curl_setopt($c, CURLOPT_HTTPHEADER, array(
            "Authorization: " . $authorizationToken,
            "X-XC-SCHEMA-VERSION: 1.0.1",
            "X-XC-SCHEMA-URI: https://api.x.com/ocl/salesChannel/site/search/1.0.0",
            "Content-Type: " . $contentType
        ));

        $body = new Xcom_Chronicle_Model_Message_Saleschannel_Sites_Inbound();
        $body = $body->process(new Varien_Object(array()))->getBody();
        curl_setopt($c, CURLOPT_POSTFIELDS, $body);

        $data = curl_exec($c);

//        if (curl_errno($c)) {
//            $this->fail("Received error code after executing cURL: " . curl_errno($c));
//        }

        $responseCode = curl_getinfo($c, CURLINFO_HTTP_CODE);
        //$this->assertEquals(200, $responseCode);

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

//        if (curl_errno($c)) {
//            $this->fail("Received error code after executing cURL: " . curl_errno($c));
//        }

        $responseCode = curl_getinfo($c, CURLINFO_HTTP_CODE);
        // $this->assertEquals($expectedResponse, $responseCode, "Response code did not match expected.");

        return $data;
    }
}