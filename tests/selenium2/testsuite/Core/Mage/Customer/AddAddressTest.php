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
 * @copyright   Copyright (c) 2012 Magento Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/**
 * <p>Add address tests.</p>
 *
 * @package     selenium
 * @subpackage  tests
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class Core_Mage_Customer_AddAddressTest extends Mage_Selenium_TestCase
{
    protected static $_customerTitleParameter = '';

    /**
     * <p>Log in to Backend.</p>
     */
    public function setUpBeforeTests()
    {
        $this->loginAdminUser();
    }

    /**
     * <p>Preconditions:</p>
     * <p>Navigate to System -> Manage Customers</p>
     */
    protected function assertPreConditions()
    {
        $this->navigate('manage_customers');
        $this->addParameter('id', '0');
        $this->addParameter('customer_first_last_name', self::$_customerTitleParameter);
    }

    /**
     * <p>Create customer for add customer address tests</p>
     * @group preConditions
     * @return array
     * @test
     */
    public function createCustomerTest()
    {
        //Data
        $userData = $this->loadData('generic_customer_account', null, 'email');
        $searchData = $this->loadData('search_customer', array('email' => $userData['email']));
        self::$_customerTitleParameter = $userData['first_name'] . ' ' . $userData['last_name'];
        //Steps
        $this->customerHelper()->createCustomer($userData);
        //Verifying
        $this->assertMessagePresent('success', 'success_saved_customer');

        return $searchData;
    }

    /**
     * <p>Add address for customer. Fill in only required field.</p>
     * <p>Steps:</p>
     * <p>1. Search and open customer.</p>
     * <p>2. Open 'Addresses' tab.</p>
     * <p>3. Click 'Add New Address' button.</p>
     * <p>4. Fill in required fields.</p>
     * <p>5. Click  'Save Customer' button</p>
     * <p>Expected result:</p>
     * <p>Customer address is added. Customer info is saved.</p>
     * <p>Success Message is displayed</p>
     *
     * @param array $searchData
     *
     * @test
     * @depends createCustomerTest
     * @TestlinkId	TL-MAGE-3604
     */
    public function withRequiredFieldsOnly(array $searchData)
    {
        //Data

        $addressData = $this->loadData('generic_address');
        //Steps
        $this->customerHelper()->openCustomer($searchData);
        $this->customerHelper()->addAddress($addressData);
        $this->saveForm('save_customer');
        //Verifying
        $this->assertMessagePresent('success', 'success_saved_customer');
    }

    /**
     * Add Address for customer with one empty required field.
     * <p>Steps:</p>
     * <p>1. Search and open customer.</p>
     * <p>2. Open 'Addresses' tab.</p>
     * <p>3. Click 'Add New Address' button.</p>
     * <p>4. Fill in fields except one required.</p>
     * <p>5. Click  'Save Customer' button</p>
     * <p>Expected result:</p>
     * <p>Customer address isn't added. Customer info is not saved.</p>
     * <p>Error Message is displayed</p>
     *
     * @param string $emptyField
     * @param array $searchData
     *
     * @test
     * @depends createCustomerTest
     * @dataProvider withRequiredFieldsEmptyDataProvider
     * @TestlinkId TL-MAGE-3604
     * @group skip_due_to_bug
     */
    public function withRequiredFieldsEmpty($emptyField, $searchData)
    {
        //Data
        if ($emptyField != 'country') {
            $addressData = $this->loadData('generic_address', array($emptyField => ''));
        } else {
            $addressData = $this->loadData('generic_address', array($emptyField => '',
                                                                   'state'      => '%noValue%'));
        }
        //Steps
        $this->customerHelper()->openCustomer($searchData);
        $this->customerHelper()->addAddress($addressData);
        $this->saveForm('save_customer');
        //Verifying
        // Defining and adding %fieldXpath% for customer Uimap
        $fieldSet = $this->getUimapPage('admin', 'edit_customer')->findFieldset('edit_address');
        if ($emptyField != 'country' and $emptyField != 'state') {
            $fieldXpath = $fieldSet->findField($emptyField);
        } else {
            $fieldXpath = $fieldSet->findDropdown($emptyField);
        }
        if ($emptyField == 'street_address_line_1') {
            $fieldXpath .= "/ancestor::div[@class='multi-input']";
        }
        $this->addParameter('fieldXpath', $fieldXpath);

        $this->assertMessagePresent('validation', 'empty_required_field');
        $this->assertTrue($this->verifyMessagesCount(), $this->getParsedMessages());
    }

    public function withRequiredFieldsEmptyDataProvider()
    {
        return array(
            array('first_name'),
            array('last_name'),
            array('street_address_line_1'),
            array('city'),
            array('country'),
            array('state'), // Fails because of MAGE-1424 // Should be required only if country='United States'
            array('zip_code'),
            array('telephone')
        );
    }

    /**
     * <p>Add address for customer. Fill in all fields by using special characters(except the field "country").</p>
     * <p>Steps:</p>
     * <p>1. Search and open customer.</p>
     * <p>2. Open 'Addresses' tab.</p>
     * <p>3. Click 'Add New Address' button.</p>
     * <p>4. Fill in fields by long value alpha-numeric data except 'country' field.</p>
     * <p>5. Click  'Save Customer' button</p>
     * <p>Expected result:</p>
     * <p>Customer address is added. Customer info is saved.</p>
     * <p>Success Message is displayed.</p>
     *
     * @param array $searchData
     *
     * @test
     * @depends createCustomerTest
     * @TestlinkId	TL-MAGE-3605
     */
    public function withSpecialCharactersExceptCountry(array $searchData)
    {
        //Data
        $specialCharacters = array(
            'prefix'                => $this->generate('string', 32, ':punct:'),
            'first_name'            => $this->generate('string', 32, ':punct:'),
            'middle_name'           => $this->generate('string', 32, ':punct:'),
            'last_name'             => $this->generate('string', 32, ':punct:'),
            'suffix'                => $this->generate('string', 32, ':punct:'),
            'company'               => $this->generate('string', 32, ':punct:'),
            'street_address_line_1' => $this->generate('string', 32, ':punct:'),
            'street_address_line_2' => $this->generate('string', 32, ':punct:'),
            'city'                  => $this->generate('string', 32, ':punct:'),
            'country'               => 'Ukraine',
            'state'                 => '%noValue%',
            'region'                => $this->generate('string', 32, ':punct:'),
            'zip_code'              => $this->generate('string', 32, ':punct:'),
            'telephone'             => $this->generate('string', 32, ':punct:'),
            'fax'                   => $this->generate('string', 32, ':punct:')
        );
        $addressData = $this->loadData('generic_address', $specialCharacters);
        //Steps
        $this->customerHelper()->openCustomer($searchData);
        $this->customerHelper()->addAddress($addressData);
        $this->saveForm('save_customer');
        //Verifying #–1
        $this->assertMessagePresent('success', 'success_saved_customer');
        //Steps
        $this->customerHelper()->openCustomer($searchData);
        $this->openTab('addresses');
        //Verifying #–2 - Check saved values
        $addressNumber = $this->customerHelper()->isAddressPresent($addressData);
        $this->assertNotEquals(0, $addressNumber, 'The specified address is not present.');
    }

    /**
     * <p>Add address for customer. Fill in only required field. Use max long values for fields.</p>
     * <p>Steps:</p>
     * <p>1. Search and open customer.</p>
     * <p>2. Open 'Addresses' tab.</p>
     * <p>3. Click 'Add New Address' button.</p>
     * <p>4. Fill in fields by long value alpha-numeric data except 'country' field.</p>
     * <p>5. Click  'Save Customer' button</p>
     * <p>Expected result:</p>
     * <p>Customer address is added. Customer info is saved.</p>
     * <p>Success Message is displayed. Length of fields are 255 characters.</p>
     *
     * @param array $searchData
     *
     * @test
     * @depends createCustomerTest
     * @TestlinkId	TL-MAGE-3603
     */
    public function withLongValuesExceptCountry(array $searchData)
    {
        //Data
        $longValues = array(
            'prefix'                => $this->generate('string', 255, ':alnum:'),
            'first_name'            => $this->generate('string', 255, ':alnum:'),
            'middle_name'           => $this->generate('string', 255, ':alnum:'),
            'last_name'             => $this->generate('string', 255, ':alnum:'),
            'suffix'                => $this->generate('string', 255, ':alnum:'),
            'company'               => $this->generate('string', 255, ':alnum:'),
            'street_address_line_1' => $this->generate('string', 255, ':alnum:'),
            'street_address_line_2' => $this->generate('string', 255, ':alnum:'),
            'city'                  => $this->generate('string', 255, ':alnum:'),
            'country'               => 'Ukraine',
            'state'                 => '%noValue%',
            'region'                => $this->generate('string', 255, ':alnum:'),
            'zip_code'              => $this->generate('string', 255, ':alnum:'),
            'telephone'             => $this->generate('string', 255, ':alnum:'),
            'fax'                   => $this->generate('string', 255, ':alnum:')
        );
        $addressData = $this->loadData('generic_address', $longValues);
        //Steps
        $this->customerHelper()->openCustomer($searchData);
        $this->customerHelper()->addAddress($addressData);
        $this->saveForm('save_customer');
        //Verifying #–1
        $this->assertMessagePresent('success', 'success_saved_customer');
        //Steps
        $this->customerHelper()->openCustomer($searchData);
        $this->openTab('addresses');
        //Verifying #–2 - Check saved values
        $addressNumber = $this->customerHelper()->isAddressPresent($addressData);
        $this->assertNotEquals(0, $addressNumber, 'The specified address is not present.');
    }

    /**
     * <p>Add address for customer. Fill in only required field. Use this address as Default Billing.</p>
     * <p>Steps:</p>
     * <p>1. Search and open customer.</p>
     * <p>2. Open 'Addresses' tab.</p>
     * <p>3. Click 'Add New Address' button.</p>
     * <p>4. Fill in required fields.</p>
     * <p>5. Click  'Save Customer' button</p>
     * <p>Expected result:</p>
     * <p>Customer address is added. Customer info is saved.</p>
     * <p>Success Message is displayed</p>
     *
     * @param array $searchData
     *
     * @test
     * @depends createCustomerTest
     * @TestlinkId	TL-MAGE-3601
     */
    public function withDefaultBillingAddress(array $searchData)
    {
        //Data
        $addressData = $this->loadData('all_fields_address', array('default_shipping_address' => 'No'));
        //Steps
        // 1.Open customer
        $this->customerHelper()->openCustomer($searchData);
        $this->customerHelper()->addAddress($addressData);
        $this->saveForm('save_customer');
        //Verifying
        $this->assertMessagePresent('success', 'success_saved_customer');
        //Steps
        $this->customerHelper()->openCustomer($searchData);
        $this->openTab('addresses');
        //Verifying #–2 - Check saved values
        $addressNumber = $this->customerHelper()->isAddressPresent($addressData);
        $this->assertNotEquals(0, $addressNumber, 'The specified address is not present.');
    }

    /**
     * <p>Add address for customer. Fill in only required field. Use this address as Default Shipping.</p>
     * <p>Steps:</p>
     * <p>1. Search and open customer.</p>
     * <p>2. Open 'Addresses' tab.</p>
     * <p>3. Click 'Add New Address' button.</p>
     * <p>4. Fill in required fields.</p>
     * <p>5. Click  'Save Customer' button</p>
     * <p>Expected result:</p>
     * <p>Customer address is added. Customer info is saved.</p>
     * <p>Success Message is displayed</p>
     *
     * @param array $searchData
     *
     * @test
     * @depends createCustomerTest
     * @TestlinkId	TL-MAGE-3602
     */
    public function withDefaultShippingAddress(array $searchData)
    {
        $addressData = $this->loadData('all_fields_address', array('default_billing_address' => 'No'));
        //Steps
        $this->customerHelper()->openCustomer($searchData);
        $this->customerHelper()->addAddress($addressData);
        $this->saveForm('save_customer');
        //Verifying
        $this->assertMessagePresent('success', 'success_saved_customer');
        //Steps
        $this->customerHelper()->openCustomer($searchData);
        $this->openTab('addresses');
        //Verifying #–2 - Check saved values
        $addressNumber = $this->customerHelper()->isAddressPresent($addressData);
        $this->assertNotEquals(0, $addressNumber, 'The specified address is not present.');
    }
}