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
 * @package     Xcom_Mapping
 * @copyright   Copyright (c) 2010 Magento Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

class Xcom_Xfabric_Model_Observer
{
    const AVRO_BINARY_SIZE_THRESHOLD = 2097152; // 2 MB
    /**
     * Default page size for response collection.
     */
    const PAGE_SIZE = 100;

    public function processInstantMessages(Varien_Event_Observer $observer)
    {
        /* @deprecated Backward compatibility with Messaging Framework 0.0.1 */
        $message = $observer->getEvent()->getMessage();
        $responseMessage = Mage::helper('xcom_xfabric')->getMessage($message->getTopic(), true);
        $messageData = $message->getMessageData();
        if ($messageData === null) {
            $messageData = array($messageData);
        }
        if (!$responseMessage instanceof Xcom_Xfabric_Model_Message_Response) {
            return;
        }
        if (!$responseMessage->isProcessLater()) {
            $responseMessage
                ->setMessageId($message->getId())
                ->setBody($messageData)
                ->addData($messageData)
                ->setTopic($message->getTopic())
                ->setHeaders($message->getHeaders())
                ->process();
            $responseMessage->save();
        }
    }

    /**
     * Process postponed responses.
     *
     * @return Xcom_Xfabric_Model_Observer
     */
    public function proceedDelayedProcess()
    {
        $collection = Mage::getResourceModel('xcom_xfabric/message_response_collection')
            ->setPageSize(self::PAGE_SIZE)
            ->setOrder('created_at', Varien_Data_Collection::SORT_ORDER_DESC)
            ->addFieldToFilter('is_processed', 0);

        for ($i = 1; $i <= $collection->getLastPageNumber(); $i++) {
            $collection->setCurPage($i)
                ->addLimitPage()
                ->clear();

            foreach ($collection as $response) {
                try {
                    $headers = unserialize($response->getData('headers'));
                    if (!isset($headers['Content-Length']) || $headers['Content-Length'] > self::AVRO_BINARY_SIZE_THRESHOLD) {
                        // 'Content-Length' header is over threshold or not present (streaming)
                        // So we must increase memory limit for processing of message
                        ini_set('memory_limit', '1024M');
                    }
                    $body = unserialize($response->getData('body'));

                    $messageOptions = array(
                        'body' => $body,
                        'headers' => $headers,
                        'topic' => $response->getData('topic'),
                        'message_data' => $body,
                        'db_adapter' => Mage::getModel('xcom_xfabric/message_response')
                    );
                    $message = Mage::getModel('xcom_xfabric/message', $messageOptions);
                    /*topic related event*/
                    $eventName = 'response_message_process_postponed_' .
                        Mage::helper('xcom_xfabric')->getEventSuffix($message->getTopic());

                    Mage::dispatchEvent($eventName, array('message' => $message));


                    /* @deprecated backward compatibility with Messaging Framework 0.0.1 */
                    $inboundMessage = Mage::helper('xcom_xfabric')->getMessage($response->getData('topic'), true);
                    if (!$inboundMessage instanceof Xcom_Xfabric_Model_Message_Response) {
                        continue;
                    }
                    if ($inboundMessage->isProcessLater()) {
                        $inboundMessage->addData($response->getData());

                        $inboundMessage->setHeaders($headers);
                        $inboundMessage->setBody($body);

                        $inboundMessage->process();
                        $inboundMessage->save();
                    }
                    unset($inboundMessage);
                    unset($message);
                } catch (Exception $e) {
                    Mage::logException($e);
                    continue;
                }
            }
        }
        return $this;
    }
}
