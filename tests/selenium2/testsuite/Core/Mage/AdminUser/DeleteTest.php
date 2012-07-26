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
 * Deleting Admin User
 *
 * @package     selenium
 * @subpackage  tests
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class Core_Mage_AdminUser_DeleteTest extends Mage_Selenium_TestCase
{
    /**
     * Log in to Backend.
     */
    public function setUpBeforeTests()
    {
        $this->loginAdminUser();
    }

    /**
     * <p>Preconditions:</p>
     * <p>Navigate to System -> Permissions -> Users.</p>
     */
    protected function assertPreConditions()
    {
        $this->navigate('manage_admin_users');
        $this->addParameter('id', '0');
    }

    /**
     * <p>Create Admin User (all required fields are filled).</p>
     * <p>Steps:</p>
     * <p>1.Press "Add New User" button.</p>
     * <p>2.Fill all required fields.</p>
     * <p>3.Press "Save User" button.</p>
     * <p>4.Press "Delete User" button.</p>
     * <p>Expected result:</p>
     * <p>User successfully deleted.</p>
     * <p>Message "The user has been deleted." is displayed.</p>
     *
     * @test
     * @TestlinkId TL-MAGE-3149
     */
    public function deleteAdminUserDeletable()
    {
        //Data
        $userData = $this->loadData('generic_admin_user', null, array('email', 'user_name'));
        $searchData = $this->loadData('search_admin_user',
                array('email' => $userData['email'], 'user_name' => $userData['user_name']));
        //Steps
        $this->adminUserHelper()->createAdminUser($userData);
        //Verifying
        $this->assertMessagePresent('success', 'success_saved_user');
        $this->assertTrue($this->checkCurrentPage('edit_admin_user'), $this->getParsedMessages());
        //Steps
        $this->clickButtonAndConfirm('delete_user', 'confirmation_for_delete');
        //Verifying
        $this->assertMessagePresent('success', 'success_deleted_user');
    }

    /**
     * <p>Delete logged in as Admin User</p>
     *
     * @test
     * @TestlinkId TL-MAGE-5228
     */
    public function deleteAdminUserCurrent()
    {
        //Data
        $searchData = $this->loadData('search_admin_user');
        $searchDataCurrentUser = array();
        //Steps
        $this->navigate('my_account');
        $this->assertTrue($this->checkCurrentPage('my_account'), $this->getParsedMessages());
        foreach ($searchData as $key => $value) {
            if ($value != '%noValue%') {
                $xpath = $this->_getControlXpath('field', $key);
                $searchDataCurrentUser[$key] = $this->getValue($xpath);
            } else {
                $searchDataCurrentUser[$key] = $value;
            }
        }
        $this->navigate('manage_admin_users');
        $this->addParameter('user_first_last_name',
                $searchDataCurrentUser['first_name'] . ' ' . $searchDataCurrentUser['last_name']);
        $this->searchAndOpen($searchDataCurrentUser);
        //Verifying
        $this->clickButtonAndConfirm('delete_user', 'confirmation_for_delete');
        //Verifying
        $this->assertMessagePresent('error', 'cannot_delete_account');
    }
}