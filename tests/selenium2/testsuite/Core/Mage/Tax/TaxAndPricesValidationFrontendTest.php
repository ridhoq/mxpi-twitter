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
 * Prices Validation on the frontend
 *
 * @package     selenium
 * @subpackage  tests
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class Core_Mage_Tax_TaxAndPricesValidationFrontendTest extends Mage_Selenium_TestCase
{
    public function setUpBeforeTests()
    {
        $this->loginAdminUser();
        $this->navigate('system_configuration');
        $this->systemConfigurationHelper()->configure('shipping_settings_default');
        $this->systemConfigurationHelper()->configure('flat_rate_for_price_verification');
        $this->navigate('manage_tax_rule');
        $this->taxHelper()-> deleteRulesExceptSpecified(array('Retail Customer-Taxable Goods-Rate 1'));
    }

    protected function assertPreConditions()
    {
        $this->loginAdminUser();
        $this->addParameter('id', '0');
    }

    /**
     * Create Customer for tests
     *
     * @test
     * @return array
     * @group preConditions
     */
    public function createCustomer()
    {
        //Data
        $userData = $this->loadData('customer_account_for_prices_validation', null, 'email');
        $addressData = $this->loadData('customer_account_address_for_prices_validation');
        //Steps
        $this->navigate('manage_customers');
        $this->customerHelper()->createCustomer($userData, $addressData);
        //Verifying
        $this->assertMessagePresent('success', 'success_saved_customer');
        $customer = array('email'    => $userData['email'],
                          'password' => $userData['password']);
        return $customer;
    }

    /**
     * Create category
     *
     * @test
     * @return string
     * @group preConditions
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
     * Create Simple Products for tests
     * @depends createCategory
     * @test
     *
     * @param $category
     *
     * @return array
     * @group preConditions
     */
    public function createProducts($category)
    {
        $products = array();
        $this->navigate('manage_products');
        for ($i = 1; $i <= 3; $i++) {
            $simpleProductData = $this->loadData('simple_product_for_prices_validation_front_' . $i,
                                                 array('categories' => $category),
                                                 array('general_name', 'general_sku'));
            $products['sku'][$i] = $simpleProductData['general_sku'];
            $products['name'][$i] = $simpleProductData['general_name'];
            $this->productHelper()->createProduct($simpleProductData);
            $this->assertMessagePresent('success', 'success_saved_product');
        }
        return $products;
    }

    /**
     * Create Order on the backend and validate prices with taxes
     *
     * @dataProvider validateTaxFrontendDataProvider
     * @depends createCustomer
     * @depends createProducts
     * @depends createCategory
     * @test
     *
     * @param $sysConfigData
     * @param $customer
     * @param $products
     * @param $category
     *
     * @group skip_due_to_bug
     */
    public function validateTaxFrontend($sysConfigData, $customer, $products, $category)
    {
        //Data
        $category = substr($category, strpos($category, '/') + 1);
        //Preconditions
        $this->navigate('system_configuration');
        $this->systemConfigurationHelper()->configure($sysConfigData);
        $this->customerHelper()->frontLoginCustomer($customer);
        $this->shoppingCartHelper()->frontClearShoppingCart();
        //Verify and add products to shopping cart
        $cartProductsData = $this->loadData($sysConfigData . '_front_prices_in_cart_simple');
        $checkoutData = $this->loadData($sysConfigData . '_front_prices_checkout_data');
        $orderDetailsData = $this->loadData($sysConfigData . '_front_prices_on_order_details');
        foreach ($products['name'] as $key => $productName) {
            $priceInCategory = $this->loadData($sysConfigData . '_front_prices_in_category_simple_' . $key,
                                               array('product_name' => $productName,
                                                    'category'      => $category));
            $priceInProdDetails = $this->loadData($sysConfigData . '_front_prices_in_product_simple_' . $key);
            $this->categoryHelper()->frontOpenCategoryAndValidateProduct($priceInCategory);
            $this->productHelper()->frontOpenProduct($productName, $category);
            $this->categoryHelper()->frontVerifyProductPrices($priceInProdDetails);
            $this->productHelper()->frontAddProductToCart();
            $cartProductsData['product_' . $key]['product_name'] = $productName;
            $checkoutData['validate_prod_data']['product_' . $key]['product_name'] = $productName;
            $orderDetailsData['validate_prod_data']['product_' . $key]['product_name'] = $productName;
        }
        foreach ($products['sku'] as $key => $productSku) {
            $orderDetailsData['validate_prod_data']['product_' . $key]['sku'] = $productSku;
        }
        $this->shoppingCartHelper()->frontEstimateShipping('estimate_shipping', 'shipping_flatrate');
        $this->shoppingCartHelper()->verifyPricesDataOnPage($cartProductsData, $checkoutData['validate_total_data']);
        $orderId = '# ' . $this->checkoutOnePageHelper()->frontCreateCheckout($checkoutData);
        $this->addParameter('orderId', $orderId);
        $this->clickControl('link', 'order_number');
        $this->shoppingCartHelper()->verifyPricesDataOnPage($orderDetailsData['validate_prod_data'],
                                                            $orderDetailsData['validate_total_data']);
    }

    public function validateTaxFrontendDataProvider()
    {
        return array(
            array('unit_cat_ex_ship_in'),
            array('unit_cat_ex_ship_ex'),
            array('row_cat_ex_ship_ex'),
            array('total_cat_ex_ship_ex'),
            array('row_cat_ex_ship_in'),
            array('total_cat_ex_ship_in'),
            array('unit_cat_in_ship_ex'),
            array('row_cat_in_ship_ex'),
            array('total_cat_in_ship_ex'),
            array('unit_cat_in_ship_in'),
            array('row_cat_in_ship_in'),
            array('total_cat_in_ship_in')
        );
    }
}