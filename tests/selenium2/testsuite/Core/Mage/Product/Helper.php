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
 * Helper class
 *
 * @package     selenium
 * @subpackage  tests
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class Core_Mage_Product_Helper extends Mage_Selenium_TestCase
{
    public static $arrayToReturn = array();

    /**
     * Fill in Product Settings tab
     *
     * @param array $productData
     * @param string $productType Value - simple|virtual|bundle|configurable|downloadable|grouped
     */
    public function fillProductSettings($productData, $productType = 'simple')
    {
        $attributeSet = (isset($productData['product_attribute_set']))
            ? $productData['product_attribute_set']
            : null;

        $attributeSetXpath = $this->_getControlXpath('dropdown', 'product_attribute_set');
        $productTypeXpath = $this->_getControlXpath('dropdown', 'product_type');

        if (!empty($attributeSet)) {
            $this->select($attributeSetXpath, 'label=' . $attributeSet);
            $attributeSetID = $this->getValue($attributeSetXpath . '/option[text()=\'' . $attributeSet . '\']');
        } else {
            $attributeSetID = $this->getValue($attributeSetXpath . "/option[@selected='selected']");
        }
        $this->select($productTypeXpath, 'value=' . $productType);

        $this->addParameter('setId', $attributeSetID);
        $this->addParameter('productType', $productType);

        $this->clickButton('continue');
    }

    /**
     * Select Dropdown Attribute(s) for configurable product creation
     *
     * @param array $productData
     */
    public function fillConfigurableSettings(array $productData)
    {
        $attributes = (isset($productData['configurable_attribute_title']))
            ? explode(',', $productData['configurable_attribute_title'])
            : null;

        if (!empty($attributes)) {
            $attributesId = array();
            $attributes = array_map('trim', $attributes);

            foreach ($attributes as $attributeTitle) {
                $this->addParameter('attributeTitle', $attributeTitle);
                $xpath = $this->_getControlXpath('checkbox', 'configurable_attribute_title');
                if ($this->isElementPresent($xpath)) {
                    $attributesId[] = $this->getAttribute($xpath . '/@value');
                    $this->click($xpath);
                } else {
                    $this->fail("Dropdown attribute with title '$attributeTitle' is not present on the page");
                }
            }

            $attributesUrl = urlencode(base64_encode(implode(',', $attributesId)));
            $this->addParameter('attributesUrl', $attributesUrl);

            $this->clickButton('continue');
        } else {
            $this->fail('Dropdown attribute for configurable product creation is not set');
        }
    }

    /**
     * Fill Product Tab
     *
     * @param array $productData
     * @param string $tabName Value - general|prices|meta_information|images|recurring_profile
     * |design|gift_options|inventory|websites|categories|related|up_sells
     * |cross_sells|custom_options|bundle_items|associated|downloadable_information
     *
     * @return bool
     */
    public function fillTab(array $productData, $tabName = 'general')
    {
        $tabData = array();
        $needFilling = false;

        foreach ($productData as $key => $value) {
            if (preg_match('/^' . $tabName . '/', $key)) {
                $tabData[$key] = $value;
            }
        }

        if ($tabData) {
            $needFilling = true;
        }

        $tabXpath = $this->_getControlXpath('tab', $tabName);
        if ($tabName == 'websites' && !$this->isElementPresent($tabXpath)) {
            $needFilling = false;
        }

        if (!$needFilling) {
            return true;
        }

        $this->openTab($tabName);

        switch ($tabName) {
            case 'prices':
                $arrayKey = 'prices_tier_price_data';
                if (array_key_exists($arrayKey, $tabData) && is_array($tabData[$arrayKey])) {
                    foreach ($tabData[$arrayKey] as $value) {
                        $this->addTierPrice($value);
                    }
                }
                $this->fillForm($tabData, 'prices');
                $this->fillUserAttributesOnTab($tabData, $tabName);
                break;
            case 'websites':
                $websites = explode(',', $tabData[$tabName]);
                $websites = array_map('trim', $websites);
                foreach ($websites as $value) {
                    $this->selectWebsite($value);
                }
                break;
            case 'categories':
                $categories = explode(',', $tabData[$tabName]);
                $categories = array_map('trim', $categories);
                foreach ($categories as $value) {
                    $this->categoryHelper()->selectCategory($value);
                }
                break;
            case 'related':
            case 'up_sells':
            case 'cross_sells':
                $arrayKey = $tabName . '_data';
                if (array_key_exists($arrayKey, $tabData) && is_array($tabData[$arrayKey])) {
                    foreach ($tabData[$arrayKey] as $value) {
                        $this->assignProduct($value, $tabName);
                    }
                }
                break;
            case 'custom_options':
                $arrayKey = $tabName . '_data';
                if (array_key_exists($arrayKey, $tabData) && is_array($tabData[$arrayKey])) {
                    foreach ($tabData[$arrayKey] as $value) {
                        $this->addCustomOption($value);
                    }
                }
                break;
            case 'bundle_items':
                $arrayKey = $tabName . '_data';
                if (array_key_exists($arrayKey, $tabData) && is_array($tabData[$arrayKey])) {
                    if (array_key_exists('ship_bundle_items', $tabData[$arrayKey])) {
                        $array['ship_bundle_items'] = $tabData[$arrayKey]['ship_bundle_items'];
                        $this->fillForm($array, 'bundle_items');
                    }
                    foreach ($tabData[$arrayKey] as $value) {
                        if (is_array($value)) {
                            $this->addBundleOption($value);
                        }
                    }
                }
                break;
            case 'associated':
                $arrayKey = $tabName . '_grouped_data';
                $arrayKey1 = $tabName . '_configurable_data';
                if (array_key_exists($arrayKey, $tabData) && is_array($tabData[$arrayKey])) {
                    foreach ($tabData[$arrayKey] as $value) {
                        $this->assignProduct($value, $tabName);
                    }
                } elseif (array_key_exists($arrayKey1, $tabData) && is_array($tabData[$arrayKey1])) {
                    $attributeTitle = (isset($productData['configurable_attribute_title']))
                        ? $productData['configurable_attribute_title']
                        : null;
                    if (!$attributeTitle) {
                        $this->fail('Attribute Title for configurable product is not set');
                    }
                    $this->addParameter('attributeTitle', $attributeTitle);
                    $this->fillForm($tabData[$arrayKey1], $tabName);
                    foreach ($tabData[$arrayKey1] as $value) {
                        if (is_array($value)) {
                            $this->assignProduct($value, $tabName, $attributeTitle);
                        }
                    }
                }
                break;
            case 'downloadable_information':
                $arrayKey = $tabName . '_data';
                if (array_key_exists($arrayKey, $tabData) && is_array($tabData[$arrayKey])) {
                    foreach ($tabData[$arrayKey] as $key => $value) {
                        if (preg_match('/^downloadable_sample_/', $key) && is_array($value)) {
                            $this->addDownloadableOption($value, 'sample');
                        }
                        if (preg_match('/^downloadable_link_/', $key) && is_array($value)) {
                            $this->addDownloadableOption($value, 'link');
                        }
                    }
                }
                $this->fillForm($tabData[$arrayKey], $tabName);
                break;
            default:
                $this->fillForm($tabData, $tabName);
                $this->fillUserAttributesOnTab($tabData, $tabName);
                break;
        }
        return true;
    }

    /**
     * Add Tier Price
     *
     * @param array $tierPriceData
     */
    public function addTierPrice(array $tierPriceData)
    {
        $rowNumber = $this->getXpathCount($this->_getControlXpath('fieldset', 'tier_price_row'));
        $this->addParameter('tierPriceId', $rowNumber);
        $this->clickButton('add_tier_price', false);
        $this->fillForm($tierPriceData, 'prices');
    }

    /**
     * Add Custom Option
     *
     * @param array $customOptionData
     */
    public function addCustomOption(array $customOptionData)
    {
        $fieldSetXpath = $this->_getControlXpath('fieldset', 'custom_option_set');
        $optionId = $this->getXpathCount($fieldSetXpath) + 1;
        $this->addParameter('optionId', $optionId);
        $this->clickButton('add_option', false);
        $this->fillForm($customOptionData, 'custom_options');
        foreach ($customOptionData as $rowKey => $rowValue) {
            if (preg_match('/^custom_option_row/', $rowKey) && is_array($rowValue)) {
                $rowId = $this->getXpathCount($fieldSetXpath . "//tr[contains(@id,'product_option_')][not(@style)]");
                $this->addParameter('rowId', $rowId);
                $this->clickButton('add_row', false);
                $this->fillForm($rowValue, 'custom_options');
            }
        }
    }

    /**
     * Select Website by Website name
     *
     * @param $websiteName
     * @param $action
     */
    public function selectWebsite($websiteName, $action = 'select')
    {
        $this->addParameter('websiteName', $websiteName);
        $websiteXpath = $this->_getControlXpath('checkbox', 'websites');
        if ($this->isElementPresent($websiteXpath)) {
            if ($this->getValue($websiteXpath) == 'off') {
                switch ($action) {
                    case 'select':
                        $this->click($websiteXpath);
                        break;
                    case 'verify':
                        $this->addVerificationMessage('Website with name "' . $websiteName . '" is not selected');
                        break;
                }
            }
        } else {
            $this->fail('Website with name "' . $websiteName . '" does not exist');
        }
    }

    /**
     * Assign product. Use for fill in 'Related Products', 'Up-sells' or 'Cross-sells' tabs
     *
     * @param array $data
     * @param string $tabName
     * @param string $attributeTitle
     */
    public function assignProduct(array $data, $tabName, $attributeTitle = null)
    {
        $fillingData = array();

        foreach ($data as $key => $value) {
            if (!preg_match('/^' . $tabName . '_search_/', $key)) {
                $fillingData[$key] = $value;
                unset($data[$key]);
            }
        }

        if ($attributeTitle) {
            $attributeCode = $this->getAttribute("//a[span[text()='$attributeTitle']]/@name");
            $this->addParameter('attributeCode', $attributeCode);
            $this->addParameter('attributeTitle', $attributeTitle);
        }
        $this->searchAndChoose($data, $tabName);
        //Fill in additional data
        if ($fillingData) {
            $xpathTR = $this->formSearchXpath($data);
            if ($attributeTitle) {
                $xpath = $this->_getControlXpath('fieldset', 'associated') . '//table[@id]';
                $number = $this->getColumnIdByName($attributeTitle, $xpath);
                $setXpath = $this->_getControlXpath('fieldset', 'associated');
                $attributeValue = $this->getText($setXpath . $xpathTR . "//td[$number]");
                $this->addParameter('attributeValue', $attributeValue);
            } else {
                $this->addParameter('productXpath', $xpathTR);
            }
            $this->fillForm($fillingData, $tabName);
        }
    }

    /**
     * Add Bundle Option
     *
     * @param array $bundleOptionData
     */
    public function addBundleOption(array $bundleOptionData)
    {
        $fieldSetXpath = $this->_getControlXpath('fieldset', 'bundle_items');
        $optionsCount = $this->getXpathCount($fieldSetXpath . "//div[@class='option-box']");
        $this->addParameter('optionId', $optionsCount);
        $this->clickButton('add_new_option', false);
        $this->fillForm($bundleOptionData, 'bundle_items');
        foreach ($bundleOptionData as $value) {
            $productSearch = array();
            $selectionSettings = array();
            if (is_array($value)) {
                foreach ($value as $k => $v) {
                    if ($k == 'bundle_items_search_name' or $k == 'bundle_items_search_sku') {
                        $this->addParameter('productSku', $v);
                    }
                    if (preg_match('/^bundle_items_search_/', $k)) {
                        $productSearch[$k] = $v;
                    } elseif ($k == 'bundle_items_qty_to_add') {
                        $selectionSettings['selection_item_default_qty'] = $v;
                    } elseif (preg_match('/^selection_item_/', $k)) {
                        $selectionSettings[$k] = $v;
                    }
                }
                if ($productSearch) {
                    $this->clickButton('add_selection', false);
                    $this->pleaseWait();
                    $this->searchAndChoose($productSearch, 'select_product_to_bundle_option');
                    $this->clickButton('add_selected_products', false);
                    if ($selectionSettings) {
                        $this->fillForm($selectionSettings);
                    }
                }
            }
        }
    }

    /**
     * Add Sample for Downloadable product
     *
     * @param array $optionData
     * @param string $type
     */
    public function addDownloadableOption(array $optionData, $type)
    {
        $fieldSet = $this->_getControlXpath('link', 'downloadable_' . $type);
        if (!$this->isElementPresent($fieldSet . "/parent::*[normalize-space(@class)='open']")) {
            $this->clickControl('link', 'downloadable_' . $type, false);
        }

        $fieldSetXpath = $this->_getControlXpath('fieldset', 'downloadable_' . $type);
        $rowNumber = $this->getXpathCount($fieldSetXpath . "//*[@id='" . $type . "_items_body']/tr");
        $this->addParameter('rowId', $rowNumber);
        $this->clickButton('downloadable_' . $type . '_add_new_row', false);
        $this->fillForm($optionData, 'downloadable_information');
    }

    /**
     * Fill user product attribute
     *
     * @param array $productData
     * @param string $tabName
     */
    public function fillUserAttributesOnTab(array $productData, $tabName)
    {
        $userFieldData = $tabName . '_user_attr';
        if (array_key_exists($userFieldData, $productData) && is_array($productData[$userFieldData])) {
            foreach ($productData[$userFieldData] as $fieldType => $dataArray) {
                if (is_array($dataArray)) {
                    foreach ($dataArray as $fieldKey => $fieldValue) {
                        $this->addParameter('attibuteCode' . ucfirst(strtolower($fieldType)), $fieldKey);
                        $xpath = $this->_getControlXpath($fieldType, $tabName . '_user_attr_' . $fieldType);
                        switch ($fieldType) {
                            case 'dropdown':
                                $this->select($xpath, $fieldValue);
                                break;
                            case 'field':
                                $this->type($xpath, $fieldValue);
                                break;
                            case 'multiselect':
                                $this->removeAllSelections($xpath);
                                $values = explode(',', $fieldValue);
                                $values = array_map('trim', $values);
                                foreach ($values as $v) {
                                    $this->addSelection($xpath, $v);
                                }
                                break;
                        }
                    }
                }
            }
        }
    }

    /**
     * Create Product
     *
     * @param array $productData
     * @param string $productType
     */
    public function createProduct(array $productData, $productType = 'simple')
    {
        $productData = $this->arrayEmptyClear($productData);
        $this->clickButton('add_new_product');
        $this->fillProductSettings($productData, $productType);
        if ($productType == 'configurable') {
            $this->fillConfigurableSettings($productData);
        }
        $this->fillProductInfo($productData, $productType);
        $this->saveForm('save');
    }

    /**
     * Fill Product info
     *
     * @param array $productData
     * @param string $productType
     */
    public function fillProductInfo(array $productData, $productType = 'simple')
    {
        $this->fillTab($productData);
        $this->fillTab($productData, 'prices');
        $this->fillTab($productData, 'meta_information');
        //@TODO Fill in Images Tab
        if ($productType == 'simple' || $productType == 'virtual') {
            $this->fillTab($productData, 'recurring_profile');
        }
        $this->fillTab($productData, 'design');
        $this->fillTab($productData, 'gift_options');
        $this->fillTab($productData, 'inventory');
        $this->fillTab($productData, 'websites');
        $this->fillTab($productData, 'categories');
        $this->fillTab($productData, 'related');
        $this->fillTab($productData, 'up_sells');
        $this->fillTab($productData, 'cross_sells');
        $this->fillTab($productData, 'custom_options');
        if ($productType == 'grouped' || $productType == 'configurable') {
            $this->fillTab($productData, 'associated');
        }
        if ($productType == 'bundle') {
            $this->fillTab($productData, 'bundle_items');
        }
        if ($productType == 'downloadable') {
            $this->fillTab($productData, 'downloadable_information');
        }
    }

    /**
     * Open product.
     *
     * @param array $productSearch
     */
    public function openProduct(array $productSearch)
    {
        $this->_prepareDataForSearch($productSearch);
        $xpathTR = $this->search($productSearch, 'product_grid');
        $this->assertNotNull($xpathTR, 'Product is not found');
        $cellId = $this->getColumnIdByName('Name');
        $this->addParameter('productName', $this->getText($xpathTR . '//td[' . $cellId . ']'));
        $this->addParameter('id', $this->defineIdFromTitle($xpathTR));
        $this->click($xpathTR . "//a[text()='Edit']");
        $this->waitForPageToLoad($this->_browserTimeoutPeriod);
        $this->validatePage();
    }

    /**
     * Verify product info
     *
     * @param array $productData
     * @param array $skipElements
     */
    public function verifyProductInfo(array $productData, $skipElements = array())
    {
        $productData = $this->arrayEmptyClear($productData);
        $nestedArrays = array();
        foreach ($productData as $key => $value) {
            if (is_array($value)) {
                $nestedArrays[$key] = $value;
                unset($productData[$key]);
            }
            if ($key == 'websites' or $key == 'categories') {
                $nestedArrays[$key] = $value;
                unset($productData[$key]);
            }
        }
        $this->verifyForm($productData, null, $skipElements);
        // Verify tier prices
        if (array_key_exists('prices_tier_price_data', $nestedArrays)) {
            $this->verifyTierPrices($nestedArrays['prices_tier_price_data']);
        }
        //Verify selected websites
        if (array_key_exists('websites', $nestedArrays)) {
            $tabXpath = $this->_getControlXpath('tab', 'websites');
            if ($this->isElementPresent($tabXpath)) {
                $this->openTab('websites');
                $websites = explode(',', $nestedArrays['websites']);
                $websites = array_map('trim', $websites);
                foreach ($websites as $value) {
                    $this->selectWebsite($value, 'verify');
                }
            }
        }
        //Verify selected categories
        if (array_key_exists('categories', $nestedArrays)) {
            $categories = explode(',', $nestedArrays['categories']);
            $categories = array_map('trim', $categories);
            $this->openTab('categories');
            foreach ($categories as $value) {
                $this->isSelectedCategory($value);
            }
        }
        //Verify assigned products for 'Related Products', 'Up-sells', 'Cross-sells' tabs
        if (array_key_exists('related_data', $nestedArrays)) {
            $this->openTab('related');
            foreach ($nestedArrays['related_data'] as $value) {
                $this->isAssignedProduct($value, 'related');
            }
        }
        if (array_key_exists('up_sells_data', $nestedArrays)) {
            $this->openTab('up_sells');
            foreach ($nestedArrays['up_sells_data'] as $value) {
                $this->isAssignedProduct($value, 'up_sells');
            }
        }
        if (array_key_exists('cross_sells_data', $nestedArrays)) {
            $this->openTab('cross_sells');
            foreach ($nestedArrays['cross_sells_data'] as $value) {
                $this->isAssignedProduct($value, 'cross_sells');
            }
        }
        // Verify Associated Products tab
        if (array_key_exists('associated_grouped_data', $nestedArrays)) {
            $this->openTab('associated');
            foreach ($nestedArrays['associated_grouped_data'] as $value) {
                $this->isAssignedProduct($value, 'associated');
            }
        }
        if (array_key_exists('associated_configurable_data', $nestedArrays)) {
            $this->openTab('associated');
            $attributeTitle = (isset($productData['configurable_attribute_title']))
                ? $productData['configurable_attribute_title']
                : null;
            if (!$attributeTitle) {
                $this->fail('Attribute Title for configurable product is not set');
            }
            $this->addParameter('attributeTitle', $attributeTitle);
            $this->verifyForm($nestedArrays['associated_configurable_data'], 'associated');
            foreach ($nestedArrays['associated_configurable_data'] as $value) {
                if (is_array($value)) {
                    $this->isAssignedProduct($value, 'associated', $attributeTitle);
                }
            }
        }
        if (array_key_exists('custom_options_data', $nestedArrays)) {
            $this->verifyCustomOption($nestedArrays['custom_options_data']);
        }
        if (array_key_exists('bundle_items_data', $nestedArrays)) {
            $this->verifyBundleOptions($nestedArrays['bundle_items_data']);
        }
        if (array_key_exists('downloadable_information_data', $nestedArrays)) {
            $samples = array();
            $links = array();
            foreach ($nestedArrays['downloadable_information_data'] as $key => $value) {
                if (preg_match('/^downloadable_sample_/', $key) && is_array($value)) {
                    $samples[$key] = $value;
                }
                if (preg_match('/^downloadable_link_/', $key) && is_array($value)) {
                    $links[$key] = $value;
                }
            }
            if ($samples) {
                $this->verifyDownloadableOptions($samples, 'sample');
            }
            if ($links) {
                $this->verifyDownloadableOptions($links, 'link');
            }
            $this->verifyForm($nestedArrays['downloadable_information_data'], 'downloadable_information');
        }
        // Error Output
        $this->assertEmptyVerificationErrors();
    }

    /**
     * Verify Tier Prices
     *
     * @param array $tierPriceData
     *
     * @return boolean
     */
    public function verifyTierPrices(array $tierPriceData)
    {
        $rowQty = $this->getXpathCount($this->_getControlXpath('fieldset', 'tier_price_row'));
        $needCount = count($tierPriceData);
        if ($needCount != $rowQty) {
            $this->addVerificationMessage('Product must be contains ' . $needCount
                                              . 'Tier Price(s), but contains ' . $rowQty);
            return false;
        }
        $i = 0;
        foreach ($tierPriceData as $value) {
            $this->addParameter('tierPriceId', $i);
            $this->verifyForm($value, 'prices');
            $i++;
        }
        return true;
    }

    /**
     * Verify that category is selected
     *
     * @param string $categoryPath
     */
    public function isSelectedCategory($categoryPath)
    {
        $nodes = explode('/', $categoryPath);
        $rootCat = array_shift($nodes);

        $correctRoot = $this->categoryHelper()->defineCorrectCategory($rootCat);

        foreach ($nodes as $value) {
            $correctSubCat = array();

            for ($i = 0; $i < count($correctRoot); $i++) {
                $correctSubCat = array_merge($correctSubCat,
                                             $this->categoryHelper()->defineCorrectCategory($value, $correctRoot[$i]));
            }
            $correctRoot = $correctSubCat;
        }

        if ($correctRoot) {
            $catXpath = '//*[@id=\'' . array_shift($correctRoot) . '\']/parent::*/input';
            if ($this->getValue($catXpath) == 'off') {
                $this->addVerificationMessage('Category with path: "' . $categoryPath . '" is not selected');
            }
        } else {
            $this->fail("Category with path='$categoryPath' not found");
        }
    }

    /**
     * Verify that product is assigned
     *
     * @param array $data
     * @param string $fieldSetName
     * @param string $attributeTitle
     */
    public function isAssignedProduct(array $data, $fieldSetName, $attributeTitle = null)
    {
        $fillingData = array();

        foreach ($data as $key => $value) {
            if (!preg_match('/^' . $fieldSetName . '_search_/', $key)) {
                $fillingData[$key] = $value;
                unset($data[$key]);
            }
        }

        if ($attributeTitle) {
            $attributeCode = $this->getAttribute("//a[span[text()='$attributeTitle']]/@name");
            $this->addParameter('attributeCode', $attributeCode);
            $this->addParameter('attributeTitle', $attributeTitle);
        }
        $xpathTR = $this->formSearchXpath($data);
        $fieldSetXpath = $this->_getControlXpath('fieldset', $fieldSetName);

        if (!$this->isElementPresent($fieldSetXpath . $xpathTR)) {
            $this->addVerificationMessage($fieldSetName . " tab: Product is not assigned with data: \n"
                                              . print_r($data, true));
        } else {
            if ($fillingData) {
                if ($attributeTitle) {
                    $xpath = $this->_getControlXpath('fieldset', 'associated') . '//table[@id]';
                    $number = $this->getColumnIdByName($attributeTitle, $xpath);
                    $attributeValue = $this->getText($xpathTR . "//td[$number]");
                    $this->addParameter('attributeValue', $attributeValue);
                } else {
                    $this->addParameter('productXpath', $xpathTR);
                }
                $this->verifyForm($fillingData, $fieldSetName);
            }
        }
    }

    /**
     * Verify Custom Options
     *
     * @param array $customOptionData
     *
     * @return boolean
     */
    public function verifyCustomOption(array $customOptionData)
    {
        $this->openTab('custom_options');
        $fieldSetXpath = $this->_getControlXpath('fieldset', 'custom_option_set');
        $optionsQty = $this->getXpathCount($fieldSetXpath);
        $needCount = count($customOptionData);
        if ($needCount != $optionsQty) {
            $this->addVerificationMessage('Product must be contains ' . $needCount
                                              . ' Custom Option(s), but contains ' . $optionsQty);
            return false;
        }
        $id = $this->getAttribute($fieldSetXpath . "[1]/@id");
        $id = explode('_', $id);
        foreach ($id as $value) {
            if (is_numeric($value)) {
                $optionId = $value;
            }
        }
        // @TODO Need implement full verification for custom options with type = select (not tested rows)
        foreach ($customOptionData as $value) {
            if (is_array($value)) {
                $this->addParameter('optionId', $optionId);
                $this->verifyForm($value, 'custom_options');
                $optionId--;
            }
        }
        return true;
    }

    /**
     * verify Bundle Options
     *
     * @param array $bundleData
     *
     * @return boolean
     */
    public function verifyBundleOptions(array $bundleData)
    {
        $this->openTab('bundle_items');
        $fieldSetXpath = $this->_getControlXpath('fieldset', 'bundle_items');
        $optionSet = $fieldSetXpath . "//div[@class='option-box']";
        $optionsCount = $this->getXpathCount($optionSet);
        $needCount = count($bundleData);
        if (array_key_exists('ship_bundle_items', $bundleData)) {
            $needCount = $needCount - 1;
        }
        if ($needCount != $optionsCount) {
            $this->addVerificationMessage('Product must be contains ' . $needCount
                                              . 'Bundle Item(s), but contains ' . $optionsCount);
            return false;
        }

        $i = 0;
        foreach ($bundleData as $option => $values) {
            if (is_string($values)) {
                $this->verifyForm(array($option => $values), 'bundle_items');
            }
            if (is_array($values)) {
                $this->addParameter('optionId', $i);
                $this->verifyForm($values, 'bundle_items');
                foreach ($values as $k => $v) {
                    if (preg_match('/^add_product_/', $k) && is_array($v)) {
                        $selectionSettings = array();
                        foreach ($v as $field => $data) {
                            if ($field == 'bundle_items_search_name' or $field == 'bundle_items_search_sku') {
                                $productSku = $data;
                            }
                            if (!preg_match('/^bundle_items_search/', $field)) {
                                if ($field == 'bundle_items_qty_to_add') {
                                    $selectionSettings['selection_item_default_qty'] = $data;
                                } else {
                                    $selectionSettings[$field] = $data;
                                }
                            }
                        }
                        $k = $i + 1;
                        if (!$this->isElementPresent($optionSet . "[$k]"
                                                         . "//tr[@class='selection' and contains(.,'$productSku')]")
                        ) {
                            $this->addVerificationMessage("Product with sku(name)'" . $productSku
                                                              . "' is not assigned to bundle item $i");
                        } else {
                            if ($selectionSettings) {
                                $this->addParameter('productSku', $productSku);
                                $this->verifyForm($selectionSettings, 'bundle_items');
                            }
                        }
                    }
                }
                $i++;
            }
        }
        return true;
    }

    /**
     * Verify Downloadable Options
     *
     * @param array $optionsData
     * @param string $type
     *
     * @return bool
     */
    public function verifyDownloadableOptions(array $optionsData, $type)
    {
        $fieldSetXpath = $this->_getControlXpath('fieldset', 'downloadable_' . $type);
        $rowQty = $this->getXpathCount($fieldSetXpath . "//*[@id='" . $type . "_items_body']/tr");
        $needCount = count($optionsData);
        if ($needCount != $rowQty) {
            $this->addVerificationMessage('Product must be contains ' . $needCount
                                              . ' Downloadable ' . $type . '(s), but contains ' . $rowQty);
            return false;
        }
        $i = 0;
        foreach ($optionsData as $value) {
            $this->addParameter('rowId', $i);
            $this->verifyForm($value, 'downloadable_information');
            $i++;
        }
        return true;
    }

    #*******************************************
    #*         Frontend Helper Methods         *
    #*******************************************

    /**
     * Open product on FrontEnd
     *
     * @param string $productName
     * @param $categoryPath
     */
    public function frontOpenProduct($productName, $categoryPath = null)
    {
        if (!is_string($productName)) {
            $this->fail('Wrong data to open a product');
        }
        $productUrl = trim(strtolower(preg_replace('#[^0-9a-z]+#i', '-', $productName)), '-');
        $this->addParameter('productUrl', $productUrl);

        if ($categoryPath) {
            $nodes = explode('/', $categoryPath);
            if (count($nodes) > 1) {
                array_shift($nodes);
            }
            $nodes = array_reverse($nodes);
            $categoryName = '';
            foreach ($nodes as $value) {
                $categoryName = $categoryName . ' - ' . trim($value);
            }
            $this->addParameter('productTitle', $productName . $categoryName);
        } else {
            $this->addParameter('productTitle', $productName);
        }
        $this->frontend('product_page');
        $this->addParameter('productName', $productName);
        $openedProductName = $this->getText($this->_getControlXpath('pageelement', 'product_name'));
        $this->assertEquals($productName, $openedProductName,
                            "Product with name '$openedProductName' is opened, but should be '$productName'");
    }

    /**
     * Add product to shopping cart
     *
     * @param array|null $dataForBuy
     */
    public function frontAddProductToCart($dataForBuy = null)
    {
        if ($dataForBuy) {
            $this->frontFillBuyInfo($dataForBuy);
        }
        $xpathName = $this->getCurrentUimapPage()->getMainForm()->findPageelement('product_name');
        $openedProductName = $this->getText($xpathName);
        $this->addParameter('productName', $openedProductName);
        $this->clickButton('add_to_cart');
    }

    /**
     * Choose custom options and additional products
     *
     * @param array $dataForBuy
     */
    public function frontFillBuyInfo($dataForBuy)
    {
//        For EE edition
//        $customize = $this->controlIsPresent('button', 'customize_and_add_to_cart');
//        if ($customize) {
//            $this->clickButton('customize_and_add_to_cart', false);
//            $this->pleaseWait();
//        }
        foreach ($dataForBuy as $value) {
            $fill = (isset($value['options_to_choose'])) ? $value['options_to_choose'] : array();
            $params = (isset($value['parameters'])) ? $value['parameters'] : array();
            foreach ($params as $key => $value) {
                $this->addParameter($key, $value);
            }
            $this->fillForm($fill);
        }
    }

    /**
     * Verify product info on frontend
     *
     * @param array $productData
     */
    public function frontVerifyProductInfo(array $productData)
    {
        $productData = $this->arrayEmptyClear($productData);
        $this->frontOpenProduct($productData['general_name']);
        $xpathArray = $this->getCustomOptionsXpathes($productData);
        foreach ($xpathArray as $fieldName => $data) {
            if (is_string($data)) {
                if (!$this->isElementPresent($data)) {
                    $this->addVerificationMessage('Could not find element ' . $fieldName);
                }
            } else {
                foreach ($data as $optionData) {
                    foreach ($optionData as $x => $y) {
                        if (!preg_match('/xpath/', $x)) {
                            continue;
                        }
                        if (!$this->isElementPresent($y)) {
                            $this->addVerificationMessage('Could not find element type "' . $optionData['type'] .
                                                              '" and title "' . $optionData['title'] . '"');
                        }
                    }
                }
            }
        }
        $this->assertEmptyVerificationErrors();
    }

    /**
     * Gets the xpathes for validation on frontend
     *
     * @param array $productData
     *
     * @return array
     */
    public function getCustomOptionsXpathes(array $productData)
    {
        $xpathArray = array();
        $date = strtotime(date("m/d/Y"));
        $startDate = isset($productData['prices_special_price_from'])
            ? strtotime($productData['prices_special_price_from'])
            : 1;
        $expirationDate = isset($productData['prices_special_price_to'])
            ? strtotime($productData['prices_special_price_to'])
            : 1;
        if ($startDate <= $date && $expirationDate >= $date) {
            $priceToCalc = $productData['prices_special_price'];
        } else {
            $priceToCalc = $productData['prices_price'];
        }
        $avail = (isset($productData['inventory_stock_availability']))
            ? $productData['inventory_stock_availability']
            : null;
        $allowedQty = (isset($productData['inventory_min_allowed_qty']))
            ? $productData['inventory_min_allowed_qty']
            : null;
        $shortDescription = (isset($productData['general_short_description']))
            ? $productData['general_short_description']
            : null;
        $longDescription = (isset($productData['general_description'])) ? $productData['general_description'] : null;
        if ($shortDescription) {
            $this->addParameter('shortDescription', $shortDescription);
            $xpathArray['Short Description'] = $this->_getControlXpath('pageelement', 'short_description');
        }
        if ($longDescription) {
            $this->addParameter('longDescription', $longDescription);
            $xpathArray['Description'] = $this->_getControlXpath('pageelement', 'descriprion');
        }
        $avail = ($avail == 'In Stock') ? 'In stock' : 'Out of stock';
        if ($avail == 'Out of stock') {
            $this->addParameter('avail', $avail);
            $xpathArray['Availability'] = $this->_getControlXpath('pageelement', 'availability_param');
            return $xpathArray;
        }
        $allowedQty = ($allowedQty == null) ? '1' : $allowedQty;
        $this->addParameter('price', $allowedQty);
        $xpathArray['Quantity'] = $this->_getControlXpath('pageelement', 'qty');
        $i = 0;
        foreach ($productData['custom_options_data'] as $value) {
            $title = $value['custom_options_general_title'];
            $optionType = $value['custom_options_general_input_type'];
            $xpathArray['custom_options']['option_' . $i]['title'] = $title;
            $xpathArray['custom_options']['option_' . $i]['type'] = $optionType;
            $this->addParameter('title', $title);
            if ($value['custom_options_general_input_type'] == 'Drop-down'
                    || $value['custom_options_general_input_type'] == 'Multiple Select') {
                $someArr = $this->_formXpathForCustomOptionsRows($value, $priceToCalc, $i, 'custom_option_select');
                $xpathArray = array_merge_recursive($xpathArray, $someArr);
            } elseif ($value['custom_options_general_input_type'] == 'Radio Buttons'
                    || $value['custom_options_general_input_type'] == 'Checkbox') {
                $someArr = $this->_formXpathForCustomOptionsRows($value, $priceToCalc, $i, 'custom_option_check');
                $xpathArray = array_merge_recursive($xpathArray, $someArr);
            } else {
                $someArr = $this->_formXpathesForFieldsArray($value, $i, $priceToCalc);
                $xpathArray = array_merge_recursive($xpathArray, $someArr);
            }
            $i++;
        }
        return $xpathArray;
    }

    /**
     * @param array $value
     * @param int $i
     * @param string $priceToCalc
     *
     * @return array
     */
    private function _formXpathesForFieldsArray(array $value, $i, $priceToCalc)
    {
        $xpathArray = array();
        if (array_key_exists('custom_options_price_type', $value)) {
            if ($value['custom_options_price_type'] == 'Fixed' && isset($value['custom_options_price'])) {
                $price = '$' . number_format((float)$value['custom_options_price'], 2);
                $this->addParameter('price', $price);
                $xpath = $this->_getControlXpath('pageelement', 'custom_option_non_select');
                $someArr = $this->_defineXpathForAdditionalOptions($value, $i, $xpath);
                $xpathArray = array_merge_recursive($xpathArray, $someArr);
            } elseif ($value['custom_options_price_type'] == 'Percent' && isset($value['custom_options_price'])) {
                $price = '$' . number_format(round($priceToCalc / 100 * $value['custom_options_price'], 2), 2);
                $this->addParameter('price', $price);
                $xpath = $this->_getControlXpath('pageelement', 'custom_option_non_select');
                $someArr = $this->_defineXpathForAdditionalOptions($value, $i, $xpath);
                $xpathArray = array_merge_recursive($xpathArray, $someArr);
            } else {
                $xpath = $this->_getControlXpath('pageelement', 'custom_option_non_select_wo_price');
                $someArr = $this->_defineXpathForAdditionalOptions($value, $i, $xpath);
                $xpathArray = array_merge_recursive($xpathArray, $someArr);
            }
        }
        return $xpathArray;
    }

    /**
     * @param array $value
     * @param int $i
     * @param string $xpath
     *
     * @return array
     */
    private function _defineXpathForAdditionalOptions(array $value, $i, $xpath)
    {
        $xpathArray = array();
        $count = 0;
        if (array_key_exists('custom_options_max_characters', $value)
                || array_key_exists('custom_options_allowed_file_extension', $value)
                || array_key_exists('custom_options_image_size_x', $value)
                || array_key_exists('custom_options_image_size_y', $value)) {
            if (array_key_exists('custom_options_max_characters', $value)) {
                $this->addParameter('maxChars', $value['custom_options_max_characters']);
                $xpathMax = $this->_getControlXpath('pageelement', 'custom_option_max_chars');
                $xpathArray['custom_options']['option_' . $i]['xpath_' . $count++] = $xpathMax;
            }
            if (array_key_exists('custom_options_allowed_file_extension', $value)) {
                $this->addParameter('fileExt', $value['custom_options_allowed_file_extension']);
                $xpathExt = $this->_getControlXpath('pageelement', 'custom_option_file_ext');
                $xpathArray['custom_options']['option_' . $i]['xpath_' . $count++] = $xpathExt;
            }
            if (array_key_exists('custom_options_image_size_x', $value)) {
                $this->addParameter('fileWidth', $value['custom_options_image_size_x']);
                $xpathExt = $this->_getControlXpath('pageelement', 'custom_option_file_max_width');
                $xpathArray['custom_options']['option_' . $i]['xpath_' . $count++] = $xpathExt;
            }
            if (array_key_exists('custom_options_image_size_y', $value)) {
                $this->addParameter('fileHeight', $value['custom_options_image_size_y']);
                $xpathExt = $this->_getControlXpath('pageelement', 'custom_option_file_max_height');
                $xpathArray['custom_options']['option_' . $i]['xpath_' . $count++] = $xpathExt;
            }
        } else {
            $xpathArray['custom_options']['option_' . $i]['xpath_' . $count++] = $xpath;
        }
        return $xpathArray;
    }

    /**
     * @param array $options
     * @param string $priceToCalc
     * @param int $i
     * @param string $pageelement
     *
     * @return array
     */
    private function _formXpathForCustomOptionsRows(array $options, $priceToCalc, $i, $pageelement)
    {
        $xpathArray = array();
        $count = 0;
        foreach ($options as $k => $v) {
            if (!preg_match('/^custom_option_row_/', $k)) {
                continue;
            }
            $optionTitle = $v['custom_options_title'];
            $this->addParameter('optionTitle', $optionTitle);
            if (array_key_exists('custom_options_price_type', $v)) {
                if ($v['custom_options_price_type'] == 'Fixed' && isset($v['custom_options_price'])) {
                    $optionPrice = '$' . number_format((float)$v['custom_options_price'], 2);
                    $this->addParameter('optionPrice', $optionPrice);
                    $xpathArray['custom_options']['option_' . $i]['xpath_' . $count++] =
                        $this->_getControlXpath('pageelement', $pageelement);
                } elseif ($v['custom_options_price_type'] == 'Percent' && isset($v['custom_options_price'])) {
                    $optionPrice = '$' . number_format(round
                                                       ($priceToCalc / 100 * $v['custom_options_price'], 2), 2);
                    $this->addParameter('optionPrice', $optionPrice);
                    $xpathArray['custom_options']['option_' . $i]['xpath_' . $count++] =
                        $this->_getControlXpath('pageelement', $pageelement);
                } else {
                    $xpathArray['custom_options']['option_' . $i]['xpath_' . $count++] =
                        $this->_getControlXpath('pageelement', $pageelement . '_wo_price');
                }
            } else {
                $xpathArray['custom_options']['option_' . $i]['xpath_' . $count++] =
                    $this->_getControlXpath('pageelement', $pageelement . '_wo_price');
            }
        }
        return $xpathArray;
    }
}
