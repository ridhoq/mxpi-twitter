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
 * Compare Products tests
 *
 * @package     selenium
 * @subpackage  tests
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class Core_Mage_CompareProducts_CompareProductsTest extends Mage_Selenium_TestCase
{
    protected $_useTearDown = false;

    public function setUpBeforeTests()
    {
        $this->loginAdminUser();
        $this->navigate('system_configuration');
        $this->systemConfigurationHelper()->configure('default_tax_config');
    }

    protected function assertPreConditions()
    {
        $this->addParameter('id', '0');
    }

    protected function tearDownAfterTest()
    {
        $this->compareProductsHelper()->frontCloseComparePopup();
        if ($this->_useTearDown) {
            $this->frontend();
            $this->compareProductsHelper()->frontClearAll();
            $this->_useTearDown = false;
        }
    }

    /**
     * @test
     * @return array
     */
    public function preconditionsForTests()
    {
        //Data
        $category = $this->loadData('sub_category_required');
        $path = $category['parent_category'] . '/' . $category['name'];
        $simple = $this->loadData('compare_simple_product', array('categories' => $path));
        $virtual = $this->loadData('compare_virtual_product', array('categories' => $path));
        //Steps
        $this->loginAdminUser();
        $this->navigate('manage_categories', false);
        $this->categoryHelper()->checkCategoriesPage();
        $this->categoryHelper()->createCategory($category);
        //Verifying
        $this->assertMessagePresent('success', 'success_saved_category');
        //Steps
        $this->navigate('manage_products');
        $this->productHelper()->createProduct($simple);
        //Verifying
        $this->assertMessagePresent('success', 'success_saved_product');
        //Steps
        $this->productHelper()->createProduct($virtual, 'virtual');
        //Verifying
        $this->assertMessagePresent('success', 'success_saved_product');
        $this->clearInvalidedCache();
        return array('catName' => $category['name'],
                     'names'   => array($simple['general_name'], $virtual['general_name']),
                     'verify'  => array(
                         'product_1/product_name' => $simple['general_name'],
                         'product_1/SKU'          => $simple['general_sku'],
                         'product_2/product_name' => $virtual['general_name'],
                         'product_2/SKU'          => $virtual['general_sku']));
    }

    /**
     * <p>Adds a product to Compare Products from Product Details page.</p>
     * <p>Steps:</p>
     * <p>1. Open product</p>
     * <p>2. Add product to Compare Products</p>
     * <p>Expected result:</p>
     * <p>Success message is displayed.Product is displayed in Compare Products pop-up window</p>
     *
     * @param array $data
     *
     * @test
     * @depends preconditionsForTests
     * @TestlinkId	TL-MAGE-3234
     */
    public function addProductToCompareListFromProductPage($data)
    {
        $verify = $this->loadData('verify_compare_data', $data['verify']);
        //Steps and Verifying
        foreach ($data['names'] as $value) {
            $this->compareProductsHelper()->frontAddToCompareFromProductPage($value);
            $this->assertMessagePresent('success', 'product_added_to_comparison');
            $this->assertTrue($this->controlIsPresent('link', 'compare_product_link'),
                              'Product is not available in Compare widget');
        }
        //Steps
        $this->compareProductsHelper()->frontOpenComparePopup();
        //Verifying
        $this->compareProductsHelper()->frontVerifyProductDataInComparePopup($verify);
        $this->_useTearDown = true;
    }

    /**
     * <p>Adds a products to Compare Products from Category page.</p>
     * <p>Steps:</p>
     * <p>1. Open product</p>
     * <p>2. Add product to Compare Products</p>
     * <p>Expected result:</p>
     * <p>Success message is displayed.Products displayed in Compare Products pop-up window</p>
     *
     * @param array $data
     *
     * @test
     * @depends preconditionsForTests
     * @TestlinkId	TL-MAGE-3233
     */
    public function addProductToCompareListFromCatalogPage($data)
    {
        //Steps and Verifying
        $this->frontend();
        foreach ($data['names'] as $value) {
            $this->compareProductsHelper()->frontAddToCompareFromCatalogPage(
                $value, $data['catName']);
            $this->assertMessagePresent('success', 'product_added_to_comparison');
            $this->assertTrue($this->controlIsPresent('link', 'compare_product_link'),
                              'Product is not available in Compare widget');
            $this->compareProductsHelper()->frontOpenComparePopup();
            $this->assertTrue($this->controlIsPresent('link', 'product_title'),
                              'There is no expected product in Compare Products popup');
            $this->compareProductsHelper()->frontCloseComparePopup();
        }
        $this->_useTearDown = true;
    }

    /**
     * <p>Remove a product from CompareProducts block</p>
     * <p>Steps:</p>
     * <p>1. Open product</p>
     * <p>2. Add two products to CompareProducts</p>
     * <p>3. Remove products from Compare Products</p>
     * <p>Expected result:</p>
     * <p>Success message is displayed</p>
     * <p>Products should not be displayed in the Compare Products pop-up</p>
     *
     * @param array $data
     *
     * @test
     * @depends preconditionsForTests
     * @TestlinkId	TL-MAGE-3236
     */
    public function removeProductFromCompareBlockList($data)
    {
        //Steps and Verifying
        $this->frontend();
        foreach ($data['names'] as $value) {
            $this->compareProductsHelper()->frontAddToCompareFromCatalogPage(
                $value, $data['catName']);
            $this->assertMessagePresent('success', 'product_added_to_comparison');
            $this->compareProductsHelper()->frontRemoveProductFromCompareBlock($value);
            $this->assertMessagePresent('success', 'product_removed_from_comparison');
            $this->assertFalse($this->controlIsPresent('link', 'compare_product_link'),
                               'There is unexpected product in Compare Products widget');
        }
    }

    /**
     * <p>Compare Products block is not displayed without products</p>
     * <p>Steps:</p>
     * <p>1. Open product</p>
     * <p>2. Add product to Compare Products</p>
     * <p>3. Remove product from Compare Products</p>
     * <p>Expected result:</p>
     * <p>Compare Products block should be empty</p>
     *
     * @param array $data
     *
     * @test
     * @depends preconditionsForTests.
     * @TestlinkId	TL-MAGE-3235
     */
    public function emptyCompareListIsNotAvailable($data)
    {
        //Steps
        $this->frontend();
        $this->compareProductsHelper()->frontAddToCompareFromCatalogPage($data['names'][0], $data['catName']);
        //Verifying
        $this->assertMessagePresent('success', 'product_added_to_comparison');
        $this->assertTrue($this->controlIsPresent('link', 'compare_product_link'),
                          'Product is not available in Compare widget');
        //Steps
        $this->compareProductsHelper()->frontClearAll();
        $this->assertMessagePresent('success', 'compare_list_cleared');
        //Verifying
        $this->assertTrue($this->controlIsPresent('pageelement', 'compare_block_empty'),
                          'There is unexpected product(s) in Compare Products widget');
    }
}