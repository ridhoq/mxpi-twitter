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
class Core_Mage_Store_Helper extends Mage_Selenium_TestCase
{
    /**
     * Create Website|Store|Store View
     *
     * Preconditions: 'Manage Stores' page is opened.
     * @param array|string $data
     * @param string $name
     */
    public function createStore($data, $name)
    {
        if (is_string($data)) {
            $data = $this->loadData($data);
        }
        $data = $this->arrayEmptyClear($data);

        $this->clickButton('create_' . $name);
        $this->fillForm($data);
        $this->saveForm('save_' . $name);
    }

    /**
     * Delete Website|Store|Store View
     *
     * @param array $storeData
     * @return boolean
     */
    public function deleteStore(array $storeData)
    {
        //Delete array keys with value = '%noValue%'
        $storeData = $this->arrayEmptyClear($storeData);
        //Determination of element name
        $elementName = '';
        foreach ($storeData as $fieldName => $fieldValue) {
            if (preg_match('/_name$/', $fieldName)) {
                $elementName = $fieldName;
            }
        }
        $element = preg_replace('/_name$/', '', $elementName);
        if ($elementName == '') {
            $this->fail('It is impossible to determine what needs to be deleted');
        }
        //Search
        $this->clickButton('reset_filter');
        $this->fillForm(array($elementName => $storeData[$elementName]));
        $this->clickButton('search');
        //Determination of found items amount
        $fieldsetXpath = $this->_getControlXpath('fieldset', 'manage_stores');
        $qtyElementsInTable = $this->_getControlXpath('pageelement', 'qtyElementsInTable',
                                                      $this->getUimapPage('admin', 'dashboard'));
        $foundItems = $this->getText($fieldsetXpath . $qtyElementsInTable);
        if ($foundItems == 0) {
            $this->fail('No records found.');
        }
        //Determination of row id
        $names = $this->getTableHeadRowNames();
        foreach ($names as $key => $value) {
            $names[$key] = trim(strtolower(preg_replace('#[^0-9a-z]+#i', '_', $value)), '_');
        }
        $number = (in_array($elementName, $names)) ? array_search($elementName, $names) + 1 : 0;
        //Deletion
        $error = false;
        $this->addParameter('elementTitle', $storeData[$elementName]);
        for ($i = 1; $i <= $foundItems; $i++) {
            //Definition element url
            $xpath = $fieldsetXpath . '//table[@id]/tbody' . '/tr[' . $i . ']/td[' . $number . ']/a';
            $url = $this->getAttribute($xpath . '@href');
            //Open element
            $this->addParameter('id', $this->defineIdFromUrl($url));
            $this->openWindow($url, 'edit');
            $this->selectWindow('name=edit');
            $this->waitForPageToLoad($this->_browserTimeoutPeriod);
            $this->validatePage('edit_' . $element);
            //Searching a necessary element
            if ($this->verifyForm($storeData)) {
                if ($this->controlIsPresent('button', 'delete_' . $element)) {
                    $this->clickButton('delete_' . $element);
                    $this->fillForm(array('create_backup' => 'No'));
                    $this->clickButton('delete_' . $element);
                    $this->assertMessagePresent('success', 'success_deleted_' . $element);
                    $this->close();
                    $this->selectWindow(null);

                    return true;
                } else {
                    $error = true;
                    $this->close();
                    $this->selectWindow(null);
                }
            } else {
                $this->close();
                $this->selectWindow(null);
            }
        }

        if ($error) {
            $this->fail('It is impossible to detele ' . $element);
        }

        return false;
    }
}