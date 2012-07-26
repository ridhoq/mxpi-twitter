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
 * Test creation new customer from Backend
 *
 * @package     selenium
 * @subpackage  tests
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class Core_Mage_Customer_CreateTest extends Mage_Selenium_TestCase
{
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
    }

    /**
     * <p>Test navigation.</p>
     * <p>Steps:</p>
     * <p>1. Verify that 'Add New Customer' button is present and click her.</p>
     * <p>2. Verify that the create customer page is opened.</p>
     * <p>3. Verify that 'Back' button is present.</p>
     * <p>4. Verify that 'Save Customer' button is present.</p>
     * <p>5. Verify that 'Reset' button is present.</p>
     *
     * @test
     */
    public function navigation()
    {
        $this->assertTrue($this->buttonIsPresent('add_new_customer'),
                'There is no "Add New Customer" button on the page');
        $this->clickButton('add_new_customer');
        $this->assertTrue($this->checkCurrentPage('create_customer'), $this->getParsedMessages());
        $this->assertTrue($this->buttonIsPresent('back'), 'There is no "Back" button on the page');
        $this->assertTrue($this->buttonIsPresent('save_customer'), 'There is no "Save" button on the page');
        $this->assertTrue($this->buttonIsPresent('save_and_continue_edit'),
                'There is no "Save and Continue Edit" button on the page');
        $this->assertTrue($this->buttonIsPresent('reset'), 'There is no "Reset" button on the page');
    }

    /**
     * <p>Create customer by filling in only required fields</p>
     * <p>Steps:</p>
     * <p>1. Click 'Add New Customer' button.</p>
     * <p>2. Fill in reqired fields.</p>
     * <p>3. Click 'Save Customer' button.</p>
     * <p>Expected result:</p>
     * <p>Customer is created.</p>
     * <p>Success Message is displayed</p>
     *
     * @return array
     * @test
     * @depends navigation
     * @TestlinkId TL-MAGE-3587
     */
    public function withRequiredFieldsOnly()
    {
        //Data
        $userData = $this->loadData('generic_customer_account', null, 'email');
        //Steps
        $this->customerHelper()->createCustomer($userData);
        //Verifying
        $this->assertMessagePresent('success', 'success_saved_customer');

        return $userData;
    }

    /**
     * <p>Create customer. Use email that already exist</p>
     * <p>Steps:</p>
     * <p>1. Click 'Add New Customer' button.</p>
     * <p>2. Fill in 'Email' field by using email that already exist.</p>
     * <p>3. Fill other required fields by regular data.</p>
     * <p>4. Click 'Save Customer' button.</p>
     * <p>Expected result:</p>
     * <p>Customer is not created.</p>
     * <p>Error Message is displayed.</p>
     *
     * @param array $userData
     *
     * @test
     * @depends withRequiredFieldsOnly
     * @TestlinkId    TL-MAGE-3582
     */
    public function withEmailThatAlreadyExists(array $userData)
    {
        //Steps
        $this->customerHelper()->createCustomer($userData);
        //Verifying
        $this->assertMessagePresent('error', 'customer_email_exist');
    }

    /**
     * <p>Ceate customer with one empty reqired field</p>
     * <p>Steps:</p>
     * <p>1. Click 'Add New Customer' button.</p>
     * <p>2. Fill in fields exept one required.</p>
     * <p>3. Click 'Save Customer' button.</p>
     * <p>Expected result:</p>
     * <p>Customer is not created.</p>
     * <p>Error Message is displayed.</p>
     *
     * @param string $emptyField
     *
     * @test
     * @dataProvider withRequiredFieldsEmptyDataProvider
     * @depends withRequiredFieldsOnly
     * @TestlinkId    TL-MAGE-3586
     */
    public function withRequiredFieldsEmpty($emptyField)
    {
        //Data
        $userData = $this->loadData('generic_customer_account', array($emptyField => '%noValue%'), 'email');
        //Steps
        $this->customerHelper()->createCustomer($userData);
        //Verifying
        $tab = $this->getCurrentUimapPage()->findTab('account_information');
        $xpath = $tab->findField($emptyField);
        $this->addParameter('fieldXpath', $xpath);
        $this->assertMessagePresent('error', 'empty_required_field');
        $this->assertTrue($this->verifyMessagesCount(), $this->getParsedMessages());
    }

    public function withRequiredFieldsEmptyDataProvider()
    {
        return array(
            array('first_name'),
            array('last_name'),
            array('password'),
            array('email')
        );
    }

    /**
     * <p>Create customer. Fill in all fields by using special characters(except the field "email").</p>
     * <p>Steps:</p>
     * <p>1. Click 'Add New Customer' button.</p>
     * <p>2. Fill in fields on 'Account Information' tab.</p>
     * <p>3. Click 'Save Customer' button.</p>
     * <p>Expected result:</p>
     * <p>Customer is created.</p>
     * <p>Success Message is displayed</p>
     *
     * @test
     * @depends withRequiredFieldsOnly
     * @TestlinkId	TL-MAGE-3588
     */
    public function withSpecialCharactersExeptEmail()
    {
        //Data
        $userData = $this->loadData('generic_customer_account',
                array(
                            'prefix'         => $this->generate('string', 32, ':punct:'),
                            'first_name'     => $this->generate('string', 32, ':punct:'),
                            'middle_name'    => $this->generate('string', 32, ':punct:'),
                            'last_name'      => $this->generate('string', 32, ':punct:'),
                            'suffix'         => $this->generate('string', 32, ':punct:'),
                            'tax_vat_number' => $this->generate('string', 32, ':punct:'),
                            'password'       => $this->generate('string', 32, ':punct:')
                ), 'email'
        );
        $searchData = $this->loadData('search_customer', array('email' => $userData['email']));
        //Steps
        $this->customerHelper()->createCustomer($userData);
        //Verifying
        $this->assertMessagePresent('success', 'success_saved_customer');
        //Steps
        $param = $userData['first_name'] .' '.$userData['last_name'];
        $this->addParameter('customer_first_last_name', $param);
        $this->customerHelper()->openCustomer($searchData);
        $this->openTab('account_information');
        //Verifying
        $this->assertTrue($this->verifyForm($userData, 'account_information'), $this->getParsedMessages());
    }

    /**
     * <p>Create Customer. Fill in fields. Use max long values for fields.</p>
     * <p>Steps:</p>
     * <p>1. Click 'Add New Customer' button.</p>
     * <p>2. Fill in fields by long value alpha-numeric data on 'Account Information' tab.</p>
     * <p>3. Click 'Save Customer' button.</p>
     * <p>Expected result:</p>
     * <p>Customer is created. Success Message is displayed.</p>
     * <p>Length of fields are 255 characters.</p>
     *
     * @test
     * @depends withRequiredFieldsOnly
     * @TestlinkId	TL-MAGE-3585
     */
    public function withLongValues()
    {
        //Data
        $longValues = array(
            'prefix'         => $this->generate('string', 255, ':alnum:'),
            'first_name'     => $this->generate('string', 255, ':alnum:'),
            'middle_name'    => $this->generate('string', 255, ':alnum:'),
            'last_name'      => $this->generate('string', 255, ':alnum:'),
            'suffix'         => $this->generate('string', 255, ':alnum:'),
            'email'          => $this->generate('email', 128, 'valid'),
            'tax_vat_number' => $this->generate('string', 255, ':alnum:'),
            'password'       => $this->generate('string', 255, ':alnum:')
        );
        $userData = $this->loadData('generic_customer_account', $longValues);
        $searchData = $this->loadData('search_customer', array('email' => $userData['email']));
        //Steps
        $this->customerHelper()->createCustomer($userData);
        //Verifying
        $this->assertMessagePresent('success', 'success_saved_customer');
        //Steps
        $param = $userData['first_name'] .' '.$userData['last_name'];
        $this->addParameter('customer_first_last_name', $param);
        $this->customerHelper()->openCustomer($searchData);
        $this->openTab('account_information');
        //Verifying
        $this->assertTrue($this->verifyForm($userData, 'account_information'), $this->getParsedMessages());
    }

    /**
     * <p>Create customer with invalid value for 'Email' field</p>
     * <p>Steps:</p>
     * <p>1. Click 'Add New Customer' button.</p>
     * <p>2. Fill in 'Email' field by wrong value.</p>
     * <p>3. Fill other required fields by regular data.</p>
     * <p>4. Click 'Save Customer' button.</p>
     * <p>Expected result:</p>
     * <p>Customer is not created.</p>
     * <p>Error Message is displayed.</p>
     *
     * @param string $wrongEmail
     *
     * @test
     * @dataProvider withInvalidEmailDataProvider
     * @depends withRequiredFieldsOnly
     * @TestlinkId	TL-MAGE-3583
     */
    public function withInvalidEmail($wrongEmail)
    {
        //Data
        $userData = $this->loadData('generic_customer_account', array('email' => $wrongEmail));
        //Steps
        $this->customerHelper()->createCustomer($userData);
        //Verifying
        $this->assertMessagePresent('error', 'invalid_email');
    }

    public function withInvalidEmailDataProvider()
    {
        return array(
            array('invalid'),
            array('test@invalidDomain'),
            array('te@st@magento.com')
        );
    }

    /**
     * <p>Create customer. Use a value for 'Password' field the length of which less than 6 characters.</p>
     * <p>Steps:</p>
     * <p>1. Click 'Add New Customer' button.</p>
     * <p>2. Fill in 'Password' field by wrong value.</p>
     * <p>3. Fill other required fields by regular data.</p>
     * <p>4. Click 'Save Customer' button.</p>
     * <p>Expected result:</p>
     * <p>Customer is not created.</p>
     * <p>Error Message is displayed.</p>
     *
     * @test
     * @depends withRequiredFieldsOnly
     * @TestlinkId	TL-MAGE-3584
     */
    public function withInvalidPassword()
    {
        //Data
        $userData = $this->loadData('generic_customer_account',
                array('password' => $this->generate('string', 5, ':alnum:')), 'email');
        //Steps
        $this->customerHelper()->createCustomer($userData);
        //Verifying
        $this->assertMessagePresent('error', 'password_too_short');
    }

    /**
     * <p>Create customer with auto-generated password</p>
     * <p>Steps:</p>
     * <p>1. Click 'Add New Customer' button.</p>
     * <p>2. Fill in reqired fields.</p>
     * <p>3. Click 'Save Customer' button.</p>
     * <p>Expected result:</p>
     * <p>Customer is created.</p>
     * <p>Success Message is displayed</p>
     *
     * @test
     * @depends withRequiredFieldsOnly
     * @TestlinkId	TL-MAGE-3581
     */
    public function withAutoGeneratedPassword()
    {
        //Data
        $userData = $this->loadData('generic_customer_account',
                array('password' => '%noValue%', 'auto_generated_password' => 'Yes'), 'email');
        //Steps
        $this->customerHelper()->createCustomer($userData);
        //Verifying
        $this->assertMessagePresent('success', 'success_saved_customer');
    }

    /**
     * <p>Create customer with one address by filling all fields</p>
     * <p>Steps:</p>
     * <p>1. Click 'Add New Customer' button.</p>
     * <p>2. Fill in fields on 'Account Information' tab.</p>
     * <p>3. Open 'Addresses' tab.</p>
     * <p>4. Click 'Add New Address' button.</p>
     * <p>5. Fill in all fields on 'Addresses' tab.</p>
     * <p>6. Click 'Save Customer' button.</p>
     * <p>Expected result:</p>
     * <p>Customer with address is created.</p>
     * <p>Success Message is displayed</p>
     *
     * @test
     * @depends withRequiredFieldsOnly
     * @TestlinkId	TL-MAGE-3580
     */
    public function withAddress()
    {
        //Data
        $userData = $this->loadData('all_fields_customer_account', null, 'email');
        $addressData = $this->loadData('all_fields_address');
        //Steps
        $this->customerHelper()->createCustomer($userData, $addressData);
        //Verifying
        $this->assertMessagePresent('success', 'success_saved_customer');
    }
}