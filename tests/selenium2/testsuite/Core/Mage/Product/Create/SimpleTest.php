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
 * Simple product creation tests
 *
 * @package     selenium
 * @subpackage  tests
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class Core_Mage_Product_Create_SimpleTest extends Mage_Selenium_TestCase
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
     * <p>Navigate to Catalog -> Manage Products</p>
     */
    protected function assertPreConditions()
    {
        $this->navigate('manage_products');
        $this->addParameter('id', '0');
    }

    protected function tearDownAfterTest()
    {
        $windowQty = $this->getAllWindowNames();
        if (count($windowQty) > 1 && end($windowQty) != 'null') {
            $this->selectWindow("name=" . end($windowQty));
            $this->close();
            $this->selectWindow(null);
        }
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
     * @return array $productData
     *
     * @TestlinkId    TL-MAGE-3422
     * @test
     */
    public function onlyRequiredFieldsInSimple()
    {
        //Data
        $productData = $this->loadData('simple_product_required');
        //Steps
        $this->productHelper()->createProduct($productData);
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
     * @depends onlyRequiredFieldsInSimple
     *
     * @TestlinkId    TL-MAGE-3411
     * @test
     */
    public function allFieldsInSimple()
    {
        //Data
        $productData = $this->loadData('simple_product');
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
     * @param $productData
     *
     * @depends onlyRequiredFieldsInSimple
     *
     * @TestlinkId    TL-MAGE-3414
     * @test
     */
    public function existSkuInSimple($productData)
    {
        //Steps
        $this->productHelper()->createProduct($productData);
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
     * @param $emptyField
     * @param $fieldType
     *
     * @dataProvider withRequiredFieldsEmptyDataProvider
     * @depends onlyRequiredFieldsInSimple
     *
     * @TestlinkId    TL-MAGE-12
     * @test
     */
    public function withRequiredFieldsEmpty($emptyField, $fieldType)
    {
        //Data
        if ($emptyField == 'general_visibility') {
            $overrideData = array($emptyField => '-- Please Select --');
        } elseif ($emptyField == 'inventory_qty') {
            $overrideData = array($emptyField => '');
        } else {
            $overrideData = array($emptyField => '%noValue%');
        }
        $productData = $this->loadData('simple_product_required', $overrideData);
        //Steps
        $this->productHelper()->createProduct($productData);
        //Verifying
        $this->addFieldIdToMessage($fieldType, $emptyField);
        $this->assertMessagePresent('validation', 'empty_required_field');
        $this->assertTrue($this->verifyMessagesCount(), $this->getParsedMessages());
    }

    public function withRequiredFieldsEmptyDataProvider()
    {
        return array(
            array('general_name', 'field'),
            array('general_description', 'field'),
            array('general_short_description', 'field'),
            array('general_sku', 'field'),
            array('general_weight', 'field'),
            array('general_status', 'dropdown'),
            array('general_visibility', 'dropdown'),
            array('prices_price', 'field'),
            array('prices_tax_class', 'dropdown'),
            array('inventory_qty', 'field')
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
     * @depends onlyRequiredFieldsInSimple
     *
     * @TestlinkId    TL-MAGE-3423
     * @test
     */
    public function specialCharactersInRequiredFields()
    {
        //Data
        $productData = $this->loadData('simple_product_required',
                                       array(
                                            'general_name'              => $this->generate('string', 32, ':punct:'),
                                            'general_description'       => $this->generate('string', 32, ':punct:'),
                                            'general_short_description' => $this->generate('string', 32, ':punct:'),
                                            'general_sku'               => $this->generate('string', 32, ':punct:')
                                       ));
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
     * @depends onlyRequiredFieldsInSimple
     *
     * @TestlinkId    TL-MAGE-3421
     * @test
     */
    public function longValuesInRequiredFields()
    {
        //Data
        $productData = $this->loadData('simple_product_required',
                                       array(
                                            'general_name'              => $this->generate('string', 255, ':alnum:'),
                                            'general_description'       => $this->generate('string', 255, ':alnum:'),
                                            'general_short_description' => $this->generate('string', 255, ':alnum:'),
                                            'general_sku'               => $this->generate('string', 64, ':alnum:'),
                                            'general_weight'            => 99999999.9999
                                       ));
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
     * @depends onlyRequiredFieldsInSimple
     *
     * @TestlinkId    TL-MAGE-3415
     * @test
     */
    public function incorrectSkuLengthInSimple()
    {
        //Data
        $productData = $this->loadData('simple_product_required',
                                       array('general_sku' => $this->generate('string', 65, ':alnum:')));
        //Steps
        $this->productHelper()->createProduct($productData);
        //Verifying
        $this->assertMessagePresent('validation', 'incorrect_sku_length');
        $this->assertTrue($this->verifyMessagesCount(), $this->getParsedMessages());
    }

    /**
     * Fails due to MAGE-5658
     * <p>Creating product with invalid weight</p>
     * <p>Steps</p>
     * <p>1. Click "Add Product" button;</p>
     * <p>2. Fill in "Attribute Set", "Product Type" fields;</p>
     * <p>3. Click "Continue" button;</p>
     * <p>4. Fill in "Weight" field with special characters, the rest - with normal data;</p>
     * <p>5. Click "Save" button;</p>
     * <p>Expected result:</p>
     * <p>Product created, confirmation message appears, Weight=0;</p>
     *
     * @TestlinkId    TL-MAGE-3420
     * @test
     * @group skip_due_to_bug
     */
    public function invalidWeightInSimple()
    {
        //Data
        $productData = $this->loadData('simple_product_required',
                                       array('general_weight' => $this->generate('string', 9, ':punct:')));
        //Steps
        $this->productHelper()->createProduct($productData);
        //Verifying
        $this->addFieldIdToMessage('field', 'general_weight');
        $this->assertMessagePresent('validation', 'enter_valid_number');
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
     * @param $invalidPrice
     *
     * @dataProvider invalidNumericFieldDataProvider
     * @depends onlyRequiredFieldsInSimple
     *
     * @TestlinkId    TL-MAGE-3416
     * @test
     */
    public function invalidPriceInSimple($invalidPrice)
    {
        //Data
        $productData = $this->loadData('simple_product_required', array('prices_price' => $invalidPrice));
        //Steps
        $this->productHelper()->createProduct($productData);
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
     * @param $invalidValue
     *
     * @dataProvider invalidNumericFieldDataProvider
     * @depends onlyRequiredFieldsInSimple
     *
     * @TestlinkId    TL-MAGE-3418
     * @test
     */
    public function invalidSpecialPriceInSimple($invalidValue)
    {
        //Data
        $productData = $this->loadData('simple_product_required', array('prices_special_price' => $invalidValue));
        //Steps
        $this->productHelper()->createProduct($productData);
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
     * @param $emptyTierPrice
     *
     * @dataProvider emptyTierPriceFieldsDataProvider
     * @depends onlyRequiredFieldsInSimple
     *
     * @TestlinkId    TL-MAGE-3413
     * @test
     */
    public function emptyTierPriceFields($emptyTierPrice)
    {
        //Data
        $productData = $this->loadData('simple_product_required');
        $productData['prices_tier_price_data'][] = $this->loadData('prices_tier_price_1',
                                                                   array($emptyTierPrice => '%noValue%'));
        //Steps
        $this->productHelper()->createProduct($productData);
        //Verifying
        $this->addFieldIdToMessage('field', $emptyTierPrice);
        $this->assertMessagePresent('validation', 'empty_required_field');
        $this->assertTrue($this->verifyMessagesCount(), $this->getParsedMessages());
    }

    public function emptyTierPriceFieldsDataProvider()
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
     * @param $invalidTierData
     *
     * @dataProvider invalidNumericFieldDataProvider
     * @depends onlyRequiredFieldsInSimple
     *
     * @TestlinkId    TL-MAGE-3419
     * @test
     */
    public function invalidTierPriceInSimple($invalidTierData)
    {
        //Data
        $tierData = array(
            'prices_tier_price_qty'   => $invalidTierData,
            'prices_tier_price_price' => $invalidTierData
        );
        $productData = $this->loadData('simple_product_required');
        $productData['prices_tier_price_data'][] = $this->loadData('prices_tier_price_1', $tierData);
        //Steps
        $this->productHelper()->createProduct($productData);
        //Verifying
        foreach ($tierData as $key => $value) {
            $this->addFieldIdToMessage('field', $key);
            $this->assertMessagePresent('validation', 'enter_greater_than_zero');
        }
        $this->assertTrue($this->verifyMessagesCount(2), $this->getParsedMessages());
    }

    /**
     * <p>Creating product with invalid Qty</p>
     * <p>Steps</p>
     * <p>1. Click "Add Product" button;</p>
     * <p>2. Fill in "Attribute Set", "Product Type" fields;</p>
     * <p>3. Click "Continue" button;</p>
     * <p>4. Fill in required fields with correct data, "Qty" field - with special characters;</p>
     * <p>5. Click "Save" button;</p>
     * <p>Expected result:</p>
     * <p>Product is not created, error message appears;</p>
     *
     * @param $invalidQty
     *
     * @dataProvider invalidQtyDataProvider
     * @depends onlyRequiredFieldsInSimple
     *
     * @TestlinkId    TL-MAGE-3417
     * @test
     */
    public function invalidQtyInSimple($invalidQty)
    {
        //Data
        $productData = $this->loadData('simple_product_required', array('inventory_qty' => $invalidQty));
        //Steps
        $this->productHelper()->createProduct($productData);
        //Verifying
        $this->addFieldIdToMessage('field', 'inventory_qty');
        $this->assertMessagePresent('validation', 'enter_valid_number');
        $this->assertTrue($this->verifyMessagesCount(), $this->getParsedMessages());
    }

    public function invalidQtyDataProvider()
    {
        return array(
            array($this->generate('string', 9, ':punct:')),
            array($this->generate('string', 9, ':alpha:')),
            array('g3648GJHghj'),
        );
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
     * Quick create
     *
     * depends onlyRequiredFieldsInSimple
     * @return array
     * @test
     */
    public function onConfigurableProductPageQuickCreate()
    {
        //Data
        $attrData = $this->loadData('product_attribute_dropdown_with_options');
        $associatedAttributes = $this->loadData('associated_attributes',
                                                array('General' => $attrData['attribute_code']));
        $configurable = $this->loadData('configurable_product_required',
                                        array('configurable_attribute_title' => $attrData['admin_title']));
        $productSearch = $this->loadData('product_search', array('product_sku' => $configurable['general_sku']));
        $quickSimple = $this->loadData('quick_simple_product',
                                       array('quick_simple_product_attribute_value' => $attrData['option_1']['admin_option_name']));
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
        //Steps
        $this->navigate('manage_products');
        $this->productHelper()->createProduct($configurable, 'configurable');
        //Verifying
        $this->assertMessagePresent('success', 'success_saved_product');
        //Steps
        $this->productHelper()->openProduct($productSearch);
        $this->addParameter('attributeCode', $attrData['attribute_code']);
        $this->fillForm($quickSimple, 'associated');
        $this->clickButton('quick_create', false);
        $this->pleaseWait();
        //Verifying
        $this->assertMessagePresent('success', 'success_created_product');

        return array('search' => $productSearch,
                     'attr'   => $attrData);
    }

    /**
     * Create Empty
     *
     * @param $data
     *
     * @depends onConfigurableProductPageQuickCreate
     * @test
     */
    public function onConfigurableProductPageCreateEmpty($data)
    {
        //Data
        $searchAttr = $this->loadData('attribute_search_data',
                                      array('attribute_code' => $data['attr']['attribute_code']));
        $simpleEmpty = $this->loadData('simple_product_required');
        $simpleEmpty['general_user_attr']['dropdown'][$data['attr']['attribute_code']] =
            $data['attr']['option_2']['admin_option_name'];
        //Steps
        //1.Define attribute ID
        $attrId = $this->productAttributeHelper()->defineAttributeId($searchAttr);
        $this->addParameter('attrId', $attrId);
        //2.Define attribute set ID that used in product
        $this->navigate('manage_products');
        $data['search'] = $this->arrayEmptyClear($data['search']);
        $productXpath = $this->search($data['search']);
        $this->assertNotEquals(null, $productXpath);
        $columnId = $this->getColumnIdByName('Attrib. Set Name');
        $value = $this->getText($productXpath . "/td[$columnId]");
        $setId = $this->getValue("//tr[@class='filter']/th[$columnId]//option[text()='$value']");
        $this->addParameter('setId', $setId);
        //3. Open product and create simple product
        $this->productHelper()->openProduct($data['search']);
        $this->openTab('associated');
        $this->clickButton('create_empty', false);
        $names = $this->getAllWindowNames();
        $this->waitForPopUp(end($names), '30000');
        $this->selectWindow("name=" . end($names));
        $this->productHelper()->fillProductInfo($simpleEmpty);
        $this->saveForm('save', false);
        $this->selectWindow(null);
        $this->waitForAjax();
        $xpath = $this->search(array('associated_search_sku' => $simpleEmpty['general_sku']), 'associated');
        $this->assertNotEquals(null, $xpath, 'Product is not found');
    }

    /**
     * Copy From Configurable
     *
     * @param $data
     *
     * @depends onConfigurableProductPageQuickCreate
     * @test
     */
    public function onConfigurableProductPageCopyFromConfigurable($data)
    {
        //Data
        $searchAttr = $this->loadData('attribute_search_data',
                                      array('attribute_code' => $data['attr']['attribute_code']));
        $simple = array('general_weight' => '3,21',
                        'general_sku'    => $this->generate('string', 15, ':alnum:'));
        $simple['general_user_attr']['dropdown'][$data['attr']['attribute_code']] =
            $data['attr']['option_3']['admin_option_name'];
        //Steps
        //1.Define attribute ID
        $attrId = $this->productAttributeHelper()->defineAttributeId($searchAttr);
        $this->addParameter('attrId', $attrId);
        //2.Define attribute set ID that used in product
        $this->navigate('manage_products');
        $data['search'] = $this->arrayEmptyClear($data['search']);
        $productXpath = $this->search($data['search']);
        $this->assertNotEquals(null, $productXpath);
        $columnId = $this->getColumnIdByName('Attrib. Set Name');
        $value = $this->getText($productXpath . "/td[$columnId]");
        $setId = $this->getValue("//tr[@class='filter']/th[$columnId]//option[text()='$value']");
        $this->addParameter('setId', $setId);
        //3. Open product and create simple product
        $this->productHelper()->openProduct($data['search']);
        $this->addParameter('productId', $this->getParameter('id'));
        $this->openTab('associated');
        $this->clickButton('create_copy_from_configurable', false);
        $names = $this->getAllWindowNames();
        $this->waitForPopUp(end($names), '30000');
        $this->selectWindow("name=" . end($names));
        $this->productHelper()->fillProductInfo($simple);
        $this->saveForm('save', false);
        $this->selectWindow(null);
        $this->waitForAjax();
        $xpath = $this->search(array('associated_search_sku' => $simple['general_sku']), 'associated');
        $this->assertNotEquals(null, $xpath, 'Product is not found');
    }
}