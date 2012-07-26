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
 * Create new product attribute. Type: Multiple Select
 *
 * @package     selenium
 * @subpackage  tests
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class Core_Mage_ProductAttribute_Create_MultiSelectTest extends Mage_Selenium_TestCase
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
     * <p>Navigate to System -> Manage Attributes.</p>
     */
    protected function assertPreConditions()
    {
        $this->navigate('manage_attributes');
        $this->addParameter('id', 0);
    }

    /**
     * @test
     */
    public function navigation()
    {
        $this->assertTrue($this->buttonIsPresent('add_new_attribute'),
                'There is no "Add New Attribute" button on the page');
        $this->clickButton('add_new_attribute');
        $this->assertTrue($this->checkCurrentPage('new_product_attribute'), $this->getParsedMessages());
        $this->assertTrue($this->buttonIsPresent('back'), 'There is no "Back" button on the page');
        $this->assertTrue($this->buttonIsPresent('reset'), 'There is no "Reset" button on the page');
        $this->assertTrue($this->buttonIsPresent('save_attribute'), 'There is no "Save" button on the page');
        $this->assertTrue($this->buttonIsPresent('save_and_continue_edit'),
                'There is no "Save and Continue Edit" button on the page');
    }

    /**
     * <p>Create "Multiple Select" type Product Attribute (required fields only)</p>
     * <p>Steps:</p>
     * <p>1.Click on "Add New Attribute" button</p>
     * <p>2.Choose "Multiple Select" in 'Catalog Input Type for Store Owner' Multiple Select</p>
     * <p>3.Fill all required fields</p>
     * <p>4.Click on "Save Attribute" button</p>
     * <p>Expected result:</p>
     * <p>New attribute ["Multiple Select" type] successfully created.</p>
     * <p>Success message: 'The product attribute has been saved.' is displayed.</p>
     *
     * @return array
     * @test
     * @depends navigation
     * @TestlinkId TL-MAGE-3557
     */
    public function withRequiredFieldsOnly()
    {
        //Data
        $attrData = $this->loadData('product_attribute_multiselect', null, array('attribute_code', 'admin_title'));
        //Steps
        $this->productAttributeHelper()->createAttribute($attrData);
        //Verifying
        $this->assertMessagePresent('success', 'success_saved_attribute');

        return $attrData;
    }

    /**
     * <p>Checking of verification for duplicate of Product Attributes with similar code
     * Creation of new attribute with existing code.</p>
     * <p>Steps:</p>
     * <p>1.Click on "Add New Attribute" button</p>
     * <p>2.Choose "Multiple Select" in 'Catalog Input Type for Store Owner' Multiple Select</p>
     * <p>3.Fill 'Attribute Code' field by code used in test before.</p>
     * <p>4.Fill other required fields by regular data.</p>
     * <p>5.Click on "Save Attribute" button</p>
     * <p>Expected result:</p>
     * <p>New attribute ["Multiple Select" type] shouldn't be created.</p>
     * <p>Error message: 'Attribute with the same code already exists' is displayed.</p>
     *
     * @param array $attrData
     *
     * @test
     * @depends withRequiredFieldsOnly
     * @TestlinkId TL-MAGE-5353
     */
    public function withAttributeCodeThatAlreadyExists(array $attrData)
    {
        //Steps
        $this->productAttributeHelper()->createAttribute($attrData);
        //Verifying
        $this->assertMessagePresent('error', 'exists_attribute_code');
    }

    /**
     * <p>Checking validation for required fields are EMPTY</p>
     * <p>Steps:</p>
     * <p>1.Click on "Add New Attribute" button</p>
     * <p>2.Choose "Multiple Select" in 'Catalog Input Type for Store Owner' Multiple Select</p>
     * <p>3.Skip filling of one field required and fill other required fields.</p>
     * <p>4.Click on "Save Attribute" button</p>
     * <p>Expected result:</p>
     * <p>New attribute ["Multiple Select" type] shouldn't be created.</p>
     * <p>Error JS message: 'This is a required field.' is displayed.</p>
     *
     * @param $emptyField
     *
     * @test
     * @dataProvider withRequiredFieldsEmptyDataProvider
     * @depends withRequiredFieldsOnly.
     * @TestlinkId TL-MAGE-3557
     */
    public function withRequiredFieldsEmpty($emptyField)
    {
        //Data
        if ($emptyField == 'apply_to') {
            $attrData = $this->loadData('product_attribute_multiselect', array($emptyField => 'Selected Product Types'),
                    'attribute_code');
        } else {
            $attrData = $this->loadData('product_attribute_multiselect', array($emptyField => '%noValue%'),
                    'attribute_code');
        }
        //Steps
        $this->productAttributeHelper()->createAttribute($attrData);
        //Verifying
        if ($emptyField != 'apply_to') {
            $fieldXpath = $this->_getControlXpath('field', $emptyField);
        } else {
            $fieldXpath = $this->_getControlXpath('multiselect', 'apply_product_types');
        }
        $this->addParameter('fieldXpath', $fieldXpath);
        $this->assertMessagePresent('validation', 'empty_required_field');
        $this->assertTrue($this->verifyMessagesCount(), $this->getParsedMessages());
    }

    public function withRequiredFieldsEmptyDataProvider()
    {
        return array(
            array('attribute_code'),
            array('admin_title'),
            array('apply_to')
        );
    }

    /**
     * <p>Checking validation for valid data in the 'Attribute Code' field</p>
     * <p>Steps:</p>
     * <p>1.Click on "Add New Attribute" button</p>
     * <p>2.Choose "Multiple Select" in 'Catalog Input Type for Store Owner' Multiple Select</p>
     * <p>3.Fill 'Attribute Code' field by invalid data [Examples: '0xxx'/'_xxx'/'111']</p>
     * <p>4.Fill other required fields by regular data.</p>
     * <p>5.Click on "Save Attribute" button</p>
     * <p>Expected result:</p>
     * <p>New attribute ["Multiple Select" type] shouldn't be created.</p>
     * <p>Error JS message: 'Please use only letters (a-z), numbers (0-9) or underscore(_) in
     * this field, first character should be a letter.' is displayed.</p>
     *
     * @param $wrongAttributeCode
     * @param $validationMessage
     *
     * @test
     * @dataProvider withInvalidAttributeCodeDataProvider
     * @depends withRequiredFieldsOnly
     * @TestlinkId TL-MAGE-3554
     */
    public function withInvalidAttributeCode($wrongAttributeCode, $validationMessage)
    {
        //Data
        $attrData = $this->loadData('product_attribute_multiselect', array('attribute_code' => $wrongAttributeCode));
        //Steps
        $this->productAttributeHelper()->createAttribute($attrData);
        //Verifying
        $this->assertMessagePresent('validation', $validationMessage);
        $this->assertTrue($this->verifyMessagesCount(), $this->getParsedMessages());
    }

    public function withInvalidAttributeCodeDataProvider()
    {
        return array(
            array('11code_wrong', 'invalid_attribute_code'),
            array('CODE_wrong', 'invalid_attribute_code'),
            array('wrong code', 'invalid_attribute_code'),
            array($this->generate('string', 11, ':punct:'), 'invalid_attribute_code'),
            array($this->generate('string', 33, ':lower:'), 'wrong_length_attribute_code')
        );
    }

    /**
     * <p>Checking validation for notvalid data in the 'Position' field</p>
     * <p>Steps:</p>
     * <p>1.Click on "Add New Attribute" button.</p>
     * <p>2.Choose "Multiple Select" in 'Catalog Input Type for Store Owner' Multiple Select.</p>
     * <p>3.Fill 'Position' field by invalid data.</p>
     * <p>4.Fill other required fields by regular data.</p>
     * <p>5.Click on "Save Attribute" button</p>
     * <p>Expected result:</p>
     * <p>New attribute ["Multiple Select" type] shouldn't be created.</p>
     * <p>Error JS message: 'Please use numbers only in this field.</p>
     * <p>Please avoid spaces or other characters such as dots or commas.' is displayed.</p>
     *
     * @param $invalidPosition
     *
     * @test
     * @dataProvider withInvalidPositionDataProvider
     * @depends withRequiredFieldsOnly
     * @TestlinkId TL-MAGE-3555
     */
    public function withInvalidPosition($invalidPosition)
    {
        //Data
        $attrData = $this->loadData('product_attribute_multiselect', array('position' => $invalidPosition),
                'attribute_code');
        //Steps
        $this->productAttributeHelper()->createAttribute($attrData);
        //Verifying
        $this->assertMessagePresent('validation', 'error_invalid_position');
        $this->assertTrue($this->verifyMessagesCount(), $this->getParsedMessages());
    }

    public function withInvalidPositionDataProvider()
    {
        return array(
            array('11code'),
            array('CODE11'),
            array('11 11'),
            array('11.11'),
            array('11,11'),
            array($this->generate('string', 10, ':punct:'))
        );
    }

    /**
     * <p>Checking of correct validate of submitting form by using special
     * characters for all fields exclude 'Attribute Code' field.</p>
     * <p>Steps:</p>
     * <p>1.Click on "Add New Attribute" button</p>
     * <p>2.Choose "Multiple Select" in 'Catalog Input Type for Store Owner' Multiple Select</p>
     * <p>3.Fill 'Attribute Code' field by regular data.</p>
     * <p>4.Fill other required fields by special characters.</p>
     * <p>5.Click on "Save Attribute" button</p>
     * <p>Expected result:</p>
     * <p>New attribute ["Multiple Select" type] successfully created.</p>
     * <p>Success message: 'The product attribute has been saved.' is displayed.</p>
     *
     * @test
     * @depends withRequiredFieldsOnly
     * @TestlinkId TL-MAGE-5354
     */
    public function withSpecialCharactersInTitle()
    {
        //Data
        $attrData = $this->loadData('product_attribute_multiselect',
                array('admin_title' => $this->generate('string', 32, ':punct:')), 'attribute_code');
        $attrData['admin_title'] = preg_replace('/<|>/', '', $attrData['admin_title']);
        $searchData = $this->loadData('attribute_search_data', array('attribute_code' => $attrData['attribute_code']));
        //Steps
        $this->productAttributeHelper()->createAttribute($attrData);
        //Verifying
        $this->assertMessagePresent('success', 'success_saved_attribute');
        //Steps
        $this->productAttributeHelper()->openAttribute($searchData);
        //Verifying
        $this->productAttributeHelper()->verifyAttribute($attrData);
    }

    /**
     * <p>Checking of correct work of submitting form by using long values for fields filling</p>
     *
     * <p>Steps:</p>
     * <p>1.Click on "Add New Attribute" button</p>
     * <p>2.Choose "Multiple Select" in 'Catalog Input Type for Store Owner' Multiple Select</p>
     * <p>3.Fill all required fields by long value alpha-numeric data.</p>
     * <p>4.Click on "Save Attribute" button</p>
     * <p>Expected result:</p>
     * <p>New attribute ["Multiple Select" type] successfully created.</p>
     * <p>Success message: 'The product attribute has been saved.' is displayed.</p>
     *
     * @test
     * @depends withRequiredFieldsOnly
     * @TestlinkId TL-MAGE-3556
     */
    public function withLongValues()
    {
        //Data
        $attrData = $this->loadData('product_attribute_multiselect_with_options',
                array(
                    'attribute_code' => $this->generate('string', 30, ':lower:'),
                    'admin_title'    => $this->generate('string', 255, ':alnum:'),
                    'position'       => 2147483647
                )
        );
        $searchData = $this->loadData('attribute_search_data',
                array(
                    'attribute_code'  => $attrData['attribute_code'],
                    'attribute_lable' => $attrData['admin_title']
                )
        );
        //Steps
        $this->productAttributeHelper()->createAttribute($attrData);
        //Verifying
        $this->assertMessagePresent('success', 'success_saved_attribute');
        //Steps
        $this->productAttributeHelper()->openAttribute($searchData);
        //Verifying
        $this->productAttributeHelper()->verifyAttribute($attrData);
    }
}