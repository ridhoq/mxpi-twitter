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
class Core_Mage_CmsPages_Helper extends Mage_Selenium_TestCase
{
    /**
     * Creates page
     *
     * @param string|array $pageData
     */
    public function createCmsPage($pageData)
    {
        if (is_string($pageData)) {
            $pageData = $this->loadData($pageData);
        }
        $pageData = $this->arrayEmptyClear($pageData);

        $pageInfo = (isset($pageData['page_information'])) ? $pageData['page_information'] : array();
        $content = (isset($pageData['content'])) ? $pageData['content'] : array();
        $design = (isset($pageData['design'])) ? $pageData['design'] : array();
        $metaData = (isset($pageData['meta_data'])) ? $pageData['meta_data'] : array();

        $this->clickButton('add_new_page');

        if ($pageInfo) {
            if (array_key_exists('store_view', $pageInfo) && !$this->controlIsPresent('multiselect', 'store_view')) {
                unset($pageInfo['store_view']);
            }
            $this->fillForm($pageInfo, 'page_information');
        }
        if ($content) {
            $this->fillContent($content);
        }
        if ($design) {
            $this->fillForm($design, 'design');
        }
        if ($metaData) {
            $this->fillForm($metaData, 'meta_data');
        }
        $this->saveForm('save_page');
    }

    /**
     * Fills Content tab
     *
     * @param string $content
     */
    public function fillContent(array $content)
    {
        $widgetsData = (isset($content['widgets'])) ? $content['widgets'] : array();
        $variableData = (isset($content['variable_data'])) ? $content['variable_data'] : array();

        $this->fillForm($content, 'content');
        foreach ($widgetsData as $widget) {
            $this->insertWidget($widget);
        }
        foreach ($variableData as $variable) {
            $this->insertVariable($variable);
        }
    }

    /**
     * Insert widget
     *
     * @param array $widgets
     */
    public function insertWidget(array $widgetData)
    {
        $chooseOption = (isset($widgetData['chosen_option'])) ? $widgetData['chosen_option'] : array();
        if ($this->controlIsPresent('link', 'wysiwyg_insert_widget')) {
            $this->clickControl('link', 'wysiwyg_insert_widget', false);
        } else {
            $this->clickButton('insert_widget', false);
        }
        $this->waitForAjax();
        $this->fillForm($widgetData);
        if ($chooseOption) {
            $this->selectOptionItem($chooseOption);
        }
        $this->clickButton('submit_widget_insert', false);
        $this->waitForAjax();
    }

    /**
     * Fills selections for widget
     *
     * @param array $optionData
     */
    public function selectOptionItem($optionData)
    {
        $buttonXpath = $this->_getControlXpath('button', 'select_option');
        if ($this->isElementPresent($buttonXpath)) {
            $name = trim(strtolower(preg_replace('#[^a-z]+#i', '_', $this->getText($buttonXpath))), '_');
            $this->click($buttonXpath);
            $this->waitForAjax();
            if (!$this->isElementPresent($this->_getControlXpath('fieldset', $name))) {
                $this->fail($name . ' fieldset is not loaded');
            }
        } else {
            $this->fail('Button \'select_option\' is not present on the page ' . $this->getCurrentPage());
        }

        $rowNames = array('Title', 'Product Name');
        $title = 'Not Selected';
        if (array_key_exists('category_path', $optionData)) {
            $this->addParameter('widgetParam', "//div[@id='widget-chooser_content']");
            $nodes = explode('/', $optionData['category_path']);
            $title = end($nodes);
            $this->categoryHelper()->selectCategory($optionData['category_path'], $name);
            $this->waitForAjax();
            unset($optionData['category_path']);
        }
        if (count($optionData) > 0) {
            $xpathTR = $this->search($optionData, $name);
            $this->assertNotEquals(null, $xpathTR, 'Element is not found');
            $names = $this->getTableHeadRowNames("//div[@id='widget-chooser_content']//table[@id]");
            foreach ($rowNames as $value) {
                if (in_array($value, $names)) {
                    $nameXpath = $xpathTR . '//td[' . (array_search($value, $names) + 1) . ']';
                    if ($title == 'Not Selected') {
                        $title = $this->getText($nameXpath);
                    } else {
                        $title = $title . ' / ' . $this->getText($nameXpath);
                    }
                    break;
                }
            }
            $this->click($xpathTR);
        }
        $this->checkChosenOption($title);
    }

