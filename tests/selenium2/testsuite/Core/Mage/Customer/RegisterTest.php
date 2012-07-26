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
 * <p>Customer registration tests</p>
 *
 * @package     selenium
 * @subpackage  tests
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class Core_Mage_Customer_RegisterTest extends Mage_Selenium_TestCase
{
    /**
     * <p>Make sure that customer is not logged in, and navigate to homepage</p>
     */
    protected function assertPreConditions()
    {
        $this->logoutCustomer();
        $this->frontend('home');
        $this->frontend('customer_login');
    }

    /**
     * <p>Сustomer registration.  Filling in only required fields</p>
     * <p>Steps:</p>
     * <p>1. Navigate to 'Login or Create an Account' page.</p>
     * <p>2. Click 'Register' button.</p>
     * <p>3. Fill in reqired fields.</p>
     * <p>4. Click 'Submit' button.</p>
     * <p>Expected result:</p>
     * <p>Customer is registered.</p>
     * <p>Success Message is displayed</p>
     *
     * @return array
     * @test
     * @TestlinkId	TL-MAGE-3245
     */
    public function withRequiredFieldsOnly()
    {
        //Data
        $userData = $this->loadData('customer_account_register',
                array('email' => $this->generate('email', 20, 'valid')));
        //Steps
        $this->customerHelper()->registerCustomer($userData);
        //Verifying
        $this->assertMessagePresent('success', 'success_registration');

        return $userData;
    }

    /**
     * <p>Сustomer registration.  Use email that already exist.</p>
     * <p>Steps:</p>
     * <p>1. Navigate to 'Login or Create an Account' page.</p>
     * <p>2. Click 'Register' button.</p>
     * <p>3. Fill in 'Email' field by using code that already exist.</p>
     * <p>4. Fill other required fields by regular data.</p>
     * <p>5. Click 'Submit' button.</p>
     * <p>Expected result:</p>
     * <p>Customer is not registered.</p>
     * <p>Error Message is displayed.</p>
     *
     * @param array $userData
     *
     * @test
     * @depends withRequiredFieldsOnly
     * @TestlinkId	TL-MAGE-3239
     */
    public function withEmailThatAlreadyExists(array $userData)
    {
        //Steps
        $this->customerHelper()->registerCustomer($userData);
        //Verifying
        $this->assertMessagePresent('error', 'email_exists');
    }

    /**
     * <p>Сustomer registration. Fill in only reqired fields. Use max long values for fields.</p>
     * <p>Steps:</p>
     * <p>1. Navigate to 'Login or Create an Account' page.</p>
     * <p>2. Click 'Register' button.</p>
     * <p>3. Fill in reqired fields by long value alpha-numeric data.</p>
     * <p>4. Click 'Submit' button.</p>
     * <p>Expected result:</p>
     * <p>Customer is registered. Success Message is displayed.</p>
     * <p>Length of fields are 255 characters.</p>
     *
     * @test
     * @depends withRequiredFieldsOnly
     * @TestlinkId	TL-MAGE-3242
     */
    public function withLongValues()
    {
        //Data
        $password = $this->generate('string', 255, ':alnum:');
        $userData = $this->loadData(
                'customer_account_register',
                array(
                    'first_name'            => $this->generate('string', 255, ':alnum:'),
                    'last_name'             => $this->generate('string', 255, ':alnum:'),
                    'email'                 => $this->generate('email', 128, 'valid'),
                    'password'              => $password,
                    'password_confirmation' => $password,
                )
        );
        //Steps
        $this->customerHelper()->registerCustomer($userData);
        //Verifying
        $this->assertMessagePresent('success', 'success_registration');
        //Steps
        $this->navigate('edit_account_info');
        //Verifying
        $this->assertTrue($this->verifyForm($userData, null, array('password', 'password_confirmation')),
                $this->getParsedMessages());
    }

    /**
     * <p>Сustomer registration with empty reqired field.</p>
     * <p>Steps:</p>
     * <p>1. Navigate to 'Login or Create an Account' page.</p>
     * <p>2. Click 'Register' button.</p>
     * <p>3. Fill in fields exept one required.</p>
     * <p>4. Click 'Submit' button</p>
     * <p>Expected result:</p>
     * <p>Customer is not registered.</p>
     * <p>Error Message is displayed.</p>
     *
     * @param string $field
     * @param string $messageCount
     *
     * @test
     * @dataProvider withRequiredFieldsEmptyDataProvider
     * @depends withRequiredFieldsOnly
     * @TestlinkId	TL-MAGE-3244
     */
    public function withRequiredFieldsEmpty($field, $messageCount)
    {
        //Data
        $userData = $this->loadData('customer_account_register', $field);
        //Steps
        $this->customerHelper()->registerCustomer($userData);
        //Verifying
        $fieldset = $this->getCurrentUimapPage()->findFieldset('account_info');
        foreach ($field as $key => $value) {
            $xpath = $fieldset->findField($key);
            $this->addParameter('fieldXpath', $xpath);
        }
        $this->assertMessagePresent('error', 'empty_required_field');
        $this->assertTrue($this->verifyMessagesCount($messageCount), $this->getParsedMessages());
    }

    public function withRequiredFieldsEmptyDataProvider()
    {
        return array(
            array(array('first_name' => '%noValue%'), 1),
            array(array('last_name' => '%noValue%'), 1),
            array(array('email' => '%noValue%'), 1),
            array(array('password' => '%noValue%'), 2),
            array(array('password_confirmation' => '%noValue%'), 1),
        );
    }

    /**
     * <p>Сustomer registration. Fill in all reqired fields by using special characters(except the field "email").</p>
     * <p>Steps:</p>
     * <p>1. Navigate to 'Login or Create an Account' page.</p>
     * <p>2. Click 'Register' button.</p>
     * <p>3. Fill in reqired fields.</p>
     * <p>4. Click 'Submit' button.</p>
     * <p>Expected result:</p>
     * <p>Customer is registered.</p>
     * <p>Success Message is displayed</p>
     *
     * @test
     * @depends withRequiredFieldsOnly
     * @TestlinkId	TL-MAGE-3246
     */
    public function withSpecialCharacters()
    {
        //Data
        $password = $this->generate('string', 25, ':punct:');
        $userData = $this->loadData(
                'customer_account_register',
                array(
                    'first_name'            => $this->generate('string', 25, ':punct:'),
                    'last_name'             => $this->generate('string', 25, ':punct:'),
                    'email'                 => $this->generate('email', 20, 'valid'),
                    'password'              => $password,
                    'password_confirmation' => $password,
                )
        );
        //Steps
        $this->customerHelper()->registerCustomer($userData);
        //Verifying
        $this->assertMessagePresent('success', 'success_registration');
    }

    /**
     * <p>Сustomer registration. Fill in only reqired fields. Use value that is greater than the allowable.</p>
     * <p>Steps:</p>
     * <p>1. Navigate to 'Login or Create an Account' page.</p>
     * <p>2. Click 'Register' button.</p>
     * <p>3. Fill in one field by using value that is greater than the allowable.</p>
     * <p>4. Fill other required fields by regular data.</p>
     * <p>5. Click 'Submit' button.</p>
     * <p>Expected result:</p>
     * <p>Customer is not registered.</p>
     * <p>Error Message is displayed.</p>
     *
     * @param array $longValue
     *
     * @test
     * @dataProvider withLongValuesNotValidDataProvider
     * @depends withRequiredFieldsOnly
     * @TestlinkId	TL-MAGE-3243
     */
    public function withLongValuesNotValid($longValue)
    {
        //Data
        $userData = $this->loadData('customer_account_register', $longValue);
        //Steps
        $this->customerHelper()->registerCustomer($userData);
        //Verifying
        foreach ($longValue as $key => $value) {
            $fieldName = $key;
        }
        $this->assertMessagePresent('error', "not_valid_length_$fieldName");
    }

    public function withLongValuesNotValidDataProvider()
    {
        return array(
            array(array('first_name' => $this->generate('string', 256, ':alnum:'))),
            array(array('last_name' => $this->generate('string', 256, ':alnum:'))),
            array(array('email' => $this->generate('email', 256, 'valid'))),
        );
    }

    /**
     * <p>Сustomer registration with invalid value for 'Email' field</p>
     * <p>Steps:</p>
     * <p>1. Navigate to 'Login or Create an Account' page.</p>
     * <p>2. Click 'Register' button.</p>
     * <p>3. Fill in 'Email' field by wrong value.</p>
     * <p>4. Fill other required fields by regular data.</p>
     * <p>5. Click 'Submit' button.</p>
     * <p>Expected result:</p>
     * <p>Customer is not registered.</p>
     * <p>Error Message is displayed.</p>
     *
     * @param array $invalidEmail
     *
     * @test
     * @dataProvider withInvalidEmailDataProvider
     * @depends withRequiredFieldsOnly
     * @TestlinkId	TL-MAGE-3240
     */
    public function withInvalidEmail($invalidEmail)
    {
        //Data
        $userData = $this->loadData('customer_account_register', $invalidEmail);
        //Steps
        $this->customerHelper()->registerCustomer($userData);
        //Verifying
        $this->assertMessagePresent('error', 'invalid_mail');
    }

    public function withInvalidEmailDataProvider()
    {
        return array(
            array(array('email' => 'invalid')),
            array(array('email' => 'test@invalidDomain')),
            array(array('email' => 'te@st@magento.com'))
        );
    }

    /**
     * <p>Сustomer registration with invalid value for 'Password' fields</p>
     * <p>Steps:</p>
     * <p>1. Navigate to 'Login or Create an Account' page.</p>
     * <p>2. Click 'Register' button.</p>
     * <p>3. Fill in 'password' fields by wrong value.</p>
     * <p>4. Fill other required fields by regular data.</p>
     * <p>5. Click 'Submit' button.</p>
     * <p>Expected result:</p>
     * <p>Customer is not registered.</p>
     * <p>Error Message is displayed.</p>
     *
     * @param array $invalidPassword
     * @param string $errorMessage
     *
     * @test
     * @dataProvider withInvalidPasswordDataProvider
     * @depends withRequiredFieldsOnly
     * @TestlinkId	TL-MAGE-3241
     */
    public function withInvalidPassword($invalidPassword, $errorMessage)
    {
        //Data
        $userData = $this->loadData('customer_account_register', $invalidPassword);
        //Steps
        $this->customerHelper()->registerCustomer($userData);
        //Verifying
        $this->assertMessagePresent('error', $errorMessage);
    }

    public function withInvalidPasswordDataProvider()
    {
        return array(
            array(array('password' => 12345, 'password_confirmation' => 12345), 'short_passwords'),
            array(array('password' => 1234567, 'password_confirmation' => 12345678), 'passwords_not_match'),
        );
    }
}
