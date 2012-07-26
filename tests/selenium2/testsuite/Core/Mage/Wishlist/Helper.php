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
 * Helper class
 *
 * @package     selenium
 * @subpackage  tests
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class Core_Mage_Wishlist_Helper extends Mage_Selenium_TestCase
{
    /**
     * Adds product to wishlist from a specific catalog page.
     *
     * @param string $productName
     * @param string $category
     */
    public function frontAddProductToWishlistFromCatalogPage($productName, $category)
    {
        $pageId = $this->categoryHelper()->frontSearchAndOpenPageWithProduct($productName, $category);
        if (!$pageId)
            $this->fail('Could not find the product');
        $this->addParameter('productName', $productName);
        $this->clickControl('link', 'add_to_wishlist');
    }

    /**
     * Adds product to wishlist from the product details page.
     *
     * @param string $productName
     * @param string $categoryPath
     */
    public function frontAddProductToWishlistFromProductPage($productName, $categoryPath = null)
    {
        $this->productHelper()->frontOpenProduct($productName, $categoryPath);
        $this->addParameter('productName', $productName);
        $this->clickControl('link', 'add_to_wishlist');
    }

    /**
     * Finds the product in the wishlist.
     *
     * @param string|array $productNameSet Product name or array of product names to search for.
     * @return true|array True if the products are all present.
     *                    Otherwise returns an array of product names that are absent.
     */
    public function frontWishlistHasProducts($productNameSet)
    {
        if (is_string($productNameSet)) {
            $productNameSet = array($productNameSet);
        }
        $absentProducts = array();
        foreach ($productNameSet as $productName) {
            $this->addParameter('productName', $productName);
            if (!$this->controlIsPresent('link', 'product_name')) {
                $absentProducts[] = $productName;
            }
        }
        return (empty($absentProducts)) ? true : $absentProducts;
    }

    /**
     * Removes the product(s) from the wishlist
     *
     * @param string|array $productNameSet Product name (string) or array of product names to remove
     * @param boolean $validate If true, fails the test in case the removed product is not in the wishlist.
     */
    public function frontRemoveProductsFromWishlist($productNameSet, $validate = true)
    {
        if (is_string($productNameSet))
            $productNameSet = array($productNameSet);
        foreach ($productNameSet as $productName) {
            $this->addParameter('productName', $productName);
            if ($this->controlIsPresent('link', 'remove_item')) {
                $this->clickControlAndConfirm('link', 'remove_item', 'confirmation_for_delete');
            } else if ($validate) {
                $this->fail($productName . ' is not in the wishlist.');
            }
        }
    }

    /**
     * Removes all products from the wishlist
     */
    public function frontClearWishlist()
    {
        while ($this->controlIsPresent('link', 'remove_item_generic')) {
            $this->clickControlAndConfirm('link', 'remove_item_generic', 'confirmation_for_delete');
            $this->assertTrue($this->checkCurrentPage('my_wishlist'), $this->getParsedMessages());
        }
    }

    /**
     * Shares the wishlist
     *
     * @param string|array $shareData Data used to share the wishlist (email, message)
     */
    public function frontShareWishlist($shareData)
    {
        if (!$this->buttonIsPresent('share_wishlist')) {
            $this->fail("Cannot share an empty wishlist");
        }
        $this->clickButton('share_wishlist');
        $this->fillForm($shareData);
        $this->saveForm('share_wishlist');
    }

    /**
     * Adds products to Shopping Cart from the wishlist
     *
     * @param string $productName Product name (string)
     * @param array $productOptions Options to be filled
     */
    public function frontAddToShoppingCart($productName, $productOptions = array())
    {
        $this->addParameter('productName', $productName);
        $this->navigate('my_wishlist');
        if ($this->buttonIsPresent('add_to_cart')) {
            $this->clickButton('add_to_cart');
            if ($this->getCurrentPage() == 'wishlist_configure_product') {
                $this->productHelper()->frontFillBuyInfo($productOptions);
                $this->clickButton('add_to_cart');
            }
        } else {
            $this->fail('Product ' . $productName . ' is not in the wishlist');
        }
    }
}