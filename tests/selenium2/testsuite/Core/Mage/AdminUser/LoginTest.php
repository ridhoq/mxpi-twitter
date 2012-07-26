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
 * Creating Admin User
 *
 * @package     selenium
 * @subpackage  tests
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class Core_Mage_AdminUser_LoginTest extends Mage_Selenium_TestCase
{
    public function setUpBeforeTests()
    {
        $logOutXpath = $this->_getControlXpath('link', 'log_out', $this->getUimapPage('admin', 'dashboard'));
        $this->admin('log_in_to_admin', false);
        if ($this->_findCurrentPageFromUrl($this->getLocation()) != 'log_in_to_admin'
                && $this->isElementPresent($logOutXpath)) {
            $this->logoutAdminUser();
        }
        $this->validatePage('log_in_to_admin');
        $this->clickControl('link', 'forgot_password');
        if ($this->controlIsPresent('pageelement', 'captcha')) {
            $this->loginAdminUser();
            $this->navigate('system_configuration');
            $this->systemConfigurationHelper()->configure('disable_admin_captcha');
            $this->logoutAdminUser();
        }
    }

    /**
     * <p>Preconditions:</p>
     * <p>Navigate to Login Admin Page</p>
     */
    protected function assertPreConditions()
    {
        $logOutXpath = $this->_getControlXpath('link', 'log_out', $this->getUimapPage('admin', 'dashboard'));
        $this->admin('log_in_to_admin', false);
        if ($this->_findCurrentPageFromUrl($this->getLocation()) != 'log_in_to_admin'
                && $this->isElementPresent($logOutXpath)) {
            $this->logoutAdminUser();
        }
        $this->validatePage('log_in_to_admin');
        $this->addParameter('id', '0');
    }

    /**
     * Login to Admin
     *
     * @test
     * @return array
     */
    public function loginValidUser()
    {
        //Data
        $loginData = array(
            'user_name' => $this->_configHelper->getDefaultLogin(),
            'password'  => $this->_configHelper->getDefaultPassword()
        );
        //Steps
        $this->adminUserHelper()->loginAdmin($loginData);
        //Verifying
        $this->assertTrue($this->checkCurrentPage('dashboard'), $this->getParsedMessages());
        $this->logoutAdminUser();

        return $loginData;
    }

    /**
     * <p>Login with empty "Username"/"Password"</p>
     * <p>Steps</p>
     * <p>1. Leave one field empty;</p>
     * <p>2. Click "Login" button;</p>
     * <p>Expected result:</p>
     * <p>Error message appears - "This is a required field"</p>
     *
     * @param string $emptyField
     * @param array $loginData
     *
     * @test
     * @dataProvider loginEmptyOneFieldDataProvider
     * @depends loginValidUser
     * @TestlinkId TL-MAGE-3154
     */
    public function loginEmptyOneField($emptyField, $loginData)
    {
        //Data
        $loginData[$emptyField] = '%noValue%';
        //Steps
        $this->adminUserHelper()->loginAdmin($loginData);
        //Verifying
        $this->assertMessagePresent('validation', 'empty_' . $emptyField);
        $this->assertTrue($this->verifyMessagesCount(), $this->getParsedMessages());
    }

    public function loginEmptyOneFieldDataProvider()
    {
        return array(
            array('user_name'),
            array('password')
        );
    }

    /**
     * <p>Login with not existing user</p>
     * <p>Steps</p>
     * <p>1.Fill in fields with incorrect data;</p>
     * <p>2. Click "Login" button;</p>
     * <p>Expected result:</p>
     * <p>Error message appears - "Invalid User Name or Password."</p>
     *
     * @param array $loginData
     *
     * @test
     * @depends loginValidUser
     * @TestlinkId TL-MAGE-3157
     */
    public function loginNonExistantUser($loginData)
    {
        //Data
        $loginData['user_name'] = 'nonExistantUser';
        //Steps
        $this->adminUserHelper()->loginAdmin($loginData);
        //Verifying
        $this->assertMessagePresent('error', 'wrong_credentials');
    }

    /**
     * <p>Login with incorrect password</p>
     * <p>Steps</p>
     * <p>1.Fill "Username" field with correct data and "Password" with incorrect data;</p>
     * <p>2. Click "Login" button;</p>
     * <p>Expected result:</p>
     * <p>Error message appears - "Invalid User Name or Password."</p>
     *
     * @param array $loginData
     *
     * @test
     * @depends loginValidUser
     * @TestlinkId TL-MAGE-3156
     */
    public function loginIncorrectPassword($loginData)
    {
        //Data
        $loginData['password'] = $this->generate('string', 9, ':punct:');
        //Steps
        $this->adminUserHelper()->loginAdmin($loginData);
        //Verifying
        $this->assertMessagePresent('error', 'wrong_credentials');
    }

    /**
     * <p>Login with inactive Admin User account</p>
     * <p>Steps</p>
     * <p>Pre-Conditions:</p>
     * <p>Inactive Admin User is created</p>
     * <p>1.Fill in "Username" and "Password" fields with correct data;</p>
     * <p>2. Click "Login" button;</p>
     * <p>Expected result:</p>
     * <p>Error message appears - "This account is inactive."</p>
     *
     * @test
     * @depends loginValidUser
     * @TestlinkId TL-MAGE-3155
     */
    public function loginInactiveAdminAccount()
    {
        //Data
        $userData = $this->loadData('generic_admin_user',
                array('this_acount_is' => 'Inactive', 'role_name' => 'Administrators'), array('email', 'user_name'));
        $loginData = array('user_name' => $userData['user_name'], 'password' => $userData['password']);
        //Steps
        $this->loginAdminUser();
        $this->navigate('manage_admin_users');
        $this->adminUserHelper()->createAdminUser($userData);
        //Verifying
        $this->assertMessagePresent('success', 'success_saved_user');
        //Steps
        $this->logoutAdminUser();
        $this->adminUserHelper()->loginAdmin($loginData);
        //Verifying
        $this->assertMessagePresent('error', 'inactive_account');
    }

    /**
     * <p>Login without any permissions</p>
     * <p>Steps</p>
     * <p>Pre-Conditions:</p>
     * <p>Create a new user without Adminstrators role</p>
     * <p>1.Fill in "Username" and "Password" fields with correct data;</p>
     * <p>2. Click "Login" button;</p>
     * <p>Expected result:</p>
     * <p>Error message appears - "This account is inactive."</p>
     *
     * @test
     * @depends loginValidUser
     * @TestlinkId TL-MAGE-3158
     */
    public function loginWithoutPermissions()
    {
        //Data
        $userData = $this->loadData('generic_admin_user', null, array('email', 'user_name'));
        $loginData = array('user_name' => $userData['user_name'], 'password' => $userData['password']);
        //Pre-Conditions
        $this->loginAdminUser();
        $this->navigate('manage_admin_users');
        $this->adminUserHelper()->createAdminUser($userData);
        //Verifying
        $this->assertMessagePresent('success', 'success_saved_user');
        //Steps
        $this->logoutAdminUser();
        $this->adminUserHelper()->loginAdmin($loginData);
        //Verifying
        $this->assertMessagePresent('error', 'access_denied');
    }

    /**
     * <p>Empty field "Forgot password"</p>
     * <p>Steps</p>
     * <p>1. Goto Login page;</p>
     * <p>2. Click "Forgot Your password" link;</p>
     * <p>3. Leave "Email Address" field empty;</p>
     * <p>4. Click "Retrieve Password" button;</p>
     * <p>Expected result:</p>
     * <p>"This is a required field" message appears;</p>
     *
     * @test
     * @TestlinkId TL-MAGE-3150
     */
    public function forgotEmptyPassword()
    {
        //Data
        $emailData = array('email' => '%noValue%');
        //Steps
        $this->adminUserHelper()->forgotPassword($emailData);
        //Verifying
        $this->assertMessagePresent('error', 'empty_email');
        $this->assertTrue($this->checkCurrentPage('forgot_password'), $this->getParsedMessages());
    }

    /**
     * <p>Invalid e-mail used in "Forgot password" field</p>
     * <p>Steps</p>
     * <p>1. Goto Login page;</p>
     * <p>2. Click "Forgot Your password" link;</p>
     * <p>3. Enter non-existing e-mail into "Email Address" field;</p>
     * <p>4. Click "Retrieve Password" button;</p>
     * <p>Expected result:</p>
     * <p>"If there is an account associated.." message appears;</p>
     *
     * @test
     * @TestlinkId TL-MAGE-3152
     */
    public function forgotPasswordInvalidEmail()
    {
        //Data
        $emailData = array('email' => $this->generate('email', 15));
        //Steps
        $this->adminUserHelper()->forgotPassword($emailData);
        //Verifying
        $this->addParameter('adminEmail', $emailData['email']);
        $this->assertMessagePresent('success', 'retrieve_password');
    }

    /**
     * <p>Valid e-mail used in "Forgot password" field</p>
     * <p>Steps</p>
     * <p>Pre-Conditions:</p>
     * <p>Admin User is created</p>
     * <p>1.Fill in "Forgot password" field with correct data;</p>
     * <p>2. Click "Retriewe password" button;</p>
     * <p>Expected result:</p>
     * <p>Success message "If there is an account associated.." appears.</p>
     * <p>Please check your email and click Back to Login."</p>
     *
     * @test
     * @TestlinkId TL-MAGE-3151
     */
    public function forgotPasswordCorrectEmail()
    {
        //Data
        $userData = $this->loadData('generic_admin_user', null, array('email', 'user_name'));
        $emailData = array('email' => $userData['email']);
        //Steps
        $this->loginAdminUser();
        $this->navigate('manage_admin_users');
        $this->adminUserHelper()->createAdminUser($userData);
        //Verifying
        $this->assertMessagePresent('success', 'success_saved_user');
        //Steps
        $this->logoutAdminUser();
        $this->adminUserHelper()->forgotPassword($emailData);
        //Verifying
        $this->addParameter('adminEmail', $emailData['email']);
        $this->assertMessagePresent('success', 'retrieve_password');
    }

    /**
     * <p>Valid e-mail used in "Forgot password" field, login with old password</p>
     * <p>Steps</p>
     * <p>Pre-Conditions:</p>
     * <p>Admin User is created</p>
     * <p>1.Fill in "Forgot password" field with correct data;</p>
     * <p>2. Click "Retriewe password" button;</p>
     * <p>Expected result:</p>
     * <p>Success message appears -</p>
     * <p>"A new password was sent to your email address.</p>
     * <p>Please check your email and click Back to Login."</p>
     * <p>3. Click "Back to Login" link</p>
     * <p>4. Try to login using old credentials</p>
     * <p>Expected result:</p>
     * <p>User still can login, since the password hasn't been reset.</p>
     *
     * @test
     * @TestlinkId TL-MAGE-3153
     */
    public function forgotPasswordOldPassword()
    {
        //Data
        $userData = $this->loadData('generic_admin_user', array('role_name' => 'Administrators'),
                array('email', 'user_name'));
        $emailData = array('email' => $userData['email']);
        $loginData = array('user_name' => $userData['user_name'], 'password' => $userData['password']);
        //Steps
        $this->loginAdminUser();
        $this->navigate('manage_admin_users');
        $this->adminUserHelper()->createAdminUser($userData);
        //Verifying
        $this->assertMessagePresent('success', 'success_saved_user');
        //Steps
        $this->logoutAdminUser();
        $this->adminUserHelper()->forgotPassword($emailData);
        //Verifying
        $this->addParameter('adminEmail', $emailData['email']);
        $this->assertMessagePresent('success', 'retrieve_password');
        //Steps
        $this->adminUserHelper()->loginAdmin($loginData);
        //Verifying
        $this->assertTrue($this->checkCurrentPage('dashboard'), $this->getParsedMessages());
    }
}