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
 * Test for related, up-sell and cross-sell products.
 *
 * @package     selenium
 * @subpackage  tests
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class Core_Mage_Product_Linking_GroupedLinkingTest extends Mage_Selenium_TestCase
{
    protected static $productsInStock = array();
    protected static $productsOutOfStock = array();
    protected function assertPreConditions()
    {}

    /**
     * <p>Preconditions</p>
     * <p>Create attribute</p>
     *
     * @test
     */
    public function createAttribute()
    {
        $attrData = $this->loadData('product_attribute_dropdown_with_options', null,
                array('admin_title', 'attribute_code'));
        $associatedAttributes = $this->loadData('associated_attributes',
                array('General' => $attrData['attribute_code']));
        $this->loginAdminUser();
        $this->navigate('manage_attributes');
        $this->productAttributeHelper()->createAttribute($attrData);
        $this->assertMessagePresent('success', 'success_saved_attribute');
        $this->navigate('manage_attribute_sets');
        $this->attributeSetHelper()->openAttributeSet();
        $this->attributeSetHelper()->addAttributeToSet($associatedAttributes);
        $this->addParameter('attributeName', 'Default');
        $this->saveForm('save_attribute_set');
        $this->assertMessagePresent('success', 'success_attribute_set_saved');

        return $attrData;
    }

    /**
     * <p>Preconditions</p>
     * <p>Create simple product for adding it to bundle and associated product</p>
     *
     * @depends createAttribute
     * @test
     * @return string
     */
    public function createSimpleProductForBundle($attrData)
    {
        $productData = $this->loadData('simple_product_visible', null, array('general_name','general_sku'));
        $productData['general_user_attr']['dropdown'][$attrData['attribute_code']] =
                $attrData['option_1']['admin_option_name'];
        $this->loginAdminUser();
        $this->navigate('manage_products');
        $this->productHelper()->createProduct($productData);
        $this->assertMessagePresent('success', 'success_saved_product');

        return $productData['general_sku'];
    }
    /**
     * <p>Preconditions</p>
     * <p>Create products for linking in stock</p>
     *
     * @dataProvider productTypesDataProvider
     * @depends createAttribute
     * @depends createSimpleProductForBundle
     *
     * @test
     */
    public function createProductsForLinkingInStock($productType, $attrData, $simple)
    {
        $productData = $this->loadData($productType. '_product_related',
                                           array('bundle_items_search_sku' => $simple,
                                                 'configurable_attribute_title' => $attrData['admin_title'],
                                                 'associated_search_sku' => $simple));
        $this->loginAdminUser();
        $this->navigate('manage_products');
        $this->productHelper()->createProduct($productData, $productType);
        $this->assertMessagePresent('success', 'success_saved_product');

        self::$productsInStock[$productType]['general_name'] = $productData['general_name'];
        self::$productsInStock[$productType]['general_sku'] = $productData['general_sku'];
    }

    /**
     * <p>Preconditions</p>
     * <p>Create products for linking out of stock</p>
     *
     * @dataProvider productTypesDataProvider
     * @depends createAttribute
     * @depends createSimpleProductForBundle
     *
     * @test
     */
    public function createProductsForLinkingOutOfStock($productType, $attrData, $simple)
    {
        $productData = $this->loadData($productType. '_product_related',
                                           array('bundle_items_search_sku' => $simple,
                                                 'configurable_attribute_title' => $attrData['admin_title'],
                                                 'associated_search_sku' => $simple,
                                                 'inventory_stock_availability' => 'Out of Stock'));
        $this->loginAdminUser();
        $this->navigate('manage_products');
        $this->productHelper()->createProduct($productData, $productType);
        $this->assertMessagePresent('success', 'success_saved_product');

        self::$productsOutOfStock[$productType]['general_name'] = $productData['general_name'];
        self::$productsOutOfStock[$productType]['general_sku'] = $productData['general_sku'];
    }

    public function productTypesDataProvider()
    {
        return array(
            array('simple'),
            array('virtual'),
            array('downloadable'),
            array('bundle'),
            array('configurable'),
            array('grouped')
        );
    }

    /**
     * <p>Review related products on frontend.</p>
     * <p>Preconditions:</p>
     * <p>Create All Types of products (in stock) and realize next test for all of them;</p>
     * <p>Steps:</p>
     * <p>1. Create 1 grouped product in stock; Attach all types of products to the first one as related products</p>
     * <p>2. Navigate to frontend;</p>
     * <p>3. Open product details page;</p>
     * <p>4. Validate names of related products in "related products block";</p>
     * <p>Expected result:</p>
     * <p>Products are created, The grouped product contains block with related products; Names of related products are correct</p>
     *
     * @depends createSimpleProductForBundle
     * @depends createProductsForLinkingInStock
     * @test
     */
    public function relatedInStock($simple)
    {
        $productData1 = $this->loadData('grouped_product_for_linking_products',
                                       array('associated_search_sku' => $simple));
        $productData2 = $this->loadData('grouped_product_for_linking_products',
                                       array('associated_search_sku' => $simple));
        $i = 1;
        foreach (self::$productsInStock as $prod) {
            if ($i % 2) {
                $productData1['related_data']['related_' . $i++]['related_search_sku'] = $prod['general_sku'];
            } else {
                $productData2['related_data']['related_' . $i++]['related_search_sku'] = $prod['general_sku'];
            }

        }
        $this->loginAdminUser();
        $this->navigate('manage_products');
        $this->productHelper()->createProduct($productData1, 'grouped');
        $this->assertMessagePresent('success', 'success_saved_product');
        $this->productHelper()->createProduct($productData2, 'grouped');
        $this->assertMessagePresent('success', 'success_saved_product');

        $this->reindexInvalidedData();
        $this->clearInvalidedCache();
        $this->logoutCustomer();
        $i = 1;
        $errors = array();
        foreach (self::$productsInStock as $prod) {
            if ($i % 2) {
                $this->productHelper()->frontOpenProduct($productData1['general_name']);
            } else {
                $this->productHelper()->frontOpenProduct($productData2['general_name']);
            }
            $this->addParameter('productName', $prod['general_name']);
            if (!$this->controlIsPresent('link', 'related_product')) {
                $errors[] = 'Related Product ' . $prod['general_name'] . ' is not on the page';
            }
            $i++;
        }
        if (!empty($errors)) {
            $this->fail(implode("\n", $errors));
        }
    }

    /**
     * <p>Review related products on frontend.</p>
     * <p>Preconditions:</p>
     * <p>Create All Types of products (out of stock) and realize next test for all of them;</p>
     * <p>Steps:</p>
     * <p>1. Create 1 grouped product in stock; Attach all types of products to the first one as related products</p>
     * <p>2. Navigate to frontend;</p>
     * <p>3. Open product details page for the first product;</p>
     * <p>4. Check if the first product contains any related products;</p>
     * <p>Expected result:</p>
     * <p>Products are created, The grouped product does not contains any related products;</p>
     *
     * @depends createSimpleProductForBundle
     * @depends createProductsForLinkingOutOfStock
     * @test
     */
    public function relatedOutOfStock($simple)
    {
        $productData = $this->loadData('grouped_product_for_linking_products',
                                       array('associated_search_sku' => $simple));
        $i = 1;
        foreach (self::$productsOutOfStock as $prod) {
            $productData['related_data']['related_' . $i++]['related_search_sku'] = $prod['general_sku'];
        }
        $this->loginAdminUser();
        $this->navigate('manage_products');
        $this->productHelper()->createProduct($productData, 'grouped');
        $this->assertMessagePresent('success', 'success_saved_product');

        $this->reindexInvalidedData();
        $this->clearInvalidedCache();
        $this->logoutCustomer();
        $errors = array();
        $this->productHelper()->frontOpenProduct($productData['general_name']);
        foreach (self::$productsOutOfStock as $prod) {
            $this->addParameter('productName', $prod['general_name']);
            if ($this->controlIsPresent('link', 'related_product')) {
                $errors[] = 'Related Product ' . $prod['general_name'] . ' is on the page';
            }
        }
        if (!empty($errors)) {
            $this->fail(implode("\n", $errors));
        }
    }

    /**
     * <p>Review Cross-sell products on frontend.</p>
     * <p>Preconditions:</p>
     * <p>Create All Types of products (in stock) and realize next test for all of them;</p>
     * <p>Steps:</p>
     * <p>1. Create 1 grouped product in stock;  Attach all types of products to the first one as cross-sell product</p>
     * <p>2. Navigate to frontend;</p>
     * <p>3. Open product details page;</p>
     * <p>4. Add product to shopping cart;</p>
     * <p>5. Validate names of cross-sell products in "cross-sell products block" in shopping cart;</p>
     * <p>Expected result:</p>
     * <p>Products are created, The grouped product contains block with cross-sell products; Names of cross-sell products are correct</p>
     *
     * @depends createSimpleProductForBundle
     * @depends createProductsForLinkingInStock
     * @test
     */
    public function crossSellsInStock($simple)
    {
        $productData1 = $this->loadData('grouped_product_for_linking_products',
                                       array('associated_search_sku' => $simple));
        $productData2 = $this->loadData('grouped_product_for_linking_products',
                                       array('associated_search_sku' => $simple));
        $i = 1;
        foreach (self::$productsInStock as $prod) {
            if ($i % 2) {
                $productData1['cross_sells_data']['cross_sells_' . $i++]['cross_sells_search_sku'] = $prod['general_sku'];
            } else {
                $productData2['cross_sells_data']['cross_sells_' . $i++]['cross_sells_search_sku'] = $prod['general_sku'];
            }

        }
        $this->loginAdminUser();
        $this->navigate('manage_products');
        $this->productHelper()->createProduct($productData1, 'grouped');
        $this->assertMessagePresent('success', 'success_saved_product');
        $this->productHelper()->createProduct($productData2, 'grouped');
        $this->assertMessagePresent('success', 'success_saved_product');

        $this->reindexInvalidedData();
        $this->clearInvalidedCache();
        $this->logoutCustomer();
        $i = 1;
        $errors = array();
        foreach (self::$productsInStock as $prod) {
            $this->addParameter('crosssellProductName', $prod['general_name']);
            if ($i % 2) {
                $this->productHelper()->frontOpenProduct($productData1['general_name']);
                $this->productHelper()->frontAddProductToCart();
            } else {
                $this->productHelper()->frontOpenProduct($productData2['general_name']);
                $this->productHelper()->frontAddProductToCart();
            }
            if (!$this->controlIsPresent('link', 'crosssell_product')) {
                $errors[] = 'Cross-sell Product ' . $prod['general_name'] . ' is not on the page';
            }
            $this->shoppingCartHelper()->frontClearShoppingCart();
            $i++;
        }
        if (!empty($errors)) {
            $this->fail(implode("\n", $errors));
        }
    }

    /**
     * <p>Review Cross-sell products on frontend.</p>
     * <p>Preconditions:</p>
     * <p>Create All Types of products (out of stock) and realize next test for all of them;</p>
     * <p>Steps:</p>
     * <p>1. Create 1 grouped products in stock; Attach all types of products to the first one as cross-sell product</p>
     * <p>2. Navigate to frontend;</p>
     * <p>3. Open product details page;</p>
     * <p>4. Add product to shopping cart;</p>
     * <p>5. Validate that shopping cart page with the added product does not contains any cross-sell products;</p>
     * <p>Expected result:</p>
     * <p>Products are created, The grouped product in the shopping cart does not contain the cross-sell products</p>
     *
     * @depends createSimpleProductForBundle
     * @depends createProductsForLinkingOutOfStock
     * @test
     */
    public function crossSellsOutOfStock($simple)
    {
        $productData = $this->loadData('grouped_product_for_linking_products',
                                       array('associated_search_sku' => $simple));
        $i = 1;
        foreach (self::$productsOutOfStock as $prod) {
            $productData['cross_sells_data']['cross_sells_' . $i++]['cross_sells_search_sku'] = $prod['general_sku'];
        }
        $this->loginAdminUser();
        $this->navigate('manage_products');
        $this->productHelper()->createProduct($productData, 'grouped');
        $this->assertMessagePresent('success', 'success_saved_product');

        $this->reindexInvalidedData();
        $this->clearInvalidedCache();
        $this->logoutCustomer();
        $errors = array();
        $this->productHelper()->frontOpenProduct($productData['general_name']);
        $this->productHelper()->frontAddProductToCart();
        foreach (self::$productsOutOfStock as $prod) {
            $this->addParameter('crosssellProductName', $prod['general_name']);
            if ($this->controlIsPresent('link', 'crosssell_product')) {
                $errors[] = 'Cross-sell Product ' . $prod['general_name'] . ' is on the page';
            }
        }
        if (!empty($errors)) {
            $this->fail(implode("\n", $errors));
        }
    }

    /**
     * <p>Review Up-sell products on frontend.</p>
     * <p>Preconditions:</p>
     * <p>Create All Types of products (in stock) and realize next test for all of them;</p>
     * <p>Steps:</p>
     * <p>1. Create 1 grouped product in stock; Attach all types of products to the first one as up-sell products</p>
     * <p>2. Navigate to frontend;</p>
     * <p>3. Open product details page;</p>
     * <p>4. Validate names of up-sell products in "up-sell products block";</p>
     * <p>Expected result:</p>
     * <p>Products are created, The grouped product contains block with up-sell products; Names of up-sell products are correct</p>
     *
     * @depends createSimpleProductForBundle
     * @depends createProductsForLinkingInStock
     * @test
     */
    public function upSellsInStock($simple)
    {
        $productData1 = $this->loadData('grouped_product_for_linking_products',
                                       array('associated_search_sku' => $simple));
        $productData2 = $this->loadData('grouped_product_for_linking_products',
                                       array('associated_search_sku' => $simple));
        $i = 1;
        foreach (self::$productsInStock as $prod) {
            if ($i % 2) {
                $productData1['up_sells_data']['up_sells_' . $i++]['up_sells_search_sku'] = $prod['general_sku'];
            } else {
                $productData2['up_sells_data']['up_sells_' . $i++]['up_sells_search_sku'] = $prod['general_sku'];
            }

        }
        $this->loginAdminUser();
        $this->navigate('manage_products');
        $this->productHelper()->createProduct($productData1, 'grouped');
        $this->assertMessagePresent('success', 'success_saved_product');
        $this->productHelper()->createProduct($productData2, 'grouped');
        $this->assertMessagePresent('success', 'success_saved_product');

        $this->reindexInvalidedData();
        $this->clearInvalidedCache();
        $this->logoutCustomer();
        $i = 1;
        $errors = array();
        foreach (self::$productsInStock as $prod) {
            if ($i % 2) {
                $this->productHelper()->frontOpenProduct($productData1['general_name']);
            } else {
                $this->productHelper()->frontOpenProduct($productData2['general_name']);
            }
            $this->addParameter('productName', $prod['general_name']);
            if (!$this->controlIsPresent('link', 'upsell_product')) {
                $errors[] = 'Up-sell Product ' . $prod['general_name'] . ' is not on the page';
            }
            $i++;
        }
        if (!empty($errors)) {
            $this->fail(implode("\n", $errors));
        }
    }

    /**
     * <p>Review Up-sell products on frontend.</p>
     * <p>Preconditions:</p>
     * <p>Create All Types of products (out of stock) and realize next test for all of them;</p>
     * <p>Steps:</p>
     * <p>1. Create 1 grouped product in stock; Attach all types of products to the first one as up-sell products</p>
     * <p>2. Navigate to frontend;</p>
     * <p>3. Open product details page;</p>
     * <p>4. Validate that product details page for the first product does not contain up-sell block with the products;</p>
     * <p>Expected result:</p>
     * <p>Products are created, The grouped product details page does not contain any up-sell product</p>
     *
     * @depends createSimpleProductForBundle
     * @depends createProductsForLinkingOutOfStock
     * @test
     */
    public function upSellsOutOfStock($simple)
    {
        $productData = $this->loadData('grouped_product_for_linking_products',
                                       array('associated_search_sku' => $simple));
        $i = 1;
        foreach (self::$productsOutOfStock as $prod) {
            $productData['up_sells_data']['up_sells_' . $i++]['up_sells_search_sku'] = $prod['general_sku'];
        }
        $this->loginAdminUser();
        $this->navigate('manage_products');
        $this->productHelper()->createProduct($productData, 'grouped');
        $this->assertMessagePresent('success', 'success_saved_product');

        $this->reindexInvalidedData();
        $this->clearInvalidedCache();
        $this->logoutCustomer();
        $errors = array();
        $this->productHelper()->frontOpenProduct($productData['general_name']);
        foreach (self::$productsOutOfStock as $prod) {
            $this->addParameter('productName', $prod['general_name']);
            if ($this->controlIsPresent('link', 'upsell_product')) {
                $errors[] = 'Up-sell Product ' . $prod['general_name'] . ' is on the page';
            }
        }
        if (!empty($errors)) {
            $this->fail(implode("\n", $errors));
        }
    }
}
