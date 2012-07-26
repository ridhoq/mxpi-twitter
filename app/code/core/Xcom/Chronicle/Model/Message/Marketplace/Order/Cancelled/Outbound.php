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
 * @package     Xcom_Chronicle
 * @copyright   Copyright (c) 2010 Magento Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

class Xcom_Chronicle_Model_Message_Marketplace_Order_Cancelled_Outbound extends Xcom_Xfabric_Model_Message_Request
{
    protected function _construct()
    {
        parent::_construct();
        $this->_topic = 'com.x.ordermanagement.v2/ProcessSalesChannelOrder.MarketplaceOrder/MarketplaceOrderCancelled';
        $this->_schemaRecordName = 'MarketplaceOrderCancelled';
        $this->_schemaVersion  = "2.0.1";
    }

    /**
     * @param null|Varien_Object $dataObject
     * @return Xcom_Xfabric_Model_Message_Request
     */
    public function _prepareData(Varien_Object $dataObject = null)
    {
        $avroDataObject = Mage::getModel('xcom_chronicle/message_order',
            array('order' => $dataObject->getOrder(), 'type' => 'regular'));
        $data = array(
            'cancelledOrders' => array($avroDataObject->toArray())
        );
        $this->setMessageData($data);
        return parent::_prepareData($dataObject);
    }

}
