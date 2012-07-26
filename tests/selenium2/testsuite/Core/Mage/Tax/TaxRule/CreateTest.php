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
 * Tax Rule creation tests
 *
 * @package     selenium
 * @subpackage  tests
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class Core_Mage_Tax_TaxRule_CreateTest extends Mage_Selenium_TestCase
{
    /**
     * <p>Save rule name for clean up</p>
     */
    protected $_ruleToBeDeleted = null;

    /**
     * <p>Login to backend</p>
     */
    public function setUpBeforeTests()
    {
        $this->loginAdminUser();
    }

    /**
     * <p>Preconditions:</p>
     * <p>Navigate to Sales->Tax->Manage Tax Rules</p>
     */
    protected function assertPreConditions()
    {
        $this->navigate('manage_tax_rule');
    }

    protected function tearDownAfterTest()
    {
        //Remove Tax rule after test
        if (!is_null($this->_ruleToBeDeleted)) {
            $this->navigate('manage_tax_rule');
            $this->taxHelper()->deleteTaxItem($this->_ruleToBeDeleted, 'rule');
            $this->_ruleToBeDeleted = null;
        }
    }

    /**
     * <p>Create Tax Rate for tests<p>
     *
     * @return string $taxRateData
     *
     * @test
     * @group preConditions
     */
    public function setupTestDataCreateTaxRate()
    {
        //Data
        $taxRateData = $this->loadData('tax_rate_create_test');
        //Steps
        $this->navigate('manage_tax_zones_and_rates');
        $this->taxHelper()->createTaxItem($taxRateData, 'rate');
        //Verifying
        $this->assertMessagePresent('success', 'success_saved_tax_rate');
        return $taxRateData['tax_identifier'];
    }
    /**
     * <p>Create Product Tax class Core_Mage_for tests</p>
     *
     * @return string $productTaxClassData
     * @group preConditions
     * @test
     */
    public function setupTestDataCreateTaxRule()
    {
        //Data
        $productTaxClassData = $this->loadData('new_product_tax_class');
        //Steps
        $this->navigate('manage_product_tax_class');
        $this->taxHelper()->createTaxItem($productTaxClassData, 'product_class');
        //Verifying
        $this->assertMessagePresent('success', 'success_saved_tax_class');
        return $productTaxClassData['product_class_name'];
    }

    /**
     * <p>Creating Tax Rule with required fields</p>
     * <p>Steps</p>
     * <p>1. Click "Add New Tax Rule" button </p>
     * <p>2. Fill in required fields</p>
     * <p>3. Click "Save Rule" button</p>
     * <p>Expected Result:</p>
     * <p>Tax Rule created, success message appears</p>
     *
     * @depends setupTestDataCreateTaxRate
     * @depends setupTestDataCreateTaxRule
     * @param array $taxRateData
     * @param array $productTaxClassData
     * @return array $taxRuleData
     * @test
     */
    public function withRequiredFieldsOnly($taxRateData, $productTaxClassData)
    {
        //Data
        $taxRuleData = $this->loadData('new_tax_rule_required', array(
            'tax_rate' => $taxRateData, 'product_tax_class' => $productTaxClassData));
        $searchTaxRuleData = $this->loadData('search_tax_rule', array('filter_name' => $taxRuleData['name']));
        //Steps
        $this->taxHelper()->createTaxItem($taxRuleData, 'rule');
        //Verifying
        $this->assertMessagePresent('success', 'success_saved_tax_rule');
        //Steps
        $this->taxHelper()->openTaxItem($searchTaxRuleData, 'rule');
        //Verifying
        $this->assertTrue($this->verifyForm($taxRuleData), $this->getParsedMessages());
        return $taxRuleData;
    }

    /**
     * <p>Creating Tax Rule with name that exists</p>
     * <p>Steps</p>
     * <p>1. Click "Add New Tax Rule" button </p>
     * <p>2. Fill in Name with value that exists</p>
     * <p>3. Click "Save Rule" button</p>
     * <p>Expected Result:</p>
     * <p>Tax Rule should not be created, error message appears</p>
     *
     * @depends withRequiredFieldsOnly
     * @param array $taxRuleData
     * @test
     */
    public function withNameThatAlreadyExists($taxRuleData)
    {
        //Data
        $searchTaxRuleData = $this->loadData('search_tax_rule', array('filter_name' => $taxRuleData['name']));
        $this->_ruleToBeDeleted = $searchTaxRuleData;
        //Steps
        $this->taxHelper()->createTaxItem($taxRuleData, 'rule');
        //Verifying
        $this->assertMessagePresent('error', 'code_already_exists');
    }

    /**
     * <p>Creating a Tax Rule with empty required fields.</p>
     * <p>Steps:</p>
     * <p>1. Click button "Add New Tax Rule"</p>
     * <p>2. Fill in the fields, but leave one required field empty;</p>
     * <p>3. Click button "Save Rule".</p>
     * <p>Expected result:</p>
     * <p>Received error message</p>
     *
     * @depends setupTestDataCreateTaxRate
     * @depends setupTestDataCreateTaxRule
     * @dataProvider withEmptyRequiredFieldsDataProvider
     * @param string $emptyFieldName Name of the field to leave empty
     * @param string $fieldType Type of the field to leave empty
     * @param string $taxRateData
     * @param string $productTaxClassData
     *
     * @test
     *
     * @group skip_due_to_bug
     */
    public function withEmptyRequiredFields($emptyFieldName, $fieldType, $taxRateData, $productTaxClassData)
    {
        //Data
        $taxRuleData = $this->loadData('new_tax_rule_required', array(
            'tax_rate' => $taxRateData,
            'product_tax_class' => $productTaxClassData,
            $emptyFieldName => ''));
        //Steps
        $this->taxHelper()->createTaxItem($taxRuleData, 'rule');
        //Verifying
        $this->addFieldIdToMessage($fieldType, $emptyFieldName);
        $this->assertMessagePresent('validation', 'empty_required_field');
        $this->assertTrue($this->verifyMessagesCount(), $this->getParsedMessages());
    }

    public function withEmptyRequiredFieldsDataProvider()
    {
        return array(
            array('name', 'field'),
            array('customer_tax_class', 'multiselect'),
            array('product_tax_class', 'multiselect'),
            array('tax_rate', 'multiselect'),
            array('priority', 'field'),
            array('sort_order', 'field')
        );
    }

    /**
     * Fails because of MAGE-5237
     * <p>Creating a new Tax Rule with special values (long, special chars).</p>
     * <p>Steps:</p>
     * <p>1. Click button "Add New Tax Rule"</p>
     * <p>2. Fill in the fields</p>
     * <p>3. Click button "Save Rule"</p>
     * <p>4. Open the Tax Rule</p>
     * <p>Expected result:</p>
     * <p>All fields has the same values.</p>
     *
     * @depends setupTestDataCreateTaxRate
     * @depends setupTestDataCreateTaxRule
     * @dataProvider withSpecialValuesDataProvider
     * @param array $taxRateData
     * @param array $productTaxClassData
     * @param array $specialValue
     *
     * @test
     *
     * @group skip_due_to_bug
     */
    public function withSpecialValues($specialValue, $taxRateData, $productTaxClassData)
    {
        //Data
        $taxRuleData = $this->loadData('new_tax_rule_required', array(
            'tax_rate' => $taxRateData,
            'name' => $specialValue,
            'product_tax_class' => $productTaxClassData));
        $searchTaxRuleData = $this->loadData('search_tax_rule', array('filter_name' => $taxRuleData['name']));
        //Steps
        $this->taxHelper()->createTaxItem($taxRuleData, 'rule');
        //Verifying
        $this->assertMessagePresent('success', 'success_saved_tax_rule');
        $this->_ruleToBeDeleted = $searchTaxRuleData;
        //Steps
        $this->taxHelper()->openTaxItem($searchTaxRuleData, 'rule');
        //Verifying
        $this->assertTrue($this->verifyForm($taxRuleData), $this->getParsedMessages());
    }

    public function withSpecialValuesDataProvider()
    {
        return array(
            array($this->generate('string', 255)),
            array($this->generate('string', 50, ':punct:'))
        );
    }

    /**
     * <p>Creating a new Tax Rule with invalid values for Priority.</p>
     * <p>Steps:</p>
     * <p>1. Click button "Add New Tax Rule"</p>
     * <p>2. Fill in the Priority field with invalid value</p>
     * <p>3. Click button "Save Rule"</p>
     * <p>Expected result:</p>
     * <p>Error message: Please enter a valid number in this field.</p>
     *
     * @depends setupTestDataCreateTaxRate
     * @depends setupTestDataCreateTaxRule
     * @dataProvider invalidValuesDataProvider
     * @test
     *
     * @param array $taxRateData
     * @param array $productTaxClassData
     * @param array $specialValue
     */
    public function withInvalidValuesForPriority($specialValue, $taxRateData, $productTaxClassData)
    {
        //Data
        $taxRuleData = $this->loadData('new_tax_rule_required', array(
            'tax_rate' => $taxRateData,
            'priority' => $specialValue,
            'product_tax_class' => $productTaxClassData));
        //Steps
        $this->taxHelper()->createTaxItem($taxRuleData, 'rule');
        //Verifying
        $this->addFieldIdToMessage('field', 'priority');
        $this->assertMessagePresent('error', 'enter_not_negative_number');
    }

    /**
     * <p>Creating a new Tax Rule with invalid values for Sort Order.</p>
     * <p>Steps:</p>
     * <p>1. Click button "Add New Tax Rule"</p>
     * <p>2. Fill in the Sort Order field with invalid value</p>
     * <p>3. Click button "Save Rule"</p>
     * <p>Expected result:</p>
     * <p>Error message: Please enter a valid number in this field.</p>
     *
     * @depends setupTestDataCreateTaxRate
     * @depends setupTestDataCreateTaxRule
     * @dataProvider invalidValuesDataProvider
     * @test
     *
     * @param array $taxRateData
     * @param array $productTaxClassData
     * @param array $specialValue
     */
    public function withInvalidValuesForSortOrder($specialValue, $taxRateData, $productTaxClassData)
    {
        //Data
        $taxRuleData = $this->loadData('new_tax_rule_required', array(
            'tax_rate' => $taxRateData,
            'sort_order' => $specialValue,
            'product_tax_class' => $productTaxClassData));
        //Steps
        $this->taxHelper()->createTaxItem($taxRuleData, 'rule');
        //Verifying
        $this->addFieldIdToMessage('field', 'sort_order');
        $this->assertMessagePresent('error', 'enter_not_negative_number');
    }

    public function invalidValuesDataProvider()
    {
        return array(
            array($this->generate('string', 50, ':alpha:')),
            array($this->generate('string', 50, ':punct:'))
        );
    }
}