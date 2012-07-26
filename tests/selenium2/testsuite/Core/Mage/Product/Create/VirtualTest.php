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
 * Virtual product creation tests
 *
 * @package     selenium
 * @subpackage  tests
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class Core_Mage_Product_Create_VirtualTest extends Mage_Selenium_TestCase
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
     * @TestlinkId    TL-MAGE-5333
     * @test
     */
    public function onlyRequiredFieldsInVirtual()
    {
        //Data
        $productData = $this->loadData('virtual_product_required', null,
                                       array('general_name', 'general_sku'));
        //Steps
        $this->productHelper()->createProduct($productData, 'virtual');
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
     * @depends onlyRequiredFieldsInVirtual
     *
     * @TestlinkId    TL-MAGE-5334
     * @test
     */
    public function allFieldsInVirtual()
    {
        //Data
        $productData = $this->loadData('virtual_product', null, array('general_name', 'general_sku'));
        $productSearch = $this->loadData('product_search', array('product_sku' => $productData['general_sku']));
        //Steps
        $this->productHelper()->createProduct($productData, 'virtual');
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
     * @depends onlyRequiredFieldsInVirtual
     *
     * @TestlinkId    TL-MAGE-5336
     * @test
     */
    public function existSkuInVirtual($productData)
    {
        //Steps
        $this->productHelper()->createProduct($productData, 'virtual');
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
     * @dataProvider withRequiredFieldsEmptyDataProvider
     * @depends onlyRequiredFieldsInVirtual
     *
     * @TestlinkId    TL-MAGE-5337
     * @test
     */
    public function withRequiredFieldsEmpty($emptyField, $fieldType)
    {
        //Data
        if ($emptyField == 'general_sku') {
            $productData = $this->loadData('virtual_product_required',
                                           array($emptyField => '%noValue%'));
        } elseif ($emptyField == 'general_visibility') {
            $productData = $this->loadData('virtual_product_required',
                                           array($emptyField => '-- Please Select --'), 'general_sku');
        } elseif ($emptyField == 'inventory_qty') {
            $productData = $this->loadData('virtual_product_required', array($emptyField => ''),
                                           'general_sku');
        } else {
            $productData = $this->loadData('virtual_product_required',
                                           array($emptyField => '%noValue%'), 'general_sku');
        }
        //Steps
        $this->productHelper()->createProduct($productData, 'virtual');
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
     * @depends onlyRequiredFieldsInVirtual
     *
     * @TestlinkId    TL-MAGE-5338
     * @test
     */
    public function specialCharactersInRequiredFields()
    {
        //Data
        $productData = $this->loadData('virtual_product_required',
                                       array(
                                            'general_name'              => $this->generate('string', 32, ':punct:'),
                                            'general_description'       => $this->generate('string', 32, ':punct:'),
                                            'general_short_description' => $this->generate('string', 32, ':punct:'),
                                            'general_sku'               => $this->generate('string', 32, ':punct:')
                                       ));
        $productSearch = $this->loadData('product_search',
                                         array('product_sku' => $productData['general_sku']));
        //Steps
        $this->productHelper()->createProduct($productData, 'virtual');
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
     * @depends onlyRequiredFieldsInVirtual
     * @test
     */
    public function longValuesInRequiredFields()
    {
        //Data
        $productData = $this->loadData('virtual_product_required',
                                       array(
                                            'general_name'              => $this->generate('string', 255, ':alnum:'),
                                            'general_description'       => $this->generate('string', 255, ':alnum:'),
                                            'general_short_description' => $this->generate('string', 255, ':alnum:'),
                                            'general_sku'               => $this->generate('string', 64, ':alnum:')
                                       ));
        $productSearch = $this->loadData('product_search',
                                         array('product_sku' => $productData['general_sku']));
        //Steps
        $this->productHelper()->createProduct($productData, 'virtual');
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
     * @depends onlyRequiredFieldsInVirtual
     *
     * @TestlinkId    TL-MAGE-5340
     * @test
     */
    public function incorrectSkuLengthInVirtual()
    {
        //Data
        $productData = $this->loadData('virtual_product_required',
                                       array('general_sku' => $this->generate('string', 65, ':alnum:')));
        //Steps
        $this->productHelper()->createProduct($productData, 'virtual');
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
     * @depends onlyRequiredFieldsInVirtual
     * @test
     */
    public function invalidPriceInVirtual($invalidPrice)
    {
        //Data
        $productData = $this->loadData('virtual_product_required',
                                       array('prices_price' => $invalidPrice), 'general_sku');
        //Steps
        $this->productHelper()->createProduct($productData, 'virtual');
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
     * @depends onlyRequiredFieldsInVirtual
     *
     * @TestlinkId    TL-MAGE-5342
     * @test
     */
    public function invalidSpecialPriceInVirtual($invalidValue)
    {
        //Data
        $productData = $this->loadData('virtual_product_required',
                                       array('prices_special_price' => $invalidValue), 'general_sku');
        //Steps
        $this->productHelper()->createProduct($productData, 'virtual');
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
     * @dataProvider emptyTierPriceFieldsDataProvider
     * @depends onlyRequiredFieldsInVirtual
     *
     * @TestlinkId    TL-MAGE-5343
     * @test
     */
    public function emptyTierPriceFields($emptyTierPrice)
    {
        //Data
        $productData = $this->loadData('virtual_product_required', null, 'general_sku');
        $productData['prices_tier_price_data'][] = $this->loadData('prices_tier_price_1',
                                                                   array($emptyTierPrice => '%noValue%'));
        //Steps
        $this->productHelper()->createProduct($productData, 'virtual');
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
     * @dataProvider invalidNumericFieldDataProvider
     * @depends onlyRequiredFieldsInVirtual
     *
     * @TestlinkId    TL-MAGE-5344
     * @test
     */
    public function invalidTierPriceInVirtual($invalidTierData)
    {
        //Data
        $tierData = array(
            'prices_tier_price_qty'   => $invalidTierData,
            'prices_tier_price_price' => $invalidTierData
        );
        $productData = $this->loadData('virtual_product_required', null, 'general_sku');
        $productData['prices_tier_price_data'][] = $this->loadData('prices_tier_price_1', $tierData);
        //Steps
        $this->productHelper()->createProduct($productData, 'virtual');
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
     * @dataProvider invalidQtyDataProvider
     * @depends onlyRequiredFieldsInVirtual
     *
     * @TestlinkId    TL-MAGE-5345
     * @test
     */
    public function invalidQtyInVirtual($invalidQty)
    {
        //Data
        $productData = $this->loadData('virtual_product_required',
                                       array('inventory_qty' => $invalidQty), 'general_sku');
        //Steps
        $this->productHelper()->createProduct($productData, 'virtual');
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
}