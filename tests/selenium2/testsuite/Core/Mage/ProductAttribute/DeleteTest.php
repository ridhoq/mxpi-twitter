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
 * Delete product attributes
 *
 * @package     selenium
 * @subpackage  tests
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class Core_Mage_ProductAttribute_DeleteTest extends Mage_Selenium_TestCase
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
     * <p>Delete Product Attributes</p>
     * <p>Steps:</p>
     * <p>1.Click on "Add New Attribute" button</p>
     * <p>2.Fill all required fields</p>
     * <p>3.Click on "Save Attribute" button</p>
     * <p>4.Search and open attribute</p>
     * <p>5.Click on "Delete Attribute" button</p>
     * <p>Expected result:</p>
     * <p>Attribute successfully deleted.</p>
     * <p>Success message: 'The product attribute has been deleted.' is displayed.</p>
     *
     * @param $dataName
     *
     * @test
     * @dataProvider deleteProductAttributeDeletableDataProvider
     * @TestlinkId    TL-MAGE-3343
     */
    public function deleteProductAttributeDeletable($dataName)
    {
        //Data
        $attrData = $this->loadData($dataName, null, array('attribute_code', 'admin_title'));
        $searchData = $this->loadData('attribute_search_data',
                array('attribute_code' => $attrData['attribute_code']));
        //Steps
        $this->productAttributeHelper()->createAttribute($attrData);
        //Verifying
        $this->assertMessagePresent('success', 'success_saved_attribute');
        //Steps
        $this->productAttributeHelper()->openAttribute($searchData);
        $this->clickButtonAndConfirm('delete_attribute', 'delete_confirm_message');
        //Verifying
        $this->assertMessagePresent('success', 'success_deleted_attribute');
    }

    public function deleteProductAttributeDeletableDataProvider()
    {
        return array(
            array('product_attribute_textfield'),
            array('product_attribute_textarea'),
            array('product_attribute_date'),
            array('product_attribute_yesno'),
            array('product_attribute_multiselect'),
            array('product_attribute_dropdown'),
            array('product_attribute_price'),
            array('product_attribute_mediaimage'),
            array('product_attribute_fpt')
        );
    }

    /**
     * <p>Delete system Product Attributes</p>
     * <p>Steps:</p>
     * <p>1.Search and open system attribute.</p>
     * <p>Expected result:</p>
     * <p>"Delete Attribute" button isn't present.</p>
     *
     * @test
     * @TestlinkId	TL-MAGE-3342
     */
    public function deletedSystemAttribute()
    {
        $searchData = $this->loadData('attribute_search_data',
                array(
                    'attribute_code'  => 'price',
                    'attribute_lable' => 'Price',
                    'system'          => 'Yes'
                ));
        //Steps
        $this->productAttributeHelper()->openAttribute($searchData);
        //Verifying
        $this->assertFalse($this->buttonIsPresent('delete_attribute'),
                '"Delete Attribute" button is present on the page');
    }

    /**
     * Delete attribute that used in Configurable Product
     *
     * @test
     */
    public function deletedDropdownAttributeUsedInConfigurableProduct()
    {
        //Data
        $attrData = $this->loadData('product_attribute_dropdown_with_options', null,
                array('admin_title', 'attribute_code'));
        $associatedAttributes = $this->loadData('associated_attributes',
                array('General' => $attrData['attribute_code']));
        $productData = $this->loadData('configurable_product_required',
                array('configurable_attribute_title' => $attrData['admin_title']),
                array('general_sku', 'general_name'));
        $searchData = $this->loadData('attribute_search_data',
                array(
                    'attribute_code'  => $attrData['attribute_code'],
                    'attribute_lable' => $attrData['admin_title']
                ));
        //Steps
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
        $this->navigate('manage_attributes');
        $this->productAttributeHelper()->openAttribute($searchData);
        $this->clickButtonAndConfirm('delete_attribute', 'delete_confirm_message');
        //Verifying
        $this->assertMessagePresent('error', 'attribute_used_in_configurable');
    }
}