    /**
     * Checks if the inserted item is correct
     *
     * @param string $option
     */
    public function checkChosenOption($option)
    {
        $this->addParameter('elementName', $option);
        $xpathOption = $this->_getControlXpath('pageelement', 'chosen_option_verify');
        if (!$this->isElementPresent($xpathOption)) {
            $this->fail('The element ' . $option . ' was not selected');
        }
    }

    /**
     * Inserts variable
     *
     * @param string $variable
     */
    public function insertVariable($variable)
    {
        if ($this->controlIsPresent('link', 'wysiwyg_insert_variable')) {
            $this->clickControl('link', 'wysiwyg_insert_variable', false);
        } else {
            $this->clickButton('insert_variable', false);
        }
        $this->waitForAjax();
        $this->addParameter('variableName', $variable);
        $this->clickControl('link', 'variable', false);
    }

    /**
     * Opens CMSpage
     *
     * @param array $searchPage
     */
    public function openCmsPage(array $searchPage)
    {
        $searchPage = $this->arrayEmptyClear($searchPage);
        if (array_key_exists('filter_store_viev', $searchPage)
                && !$this->controlIsPresent('dropdown', 'filter_store_viev')) {
            unset($searchPage['filter_store_viev']);
        }
        $xpathTR = $this->search($searchPage, 'cms_pages_grid');
        $this->assertNotEquals(null, $xpathTR, 'CMS Page is not found');
        $cellId = $this->getColumnIdByName('Title');
        $this->addParameter('pageName', $this->getText($xpathTR . '//td[' . $cellId . ']'));
        $this->addParameter('id', $this->defineIdFromTitle($xpathTR));
        $this->click($xpathTR);
        $this->waitForPageToLoad($this->_browserTimeoutPeriod);
        $this->validatePage();
    }

    /**
     * Deletes page
     *
     * @param array $searchPage
     */
    public function deleteCmsPage(array $searchPage)
    {
        $this->openCmsPage($searchPage);
        $this->clickButtonAndConfirm('delete_page', 'confirmation_for_delete');
    }

////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////@TODO
    /**
     * Validates page after creation
     *
     * @param array $pageData
     */
    public function frontValidatePage($pageData)
    {
        $this->logoutCustomer();
        $this->addParameter('url_key', $pageData['page_information']['url_key']);
        $this->addParameter('page_title', $pageData['page_information']['page_title']);
        if (array_key_exists('content', $pageData)) {
            if (array_key_exists('content_heading', $pageData['content'])) {
                $this->addParameter('content_heading', $pageData['content']['content_heading']);
            }
        }
        $this->frontend('test_page');
        foreach ($this->countElements($pageData) as $key => $value) {
            $xpath = $this->_getControlXpath('pageelement', $key);
            $this->assertTrue($this->getXpathCount($xpath) == $value, 'Count of ' . $key . ' is not ' . $value);
        }
    }

    /**
     * Count elements for validation
     *
     * @param array $pageData
     * @return array
     */
    public function countElements(array $pageData)
    {
        $map = array(
            'CMS Page Link' => 'widget_cms_link',
            'CMS Static Block' => 'widget_static_block',
            'Catalog Category Link' => 'widget_category_link',
            'Catalog Product Link' => 'widget_product_link'
        );
        $resultArray = array();
        foreach ($map as $key => $value) {
            $resultArray[$value] = count($this->searchArray($pageData, $key));
        }
        return $resultArray;
    }

    /**
     * Search array recursively
     *
     * @param array $pageData
     * @param string $key
     * @return array
     */
    function searchArray($pageData, $key = null)
    {
        $found = ($key !== null ? array_keys($pageData, $key) : array_keys($pageData));
        foreach ($pageData as $value) {
            if (is_array($value)) {
                $found = ($key !== null ? array_merge($found, $this->searchArray($value, $key)) : array_merge($found,
                                        $this->searchArray($value)));
            }
        }
        return $found;
    }
}