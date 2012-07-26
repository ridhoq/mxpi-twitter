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
 * Create new product attribute. Type: Media Image
 *
 * @package     selenium
 * @subpackage  tests
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class Core_Mage_ProductAttribute_Create_MediaImageTest extends Mage_Selenium_TestCase
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
     * <p>Create "Media Image" type Product Attribute (required fields only)</p>
     * <p>Steps:</p>
     * <p>1.Click on "Add New Attribute" button</p>
     * <p>2.Choose "Media Image" in 'Catalog Input Type for Store Owner' dropdown</p>
     * <p>3.Fill all required fields</p>
     * <p>4.Click on "Save Attribute" button</p>
     * <p>Expected result:</p>
     * <p>New attribute ["Media Image" type] successfully created.</p>
     * <p>Success message: 'The product attribute has been saved.' is displayed.</p>
     *
     * @return array
     * @test
     * @depends navigation
     * @TestlinkId TL-MAGE-3562
     */
    public function withRequiredFieldsOnly()
    {
        //Data
        $attrData = $this->loadData('product_attribute_mediaimage', null, array('attribute_code', 'admin_title'));
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
     * <p>2.Choose "Media Image" in 'Catalog Input Type for Store Owner' dropdown</p>
     * <p>3.Fill 'Attribute Code' field by code used in test before.</p>
     * <p>4.Fill other required fields by regular data.</p>
     * <p>5.Click on "Save Attribute" button</p>
     * <p>Expected result:</p>
     * <p>New attribute ["Media Image" type] shouldn't be created.</p>
     * <p>Error message: 'Attribute with the same code already exists' is displayed.</p>
     *
     * @param array $attrData
     *
     * @test
     * @depends withRequiredFieldsOnly
     * @TestlinkId TL-MAGE-5351
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
     * <p>2.Choose "Media Image" in 'Catalog Input Type for Store Owner' dropdown</p>
     * <p>3.Skip filling of one field required and fill other required fields.</p>
     * <p>4.Click on "Save Attribute" button</p>
     * <p>Expected result:</p>
     * <p>New attribute ["Media Image" type] shouldn't be created.</p>
     * <p>Error JS message: 'This is a required field.' is displayed.</p>
     *
     * @param $emptyField
     *
     * @test
     * @dataProvider withRequiredFieldsEmptyDataProvider
     * @depends withRequiredFieldsOnly.
     * @TestlinkId TL-MAGE-3561
     */
    public function withRequiredFieldsEmpty($emptyField)
    {
        //Data
        if ($emptyField == 'apply_to') {
            $attrData = $this->loadData('product_attribute_mediaimage', array($emptyField => 'Selected Product Types'),
                    'attribute_code');
        } else {
            $attrData = $this->loadData('product_attribute_mediaimage', array($emptyField => '%noValue%'),
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
     * <p>2.Choose "Media Image" in 'Catalog Input Type for Store Owner' dropdown</p>
     * <p>3.Fill 'Attribute Code' field by invalid data [Examples: '0xxx'/'_xxx'/'111']</p>
     * <p>4.Fill other required fields by regular data.</p>
     * <p>5.Click on "Save Attribute" button</p>
     * <p>Expected result:</p>
     * <p>New attribute ["Media Image" type] shouldn't be created.</p>
     * <p>Error JS message: 'Please use only letters (a-z), numbers (0-9) or underscore(_) in
     * this field, first character should be a letter.' is displayed.</p>
     *
     * @param $wrongAttributeCode
     * @param $validationMessage
     *
     * @test
     * @dataProvider withInvalidAttributeCodeDataProvider
     * @depends withRequiredFieldsOnly
     * @TestlinkId TL-MAGE-3559
     */
    public function withInvalidAttributeCode($wrongAttributeCode, $validationMessage)
    {
        //Data
        $attrData = $this->loadData('product_attribute_mediaimage', array('attribute_code' => $wrongAttributeCode));
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
     * <p>Checking of correct validate of submitting form by using special
     * characters for all fields exclude 'Attribute Code' field.</p>
     * <p>Steps:</p>
     * <p>1.Click on "Add New Attribute" button</p>
     * <p>2.Choose "Media Image" in 'Catalog Input Type for Store Owner' dropdown</p>
     * <p>3.Fill 'Attribute Code' field by regular data.</p>
     * <p>4.Fill other required fields by special characters.</p>
     * <p>5.Click on "Save Attribute" button</p>
     * <p>Expected result:</p>
     * <p>New attribute ["Media Image" type] successfully created.</p>
     * <p>Success message: 'The product attribute has been saved.' is displayed.</p>
     *
     * @test
     * @depends withRequiredFieldsOnly
     * @TestlinkId TL-MAGE-5352
     */
    public function withSpecialCharactersInTitle()
    {
        //Data
        $attrData = $this->loadData('product_attribute_mediaimage',
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
     * <p>Steps:</p>
     * <p>1.Click on "Add New Attribute" button</p>
     * <p>2.Choose "Media Image" in 'Catalog Input Type for Store Owner' dropdown</p>
     * <p>3.Fill all required fields by long value alpha-numeric data.</p>
     * <p>4.Click on "Save Attribute" button</p>
     * <p>Expected result:</p>
     * <p>New attribute ["Media Image" type] successfully created.</p>
     * <p>Success message: 'The product attribute has been saved.' is displayed.</p>
     *
     * @test
     * @depends withRequiredFieldsOnly
     * @TestlinkId TL-MAGE-3560
     */
    public function withLongValues()
    {
        //Data
        $attrData = $this->loadData('product_attribute_mediaimage',
                array(
                    'attribute_code' => $this->generate('string', 30, ':lower:'),
                    'admin_title'    => $this->generate('string', 255, ':alnum:')
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