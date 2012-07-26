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
 * Create Widget Test
 *
 * @package     selenium
 * @subpackage  tests
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class Core_Mage_CmsWidgets_CreateTest extends Mage_Selenium_TestCase
{
    protected static $products = array();

    public function setUpBeforeTests()
    {
        $this->loginAdminUser();
    }

    protected function assertPreconditions()
    {
        $this->addParameter('id', '0');
    }

    /**
     * <p>Preconditions</p>
     * <p>Creates Category to use during tests</p>
     * @test
     * @return string
     */
    public function createCategory()
    {
        //Data
        $categoryData = $this->loadData('sub_category_required');
        //Steps
        $this->navigate('manage_categories', false);
        $this->categoryHelper()->checkCategoriesPage();
        $this->categoryHelper()->createCategory($categoryData);
        //Verification
        $this->assertMessagePresent('success', 'success_saved_category');
        $this->categoryHelper()->checkCategoriesPage();

        return $categoryData['parent_category'] . '/' . $categoryData['name'];
    }

    /**
     * <p>Preconditions</p>
     * <p>Creates Attribute (dropdown) to use during tests</p>
     * @test
     * @return array
     */
    public function createAttribute()
    {
        $attrData = $this->loadData('product_attribute_dropdown_with_options', null,
                                    array('admin_title', 'attribute_code'));
        $associatedAttributes = $this->loadData('associated_attributes',
                                                array('General' => $attrData['attribute_code']));
        $this->navigate('manage_attributes');
        $this->productAttributeHelper()->createAttribute($attrData);
        $this->assertMessagePresent('success', 'success_saved_attribute');
        $this->navigate('manage_attribute_sets');
        $this->attributeSetHelper()->openAttributeSet();
        $this->attributeSetHelper()->addAttributeToSet($associatedAttributes);
        $this->saveForm('save_attribute_set');
        $this->assertMessagePresent('success', 'success_attribute_set_saved');

        return $attrData;
    }

    /**
     * Create required products for testing
     * @dataProvider createProductsDataProvider
     * @depends createCategory
     * @depends createAttribute
     * @test
     *
     * @param $dataProductType
     * @param $category
     * @param $attrData
     */
    public function createProducts($dataProductType, $category, $attrData)
    {
        $this->navigate('manage_products');
        //Data
        if ($dataProductType == 'configurable') {
            $productData = $this->loadData($dataProductType . '_product_required',
                                           array('configurable_attribute_title' => $attrData['admin_title'],
                                                'categories'                    => $category),
                                           array('general_sku', 'general_name'));
        } else {
            $productData = $this->loadData($dataProductType . '_product_required', array('categories' => $category),
                                           array('general_name', 'general_sku'));
        }
        //Steps
        $this->productHelper()->createProduct($productData, $dataProductType);
        //Verifying
        $this->assertMessagePresent('success', 'success_saved_product');
        self::$products['sku'][$dataProductType] = $productData['general_sku'];
        self::$products['name'][$dataProductType] = $productData['general_name'];
    }

    public function createProductsDataProvider()
    {
        return array(
            array('simple'),
            array('grouped'),
            array('configurable'),
            array('virtual'),
            array('bundle'),
            array('downloadable')
        );
    }

    /**
     * <p>Creates All Types of widgets</p>
     * <p>Steps:</p>
     * <p>1. Navigate to Manage Widgets page</p>
     * <p>2. Create all types of widgets with all fields filled</p>
     * <p>Expected result</p>
     * <p>Widgets are created successfully</p>
     *
     * @param string $dataWidgetType
     * @param string $category
     *
     * @test
     * @dataProvider widgetTypesDataProvider
     * @depends createCategory
     * @depends createProducts
     * @TestlinkId TL-MAGE-3229
     */
    public function createAllTypesOfWidgetsAllFields($dataWidgetType, $category)
    {
        //Data
        $temp['filter_sku'] = self::$products['sku']['simple'];
        $temp['category_path'] = $category;
        $widgetData = $this->loadData($dataWidgetType . '_widget', $temp, 'widget_instance_title');
        $i = 1;
        foreach (self::$products['sku'] as $value) {
            $widgetData['layout_updates']['layout_3']['choose_options']['product_' . $i++]['filter_sku'] = $value;
        }
        $i = 1;
        foreach (self::$products['sku'] as $value) {
            $y = $i + 3;
            $widgetData['layout_updates']['layout_' . $y]['choose_options']['product_' . $i++]['filter_sku'] = $value;
        }
        //Steps
        $this->navigate('manage_cms_widgets');
        $this->cmsWidgetsHelper()->createWidget($widgetData);
        //Verifying
        $this->assertMessagePresent('success', 'successfully_saved_widget');
    }

    public function widgetTypesDataProvider()
    {
        return array(
            array('cms_page_link'),
            array('cms_static_block'),
            array('catalog_category_link'),
            array('catalog_new_products_list'),
            array('catalog_product_link'),
            array('orders_and_returns'),
            array('recently_compared_products'),
            array('recently_viewed_products')
        );
    }

    /**
     * <p>Creates All Types of widgets with required fields only</p>
     * <p>Steps:</p>
     * <p>1. Navigate to Manage Widgets page</p>
     * <p>2. Create all types of widgets with required fields filled</p>
     * <p>Expected result</p>
     * <p>Widgets are created successfully</p>
     *
     * @param string $dataWidgetType
     * @param string $category
     *
     * @test
     * @dataProvider widgetTypesReqDataProvider
     * @depends createCategory
     * @depends createProducts
     * @TestlinkId	TL-MAGE-3230
     */
    public function createAllTypesOfWidgetsReqFields($dataWidgetType, $category)
    {
        //Data
        $temp['filter_sku'] = self::$products['sku']['simple'];
        $temp['category_path'] = $category;
        $widgetData = $this->loadData($dataWidgetType . '_widget_req', $temp, 'widget_instance_title');
        //Steps
        $this->navigate('manage_cms_widgets');
        $this->cmsWidgetsHelper()->createWidget($widgetData);
        //Verifying
        $this->assertMessagePresent('success', 'successfully_saved_widget');
    }

    public function widgetTypesReqDataProvider()
    {
        return array(
            array('cms_page_link'),
            array('cms_static_block'),
            array('catalog_category_link'),
            array('catalog_new_products_list'),
            array('catalog_product_link'),
            array('orders_and_returns'),
            array('recently_compared_products'),
            array('recently_viewed_products')
        );
    }

    /**
     * <p>Creates All Types of widgets with required fields empty</p>
     * <p>Steps:</p>
     * <p>1. Navigate to Manage Widgets page</p>
     * <p>2. Create all types of widgets with required fields empty</p>
     * <p>Expected result</p>
     * <p>Widgets are not created. Message about required field empty appears.</p>
     *
     * @param string $dataWidgetType
     * @param string $emptyField
     * @param string $fieldType
     * @param string $category
     *
     * @test
     * @dataProvider withEmptyFieldsDataProvider
     * @depends createCategory
     * @depends createProducts
     * @TestlinkId	TL-MAGE-3231
     */
    public function withEmptyFields($dataWidgetType, $emptyField, $fieldType, $category)
    {
        //Data
        $temp['filter_sku'] = self::$products['sku']['simple'];
        $temp['category_path'] = $category;
        if ($fieldType == 'field') {
            $temp[$emptyField] = ' ';
        } elseif ($fieldType == 'dropdown') {
            if ($emptyField == 'select_display_on') {
                $temp['select_block_reference'] = '%noValue%';
                $temp['select_template'] = '%noValue%';
            }
            $temp[$emptyField] = '-- Please Select --';
        } else {
            $temp['widget_options'] = '%noValue%';
            $this->addParameter('elementName', 'Not Selected');
        }
        $widgetData = $this->loadData($dataWidgetType . '_widget_req', $temp);
        //Steps
        $this->navigate('manage_cms_widgets');
        $this->cmsWidgetsHelper()->createWidget($widgetData);
        //Verifying
        $this->addFieldIdToMessage($fieldType, $emptyField);
        $this->assertMessagePresent('validation', 'empty_required_field');
        $this->assertTrue($this->verifyMessagesCount(), $this->getParsedMessages());
    }

    public function withEmptyFieldsDataProvider()
    {
        return array(
            array('cms_page_link', 'widget_instance_title', 'field'),
            array('cms_page_link', 'page_id', 'pageelement'),
            array('cms_page_link', 'select_display_on', 'dropdown'),
            array('cms_page_link', 'select_block_reference', 'dropdown'),
            array('cms_static_block', 'widget_instance_title', 'field'),
            array('cms_static_block', 'block_id', 'pageelement'),
            array('cms_static_block', 'select_display_on', 'dropdown'),
            array('cms_static_block', 'select_block_reference', 'dropdown'),
            array('catalog_category_link', 'widget_instance_title', 'field'),
            array('catalog_category_link', 'category_id', 'pageelement'),
            array('catalog_category_link', 'select_display_on', 'dropdown'),
            array('catalog_category_link', 'select_block_reference', 'dropdown'),
            array('catalog_new_products_list', 'widget_instance_title', 'field'),
            array('catalog_new_products_list', 'number_of_products_to_display', 'field'),
            array('catalog_new_products_list', 'select_display_on', 'dropdown'),
            array('catalog_new_products_list', 'select_block_reference', 'dropdown'),
            array('catalog_product_link', 'widget_instance_title', 'field'),
            array('catalog_product_link', 'category_id', 'pageelement'),
            array('catalog_product_link', 'select_display_on', 'dropdown'),
            array('catalog_product_link', 'select_block_reference', 'dropdown'),
            array('orders_and_returns', 'widget_instance_title', 'field'),
            array('orders_and_returns', 'select_display_on', 'dropdown'),
            array('orders_and_returns', 'select_block_reference', 'dropdown'),
            array('recently_compared_products', 'widget_instance_title', 'field'),
            array('recently_compared_products', 'number_of_products_to_display_compared_and_viewed', 'field'),
            array('recently_compared_products', 'select_display_on', 'dropdown'),
            array('recently_compared_products', 'select_block_reference', 'dropdown'),
            array('recently_viewed_products', 'widget_instance_title', 'field'),
            array('recently_viewed_products', 'number_of_products_to_display_compared_and_viewed', 'field'),
            array('recently_viewed_products', 'select_display_on', 'dropdown'),
            array('recently_viewed_products', 'select_block_reference', 'dropdown')
        );
    }
}