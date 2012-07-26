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
 * Products deletion tests
 *
 * @package     selenium
 * @subpackage  tests
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class Core_Mage_Product_DeleteTest extends Mage_Selenium_TestCase
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
     * <p>Delete product.</p>
     * <p>Steps:</p>
     * <p>1. Click "Add product" button;</p>
     * <p>2. Fill in "Attribute Set" and "Product Type" fields;</p>
     * <p>3. Click "Continue" button;</p>
     * <p>4. Fill in required fields;</p>
     * <p>5. Click "Save" button;</p>
     * <p>Expected result:</p>
     * <p>Product is created, confirmation message appears;</p>
     * <p>6. Open product;</p>
     * <p>7. Click "Delete" button;</p>
     * <p>Expected result:</p>
     * <p>Product is deleted, confirmation message appears;</p>
     *
     * @param string $type
     *
     * @test
     * @dataProvider deleteSingleProductDataProvider
     * @TestlinkId    TL-MAGE-3425
     */
    public function deleteSingleProduct($type)
    {
        //Data
        $productData = $this->loadData($type . '_product_required', null, array('general_name', 'general_sku'));
        $productSearch = $this->loadData('product_search', array('product_sku' => $productData['general_sku']));
        //Steps
        $this->productHelper()->createProduct($productData, $type);
        //Verifying
        $this->assertMessagePresent('success', 'success_saved_product');
        //Steps
        $this->productHelper()->openProduct($productSearch);
        $this->clickButtonAndConfirm('delete', 'confirmation_for_delete');
        //Verifying
        $this->assertMessagePresent('success', 'success_deleted_product');
    }

    public function deleteSingleProductDataProvider()
    {
        return array(
            array('simple'),
            array('virtual'),
            array('downloadable'),
            array('grouped'),
            array('bundle')
        );
    }

    /**
     * <p>Delete configurable product</p>
     * <p>Steps:</p>
     * <p>1. Click "Add product" button;</p>
     * <p>2. Fill in "Attribute Set" and "Product Type" fields;</p>
     * <p>3. Click "Continue" button;</p>
     * <p>4. Fill in required fields;</p>
     * <p>5. Click "Save" button;</p>
     * <p>Expected result:</p>
     * <p>Product is created, confirmation message appears;</p>
     * <p>6. Open product;</p>
     * <p>7. Click "Delete" button;</p>
     * <p>Expected result:</p>
     * <p>Product is deleted, confirmation message appears;</p>
     *
     * @return array
     * @test
     * @TestlinkId	TL-MAGE-3424
     */
    public function deleteSingleConfigurableProduct()
    {
        //Data
        $attrData = $this->loadData('product_attribute_dropdown_with_options', null,
                array('admin_title', 'attribute_code'));
        $associatedAttributes = $this->loadData('associated_attributes',
                array('General' => $attrData['attribute_code']));
        $productData = $this->loadData('configurable_product_required',
                array('configurable_attribute_title' => $attrData['admin_title']),
                array('general_name', 'general_sku'));
        $productSearch = $this->loadData('product_search', array('product_sku' => $productData['general_sku']));
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
        $this->productHelper()->createProduct($productData, 'configurable');
        //Verifying
        $this->assertMessagePresent('success', 'success_saved_product');
        //Steps
        $this->productHelper()->openProduct($productSearch);
        $this->clickButtonAndConfirm('delete', 'confirmation_for_delete');
        //Verifying
        $this->assertMessagePresent('success', 'success_deleted_product');

        return $attrData;
    }

    /**
     * Delete product that used in configurable
     *
     * @param string $type
     * @param array $attrData
     *
     * @test
     * @dataProvider deleteAssociatedToConfigurableDataProvider
     * @depends deleteSingleConfigurableProduct
     */
    public function deleteAssociatedToConfigurable($type, $attrData)
    {
        //Data
        $associated = $this->loadData($type . '_product_required', null, array('general_name', 'general_sku'));
        $associated['general_user_attr']['dropdown'][$attrData['attribute_code']] =
                $attrData['option_1']['admin_option_name'];
        $configPr = $this->loadData('configurable_product_required',
                array('configurable_attribute_title' => $attrData['admin_title']),
                array('general_name', 'general_sku'));
        $configPr['associated_configurable_data'] = $this->loadData('associated_configurable_data',
                array('associated_search_sku' => $associated['general_sku']));
        $productSearch = $this->loadData('product_search', array('product_sku' => $associated['general_sku']));
        //Steps
        $this->productHelper()->createProduct($associated, $type);
        //Verifying
        $this->assertMessagePresent('success', 'success_saved_product');
        //Steps
        $this->productHelper()->createProduct($configPr, 'configurable');
        //Verifying
        $this->assertMessagePresent('success', 'success_saved_product');
        //Steps
        $this->productHelper()->openProduct($productSearch);
        $this->clickButtonAndConfirm('delete', 'confirmation_for_delete');
        //Verifying
        $this->assertMessagePresent('success', 'success_deleted_product');
    }

    public function deleteAssociatedToConfigurableDataProvider()
    {
        return array(
            array('simple'),
            array('virtual')
        );
    }

    /**
     * Delete product that used in Grouped or bundle
     *
     * @param string $associatedType
     * @param string $type
     *
     * @test
     * @dataProvider deleteAssociatedProductDataProvider
     */
    public function deleteAssociatedProduct($associatedType, $type)
    {
        //Data
        $associatedData = $this->loadData($associatedType . '_product_required', null,
                array('general_name', 'general_sku'));
        if ($type == 'grouped') {
            $productData = $this->loadData($type . '_product_required',
                    array('associated_search_sku' => $associatedData['general_sku']),
                    array('general_name', 'general_sku'));
        } else {
            $productData = $this->loadData($type . '_product_required', null, array('general_name', 'general_sku'));
            $productData['bundle_items_data']['item_1'] = $this->loadData('bundle_item_1',
                    array('bundle_items_sku' => $associatedData['general_sku']));
        }
        $productSearch = $this->loadData('product_search', array('product_sku' => $associatedData['general_sku']));
        //Steps
        $this->productHelper()->createProduct($associatedData, $associatedType);
        //Verifying
        $this->assertMessagePresent('success', 'success_saved_product');
        //Steps
        $this->productHelper()->createProduct($productData, $type);
        //Verifying
        $this->assertMessagePresent('success', 'success_saved_product');
        //Steps
        $this->productHelper()->openProduct($productSearch);
        $this->clickButtonAndConfirm('delete', 'confirmation_for_delete');
        //Verifying
        $this->assertMessagePresent('success', 'success_deleted_product');
    }

    public function deleteAssociatedProductDataProvider()
    {
        return array(
            array('simple', 'grouped'),
            array('virtual', 'grouped'),
            array('downloadable', 'grouped'),
            array('simple', 'bundle'),
            array('virtual', 'bundle')
        );
    }

    /**
     * <p>Delete several products.</p>
     * <p>Preconditions: Create several products</p>
     * <p>Steps:</p>
     * <p>1. Search and choose several products.</p>
     * <p>3. Select 'Actions' to 'Delete'.</p>
     * <p>2. Click 'Submit' button.</p>
     * <p>Expected result:</p>
     * <p>Products are deleted.</p>
     * <p>Success Message is displayed.</p>
     * @test
     * @TestlinkId    TL-MAGE-3426
     */
    public function throughMassAction()
    {
        $productQty = 2;
        for ($i = 1; $i <= $productQty; $i++) {
            //Data
            $productData = $this->loadData('simple_product_required', null, array('general_name', 'general_sku'));
            ${'searchData' . $i} = $this->loadData('product_search', array('email' => $productData['general_sku']));
            //Steps
            $this->productHelper()->createProduct($productData);
            //Verifying
            $this->assertMessagePresent('success', 'success_saved_product');
        }
        for ($i = 1; $i <= $productQty; $i++) {
            $this->searchAndChoose(${'searchData' . $i});
        }
        $this->addParameter('qtyDeletedProducts', $productQty);
        $xpath = $this->_getControlXpath('dropdown', 'product_massaction');
        $this->select($xpath, 'Delete');
        $this->clickButtonAndConfirm('submit', 'confirmation_for_delete');
        //Verifying
        $this->assertMessagePresent('success', 'success_deleted_products_massaction');
    }
}