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

/**
 * Stub Message Model
 *
 * @category   Xcom
 * @package    Xcom_Stub
 */

class Xcom_Stub_Model_Message extends Mage_Core_Model_Abstract
{
    public function _construct()
    {
        parent::_construct();
        $this->_init('xcom_stub/message');
    }

    /**
     * Retrieve message by topic and body.
     *
     * @param $request
     * @return Xcom_Xfabric_Model_Message_Response
     */
    public function receive($request)
    {
        $data = $this->_getResource()
            ->getMessageData($request->getTopic(), json_encode($request->getMessageData()));
        if (!$data) {
            return false;
        }

        /** @var $response Xcom_Xfabric_Model_Message_Response */
        $response = Mage::helper('xcom_xfabric')->getMessage($data['topic'], true);

        // Prepare headers
        $headers = array();
        foreach (explode("\n", $data['headers']) as $val) {
            $tmp = explode(":", $val);
            if (!empty($tmp[1])) {
                $headers[trim($tmp[0])] = trim($tmp[1]);
            }
        }
        // Sending response with the same correlationId is used in Initializer scenario
        if ($correlationId = $request->getCorrelationId()) {
            $response->setCorrelationId($correlationId);
            $headers = array_merge($headers, array(
                Xcom_Xfabric_Model_Message_Abstract::CORRELATION_ID_HEADER => $correlationId,
            ));
        }

        $headers = array_merge($headers, array(
            Xcom_Xfabric_Model_Message_Abstract::SCHEMA_URI_HEADER => $request->getSchemaUri(),
        ));
        $response->setHeaders($headers);

        $response->setBody($data['body']);
        $response->setEncoder('json');
        $response->decode();
        if (!$response->isProcessLater()) {
            $response->process();
        }
        $response->save();
        return $response;
    }

    /**
     * Get the schema URI.
     *
     * @param Xcom_Xfabric_Model_Message_Abstract $message
     * @return string
     */
    public function getSchemaUri($message)
    {
        $host = Mage::helper('xcom_xfabric')->getOntologyBaseUri();
        return $host . $message->getTopic() . '/' . $message->getSchemaVersion();
    }

    public function getRecipientHeaderArray()
    {
        $headers = array();
        $data = explode("\n", $this->getRecipientMessageHeader());
        foreach ($data as $item) {
            if (empty($item)) {
                continue;
            }
            $tmpArray = explode(":", $item);
            $headers[trim($tmpArray[0])] = trim($tmpArray[1]);
        }
        return $headers;
    }
}
