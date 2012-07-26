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
 * Order creation with different type of products
 *
 * @package     selenium
 * @subpackage  tests
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class Core_Mage_Order_Create_WithDifferentProductsTest extends Mage_Selenium_TestCase
{
    /**
     * <p>Preconditions:</p>
     *
     * <p>Log in to Backend.</p>
     * <p>Navigate to 'Manage Products' page</p>
     */
    public function setUpBeforeTests()
    {
        $this->loginAdminUser();
    }

    /**
     * Create all types of products
     * @return array
     * @test
     */
    public function preconditionsForTests()
    {
        //Data
        $attrData = $this->loadDataSet('ProductAttribute', 'product_attribute_dropdown_with_options');
        $attrCode = $attrData['attribute_code'];
        $associatedAttributes = $this->loadDataSet('AttributeSet', 'associated_attributes',
                                                   array('General' => $attrData['attribute_code']));
        $simple = $this->loadDataSet('Product', 'simple_product_visible');
        $simple['general_user_attr']['dropdown'][$attrCode] = $attrData['option_1']['admin_option_name'];
        $virtual = $this->loadDataSet('Product', 'virtual_product_visible');
        $virtual['general_user_attr']['dropdown'][$attrCode] = $attrData['option_2']['admin_option_name'];
        $download = $this->loadDataSet('SalesOrder', 'downloadable_product_for_order',
                                       array('downloadable_links_purchased_separately' => 'No'));
        $download['general_user_attr']['dropdown'][$attrCode] = $attrData['option_3']['admin_option_name'];
        $bundle = $this->loadDataSet('SalesOrder', 'fixed_bundle_for_order', null,
                                     array('add_product_1' => $simple['general_sku'],
                                           'add_product_2' => $virtual['general_sku']));
        $configurable = $this->loadDataSet('SalesOrder', 'configurable_product_for_order',
                                           array('configurable_attribute_title' => $attrData['admin_title']),
                                           array('associated_1' => $simple['general_sku'],
                                                 'associated_2' => $virtual['general_sku'],
                                                 'associated_3' => $download['general_sku']));
        $grouped = $this->loadDataSet('SalesOrder', 'grouped_product_for_order', null,
                                      array('associated_1' => $simple['general_sku'],
                                            'associated_2' => $virtual['general_sku'],
                                            'associated_3' => $download['general_sku']));
        //Steps and Verification
        $this->navigate('manage_attributes');
        $this->productAttributeHelper()->createAttribute($attrData);
        $this->assertMessagePresent('success', 'success_saved_attribute');
        $this->navigate('manage_attribute_sets');
        $this->attributeSetHelper()->openAttributeSet();
        $this->attributeSetHelper()->addAttributeToSet($associatedAttributes);
        $this->saveForm('save_attribute_set');
        $this->assertMessagePresent('success', 'success_attribute_set_saved');
        $this->navigate('manage_products');
        $this->productHelper()->createProduct($simple);
        $this->assertMessagePresent('success', 'success_saved_product');
        $this->productHelper()->createProduct($virtual, 'virtual');
        $this->assertMessagePresent('success', 'success_saved_product');
        $this->productHelper()->createProduct($download, 'downloadable');
        $this->assertMessagePresent('success', 'success_saved_product');
        $this->productHelper()->createProduct($bundle, 'bundle');
        $this->assertMessagePresent('success', 'success_saved_product');
        $this->productHelper()->createProduct($configurable, 'configurable');
        $this->assertMessagePresent('success', 'success_saved_product');
        $this->productHelper()->createProduct($grouped, 'grouped');
        $this->assertMessagePresent('success', 'success_saved_product');

        return array('simple_name'         => $simple['general_name'],
                     'simple_sku'          => $simple['general_sku'],
                     'simple_option'       => $attrData['option_1']['admin_option_name'],
                     'downloadable_name'   => $download['general_name'],
                     'downloadable_sku'    => $download['general_sku'],
                     'downloadable_option' => $attrData['option_3']['admin_option_name'],
                     'virtual_name'        => $virtual['general_name'],
                     'virtual_sku'         => $virtual['general_sku'],
                     'virtual_option'      => $attrData['option_2']['admin_option_name'],
                     'bundle_name'         => $bundle['general_name'],
                     'bundle_sku'          => $bundle['general_sku'],
                     'configurable_name'   => $configurable['general_name'],
                     'configurable_sku'    => $configurable['general_sku'],
                     'grouped_name'        => $grouped['general_name'],
                     'grouped_sku'         => $grouped['general_sku'],
                     'title'               => $attrData['admin_title']);
    }

    /**
     * @param string $productType
     * @param string $order
     * @param array $testData
     *
     * @test
     * @dataProvider withoutOptionsDataProvider
     * @depends preconditionsForTests
     * @TestlinkId	TL-MAGE-3281, TL-MAGE-3282, TL-MAGE-3279
     */
    public function withoutOptions($productType, $order, $testData)
    {
        //Data
        $orderData = $this->loadDataSet('SalesOrder', $order,
                                        array('filter_sku' => $testData[$productType . '_sku']));
        //Steps and Verifying
        $this->orderWorkflow($orderData, $productType);
    }

    /**
     * <p>Creating order with virtual(simple) product with custom options</p>
     * <p>Steps:</p>
     * <p>1. Navigate to "Manage Orders" page;</p>
     * <p>2. Create new order for new customer;</p>
     * <p>3. Select virtual product and add it to the order.
     *       Fill any required information to configure product;</p>
     * <p>4. Fill in all required information.
     *       Shipping address fill and shipping methods should be disabled;</p>
     * <p>5. Click "Submit Order" button;</p>
     * <p>Expected result:</p>
     * <p>Order is created;</p>
     *
     * @param string $productType
     * @param string $order
     *
     * @test
     * @dataProvider productDataProvider
     * @TestlinkId	TL-MAGE-3278
     */
    public function withCustomOptions($productType, $order)
    {
        //Data
        $customOption = $this->loadDataSet('Product', 'custom_options_data');
        $orderCustomOption = $this->loadDataSet('SalesOrder', 'config_option_custom_options');
        $product = $this->loadDataSet('Product', $productType . '_product_visible',
                                      array('custom_options_data' => $customOption));
        $orderData = $this->loadDataSet('SalesOrder', $order,
                                        array('filter_sku'           => $product['general_sku'],
                                              'configurable_options' => $orderCustomOption));
        //Steps and Verifying
        $this->navigate('manage_products');
        $this->productHelper()->createProduct($product, $productType);
        $this->assertMessagePresent('success', 'success_saved_product');
        $this->orderWorkflow($orderData, $productType);
    }

    /**
     * <p>Creating order with downloadable products</p>
     * <p>Steps:</p>
     * <p>1. Navigate to "Manage Orders" page;</p>
     * <p>2. Create new order for new customer;</p>
     * <p>3. Select downloadable product and add it to the order.
     *       Fill any required information to configure product;</p>
     * <p>4. Fill in all required information; Shipping methods and address should be disabled;</p>
     * <p>5. Click "Submit Order" button;</p>
     * <p>Expected result:</p>
     * <p>Order is created;</p>
     *
     * @test
     * @TestlinkId	TL-MAGE-3280
     */
    public function withDownloadableConfigProduct()
    {
        //Data
        $downloadable = $this->loadDataSet('Product', 'downloadable_product_visible');
        $orderProductOption = $this->loadDataSet('SalesOrder', 'config_option_download');
        $orderData = $this->loadDataSet('SalesOrder', 'order_virtual',
                                        array('filter_sku'           => $downloadable['general_sku'],
                                              'configurable_options' => $orderProductOption));
        //Steps and Verifying
        $this->navigate('manage_products');
        $this->productHelper()->createProduct($downloadable, 'downloadable');
        $this->assertMessagePresent('success', 'success_saved_product');
        $this->orderWorkflow($orderData, 'downloadable');
    }

    /**
     * <p>Creating order with bundled products</p>
     * <p>Steps:</p>
     * <p>1. Navigate to "Manage Orders" page;</p>
     * <p>2. Create new order for new customer;</p>
     * <p>3. Select bundled product and add it to the order.
     *       Fill any required information to configure product;</p>
     * <p>4. Fill in all required information</p>
     * <p>5. Click "Submit Order" button;</p>
     * <p>Expected result:</p>
     * <p>Order is created;</p>
     *
     * @param string $productType
     * @param string $order
     * @param array $testData
     *
     * @test
     * @dataProvider productDataProvider
     * @depends preconditionsForTests
     * @TestlinkId	TL-MAGE-3271, TL-MAGE-3272
     */
    public function withBundleProduct($productType, $order, $testData)
    {
        //Order Data
        $multiSelect = $this->loadDataSet('SalesOrder', 'configure_field_multiselect',
                                          array('fieldsValue' => $testData[$productType . '_name']));
        $dropDown = $this->loadDataSet('SalesOrder', 'configure_field_dropdown',
                                       array('fieldsValue' => $testData[$productType . '_name']));
        $checkBox = $this->loadDataSet('SalesOrder', 'configure_field_checkbox',
                                       array('fieldParameter' => $testData[$productType . '_name']));
        $radio = $this->loadDataSet('SalesOrder', 'configure_field_radiobutton',
                                    array('fieldParameter' => $testData[$productType . '_name']));
        $configurable = $this->loadDataSet('SalesOrder', 'config_option_bundle',
                                           array('field_checkbox'    => $checkBox,
                                                 'field_dropdow'     => $dropDown,
                                                 'field_multiselect' => $multiSelect,
                                                 'field_radio'       => $radio));
        $orderData = $this->loadDataSet('SalesOrder', $order,
                                        array('filter_sku'           => $testData['bundle_sku'],
                                              'configurable_options' => $configurable));
        //Steps and Verifying
        $this->orderWorkflow($orderData, $productType);
    }

    /**
     * <p>Creating order with configurable product</p>
     * <p>Steps:</p>
     * <p>1. Navigate to "Manage Orders" page;</p>
     * <p>2. Create new order for new customer;</p>
     * <p>3. Select configurable product and add it to the order.
     *       Fill any required information to configure product;</p>
     * <p>4. Fill in all required information</p>
     * <p>5. Click "Submit Order" button;</p>
     * <p>Expected result:</p>
     * <p>Order is created;</p>
     *
     * @param string $productType
     * @param string $order
     * @param array $testData
     *
     * @test
     * @dataProvider withoutOptionsDataProvider
     * @depends preconditionsForTests
     * @TestlinkId	TL-MAGE-3274, TL-MAGE-3275, TL-MAGE-3273
     */
    public function withConfigurableProduct($productType, $order, $testData)
    {
        //Data
        $orderProductOption = $this->loadDataSet('SalesOrder', 'config_option_configurable',
                                                 array('title'       => $testData['title'],
                                                       'fieldsValue' => $testData[$productType . '_option']));
        $orderData = $this->loadDataSet('SalesOrder', $order,
                                        array('filter_sku'          => $testData['configurable_sku'],
                                              'configurable_options'=> $orderProductOption));
        //Steps and Verifying
        $this->orderWorkflow($orderData, $productType);
    }

    /**
     * <p>Creating order with grouped products</p>
     * <p>Steps:</p>
     * <p>1. Navigate to "Manage Orders" page;</p>
     * <p>2. Create new order for new customer;</p>
     * <p>3. Select group product and add it to the order.
     *       Fill any required information to configure product;</p>
     * <p>4. Fill in all required information</p>
     * <p>5. Click "Submit Order" button;</p>
     * <p>Expected result:</p>
     * <p>Order is created;</p>
     *
     * @param string $productType
     * @param string $order
     * @param array $testData
     *
     * @test
     * @dataProvider withoutOptionsDataProvider
     * @depends preconditionsForTests
     * @TestlinkId	TL-MAGE-3276, TL-MAGE-3277
     */
    public function withGroupedProduct($productType, $order, $testData)
    {
        //Data
        $orderProductOption = $this->loadDataSet('SalesOrder', 'config_option_grouped',
                                                 array('fieldParameter' => $testData[$productType . '_sku']));
        $orderData = $this->loadDataSet('SalesOrder', $order,
                                        array('filter_sku'           => $testData['grouped_sku'],
                                              'configurable_options' => $orderProductOption));
        //Steps and Verifying
        $this->orderWorkflow($orderData, $productType);
    }

    public function productDataProvider()
    {
        return array(
            array('simple', 'order_physical'),
            array('virtual', 'order_virtual')
        );
    }

    public function withoutOptionsDataProvider()
    {
        return array(
            array('simple', 'order_physical'),
            array('virtual', 'order_virtual'),
            array('downloadable', 'order_virtual')
        );
    }

    /**
     * Helper method
     *
     * @param array $orderData
     * @param string $productType
     */
    private function orderWorkflow($orderData, $productType)
    {
        $this->navigate('manage_sales_orders');
        $this->orderHelper()->createOrder($orderData);
        $this->assertMessagePresent('success', 'success_created_order');
        $this->orderInvoiceHelper()->createInvoiceAndVerifyProductQty();
        if ($productType == 'simple') {
            $this->orderShipmentHelper()->createShipmentAndVerifyProductQty();
        }
        $this->orderCreditMemoHelper()->createCreditMemoAndVerifyProductQty('refund_offline');
        $this->clickButton('reorder');
        $this->orderHelper()->submitOrder();
        $this->assertMessagePresent('success', 'success_created_order');
        $this->clickButtonAndConfirm('cancel', 'confirmation_for_cancel');
        $this->assertMessagePresent('success', 'success_canceled_order');
    }
}