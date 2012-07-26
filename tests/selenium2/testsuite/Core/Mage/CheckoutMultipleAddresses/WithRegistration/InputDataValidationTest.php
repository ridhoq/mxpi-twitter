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
 * Tests for Checkout with Multiple Addresses. Frontend
 *
 * @package     selenium
 * @subpackage  tests
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class Core_Mage_CheckoutMultipleAddresses_WithRegistration_InputDataValidationTest extends Mage_Selenium_TestCase
{
    protected function tearDownAfterTest()
    {
        $this->shoppingCartHelper()->frontClearShoppingCart();
        $this->logoutCustomer();
    }

    /**
     * <p>Creating Simple product</p>
     *
     * @test
     * @return array $productData
     */
    public function preconditionsCreateProduct()
    {
        //Data
        $productData = $this->loadData('simple_product_visible');
        //Steps
        $this->loginAdminUser();
        $this->navigate('manage_products');
        $this->productHelper()->createProduct($productData);
        //Verification
        $this->assertMessagePresent('success', 'success_saved_product');
        return $productData;
    }

################################################################################
#                                                                              #
#                     Create an Account Page                                    #
#                                                                              #
################################################################################

    /**
     * <p>Customer registration.  Filling in only required fields</p>
     * <p>Steps:</p>
     * <p>1. Open product page.</p>
     * <p>2. Add product to Shopping Cart.</p>
     * <p>3. Click "Checkout with Multiple Addresses".</p>
     * <p>4. Select Checkout Method with Registering</p>
     * <p>5. Navigate to 'Create an Account' page.</p>
     * <p>6. Fill in required fields.</p>
     * <p>7. Click 'Submit' button.</p>
     * <p>Expected result:</p>
     * <p>Customer is registered.</p>
     * <p>Success Message is displayed</p>
     *
     * @param array $productData
     *
     * @return array $checkoutData
     * @test
     * @depends preconditionsCreateProduct
     * @TestlinkId TL-MAGE-5313
     */
    public function withRequiredFieldsOnly($productData = array())
    {
        //Data
        $checkoutData = $this->loadData('multiple_invalid_data_register',
            array ('products_to_add/product_1' => $productData,
                'general_account_info/company' => '',
                'general_account_info/fax' => '',
                'general_account_info/street_address_2' => ''
            )
        );
        //Steps
        $this->checkoutMultipleAddressesHelper()->frontCreateMultipleCheckout($checkoutData, false);
        //Verification
        $this->assertMessagePresent('success', 'success_registered_user');
        return $checkoutData;
    }

    /**
     * <p>Customer registration.  Use email that already exist.</p>
     * <p>Steps:</p>
     * <p>1. Open product page.</p>
     * <p>2. Add product to Shopping Cart.</p>
     * <p>3. Click "Checkout with Multiple Addresses".</p>
     * <p>4. Select Checkout Method with Registering</p>
     * <p>5. Navigate to 'Create an Account' page.</p>
     * <p>6. Fill in 'Email' field by using code that already exist.</p>
     * <p>7. Fill other required fields by regular data.</p>
     * <p>8. Click 'Submit' button.</p>
     * <p>Expected result:</p>
     * <p>Customer is not registered.</p>
     * <p>Error Message is displayed.</p>
     *
     * @param array $checkoutData
     *
     * @test
     * @depends withRequiredFieldsOnly
     * @TestlinkId	TL-MAGE-5314
     */
    public function withEmailThatAlreadyExists(array $checkoutData)
    {
        //Steps
        $this->checkoutMultipleAddressesHelper()->frontCreateMultipleCheckout($checkoutData, false);
        //Verification
        $this->assertMessagePresent('error', 'email_exists');
    }

    /**
     * <p>Customer registration. Fill in only required fields. Use max long values for fields.</p>
     * <p>Steps:</p>
     * <p>1. Open product page.</p>
     * <p>2. Add product to Shopping Cart.</p>
     * <p>3. Click "Checkout with Multiple Addresses".</p>
     * <p>4. Select Checkout Method with Registering</p>
     * <p>5. Navigate to 'Create an Account' page.</p>
     * <p>6. Fill in required fields by long value alpha-numeric data.</p>
     * <p>7. Click 'Submit' button.</p>
     * <p>Expected result:</p>
     * <p>Customer is registered. Success Message is displayed.</p>
     * <p>Length of fields are 255 characters.</p>
     *
     * @param array $productData
     *
     * @test
     * @depends preconditionsCreateProduct
     * @TestlinkId TL-MAGE-5315
     */
    public function withLongValues($productData)
    {
        //Data
        $password = $this->generate('string', 255, ':alnum:');
        $checkoutData = $this->loadData('multiple_invalid_data_register',
            array ('products_to_add/product_1' => $productData,
                'general_account_info/first_name' => $this->generate('string', 255, ':alnum:'),
                'general_account_info/last_name' => $this->generate('string', 255, ':alnum:'),
                'general_account_info/email' => $this->generate('email', 128, 'valid'),
                'general_account_info/password' => $password,
                'general_account_info/password_confirmation' => $password,
                'general_account_info/company' => '',
                'general_account_info/telephone' => $this->generate('string', 255, ':alpha:'),
                'general_account_info/fax' => '',
                'general_account_info/street_address_1' => $this->generate('string', 255, ':alnum:'),
                'general_account_info/street_address_2' => '',
                'general_account_info/city' => $this->generate('string', 255, ':alnum:'),
                'general_account_info/zip_code' => $this->generate('string', 255, ':alnum:')
            )
        );
        //Steps
        $this->checkoutMultipleAddressesHelper()->frontCreateMultipleCheckout($checkoutData, false);
        //Verification
        $this->assertMessagePresent('success', 'success_registered_user');
    }

    /**
     * <p>Customer registration with empty required field.</p>
     * <p>Steps:</p>
     * <p>1. Open product page.</p>
     * <p>2. Add product to Shopping Cart.</p>
     * <p>3. Click "Checkout with Multiple Addresses".</p>
     * <p>4. Select Checkout Method with Registering</p>
     * <p>5. Navigate to 'Create an Account' page.</p>
     * <p>6. Fill in fields except one required.</p>
     * <p>7. Click 'Submit' button</p>
     * <p>Expected result:</p>
     * <p>Customer is not registered.</p>
     * <p>Error Message is displayed.</p>
     *
     * @param string $field
     * @param string $fieldType
     * @param array $productData
     *
     * @test
     * @dataProvider withRequiredFieldsEmptyDataProvider
     * @depends preconditionsCreateProduct
     * @depends withRequiredFieldsOnly
     * @TestlinkId	TL-MAGE-5316
     */
    public function withRequiredFieldsEmpty($field,$fieldType,$productData)
    {
        //Data
        $userData = $this->loadData('general_account_info',
            array ('company' => '', 'fax' => '', 'street_address_2' => '', $field => '')
        );
        //Steps
        $this->productHelper()->frontOpenProduct($productData['general_name']);
        $this->productHelper()->frontAddProductToCart();
        $this->clickControl('link', 'checkout_with_multiple_addresses');
        $this->clickButton('create_account');
        $this->fillForm($userData);
        $this->saveForm('submit');
        //Verification
        $this->addFieldIdToMessage($fieldType, $field);
        if ($fieldType == 'dropdown') {
            $this->assertMessagePresent('validation', 'please_select_option');
        } else {
            $this->assertMessagePresent('validation', 'empty_required_field');
        }
    }

    public function withRequiredFieldsEmptyDataProvider()
    {
        return array(
            array('first_name', 'field'),//First Name
            array('last_name', 'field'),//Last Name
            array('email', 'field'),//Email Address
            array('telephone', 'field'),//Telephone
            array('street_address_1', 'field'),
            array('city', 'field'),//City
            array('state', 'dropdown'),//State/Province
            array('zip_code', 'field'),//Zip/Postal Code
            array('country', 'dropdown'),//Country
            array('password', 'field'),//Password
            array('password_confirmation', 'field')//Confirm Password
        );
    }

    /**
     * <p>Customer registration. Fill in all required fields by using special characters(except the field "email").</p>
     * <p>Steps:</p>
     * <p>1. Open product page.</p>
     * <p>2. Add product to Shopping Cart.</p>
     * <p>3. Click "Checkout with Multiple Addresses".</p>
     * <p>4. Select Checkout Method with Registering</p>
     * <p>5. Navigate to 'Create an Account' page.</p>
     * <p>6. Fill in all required fields by using special characters(except the field "email").</p>
     * <p>7. Click 'Submit' button.</p>
     * <p>Expected result:</p>
     * <p>Customer is registered.</p>
     * <p>Success Message is displayed</p>
     *
     * @param $productData
     *
     * @test
     * @depends preconditionsCreateProduct
     * @TestlinkId	TL-MAGE-5317
     */
    public function withSpecialCharacters($productData)
    {
        //Data
        $password = $this->generate('string', 50, ':punct:');
        $checkoutData = $this->loadData('multiple_invalid_data_register',
            array ('products_to_add/product_1' => $productData,
                'general_account_info/first_name' => $this->generate('string', 50, ':punct:'),
                'general_account_info/last_name' => $this->generate('string', 50, ':punct:'),
                'general_account_info/email' => $this->generate('email', 128, 'valid'),
                'general_account_info/password' => $password,
                'general_account_info/password_confirmation' => $password,
                'general_account_info/company' => '',
                'general_account_info/telephone' => $this->generate('string', 50, ':punct:'),
                'general_account_info/fax' => '',
                'general_account_info/street_address_1' => $this->generate('string', 255, ':punct:'),
                'general_account_info/street_address_2' => '',
                'general_account_info/city' => $this->generate('string', 255, ':punct:'),
                'general_account_info/zip_code' => $this->generate('string', 255, ':punct:')
            )
        );
        //Steps
        $this->checkoutMultipleAddressesHelper()->frontCreateMultipleCheckout($checkoutData, false);
        //Verification
        $this->assertMessagePresent('success', 'success_registered_user');

    }

    /**
     * <p>Customer registration. Fill in only required fields. Use value that is greater than the allowable.</p>
     * <p>Steps:</p>
     * <p>1. Open product page.</p>
     * <p>2. Add product to Shopping Cart.</p>
     * <p>3. Click "Checkout with Multiple Addresses".</p>
     * <p>4. Select Checkout Method with Registering</p>
     * <p>5. Navigate to 'Create an Account' page.</p>
     * <p>6. Fill in one field by using value that is greater than the allowable.</p>
     * <p>7. Fill other required fields by regular data.</p>
     * <p>8. Click 'Submit' button.</p>
     * <p>Expected result:</p>
     * <p>Customer is not registered.</p>
     * <p>Error Message is displayed.</p>
     *
     * @param string $field
     * @param string $fieldName
     * @param string $fieldValue
     * @param array $productData
     *
     * @test
     * @dataProvider withLongValuesNotValidDataProvider
     * @depends preconditionsCreateProduct
     * @TestlinkId	TL-MAGE-5318
     */
    public function withLongValuesNotValid($field, $fieldName, $fieldValue, $productData)
    {
        //Data
        $userData = $this->loadData('general_account_info',
            array ('company' => '', 'fax' => '', 'street_address_2' => '', $field => $fieldValue)
        );
        //Steps
        $this->productHelper()->frontOpenProduct($productData['general_name']);
        $this->productHelper()->frontAddProductToCart();
        $this->clickControl('link', 'checkout_with_multiple_addresses');
        $this->clickButton('create_account');
        $this->fillForm($userData);
        $this->saveForm('submit');
        //Verification
        $this->addParameter('fieldName',$fieldName );
        $this->assertTrue($this->checkCurrentPage('checkout_multishipping_register'),"Unexpected page");
        $errorMsg = ($field=='email')? 'not_valid_length_email' : 'not_valid_length';
        $this->assertMessagePresent('error', $errorMsg);
    }

    public function withLongValuesNotValidDataProvider()
    {
        return array(
            array('first_name', 'First Name' , $this->generate('string', 256, ':alnum:')),//First Name
            array('last_name', 'Last Name',$this->generate('string', 256, ':alnum:')),//Last Name
            array('email', 'field',$this->generate('email', 256, 'valid')),//Email Address
            array('telephone', 'Telephone',$this->generate('string', 256, ':alnum:')),//Telephone
            array('street_address_1', 'Street Address',$this->generate('string', 256, ':alnum:')),
            array('city', 'City',$this->generate('string', 256, ':alnum:'))//City
        );
    }

    /**
     * <p>Customer registration. Fill in only Zip Code field. Use value that is greater than the allowable.</p>
     * <p>Steps:</p>
     * <p>1. Open product page.</p>
     * <p>2. Add product to Shopping Cart.</p>
     * <p>3. Click "Checkout with Multiple Addresses".</p>
     * <p>4. Select Checkout Method with Registering</p>
     * <p>5. Navigate to 'Create an Account' page.</p>
     * <p>6. Fill in Zip Code field by using value that is greater than the allowable.</p>
     * <p>7. Fill other required fields by regular data.</p>
     * <p>8. Click 'Submit' button.</p>
     * <p>Expected result:</p>
     * <p>Customer is not registered.</p>
     * <p>Error Message is displayed.</p>
     *
     * @param array $productData
     *
     * @test
     * @depends preconditionsCreateProduct
     * @TestlinkId	TL-MAGE-5319
     */
    public function withInvalidZipCode($productData)
    {
        //Data
        $userData = $this->loadData('general_account_info', array(
                'company'           => '',
                'fax'               => '',
                'street_address_2'  => '',
                'zip_code'          => $this->generate('string', 256, ':digit:'))
        );
        //Steps
        $this->productHelper()->frontOpenProduct($productData['general_name']);
        $this->productHelper()->frontAddProductToCart();
        $this->clickControl('link', 'checkout_with_multiple_addresses');
        $this->clickButton('create_account');
        $this->fillForm($userData);
        $this->saveForm('submit');
        //Verification
        $this->addParameter('fieldName', $userData['zip_code']);
        $this->assertTrue($this->checkCurrentPage('checkout_multishipping_addresses'), "Unexpected page");
        $this->assertMessagePresent('success', 'success_registered_user');
        $this->addParameter('productName', $productData['general_name']);
        $this->_getControlXpath('dropdown', 'shipping_address_choice');
        $value = $this->getText($this->_getControlXpath('dropdown', 'shipping_address_choice'));
        $this->assertFalse(strrpos($value, $userData['zip_code']), 'Verification failed');
        $this->assertTrue((bool)strrpos($value, substr($userData['zip_code'], 0, 255)), 'Verification failed');
    }

    /**
     * <p>Customer registration with invalid value for 'Email' field</p>
     * <p>Steps:</p>
     * <p>1. Open product page.</p>
     * <p>2. Add product to Shopping Cart.</p>
     * <p>3. Click "Checkout with Multiple Addresses".</p>
     * <p>4. Select Checkout Method with Registering</p>
     * <p>5. Navigate to 'Create an Account' page.</p>
     * <p>6. Fill in 'Email' field by wrong value.</p>
     * <p>7. Fill other required fields by regular data.</p>
     * <p>8. Click 'Submit' button.</p>
     * <p>Expected result:</p>
     * <p>Customer is not registered.</p>
     * <p>Error Message is displayed.</p>
     *
     * @param string $invalidEmail
     * @param array $productData
     *
     * @test
     * @dataProvider withInvalidEmailDataProvider
     * @depends preconditionsCreateProduct
     * @depends withRequiredFieldsOnly
     * @TestlinkId	TL-MAGE-5320
     */
    public function withInvalidEmail($invalidEmail,$productData)
    {
        //Data
        $userData = $this->loadData('general_account_info',
            array ('company' => '', 'fax' => '', 'street_address_2' => '', 'email' => $invalidEmail)
        );
        //Steps
        $this->productHelper()->frontOpenProduct($productData['general_name']);
        $this->productHelper()->frontAddProductToCart();
        $this->clickControl('link', 'checkout_with_multiple_addresses');
        $this->clickButton('create_account');
        $this->fillForm($userData);
        $this->saveForm('submit');
        //Verification
        $this->assertTrue($this->checkCurrentPage('checkout_multishipping_register'),"Unexpected page");
        $this->assertMessagePresent('validation', 'invalid_mail');
    }

    public function withInvalidEmailDataProvider()
    {
        return array(
            array( 'invalid'),
            array( 'test@invalidDomain'),
            array('te@st@magento.com'),
            array('.test@magento.com'),
        );
    }

    /**
     * <p>Customer registration with invalid value for 'Password' fields</p>
     * <p>Steps:</p>
     * <p>1. Open product page.</p>
     * <p>2. Add product to Shopping Cart.</p>
     * <p>3. Click "Checkout with Multiple Addresses".</p>
     * <p>4. Select Checkout Method with Registering</p>
     * <p>5. Navigate to 'Create an Account' page.</p>
     * <p>6. Fill in 'password' fields by wrong value.</p>
     * <p>7. Fill other required fields by regular data.</p>
     * <p>8. Click 'Submit' button.</p>
     * <p>Expected result:</p>
     * <p>Customer is not registered.</p>
     * <p>Error Message is displayed.</p>
     *
     * @param string $invalidPassword
     * @param string $errorMessage
     * @param array $productData
     *
     * @test
     * @dataProvider withInvalidPasswordDataProvider
     * @depends preconditionsCreateProduct
     * @depends withRequiredFieldsOnly
     * @TestlinkId	TL-MAGE-5321
     */
    public function withInvalidPassword($invalidPassword, $errorMessage,$productData)
    {
        //Data
        $userData = $this->loadData('general_account_info',
            array ('company' => '',
                'fax' => '',
                'street_address_2' => '',
                'password' => $invalidPassword['password'],
                'password_confirmation' => $invalidPassword['password_confirmation']
            )
        );
        //Steps
        $this->productHelper()->frontOpenProduct($productData['general_name']);
        $this->productHelper()->frontAddProductToCart();
        $this->clickControl('link', 'checkout_with_multiple_addresses');
        $this->clickButton('create_account');
        $this->fillForm($userData);
        $this->saveForm('submit');
        //Verification
        $this->assertTrue($this->checkCurrentPage('checkout_multishipping_register'),"Unexpected page");
        $this->assertMessagePresent('validation', $errorMessage);
    }

    /**
     * DataProvider for withInvalidPassword
     *
     * @return array
     */
    public function withInvalidPasswordDataProvider()
    {
        return array(
            array(array('password' => 12345, 'password_confirmation' => 12345), 'short_passwords'),
            array(array('password' => 1234567, 'password_confirmation' => 12345678), 'passwords_not_match'),
        );
    }
}
