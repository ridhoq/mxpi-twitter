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
class Core_Mage_CmsWidgets_Helper extends Mage_Selenium_TestCase
{
    /**
     * Creates widget
     *
     * @param string|array $widgetData
     */
    public function createWidget($widgetData)
    {
        if (is_string($widgetData)) {
            $widgetData = $this->loadData($widgetData);
        }
        $widgetData = $this->arrayEmptyClear($widgetData);
        $settings = (isset($widgetData['settings'])) ? $widgetData['settings'] : array();
        $frontProperties = (isset($widgetData['frontend_properties'])) ? $widgetData['frontend_properties'] : array();
        $layoutUpdates = (isset($widgetData['layout_updates'])) ? $widgetData['layout_updates'] : array();
        $widgetOptions = (isset($widgetData['widget_options'])) ? $widgetData['widget_options'] : array();

        $this->clickButton('add_new_widget_instance');
        $this->fillWidgetSettings($settings);
        if (array_key_exists('assign_to_store_views', $frontProperties)
                && !$this->controlIsPresent('multiselect', 'assign_to_store_views')) {
            unset($frontProperties['assign_to_store_views']);
        }
        $this->fillForm($frontProperties, 'frontend_properties');

        if ($layoutUpdates) {
            $this->fillLayoutUpdates($layoutUpdates);
        }
        if ($widgetOptions) {
            $this->fillWidgetOptions($widgetOptions);
        }
        $this->saveForm('save');
    }

    /**
     * Fills settings for creating widget
     *
     * @param array $settings
     */
    public function fillWidgetSettings(array $settings)
    {
        if ($settings) {
            $xpath = $this->_getControlXpath('dropdown', 'type');
            $type = $this->getValue($xpath . '/option[text()="' . $settings['type'] . '"]');
            $this->addParameter('type', str_replace('/', '-', $type));
            $packageTheme = array_map('trim', (explode('/', $settings['design_package_theme'])));
            $this->addParameter('package', $packageTheme[0]);
            $this->addParameter('theme', $packageTheme[1]);
            $this->fillForm($settings);
        }
        $this->clickButton('continue', false);
        if ($this->isAlertPresent()) {
            $this->fail($this->getAlert());
        }
        $this->waitForPageToLoad($this->_browserTimeoutPeriod);
        $this->validatePage('add_widget_options');
    }

    /**
     * Fills data for layout updates
     *
     * @param string|array $layoutData
     */
    public function fillLayoutUpdates(array $layoutData)
    {
        $count = 0;
        foreach ($layoutData as $key => $value) {
            $this->clickButton('add_layout_update', false);
            $this->addParameter('index', $count);
            $xpath = $this->_getControlXpath('dropdown', 'select_display_on');
            $layoutName = $this->getValue($xpath . '//option[text()="' . $value['select_display_on'] . '"]');
            $this->addParameter('layout', $layoutName);
            $this->addParameter('widgetParam', "//div[@id='" . $layoutName . '_ids_' . $count++ . "']");
            $this->fillForm($value);
            $xpathOptionsAll = $this->_getControlXpath('radiobutton', 'all_categories_products_radio');
            if (array_key_exists('choose_options', $value)) {
                if (preg_match('/anchor_categories/', $layoutName)) {
                    $this->chooseLayoutOptions($value['choose_options'], 'categories');
                } else {
                    $this->chooseLayoutOptions($value['choose_options']);
                }
            } else {
                if ($this->isElementPresent($xpathOptionsAll)) {
                    $this->check($xpathOptionsAll);
                }
            }
        }
    }

    /**
     * Fills options for layout updates
     *
     * @param array $layoutOptions
     * @param string $layoutName
     */
    public function chooseLayoutOptions(array $layoutOptions, $layoutName = 'products')
    {
        $this->clickControl('radiobutton', 'specific_categories_products_radio', false);
        $this->clickControl('link', 'open_chooser', false);
        $this->pleaseWait();
        if ($layoutName == 'categories') {
            foreach ($layoutOptions as $key => $value) {
                $this->categoryHelper()->selectCategory($value, 'layout_updates_body');
            }
        } elseif ($layoutName == 'products') {
            foreach ($layoutOptions as $key => $value) {
                $this->searchAndChoose($value, 'layout_products_fieldset');
            }
        } else {
            return;
        }
        $this->clickControl('link', 'apply', false);
    }

    /**
     * Fills "Widget Options" tab
     *
     * @param array $widgetOptions
     */
    public function fillWidgetOptions(array $widgetOptions)
    {
        $options = (isset($widgetOptions['chosen_option'])) ? $widgetOptions['chosen_option'] : null;
        $this->fillForm($widgetOptions, 'widgets_options');
        if ($options) {
            $this->cmsPagesHelper()->selectOptionItem($options);
        }
    }

    /**
     * Opens widget
     *
     * @param array $searchWidget
     */
    public function openWidget(array $searchWidget)
    {
        $searchWidget = $this->arrayEmptyClear($searchWidget);
        $xpathTR = $this->search($searchWidget, 'cms_widgets_grid');
        $this->assertNotEquals(null, $xpathTR, 'Widget is not found');
        $cellId = $this->getColumnIdByName('Widget Instance Title');
        $this->addParameter('widgetName', $this->getText($xpathTR . '//td[' . $cellId . ']'));
        $this->addParameter('id', $this->defineIdFromTitle($xpathTR));
        $this->click($xpathTR);
        $this->waitForPageToLoad($this->_browserTimeoutPeriod);
        $this->validatePage();
    }

    /**
     * Deletes widget
     *
     * @param array $searchWidget
     */
    public function deleteWidget(array $searchWidget)
    {
        $this->openWidget($searchWidget);
        $this->clickButtonAndConfirm('delete', 'confirmation_for_delete');
    }
}