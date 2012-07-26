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
 * Configurable product creation tests
 *
 * @package     selenium
 * @subpackage  tests
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class Core_Mage_Product_Create_ConfigurableTest extends Mage_Selenium_TestCase
{
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
        $this->navigate('manage_products');
        $this->addParameter('id', '0');
    }

    /**
     * Test Realizing precondition for creating configurable product.
     *
     * @test
     */
    public function createConfigurableAttribute()
    {
        //Data
        $attrData = $this->loadData('product_attribute_dropdown_with_options', null,
                                    array('admin_title', 'attribute_code'));
        $associatedAttributes = $this->loadData('associated_attributes',
                                                array('General' => $attrData['attribute_code']));
        //Steps
        $this->navigate('manage_attributes');
        $this->productAttributeHelper()->createAttribute($attrData);
        //Verifying
        $this->assertMessagePresent('success', 'success_saved_attribute');
        //Steps
        $this->navigate('manage_attribute_sets');
        $this->attributeSetHelper()->openAttributeSet();
        $this->attributeSetHelper()->addAttributeToSet($associatedAttributes);
        $this->saveForm('save_attribute_set');
        //Verifying
        $this->assertMessagePresent('success', 'success_attribute_set_saved');

        return $attrData;
    }

    /**
     * <p>Creating product with required fields only</p>
     * <p>Steps:</p>
     * <p>1. Click "Add product" button;</p>
     * <p>2. Fill in "Attribute Set" and "Product Type" fields;</p>
     * <p>3. Click "Continue" button;</p>
     * <p>4. Fill in required fields;</p>
     * <p>5. Click "Save" button;</p>
     * <p>Expected result:</p>
     * <p>Product is created, confirmation message appears;</p>
     *
     * @depends createConfigurableAttribute
     *
     * @TestlinkId    TL-MAGE-3374
     * @test
     */
    public function onlyRequiredFieldsInConfigurable($attrData)
    {
        //Data
        $productData = $this->loadData('configurable_product_required',
                                       array('configurable_attribute_title' => $attrData['admin_title']),
                                       array('general_sku', 'general_name'));
        //Steps
        $this->productHelper()->createProduct($productData, 'configurable');
        //Verifying
        $this->assertMessagePresent('success', 'success_saved_product');

        return $productData;
    }

    /**
     * <p>Creating product with all fields</p>
     * <p>Steps:</p>
     * <p>1. Click "Add product" button;</p>
     * <p>2. Fill in "Attribute Set" and "Product Type" fields;</p>
     * <p>3. Click "Continue" button;</p>
     * <p>4. Fill all fields;</p>
     * <p>5. Click "Save" button;</p>
     * <p>Expected result:</p>
     * <p>Product is created, confirmation message appears;</p>
     *
     * @depends createConfigurableAttribute
     *
     * @TestlinkId    TL-MAGE-3362
     * @test
     */
    public function allFieldsInConfigurable($attrData)
    {
        //Data
        $productData = $this->loadData('configurable_product',
                                       array('configurable_attribute_title' => $attrData['admin_title']),
                                       array('general_name', 'general_sku'));
        $productSearch = $this->loadData('product_search', array('product_sku' => $productData['general_sku']));
        //Steps
        $this->productHelper()->createProduct($productData, 'configurable');
        //Verifying
        $this->assertMessagePresent('success', 'success_saved_product');
        //Steps
        $this->productHelper()->openProduct($productSearch);
        //Verifying
        $this->productHelper()->verifyProductInfo($productData, array('configurable_attribute_title'));
    }

    /**
     * <p>Creating product with existing SKU</p>
     * <p>Steps:</p>
     * <p>1. Click "Add product" button;</p>
     * <p>2. Fill in "Attribute Set" and "Product Type" fields;</p>
     * <p>3. Click "Continue" button;</p>
     * <p>4. Fill in required fields using exist SKU;</p>
     * <p>5. Click "Save" button;</p>
     * <p>6. Verify error message;</p>
     * <p>Expected result:</p>
     * <p>Error message appears;</p>
     *
     * @depends onlyRequiredFieldsInConfigurable
     *
     * @TestlinkId    TL-MAGE-3368
     * @test
     */
    public function existSkuInConfigurable($productData)
    {
        //Steps
        $this->productHelper()->createProduct($productData, 'configurable');
        //Verifying
        $this->assertMessagePresent('validation', 'existing_sku');
        $this->assertTrue($this->verifyMessagesCount(), $this->getParsedMessages());
    }

    /**
     * <p>Creating product with empty required fields</p>
     * <p>Steps:</p>
     * <p>1. Click "Add product" button;</p>
     * <p>2. Fill in "Attribute Set" and "Product Type" fields;</p>
     * <p>3. Click "Continue" button;</p>
     * <p>4. Leave one required field empty and fill in the rest of fields;</p>
     * <p>5. Click "Save" button;</p>
     * <p>6. Verify error message;</p>
     * <p>7. Repeat scenario for all required fields for both tabs;</p>
     * <p>Expected result:</p>
     * <p>Product is not created, error message appears;</p>
     *
     * @dataProvider emptyRequiredFieldInConfigurableDataProvider
     * @depends createConfigurableAttribute
     *
     * @TestlinkId    TL-MAGE-3366
     * @test
     */
    public function emptyRequiredFieldInConfigurable($emptyField, $fieldType, $attrData)
    {
        //Data
        $overrideData = array('configurable_attribute_title' => $attrData['admin_title']);
        if ($emptyField == 'general_visibility') {
            $overrideData[$emptyField] = '-- Please Select --';
        } elseif ($emptyField == 'inventory_qty') {
            $overrideData[$emptyField] = '';
        } else {
            $overrideData[$emptyField] = '%noValue%';
        }
        $productData = $this->loadData('configurable_product_required', $overrideData,
                                       array('general_name', 'general_sku'));
        //Steps
        $this->productHelper()->createProduct($productData, 'configurable');
        //Verifying
        $this->addFieldIdToMessage($fieldType, $emptyField);
        $this->assertMessagePresent('validation', 'empty_required_field');
        $this->assertTrue($this->verifyMessagesCount(), $this->getParsedMessages());
    }

    public function emptyRequiredFieldInConfigurableDataProvider()
    {
        return array(
            array('general_name', 'field'),
            array('general_description', 'field'),
            array('general_short_description', 'field'),
            array('general_sku', 'field'),
            array('general_status', 'dropdown'),
            array('general_visibility', 'dropdown'),
            array('prices_price', 'field'),
            array('prices_tax_class', 'dropdown'),
        );
    }

    /**
     * <p>Creating product with special characters into required fields</p>
     * <p>Steps</p>
     * <p>1. Click "Add Product" button;</p>
     * <p>2. Fill in "Attribute Set", "Product Type" fields;</p>
     * <p>3. Click "Continue" button;</p>
     * <p>4. Fill in required fields with special symbols ("General" tab), rest - with normal data;
     * <p>5. Click "Save" button;</p>
     * <p>Expected result:</p>
     * <p>Product created, confirmation message appears</p>
     *
     * @depends createConfigurableAttribute
     *
     * @TestlinkId    TL-MAGE-3375
     * @test
     */
    public function specialCharactersInRequiredFields($attrData)
    {
        //Data
        $productData = $this->loadData('configurable_product_required',
                                       array(
                                            'configurable_attribute_title' => $attrData['admin_title'],
                                            'general_name'                 => $this->generate('string', 32, ':punct:'),
                                            'general_description'          => $this->generate('string', 32, ':punct:'),
                                            'general_short_description'    => $this->generate('string', 32, ':punct:'),
                                            'general_sku'                  => $this->generate('string', 32, ':punct:')
                                       ));
        $productSearch = $this->loadData('product_search', array('product_sku' => $productData['general_sku']));
        //Steps
        $this->productHelper()->createProduct($productData, 'configurable');
        //Verifying
        $this->assertMessagePresent('success', 'success_saved_product');
        //Steps
        $this->productHelper()->openProduct($productSearch);
        //Verifying
        $this->productHelper()->verifyProductInfo($productData, array('configurable_attribute_title'));
    }

    /**
     * <p>Creating product with long values from required fields</p>
     * <p>Steps</p>
     * <p>1. Click "Add Product" button;</p>
     * <p>2. Fill in "Attribute Set", "Product Type" fields;</p>
     * <p>3. Click "Continue" button;</p>
     * <p>4. Fill in required fields with long values ("General" tab), rest - with normal data;
     * <p>5. Click "Save" button;</p>
     * <p>Expected result:</p>
     * <p>Product created, confirmation message appears</p>
     *
     * @depends createConfigurableAttribute
     *
     * @TestlinkId    TL-MAGE-3373
     * @test
     */
    public function longValuesInRequiredFields($attrData)
    {
        //Data
        $productData = $this->loadData('configurable_product_required',
                                       array(
                                            'configurable_attribute_title' => $attrData['admin_title'],
                                            'general_name'                 => $this->generate('string', 255, ':alnum:'),
                                            'general_description'          => $this->generate('string', 255, ':alnum:'),
                                            'general_short_description'    => $this->generate('string', 255, ':alnum:'),
                                            'general_sku'                  => $this->generate('string', 64, ':alnum:')
                                       ));
        $productSearch = $this->loadData('product_search', array('product_sku' => $productData['general_sku']));
        //Steps
        $this->productHelper()->createProduct($productData, 'configurable');
        //Verifying
        $this->assertMessagePresent('success', 'success_saved_product');
        //Steps
        $this->productHelper()->openProduct($productSearch);
        //Verifying
        $this->productHelper()->verifyProductInfo($productData, array('configurable_attribute_title'));
    }

    /**
     * <p>Creating product with SKU length more than 64 characters.</p>
     * <p>Steps</p>
     * <p>1. Click "Add Product" button;</p>
     * <p>2. Fill in "Attribute Set", "Product Type" fields;</p>
     * <p>3. Click "Continue" button;</p>
     * <p>4. Fill in required fields, use for sku string with length more than 64 characters</p>
     * <p>5. Click "Save" button;</p>
     * <p>Expected result:</p>
     * <p>Product is not created, error message appears;</p>
     *
     * @depends createConfigurableAttribute
     *
     * @TestlinkId    TL-MAGE-3369
     * @test
     */
    public function incorrectSkuLengthInConfigurable($attrData)
    {
        //Data
        $productData = $this->loadData('configurable_product_required',
                                       array('configurable_attribute_title' => $attrData['admin_title'],
                                            'general_sku'                   => $this->generate('string', 65,
                                                                                               ':alnum:')));
        //Steps
        $this->productHelper()->createProduct($productData, 'configurable');
        //Verifying
        $this->assertMessagePresent('validation', 'incorrect_sku_length');
        $this->assertTrue($this->verifyMessagesCount(), $this->getParsedMessages());
    }

    /**
     * <p>Creating product with invalid price</p>
     * <p>Steps</p>
     * <p>1. Click "Add Product" button;</p>
     * <p>2. Fill in "Attribute Set", "Product Type" fields;</p>
     * <p>3. Click "Continue" button;</p>
     * <p>4. Fill in "Price" field with special characters, the rest fields - with normal data;</p>
     * <p>5. Click "Save" button;</p>
     * <p>Expected result:</p>
     * <p>Product is not created, error message appears;</p>
     *
     * @dataProvider invalidNumericFieldDataProvider
     * @depends createConfigurableAttribute
     *
     * @TestlinkId    TL-MAGE-3370
     * @test
     */
    public function invalidPriceInConfigurable($invalidPrice, $attrData)
    {
        //Data
        $productData = $this->loadData('configurable_product_required',
                                       array('configurable_attribute_title' => $attrData['admin_title'],
                                            'prices_price'                  => $invalidPrice),
                                       'general_sku');
        //Steps
        $this->productHelper()->createProduct($productData, 'configurable');
        //Verifying
        $this->addFieldIdToMessage('field', 'prices_price');
        $this->assertMessagePresent('validation', 'enter_zero_or_greater');
        $this->assertTrue($this->verifyMessagesCount(), $this->getParsedMessages());
    }

    /**
     * <p>Creating product with invalid special price</p>
     * <p>Steps</p>
     * <p>1. Click "Add Product" button;</p>
     * <p>2. Fill in "Attribute Set", "Product Type" fields;</p>
     * <p>3. Click "Continue" button;</p>
     * <p>4. Fill in field "Special Price" with invalid data, the rest fields - with correct data;
     * <p>5. Click "Save" button;</p>
     * <p>Expected result:<p>
     * <p>Product is not created, error message appears;</p>
     *
     * @dataProvider invalidNumericFieldDataProvider
     * @depends createConfigurableAttribute
     *
     * @TestlinkId    TL-MAGE-3371
     * @test
     */
    public function invalidSpecialPriceInConfigurable($invalidValue, $attrData)
    {
        //Data
        $productData = $this->loadData('configurable_product_required',
                                       array('configurable_attribute_title' => $attrData['admin_title'],
                                            'prices_special_price'          => $invalidValue),
                                       'general_sku');
        //Steps
        $this->productHelper()->createProduct($productData, 'configurable');
        //Verifying
        $this->addFieldIdToMessage('field', 'prices_special_price');
        $this->assertMessagePresent('validation', 'enter_zero_or_greater');
        $this->assertTrue($this->verifyMessagesCount(), $this->getParsedMessages());
    }

    /**
     * <p>Creating product with empty tier price</p>
     * <p>Steps<p>
     * <p>1. Click "Add Product" button;</p>
     * <p>2. Fill in "Attribute Set", "Product Type" fields;</p>
     * <p>3. Click "Continue" button;</p>
     * <p>4. Fill in required fields with correct data;</p>
     * <p>5. Click "Add Tier" button and leave fields in current fieldset empty;</p>
     * <p>6. Click "Save" button;</p>
     * <p>Expected result:</p>
     * <p>Product is not created, error message appears;</p>
     *
     * @dataProvider emptyTierPriceFieldsInConfigurableDataProvider
     * @depends createConfigurableAttribute
     *
     * @TestlinkId    TL-MAGE-3367
     * @test
     */
    public function emptyTierPriceFieldsInConfigurable($emptyTierPrice, $attrData)
    {
        //Data
        $productData = $this->loadData('configurable_product_required',
                                       array('configurable_attribute_title' => $attrData['admin_title']),
                                       'general_sku');
        $productData['prices_tier_price_data'][] = $this->loadData('prices_tier_price_1',
                                                                   array($emptyTierPrice => '%noValue%'));
        //Steps
        $this->productHelper()->createProduct($productData, 'configurable');
        //Verifying
        $this->addFieldIdToMessage('field', $emptyTierPrice);
        $this->assertMessagePresent('validation', 'empty_required_field');
        $this->assertTrue($this->verifyMessagesCount(), $this->getParsedMessages());
    }

    public function emptyTierPriceFieldsInConfigurableDataProvider()
    {
        return array(
            array('prices_tier_price_qty'),
            array('prices_tier_price_price'),
        );
    }

    /**
     * <p>Creating product with invalid Tier Price Data</p>
     * <p>Steps</p>
     * <p>1. Click "Add Product" button;</p>
     * <p>2. Fill in "Attribute Set", "Product Type" fields;</p>
     * <p>3. Click "Continue" button;</p>
     * <p>4. Fill in required fields with correct data;</p>
     * <p>5. Click "Add Tier" button and fill in fields in current fieldset with imcorrect data;</p>
     * <p>6. Click "Save" button;</p>
     * <p>Expected result:</p>
     * <p>Product is not created, error message appears;</p>
     *
     * @dataProvider invalidNumericFieldDataProvider
     * @depends createConfigurableAttribute
     *
     * @TestlinkId    TL-MAGE-3372
     * @test
     */
    public function invalidTierPriceInConfigurable($invalidTierData, $attrData)
    {
        //Data
        $tierData = array(
            'prices_tier_price_qty'   => $invalidTierData,
            'prices_tier_price_price' => $invalidTierData
        );
        $productData = $this->loadData('configurable_product_required',
                                       array('configurable_attribute_title' => $attrData['admin_title']),
                                       'general_sku');
        $productData['prices_tier_price_data'][] = $this->loadData('prices_tier_price_1', $tierData);
        //Steps
        $this->productHelper()->createProduct($productData, 'configurable');
        //Verifying
        foreach ($tierData as $key => $value) {
            $this->addFieldIdToMessage('field', $key);
            $this->assertMessagePresent('validation', 'enter_greater_than_zero');
        }
        $this->assertTrue($this->verifyMessagesCount(2), $this->getParsedMessages());
    }

    public function invalidNumericFieldDataProvider()
    {
        return array(
            array($this->generate('string', 9, ':punct:')),
            array($this->generate('string', 9, ':alpha:')),
            array('g3648GJHghj'),
            array('-128')
        );
    }

    /**
     * <p>Creating Configurable product with Simple product</p>
     * <p>Preconditions</p>
     * <p> Simple product created</p>
     * <p>Steps:</p>
     * <p>1. Click 'Add product' button;</p>
     * <p>2. Fill in 'Attribute Set' and 'Product Type' fields;</p>
     * <p>3. Click 'Continue' button;</p>
     * <p>4. Fill in all required fields;</p>
     * <p>5. Goto "Associated products" tab;</p>
     * <p>6. Select created Simple product;</p>
     * <p>5. Click 'Save' button;</p>
     * <p>Expected result:</p>
     * <p>Product is created, confirmation message appears;</p>
     *
     * @TestlinkId    TL-MAGE-3364
     * @test
     * @depends createConfigurableAttribute
     */
    public function configurableWithSimpleProduct($attrData)
    {
        //Data
        $simple = $this->loadData('simple_product_required', null, array('general_name', 'general_sku'));
        $simple['general_user_attr']['dropdown'][$attrData['attribute_code']] =
            $attrData['option_1']['admin_option_name'];
        $configurable = $this->loadData('configurable_product_required',
                                        array('configurable_attribute_title' => $attrData['admin_title']),
                                        array('general_name', 'general_sku'));
        $configurable['associated_configurable_data'] = $this->loadData('associated_configurable_data',
                                                                        array('associated_search_sku' => $simple['general_sku']));
        $productSearch = $this->loadData('product_search', array('product_sku' => $configurable['general_sku']));
        //Steps
        $this->productHelper()->createProduct($simple);
        //Verifying
        $this->assertMessagePresent('success', 'success_saved_product');
        //Steps
        $this->productHelper()->createProduct($configurable, 'configurable');
        //Verifying
        $this->assertMessagePresent('success', 'success_saved_product');
        //Steps
        $this->productHelper()->openProduct($productSearch);
        //Verifying
        $this->productHelper()->verifyProductInfo($configurable, array('configurable_attribute_title'));
    }

    /**
     * <p>Creating Configurable product with Virtual product</p>
     * <p>Preconditions</p>
     * <p>Virtual product created</p>
     * <p>Steps:</p>
     * <p>1. Click 'Add product' button;</p>
     * <p>2. Fill in 'Attribute Set' and 'Product Type' fields;</p>
     * <p>3. Click 'Continue' button;</p>
     * <p>4. Fill in all required fields;</p>
     * <p>5. Goto "Associated products" tab;</p>
     * <p>6. Select created Virtual product;</p>
     * <p>5. Click 'Save' button;</p>
     * <p>Expected result:</p>
     * <p>Product is created, confirmation message appears;</p>
     *
     * @TestlinkId    TL-MAGE-3365
     * @test
     * @depends createConfigurableAttribute
     */
    public function configurableWithVirtualProduct($attrData)
    {
        //Data
        $virtual = $this->loadData('virtual_product_required', null, array('general_name', 'general_sku'));
        $virtual['general_user_attr']['dropdown'][$attrData['attribute_code']] =
            $attrData['option_2']['admin_option_name'];
        $configurable = $this->loadData('configurable_product_required',
                                        array('configurable_attribute_title' => $attrData['admin_title']),
                                        array('general_name', 'general_sku'));
        $configurable['associated_configurable_data'] = $this->loadData('associated_configurable_data',
                                                                        array('associated_search_sku' => $virtual['general_sku']));
        $productSearch = $this->loadData('product_search', array('product_sku' => $configurable['general_sku']));
        //Steps
        $this->productHelper()->createProduct($virtual, 'virtual');
        //Verifying
        $this->assertMessagePresent('success', 'success_saved_product');
        //Steps
        $this->productHelper()->createProduct($configurable, 'configurable');
        //Verifying
        $this->assertMessagePresent('success', 'success_saved_product');
        //Steps
        $this->productHelper()->openProduct($productSearch);
        //Verifying
        $this->productHelper()->verifyProductInfo($configurable, array('configurable_attribute_title'));
    }

    /**
     * <p>Creating Configurable product with Downloadable product</p>
     * <p>Preconditions</p>
     * <p>Downloadable product created</p>
     * <p>Steps:</p>
     * <p>1. Click 'Add product' button;</p>
     * <p>2. Fill in 'Attribute Set' and 'Product Type' fields;</p>
     * <p>3. Click 'Continue' button;</p>
     * <p>4. Fill in all required fields;</p>
     * <p>5. Goto "Associated products" tab;</p>
     * <p>6. Select created Downloadable product;</p>
     * <p>5. Click 'Save' button;</p>
     * <p>Expected result:</p>
     * <p>Product is created, confirmation message appears;</p>
     *
     * @TestlinkId    TL-MAGE-3363
     * @test
     * @depends createConfigurableAttribute
     */
    public function configurableWithDownloadableProduct($attrData)
    {
        //Data
        $download = $this->loadData('downloadable_product_required', null, array('general_name', 'general_sku'));
        $download['general_user_attr']['dropdown'][$attrData['attribute_code']] =
            $attrData['option_3']['admin_option_name'];
        $configurable = $this->loadData('configurable_product_required',
                                        array('configurable_attribute_title' => $attrData['admin_title']),
                                        array('general_name', 'general_sku'));
        $configurable['associated_configurable_data'] = $this->loadData('associated_configurable_data',
                                                                        array('associated_search_sku' => $download['general_sku']));
        $productSearch = $this->loadData('product_search', array('product_sku' => $configurable['general_sku']));
        //Steps
        $this->productHelper()->createProduct($download, 'downloadable');
        //Verifying
        $this->assertMessagePresent('success', 'success_saved_product');
        //Steps
        $this->productHelper()->createProduct($configurable, 'configurable');
        //Verifying
        $this->assertMessagePresent('success', 'success_saved_product');
        //Steps
        $this->productHelper()->openProduct($productSearch);
        //Verifying
        $this->productHelper()->verifyProductInfo($configurable, array('configurable_attribute_title'));
    }
}