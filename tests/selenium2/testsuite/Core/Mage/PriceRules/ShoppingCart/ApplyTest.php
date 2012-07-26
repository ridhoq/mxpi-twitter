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
 * Applying rules for SCPR tests
 *
 * @package     selenium
 * @subpackage  tests
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class Core_Mage_PriceRules_ShoppingCart_ApplyTest extends Mage_Selenium_TestCase
{
    /**
     * <p>Setup:</p>
     * <p>Configure system for tests</p>
     */
    public function setUpBeforeTests()
    {
        $this->loginAdminUser();
        $this->navigate('system_configuration');
        $this->systemConfigurationHelper()->configure('default_tax_config');
        $this->systemConfigurationHelper()->configure('flatrate_enable');
    }

    /**
     * <p>Preconditions:</p>
     * <p>Login Admin to backend</p>
     */
    protected function assertPreConditions()
    {
        $this->loginAdminUser();
        $this->addParameter('id', '0');
    }

    /**
     * Create Customer for tests
     * @return array    Returns array with the registration info
     * @test
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
     * @return string   Returns category path
     * @test
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
     *
     * @param string $category  String with the category path
     *
     * @return array    Returns the array with the sku and name of newly created products
     * @depends createCategory
     * @test
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
        $this->reindexInvalidedData();
        $this->clearInvalidedCache();
        return $products;
    }

    /**
     * <p>Create Shopping cart price rule</p>
     * <p>Steps:</p>
     * <p>1. Navigate to Promotions - Shopping Cart Price Rules;</p>
     * <p>2. Fill form for SCPR (Type of discount is provided via data provider);
     * Select specific category in conditions; Add coupon that should be applied;</p>
     * <p>3. Save newly created SCPR;</p>
     * <p>4. Navigate to frontend;</p>
     * <p>5. Add product(s) for which rule should be applied to shopping cart;</p>
     * <p>6. Apply coupon for the shopping cart;</p>
     * <p>6. Verify prices for the product(s) in the totals of shopping cart;</p>
     * <p>Expected results:</p>
     * <p>Rule is created; Totals changed after applying coupon; Rule is discounting percent of each product;</p>
     *
     * @param string $ruleType
     * @param array  $customer  Array with the customer information for logging in to the frontend
     * @param string $category  String with the category path for creating rules
     * @param array  $products  Array with the products' names and sku for validating prices on the frontend
     *
     * @test
     * @dataProvider createSCPRDataProvider
     * @depends createCustomer
     * @depends createCategory
     * @depends createProducts
     * @TestlinkId TL-MAGE-3563
     */
    public function createSCPR($ruleType, $customer, $category, $products)
    {
        $cartProductsData = $this->loadData('prices_for_' . $ruleType);
        $checkoutData = $this->loadData('totals_for_' . $ruleType);
        $this->navigate('manage_shopping_cart_price_rules');
        $ruleData = $this->loadData('scpr_' . $ruleType,
                                    array('category' => $category),
                                    array('rule_name', 'coupon_code'));
        $this->PriceRulesHelper()->createRule($ruleData);
        $this->assertMessagePresent('success', 'success_saved_rule');
        $this->customerHelper()->frontLoginCustomer($customer);
        $this->shoppingCartHelper()->frontClearShoppingCart();
        foreach ($products['name'] as $key => $productName) {
            $cartProductsData['product_' . $key]['product_name'] = $productName;
            $this->productHelper()->frontOpenProduct($productName);
            $this->productHelper()->frontAddProductToCart();
        }
        $this->shoppingCartHelper()->frontEstimateShipping('estimate_shipping', 'shipping_flatrate');
        $this->addParameter('couponCode', $ruleData['info']['coupon_code']);
        $this->fillForm(array('coupon_code' => $ruleData['info']['coupon_code']));
        $this->clickButton('apply_coupon');
        $this->assertMessagePresent('success', 'success_applied_coupon');
        $this->shoppingCartHelper()->verifyPricesDataOnPage($cartProductsData, $checkoutData);
    }

    /**
     * Data Provider for SCPR
     * @return array
     */
    public function createSCPRDataProvider()
    {
        return array(
            array('percent_of_product_price_discount'),
            array('fixed_amount_discount'),
            array('fixed_amount_discount_for_whole_cart')
        );
    }
}
