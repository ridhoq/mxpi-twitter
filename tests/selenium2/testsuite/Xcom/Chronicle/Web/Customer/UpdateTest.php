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
 * @category    tests
 * @package     selenium
 * @subpackage  tests
 * @author      Magento Core Team <core@magentocommerce.com>
 * @copyright   Copyright (c) 2010 Magento Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/**
 * Update Customer
 *
 * @package     selenium
 * @subpackage  tests
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

use Xcom_Chronicle_XMessages_Customer as Customer;

class Xcom_Chronicle_Web_Customer_UpdateTest extends Xcom_Chronicle_TestCase
{

    protected $_customerDataComplete;
    /**
     * <p>Preconditions:</p>
     * <p>Log in to Backend.</p>
     */
    public function setUpBeforeTests()
    {
        $this->loginAdminUser();
    }

    protected function assertPreConditions()
    {
        $this->addParameter('id', '0');
    }

    /**
     * Creates a customer to update before every test
     */
    public function setUpBeforeEachTest()
    {
        $this->_customerDataComplete = $this->_createCustomer();
    }

    /**
     *  Update Customer
     *  Preconditions: Create a customer
     *
     *  @dataProvider updateCustomerDataProvider
     *  @test
     */
    public function updateCustomer($updateCustomerData, $expectedMsg)
    {
        $this->_updateCustomer($updateCustomerData);
        $msgs = $this->_get2dXMessages();
        $this->verifyXMessage($expectedMsg, $msgs);
    }

    public function updateCustomerDataProvider()
    {
        $updateCustomerFirstName = array("first_name" => "updated_first_name_" . $this->generate('string', 5, ':lower:'));
        $updateCustomerMiddleName = array("middle_name" => "updated_middle_name_" . $this->generate('string', 5, ':lower:'));
        $updateCustomerLastName = array("last_name" => "updated_last_name_" . $this->generate('string', 5, ':lower:'));
        $updateCustomerPrefix = array("prefix" => "updated_prefix_" . $this->generate('string', 5, ':lower:'));
        $updateCustomerSuffix = array("suffix" => "updated_suffix_" . $this->generate('string', 5, ':lower:'));
        $updateCustomerEmail = array("email" => "updated_email_" . $this->generate('string', 5, ':lower:') . "@domain.com");
        $updateCustomerGender = array("gender" => null);
        $updateCustomerDateOfBirth = array("date_of_birth" => rand(1, 12) . "/" . rand(1, 28) . "/" . rand(1970, 2050));
        $generatedMsg = array(0 => array("topic" => Customer::CUSTOMER_UPDATED));


        return array
        (
            "Update Customer First Name" => array($updateCustomerFirstName, $generatedMsg),
            "Update Customer Middle Name" => array($updateCustomerMiddleName, $generatedMsg),
            "Update Customer Last Name" => array($updateCustomerLastName, $generatedMsg),
            "Update Customer Prefix" => array($updateCustomerPrefix, $generatedMsg),
            "Update Customer Suffix" => array($updateCustomerSuffix, $generatedMsg),
            "Update Customer Email" => array($updateCustomerEmail, $generatedMsg),
            "Update Customer Gender" => array($updateCustomerGender, $generatedMsg),
            "Update Customer Date of Birth" => array($updateCustomerDateOfBirth, $generatedMsg)
        );
    }
    /*
     * Helper method that updates customer data (only 'Account Information')
     */
    protected function _updateCustomer($updateCustomerData)
    {
        $customerData = $this->_customerDataComplete['customerData'];
        $this->navigate('manage_customers');
        $this->customerHelper()->openCustomer($customerData["email"]);
        $this->customerHelper()->openTab('account_information');
        $this->fillForm($updateCustomerData);
        $this->saveForm('save_customer');
        $this->assertMessagePresent('success', 'success_saved_customer');
    }

    /**
     *  Update Customer Address
     *  Preconditions: Create a customer
     *
     *  @dataProvider updateCustomerAddressDataProvider
     *  @test
     */
    public function updateCustomerAddress($updateAddressData, $expectedMsg)
    {
        $this->_updateCustomerAddress($updateAddressData);
        $msgs = $this->_get2dXMessages();
        $this->verifyXMessage($expectedMsg, $msgs);
    }

    public function updateCustomerAddressDataProvider()
    {
        $updateCustomerCompany =  array("company" =>" updated_company_" . $this->generate('string', 5, ':lower:'));
        $updateCustomerStreetAddress1 = array("street_address_line_1" =>$this->generate('string', 5, ':digit') . " updated_street_name_" . $this->generate('string', 5, ':lower:'));
        $updateCustomerStreetAddress2 = array("street_address_line_2" =>" updated_suite_" . $this->generate('string', 5, ':lower:') . " " . $this->generate('string', 4, ':digit:'));
        $updateCustomerCity = array("city" =>" updated_city_" . $this->generate('string', 5, ':lower:'));
        $updateCustomerCountry = array("country" => "Bangladesh");
        $updateCustomerZipCode = array("zip_code" => "updated_zip_code_" . $this->generate('string', 4, ':digit:'));
        $updateCustomerTelephone = array("zip_code" => "updated_zip_code_" . $this->generate('string', 4, ':digit:'));
        $generatedMsg = array(0 => array("topic" => Customer::CUSTOMER_UPDATED));

        return array
        (
          "Updated Customer Company" => array($updateCustomerCompany, $generatedMsg),
          "Updated Customer Street Address (Line 1)" => array($updateCustomerStreetAddress1, $generatedMsg),
          "Updated Customer Street Address (Line 2)" => array($updateCustomerStreetAddress2, $generatedMsg),
          "Updated Customer City" => array($updateCustomerCity, $generatedMsg),
          "Updated Customer Country" => array($updateCustomerCountry, $generatedMsg),
          "Updated Customer Zip Code" => array($updateCustomerZipCode, $generatedMsg),
          "Updated Customer Telephone" => array($updateCustomerTelephone, $generatedMsg),
        );
    }

    /*
     * Helper method that updates customer address data (only 'Addresses')
     */
    protected function _updateCustomerAddress($updateCustomerAddressData)
    {
        $customerData = $this->_customerDataComplete['customerData'];
        $customerAddressData = $this->_customerDataComplete['customerAddressData'];
        $this->navigate('manage_customers');
        $this->customerHelper()->openCustomer($customerData["email"]);
        $this->customerHelper()->openTab('addresses');
        $this->customerHelper()->isAddressPresent($customerAddressData);
        $this->fillForm($updateCustomerAddressData);
        $this->saveForm('save_customer');
        $this->assertMessagePresent('success', 'success_saved_customer');
    }
}