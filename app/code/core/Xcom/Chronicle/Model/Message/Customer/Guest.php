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

class Xcom_Chronicle_Model_Message_Customer_Guest extends Varien_Object
{
    /**
     * @param Mage_Sales_Model_Order $order
     */
    public function __construct(Mage_Sales_Model_Order $order)
    {
        $this->setData($this->_createCustomer($order));
    }

    protected function _createCustomer(Mage_Sales_Model_Order $order)
    { 
        $addresses = array();
        $primaryPhone = null;
        $company = null;
        if ($order->getBillingAddress()) {
            $addresses[] = Mage::helper('xcom_chronicle')->createAddress($order->getBillingAddress(), array(Xcom_Chronicle_Helper_Data::ADDRESS_TAG_BILLING));
            $company = $order->getBillingAddress()->getCompany();
            $primaryPhone = $order->getBillingAddress()->getTelephone();
        }
        if ($order->getShippingAddress()) {
            $addresses[] = Mage::helper('xcom_chronicle')->createAddress($order->getShippingAddress(), array(Xcom_Chronicle_Helper_Data::ADDRESS_TAG_SHIPPING));
        }

        $data = array(
            'id'    => Mage::helper('xcom_chronicle')->createEntityId('guest' . $order->getRealOrderId()),
            'fullName'      => $this->_createCustomerName($order),
            'addresses' => $addresses,
            'primaryPhone' => array(
                'number' => $primaryPhone,
                'type' => 'UNKNOWN',
            ),
            'email' => array(
                    'emailAddress'  => $order->getCustomerEmail(),
                    'extension'     => null
                ),
            'gender' => null,
            'dateOfBirth' => null,
            'company' => $company,
            'dateCreated' => date('c', strtotime($order->getCreatedAt())),
            'lastModified' => date('c', strtotime($order->getUpdatedAt())),
            'sourceIds' => null,
            'emailOptOut' => null,
            'doNotCall' => null,
        );

        return $data;
    }

    protected function _createCustomerName(Mage_Sales_Model_Order $order)
    {
        $data = array(
            'firstName'     => $order->getCustomerFirstname(),
            'middleName'    => strlen($order->getCustomerMiddlename()) > 0 ? $order->getCustomerMiddlename() : null,
            'lastName'      => $order->getCustomerLastname(),
            'prefix'        => strlen($order->getCustomerPrefix()) > 0 ? $order->getCustomerPrefix() : null,
            'suffix'        => strlen($order->getCustomerSuffix()) > 0 ? $order->getCustomerSuffix() : null
        );

        return $data;
    }

    /**
     * @param Mage_Customer_Model_Address $address
     * @return array|null
     */
    protected function _createPhoneNumber(Mage_Customer_Model_Address $address)
    {
        $primaryPhone = $address->getTelephone();

        $data = array(
            'number'    => $primaryPhone,
            'type'      => 'UNKNOWN'
        );

        return $data;
    }
}
