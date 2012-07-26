<?php
/**
 * Base class for integration tests
 */
class Xcom_Messaging_TestCase extends Xcom_Database_TestCase
{
    protected $_testSubscriberBaseURL;
    protected $_xfabricBearerToken;
    protected $_baseUrl;
    protected $_avroEncoding;
    protected static $_lastXMessageId;

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
        $test = $this->_executeCurl($finalURL);
        $msgs = json_decode($test);
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
}
