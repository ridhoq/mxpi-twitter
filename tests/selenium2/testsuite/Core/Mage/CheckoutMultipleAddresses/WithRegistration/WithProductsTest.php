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
 * Checkout Multiple Addresses tests with different product types
 *
 * @package     selenium
 * @subpackage  tests
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class Core_Mage_CheckoutMultipleAddresses_WithRegistration_WithProductsTest extends Mage_Selenium_TestCase
{
    /**
     * @var array
     */
    protected static $_products = array();

    /**
     * <p>Login to backend</p>
     */
    public function assertPreConditions()
    {
        $this->loginAdminUser();
    }

    /**
     * <p>Preconditions</p>
     * <p>Create attribute</p>
     *
     * @return array $attrData
     *
     * @test
     */
    public function createAttribute()
    {
        //Data
        $attrData = $this->loadData('product_attribute_dropdown_with_options', null,
                array('admin_title', 'attribute_code'));
        $associatedAttributes = $this->loadData('associated_attributes',
                array('General' => $attrData['attribute_code']));
        //Steps
        $this->loginAdminUser();
        $this->navigate('manage_attributes');
        $this->productAttributeHelper()->createAttribute($attrData);
        $this->assertMessagePresent('success', 'success_saved_attribute');
        $this->navigate('manage_attribute_sets');
        $this->attributeSetHelper()->openAttributeSet();
        $this->attributeSetHelper()->addAttributeToSet($associatedAttributes);
        $this->addParameter('attributeName', 'Default');
        $this->saveForm('save_attribute_set');
        //Verification
        $this->assertMessagePresent('success', 'success_attribute_set_saved');

        return $attrData;
    }

    /**
     * <p>Checkout with multiple addresses simple/virtual/downloadable products and adding it to bundle and</p>
     * <p>associated product</p>
     * <p>Preconditions:</p>
     * <p>1.Product is created;</p>
     * <p>Steps:</p>
     * <p>1. Open product page;</p>
     * <p>2. Add product to Shopping Cart;</p>
     * <p>3. Click "Checkout with Multiple Addresses";</p>
     * <p>4. Select Checkout Method with Registering;</p>
     * <p>5. Fill in Personal/Address/Login info;</p>
     * <p>6. Check success registration message;</p>
     * <p>7. Select Shipping Addresses and click "Continue to Shipping Information";</p>
     * <p>8. Fill in Shipping Information tab and click "Continue to Billing Information";</p>
     * <p>9. Fill in Billing Information tab and click "Continue to Review Your Order";</p>
     * <p>10. Verify information into "Order Review" tab;</p>
     * <p>11. Place order;</p>
     * <p>Expected result:</p>
     * <p>Checkout is successful;</p>
     *
     * @param string $productDataSet
     * @param string $productType
     * @param array $attrData
     *
     * @test
     * @dataProvider createProductForAssociatedDataProvider
     * @depends createAttribute
     * @TestlinkId	TL-MAGE-523
     */
    public function createProductForAssociated($productDataSet, $productType, $attrData)
    {
        //Data
        $productData = $this->loadData($productDataSet, null, array('general_name', 'general_sku'));
        $productData['general_user_attr']['dropdown'][$attrData['attribute_code']] =
                $attrData['option_1']['admin_option_name'];
        $checkoutData = $this->loadData('multiple_with_register_flatrate_checkmoney');
        $checkoutData['shipping_address_data']['address_to_ship_1']['general_name'] = $productData['general_name'];
        $checkoutData['products_to_add']['product_1']['general_name'] = $productData['general_name'];
        if(preg_match('/virtual_product_visible/', $productDataSet) ||
           preg_match('/downloadable_product_visible_multi_checkout/', $productDataSet)) {
                $checkoutData['products_to_add']['product_2'] = self::$_products['simple_product_visible'];
                $checkoutData['shipping_address_data']['address_to_ship_1']['general_name'] =
                        self::$_products['simple_product_visible']['general_name'];
            }
        //Steps
        $this->navigate('manage_products');
        $this->productHelper()->createProduct($productData, $productType);
        //Verification
        $this->assertMessagePresent('success', 'success_saved_product');

        self::$_products[$productDataSet]['general_name'] = $productData['general_name'];
        self::$_products[$productDataSet]['general_sku'] = $productData['general_sku'];
        //Steps
        $this->logoutCustomer();
        $this->checkoutMultipleAddressesHelper()->frontCreateMultipleCheckout($checkoutData);
        //Verification
        $this->assertMessagePresent('success', 'success_checkout');
        }

    /**
     * <p>Data provider for createProductForAssociated test</p>
     *
     * @return array
     */
    public function createProductForAssociatedDataProvider()
    {
        return array(
            array('simple_product_visible', 'simple'),
            array('virtual_product_visible', 'virtual'),
            array('downloadable_product_visible_multi_checkout', 'downloadable')
        );
    }

    /**
     * <p>Checkout with multiple addresses simple/virtual/downloadable products with custom options</p>
     * <p>Preconditions:</p>
     * <p>1.Product is created;</p>
     * <p>Steps:</p>
     * <p>1. Open product page;</p>
     * <p>2. Add product to Shopping Cart;</p>
     * <p>3. Click "Checkout with Multiple Addresses";</p>
     * <p>4. Select Checkout Method with Registering;</p>
     * <p>5. Fill in Personal/Address/Login info;</p>
     * <p>6. Check success registration message;</p>
     * <p>7. Select Shipping Addresses and click "Continue to Shipping Information";</p>
     * <p>8. Fill in Shipping Information tab and click "Continue to Billing Information";</p>
     * <p>9. Fill in Billing Information tab and click "Continue to Review Your Order";</p>
     * <p>10. Verify information into "Order Review" tab;</p>
     * <p>11. Place order;</p>
     * <p>Expected result:</p>
     * <p>Checkout is successful;</p>
     *
     * @param string $productType
     * @param string $productDataSet
     *
     * @test
     * @dataProvider createSimpleTypesProductsDataProvider
     * @TestlinkId	TL-MAGE-5326
     */
    public function createSimpleTypesProducts($productDataSet, $productType)
    {
        //Data
        $productData = $this->loadData($productDataSet);
        $customOptions = $this->loadData('custom_options_to_add_to_shopping_cart');
        $customOptionsDownloadableLinks = $this->loadData('downloadable_options_to_add_to_shopping_cart');
        $checkoutData = $this->loadData('multiple_with_register_flatrate_checkmoney');
        $checkoutData['shipping_address_data']['address_to_ship_1']['general_name'] = $productData['general_name'];
        $checkoutData['products_to_add']['product_1']['general_name'] = $productData['general_name'];
        if(preg_match('/virtual_/', $productDataSet) || preg_match('/downloadable_/', $productDataSet)) {
            $checkoutData['products_to_add']['product_2'] = self::$_products['simple_product_visible'];
            $checkoutData['shipping_address_data']['address_to_ship_1']['general_name'] =
                self::$_products['simple_product_visible']['general_name'];
        }
        if(preg_match('/_options/', $productDataSet)) {
            $checkoutData['products_to_add']['product_1']['options'] = $customOptions;
        }
        if(preg_match('/_options_links/', $productDataSet)) {
            $checkoutData['products_to_add']['product_1']['options'] = $customOptions;
            $checkoutData['products_to_add']['product_1']['options']['option_10'] =
                $customOptionsDownloadableLinks['option_1'];
        }
        //Steps
        $this->navigate('manage_products');
        $this->productHelper()->createProduct($productData, $productType);
        //Verification
        $this->assertMessagePresent('success', 'success_saved_product');
        //Steps
        $this->logoutCustomer();
        $this->checkoutMultipleAddressesHelper()->frontCreateMultipleCheckout($checkoutData);
        //Verification
        $this->assertMessagePresent('success', 'success_checkout');
    }

    /**
     * <p>Data provider for createSimpleTypesProducts test</p>
     *
     * @return array
     */
    public function createSimpleTypesProductsDataProvider()
    {
        return array(
            array('simple_multi_checkout_options', 'simple'),
            array('virtual_multi_checkout_options', 'virtual'),
            array('downloadable_multi_checkout_options_no_links', 'downloadable'),
            array('downloadable_multi_checkout_no_links', 'downloadable'),
            array('downloadable_multi_checkout_options_links', 'downloadable'),
            array('downloadable_multi_checkout', 'downloadable')
        );
    }

    /**
     * <p>Checkout with multiple addresses grouped product</p>
     * <p>Preconditions:</p>
     * <p>1.Product is created;</p>
     * <p>Steps:</p>
     * <p>1. Open product page;</p>
     * <p>2. Add product to Shopping Cart;</p>
     * <p>3. Click "Checkout with Multiple Addresses";</p>
     * <p>4. Select Checkout Method with Registering;</p>
     * <p>5. Fill in Personal/Address/Login info;</p>
     * <p>6. Check success registration message;</p>
     * <p>7. Select Shipping Addresses and click "Continue to Shipping Information";</p>
     * <p>8. Fill in Shipping Information tab and click "Continue to Billing Information";</p>
     * <p>9. Fill in Billing Information tab and click "Continue to Review Your Order";</p>
     * <p>10. Verify information into "Order Review" tab;</p>
     * <p>11. Place order;</p>
     * <p>Expected result:</p>
     * <p>Checkout is successful;</p>
     *
     * @test
     * @TestlinkId	TL-MAGE-5328
     */
    public function createGroupedProduct()
    {
        //Data
        $productData = $this->loadData('grouped_product_visible',
            array('associated_search_sku' => self::$_products['simple_product_visible']['general_sku']),
            array('general_name', 'general_sku'));
        $productData['associated_grouped_data']['associated_grouped_2'] = $this->loadData('associated_grouped',
            array('associated_search_sku' => self::$_products['virtual_product_visible']['general_sku']));
        $productData['associated_grouped_data']['associated_grouped_3'] = $this->loadData('associated_grouped',
            array('associated_search_sku' =>
                self::$_products['downloadable_product_visible_multi_checkout']['general_sku']));
        $checkoutData = $this->loadData('multiple_with_register_flatrate_checkmoney');
        $checkoutData['shipping_address_data']['address_to_ship_1']['general_name'] =
                self::$_products['simple_product_visible']['general_name'];
        $checkoutData['products_to_add']['product_1']['general_name'] = $productData['general_name'];
        $checkoutDataGrouped = $this->loadData('grouped_options_to_add_to_shopping_cart');
        $checkoutDataGrouped['option_1']['parameters']['subproductName'] =
                self::$_products['simple_product_visible']['general_name'];
        $checkoutDataGrouped['option_2']['parameters']['subproductName'] =
                self::$_products['virtual_product_visible']['general_name'];
        $checkoutDataGrouped['option_3']['parameters']['subproductName'] =
                self::$_products['downloadable_product_visible_multi_checkout']['general_name'];
        $checkoutData['products_to_add']['product_1']['options'] = $checkoutDataGrouped;
        //Steps
        $this->navigate('manage_products');
        $this->productHelper()->createProduct($productData, 'grouped');
        //Verification
        $this->assertMessagePresent('success', 'success_saved_product');
        //Steps
        $this->logoutCustomer();
        $this->checkoutMultipleAddressesHelper()->frontCreateMultipleCheckout($checkoutData);
        //Verification
        $this->assertMessagePresent('success', 'success_checkout');
    }

    /**
     * <p>Checkout with multiple addresses bundle products</p>
     * <p>Preconditions:</p>
     * <p>1.Product is created;</p>
     * <p>Steps:</p>
     * <p>1. Open product page;</p>
     * <p>2. Add product to Shopping Cart;</p>
     * <p>3. Click "Checkout with Multiple Addresses";</p>
     * <p>4. Select Checkout Method with Registering;</p>
     * <p>5. Fill in Personal/Address/Login info;</p>
     * <p>6. Check success registration message;</p>
     * <p>7. Select Shipping Addresses and click "Continue to Shipping Information";</p>
     * <p>8. Fill in Shipping Information tab and click "Continue to Billing Information";</p>
     * <p>9. Fill in Billing Information tab and click "Continue to Review Your Order";</p>
     * <p>10. Verify information into "Order Review" tab;</p>
     * <p>11. Place order;</p>
     * <p>Expected result:</p>
     * <p>Checkout is successful;</p>
     *
     * @param string $productDataSet
     *
     * @test
     * @dataProvider createBundleProductsDataProvider
     * @TestlinkId	TL-MAGE-5329
     */
    public function createBundleProducts($productDataSet)
    {
        //Data
        $productData = $this->loadData($productDataSet, array('add_product_1/bundle_items_search_sku' =>
            self::$_products['simple_product_visible']['general_sku'],
            'add_product_2/bundle_items_search_sku' =>
            self::$_products['virtual_product_visible']['general_sku']));
        $customOptions = $this->loadData('custom_options_to_add_to_shopping_cart');
        $customOptionsBundle = $this->loadData('bundle_options_to_add_to_shopping_cart',
            array('custom_option_multiselect' => self::$_products['simple_product_visible']['general_name'],
            'optionTitle' => self::$_products['simple_product_visible']['general_name'],
            'custom_option_dropdown' => self::$_products['simple_product_visible']['general_name']));
        $checkoutData = $this->loadData('multiple_with_register_flatrate_checkmoney');
        $checkoutData['shipping_address_data']['address_to_ship_1']['general_name'] = $productData['general_name'];
        $checkoutData['products_to_add']['product_1']['general_name'] = $productData['general_name'];
        $checkoutData['products_to_add']['product_1']['options'] = $customOptionsBundle;
        if(preg_match('/fixed_bundle_multi_checkout_options/', $productDataSet)) {
            $checkoutData['products_to_add']['product_1']['options'] = $customOptions;
            $checkoutData['products_to_add']['product_1']['options']['option_10'] = $customOptionsBundle['option_1'];
            $checkoutData['products_to_add']['product_1']['options']['option_11'] = $customOptionsBundle['option_2'];
            $checkoutData['products_to_add']['product_1']['options']['option_12'] = $customOptionsBundle['option_3'];
            $checkoutData['products_to_add']['product_1']['options']['option_13'] = $customOptionsBundle['option_4'];
            }
        if(preg_match('/dynamic_bundle_multi_checkout_options/', $productDataSet)) {
            unset($checkoutData['products_to_add']['product_1']['options']);
            $checkoutData['products_to_add']['product_1']['options'] = $customOptionsBundle;
            }
        //Steps
        $this->navigate('manage_products');
        $this->productHelper()->createProduct($productData, 'bundle');
        //Verification
        $this->assertMessagePresent('success', 'success_saved_product');
        //Steps
        $this->logoutCustomer();
        $this->checkoutMultipleAddressesHelper()->frontCreateMultipleCheckout($checkoutData);
        //Verification
        $this->assertMessagePresent('success', 'success_checkout');
    }

    /**
     * <p>Data provider for createBundleProducts test</p>
     *
     * @return array
     */
    public function createBundleProductsDataProvider()
    {
        return array(
            array('fixed_bundle_multi_checkout'),
            array('dynamic_bundle_multi_checkout'),
            array('fixed_bundle_multi_checkout_options'),
            array('dynamic_bundle_multi_checkout_options')
        );
    }

    /**
     * <p>Checkout with multiple addresses configurable product with associated simple</p>
     * <p>Preconditions:</p>
     * <p>1.Product is created;</p>
     * <p>Steps:</p>
     * <p>1. Open product page;</p>
     * <p>2. Add product to Shopping Cart;</p>
     * <p>3. Click "Checkout with Multiple Addresses";</p>
     * <p>4. Select Checkout Method with Registering;</p>
     * <p>5. Fill in Personal/Address/Login info;</p>
     * <p>6. Check success registration message;</p>
     * <p>7. Select Shipping Addresses and click "Continue to Shipping Information";</p>
     * <p>8. Fill in Shipping Information tab and click "Continue to Billing Information";</p>
     * <p>9. Fill in Billing Information tab and click "Continue to Review Your Order";</p>
     * <p>10. Verify information into "Order Review" tab;</p>
     * <p>11. Place order;</p>
     * <p>Expected result:</p>
     * <p>Checkout is successful;</p>
     *
     * @param string $productDataSet
     * @param array $attrData
     *
     * @test
     * @dataProvider createConfigurableDataProvider
     * @depends createAttribute
     * @TestlinkId TL-MAGE-5330
     */
    public function createConfigurableWithSimple($productDataSet, $attrData)
    {
        //Data
        $productData = $this->loadData($productDataSet, array(
            'configurable_attribute_title' => $attrData['admin_title'],
            'associated_configurable_1/associated_search_sku' =>
            self::$_products['simple_product_visible']['general_sku']));
        $customOptionsConfig = $this->loadData('configurable_options_to_add_to_shopping_cart');
        $customOptions = $this->loadData('custom_options_to_add_to_shopping_cart');
        $checkoutData = $this->loadData('multiple_with_register_flatrate_checkmoney');
        $checkoutData['shipping_address_data']['address_to_ship_1']['general_name'] = $productData['general_name'];
        $checkoutData['products_to_add']['product_1']['general_name'] = $productData['general_name'];
        $customOptionsConfig['option_1']['parameters']['title'] = $attrData['admin_title'];
        $customOptionsConfig['option_1']['options_to_choose']['custom_option_dropdown'] =
            $attrData['option_1']['store_view_titles']['Default Store View'];
        $checkoutData['products_to_add']['product_1']['options'] = $customOptionsConfig;
        if(preg_match('/_options/', $productDataSet)) {
            $checkoutData['products_to_add']['product_1']['options'] = $customOptions;
            $checkoutData['products_to_add']['product_1']['options']['option_10'] = $customOptionsConfig['option_1'];
        }
        //Steps
        $this->navigate('manage_products');
        $this->productHelper()->createProduct($productData, 'configurable');
        //Verification
        $this->assertMessagePresent('success', 'success_saved_product');
        //Steps
        $this->logoutCustomer();
        $this->checkoutMultipleAddressesHelper()->frontCreateMultipleCheckout($checkoutData);
        //Verification
        $this->assertMessagePresent('success', 'success_checkout');
    }

    /**
     * <p>Checkout with multiple addresses configurable product with associated virtual</p>
     * <p>Preconditions:</p>
     * <p>1.Product is created;</p>
     * <p>Steps:</p>
     * <p>1. Open product page;</p>
     * <p>2. Add product to Shopping Cart;</p>
     * <p>3. Click "Checkout with Multiple Addresses";</p>
     * <p>4. Select Checkout Method with Registering;</p>
     * <p>5. Fill in Personal/Address/Login info;</p>
     * <p>6. Check success registration message;</p>
     * <p>7. Select Shipping Addresses and click "Continue to Shipping Information";</p>
     * <p>8. Fill in Shipping Information tab and click "Continue to Billing Information";</p>
     * <p>9. Fill in Billing Information tab and click "Continue to Review Your Order";</p>
     * <p>10. Verify information into "Order Review" tab;</p>
     * <p>11. Place order;</p>
     * <p>Expected result:</p>
     * <p>Checkout is successful;</p>
     *
     * @param string $productDataSet
     * @param array $attrData
     *
     * @test
     * @dataProvider createConfigurableDataProvider
     * @depends createAttribute
     * @TestlinkId	TL-MAGE-5331
     */
    public function createConfigurableWithVirtual($productDataSet, $attrData)
    {
        //Data
        $productData = $this->loadData($productDataSet, array(
            'configurable_attribute_title' => $attrData['admin_title'],
            'associated_configurable_1/associated_search_sku' =>
            self::$_products['virtual_product_visible']['general_sku']));
        $customOptionsConfig = $this->loadData('configurable_options_to_add_to_shopping_cart');
        $customOptions = $this->loadData('custom_options_to_add_to_shopping_cart');
        $checkoutData = $this->loadData('multiple_with_register_flatrate_checkmoney');
        $checkoutData['shipping_address_data']['address_to_ship_1']['general_name'] =
            self::$_products['simple_product_visible']['general_name'];
        $checkoutData['products_to_add']['product_1']['general_name'] = $productData['general_name'];
        $checkoutData['products_to_add']['product_2']['general_name'] =
            self::$_products['simple_product_visible']['general_name'];
        $customOptionsConfig['option_1']['parameters']['title'] = $attrData['admin_title'];
        $customOptionsConfig['option_1']['options_to_choose']['custom_option_dropdown'] =
            $attrData['option_1']['store_view_titles']['Default Store View'];
        $checkoutData['products_to_add']['product_1']['options'] = $customOptionsConfig;
        if(preg_match('/_options/', $productDataSet)) {
            $checkoutData['products_to_add']['product_1']['options'] = $customOptions;
            $checkoutData['products_to_add']['product_1']['options']['option_10'] = $customOptionsConfig['option_1'];
        }
        //Steps
        $this->navigate('manage_products');
        $this->productHelper()->createProduct($productData, 'configurable');
        //Verification
        $this->assertMessagePresent('success', 'success_saved_product');
        //Steps
        $this->logoutCustomer();
        $this->checkoutMultipleAddressesHelper()->frontCreateMultipleCheckout($checkoutData);
        //Verification
        $this->assertMessagePresent('success', 'success_checkout');
    }

    /**
     * <p>Checkout with multiple addresses configurable product with associated downloadable</p>
     * <p>Preconditions:</p>
     * <p>1.Product is created;</p>
     * <p>Steps:</p>
     * <p>1. Open product page;</p>
     * <p>2. Add product to Shopping Cart;</p>
     * <p>3. Click "Checkout with Multiple Addresses";</p>
     * <p>4. Select Checkout Method with Registering;</p>
     * <p>5. Fill in Personal/Address/Login info;</p>
     * <p>6. Check success registration message;</p>
     * <p>7. Select Shipping Addresses and click "Continue to Shipping Information";</p>
     * <p>8. Fill in Shipping Information tab and click "Continue to Billing Information";</p>
     * <p>9. Fill in Billing Information tab and click "Continue to Review Your Order";</p>
     * <p>10. Verify information into "Order Review" tab;</p>
     * <p>11. Place order;</p>
     * <p>Expected result:</p>
     * <p>Checkout is successful;</p>
     *
     * @param string $productDataSet
     * @param array $attrData
     *
     * @test
     * @dataProvider createConfigurableDataProvider
     * @depends createAttribute
     * @TestlinkId	TL-MAGE-5332
     */
    public function createConfigurableWithDownloadable($productDataSet, $attrData)
    {
        //Data
        $productData = $this->loadData($productDataSet, array(
            'configurable_attribute_title' => $attrData['admin_title'],
            'associated_configurable_1/associated_search_sku' =>
            self::$_products['downloadable_product_visible_multi_checkout']['general_sku']));
        $customOptionsConfig = $this->loadData('configurable_options_to_add_to_shopping_cart');
        $customOptions = $this->loadData('custom_options_to_add_to_shopping_cart');
        $checkoutData = $this->loadData('multiple_with_register_flatrate_checkmoney');
        $checkoutData['shipping_address_data']['address_to_ship_1']['general_name'] =
            self::$_products['simple_product_visible']['general_name'];
        $checkoutData['products_to_add']['product_1']['general_name'] = $productData['general_name'];
        $checkoutData['products_to_add']['product_2']['general_name'] =
            self::$_products['simple_product_visible']['general_name'];
        $customOptionsConfig['option_1']['parameters']['title'] = $attrData['admin_title'];
        $customOptionsConfig['option_1']['options_to_choose']['custom_option_dropdown'] =
            $attrData['option_1']['store_view_titles']['Default Store View'];
        $checkoutData['products_to_add']['product_1']['options'] = $customOptionsConfig;
        if(preg_match('/_options/', $productDataSet)) {
            $checkoutData['products_to_add']['product_1']['options'] = $customOptions;
            $checkoutData['products_to_add']['product_1']['options']['option_10'] = $customOptionsConfig['option_1'];
        }
        //Steps
        $this->navigate('manage_products');
        $this->productHelper()->createProduct($productData, 'configurable');
        //Verification
        $this->assertMessagePresent('success', 'success_saved_product');
        //Steps
        $this->logoutCustomer();
        $this->checkoutMultipleAddressesHelper()->frontCreateMultipleCheckout($checkoutData);
        //Verification
        $this->assertMessagePresent('success', 'success_checkout');
    }

    /**
     * <p>Data provider for createConfigurableWithSimple, createConfigurableWithVirtual, createConfigurableWithDownloadable tests</p>
     *
     * @return array
     */
    public function createConfigurableDataProvider()
    {
        return array(
            array('configurable_multi_checkout'),
            array('configurable_multi_checkout_options')
        );
    }
}
