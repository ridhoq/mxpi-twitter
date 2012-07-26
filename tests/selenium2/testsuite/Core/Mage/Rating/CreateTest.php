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
 * Rating creation into backend
 *
 * @package     selenium
 * @subpackage  tests
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class Core_Mage_Rating_CreateTest extends Mage_Selenium_TestCase
{
    /**
     * <p>Log in to Backend.</p>
     */
    public function setUpBeforeTests()
    {
        $this->loginAdminUser();
    }

    /**
     * <p>Preconditions:</p>
     *
     * @test
     * @return string
     */
    public function preconditionsForTests()
    {
        //Data
        $storeView = $this->loadData('generic_store_view');
        //Steps
        $this->navigate('manage_stores');
        $this->storeHelper()->createStore($storeView, 'store_view');
        //Verification
        $this->assertMessagePresent('success', 'success_saved_store_view');

        return $storeView['store_view_name'];
    }

    /**
     * <p>Creating Rating with required fields only</p>
     *
     * <p>Steps:</p>
     * <p>1. Click "Add New Rating" button;</p>
     * <p>2. Fill in required fields by regular data;</p>
     * <p>3. Click "Save Rating" button;</p>
     * <p>Expected result:</p>
     * <p>Success message appears - rating saved</p>
     *
     * @return array
     * @test
     * @depends preconditionsForTests
     * @TestlinkId	TL-MAGE-3474
     */
    public function withRequiredFieldsOnly()
    {
        //Data
        $ratingData = $this->loadData('rating_required_fields');
        //Steps
        $this->navigate('manage_ratings');
        $this->ratingHelper()->createRating($ratingData);
        //Verification
        $this->assertMessagePresent('success', 'success_saved_rating');

        return $ratingData;
    }

    /**
     * <p>Creating Rating with empty required fields</p>
     *
     * <p>Steps:</p>
     * <p>1. Click "Add New Rating" button;</p>
     * <p>2. Leave required fields empty;</p>
     * <p>3. Click "Save Rating" button;</p>
     * <p>Expected result:</p>
     * <p>Error message appears - "This is a required field";</p>
     *
     * @test
     * @TestlinkId	TL-MAGE-3470
     */
    public function withEmptyDefaultValue()
    {
        //Data
        $ratingData = $this->loadData('rating_required_fields', array('default_value' => '%noValue%'));
        //Steps
        $this->navigate('manage_ratings');
        $this->ratingHelper()->createRating($ratingData);
        //Verification
        $this->addFieldIdToMessage('field', 'default_value');
        $this->assertMessagePresent('validation', 'empty_required_field');
        $this->assertTrue($this->verifyMessagesCount(), $this->getParsedMessages());
    }

    /**
     * <p>Creating Rating with existing name(default value)</p>
     *
     * <p>Steps:</p>
     * <p>1. Click "Add New Rating" button;</p>
     * <p>2. Fill in "Default Value" with existing value;</p>
     * <p>3. Click "Save Rating" button;</p>
     * <p>Expected result:</p>
     * <p>Rating is not saved, Message appears "already exists."</p>
     *
     * @param $ratingData
     *
     * @test
     * @depends withRequiredFieldsOnly
     * @TestlinkId    TL-MAGE-3471
     */
    public function withExistingRatingName($ratingData)
    {
        //Steps
        $this->navigate('manage_ratings');
        $this->ratingHelper()->createRating($ratingData);
        //Verification
        $this->assertMessagePresent('error', 'existing_name');
    }

    /**
     * <p>Creating Rating with filling Fields</p>
     *
     * <p>Preconditions:</p>
     * <p>Store View created</p>
     * <p>Steps:</p>
     * <p>1. Click "Add New Rating" button;</p>
     * <p>2. Fill in all fields by regular data;</p>
     * <p>3. Click "Save Rating" button;</p>
     * <p>Expected result:</p>
     * <p>Success message appears - rating saved</p>
     *
     * @param $storeView
     *
     * @test
     * @depends preconditionsForTests
     * @TestlinkId    TL-MAGE-435
     */
    public function withAllFields($storeView)
    {
        $rating = $this->loadData('default_rating', array('visible_in' => $storeView));
        $search = $this->loadData('search_rating', array('filter_rating_name' => $rating['default_value']));
        //Steps
        $this->navigate('manage_ratings');
        $this->ratingHelper()->createRating($rating);
        //Verification
        $this->assertMessagePresent('success', 'success_saved_rating');
        //Steps
        $this->ratingHelper()->openRating($search);
        $this->ratingHelper()->verifyRatingData($rating);
    }

    /**
     * <p>Creating a new rating with long values into required fields</p>
     * <p>Steps:</p>
     * <p>1. Click button "Add New Rating"</p>
     * <p>2. Fill in fields in Rating Details area by long values</p>
     * <p>4. Click button "Save Rating"</p>
     * <p>Expected result:</p>
     * <p>Received the message that the rating has been saved.</p>
     *
     * @param $storeView
     *
     * @test
     * @depends preconditionsForTests
     * @TestlinkId    TL-MAGE-3473
     */
    public function withLongValues($storeView)
    {
        $rating = $this->loadData('rating_long_values', array('visible_in' => $storeView));
        $search = $this->loadData('search_rating', array('filter_rating_name' => $rating['default_value']));
        //Steps
        $this->navigate('manage_ratings');
        $this->ratingHelper()->createRating($rating);
        //Verification
        $this->assertMessagePresent('success', 'success_saved_rating');
        //Steps
        $this->ratingHelper()->openRating($search);
        $this->ratingHelper()->verifyRatingData($rating);
    }

    /**
     * <p>Creating a new rating with special characters into required fields</p>
     * <p>Steps:</p>
     * <p>1. Click button "Add New Rating"</p>
     * <p>2. Fill in fields in Review Details area by special characters</p>
     * <p>3. Click button "Save Rating"</p>
     * <p>Expected result:</p>
     * <p>Received the message that the rating has been saved.</p>
     *
     * @param $storeView
     * @test
     * @depends preconditionsForTests
     * @TestlinkId	TL-MAGE-3475
     */
    public function withSpecialCharacters($storeView)
    {
        $rating = $this->loadData('rating_special_symbols', array('visible_in' => $storeView));
        $search = $this->loadData('search_rating', array('filter_rating_name' => $rating['default_value']));
        //Steps
        $this->navigate('manage_ratings');
        $this->ratingHelper()->createRating($rating);
        //Verification
        $this->assertMessagePresent('success', 'success_saved_rating');
        //Steps
        $this->ratingHelper()->openRating($search);
        $this->ratingHelper()->verifyRatingData($rating);
    }
}