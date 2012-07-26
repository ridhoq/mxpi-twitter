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

class Xcom_Chronicle_Model_Message_Order_Nonsensitive extends Xcom_Chronicle_Model_Message_Order
{

    /**
     * Need to hide the following:
     *
     * 	 CustomerInfo           customer;
     *   com.x.core.v1.Address  billingAddress;
     *   array<PaymentInfo>     paymentMethods;
     *   ShipTo                 destination;
     *   union{null,string}     purchaseOrder = null;
     * */

    /**
     * 'type' in params can be:
     * - simple
     * - regular
     * @param array params
     */
    public function __construct($params)
    {
        parent::__construct($params);

//
//        $order = $params['order'];
//        if (null === $order) {
//            Mage::log('NonSensitiveOrder no order fed in', null, 'debug.log', true);
//            return;
//        }
//        $orderType = $params['type'];
//        if (!isset($orderType)) {
//            $orderType = 'simple';
//        }
//        $this->_setupIfChannelOrder($order);
//        // cache the proper order number
//        $this->_orderNumber = $this->_getOrderNumber($order);
//        $orderData = $this->_createOrder($order, $orderType);

        $orderData = $this->getData();
        unset($orderData['customer']);
        unset($orderData['billingAddress']);
        unset($orderData['paymentMethods']);
        unset($orderData['destination']);
        unset($orderData['purchaseOrder']);

        $this->setData($orderData);
    }

}
