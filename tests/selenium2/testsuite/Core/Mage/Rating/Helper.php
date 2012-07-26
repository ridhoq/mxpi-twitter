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
class Core_Mage_Rating_Helper extends Mage_Selenium_TestCase
{
    /**
     * Creates rating
     *
     * @param $ratingData
     */
    public function createRating($ratingData)
    {
        $this->clickButton('add_new_rating');
        $this->fillTabs($ratingData);
        $this->saveForm('save_rating');
    }

    /**
     * Edit existing rating
     *
     * @param $ratingData
     * @param $searchData
     */
    public function editRating($ratingData, $searchData)
    {
        $this->openRating($searchData);
        $this->fillTabs($ratingData);
        $this->saveForm('save_rating');
    }

    /**
     * Opens rating
     *
     * @param array $ratingSearch
     */
    public function openRating(array $ratingSearch)
    {
        $ratingSearch = $this->arrayEmptyClear($ratingSearch);
        $xpathTR = $this->search($ratingSearch, 'manage_ratings_grid');
        $this->assertNotEquals(null, $xpathTR, 'Rating is not found');
        $param = $this->getText($xpathTR . '/td[' . $this->getColumnIdByName('Rating Name') . ']');
        $this->addParameter('elementTitle', $param);
        $this->addParameter('id', $this->defineIdFromTitle($xpathTR));
        $this->click($xpathTR);
        $this->waitForPageToLoad($this->_browserTimeoutPeriod);
        $this->validatePage();
    }

    /**
     * Fills tabs in new/edit rating
     *
     * @param string|array $ratingData
     */
    public function fillTabs($ratingData)
    {
        if (is_string($ratingData)) {
            $ratingData = $this->loadData($ratingData);
        }
        $ratingData = $this->arrayEmptyClear($ratingData);
        $this->fillForm($ratingData);
        if (isset($ratingData['store_view_titles'])) {
            $this->fillRatingTitles($ratingData['store_view_titles']);
        }
    }

    /**
     * Fills rating titles for each store view
     *
     * @param array $storeViewTitles
     */
    public function fillRatingTitles(array $storeViewTitles)
    {
        foreach ($storeViewTitles as $value) {
            if (isset($value['store_view_name']) && isset($value['store_view_title'])) {
                $this->addParameter('storeViewName', $value['store_view_name']);
                $this->fillForm(array('store_view_title' => $value['store_view_title']));
            } else {
                $this->fail('Incorrect data to fill');
            }
        }
    }

    /**
     * Open Rating and delete
     *
     * @param array $searchData
     */
    public function deleteRating(array $searchData)
    {
        $this->openRating($searchData);
        $this->clickButtonAndConfirm('delete_rating', 'confirmation_for_delete');
    }

    /**
     * Verify Rating
     *
     * @param array|string $ratingData
     */
    public function verifyRatingData($ratingData)
    {
        if (is_string($ratingData)) {
            $ratingData = $this->loadData($ratingData);
        }
        $ratingData = $this->arrayEmptyClear($ratingData);
        $titles = (isset($ratingData['store_view_titles'])) ? $ratingData['store_view_titles'] : array();
        $this->verifyForm($ratingData);

        foreach ($titles as $value) {
            $this->addParameter('storeViewName', $value['store_view_name']);
            $this->verifyForm(array('store_view_title' => $value['store_view_title']));
        }
        $this->assertEmptyVerificationErrors();
    }
}