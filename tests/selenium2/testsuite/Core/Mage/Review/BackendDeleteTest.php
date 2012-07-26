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
 * Delete review into backend
 *
 * @package     selenium
 * @subpackage  tests
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class Core_Mage_Review_BackendDeleteTest extends Mage_Selenium_TestCase
{
    /**
     * <p>Preconditions:</p>
     * <p>Login as admin to backend</p>
     * <p>Navigate to Catalog -> Reviews and Ratings -> Customer Reviews -> All Reviews</p>
     */
    protected function assertPreConditions()
    {
        $this->loginAdminUser();
    }

    /**
     * <p>Preconditions:</p>
     *
     * @test
     * @return array
     */
    public function preconditionsForTests()
    {
        //Data
        $simpleData = $this->loadData('simple_product_visible');
        $storeView = $this->loadData('generic_store_view');
        $ratingData = $this->loadData('default_rating', array('visible_in' => $storeView['store_view_name']));
        //Steps
        $this->navigate('manage_products');
        $this->productHelper()->createProduct($simpleData);
        //Verification
        $this->assertMessagePresent('success', 'success_saved_product');
        //Steps
        $this->navigate('manage_stores');
        $this->storeHelper()->createStore($storeView, 'store_view');
        //Verification
        $this->assertMessagePresent('success', 'success_saved_store_view');
        //Steps
        $this->navigate('manage_ratings');
        $this->ratingHelper()->createRating($ratingData);
        //Verification
        $this->assertMessagePresent('success', 'success_saved_rating');
        return array(
                'sku'        => $simpleData['general_sku'],
                'name'       => $simpleData['general_name'],
                'store'      => $storeView['store_view_name'],
                'withRating' => array('filter_sku'  => $simpleData['general_sku'],
                                      'rating_name' => $ratingData['default_value'],
                                      'visible_in'  => $storeView['store_view_name']));
    }

    /**
     * <p>Delete review with Rating</p>
     *
     * <p>Preconditions:</p>
     * <p>Rating created</p>
     * <p>Review with rating created;</p>
     * <p>Steps:</p>
     * <p>1. Select created review from the list and open it;</p>
     * <p>2. Click "Delete Review" button;</p>
     * <p>Expected result:</p>
     * <p>Success message appears - review removed from the list</p>
     *
     * @param $data
     *
     * @test
     * @depends preconditionsForTests
     * @TestlinkId    TL-MAGE-3600
     */
    public function deleteWithRating($data)
    {
        //Data
        $reviewData = $this->loadData('review_required_with_rating', $data['withRating']);
        $search = $this->loadData('search_review_admin',
                array('filter_nickname' => $reviewData['nickname'], 'filter_product_sku' => $data['sku']));
        //Steps
        $this->navigate('manage_all_reviews');
        $this->reviewHelper()->createReview($reviewData);
        //Verification
        $this->assertMessagePresent('success', 'success_saved_review');
        //Steps
        $this->reviewHelper()->deleteReview($search);
        //Verification
        $this->assertMessagePresent('success', 'success_deleted_review');
    }

    /**
     * <p>Delete review without Rating</p>
     *
     * <p>Preconditions:</p>
     * <p>Review without rating created;</p>
     * <p>Steps:</p>
     * <p>1. Select created review from the list and open it;</p>
     * <p>2. Click "Delete Review" button;</p>
     * <p>Expected result:</p>
     * <p>Success message appears - review removed from the list</p>
     *
     * @param $data
     *
     * @test
     * @depends preconditionsForTests
     * @TestlinkId    TL-MAGE-461
     */
    public function deleteWithoutRating($data)
    {
        //Data
        $reviewData = $this->loadData('review_required_without_rating', array('filter_sku' => $data['sku']));
        $search = $this->loadData('search_review_admin',
                array('filter_nickname' => $reviewData['nickname'], 'filter_product_sku' => $data['sku']));
        //Steps
        $this->navigate('manage_all_reviews');
        $this->reviewHelper()->createReview($reviewData);
        //Verification
        $this->assertMessagePresent('success', 'success_saved_review');
        //Steps
        $this->reviewHelper()->deleteReview($search);
        //Verification
        $this->assertMessagePresent('success', 'success_deleted_review');
    }

    /**
     * <p>Delete review using Mass-Action</p>
     *
     * <p>Preconditions:</p>
     * <p>Review created;</p>
     * <p>Steps:</p>
     * <p>1. Select created review from the list check it;</p>
     * <p>2. Select "Delete" in Actions;</p>
     * <p>3. Click "Submit" button;</p>
     * <p>Success message appears - review removed from the list</p>
     *
     * @param $data
     *
     * @test
     * @depends preconditionsForTests
     * @TestlinkId    TL-MAGE-459
     */
    public function deleteMassAction($data)
    {
        //Data
        $reviewData = $this->loadData('review_required_without_rating', array('filter_sku' => $data['sku']));
        $search = $this->loadData('search_review_admin',
                array('filter_nickname' => $reviewData['nickname'], 'filter_product_sku' => $data['sku']));
        //Steps
        $this->navigate('manage_all_reviews');
        $this->reviewHelper()->createReview($reviewData);
        //Verification
        $this->assertMessagePresent('success', 'success_saved_review');
        //Steps
        $this->searchAndChoose($search);
        $this->fillForm(array('actions' => 'Delete'));
        $this->clickButtonAndConfirm('submit', 'confirmation_for_delete_all');
        //Verification
        $this->assertMessagePresent('success', 'success_deleted_review_massaction');
    }
}