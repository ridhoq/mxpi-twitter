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
 * Adding product of type bundle fixed with percent options enabled for sub-products to shopping cart
 * Test added due to bug MAGE-5495 verification
 *
 * @package     selenium
 * @subpackage  tests
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class Core_Mage_Various_AddToShoppingCartTest extends Mage_Selenium_TestCase
{
    /**
     * <p>Login to backend</p>
     */
    public function setUpBeforeTests()
    {
        $this->loginAdminUser();
    }

    /**
     * <p>Preconditions:</p>
     * <p>Navigate to Catalog - Manage Products</p>
     */
    protected function assertPreConditions()
    {
        $this->navigate('manage_products');
    }

    /**
     * <p>Adding Bundle product with Simple product to cart (Price Type = Percent)</p>
     * <p>Verification of MAGE-5495</p>
     * <p>Steps:</p>
     * <p>1. Go to Backend.</p>
     * <p>2. Create bundle fixed product. Add sub-product with Price Type = Percent.</p>
     * <p>3. Add configured bundle fixed product to the cart.</p>
     * <p>Expected Result:</p>
     * <p>Product added to the cart.</p>
     *
     * @test
     */
    public function bundleWithSimpleProductPercentPrice()
    {
        //Data
        $simpleData = $this->loadData('simple_product_visible');
        $bundleData = $this->loadData('fixed_bundle_visible', null, array('general_name', 'general_sku'));
        $bundleData['bundle_items_data']['item_1'] = $this->loadData('bundle_item_2', array(
            'bundle_items_search_sku'     => $simpleData['general_sku'],
            'selection_item_price'        => '10',
            'selection_item_price_type'   => 'Percent',)
        );
        $productSearch = $this->loadData('product_search', array('product_sku' => $bundleData['general_sku']));
        //Steps
        $this->productHelper()->createProduct($simpleData);
        //Verifying
        $this->assertMessagePresent('success', 'success_saved_product');
        //Steps
        $this->productHelper()->createProduct($bundleData, 'bundle');
        //Verifying
        $this->assertMessagePresent('success', 'success_saved_product');
        //Steps
        $this->productHelper()->openProduct($productSearch);
        //Verifying
        $this->productHelper()->verifyProductInfo($bundleData);
        //Steps
        $this->logoutCustomer();
        $this->productHelper()->frontOpenProduct($bundleData['general_name']);
        try {
            $this->productHelper()->frontAddProductToCart();
        } catch (PHPUnit_Framework_AssertionFailedError $e) {
            $this->fail($e->toString());
        }
        //Verifying
        $this->validatePage();
        $this->assertFalse($this->isTextPresent('Internal server error', 'HTTP Error 500 Internal server error'));
    }
}