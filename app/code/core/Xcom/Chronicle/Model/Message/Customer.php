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

class Xcom_Chronicle_Model_Message_Customer extends Varien_Object
{
    /**
     * @param Mage_Customer_Model_Customer $customer
     */
    public function __construct(Mage_Customer_Model_Customer $customer)
    {
        $this->setData($this->_createCustomer($customer));
    }

    /**
     * @param Mage_Customer_Model_Customer $customer
     */
    protected function _createCustomer(Mage_Customer_Model_Customer $customer)
    {
        $billingAddress = $this->_getAddressWithEntityId($customer, $customer->getDefaultBilling());

        $primaryPhone = ($billingAddress) ? $this->_createPhoneNumber($billingAddress) : null;
        $company = ($billingAddress) ? $billingAddress->getCompany() : null;

        if (!isset($primaryPhone) || !isset($company)) {
            // prefer using the billing address for primary phone and company but
            // if they aren't set, then use any address possible

            $addresses = $customer->getAddresses();
            if (!empty($addresses)) {
                foreach ($addresses as $address) {
                    if (!isset($primaryPhone)) {
                        $primaryPhone = $this->_createPhoneNumber($address);
                    }

                    if (!isset($company)) {
                        $company = $address->getCompany();
                    }
                }
            }
        }

        $data = array(
            'fullName'      => Mage::helper('xcom_chronicle')->createName($customer),
            'addresses' => $this->_getAddresses($customer),
            'primaryPhone' => $primaryPhone,
            'email' => $this->_createEmailAddress($customer),
            'gender' => $this->_createGender($customer),
            'dateOfBirth' => $this->_createBirthday($customer),
            'company' => $company,
            'dateCreated' => date('c', strtotime($customer->getCreatedAt())),
            'lastModified' => date('c', strtotime($customer->getUpdatedAt())),
            'sourceIds' => null,
            'doNotCall' => null,
            'emailOptOut' => null,
            'id'    => Mage::helper('xcom_chronicle')->createEntityId($customer->getEntityId()),
        );

        return $data;
    }

    /**
     * @param Mage_Customer_Model_Customer $customer
     * @return array|null
     */
    protected function _getAddresses(Mage_Customer_Model_Customer $customer)
    {
        $addrData = array();
        $addresses = $customer->getAddresses();
        if (!empty($addresses)) {
            foreach ($addresses as $address) {
                $tags = null;
                if ($customer->getDefaultBilling() == $address->getEntityId()) {
                    $tags = array(Xcom_Chronicle_Helper_Data::ADDRESS_TAG_BILLING);
                }
                $addrData[] = Mage::helper('xcom_chronicle')->createAddress($address, $tags);
            }
        }

        return $addrData;
    }

    /**
     * @param Mage_Customer_Model_Customer $customer
     * @param string $id
     * @return string|null
     */
    protected function _getAddressWithEntityId(Mage_Customer_Model_Customer $customer, $id)
    {
        if (isset($id)) {
            $addresses = $customer->getAddresses();
            if (!empty($addresses)) {
                foreach ($addresses as $address) {
                    $entityId = $address->getEntityId();
                    if ($entityId == $id) {
                        return $address;
                    }
                }
            }
        }

        return null;
    }

    /**
     * @param Mage_Customer_Model_Customer $customer
     * @return string|null
     */
    protected function _createBirthday(Mage_Customer_Model_Customer $customer)
    {
        $dob = $customer->getDob();
        if ($dob) {
            return date('c', strtotime($dob));
        }

        return null;
    }

    /**
     * @param Mage_Customer_Model_Customer $customer
     * @return string|null
     */
    protected function _createGender(Mage_Customer_Model_Customer $customer)
    {
        $gender = $customer->getGender();
        if ($gender == 1) {
            return 'MALE';
        } else if ($gender == 2) {
            return 'FEMALE';
        }

        return null;
    }

    /**
     * @param Mage_Customer_Model_Customer $customer
     * @return array
     */
    protected function _createEmailAddress(Mage_Customer_Model_Customer $customer)
    {
        $data = array(
            'emailAddress'  => $customer->getEmail(),
            'extension'     => null
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
        if (!isset($primaryPhone)) {
            return null;
        }

        $data = array(
            'number'    => $primaryPhone,
            'type'      => 'UNKNOWN'
        );

        return $data;
    }
}
