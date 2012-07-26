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
 * Wishlist tests
 *
 * @package     selenium
 * @subpackage  tests
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class Core_Mage_Wishlist_Wishlist extends Mage_Selenium_TestCase
{
    protected static $useTearDown = false;

    /**
     * <p>Login as a registered user</p>
     */
    public function setUpBeforeTests()
    {
        $this->logoutCustomer();
    }

    protected function assertPreConditions()
    {
        $this->loginAdminUser();
    }

    protected function tearDownAfterTest()
    {
        if (self::$useTearDown) {
            $this->loginAdminUser();
            $this->navigate('system_configuration');
            $this->systemConfigurationHelper()->configure('not_display_out_of_stock_products');
        }
    }

    /**
     * <p>Create a new customer for tests</p>
     * @return array Customer 'email' and 'password'
     * @test
     * @group preConditions
     */
    public function preconditionsCreateCustomer()
    {
        $userData = $this->loadData('generic_customer_account');
        $this->navigate('manage_customers');
        $this->customerHelper()->createCustomer($userData);
        $this->assertMessagePresent('success', 'success_saved_customer');
        return array('email'    => $userData['email'],
                     'password' => $userData['password']);
    }

    /**
     * <p>Creates Category to use during tests</p>
     * @return array Category 'name' and 'path'
     * @test
     */
    public function preconditionsCreateCategory()
    {
        //Data
        $category = $this->loadData('sub_category_required');
        //Steps and Verification
        $this->navigate('manage_categories', false);
        $this->categoryHelper()->checkCategoriesPage();
        $this->categoryHelper()->createCategory($category);
        $this->assertMessagePresent('success', 'success_saved_category');

        return array('name' => $category['name'],
                     'path' => $category['parent_category'] . '/' . $category['name']);
    }

    /**
     * <p>Creating configurable product</p>
     * @return array
     *
     * @test
     * @group preConditions
     */
    public function preconditionsCreateConfigurableAttribute()
    {
        //Data
        $attrData = $this->loadData('product_attribute_dropdown_with_options');
        $associatedAttributes = $this->loadData('associated_attributes',
                                                array('General' => $attrData['attribute_code']));
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
        return $attrData;
    }

    /**
     * <p>Create a new product of the specified type</p>
     *
     * @param array $productData Product data to fill in backend
     * @param null|string $productType E.g. 'simple'|'configurable' etc.
     *
     * @return array $productData
     */
    protected function _createProduct(array $productData, $productType)
    {
        $this->navigate('manage_products');
        $productData = $this->arrayEmptyClear($productData);
        $this->productHelper()->createProduct($productData, $productType);
        $this->assertMessagePresent('success', 'success_saved_product');
        return $productData;
    }

    /**
     * <p>Create a simple product within a category</p>
     *
     * @param array $categoryData
     *
     * @test
     * @depends preconditionsCreateCategory
     */
    public function preconditionsCreateProductSimple($categoryData)
    {
        $productData = $this->loadData('simple_product_visible', array('categories' => $categoryData['path']));
        $productSimple = $this->_createProduct($productData, 'simple');
        return $productSimple['general_name'];
    }

    /**
     * <p>Create products of all types for the tests without custom options</p>
     * SpecialOptions is mean: associated_configurable_data, associated_grouped_data etc
     *
     * @param array $attrData
     *
     * @return array Array of product names
     * @test
     * @depends preconditionsCreateConfigurableAttribute
     * @group preConditions
     */
    public function preconditionsCreateAllProductsWithoutSpecialOptions($attrData)
    {
        // Create simple product, so that it can be used in Configurable product.
        $simpleData = $this->loadData('simple_product_visible');
        $productSimple = $this->_createProduct($simpleData, 'simple');
        // Create a configurable product
        $productData = $this->loadData('configurable_product_visible',
                                       array('associated_configurable_data'  => '%noValue%',
                                             'configurable_attribute_title'  => $attrData['admin_title']),
                                       array('general_sku', 'general_name'));
        $productConfigurable = $this->_createProduct($productData, 'configurable');
        //Create a virtual product
        $productData = $this->loadData('virtual_product_visible', null, array('general_name', 'general_sku'));
        $productVirtual = $this->_createProduct($productData, 'virtual');
        //Create a downloadable product
        $productData = $this->loadData('downloadable_product_visible',
                                       array('downloadable_information_data' => '%noValue%'),
                                       array('general_name', 'general_sku'));
        $productDownloadable = $this->_createProduct($productData, 'downloadable');
        //Create a grouped product
        $productData = $this->loadData('grouped_product_visible', array('associated_grouped_data' => '%noValue%'),
                                       array('general_name', 'general_sku'));
        $productGrouped = $this->_createProduct($productData, 'grouped');
        //Create a bundle product
        $productData = $this->loadData('fixed_bundle_visible', array('bundle_items_data' => '%noValue%'),
                                       array('general_name', 'general_sku'));
        $productBundle = $this->_createProduct($productData, 'bundle');

        $allProducts = array('simple'       => $productSimple,
                             'virtual'      => $productVirtual,
                             'downloadable' => $productDownloadable,
                             'grouped'      => $productGrouped,
                             'configurable' => $productConfigurable,
                             'bundle'       => $productBundle);
        return $allProducts;
    }

    /**
     * <p>Create products of all types for the tests with custom options</p>
     * SpecialOptions is mean: associated_configurable_data, associated_grouped_data etc
     *
     * @param array $attrData
     *
     * @return array Array of product names
     * @test
     * @depends preconditionsCreateConfigurableAttribute
     */
    public function preconditionsCreateAllProductsWithSpecialOptions($attrData)
    {
        // Create simple product, so that it can be used in Configurable product.
        $simpleData = $this->loadData('simple_product_visible', null, array('general_name', 'general_sku'));
        $simpleData['general_user_attr']['dropdown'][$attrData['attribute_code']] = $attrData['option_1']['admin_option_name'];
        $productSimple = $this->_createProduct($simpleData, 'simple');
        // Create a configurable product
        $productData = $this->loadData('configurable_product_visible',
                                       array('configurable_attribute_title' => $attrData['admin_title']),
                                       array('general_sku', 'general_name'));
        $productData['associated_configurable_data'] = $this->loadData('associated_configurable_data',
            array('associated_search_sku' => $simpleData['general_sku']));
        $productData['special_options'] = $this->loadData('configurable_options_to_add_to_shopping_cart',
            array('custom_option_dropdown' => $attrData['option_1']['store_view_titles']['Default Store View'],
                  'title'                  => $attrData['admin_title']));
        $productConfigurable = $this->_createProduct($productData, 'configurable');
        //Create a virtual product
        $productData = $this->loadData('virtual_product_visible', null, array('general_name', 'general_sku'));
        $productVirtual = $this->_createProduct($productData, 'virtual');
        //Create a downloadable product
        $productData = $this->loadData('downloadable_product_visible', null, array('general_name', 'general_sku'));
        $productData['special_options'] = $this->loadData('downloadable_options_to_add_to_shopping_cart');
        $productDownloadable = $this->_createProduct($productData, 'downloadable');
        //Create a grouped product
        $productData = $this->loadData('grouped_product_visible',
                                       array('associated_search_name'         => $simpleData['general_name'],
                                             'associated_product_default_qty' => '3'),
                                       array('general_name', 'general_sku'));
        $productGrouped = $this->_createProduct($productData, 'grouped');
        //Create a bundle product
        $productData = $this->loadData('fixed_bundle_visible', null, array('general_name', 'general_sku'));
        $productData['bundle_items_data']['item_1'] = $this->loadData('bundle_item_1',
            array('add_product_1/bundle_items_search_sku' => $simpleData['general_sku'],
                  'add_product_2/bundle_items_search_sku' => $productVirtual['general_sku']));
        $productData['special_options'] = $this->loadData('bundle_options_to_add_to_shopping_cart',
            array('custom_option_multiselect' => $productVirtual['general_name'],
                  'option_2'                  => '%noValue%',
                  'option_3'                  => '%noValue%',
                  'option_4'                  => '%noValue%',
                  'option_5'                  => '%noValue%'));
        $productBundle = $this->_createProduct($productData, 'bundle');

        return array('simple'       => $productSimple,
                     'virtual'      => $productVirtual,
                     'downloadable' => $productDownloadable,
                     'grouped'      => $productGrouped,
                     'configurable' => $productConfigurable,
                     'bundle'       => $productBundle);
    }

    /**
     * @param array $productDataSet Array of product data
     *
     * @return array Array of product names
     */
    private function _getProductNames($productDataSet)
    {
        $productNamesSet = array();
        foreach ($productDataSet as $productData) {
            $productNamesSet[] = $productData['general_name'];
        }
        return $productNamesSet;
    }

    /**
     * <p>Adds a product to Wishlist from Product Details page. For all products with custom options</p>
     * <p>Steps:</p>
     * <p>1. Open product</p>
     * <p>2. Add product to wishlist</p>
     * <p>Expected result:</p>
     * <p>Success message is displayed</p>
     *
     * @param array $customer
     * @param array $productDataSet
     *
     * @test
     * @depends preconditionsCreateCustomer
     * @depends preconditionsCreateAllProductsWithSpecialOptions
     * @TestlinkId	TL-MAGE-3517
     */
    public function addProductsWithSpecialOptionsToWishlistFromProductPage($customer, $productDataSet)
    {
        //Setup
        $productNameSet = $this->_getProductNames($productDataSet);
        $this->customerHelper()->frontLoginCustomer($customer);
        $this->navigate('my_wishlist');
        $this->wishlistHelper()->frontClearWishlist();
        //Steps
        foreach ($productNameSet as $productName) {
            $this->wishlistHelper()->frontAddProductToWishlistFromProductPage($productName);
            $this->assertMessagePresent('success', 'successfully_added_product');
        }
        //Verify
        $this->navigate('my_wishlist');
        foreach ($productNameSet as $productName) {
            $this->assertTrue($this->wishlistHelper()->frontWishlistHasProducts($productName),
                              'Product ' . $productName . ' is not in the wishlist.');
        }
    }

    /**
     * <p>Removes all products from My Wishlist. For all product types</p>
     * <p>Steps:</p>
     * <p>1. Add products to the wishlist</p>
     * <p>2. Remove one product from the wishlist</p>
     * <p>Expected result:</p>
     * <p>The product is no longer in wishlist</p>
     * <p>3. Repeat for all products until the last one</p>
     * <p>4. Remove the last product from the wishlist</p>
     * <p>Expected result:</p>
     * <p>Message 'You have no items in your wishlist.' is displayed</p>
     *
     * @param $customer
     * @param $productDataSet
     *
     * @test
     * @depends preconditionsCreateCustomer
     * @depends preconditionsCreateAllProductsWithSpecialOptions
     * @TestlinkId	TL-MAGE-3523
     */
    public function removeProductsFromWishlist($customer, $productDataSet)
    {
        //Setup
        $productNameSet = $this->_getProductNames($productDataSet);
        $this->customerHelper()->frontLoginCustomer($customer);
        $this->navigate('my_wishlist');
        $this->wishlistHelper()->frontClearWishlist();
        foreach ($productNameSet as $productName) {
            $this->wishlistHelper()->frontAddProductToWishlistFromProductPage($productName);
            $this->assertMessagePresent('success', 'successfully_added_product');
        }
        //Steps
        $lastProductName = end($productNameSet);
        array_pop($productNameSet);
        foreach ($productNameSet as $productName) {
            $this->wishlistHelper()->frontRemoveProductsFromWishlist($productName); // Remove all but last
            //Verify
            $this->assertTrue(is_array($this->wishlistHelper()->frontWishlistHasProducts($productName)),
                              'Product ' . $productName . ' is in the wishlist, but should be removed.');
        }
        //Steps
        $this->wishlistHelper()->frontRemoveProductsFromWishlist($lastProductName); //Remove the last one
        //Verify
        $this->assertTrue($this->controlIsPresent('pageelement', 'no_items'), $this->getParsedMessages());
    }

    /**
     * <p>Adds a product to Wishlist from Product Details page. For all types without custom options.</p>
     * <p>Steps:</p>
     * <p>1. Open product</p>
     * <p>2. Add product to wishlist</p>
     * <p>Expected result:</p>
     * <p>Success message is displayed</p>
     *
     * @param array $customer
     * @param array $productDataSet
     *
     * @test
     * @depends preconditionsCreateCustomer
     * @depends preconditionsCreateAllProductsWithoutSpecialOptions
     * @TestlinkId	TL-MAGE-3517
     * @group skip_due_to_bug
     */
    public function addProductsWithoutSpecialOptionsToWishlistFromProductPage($customer, $productDataSet)
    {
        //Setup
        $productNameSet = $this->_getProductNames($productDataSet);
        self::$useTearDown = true;
        $this->navigate('system_configuration');
        $this->systemConfigurationHelper()->configure('display_out_of_stock_products');
        $this->reindexInvalidedData();
        $this->clearInvalidedCache();
        $this->customerHelper()->frontLoginCustomer($customer);
        $this->navigate('my_wishlist');
        $this->wishlistHelper()->frontClearWishlist();
        //Steps
        foreach ($productNameSet as $productName) {
            $this->wishlistHelper()->frontAddProductToWishlistFromProductPage($productName);
            $this->assertMessagePresent('success', 'successfully_added_product');
            $this->assertTrue($this->wishlistHelper()->frontWishlistHasProducts($productName),
                              'Product ' . $productName . ' is not added to wishlist.');
        }
        //Verify
        $this->navigate('my_wishlist');
        foreach ($productNameSet as $productName) {
            $this->assertTrue($this->wishlistHelper()->frontWishlistHasProducts($productName),
                              'Product ' . $productName . ' is not in the wishlist.');
        }
    }

    /**
     * <p>Adds a simple product to Wishlist from Catalog page.</p>
     * <p>Steps:</p>
     * <p>1. Open category</p>
     * <p>2. Find product</p>
     * <p>3. Add product to wishlist</p>
     * <p>Expected result:</p>
     * <p>Success message is displayed</p>
     *
     * @param array $customer
     * @param array $categoryData
     * @param string $simpleProductName
     *
     * @test
     * @depends preconditionsCreateCustomer
     * @depends preconditionsCreateCategory
     * @depends preconditionsCreateProductSimple
     * @TestlinkId	TL-MAGE-3518
     */
    public function addProductToWishlistFromCatalog($customer, $categoryData, $simpleProductName)
    {
        //Setup
        $this->customerHelper()->frontLoginCustomer($customer);
        $this->navigate('my_wishlist');
        $this->wishlistHelper()->frontClearWishlist();
        //Steps
        $this->wishlistHelper()->frontAddProductToWishlistFromCatalogPage($simpleProductName, $categoryData['name']);
        //Verify
        $this->navigate('my_wishlist');
        $this->assertTrue($this->wishlistHelper()->frontWishlistHasProducts($simpleProductName),
                          'Product ' . $simpleProductName . ' is not in the wishlist.');
    }

    /**
     * <p>Adds a simple product to Wishlist from Shopping Cart.</p>
     * <p>Steps:</p>
     * <p>1. Add the product to the shopping cart</p>
     * <p>2. Move the product to wishlist</p>
     * <p>3. Open the wishlist</p>
     * <p>Expected result:</p>
     * <p>The product is in the wishlist</p>
     *
     * @param array $customer
     * @param string $simpleProductName
     *
     * @test
     * @depends preconditionsCreateCustomer
     * @depends preconditionsCreateProductSimple
     * @TestlinkId	TL-MAGE-3519
     */
    public function addProductToWishlistFromShoppingCart($customer, $simpleProductName)
    {
        //Setup
        $this->customerHelper()->frontLoginCustomer($customer);
        $this->navigate('my_wishlist');
        $this->wishlistHelper()->frontClearWishlist();
        //Steps
        $this->productHelper()->frontOpenProduct($simpleProductName);
        $this->productHelper()->frontAddProductToCart();
        $this->shoppingCartHelper()->frontMoveToWishlist($simpleProductName);
        //Verify
        $this->navigate('my_wishlist');
        $this->assertTrue($this->wishlistHelper()->frontWishlistHasProducts($simpleProductName),
                          'Product ' . $simpleProductName . ' is not in the wishlist.');
    }

    /**
     * <p>Adds products to Shopping Cart from Wishlist. For all product types without custom options</p>
     * <p>Steps:</p>
     * <p>1. Empty the shopping cart</p>
     * <p>2. Add a product to the wishlist</p>
     * <p>3. Open the wishlist</p>
     * <p>4. Click 'Add to Cart' button for each product</p>
     * <p>Expected result:</p>
     * <p>The products are in the shopping cart</p>
     *
     * @param array $customer
     * @param array $productDataSet
     *
     * @test
     * @depends preconditionsCreateCustomer
     * @depends preconditionsCreateAllProductsWithoutSpecialOptions
     * @depends addProductsWithoutSpecialOptionsToWishlistFromProductPage
     * @TestlinkId	TL-MAGE-3516
     */
    public function addProductsWithoutSpecialOptionsToShoppingCartFromWishlist($customer, $productDataSet)
    {
        //Setup
        $productNameSet = $this->_getProductNames($productDataSet);
        $this->customerHelper()->frontLoginCustomer($customer);
        $this->navigate('shopping_cart');
        $this->shoppingCartHelper()->frontClearShoppingCart();
        foreach ($productNameSet as $productName) {
            $this->wishlistHelper()->frontAddProductToWishlistFromProductPage($productName);
            $this->assertMessagePresent('success', 'successfully_added_product');
        }
        //Steps
        $this->navigate('my_wishlist');
        foreach ($productNameSet as $productName) {
            $this->wishlistHelper()->frontAddToShoppingCart($productName);
        }
        $this->wishlistHelper()->frontAddToShoppingCart($productNameSet);
        //Verify
        $this->assertTrue($this->checkCurrentPage('shopping_cart'), $this->getParsedMessages());
        foreach ($productNameSet as $productName) {
            $this->assertTrue($this->shoppingCartHelper()->frontShoppingCartHasProducts($productName),
                              'Product ' . $productName . ' is not in the shopping cart.');
        }
    }

    /**
     * <p>Adds products to Shopping Cart from Wishlist. For all product types with custom options</p>
     * <p>Steps:</p>
     * <p>1. Empty the shopping cart</p>
     * <p>2. Add a product to the wishlist, fill its custom options</p>
     * <p>3. Open the wishlist</p>
     * <p>4. Click 'Add to Cart' button for each product</p>
     * <p>Expected result:</p>
     * <p>The products are in the shopping cart</p>
     *
     * @param array $customer
     * @param array $productDataSet
     *
     * @test
     * @depends preconditionsCreateCustomer
     * @depends preconditionsCreateAllProductsWithSpecialOptions
     * @depends addProductsWithSpecialOptionsToWishlistFromProductPage
     * @TestlinkId	TL-MAGE-3514
     */
    public function addProductsWithSpecialOptionsToShoppingCartFromWishlist($customer, $productDataSet)
    {
        //Setup
        unset($productDataSet['grouped']); //will test in addGroupedProductToShoppingCartFromWishlist()
        $productNameSet = $this->_getProductNames($productDataSet);
        $this->customerHelper()->frontLoginCustomer($customer);
        $this->navigate('my_wishlist');
        $this->wishlistHelper()->frontClearWishlist();
        $this->navigate('shopping_cart');
        $this->shoppingCartHelper()->frontClearShoppingCart();
        foreach ($productNameSet as $productName) {
            $this->wishlistHelper()->frontAddProductToWishlistFromProductPage($productName);
            $this->assertMessagePresent('success', 'successfully_added_product');
        }
        //Steps
        $this->navigate('my_wishlist');
        foreach ($productDataSet as $product) {
            $productOptions = (isset($product['special_options'])) ? $product['special_options'] : array();
            $this->wishlistHelper()->frontAddToShoppingCart($product['general_name'], $productOptions);
        }
        //Verify
        $this->assertTrue($this->checkCurrentPage('shopping_cart'), $this->getParsedMessages());
        foreach ($productNameSet as $productName) {
            $this->assertTrue($this->shoppingCartHelper()->frontShoppingCartHasProducts($productName),
                              'Product ' . $productName . ' is not in the shopping cart.');
        }
    }

    /**
     * <p>Adds products to Shopping Cart from Wishlist. For all product types with custom options</p>
     * <p>Steps:</p>
     * <p>1. Empty the shopping cart</p>
     * <p>2. Add products to the wishlist</p>
     * <p>3. Open the wishlist</p>
     * <p>4. Click 'Add All to Cart' button</p>
     * <p>Expected result:</p>
     * <p>Error messages for configurable and downloadable products are displayed.</p>
     * <p>Success message for other products is displayed.</p>
     * <p>All products except grouped, configurable and downloadable are in the shopping cart</p>
     *
     * @param array $customer
     * @param array $productDataSet
     *
     * @test
     * @depends preconditionsCreateCustomer
     * @depends preconditionsCreateAllProductsWithSpecialOptions
     * @depends addProductsWithSpecialOptionsToWishlistFromProductPage
     * @TestlinkId	TL-MAGE-3513
     */
    public function addAllProductsToShoppingCartFromWishlist($customer, $productDataSet)
    {
        //Setup
        $productNameSet = $this->_getProductNames($productDataSet);
        $downloadableProductName = $productDataSet['downloadable']['general_name'];
        $configurableProductName = $productDataSet['configurable']['general_name'];
        $groupedProductName = $productDataSet['grouped']['general_name'];
        $bundleProductName = $productDataSet['bundle']['general_name'];
        $this->customerHelper()->frontLoginCustomer($customer);
        $this->navigate('my_wishlist');
        $this->wishlistHelper()->frontClearWishlist();
        $this->navigate('shopping_cart');
        $this->shoppingCartHelper()->frontClearShoppingCart();
        foreach ($productNameSet as $productName) {
            $this->wishlistHelper()->frontAddProductToWishlistFromProductPage($productName);
            $this->assertMessagePresent('success', 'successfully_added_product');
        }
        //Steps
        $this->navigate('my_wishlist');
        $this->clickButton('add_all_to_cart');
        //Verify
        //Check error message for downloadable product
        $this->addParameter('productName', $downloadableProductName);
        $this->assertMessagePresent('error', 'specify_product_links');
        //Check error message for configurable product
        $this->addParameter('productName', $configurableProductName);
        $this->assertMessagePresent('error', 'specify_product_options');
        //Check error message for bundle product
        $this->addParameter('productName', $bundleProductName);
        $this->assertMessagePresent('error', 'specify_product_options');
        //Check success message for other products
        $this->addParameter('productQty', '3');
        $this->assertMessagePresent('success', 'successfully_added_products');
        //Check if the products are in the shopping cart
        $this->navigate('shopping_cart');
        foreach ($productNameSet as $productName) {
            if ($productName == $downloadableProductName || $productName == $configurableProductName
                    || $productName == $groupedProductName || $productName == $bundleProductName) {
                $this->assertTrue(is_array($this->shoppingCartHelper()->frontShoppingCartHasProducts($productName)),
                                  'Product ' . $productName . ' is in the shopping cart, but should not be.');
            } else {
                $this->assertTrue($this->shoppingCartHelper()->frontShoppingCartHasProducts($productName),
                                  'Product ' . $productName . ' is not in the shopping cart.');
            }
        }
    }

    /**
     * Grouped product is added as several simple products to the shopping cart
     * <p>Steps:</p>
     * <p>1. Empty the shopping cart</p>
     * <p>2. Add a Grouped product to the wishlist, fill its custom options</p>
     * <p>3. Open the wishlist</p>
     * <p>4. Click 'Add to Cart' button for product</p>
     * <p>Expected result:</p>
     * <p>Grouped product is added as several simple products</p>
     *
     * @param array $customer
     * @param array $productDataSet
     *
     * @test
     * @depends preconditionsCreateCustomer
     * @depends preconditionsCreateAllProductsWithSpecialOptions
     * @TestlinkId	TL-MAGE-5346
     */
    public function addGroupedProductToShoppingCartFromWishlist($customer, $productDataSet)
    {
        //Data
        $groupedName = $productDataSet['grouped']['general_name'];
        $simpleName = $productDataSet['simple']['general_name'];
        //Setup
        $this->customerHelper()->frontLoginCustomer($customer);
        $this->navigate('my_wishlist');
        $this->wishlistHelper()->frontClearWishlist();
        $this->navigate('shopping_cart');
        $this->shoppingCartHelper()->frontClearShoppingCart();
        $this->wishlistHelper()->frontAddProductToWishlistFromProductPage($groupedName);
        $this->assertMessagePresent('success', 'successfully_added_product');
        //Steps
        $this->navigate('my_wishlist');
        $this->wishlistHelper()->frontAddToShoppingCart($groupedName);
        //Verify
        $this->assertTrue($this->checkCurrentPage('shopping_cart'), $this->getParsedMessages());
        $this->assertTrue($this->shoppingCartHelper()->frontShoppingCartHasProducts($simpleName),
                          'Product ' . $groupedName . ' is not in the shopping cart.');
        $productQty = $this->getElementByXpath($this->_getControlXpath('field', 'product_qty_by_name'), 'value');
        $this->assertEquals(3, $productQty, "Product quantity is unexpected");
    }

    /**
     * <p>Opens My Wishlist using the link in quick access bar</p>
     * <p>Steps:</p>
     * <p>1. Open home page</p>
     * <p>2. Click "My Wishlist" link</p>
     * <p>Expected result:</p>
     * <p>The wishlist is opened.</p>
     *
     * @param array $customer
     *
     * @test
     * @depends preconditionsCreateCustomer
     * @TestlinkId	TL-MAGE-3522
     */
    public function openMyWishlistViaQuickAccessLink($customer)
    {
        //Setup
        $this->customerHelper()->frontLoginCustomer($customer);
        $this->navigate('home');
        //Steps
        $this->clickControl('link', 'my_wishlist');
        //Verify
        $this->assertTrue($this->checkCurrentPage('my_wishlist'), $this->getParsedMessages());
    }

    /**
     * <p>Shares My Wishlist</p>
     * <p>Steps:</p>
     * <p>1. Add a product to the wishlist</p>
     * <p>2. Open My Wishlist</p>
     * <p>3. Click "Share Wishlist" button</p>
     * <p>4. Enter a valid email and a message</p>
     * <p>5. Click "Share Wishlist" button
     * <p>Expected result:</p>
     * <p>The success message is displayed</p>
     *
     * @param array $shareData
     * @param array $customer
     * @param string $simpleProductName
     *
     * @test
     * @dataProvider shareWishlistDataProvider
     * @depends preconditionsCreateCustomer
     * @depends preconditionsCreateProductSimple
     * @TestlinkId	TL-MAGE-3524
     */
    public function shareWishlist($shareData, $customer, $simpleProductName)
    {
        //Setup
        $shareData = $this->loadData('share_data', $shareData);
        $this->customerHelper()->frontLoginCustomer($customer);
        $this->wishlistHelper()->frontAddProductToWishlistFromProductPage($simpleProductName);
        $this->assertMessagePresent('success', 'successfully_added_product');
        //Steps
        $this->wishlistHelper()->frontShareWishlist($shareData);
        //Verify
        $this->assertMessagePresent('success', 'successfully_shared_wishlist');
    }

    public function shareWishlistDataProvider()
    {
        return array(
            array(array('emails'  => 'autotest@test.com',
                        'message' => 'autotest message')),
            array(array('message' => ''))
        );
    }

    /**
     * <p>Shares My Wishlist with invalid email(s) provided</p>
     * <p>Steps:</p>
     * <p>1. Add a product to the wishlist</p>
     * <p>2. Open My Wishlist</p>
     * <p>3. Click "Share Wishlist" button</p>
     * <p>4. Enter an invalid email and a message</p>
     * <p>5. Click "Share Wishlist" button
     * <p>Expected result:</p>
     * <p>An error message is displayed</p>
     *
     * @param string $emails
     * @param string $errorMessage
     * @param array $customer
     * @param string $simpleProductName
     *
     * @test
     * @dataProvider withInvalidEmailDataProvider
     * @depends preconditionsCreateCustomer
     * @depends preconditionsCreateProductSimple
     * @TestlinkId	TL-MAGE-3526
     */
    public function withInvalidEmail($emails, $errorMessage, $customer, $simpleProductName)
    {
        //Setup
        $shareData = $this->loadData('share_data', array('emails' => $emails));
        $this->customerHelper()->frontLoginCustomer($customer);
        $this->wishlistHelper()->frontAddProductToWishlistFromProductPage($simpleProductName);
        $this->assertMessagePresent('success', 'successfully_added_product');
        $this->navigate('my_wishlist');
        $this->wishlistHelper()->frontShareWishlist($shareData);
        //Verify
        if ($errorMessage == 'invalid_emails') {
            $this->assertMessagePresent('validation', $errorMessage);
        } else {
            $this->assertMessagePresent('error', $errorMessage);
        }
    }

    public function withInvalidEmailDataProvider()
    {
        return array(
            array('email@@domain.com', 'invalid_emails_js'),
            array('.email@domain.com', 'invalid_emails'));
    }

    /**
     * <p>Shares My Wishlist with empty email provided</p>
     * <p>Steps:</p>
     * <p>1. Add a product to the wishlist</p>
     * <p>2. Open My Wishlist</p>
     * <p>3. Click "Share Wishlist" button</p>
     * <p>4. Enter an invalid email and a message</p>
     * <p>5. Click "Share Wishlist" button
     * <p>Expected result:</p>
     * <p>An error message is displayed</p>
     *
     * @param array $customer
     * @param string $simpleProductName
     *
     * @test
     * @depends preconditionsCreateCustomer
     * @depends preconditionsCreateProductSimple
     * @TestlinkId	TL-MAGE-3525
     */
    public function shareWishlistWithEmptyEmail($customer, $simpleProductName)
    {
        //Setup
        $shareData = $this->loadData('share_data', array('emails' => ''));
        $this->customerHelper()->frontLoginCustomer($customer);
        $this->wishlistHelper()->frontAddProductToWishlistFromProductPage($simpleProductName);
        $this->assertMessagePresent('success', 'successfully_added_product');
        //Steps
        $this->navigate('my_wishlist');
        $this->wishlistHelper()->frontShareWishlist($shareData);
        //Verify
        $this->assertMessagePresent('validation', 'required_emails');
    }

    /**
     * <p>Verifies that a guest cannot open My Wishlist.</p>
     * <p>Steps:</p>
     * <p>1. Logout customer</p>
     * <p>2. Navigate to My Wishlist</p>
     * <p>Expected result:</p>
     * <p>Guest is redirected to login/register page.</p>
     *
     * @test
     * @TestlinkId	TL-MAGE-3521
     */
    public function guestCannotOpenWishlist()
    {
        //Setup
        $this->logoutCustomer();
        //Steps
        $this->clickControl('link', 'my_wishlist');
        //Verify
        $this->assertTrue($this->checkCurrentPage('customer_login'), $this->getParsedMessages());
    }

    /**
     * <p>Verifies that a guest cannot add a product to a wishlist.</p>
     * <p>Steps:</p>
     * <p>1. Logout customer</p>
     * <p>2. Open a product</p>
     * <p>3. Add products to the wishlist</p>
     * <p>Expected result:</p>
     * <p>Guest is redirected to login/register page.</p>
     *
     * @depends preconditionsCreateProductSimple
     *
     * @param string $simpleProductName
     *
     * @test
     * @TestlinkId	TL-MAGE-3520
     */
    public function guestCannotAddProductToWishlist($simpleProductName)
    {
        //Setup
        $this->logoutCustomer();
        //Steps
        $this->wishlistHelper()->frontAddProductToWishlistFromProductPage($simpleProductName);
        //Verify
        $this->assertTrue($this->checkCurrentPage('customer_login'), $this->getParsedMessages());
    }
}