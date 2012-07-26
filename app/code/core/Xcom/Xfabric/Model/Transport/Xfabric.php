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
 * @package    Xcom_Xfabric
 * @copyright   Copyright (c) 2010 Magento Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/**
 * Xfabric Message Model
 *
 * @category   Xcom
 * @package    Xcom_Xfabric
 */
class Xcom_Xfabric_Model_Transport_Xfabric
    implements Xcom_Xfabric_Model_Transport_Interface
{
    /**
     * Xfabric URL
     *
     * @var string
     */
    protected $_url;

    /**
     * Xfabric port.
     *
     * @var integer
     */
    protected $_port;

    /**
     * Configuration.
     *
     * @var array
     */
    protected $_config = array();

    /**
     * Message object.
     *
     * @var Xcom_Xfabric_Model_Message
     */
    protected $_message;


    /**
     * Initialization step.
     * Required config values:
     *  - host
     *
     * Optional config values:
     *  - port
     *  - @see Varien_Http_Adapter_Curl::_applyConfig() method
     *
     * @throws Xcom_Xfabric_Exception
     * @param array $config
     * @return Xcom_Xfabric_Model_Transport_Interface
     */
    public function __construct(array $config = array())
    {
        if (empty($config['url'])) {
            throw Mage::exception('Xcom_Xfabric', Mage::helper('xcom_xfabric')->__('Empty URL specified'));
        } else {
            $this->_url = (string)$config['url'];
        }

        $this->_config = $config;
        return $this;
    }

    /**
     * Returns host.
     *
     * @return string
     */
    public function getUrl()
    {
        return $this->_url;
    }

    /**
     * This method sends a post message via curl adapter.
     * @deprecated
     * @return Xcom_Xfabric_Model_Message_Response|boolean
     */
    public function send()
    {
        $headers        = $this->getMessage()->getCurlHeaders();
        $body           = $this->getMessage()->getBody();
        $topic          = $this->getMessage()->getTopic();
        $url            = $this->prepareUri($topic);

        Mage::getSingleton('xcom_xfabric/debug')
            ->start('Send Request to '
                . $url->getUri(),
            $topic,
            serialize($headers),
            json_encode($this->getMessage()->getMessageData())
        );

        $adapter = $this->_getAdapter();
        $adapter->setConfig($this->_config);
        $adapter->write(Zend_Http_Client::POST, $url, '1.1', $headers, $body);
        $result = $adapter->read();
        $error = '';
        $httpCode = $adapter->getInfo(CURLINFO_HTTP_CODE);

        if ($adapter->getErrno()) {
            $error = $adapter->getError();
        }

        if (!$result || $error) {
            Mage::getSingleton('xcom_xfabric/debug')
                ->stop('Unable to complete the request.', $topic, $result, 'Unable to complete the request. ' . $error);

            switch ($httpCode) {
                case '401': $errorText = 'Request is unauthorized. '; break;
                case '403': $errorText = 'Request is forbidden. '; break;
                case '404': $errorText = 'Not Found. '; break;
                case '413': $errorText = 'Message is too large'; break;
                default: $errorText = ''; break;

            }

            throw Mage::exception('Xcom_Xfabric', Mage::helper('xcom_xfabric')
                ->__('Unable to complete the request. ') . $errorText . $error);
        }

        $zendHttpCode = $this->_getHttpCode($result);
        $zendHttpMessage = $this->_getHttpMessage($result);
        $zendHttpBody = $this->_getHttpBody($result);

        if ($zendHttpCode != '200') {
            Mage::getSingleton('xcom_xfabric/debug')->stop(
                'Unable to complete the request.',
                $topic, $result, $zendHttpMessage . ' ' . $zendHttpBody);

            throw Mage::exception('Xcom_Xfabric', $zendHttpMessage . ' ' . $zendHttpBody, $zendHttpCode);
        }

        if ($this->getMessage()->isWaitResponse() && !$error) {
            $response = $this->_getResponseMessage($this->getMessage()->getCorrelationId());
            if (!$response) {
                Mage::getSingleton('xcom_xfabric/debug')
                    ->stop('Response is not received', $topic, $result, 'No Errors');
                throw Mage::exception('Xcom_Xfabric',
                    Mage::helper('xcom_xfabric')->__('Unable to complete the request. Please refer to the User Guide ' .
                    'to verify your settings and try again. If the error persists, contact your administrator.')
                );
            }
            return $response;
        }
        Mage::getSingleton('xcom_xfabric/debug')->stop('No Response is been waiting', $topic, $result, $error);
        return true;
    }

    protected function _getHttpCode($httpText)
    {
        return Zend_Http_Response::extractCode($httpText);
    }

    protected function _getHttpMessage($httpText)
    {
        return Zend_Http_Response::extractMessage($httpText);
    }

    protected function _getHttpBody($httpText)
    {
        return Zend_Http_Response::extractBody($httpText);
    }

    public function sendMessage(Xcom_Xfabric_Model_Message $message, array $options = array())
    {
        $url = $this->prepareUri($message->getTopic());
        Mage::getSingleton('xcom_xfabric/debug')->start(
            'Send Request to ' . $url->getUri(),
            $message->getTopic(),
            serialize($message->getHeaders()),
            isset($options['message_data']) ? $options['message_data'] : ''
        );

        $adapter = $this->_getAdapter();
        $adapter->setConfig($this->_config);
        $adapter->write(Zend_Http_Client::POST, $url, '1.1',
            $this->_prepareHeaders($message->getHeaders()), $message->getBody());
        $result = $adapter->read();

        $error = '';
        $httpCode = $adapter->getInfo(CURLINFO_HTTP_CODE);

        if ($adapter->getErrno()) {
            $error = $adapter->getError();
        }

        if (!$result || $error) {
            Mage::getSingleton('xcom_xfabric/debug')
                ->stop('Unable to complete the request.', $message->getTopic(), $result, 'Unable to complete the request. ' . $error);

            switch ($httpCode) {
                case '400': $errorText = 'Bad request. '; break;
                case '401': $errorText = 'Request is unauthorized. '; break;
                case '403': $errorText = 'Request is forbidden. '; break;
                case '404': $errorText = 'Not Found. '; break;
                case '413': $errorText = 'Message is too large'; break;
                default: $errorText = ''; break;

            }

            throw Mage::exception('Xcom_Xfabric', Mage::helper('xcom_xfabric')
                ->__('Unable to complete the request. ') . $errorText . $error);
        }

        $zendHttpCode = Zend_Http_Response::extractCode($result);
        $zendHttpMessage = Zend_Http_Response::extractMessage($result);
        $zendHttpBody = Zend_Http_Response::extractBody($result);

        if ($zendHttpCode != '200') {
            Mage::getSingleton('xcom_xfabric/debug')->stop(
                'Unable to complete the request.',
                $message->getTopic(), $result, $zendHttpMessage . ' ' . $zendHttpBody);

            throw Mage::exception('Xcom_Xfabric', $zendHttpMessage . ' ' . $zendHttpBody, $zendHttpCode);
        }

        if (!empty($options['synchronous']) && !$error) {
            $response = $this->_getResponseMessage($message->getCorrelationId());
            if (!$response) {
                Mage::getSingleton('xcom_xfabric/debug')
                    ->stop('Response is not received', $message->getTopic(), $result, 'No Errors');
                throw Mage::exception('Xcom_Xfabric',
                    Mage::helper('xcom_xfabric')->__('Unable to complete the request. Please refer to the User Guide ' .
                        'to verify your settings and try again. If the error persists, contact your administrator.')
                );
            }
            return $response;
        }
        Mage::getSingleton('xcom_xfabric/debug')->stop('No Response is been waiting', $message->getTopic(), $result, $error);
        return true;
    }

    protected function _prepareHeaders($headers)
    {
        $result = array();
        foreach ($headers as $headerName => $headerValue) {
            $result[] = sprintf('%s: %s', $headerName, $headerValue);
        }
        return $result;
    }

    protected function _getResponseMessage($correlationId)
    {
        $startTime = time();
        $response = null;
        $counter = 0;
        while (true) {
            $timeSpent = time() - $startTime;
            if ($timeSpent > 30) {
                break;
            }
            $counter++;
            /** @var $collection Xcom_Xfabric_Model_Resource_Message_Response_Collection */
            $collection = Mage::getResourceModel('xcom_xfabric/message_response_collection')
                ->addFieldToFilter('correlation_id', $correlationId)
                ->addFieldToFilter('direction', Xcom_Xfabric_Model_Message::DIRECTION_INBOUND);
                ;
            if (count($collection) > 0) {
                break;
            }

            usleep(1000000);
        }

        if (count($collection) > 0) {
            $response = $collection->getFirstItem();
            Mage::getSingleton('xcom_xfabric/debug')
                ->stop('Receive Response', $response['topic'], $response['headers'],
                json_encode($response['body']));

            $messageOptions = array(
                'body' => $response['body'],
                'headers' => $response['headers'],
                'topic' => $response['topic'],
                'db_adapter' => Mage::getModel('xcom_xfabric/message_response'),
                'direction' => Xcom_Xfabric_Model_Message::DIRECTION_INBOUND,
                'status' => Xcom_Xfabric_Model_Message::MESSAGE_STATUS_NEW
            );
            /** @var $message Xcom_Xfabric_Model_Message */
            $message = Mage::getModel('xcom_xfabric/message', $messageOptions);
            return $message;
        }
        return false;
    }

    /**
     * @return Varien_Http_Adapter_Curl
     */
    protected function _getAdapter()
    {
        return new Varien_Http_Adapter_Curl();
    }

    /**
     * Prepare valid uri.
     *
     * @param string $path
     * @return Zend_Uri_Http
     */
    public function prepareUri($path)
    {
        $uri = trim($this->getUrl(), '/') . '/' . ltrim($path, '/');
        $uriHttp = Zend_Uri_Http::fromString($uri);
        return $uriHttp;
    }

    /**
     * Set message object.
     * @deprecated
     * @param Xcom_Xfabric_Model_Message_Abstract $message
     * @return Xcom_Xfabric_Model_Transport_Xfabric
     */
    public function setMessage(Xcom_Xfabric_Model_Message_Abstract $message)
    {
        $this->_message = $message;
        return $this;
    }

    /**
     * Retrieve message object.
     * @deprecated
     * @throws Xcom_Xfabric_Exception
     * @return Xcom_Xfabric_Model_Message
     */
    public function getMessage()
    {
        if (!is_object($this->_message)) {
            throw Mage::exception('Xcom_Xfabric', 'Message object is not defined');
        }

        return $this->_message;
    }
}
