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
 * Tags Validation on the frontend
 *
 * @package     selenium
 * @subpackage  tests
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class Core_Mage_Tags_FrontendCreateTest extends Mage_Selenium_TestCase
{
    protected function assertPreConditions()
    {
        $this->addParameter('productUrl', '');
    }

    /**
     * <p>Preconditions</p>
     * <p>Create Customer for tests</p>
     *
     * @return array
     * @test
     */
    public function createCustomer()
    {
        //Data
        $userData = $this->loadData('customer_account_for_prices_validation', null, 'email');
        //Steps
        $this->loginAdminUser();
        $this->navigate('manage_customers');
        $this->customerHelper()->createCustomer($userData);
        //Verifying
        $this->assertMessagePresent('success', 'success_saved_customer');

        return array('email'    => $userData['email'],
                     'password' => $userData['password']);
    }

    /**
     * <p>Preconditions</p>
     * <p>Creates Category to use during tests</p>
     *
     * @return string
     * @test
     */
    public function createCategory()
    {
        //Data
        $categoryData = $this->loadData('sub_category_required');
        //Steps
        $this->loginAdminUser();
        $this->navigate('manage_categories', false);
        $this->categoryHelper()->checkCategoriesPage();
        $this->categoryHelper()->createCategory($categoryData);
        //Verification
        $this->assertMessagePresent('success', 'success_saved_category');
        $this->categoryHelper()->checkCategoriesPage();

        return $categoryData['parent_category'] . '/' . $categoryData['name'];
    }

    /**
     * <p>Preconditions</p>
     * <p>Create Simple Products for tests</p>
     *
     * @param $category
     *
     * @return mixed
     * @test
     * @depends createCategory
     */
    public function createProduct($category)
    {
        $this->loginAdminUser();
        $this->navigate('manage_products');
        $simpleProductData = $this->loadData('simple_product_for_prices_validation_front_1',
                                             array('categories' => $category), array('general_name', 'general_sku'));
        $this->productHelper()->createProduct($simpleProductData);
        $this->assertMessagePresent('success', 'success_saved_product');
        return $simpleProductData['general_name'];
    }

    /**
     * <p>Tag creating with Logged Customer</p>
     * <p>1. Login to Frontend</p>
     * <p>2. Open created product</p>
     * <p>3. Add Tag to product</p>
     * <p>4. Check confirmation message</p>
     * <p>5. Goto "My Account"</p>
     * <p>6. Check tag displaying in "My Recent Tags"</p>
     * <p>7. Goto "My Tags" tab</p>
     * <p>8. Check tag displaying on the page</p>
     * <p>9. Open current tag - page with assigned product opens</p>
     * <p>10. Tag is assigned to correct product</p>
     *
     * @param $tags
     * @param $customer
     * @param $product
     *
     * @test
     * @dataProvider tagNameDataProvider
     * @depends createCustomer
     * @depends createProduct
     * @TestlinkId TL-MAGE-3651
     */
    public function frontendTagVerificationLoggedCustomer($tags, $customer, $product)
    {
        //Setup
        $this->customerHelper()->frontLoginCustomer($customer);
        $this->productHelper()->frontOpenProduct($product);
        //Steps
        $this->tagsHelper()->frontendAddTag($tags);
        //Verification
        $this->assertMessagePresent('success', 'tag_accepted_success');
        $this->tagsHelper()->frontendTagVerification($tags, $product);
        //Cleanup
        $this->navigate('my_account_my_tags');
        $this->tagsHelper()->frontendDeleteTags($tags);
    }

    public function tagNameDataProvider()
    {
        return array(
            //1 simple word
            array($this->generate('string', 4, ':alpha:')),
            //1 tag enclosed within quotes
            array("'" . $this->generate('string', 4, ':alpha:') . "'"),
            //2 tags separated with a space
            array($this->generate('string', 4, ':alpha:') . ' ' . $this->generate('string', 7, ':alpha:')),
            //1 tag with a space; enclosed within quotes
            array("'" . $this->generate('string', 4, ':alpha:') . ' ' . $this->generate('string', 7, ':alpha:') . "'"),
            //3 tags = 1 word + 1 phrase with a space + 1 word; enclosed within quotes
            array($this->generate('string', 4, ':alpha:') . ' '
                      . "'" . $this->generate('string', 4, ':alpha:') . ' ' . $this->generate('string', 7,
                                                                                              ':alpha:') . "'"
                      . ' ' . $this->generate('string', 4, ':alpha:')),
        );
    }

    /**
     * <p>Tags Verification in Category</p>
     * <p>1. Login to Frontend</p>
     * <p>2. Open created product</p>
     * <p>3. Add Tag to product</p>
     * <p>4. Check confirmation message</p>
     * <p>5. Logout;</p>
     * <p>6. Login to backend;</p>
     * <p>7. Navigate to "Catalog->Tags->Pending Tags";</p>
     * <p>8. Change the status of created Tag;</p>
     * <p>9. Goto Frontend;</p>
     * <p>10. Check Tag displaying on category page;</p>
     *
     * @param $customer
     * @param $category
     * @param $product
     *
     * @test
     * @depends createCustomer
     * @depends createCategory
     * @depends createProduct
     * @TestlinkId    TL-MAGE-3650
     */
    public function frontendTagVerificationInCategory($customer, $category, $product)
    {
        //Data
        $tag = $this->generate('string', 10, ':alpha:');
        //Setup
        $this->customerHelper()->frontLoginCustomer($customer);
        $this->productHelper()->frontOpenProduct($product);
        //Steps
        $this->tagsHelper()->frontendAddTag($tag);
        //Verification
        $this->assertMessagePresent('success', 'tag_accepted_success');
        //Steps
        $this->loginAdminUser();
        $this->navigate('pending_tags');
        $tagToApprove = $this->loadData('backend_search_tag', array('tag_name' => $tag));
        $this->tagsHelper()->changeTagsStatus(array($tagToApprove), 'Approved');
        //Verification
        $this->frontend();
        $this->tagsHelper()->frontendTagVerificationInCategory($tag, $product, $category);
        //Cleanup
        $this->navigate('my_account_my_tags');
        $this->tagsHelper()->frontendDeleteTags($tag);
        $this->assertMessagePresent('success', 'success_deleted_tag');
    }

    /**
     * Tag creating with Not Logged Customer
     * <p>1. Goto Frontend</p>
     * <p>2. Open created product</p>
     * <p>3. Add Tag to product</p>
     * <p>4. Login page opened</p>
     * <p>Expected result:</p>
     * <p>Customer is redirected to the login page.</p>
     * <p>The tag hasn't been added for moderation in backend.</p>
     *
     * @param $product
     *
     * @test
     * @depends createProduct
     * @TestlinkId    TL-MAGE-3652
     */
    public function frontendTagVerificationNotLoggedCustomer($product)
    {
        //Data
        $tag = $this->generate('string', 8, ':alpha:');
        //Setup
        $this->logoutCustomer();
        $this->productHelper()->frontOpenProduct($product);
        //Steps
        $this->tagsHelper()->frontendAddTag($tag);
        //Verification
        $this->assertTrue($this->checkCurrentPage('customer_login'), $this->getParsedMessages());
        $this->loginAdminUser();
        $this->navigate('all_tags');
        $searchTag = $this->loadData('backend_search_tag', array('tag_name' => $tag));
        $xpathTR = $this->search($searchTag, 'tags_grid');
        $this->assertTrue(is_null($xpathTR), $this->getMessagesOnPage());
    }
}