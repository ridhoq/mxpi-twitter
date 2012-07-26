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
class Core_Mage_Tags_Helper extends Mage_Selenium_TestCase
{
    /**
     * Converts string with tags to an array for verification
     *
     * @param string $tagName
     * @return array
     */
    protected function _convertTagsStringToArray($tagName)
    {
        $tags = array();
        $tagNameArray = array_filter(explode("\n", preg_replace("/(\'(.*?)\')|(\s+)/i", "$1\n", $tagName)), 'strlen');
        foreach ($tagNameArray as $key => $value) {
            $tags[$key] = trim($value, " \x22\x27");
            $tags[$key] = htmlspecialchars($tags[$key]);
        }
        return $tags;
    }

    /**
     * <p>Create Tag</p>
     *
     * @param string $tagsString Tags to add
     */
    public function frontendAddTag($tagsString)
    {
        $tagNameArray = $this->_convertTagsStringToArray($tagsString);
        $tagQty = count($tagNameArray);
        $this->addParameter('tagQty', $tagQty);
        $this->fillForm(array('input_new_tags' => $tagsString));
        $this->clickButton('add_tags');
    }

    /**
     * Delete tag
     *
     * @param string|array $tags
     */
    public function frontendDeleteTags($tags)
    {
        if (is_string($tags))
            $tags = $this->_convertTagsStringToArray($tags);
        foreach ($tags as $tag) {
            $this->addParameter('tagName', $tag);
            $this->clickControl('link', 'tag_name');
            $this->clickButtonAndConfirm('delete_tag', 'confirmation_for_delete', false);
            $this->waitForPageToLoad($this->_browserTimeoutPeriod);
            $this->addParameter('uenc', $this->defineParameterFromUrl('uenc'));
            $this->validatePage('my_account_my_tags_after_delete');
        }
    }

    /**
     * Verification tags on frontend
     *
     * @param string|array $tags
     * @param string $product
     */
    public function frontendTagVerification($tags, $product)
    {
        if (is_string($tags))
            $tags = $this->_convertTagsStringToArray($tags);
        //Verification in "My Recent tags" area
        $this->addParameter('productName', $product);
        foreach ($tags as $tag) {
            $this->navigate('customer_account');
            $this->addParameter('tagName', $tag);
            $this->assertTrue($this->controlIsPresent('link', 'tag'), "Cannot find tag with name: $tag");
            $this->clickControl('link', 'tag');
            $this->assertTrue($this->controlIsPresent('pageelement', 'tag_name_box'), "Cannot find tag $tag in My Tags");
            $this->assertTrue($this->controlIsPresent('link', 'product_name'),"Cannot find product $product tagged with $tag");
        }
        //Verification in "My Account -> My Tags"
        foreach ($tags as $tag) {
            $this->navigate('my_account_my_tags');
            $this->addParameter('tagName', $tag);
            $this->assertTrue($this->controlIsPresent('link', 'tag_name'), "Cannot find tag with name: $tag");
            $this->clickControl('link', 'tag_name');
            $this->assertTrue($this->controlIsPresent('pageelement', 'tag_name_box'), "Cannot find tag $tag in My Tags");
            $this->assertTrue($this->controlIsPresent('link', 'product_name'),"Cannot find product $product tagged with $tag");
        }
    }

    /**
     * Verification tags in category
     *
     * @param string|array $tags
     * @param string $product
     * @param string $category
     */
    public function frontendTagVerificationInCategory($tags, $product, $category)
    {
        if (is_string($tags))
            $tags = $this->_convertTagsStringToArray($tags);
        $category = substr($category, strpos($category, '/') + 1);
        $url = trim(strtolower(preg_replace('#[^0-9a-z]+#i', '-', $category)), '-');
        $this->addParameter('productName', $product);
        $this->addParameter('categoryTitle', $category);
        $this->addParameter('categoryUrl', $url);
        foreach ($tags as $tag) {
            $this->frontend('category_page_before_reindex');
            $this->addParameter('tagName', $tag);
            $this->assertTrue($this->controlIsPresent('link', 'tag_name'), "Cannot find tag with name: $tag");
            $this->clickControl('link', 'tag_name');
            $this->assertTrue($this->checkCurrentPage('tags_products'), $this->getParsedMessages());
            $this->assertTrue($this->controlIsPresent('link', 'product_name'));
        }
    }

    /* ----------------------------------- Backend ----------------------------------- */

    /**
     * Select store view on Create/Edit tag page
     *
     * @param string $storeViewName Name of the store
     */
    protected function selectStoreView($storeViewName)
    {
        $xpath = $this->_getControlXpath('dropdown', 'switch_store');
        $toSelect = $xpath . "//option[contains(.,'" . $storeViewName . "')]";
        $isSelected = $toSelect . '[@selected]';
        if (!$this->isElementPresent($isSelected)) {
            $storeId = $this->getAttribute($toSelect . '/@value');
            $this->addParameter('storeId', $storeId);
            $this->fillForm(array('switch_store' => $storeViewName));
            $this->waitForPageToLoad($this->_browserTimeoutPeriod);
            $this->validatePage();
        }
    }

