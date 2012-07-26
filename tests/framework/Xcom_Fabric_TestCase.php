<?php
/**
 * Base class for integration tests
 */
class Xcom_Fabric_TestCase extends Xcom_Database_TestCase
{
    protected $_xfabricBearerToken;

    protected $_fabricURL;

    protected $_encoder;

    public function setUp()
    {
        $this->_xfabricBearerToken = Mage::getModel('xcom_xfabric/authorization')
            ->load()
            ->getFabricData(Xcom_Xfabric_Model_Authorization::TOKEN);
        $this->_fabricURL = Mage::getBaseUrl(Mage_Core_Model_Store::URL_TYPE_WEB) . '/tests/integration/index.php/xfabric/endpoint/';
        $this->_encoder = Mage::getModel('xcom_xfabric/encoder_avro');
        parent::setUp();
    }

    protected function _sendResponse($topic, $version, $body, $pseudonym = null, $additionalHeaders = array())
    {
        $c = curl_init($this->_fabricURL . $topic);
        curl_setopt($c, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($c, CURLOPT_POST, 1);
        curl_setopt($c, CURLOPT_SSL_VERIFYHOST, 0);
        curl_setopt($c, CURLOPT_SSL_VERIFYPEER, 0);
        curl_setopt($c, CURLOPT_FOLLOWLOCATION, 1);

        $contentType = "application/json";
        if ($this->_avroEncoding == "binary") {
            $contentType = "avro/binary";
        }
        $schemaUri = Mage::getStoreConfig('xfabric/connection_settings/ontology_server_uri') . $topic . "/" . $version;
        $headers = array(
            "Authorization: " . $this->_xfabricBearerToken,
            "X-XC-SCHEMA-VERSION: " . $version,
            "X-XC-SCHEMA-URI: " . $schemaUri,
            "Content-Type: " . $contentType
        );

        $headers = array_merge($headers, $additionalHeaders);

        if ($pseudonym !== null) {
            $headers[] = Xcom_Xfabric_Model_Message::PUBLISHER_PSEUDONYM_HEADER . ': ' . $pseudonym;
        }
        curl_setopt($c, CURLOPT_HTTPHEADER, $headers);

        $schemaOptions = array(
            'schema_uri' => $schemaUri
        );
        $schema = Mage::getModel('xcom_xfabric/schema', $schemaOptions);
        $body = $this->_encode($body, $schema->getRawSchema());
        curl_setopt($c, CURLOPT_POSTFIELDS, $body);
        $data = curl_exec($c);

        if (curl_errno($c)) {
            $this->fail('Curl error: ' . curl_error($c));
        }

        $responseCode = curl_getinfo($c, CURLINFO_HTTP_CODE);
        $this->assertEquals(200, $responseCode, "Response code did not match expected.");

        return $data;
    }

    protected function _encode($body, $schema)
    {
        return $this->_encoder->encodeText($body, $schema);
    }

    /**
     * Retrive latest message from the subscriber.
     *
     * @return array
     */
    protected function _getSubscriberLatestMessage()
    {
        return $this->_getSubscriberMessages($this->_getSubscriberUserUrl() . '/messages/latest');
    }

    /**
     * Retrive all messages by URL from the subscriber.
     *
     * @return array
     */
    protected function _getSubscriberMessages($url = null)
    {
        if (is_null($url)) {
            $url = $this->_getSubscriberUserUrl() . '/messages';
        }

        return json_decode(file_get_contents($url));
    }

    /**
     * Retrive next message.
     * //TODO: now it works roughly, because we should retrive not only latest message, but all messages.
     *
     * @return array
     */
    protected function _getNextMessage($previousMessageId, $timeout = 10, $pause = 2)
    {
        $endTime = time() + $timeout;
        while (time() < $endTime) {
            $lastMessage = $this->_getSubscriberLatestMessage();
            if ($lastMessage->id > $previousMessageId) {
                return $lastMessage;
            }
            sleep($pause);
        }

        return false;
    }

    /**
     * Get subscriber URL from PHPUnit XML config.
     *
     * @return string
     */
    protected function _getSubscriberUserUrl()
    {
        $url = getenv('XCOM_TEST_SUBSCRIBER_USER_URL');
        if (!$url) {
            $this->fail("Subscriber URL isn't set. Please add it to configuration file.");
        }

        return $url;
    }
}
