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
class Core_Mage_Review_Helper extends Mage_Selenium_TestCase
{
    /**
     * Creates review
     *
     * @param array|string $reviewData
     */
    public function createReview($reviewData)
    {
        if (is_string($reviewData)) {
            $reviewData = $this->loadData($reviewData);
        }
        $reviewData = $this->arrayEmptyClear($reviewData);
        $this->clickButton('add_new_review');
        $product = (isset($reviewData['product_to_review'])) ? $reviewData['product_to_review'] : array();
        if (!$product) {
            $this->fail('Data for selecting product for review is not set');
        }
        $this->searchAndOpen($product, false, 'select_product_grid');
        $this->validatePage();
        $this->fillInfo($reviewData);
        $this->saveForm('save_review');
    }

    /**
     * Edit existing review
     *
     * @param array $reviewData
     * @param array $searchData
     */
    public function editReview(array $reviewData, array $searchData)
    {
        $reviewData = $this->arrayEmptyClear($reviewData);
        $this->openReview($searchData);
        $this->fillInfo($reviewData);
        $this->saveForm('save_review');
    }

    /**
     * Opens review
     *
     * @param array $reviewSearch
     */
    public function openReview(array $reviewSearch)
    {
        if (isset($reviewSearch['filter_websites'])
                && !$this->controlIsPresent('dropdown', 'filter_websites')) {
            unset($reviewSearch['filter_websites']);
        }
        $this->searchAndOpen($reviewSearch);
    }

    /**
     * Fills tabs in new/edit review
     *
     * @param string|array $reviewData
     */
    public function fillInfo($reviewData)
    {
        if (isset($reviewData['visible_in'])
                && !$this->controlIsPresent('multiselect', 'visible_in')) {
            unset($reviewData['visible_in']);
        }
        $this->fillForm($reviewData);
        if (isset($reviewData['product_rating'])) {
            $this->fillRatings($reviewData['product_rating']);
        }
    }

    /**
     * Fills ratings
     *
     * @param array $detailedRatings
     */
    public function fillRatings(array $detailedRatings)
    {
        if ($this->controlIsPresent('message', 'not_available_rating')) {
            $this->fail('Rating is not available for this store view');
        }
        foreach ($detailedRatings as $value) {
            if (isset($value['rating_name']) && isset($value['stars'])) {
                $this->addParameter('ratingName', $value['rating_name']);
                $this->addParameter('stars', $value['stars']);
                $this->fillForm(array('detailed_rating' => 'yes'));
            } else {
                $this->fail('Incorrect data to fill');
            }
        }
    }

    /**
     * Open Review and delete
     *
     * @param array $searchData
     */
    public function deleteReview(array $searchData)
    {
        $this->openReview($searchData);
        $this->clickButtonAndConfirm('delete_review', 'confirmation_for_delete');
    }

    /**
     * Verify Review
     *
     * @param array|string $reviewData
     * @param array $skipFields
     */
    public function verifyReviewData($reviewData, $skipFields = array())
    {
        if (is_string($reviewData)) {
            $reviewData = $this->loadData($reviewData);
        }
        $reviewData = $this->arrayEmptyClear($reviewData);
        if (isset($reviewData['visible_in'])
                && !$this->controlIsPresent('multiselect', 'visible_in')) {
            $skipFields = array_merge($skipFields, array('visible_in'));
        }
        $ratings = (isset($reviewData['product_rating'])) ? $reviewData['product_rating'] : array();

        $this->verifyForm($reviewData, $skipFields);
        foreach ($ratings as $rating => $ratingData) {
            $this->addParameter('ratingName', $ratingData['rating_name']);
            $this->addParameter('stars', $ratingData['stars']);
            $this->verifyChecked($this->_getControlXpath('radiobutton', 'detailed_rating'));
        }
        $this->assertEmptyVerificationErrors();
    }

    #********************************************
    #           Frontend Methods                *
    #********************************************

    /**
     * <p>Create Review</p>
     *
     * @param array|string $reviewData
     * @param bool $validateRating      In case $validateRating == TRUE - rating filling will be mandatory
     */
    public function frontendAddReview($reviewData, $validateRating = true)
    {
        if (is_string($reviewData)) {
            $reviewData = $this->loadData($reviewData);
        }
        $reviewData = $this->arrayEmptyClear($reviewData);
        $linkName = ($this->controlIsPresent('link', 'add_your_review')) ? 'add_your_review' : 'first_review';
        $this->defineCorrectParam($linkName);
        $this->clickControl('link', $linkName);
        $this->fillForm($reviewData);
        if (isset($reviewData['ratings'])) {
            $this->frontendAddRating($reviewData['ratings'], $validateRating);
        }
        $this->saveForm('submit_review');
    }

