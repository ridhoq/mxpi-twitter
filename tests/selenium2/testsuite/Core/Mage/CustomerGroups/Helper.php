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
class Core_Mage_CustomerGroups_Helper extends Mage_Selenium_TestCase
{
    /**
     * Create new Customer Group
     *
     * @param array|string $customerGroupData
     */
    public function createCustomerGroup($customerGroupData)
    {
        if (is_string($customerGroupData)) {
            $customerGroupData = $this->loadData($customerGroupData);
        }
        $customerGroupData = $this->arrayEmptyClear($customerGroupData);
        $this->clickButton('add_new_customer_group');
        $this->fillForm($customerGroupData);
        $this->saveForm('save_customer_group');
    }

    /**
     * Open Customer Group
     *
     * @param array|string $searchData
     */
    public function openCustomerGroup($searchData)
    {
        if (is_string($searchData)) {
            $searchData = $this->loadData($searchData);
        }
        $searchData = $this->arrayEmptyClear($searchData);
        $xpathTR = $this->search($searchData, 'customer_group_grid');
        $this->assertNotNull($xpathTR, 'Customer Group is not found');
        $cellId = $this->getColumnIdByName('Group Name');
        $this->addParameter('elementTitle', $this->getText($xpathTR . '//td[' . $cellId . ']'));
        $this->addParameter('id', $this->defineIdFromTitle($xpathTR));
        $this->click($xpathTR . '//td[' . $cellId . ']');
        $this->waitForPageToLoad($this->_browserTimeoutPeriod);
        $this->validatePage();
    }

    /**
     * Delete a Customer Group
     *
     * @param array|string $searchData
     */
    public function deleteCustomerGroup($searchData)
    {
        $this->openCustomerGroup($searchData);
        $this->clickButtonAndConfirm('delete_customer_group', 'confirmation_for_delete');
    }

    /**
     * Edit existing Customer Group
     *
     * @param array $customerGroupData
     * @param array|string $searchData
     */
    public function editCustomerGroup(array $customerGroupData, $searchData)
    {
        $this->openCustomerGroup($searchData);
        $this->fillForm($customerGroupData);
        $this->saveForm('save_customer_group');
    }
}