    /**
     * Edits a tag in backend
     *
     * @param string|array $tagData
     */
    public function fillTagSettings($tagData)
    {
        if (is_string($tagData))
            $tagData = $this->loadData($tagData);
        $tagData = $this->arrayEmptyClear($tagData);
        // Select store view if available
        if (array_key_exists('switch_store', $tagData)) {
            if ($this->controlIsPresent('dropdown', 'switch_store')) {
                $this->selectStoreView($tagData['switch_store']);
            } else {
                unset($tagData['switch_store']);
            }
        }
        $prodTagAdmin = (isset($tagData['products_tagged_by_admins'])) ? $tagData['products_tagged_by_admins'] : null;
        // Fill general options
        $this->fillForm($tagData);
        if ($prodTagAdmin) {
            // Add tag name to parameters
            $tagName = $this->getValue($this->_getControlXpath('field', 'tag_name'));
            $this->addParameter('tagName', $tagName);
            //Fill additional options
            $this->clickButton('save_and_continue_edit');
            if (!$this->controlIsPresent('field', 'prod_tag_admin_name')) {
                $this->clickControl('link', 'prod_tag_admin_expand', false);
                $this->waitForAjax();
            }
            $this->searchAndChoose($prodTagAdmin, 'products_tagged_by_admins');
        }
    }

    /**
     * Adds a new tag in backend
     *
     * @param string|array $tagData
     */
    public function addTag($tagData)
    {
        $this->clickButton('add_new_tag');
        $this->fillTagSettings($tagData);
        $this->saveForm('save_tag');
    }

    /**
     * Opens a tag in backend
     *
     * @param string|array $searchData Data used in Search Grid for tags
     */
    public function openTag($searchData)
    {
        if (is_string($searchData))
            $searchData = $this->loadData($searchData);
        $searchData = $this->arrayEmptyClear($searchData);
        // Check if store views are available
        $key = 'filter_store_view';
        if (array_key_exists($key, $searchData) && !$this->controlIsPresent('dropdown', 'store_view')) {
            unset($searchData[$key]);
        }
        // Search and open
        $xpathTR = $this->search($searchData, 'tags_grid');
        $this->assertNotNull($xpathTR, 'Tag is not found');
        $cellId = $this->getColumnIdByName('Tag');
        $this->addParameter('tagName', $this->getText($xpathTR . '//td[' . $cellId . ']'));
        $this->addParameter('id', $this->defineIdFromTitle($xpathTR));
        $this->click($xpathTR . '//td[' . $cellId . ']');
        $this->waitForPageToLoad($this->_browserTimeoutPeriod);
        $this->validatePage();
    }

    /**
     * Mass action: changes tags status in backend
     *
     * @param array $tagsSearchData Array of tags to change status
     * @param string $newStatus New status, e.g. 'Approved'
     *
     * Example of $tagsSearchData for one tag with 'my tag name' name: array(array('tag_name' => 'my tag name'))
     */
    public function changeTagsStatus(array $tagsSearchData, $newStatus)
    {
        foreach ($tagsSearchData as $searchData) {
            $this->searchAndChoose($searchData);
        }
        $this->fillForm(array('tags_massaction' => 'Change status', 'tags_status' => $newStatus));
        $this->clickButton('submit');
    }

    /**
     * Deletes a tag from backend
     *
     * @param string|array $searchData Data used in Search Grid for tags. Same as data used for openTag
     */
    public function deleteTag($searchData)
    {
        $this->openTag($searchData);
        $this->clickButtonAndConfirm('delete_tag', 'confirmation_for_delete');
    }

    /**
     * Checks if the tag is assigned to the product.
     * Returns true if assigned, or False otherwise.
     *
     * @param array $tagSearchData Data used in Search Grid for tags. Same as used for openTag
     * @param array $productSearchData Product to open. Same as used in productHelper()->openProduct
     */
    public function verifyTagProduct(array $tagSearchData, array $productSearchData)
    {
        $this->productHelper()->openProduct($productSearchData);
        $this->openTab('product_tags');
        $xpathTR = $this->search($tagSearchData, 'product_tags');
        return $xpathTR ? true : false;
    }

    /**
     * Checks if the customer submmitted the tag.
     * Returns true if submitted, or False otherwise.
     *
     * @param array $tagSearchData Data used in Search Grid for tags. Same as data used for openTag
     * @param array $customerSearchData Search data to open customer. Same as in customerHelper()->openCustomer
     */
    public function verifyTagCustomer(array $tagSearchData, array $customerSearchData)
    {
        $tagSearchData = $this->arrayEmptyClear($tagSearchData);
        $this->customerHelper()->openCustomer($customerSearchData);
        $this->openTab('product_tags');
        $xpathTR = $this->formSearchXpath($tagSearchData);
        do {
            if ($this->isElementPresent($xpathTR))
                return true;
            if ($this->controlIsPresent('link', 'next_page')) {
                $this->clickControl('link', 'next_page', false);
                $this->pleaseWait();
            } else
                break;
        } while (true);

        return false;
    }
}