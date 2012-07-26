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
 * Test creation new store view
 *
 * @package     selenium
 * @subpackage  tests
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class Core_Mage_Store_StoreView_CreateTest extends Mage_Selenium_TestCase
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
     * <p>Navigate to System -> Manage Stores</p>
     */
    protected function assertPreConditions()
    {
        $this->navigate('manage_stores');
    }

    /**
     * <p>Test navigation.</p>
     * <p>Steps:</p>
     * <p>1. Verify that 'Create Store View' button is present and click her.</p>
     * <p>2. Verify that the create store view page is opened.</p>
     * <p>3. Verify that 'Back' button is present.</p>
     * <p>4. Verify that 'Save Store View' button is present.</p>
     * <p>5. Verify that 'Reset' button is present.</p>
     *
     * @test
     */
    public function navigation()
    {
        $this->assertTrue($this->controlIsPresent('button', 'create_store_view'),
                'There is no "Create Store View" button on the page');
        $this->clickButton('create_store_view');
        $this->assertTrue($this->checkCurrentPage('new_store_view'), $this->getParsedMessages());
        $this->assertTrue($this->controlIsPresent('button', 'back'), 'There is no "Back" button on the page');
        $this->assertTrue($this->controlIsPresent('button', 'save_store_view'),
                'There is no "Save" button on the page');
        $this->assertTrue($this->controlIsPresent('button', 'reset'), 'There is no "Reset" button on the page');
    }

    /**
     * <p>Create Store View. Fill in only required fields.</p>
     * <p>Steps:</p>
     * <p>1. Click 'Create Store View' button.</p>
     * <p>2. Fill in required fields.</p>
     * <p>3. Click 'Save Store View' button.</p>
     * <p>Expected result:</p>
     * <p>Store View is created.</p>
     * <p>Success Message is displayed</p>
     *
     * @return array
     * @test
     * @depends navigation
     * @TestlinkId TL-MAGE-3628
     */
    public function withRequiredFieldsOnly()
    {
        //Data
        $storeViewData = $this->loadData('generic_store_view');
        //Steps
        $this->storeHelper()->createStore($storeViewData, 'store_view');
        //Verifying
        $this->assertMessagePresent('success', 'success_saved_store_view');

        return $storeViewData;
    }

    /**
     * <p>Create Store View.  Fill in field 'Code' by using code that already exist.</p>
     * <p>Steps:</p>
     * <p>1. Click 'Create Store View' button.</p>
     * <p>2. Fill in 'Code' field by using code that already exist.</p>
     * <p>3. Fill other required fields by regular data.</p>
     * <p>4. Click 'Save Store View' button.</p>
     * <p>Expected result:</p>
     * <p>Store View is not created.</p>
     * <p>Error Message is displayed.</p>
     *
     * @param array $storeViewData
     * @test
     * @depends withRequiredFieldsOnly
     * @TestlinkId	TL-MAGE-3624
     */
    public function withCodeThatAlreadyExists(array $storeViewData)
    {
        //Steps
        $this->storeHelper()->createStore($storeViewData, 'store_view');
        //Verifying
        $this->assertMessagePresent('error', 'store_view_code_exist');
    }

    /**
     * <p>Create Store View. Fill in  required fields except one field.</p>
     * <p>Steps:</p>
     * <p>1. Click 'Create Store View' button.</p>
     * <p>2. Fill in required fields except one field.</p>
     * <p>3. Click 'Save Store View' button.</p>
     * <p>Expected result:</p>
     * <p>Store View is not created.</p>
     * <p>Error Message is displayed.</p>
     *
     * @param $emptyField
     *
     * @test
     * @dataProvider withRequiredFieldsEmptyDataProvider
     * @depends withRequiredFieldsOnly
     * @TestlinkId    TL-MAGE-3627
     */
    public function withRequiredFieldsEmpty($emptyField)
    {
        //Data
        $storeViewData = $this->loadData('generic_store_view', array($emptyField => '%noValue%'));
        //Steps
        $this->storeHelper()->createStore($storeViewData, 'store_view');
        //Verifying
        $xpath = $this->_getControlXpath('field', $emptyField);
        $this->addParameter('fieldXpath', $xpath);
        $this->assertMessagePresent('error', 'empty_required_field');
        $this->assertTrue($this->verifyMessagesCount(), $this->getParsedMessages());
    }

    public function withRequiredFieldsEmptyDataProvider()
    {
        return array(
            array('store_view_name'),
            array('store_view_code'),
        );
    }

    /**
     * <p>Create Store View. Fill in only required fields. Use max long values for fields 'Name' and 'Code'</p>
     * <p>Steps:</p>
     * <p>1. Click 'Create Store View' button.</p>
     * <p>2. Fill in required fields by long value alpha-numeric data.</p>
     * <p>3. Click 'Save Store View' button.</p>
     * <p>Expected result:</p>
     * <p>Store View is created. Success Message is displayed.</p>
     * <p>Length of field "Name" is 255 characters. Length of field "Code" is 32 characters.</p>
     *
     * @test
     * @depends withRequiredFieldsOnly
     * @TestlinkId	TL-MAGE-3626
     */
    public function withLongValues()
    {
        //Data
        $longValues = array(
            'store_view_name' => $this->generate('string', 255, ':alnum:'),
            'store_view_code' => $this->generate('string', 32, ':lower:')
        );
        $storeViewData = $this->loadData('generic_store_view', $longValues);
        //Steps
        $this->storeHelper()->createStore($storeViewData, 'store_view');
        //Verifying
        $this->assertMessagePresent('success', 'success_saved_store_view');
    }

    /**
     * <p>Create Store View. Fill in field 'Name' by using special characters.</p>
     * <p>Steps:</p>
     * <p>1. Click 'Create Store View' button.</p>
     * <p>2. Fill in 'Name' field by special characters.</p>
     * <p>3. Fill other required fields by regular data.</p>
     * <p>4. Click 'Save Store View' button.</p>
     * <p>Expected result:</p>
     * <p>Store View is created.</p>
     * <p>Success Message is displayed</p>
     *
     * @test
     * @depends withRequiredFieldsOnly
     * @TestlinkId	TL-MAGE-3630
     */
    public function withSpecialCharactersInName()
    {
        //Data
        $storeViewData = $this->loadData('generic_store_view',
                array('store_view_name' => $this->generate('string', 32, ':punct:')));
        //Steps
        $this->storeHelper()->createStore($storeViewData, 'store_view');
        //Verifying
        $this->assertMessagePresent('success', 'success_saved_store_view');
    }

    /**
     * <p>Create Store View.  Fill in field 'Code' by using special characters.</p>
     * <p>Steps:</p>
     * <p>1. Click 'Create Store View' button.</p>
     * <p>2. Fill in 'Code' field by special characters.</p>
     * <p>3. Fill other required fields by regular data.</p>
     * <p>4. Click 'Save Store View' button.</p>
     * <p>Expected result:</p>
     * <p>Store View is not created.</p>
     * <p>Error Message is displayed.</p>
     *
     * @test
     * @depends withRequiredFieldsOnly
     * @TestlinkId TL-MAGE-3629
     */
    public function withSpecialCharactersInCode()
    {
        //Data
        $storeViewData = $this->loadData('generic_store_view',
                array('store_view_code' => $this->generate('string', 32, ':punct:')));
        //Steps
        $this->storeHelper()->createStore($storeViewData, 'store_view');
        //Verifying
        $this->assertMessagePresent('error', 'wrong_store_view_code');
    }

    /**
     * <p>Create Store View.  Fill in field 'Code' by using wrong values.</p>
     * <p>Steps:</p>
     * <p>1. Click 'Create Store View' button.</p>
     * <p>2. Fill in 'Code' field by wrong value.</p>
     * <p>3. Fill other required fields by regular data.</p>
     * <p>4. Click 'Save Store View' button.</p>
     * <p>Expected result:</p>
     * <p>Store View is not created.</p>
     * <p>Error Message is displayed.</p>
     *
     * @param $invalidCode
     *
     * @test
     * @dataProvider withInvalidCodeDataProvider
     * @depends withRequiredFieldsOnly
     * @TestlinkId    TL-MAGE-3625
     */
    public function withInvalidCode($invalidCode)
    {
        //Data
        $storeViewData = $this->loadData('generic_store_view', array('store_view_code' => $invalidCode));
        //Steps
        $this->storeHelper()->createStore($storeViewData, 'store_view');
        //Verifying
        $this->assertMessagePresent('error', 'wrong_store_view_code');
    }

    public function withInvalidCodeDataProvider()
    {
        return array(
            array('invalid code'),
            array('Invalid_code2'),
            array('2invalid_code2')
        );
    }
}