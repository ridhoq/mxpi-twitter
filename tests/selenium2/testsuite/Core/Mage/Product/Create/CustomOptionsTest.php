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
 * Product creation with custom options tests
 *
 * @package     selenium
 * @subpackage  tests
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class Core_Mage_Product_Create_CustomOptionsTest extends Mage_Selenium_TestCase
{
    /**
     * <p>Login to backend</p>
     */
    public function setUpBeforeTests()
    {
        $this->loginAdminUser();
    }

    /**
     * <p>Preconditions</p>
     * <p>Navigate to Catalog->Manage Products</p>
     */
    protected function assertPreConditions()
    {
        $this->navigate('manage_products');
        $this->addParameter('id', '0');
    }

    /**
     * <p>Create product with custom options</p>
     * <p>Steps</p>
     * <p>1. Click "Add Product" button;</p>
     * <p>2. Fill in "Attribute Set", "Product Type" fields;</p>
     * <p>3. Click "Continue" button;</p>
     * <p>4. Fill in required fields with correct data;</p>
     * <p>5. Click "Custom Options" tab;</p>
     * <p>6. Add all types of options;</p>
     * <p>7. Click "Save" button;</p>
     * <p>Expected result:</p>
     * <p>Product is created, susses message appears;</p>
     *
     * @TestlinkId    TL-MAGE-3382
     * @test
     */
    public function productWithAllTypesCustomOption()
    {
        //Data
        $productData = $this->loadData('simple_product_required', null, array('general_sku', 'general_name'));
        $productData['custom_options_data'] = $this->loadData('custom_options_data');
        $productSearch = $this->loadData('product_search', array('product_sku' => $productData['general_sku']));
        //Steps
        $this->productHelper()->createProduct($productData);
        //Verifying
        $this->assertMessagePresent('success', 'success_saved_product');
        //Steps
        $this->productHelper()->openProduct($productSearch);
        //Verifying
        $this->productHelper()->verifyProductInfo($productData);
    }

    /**
     * <p>Create product with empty required field in custom options</p>
     * <p>Steps</p>
     * <p>1. Click "Add Product" button;</p>
     * <p>2. Fill in "Attribute Set", "Product Type" fields;</p>
     * <p>3. Click "Continue" button;</p>
     * <p>4. Fill in required fields with correct data;</p>
     * <p>5. Click "Custom Options" tab;</p>
     * <p>6. Click "Add New Option" button;</p>
     * <p>7. Leave one required field empty;</p>
     * <p>8. Click "Save" button;</p>
     * <p>Expected result:</p>
     * <p>Product is not created, error message appears;</p>
     *
     * @param $emptyCustomField
     *
     * @dataProvider emptyFieldInCustomOptionDataProvider
     *
     * @TestlinkId    TL-MAGE-3376
     * @test
     */
    public function emptyFieldInCustomOption($emptyCustomField)
    {
        //Data
        $productData = $this->loadData('simple_product_required', null, 'general_sku');
        $productData['custom_options_data'][] = $this->loadData('custom_options_empty',
                                                                array($emptyCustomField => '%noValue%'));
        //Steps
        $this->productHelper()->createProduct($productData);
        //Verifying
        if ($emptyCustomField == 'custom_options_general_title') {
            $this->addFieldIdToMessage('field', $emptyCustomField);
            $this->assertMessagePresent('validation', 'empty_required_field');
        } else {
            $this->assertMessagePresent('validation', 'select_type_of_option');
        }
        $this->assertTrue($this->verifyMessagesCount(), $this->getParsedMessages());
    }

    public function emptyFieldInCustomOptionDataProvider()
    {
        return array(
            array('custom_options_general_title'),
            array('custom_options_general_input_type')
        );
    }

    /**
     * <p>Create product with CustomOption: Empty field 'option row Title' if 'Input Type'='Select' type</p>
     * <p>Steps</p>
     * <p>1. Click "Add Product" button;</p>
     * <p>2. Fill in "Attribute Set", "Product Type" fields;</p>
     * <p>3. Click "Continue" button;</p>
     * <p>4. Fill in required fields with correct data;</p>
     * <p>5. Click "Custom Options" tab;</p>
     * <p>6. Click "Add New Option" button;</p>
     * <p>7. Select "Multipleselect" (or any other from Select type) into "Input Type" field;</p>
     * <p>7. Leave option row title empty;</p>
     * <p>8. Click "Save" button;</p>
     * <p>Expected result:</p>
     * <p>Product is not created, error message appears;</p>
     *
     * @param string $optionDataName
     *
     * @dataProvider emptyOptionRowTitleInCustomOptionDataProvider
     *
     * @TestlinkId    TL-MAGE-3377
     * @test
     */
    public function emptyOptionRowTitleInCustomOption($optionDataName)
    {
        //Data
        $productData = $this->loadData('simple_product_required', null, 'general_sku');
        $productData['custom_options_data'][] = $this->loadData($optionDataName,
                                                                array('custom_options_title' => '%noValue%'));
        //Steps
        $this->productHelper()->createProduct($productData);
        //Verifying
        $this->addFieldIdToMessage('field', 'custom_options_title');
        $this->assertMessagePresent('validation', 'empty_required_field');
        $this->assertTrue($this->verifyMessagesCount(), $this->getParsedMessages());
    }

    public function emptyOptionRowTitleInCustomOptionDataProvider()
    {
        return array(
            array('custom_options_dropdown'),
            array('custom_options_radiobutton'),
            array('custom_options_checkbox'),
            array('custom_options_multipleselect')
        );
    }

    /**
     * <p>Create product with invalid "Sort Order" into custom options</p>
     * <p>Steps</p>
     * <p>1. Click "Add Product" button;</p>
     * <p>2. Fill in "Attribute Set", "Product Type" fields;</p>
     * <p>3. Click "Continue" button;</p>
     * <p>4. Fill in required fields with correct data;</p>
     * <p>5. Click "Custom Options" tab;</p>
     * <p>6. Click "Add New Option" button;</p>
     * <p>7. Select "Multipleselect" into "Input Type" field;</p>
     * <p>8. Fill in "Sort Order" field with incorrect data;</p>
     * <p>9. Click "Save" button;</p>
     * <p>Expected result:</p>
     * <p>Product is not created, error message appears;</p>
     *
     * @param $invalidData
     *
     * @dataProvider invalidNumericValueDataProvider
     *
     * @TestlinkId    TL-MAGE-3379
     * @test
     */
    public function invalidSortOrderInCustomOption($invalidData)
    {
        //Data
        $invalidSortOrder = array(
            'custom_options_general_sort_order' => $invalidData,
            'custom_options_sort_order'         => $invalidData
        );
        $productData = $this->loadData('simple_product_required', null, 'general_sku');
        $productData['custom_options_data'][] = $this->loadData('custom_options_multipleselect', $invalidSortOrder);
        //Steps
        $this->productHelper()->createProduct($productData);
        //Verifying
        foreach ($invalidSortOrder as $key => $value) {
            $this->addFieldIdToMessage('field', $key);
            $this->assertMessagePresent('validation', 'enter_zero_or_greater');
        }
        $this->assertTrue($this->verifyMessagesCount(2), $this->getParsedMessages());
    }

    /**
     * <p>Create product custom option: use invalid value for field 'Max Characters'</p>
     * <p>Steps</p>
     * <p>1. Click "Add Product" button;</p>
     * <p>2. Fill in "Attribute Set", "Product Type" fields;</p>
     * <p>3. Click "Continue" button;</p>
     * <p>4. Fill in required fields with correct data;</p>
     * <p>5. Click "Custom Options" tab;</p>
     * <p>6. Click "Add New Option" button;</p>
     * <p>7. Select "Field" or "Area" into "Input Type" field;</p>
     * <p>8. Fill in "Max Characters" field with incorrect data;</p>
     * <p>9. Click "Save" button;</p>
     * <p>Expected result:</p>
     * <p>Product is not created, error message appears;</p>
     *
     * @param $invalidData
     *
     * @dataProvider invalidNumericValueDataProvider
     *
     * @TestlinkId    TL-MAGE-3378
     * @test
     */
    public function invalidMaxCharInCustomOption($invalidData)
    {
        //Data
        $productData = $this->loadData('simple_product_required', null, 'general_sku');
        $productData['custom_options_data'][] = $this->loadData('custom_options_field',
                                                                array('custom_options_max_characters' => $invalidData));
        //Steps
        $this->productHelper()->createProduct($productData);
        //Verifying
        $this->addFieldIdToMessage('field', 'custom_options_max_characters');
        $this->assertMessagePresent('validation', 'enter_zero_or_greater');
        $this->assertTrue($this->verifyMessagesCount(), $this->getParsedMessages());
    }

    public function invalidNumericValueDataProvider()
    {
        return array(
            array($this->generate('string', 9, ':punct:')),
            array($this->generate('string', 9, ':alpha:')),
            array('g3648GJHghj'),
            array('-128')
        );
    }

    /**
     * <p>Create product with Custom Option: Use special symbols for filling field 'Price'</p>
     * <p>Steps</p>
     * <p>1. Click "Add Product" button;</p>
     * <p>2. Fill in "Attribute Set", "Product Type" fields;</p>
     * <p>3. Click "Continue" button;</p>
     * <p>4. Fill in required fields with correct data;</p>
     * <p>5. Click "Custom Options" tab;</p>
     * <p>6. Click "Add New Option" button;</p>
     * <p>7. Select custom option type into "Input Type" field;</p>
     * <p>8. Fill in "Price" field with incorrect data;</p>
     * <p>9. Click "Save" button;</p>
     * <p>Expected result:</p>
     * <p>Product is not created, error message appears;</p>
     *
     * @param $optionDataName
     * @param $message
     *
     * @dataProvider invalidCustomOptionDataProvider
     *
     * @TestlinkId    TL-MAGE-3383
     * @test
     *
     * @group skip_due_to_bug
     */
    public function specialSymbolsInCustomOptionsPrice($optionDataName, $message)
    {
        //Data
        $productData = $this->loadData('simple_product_required', null, 'general_sku');
        $productData['custom_options_data'][] = $this->loadData($optionDataName,
                                                                array('custom_options_price' => $this->generate('string',
                                                                                                                9,
                                                                                                                ':punct:')));
        //Steps
        $this->productHelper()->createProduct($productData);
        //Verifying
        $this->addFieldIdToMessage('field', 'custom_options_price');
        $this->assertMessagePresent('validation', $message);
        $this->assertTrue($this->verifyMessagesCount(), $this->getParsedMessages());
    }

    /**
     * <p>Create product with Custom Option: Use text value for filling field 'Price'</p>
     * <p>Steps</p>
     * <p>1. Click "Add Product" button;</p>
     * <p>2. Fill in "Attribute Set", "Product Type" fields;</p>
     * <p>3. Click "Continue" button;</p>
     * <p>4. Fill in required fields with correct data;</p>
     * <p>5. Click "Custom Options" tab;</p>
     * <p>6. Click "Add New Option" button;</p>
     * <p>7. Select custom option type into "Input Type" field;</p>
     * <p>8. Fill in "Price" field with incorrect data;</p>
     * <p>9. Click "Save" button;</p>
     * <p>Expected result:</p>
     * <p>Product is not created, error message appears;</p>
     *
     * @param $optionDataName
     * @param $message
     *
     * @dataProvider invalidCustomOptionDataProvider
     *
     * @TestlinkId    TL-MAGE-3384
     * @test
     *
     * @group skip_due_to_bug
     */
    public function textValueInCustomOptionsPrice($optionDataName, $message)
    {
        //Data
        $productData = $this->loadData('simple_product_required', null, 'general_sku');
        $productData['custom_options_data'][] = $this->loadData($optionDataName,
                                                                array('custom_options_price' => $this->generate('string',
                                                                                                                9,
                                                                                                                ':alpha:')));
        //Steps
        $this->productHelper()->createProduct($productData);
        //Verifying
        $this->addFieldIdToMessage('field', 'custom_options_price');
        $this->assertMessagePresent('validation', $message);
        $this->assertTrue($this->verifyMessagesCount(), $this->getParsedMessages());
    }

    public function invalidCustomOptionDataProvider()
    {
        return array(
            array('custom_options_field', 'enter_valid_number'),
            array('custom_options_area', 'enter_valid_number'),
            array('custom_options_file', 'enter_zero_or_greater'), //Fails due to MAGE-4609
            array('custom_options_date', 'enter_zero_or_greater'),
            array('custom_options_date_time', 'enter_zero_or_greater'),
            array('custom_options_time', 'enter_zero_or_greater'),
            array('custom_options_dropdown', 'enter_valid_number'),
            array('custom_options_radiobutton', 'enter_valid_number'),
            array('custom_options_checkbox', 'enter_valid_number'),
            array('custom_options_multipleselect', 'enter_valid_number')
        );
    }

    /**
     * <p>Create product with Custom Option: Use negative number for filling field 'Price'</p>
     * <p>Steps</p>
     * <p>1. Click "Add Product" button;</p>
     * <p>2. Fill in "Attribute Set", "Product Type" fields;</p>
     * <p>3. Click "Continue" button;</p>
     * <p>4. Fill in required fields with correct data;</p>
     * <p>5. Click "Custom Options" tab;</p>
     * <p>6. Click "Add New Option" button;</p>
     * <p>7. Select custom option type into "Input Type" field;</p>
     * <p>8. Fill in "Price" field with incorrect data;</p>
     * <p>9. Click "Save" button;</p>
     * <p>Expected result:</p>
     * <p>Product is not created, error message appears;;</p>
     *
     * @param $optionName
     *
     * @dataProvider negativeNumberInCustomOptionsPriceNegDataProvider
     *
     * @TestlinkId    TL-MAGE-3380
     * @test
     *
     * @group skip_due_to_bug
     */
    public function negativeNumberInCustomOptionsPriceNeg($optionName)
    {
        //Data
        $productData = $this->loadData('simple_product_required', null, 'general_sku');
        $productData['custom_options_data'][] = $this->loadData($optionName, array('custom_options_price' => -123));
        //Steps
        $this->productHelper()->createProduct($productData);
        //Verifying
        $this->addFieldIdToMessage('field', 'custom_options_price');
        $this->assertMessagePresent('validation', 'enter_zero_or_greater');
        $this->assertTrue($this->verifyMessagesCount(), $this->getParsedMessages());
    }

    public function negativeNumberInCustomOptionsPriceNegDataProvider()
    {
        return array(
            array('custom_options_file'), //Fails due to MAGE-4609
            array('custom_options_date'),
            array('custom_options_date_time'),
            array('custom_options_time')
        );
    }

    /**
     * <p>Create product with Custom Option: Use negative number for filling field 'Price'</p>
     * <p>Steps</p>
     * <p>1. Click "Add Product" button;</p>
     * <p>2. Fill in "Attribute Set", "Product Type" fields;</p>
     * <p>3. Click "Continue" button;</p>
     * <p>4. Fill in required fields with correct data;</p>
     * <p>5. Click "Custom Options" tab;</p>
     * <p>6. Click "Add New Option" button;</p>
     * <p>7. Select custom option type into "Input Type" field;</p>
     * <p>8. Fill in "Price" field with incorrect data;</p>
     * <p>9. Click "Save" button;</p>
     * <p>Expected result:</p>
     * <p>Product is not created, error message appears;</p>
     *
     * @param $optionName
     *
     * @dataProvider negativeNumberInCustomOptionsPricePosDataProvider
     *
     * @TestlinkId    TL-MAGE-3381
     * @test
     */
    public function negativeNumberInCustomOptionsPricePos($optionName)
    {
        //Data
        $productData = $this->loadData('simple_product_required', null, 'general_sku');
        $productData['custom_options_data'][] = $this->loadData($optionName, array('custom_options_price' => -123));
        $productSearch = $this->loadData('product_search', array('product_sku' => $productData['general_sku']));
        //Steps
        $this->productHelper()->createProduct($productData);
        //Verifying
        $this->assertMessagePresent('success', 'success_saved_product');
        //Steps
        $this->productHelper()->openProduct($productSearch);
        //Verifying
        $this->productHelper()->verifyProductInfo($productData);
    }

    public function negativeNumberInCustomOptionsPricePosDataProvider()
    {
        return array(
            array('custom_options_field'),
            array('custom_options_area'),
            array('custom_options_dropdown'),
            array('custom_options_radiobutton'),
            array('custom_options_checkbox'),
            array('custom_options_multipleselect')
        );
    }
}