    /**
     * Filling In Rating
     *
     * @param array|string $ratingData
     * @param bool $validateRating
     */
    public function frontendAddRating($ratingData, $validateRating = true)
    {
        if (is_string($ratingData)) {
            $ratingData = $this->loadData($ratingData);
        }
        foreach ($ratingData as $value) {
            $this->addParameter('rateName', $value['rating_name']);
            $this->addParameter('rateId', $value['stars']);
            if ($this->controlIsPresent('radiobutton', 'select_rate')) {
                $this->fillForm(array('select_rate' => 'Yes'));
            } else {
                $xpath = $this->_getControlXpath('radiobutton', 'select_rate');
                $this->addVerificationMessage('Rating with name ' . $value['rating_name'] . ' is not on the page');
            }
        }
        if ($validateRating) {
            $this->assertEmptyVerificationErrors();
        }
    }

    /**
     * Review verification after approve
     * (@TODO doesn't work for several reviews posted by one nickname)
     *
     * @param array $verifyData
     * @param string $productName
     */
    public function frontVerifyReviewDisplaying(array $verifyData, $productName)
    {
        $this->addParameter('productName', $productName);

        $verifyData = $this->arrayEmptyClear($verifyData);
        $review = (isset($verifyData['review'])) ? $verifyData['review'] : '';
        $nickname = (isset($verifyData['nickname'])) ? $verifyData['nickname'] : '';
        $summary = (isset($verifyData['summary_of_review'])) ? $verifyData['summary_of_review'] : '';
        $rating = (isset($verifyData['product_rating'])) ? $verifyData['product_rating'] : array();
        $ratinNames = array();
        $actualRatings = array();
        foreach ($rating as $key => $value) {
            $ratinNames[] = $value['rating_name'];
        }
        if ($this->controlIsPresent('link', 'reviews')) {
            //Open reviews
            $this->defineCorrectParam('reviews');
            $this->clickControl('link', 'reviews');
            $this->addParameter('reviewerName', $nickname);
            if (!$this->controlIsPresent('pageelement', 'review_reviwer_name')) {
                $this->fail('Customer with nickname \'' . $nickname . '\' does not added approved review');
            }
            //Define actual review summary
            $actualSummary = $this->getText($this->_getControlXpath('link', 'review_summary'));
            //Define actual review text and rating names
            $xpathReview = $this->_getControlXpath('pageelement', 'review_details');
            $xpathReviewRatings = $xpathReview . '/table';
            $text = preg_quote($this->getText($xpathReview . '/small'));
            $actualReview = trim(preg_replace('#' . $text . '#', '', $this->getText($xpathReview)));
            if ($this->isElementPresent($xpathReviewRatings)) {
                $text = preg_quote($this->getText($xpathReviewRatings));
                $actualReview = trim(preg_replace('#' . $text . '#', '', $actualReview), " \t\n\r\0\x0B");
                $ratingsCount = $this->getXpathCount($xpathReviewRatings . '//th');
                for ($i = 0; $i < $ratingsCount; $i++) {
                    $actualRatings[] = $this->getTable($xpathReviewRatings . '.' . $i . '.0');
                }
            }
            //Verification on product page
            $this->assertEquals($summary, $actualSummary,
                    'Review Summary is not equal to specified: (' . $summary . ' != ' . $actualSummary . ')');
            $this->assertEquals($review, $actualReview,
                    'Review Text is not equal to specified: (' . $review . ' != ' . $actualReview . ')');
            $this->assertEquals($ratinNames, $actualRatings, 'Review Rating names is not equal to specified');
            //Verification on Review Details page
            $this->clickControl('link', 'review_summary');
            $this->verifyTextPresent($productName,
                    $productName . ' product not display on Review Details page');
            $this->verifyTextPresent($review,
                    '\'' . $review . '\' review text not display on Review Details page');
            $this->assertEmptyVerificationErrors();
        } else {
            $this->fail('Product does not have approved review(s)');
        }
    }

    /**
     * Verification review on frontend
     * (@TODO doesn't work for several reviews posted by one nickname)
     *
     * @param array $reviewData
     * @param string $productName
     */
    public function frontVerifyReviewDisplayingInMyAccount($reviewData, $productName)
    {
        //Verification in "My Account"
        $this->navigate('customer_account');
        $this->addParameter('productName', $productName);
        $this->assertTrue($this->controlIsPresent('link', 'product_name'),
                "Can not find product with name: $productName in My Recent Reviews block");
        $this->clickControl('link', 'product_name');
        $this->assertTextPresent($reviewData['review'],
                '\'' . $reviewData['review'] . '\' review text not display on Review Details page');
        //Verification in "My Account -> My Product Reviews"
        $this->navigate('my_product_reviews');
        $this->assertTrue($this->controlIsPresent('link', 'product_name'),
                "Can not find product with name: $productName in My Product Reviews block");
    }

    /**
     * Add parameter ReviewId
     *
     * @param string $linkName
     */
    public function defineCorrectParam($linkName)
    {
        $url = $this->getAttribute($this->_getControlXpath('link', $linkName) . "/@href");
        $this->addParameter('categoryId', $this->defineParameterFromUrl('category', $url));
    }
}
