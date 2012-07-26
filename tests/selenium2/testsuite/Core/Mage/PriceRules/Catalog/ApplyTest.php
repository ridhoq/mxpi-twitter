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
 * Catalog Price Rules applying in frontend
 *
 * @package     selenium
 * @subpackage  tests
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class Core_Mage_PriceRules_Catalog_ApplyTest extends Mage_Selenium_TestCase
{
    protected $_ruleToBeDeleted = array();

    public function setUpBeforeTests()
    {
        $this->loginAdminUser();
        $this->navigate('system_configuration');
        $this->systemConfigurationHelper()->configure('default_tax_config');
    }

    /**
     * <p>Preconditions:</p>
     * <p>Navigate to Promotions -> Catalog Price Rules</p>
     */
    protected function assertPreConditions()
    {
        $this->loginAdminUser();
    }

    protected function tearDownAfterTest()
    {
        if ($this->_ruleToBeDeleted) {
            $this->loginAdminUser();
            $this->navigate('manage_catalog_price_rules');
            $this->priceRulesHelper()->deleteRule($this->_ruleToBeDeleted);
            $this->_ruleToBeDeleted = array();
        }
    }

    /**
     * <p>Preconditions</p>
     * <p>Create Customer for tests</p>
     * <p>Creates Category to use during tests</p>
     * @return array
     * @test
     */
    public function preconditionsForTests()
    {
        //Data
        $userData = $this->loadData('generic_customer_account');
        $categoryData = $this->loadData('sub_category_required');
        $simple = $this->loadData('simple_product_for_price_rules_validation_front',
                                  array('categories'       => $categoryData['parent_category'] . '/' . $categoryData['name'],
                                       'prices_tax_class'  => 'Taxable Goods'));
        //Steps
        $this->navigate('manage_customers');
        $this->customerHelper()->createCustomer($userData);
        //Verifying
        $this->assertMessagePresent('success', 'success_saved_customer');
        //Steps
        $this->navigate('manage_categories', false);
        $this->categoryHelper()->checkCategoriesPage();
        $this->categoryHelper()->createCategory($categoryData);
        //Verifying
        $this->assertMessagePresent('success', 'success_saved_category');
        //Steps
        $this->navigate('manage_products');
        $this->productHelper()->createProduct($simple);
        //Verifying
        $this->assertMessagePresent('success', 'success_saved_product');
        return array(
            'customer'     => array('email'    => $userData['email'],
                                    'password' => $userData['password']),
            'categoryPath' => $categoryData['parent_category'] . '/' . $categoryData['name'],
            'categoryName' => $categoryData['name'],
            'simpleName'   => $simple['general_name']
        );
    }

    /**
     * <p>Create catalog price rule - To Fixed Amount</p>
     * <p>Steps</p>
     * <p>1. Click "Add New Rule"</p>
     * <p>2. Fill in required fields</p>
     * <p>3. Select in "General Information" -> "Customer Groups" = "NOT LOGGED IN"</p>
     * <p>3. Select in "Apply" field option - "To Fixed Amount"</p>
     * <p>4. Specify "Discount Amount" = 10%</p>
     * <p>5. Click "Save and Apply" button</p>
     * <p>Expected result: New rule created, success message appears</p>
     * <p>Verification</p>
     * <p>6. Open product in Frontend as a GUEST</p>
     * <p>7. Verify product special price = $10.00</p>
     * <p>8. Login to Frontend</p>
     * <p>9. Verify product REGULAR PRICE = $120.00</p>
     *
     * @param string $ruleType
     * @param array $testData
     *
     * @test
     * @dataProvider applyRuleToSimpleFrontDataProvider
     * @depends preconditionsForTests
     * @TestlinkId	TL-MAGE-3308
     */
    public function applyRuleToSimpleFront($ruleType, $testData)
    {
        //Data
        $priceRuleData = $this->loadData('test_catalog_rule',
                                         array('category' => $testData['categoryPath'],
                                              'status'    => 'Active',
                                              'actions'   => $this->loadData($ruleType)));
        $productPriceLogged = $this->loadData($ruleType . '_simple_product_logged');
        $productPriceNotLogged = $this->loadData($ruleType . '_simple_product_not_logged');
        $overrideData = array('product_name' => $testData['simpleName'],
                              'category'     => $testData['categoryName']);
        $priceInCategoryLogged = $this->loadData($ruleType . '_simple_logged_category', $overrideData);
        $priceInCategoryNotLogged = $this->loadData($ruleType . '_simple_not_logged_category', $overrideData);
        //Steps
        $this->navigate('manage_catalog_price_rules');
        $this->priceRulesHelper()->setAllRulesToInactive();
        $this->priceRulesHelper()->createRule($priceRuleData);
        //Verification
        $this->assertMessagePresent('success', 'success_saved_rule');
        $this->_ruleToBeDeleted = $this->loadData('search_catalog_rule',
                                                  array('filter_rule_name' => $priceRuleData['info']['rule_name']));
        //Steps
        $this->clickButton('apply_rules', false);
        $this->waitForNewPage();
        $this->assertMessagePresent('success', 'success_applied_rule');
        $this->clearInvalidedCache();
        $this->reindexInvalidedData();
        //Verification on frontend
        $this->customerHelper()->frontLoginCustomer($testData['customer']);
        $this->categoryHelper()->frontOpenCategoryAndValidateProduct($priceInCategoryLogged);
        $this->productHelper()->frontOpenProduct($testData['simpleName'], $testData['categoryPath']);
        $this->categoryHelper()->frontVerifyProductPrices($productPriceLogged);
        $this->logoutCustomer();
        $this->productHelper()->frontOpenProduct($testData['simpleName'], $testData['categoryPath']);
        $this->categoryHelper()->frontVerifyProductPrices($productPriceNotLogged);
        $this->categoryHelper()->frontOpenCategoryAndValidateProduct($priceInCategoryNotLogged);
    }

    public function applyRuleToSimpleFrontDataProvider()
    {
        return array(
            array('by_percentage_of_the_original_price'),
            array('by_fixed_amount'),
            array('to_percentage_of_the_original_price'),
            array('to_fixed_amount')
        );
    }
}