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
 * @package     Xcom_Choreography
 * @copyright   Copyright (c) 2010 Magento Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

class Xcom_Choreography_Model_Message_MessageReceived_Inbound
{
    /**
     * Processes Inbound message. Called from Observer
     * @param Varien_Event_Observer $observer
     * @return Xcom_Choreography_Model_Message_MessageReceived_Inbound
     */
    public function process(Varien_Event_Observer $observer)
    {
        $message = $observer->getEvent()->getMessage();
        $correlationId = $message->getHeader(Xcom_Xfabric_Model_Message::CORRELATION_ID_HEADER);

        /** @var $collection Xcom_Xfabric_Model_Resource_Message_Response_Collection */
        $collection = Mage::getResourceModel('xcom_xfabric/message_response_collection')
            ->addFieldToFilter('correlation_id', $correlationId)
            ->addFieldToFilter('direction', Xcom_Xfabric_Model_Message::DIRECTION_INBOUND)
            ->getItems();
        $requestDbData = array_shift($collection);
        Mage::getModel('xcom_xfabric/message', array('db_adapter' => $requestDbData))
            ->setReceived();

        return $this;
    }
}

