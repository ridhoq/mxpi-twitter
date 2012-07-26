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
 * Tax Rate creation tests
 *
 * @package     selenium
 * @subpackage  tests
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class Core_Mage_Tax_TaxRate_CreateTest extends Mage_Selenium_TestCase
{
    /**
     * Transfer data between tests.
     * Note: "@depends" does not help in this case
     */
    protected static $_storedTaxRateData = null;

    /**
     * <p>Login to backend</p>
     */
    public function setUpBeforeTests()
    {
        $this->loginAdminUser();
    }

    /**
     * <p>Preconditions:</p>
     * <p>Navigate to Sales->Tax->Manage Tax Zones&Rates</p>
     */
    protected function assertPreConditions()
    {
        $this->navigate('manage_tax_zones_and_rates');
    }

    /**
     * <p>Creating Tax Rate with required fields</p>
     * <p>Steps</p>
     * <p>1. Click "Add New Tax Rate" button </p>
     * <p>2. Fill in required fields</p>
     * <p>3. Click "Save Rate" button</p>
     * <p>Expected Result:</p>
     * <p>Tax Rate created, success message appears</p>
     *
     * @param string $taxRateDataSetName
     *
     * @return array $taxRateData
     * @test
     * @dataProvider withRequiredFieldsOnlyDataProvider
     * @TestlinkId TL-MAGE-3506
     */
    public function withRequiredFieldsOnly($taxRateDataSetName)
    {
        //Data
        $taxRateData = $this->loadData($taxRateDataSetName);
        $searchTaxRateData = $this->loadData('search_tax_rate',
                array('filter_tax_id' => $taxRateData['tax_identifier']));
        //Steps
        $this->taxHelper()->createTaxItem($taxRateData, 'rate');
        //Verifying
        $this->assertMessagePresent('success', 'success_saved_tax_rate');
        //Steps
        $this->taxHelper()->openTaxItem($searchTaxRateData, 'rate');
        //Verifying
        $this->assertTrue($this->verifyForm($taxRateData), $this->getParsedMessages());
        self::$_storedTaxRateData = $taxRateData;
    }

    public function withRequiredFieldsOnlyDataProvider()
    {
        return array(
            array('tax_rate_create_test_zip_no'), // Zip/Post is Range => No
            array('tax_rate_create_test_zip_yes') // Zip/Post is Range => Yes
        );
    }

    /**
     * <p>Creating Tax Rate with name that exists</p>
     * <p>Steps</p>
     * <p>1. Click "Add New Tax Rate" button </p>
     * <p>2. Fill in Tax Identifier with value that exists</p>
     * <p>3. Click "Save Rate" button</p>
     * <p>Expected Result:</p>
     * <p>Tax Rate should not be created, error message appears</p>
     *
     * @depends withRequiredFieldsOnly
     * @test
     */
    public function withTaxIdentifierThatAlreadyExists()
    {
        //Steps
        $this->taxHelper()->createTaxItem(self::$_storedTaxRateData, 'rate');
        //Verifying
        $this->assertMessagePresent('error', 'code_already_exists');
    }

    /**
     * <p>Creating a Tax Rate with empty required fields.</p>
     * <p>Steps:</p>
     * <p>1. Click button "Add New Tax Rate"</p>
     * <p>2. Fill in the fields, but leave one required field empty;</p>
     * <p>3. Click button "Save Rate".</p>
     * <p>Expected result:</p>
     * <p>Received error message "This is a required field."</p>
     *
     * @param string $emptyFieldName Name of the field to leave empty
     *
     * @test
     * @dataProvider withEmptyRequiredFieldsDataProvider
     * @TestlinkId    TL-MAGE-3506
     * @group skip_due_to_bug
     */
    public function withEmptyRequiredFields($emptyFieldName)
    {
        //Data
        $taxRateData = $this->loadData('tax_rate_create_test_zip_yes', array($emptyFieldName => ''));
        //Steps
        $this->taxHelper()->createTaxItem($taxRateData, 'rate');
        //Verifying
        $this->addFieldIdToMessage('field', $emptyFieldName);
        $this->assertMessagePresent('error', 'empty_required_field');
    }

    public function withEmptyRequiredFieldsDataProvider()
    {
        return array(
            array('tax_identifier'),
            array('rate_percent'),
            array('zip_range_from'),
            array('zip_range_to')
        );
    }

    /**
     * Fails because of MAGE-5237
     * <p>Creating a new Tax Rate with special values (long, special chars).</p>
     * <p>Steps:</p>
     * <p>1. Click button "Add New Tax Rate"</p>
     * <p>2. Fill in the fields</p>
     * <p>3. Click button "Save Rate"</p>
     * <p>4. Open the Tax Rate</p>
     * <p>Expected result:</p>
     * <p>All fields has the same values.</p>
     *
     * @param array $specialValue
     *
     * @test
     * @dataProvider withSpecialValuesDataProvider
     * @TestlinkId TL-MAGE-3509
     * @group skip_due_to_bug
     */
    public function withSpecialValues($specialValue)
    {
        //Data
        $taxRateData = $this->loadData('tax_rate_create_test_zip_no', array('tax_identifier' => $specialValue));
        $searchTaxRateData = $this->loadData('search_tax_rate',
                array('filter_tax_id' => $taxRateData['tax_identifier']));
        //Steps
        $this->taxHelper()->createTaxItem($taxRateData, 'rate');
        //Verifying
        $this->assertMessagePresent('success', 'success_saved_tax_rate');
        //Steps
        $this->taxHelper()->openTaxItem($searchTaxRateData, 'rate');
        //Verifying
        $this->assertTrue($this->verifyForm($taxRateData), $this->getParsedMessages());
    }

    public function withSpecialValuesDataProvider()
    {
        return array(
            array($this->generate('string', 255)),
            array($this->generate('string', 50, ':punct:'))
        );
    }

    /**
     * <p>Creating a new Tax Rate with invalid values for Range From\To.</p>
     * <p>Steps:</p>
     * <p>1. Click button "Add New Tax Rate"</p>
     * <p>2. Fill in the fields Range From\To with invalid value</p>
     * <p>3. Click button "Save Rate"</p>
     * <p>Expected result:</p>
     * <p>Please use numbers only in this field. Please avoid spaces or other characters such as dots or commas.</p>
     *
     * @param array $specialValue
     *
     * @test
     * @dataProvider withInvalidValuesForRangeDataProvider
     * @depends withRequiredFieldsOnly
     * @TestlinkId    TL-MAGE-3508
     */
    public function withInvalidValuesForRange($specialValue)
    {
        //Data
        $taxRateData = $this->loadData('tax_rate_create_test_zip_yes',
                array('zip_range_from' => $specialValue, 'zip_range_to' => $specialValue));
        //Steps
        $this->taxHelper()->createTaxItem($taxRateData, 'rate');
        //Verifying
        $this->addFieldIdToMessage('field', 'zip_range_from');
        $this->assertMessagePresent('error', 'enter_valid_digits');
        $this->addFieldIdToMessage('field', 'zip_range_from');
        $this->assertMessagePresent('error', 'enter_valid_digits');
    }

    public function withInvalidValuesForRangeDataProvider()
    {
        return array(
            array($this->generate('string', 50)), // string
            array($this->generate('string', 25, ':digit:') . " "
                . $this->generate('string', 25, ':digit:')), // Number with space
            array($this->generate('string', 50, ':punct:')) // special chars
        );
    }

    /**
     * <p>Creating a new Tax Rate with invalid values for Rate Percent.</p>
     * <p>Steps:</p>
     * <p>1. Click button "Add New Tax Rate"</p>
     * <p>2. Fill in the field Rate Percent with invalid value</p>
     * <p>3. Click button "Save Rate"</p>
     * <p>Expected result:</p>
     * <p>Error message: Please enter a valid number in this field.</p>
     *
     * @param array $specialValue
     *
     * @test
     * @dataProvider withInvalidValueForRatePercentDataProvider
     * @depends withRequiredFieldsOnly
     * @TestlinkId    TL-MAGE-3507
     */
    public function withInvalidValueForRatePercent($specialValue)
    {
        //Data
        $taxRateData = $this->loadData('tax_rate_create_test_zip_yes', array('rate_percent' => $specialValue));
        //Steps
        $this->taxHelper()->createTaxItem($taxRateData, 'rate');
        //Verifying
        $this->addFieldIdToMessage('field', 'rate_percent');
        $this->assertMessagePresent('error', 'enter_not_negative_number');
    }

    public function withInvalidValueForRatePercentDataProvider()
    {
        return array(
            array($this->generate('string', 50, ':alpha:')),
            array($this->generate('string', 50, ':punct:'))
        );
    }

    /**
     * <p>Creating a new Tax Rate with State.</p>
     * <p>Steps:</p>
     * <p>1. Click button "Add New Tax Rate"</p>
     * <p>2. Fill in the fields, select value for State</p>
     * <p>3. Click button "Save Rate"</p>
     * <p>4. Open the Tax Rate</p>
     * <p>Expected result:</p>
     * <p>All fields have the same values.</p>
     *
     * @test
     * @depends withRequiredFieldsOnly
     * @TestlinkId TL-MAGE-3505
     */
    public function withSelectedState()
    {
        //Data
        $taxRateData = $this->loadData('tax_rate_create_with_custom_state');
        $searchTaxRateData = $this->loadData('search_tax_rate',
                array('filter_tax_id' => $taxRateData['tax_identifier']));
        //Steps
        $this->taxHelper()->createTaxItem($taxRateData, 'rate');
        //Verifying
        $this->assertMessagePresent('success', 'success_saved_tax_rate');
        //Steps
        $this->taxHelper()->openTaxItem($searchTaxRateData, 'rate');
        //Verifying
        $this->assertTrue($this->verifyForm($taxRateData), $this->getParsedMessages());
    }

    /**
     * <p>Creating a new Tax Rate with custom store view titles.</p>
     * <p>Preconditions:</p>
     * <p>1. Ceate a new store view</p>
     * <p>Steps:</p>
     * <p>1. Click button "Add New Tax Rate"</p>
     * <p>2. Fill in the fields, select title for the default and created store views</p>
     * <p>3. Click button "Save Rate"</p>
     * <p>4. Open the Tax Rate</p>
     * <p>Expected result:</p>
     * <p>All fields have the same values.</p>
     * <p>Cleanup:</p>
     * <p>Delete the created store view.</p>
     *
     * @test
     * @depends withRequiredFieldsOnly
     */
    public function withStoreViewTitle()
    {
        //Preconditions
        $this->navigate('manage_stores');
        $storeViewData = $this->loadData('generic_store_view');
        $this->storeHelper()->createStore($storeViewData, 'store_view');
        $this->assertMessagePresent('success', 'success_saved_store_view');
        //Data
        $storeViewName = $storeViewData['store_view_name'];
        $taxRateData = $this->loadData('tax_rate_create_with_store_views');
        $taxRateData['tax_titles'][$storeViewName] = 'tax rate title for ' . $storeViewName;
        $searchTaxRateData = $this->loadData('search_tax_rate',
                array('filter_tax_id' => $taxRateData['tax_identifier']));
        //Steps
        $this->navigate('manage_tax_zones_and_rates');
        $this->taxHelper()->createTaxItem($taxRateData, 'rate');
        $this->assertMessagePresent('success', 'success_saved_tax_rate');
        $this->taxHelper()->openTaxItem($searchTaxRateData, 'rate');
        //Verification
        $this->assertTrue($this->verifyForm($taxRateData), $this->getParsedMessages());
    }
}