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
 * @package     Xcom_Stub
 * @copyright   Copyright (c) 2010 Magento Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

class Xcom_Stub_Model_Transport_Stub implements Xcom_Xfabric_Model_Transport_Interface
{
    /**
     * Message object
     *
     * @var Xcom_Xfabric_Model_Message_Abstract
     */
    protected $_message;

    /**
     * Init.
     *
     * @param array $config
     * @return Xcom_Stub_Model_Transport_Stub
     */
    public function init(array $config = array())
    {
        return $this;
    }

    protected function _fakeBeforeSend()
    {
        $this->getMessage()->fakeBeforeSend();
        return $this;
    }

    /**
     * Returns response message from database.
     *
     * @throws Xcom_Stub_Exception
     * @return Xcom_Xfabric_Model_Message_Abstract
     */
    public function send()
    {
        $this->_fakeBeforeSend();

        $requestTopic = $this->getMessage()->getTopic();
        $requestHeaders = serialize($this->getMessage()->getHeaders());

        Mage::getSingleton('xcom_xfabric/debug')
            ->start('Send Request [STUB] ' . $requestTopic,
            $requestTopic, $requestHeaders, json_encode($this->getMessage()->getMessageData()));

        if ($this->getMessage()->isWaitResponse()) {
            try {
                $response = Mage::getModel('xcom_stub/message')->receive($this->getMessage());

                if (!$response) {
                    throw Mage::exception('Xcom_Stub', printf('Topic has not been defined for stub: %s', $requestTopic));
                }

                if (!is_null($response->getMessageId())) {
                    Mage::dispatchEvent('response_message_received', array('message' => $response));
                    Mage::getSingleton('xcom_xfabric/debug')
                        ->stop('Receive Response [STUB] ' . $requestTopic,
                        $response->getTopic(), $requestHeaders, serialize($response->getBody()));
                } else {
                    $response = Mage::helper('xcom_xfabric')->getMessage($requestTopic);
                    Mage::getSingleton('xcom_xfabric/debug')
                        ->stop('No Response was received [STUB] ' . $requestTopic,
                        $requestTopic, $requestHeaders, '');
                }
                return $response;

            } catch (Exception $e) {
                Mage::getSingleton('xcom_xfabric/debug')
                    ->stop('No Response was received [STUB] ' . $requestTopic,
                    $requestTopic, $requestHeaders, $e->getMessage());
            }
        } else {
            Mage::getSingleton('xcom_xfabric/debug')
                ->stop('No Response is been waiting [STUB] ' . $requestTopic, '', '',
                json_encode($this->getMessage()->getMessageData()));
        }
    }

    public function sendMessage(Xcom_Xfabric_Model_Message $message, array $options = array())
    {
        $requestTopic = $message->getTopic();
        $requestHeaders = serialize($message->getHeaders());

        Mage::getSingleton('xcom_xfabric/debug')
            ->start('Send Request [STUB] ' . $requestTopic,
            $requestTopic, $requestHeaders, json_encode($message->getMessageData()));

        if (isset($options['synchronous'])) {
            try {
                $response = Mage::getModel('xcom_stub/message')->receive($message);
                if ($response && !is_null($response->getMessageId())) {
                    Mage::dispatchEvent('response_message_received', array('message' => $response));
                    Mage::getSingleton('xcom_xfabric/debug')
                        ->stop('Receive Response [STUB] ' . $requestTopic,
                        $response->getTopic(), $requestHeaders, serialize($response->getBody()));
                } else {
                    //$response = Mage::helper('xcom_xfabric')->getMessage($requestTopic);
                    Mage::getSingleton('xcom_xfabric/debug')
                        ->stop('No Response was received [STUB] ' . $requestTopic,
                        $requestTopic, $requestHeaders, '');
                }
                return $response;

            } catch (Exception $e) {
                Mage::getSingleton('xcom_xfabric/debug')
                    ->stop('No Response was received [STUB] ' . $requestTopic,
                    $requestTopic, $requestHeaders, $e->getMessage());
            }
        }  else {
            Mage::getSingleton('xcom_xfabric/debug')
                ->stop('No Response is been waiting [STUB] ' . $requestTopic, '', '',
                json_encode($message->getMessageData()));
        }

    }


    /**
     * Set message object.
     *
     * @param Xcom_Xfabric_Model_Message_Abstract $message
     * @return Xcom_Stub_Model_Transport_Stub
     */
    public function setMessage(Xcom_Xfabric_Model_Message_Abstract $message)
    {
        $this->_message =  $message;
        return $this;
    }

    /**
     * Retrieve message object.
     *
     * @throws Xcom_Stub_Exception
     * @return Xcom_Xfabric_Model_Message_Abstract
     */
    public function getMessage()
    {
        if (!is_object($this->_message)) {
            throw Mage::exception('Xcom_Stub', "Message object is not defined");
        }
        return $this->_message;
    }
}
