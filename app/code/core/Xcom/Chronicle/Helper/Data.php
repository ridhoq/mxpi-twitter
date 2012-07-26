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
class Xcom_Chronicle_Helper_Data extends Mage_Core_Helper_Data
{
    /**
     * Create OCL Name record
     *
     * @param Mage_Sales_Model_Order_Address|Mage_Customer_Model_Customer $address
     * @return array
     */
    public function createName($address)
    {
        $data = array(
            'firstName'  => (string)$address->getFirstname(),
            'middleName' => $address->getMiddlename(), // optional
            'lastName'   => (string)$address->getLastname(),
            'prefix'     => $address->getPrefix(), // optional
            'suffix'     => $address->getSuffix(), // optional
        );

        return $data;
    }

    const ADDRESS_TAG_BILLING = 'BILLING';
    const ADDRESS_TAG_SHIPPING = 'SHIPPING';

    /**
     * Create OCL Address record
     *
     * @param Mage_Customer_Model_Address_Abstract|Mage_Sales_Model_Order_Address $address
     * @param string|null                          $billingEntityId
     * @return array
     */
    public function createAddress($address, $tags = null)
    {
        $region = $address->getRegion();

        $data = array(
            'street1'           => (string)$address->getStreet1(),
            'street2'           => (string)$address->getStreet2(),
            'street3'           => (string)$address->getStreet3(),
            'street4'           => (string)$address->getStreet4(),
            'city'              => (string)$address->getCity(),
            'county'            => null,
            'stateOrProvince'   => empty($region) ? null : $region,
            'postalCode'        => (string)$address->getPostcode(),
            'country'           => (string)Mage::getModel('directory/country')
                ->loadByCode($address->getCountryId())
                ->getIso3Code(),
            'tags'              => $tags
        );

        return $data;
    }

    /**
     * @param $amount float currency amount
     * @param $code string currency code
     * @return array
     */
    public function createCurrencyAmount($amount, $code)
    {
        return array(
            'amount' => (string)$amount,
            'code'   => (string)$code,
        );
    }

    /**
     * Returns value of 'namespace' field (e.g. used in EntityId record)
     *
     * @return string
     */
    public function getNamespace()
    {
        return (string)Mage::helper('xcom_xfabric')->getAuthModel()->getCapabilityId();
    }

    /**
     * Creates EntityId record
     *
     * @param string $id ID of the entity (e.g. customer)
     * @return array
     */
    public function createEntityId($id)
    {
        $data = array(
            'namespace' => $this->getNamespace(),
            'Id'        => (string)$id,
        );

        return $data;
    }
}
