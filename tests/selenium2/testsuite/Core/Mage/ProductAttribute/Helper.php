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
class Core_Mage_ProductAttribute_Helper extends Mage_Selenium_TestCase
{
    /**
     * Action_helper method for Create Attribute
     *
     * Preconditions: 'Manage Attributes' page is opened.
     * @param array $attrData Array which contains DataSet for filling of the current form
     */
    public function createAttribute($attrData)
    {
        $this->clickButton('add_new_attribute');
        $this->fillForm($attrData, 'properties');
        $this->fillForm($attrData, 'manage_labels_options');
        $this->storeViewTitles($attrData);
        $this->attributeOptions($attrData);
        $this->saveForm('save_attribute');
    }

    /**
     * Open Product Attribute.
     *
     * Preconditions: 'Manage Attributes' page is opened.
     * @param array $searchData
     */
    public function openAttribute($searchData)
    {
        $this->_prepareDataForSearch($searchData);
        $xpathTR = $this->search($searchData, 'attributes_grid');
        $this->assertNotNull($xpathTR, 'Attribute is not found');
        $cellId = $this->getColumnIdByName('Attribute Code');
        $this->addParameter('attribute_code', $this->getText($xpathTR . '//td[' . $cellId . ']'));
        $this->addParameter('id', $this->defineIdFromTitle($xpathTR));
        $this->click($xpathTR . '//td[' . $cellId . ']');
        $this->waitForPageToLoad($this->_browserTimeoutPeriod);
        $this->validatePage();
    }

    /**
     * Verify all data in saved Attribute.
     *
     * Preconditions: Attribute page is opened.
     * @param array $attrData
     */
    public function verifyAttribute($attrData)
    {
        $this->assertTrue($this->verifyForm($attrData, 'properties'), $this->getParsedMessages());
        $this->openTab('manage_labels_options');
        $this->storeViewTitles($attrData, 'manage_titles', 'verify');
        $this->attributeOptions($attrData, 'verify');
    }

    /**
     * Create Attribute from product page.
     *
     * Preconditions: Product page is opened.
     * @param array $attrData
     */
    public function createAttributeOnGeneralTab($attrData)
    {
        // Defining and adding %fieldSetId% for Uimap pages.
        $id = explode('_', $this->getAttribute($this->_getControlXpath('fieldset', 'product_general') . '@id'));
        foreach ($id as $value) {
            if (is_numeric($value)) {
                $fieldSetId = $value;
                $this->addParameter('tabId', $fieldSetId);
                break;
            }
        }
        //Steps. Сlick 'Create New Attribute' button, select opened window.
        $this->clickButton('create_new_attribute', false);
        $names = $this->getAllWindowNames();
        $this->waitForPopUp(end($names), '30000');
        $this->selectWindow("name=" . end($names));
        $this->validatePage('new_product_attribute_from_product_page');
        $this->fillForm($attrData, 'properties');
        $this->fillForm($attrData, 'manage_labels_options');
        $this->storeViewTitles($attrData);
        $this->attributeOptions($attrData);
        $this->addParameter('attributeId', 0);
        $this->saveForm('save_attribute', false);
    }

    /**
     * Fill or Verify Titles for different Store View
     *
     * @param array $attrData
     * @param string $fieldsetName
     * @param string $action
     */
    public function storeViewTitles($attrData, $fieldsetName = 'manage_titles', $action = 'fill')
    {
        $name = 'store_view_titles';
        if (isset($attrData['admin_title'])) {
            $attrData[$name]['Admin'] = $attrData['admin_title'];
        }
        if (array_key_exists($name, $attrData)
            && is_array($attrData[$name])
            && $attrData[$name] != '%noValue%'
        ) {
            $fieldSetXpath = $this->_getControlXpath('fieldset', $fieldsetName);
            $qtyStore = $this->getXpathCount($fieldSetXpath . '//th');
            foreach ($attrData[$name] as $storeViewName => $storeViewValue) {
                $number = -1;
                for ($i = 1; $i <= $qtyStore; $i++) {
                    if ($this->getText($fieldSetXpath . '//th[' . $i . ']') == $storeViewName) {
                        $number = $i;
                        break;
                    }
                }
                if ($number != -1) {
                    $this->addParameter('storeViewNumber', $number);
                    $fieldSet = $this->_findUimapElement('fieldset', $fieldsetName);
                    $fieldXpath = $this->_getControlXpath('field', 'titles_by_store_name', $fieldSet);
                    switch ($action) {
                        case 'fill':
                            if ($storeViewValue != '%noValue%') {
                                $this->type($fieldXpath, $storeViewValue);
                            }
                            break;
                        case 'verify':
                            $actualText = $this->getValue($fieldXpath);
                            $var = array_flip(get_html_translation_table());
                            $actualText = strtr($actualText, $var);
                            $this->assertEquals($storeViewValue, $actualText, 'Stored data not equals to specified');
                            break;
                    }
                } else {
                    $this->fail('Cannot find specified Store View with name \'' . $storeViewName . '\'');
                }
            }
        }
    }

    /**
     * Fill or Verify Options for Dropdown and Multiple Select Attributes
     *
     * @param array $attrData
     * @param string $action
     */
    public function attributeOptions($attrData, $action = 'fill')
    {
        $fieldSetXpath = $this->_getControlXpath('fieldset', 'manage_options');

        if ($action == 'verify') {
            $option = $this->getXpathCount($fieldSetXpath . "//tr[contains(@class,'option-row')]");
            $num = 1;
        }

        foreach ($attrData as $fKey => $dValue) {
            if (preg_match('/^option_/', $fKey) and is_array($attrData[$fKey])) {
                if ($this->isElementPresent($fieldSetXpath)) {
                    $optionCount = $this->getXpathCount($fieldSetXpath . "//tr[contains(@class,'option-row')]");

                    switch ($action) {
                        case 'fill':
                            $this->addParameter('fieldOptionNumber', $optionCount);
                            $this->clickButton('add_option', false);
                            $this->storeViewTitles($attrData[$fKey], 'manage_options');
                            $this->fillForm($attrData[$fKey], 'manage_labels_options');
                            break;
                        case 'verify':
                            if ($option > 0) {
                                $fieldOptionNumber = $this->getAttribute($fieldSetXpath
                                        . "//tr[contains(@class,'option-row')][" . $num
                                        . "]//input[@class='input-radio']/@value");
                                $this->addParameter('fieldOptionNumber', $fieldOptionNumber);
                                $this->assertTrue($this->verifyForm($attrData[$fKey], 'manage_labels_options'),
                                        $this->getParsedMessages());
                                $this->storeViewTitles($attrData[$fKey], 'manage_options', 'verify');
                                $num++;
                                $option--;
                            }
                            break;
                    }
                }
            }
        }
    }

    /**
     * Define Attribute Id
     *
     * @param array $searchData
     * @return int
     */
    public function defineAttributeId(array $searchData)
    {
        $this->navigate('manage_attributes');
        $searchData = $this->arrayEmptyClear($searchData);
        $attrXpath = $this->search($searchData);
        $this->assertNotEquals(null, $attrXpath);

        return $this->defineIdFromTitle($attrXpath);
    }
}