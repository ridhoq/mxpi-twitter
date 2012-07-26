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
 * "Checkout" link verification - MAGE-5490
 *
 * @package     selenium
 * @subpackage  tests
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class Core_Mage_Various_CheckoutLinkVerificationTest extends Mage_Selenium_TestCase
{
    /**
     * <p>Creating product with required fields only</p>
     * <p>Steps:</p>
     * <p>1. Click "Add product" button;</p>
     * <p>2. Fill in "Attribute Set" and "Product Type" fields;</p>
     * <p>3. Click "Continue" button;</p>
     * <p>4. Fill in required fields;</p>
     * <p>5. Click "Save" button;</p>
     * <p>Expected result:</p>
     * <p>Product is created, confirmation message appears;</p>
     *
     * @return string
     * @test
     */
    public function preconditionForTest()
    {
        //Data
        $productData = $this->loadData('simple_product_visible');
        //Steps
        $this->loginAdminUser();
        $this->navigate('manage_products');
        $this->productHelper()->createProduct($productData);
        //Verifying
        $this->assertMessagePresent('success', 'success_saved_product');

        return $productData['general_name'];
    }

    /**
     * <p>"CHECKOUT" link verification on frontend</p>
     * <p>Preconditions:</p>
     * <p>1.Product is created;</p>
     * <p>2.Customer without address is created and Logged In;</p>
     * <p>Steps:</p>
     * <p>1. Open product page;</p>
     * <p>2. Add product to Shopping Cart;</p>
     * <p>3. Click on "CHECKOUT" link;</p>
     * <p>4. Log Out Customer;</p>
     * <p>5. Open product page;</p>
     * <p>6. Add product to Shopping Cart;</p>
     * <p>7. Click on "CHECKOUT" link;</p>
     * <p>Expected result:</p>
     * <p>User is redirected to OnePageCheckout after steps 3 and 7;</p>
     *
     * @param string $productName
     *
     * @depends preconditionForTest
     * @test
     */
    public function frontendCheckoutLinkVerification($productName)
    {
        //Data
        $userData = $this->loadData('customer_account_register');
        //Steps for Preconditions
        $this->logoutCustomer();
        $this->navigate('customer_login');
        $this->customerHelper()->registerCustomer($userData);
        //Verifying
        $this->assertMessagePresent('success', 'success_registration');
        //Steps
        $this->productHelper()->frontOpenProduct($productName);
        $this->productHelper()->frontAddProductToCart();
        //Validation
        $this->clickControl('link', 'checkout');
        $this->validatePage('onepage_checkout');
        //Steps
        $this->logoutCustomer();
        $this->productHelper()->frontOpenProduct($productName);
        $this->productHelper()->frontAddProductToCart();
        //Validation
        $this->validatePage('shopping_cart');
        $this->clickControl('link', 'checkout');
        $this->validatePage('onepage_checkout');
    }
}
