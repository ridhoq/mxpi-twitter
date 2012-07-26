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
 * Tests for Review verification in Backend
 *
 * @package     selenium
 * @subpackage  tests
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class Core_Mage_Review_BackendCreateTest extends Mage_Selenium_TestCase
{
    /**
     * <p>Preconditions:</p>
     * <p>Login as admin to backend</p>
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
     * <p>Creating a new review without rating</p>
     * <p>Steps:</p>
     * <p>1. Click button "Add New Review"</p>
     * <p>2. Select product from the grid</p>
     * <p>3. Fill in fields in Review Details area</p>
     * <p>4. Click button "Save Review"</p>
     * <p>Expected result:</p>
     * <p>Received the message that the review has been saved.</p>
     * <p>5. Go to Frontend</p>
     * <p>6. Verify review on product page;</p>
     *
     * @param $data
     *
     * @test
     * @depends preconditionsForTests
     * @TestlinkId    TL-MAGE-5350
     */
    public function requiredFieldsWithoutRating($data)
    {
        //Data
        $reviewData = $this->loadData('review_required_without_rating', array('filter_sku' => $data['sku']));
        //Steps
        $this->navigate('manage_all_reviews');
        $this->reviewHelper()->createReview($reviewData);
        //Verification
        $this->assertMessagePresent('success', 'success_saved_review');
        //Steps
        $this->reindexInvalidedData();
        $this->frontend();
        $this->productHelper()->frontOpenProduct($data['name']);
        //Verification
        $this->reviewHelper()->frontVerifyReviewDisplaying($reviewData, $data['name']);
    }

    /**
     * <p>Creating a new review when its visible in other Store View with Rating</p>
     * <p>Preconditions:</p>
     * <p>Rating created</p>
     * <p>Store view created</p>
     * <p>Steps:</p>
     * <p>1. Click button "Add New Review"</p>
     * <p>2. Select product from the grid</p>
     * <p>3. Fill in fields in Review Details area - select desired Store View and specify Rating</p>
     * <p>4. Click button "Save Review"</p>
     * <p>Expected result:</p>
     * <p>Received the message that the review has been saved.</p>
     *
     * <p>Verification:</p>
     * <p>1. Login to frontend;</p>
     * <p>2. Verify that review is absent in Category and product Page in Default Store View;</p>
     * <p>3. Switch to correct store view;</p>
     * <p>4. Verify review in category;</p>
     * <p>5. Verify review on product page;</p>
     *
     * @param $data
     *
     * @test
     * @depends preconditionsForTests
     * @TestlinkId    TL-MAGE-3484
     */
    public function requiredFieldsWithRating($data)
    {
        //Data
        $reviewData = $this->loadData('review_required_with_rating', $data['withRating']);
        //Steps
        $this->navigate('manage_all_reviews');
        $this->reviewHelper()->createReview($reviewData);
        //Verification
        $this->assertMessagePresent('success', 'success_saved_review');
        //Steps
        $this->reindexInvalidedData();
        $this->frontend();
        $this->selectFrontStoreView($data['store']);
        $this->productHelper()->frontOpenProduct($data['name']);
        //Verification
        $this->reviewHelper()->frontVerifyReviewDisplaying($reviewData, $data['name']);
        //Steps
        $this->selectFrontStoreView();
        $this->productHelper()->frontOpenProduct($data['name']);
        //Verification
        $this->clickControl('link', 'reviews');
        $this->addParameter('reviewerName', $reviewData['nickname']);
        $this->assertFalse($this->controlIsPresent('pageelement', 'review_reviwer_name'),
                'Review for product displayed for \'Default Store View\' store view');
    }

    /**
     * <p>Empty fields validation</p>
     * <p>Steps:</p>
     * <p>1. Click button "Add New Review"</p>
     * <p>2. Select product from the grid</p>
     * <p>3. Leave fields in Review Details area empty</p>
     * <p>4. Click button "Save Review"</p>
     * <p>Expected result:</p>
     * <p>Error message appears</p>
     *
     * @param $emptyField
     * @param $fieldType
     * @param $data
     *
     * @test
     * @dataProvider withEmptyRequiredFieldsDataProvider
     * @depends preconditionsForTests
     * @TestlinkId    TL-MAGE-3482
     */
    public function withEmptyRequiredFields($emptyField, $fieldType, $data)
    {
        //Data
        $reviewData = $this->loadData('review_required_with_rating',
                array_merge($data['withRating'], array($emptyField => '%noValue%')));
        if ($emptyField == 'visible_in') {
            $reviewData['product_rating'] = '%noValue%';
        }
        //Steps
        $this->navigate('manage_all_reviews');
        $this->reviewHelper()->createReview($reviewData);
        //Verification
        if ($emptyField == 'product_rating') {
            $this->assertMessagePresent('validation', 'empty_validate_rating');
        } else {
            $this->addFieldIdToMessage($fieldType, $emptyField);
            $this->assertMessagePresent('validation', 'empty_required_field');
        }
        $this->assertTrue($this->verifyMessagesCount(), $this->getParsedMessages());
    }

    public function withEmptyRequiredFieldsDataProvider()
    {
        return array(
            array('visible_in', 'multiselect'),
            array('product_rating', 'radiobutton'),
            array('nickname', 'field'),
            array('summary_of_review', 'field'),
            array('review', 'field'),
        );
    }

    /**
     * <p>Creating a new review with long values into required fields</p>
     * <p>Steps:</p>
     * <p>1. Click button "Add New Review"</p>
     * <p>2. Select product from the grid</p>
     * <p>3. Fill in fields in Review Details area by long values</p>
     * <p>4. Click button "Save Review"</p>
     * <p>Expected result:</p>
     * <p>Received the message that the review has been saved.</p>
     *
     * @param $data
     *
     * @test
     * @depends preconditionsForTests
     * @TestlinkId    TL-MAGE-3481
     */
    public function withLongValues($data)
    {
        //Data
        $reviewData = $this->loadData('admin_review_long_values', array('filter_sku' => $data['sku']));
        $search = $this->loadData('search_review_admin',
                array('filter_nickname' => $reviewData['nickname'], 'filter_product_sku' => $data['sku']));
        //Steps
        $this->navigate('manage_all_reviews');
        $this->reviewHelper()->createReview($reviewData);
        //Verification
        $this->assertMessagePresent('success', 'success_saved_review');
        //Steps
        $this->reviewHelper()->openReview($search);
        //Verification
        $this->reviewHelper()->verifyReviewData($reviewData);
    }

    /**
     * <p>Creating a new review with special characters into required fields</p>
     * <p>Steps:</p>
     * <p>1. Click button "Add New Review"</p>
     * <p>2. Select product from the grid</p>
     * <p>3. Fill in fields in Review Details area by special characters</p>
     * <p>4. Click button "Save Review"</p>
     * <p>Expected result:</p>
     * <p>Received the message that the review has been saved.</p>
     *
     * @param $data
     *
     * @test
     * @depends preconditionsForTests
     * @TestlinkId    TL-MAGE-3485
     */
    public function withSpecialCharacters($data)
    {
        //Data
        $reviewData = $this->loadData('admin_review_special_symbols', array('filter_sku' => $data['sku']));
        $search = $this->loadData('search_review_admin',
                array('filter_nickname' => $reviewData['nickname'], 'filter_product_sku' => $data['sku']));
        //Steps
        $this->navigate('manage_all_reviews');
        $this->reviewHelper()->createReview($reviewData);
        //Verification
        $this->assertMessagePresent('success', 'success_saved_review');
        //Steps
        $this->reviewHelper()->openReview($search);
        //Verification
        $this->reviewHelper()->verifyReviewData($reviewData);
    }

    /**
     * <p> Update review status</p>
     *
     * <p>Preconditions:</p>
     * <p>Review with required fields created and status - "Pending";</p>
     *
     * <p>Steps</p>
     * <p>1. Select created review from the list at "All Reviews" page (by checking checkbox);</p>
     * <p>2. Select in "Actions" - "Update Status";</p>
     * <p>3. Update status to "Approved";</p>
     * <p>4. Click "Submit" button;</p>
     * <p>Expected result:</p>
     * <p>Success message appears - status updated</p>
     *
     * @param $data
     *
     * @test
     * @depends preconditionsForTests
     * @TestlinkId    TL-MAGE-3479
     */
    public function changeStatusOfReview($data)
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
        $this->reviewHelper()->editReview(array('status' => 'Not Approved'), $search);
        //Verification
        $this->assertMessagePresent('success', 'success_saved_review');
    }
}