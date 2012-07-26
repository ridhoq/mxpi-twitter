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
 * Delete Store in Backend
 *
 * @package     selenium
 * @subpackage  tests
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class Core_Mage_Store_Store_DeleteTest extends Mage_Selenium_TestCase
{
    /**
     * <p>Login to backend</p>
     */
    public function setUpBeforeTests()
    {
        $this->loginAdminUser();
    }

    /**
     * <p>Preconditions:</p>
     * <p>Navigate to System -> Manage Stores</p>
     */
    protected function assertPreConditions()
    {
        $this->navigate('manage_stores');
    }

    /**
     * <p>Delete Store without Store View</p>
     * <p>Preconditions:</p>
     * <p>Store created without Store View;</p>
     * <p>Steps:</p>
     * <p>1. Navigate to "System->Manage Stores";</p>
     * <p>2. Select created Store from the grid and open it;</p>
     * <p>3. Click "Delete Store" button;</p>
     * <p>4. Select "No" on Backup Options page;</p>
     * <p>5. Click "Delete Store" button;</p>
     * <p>Expected result:</p>
     * <p>Success message appears - "The store has been deleted."</p>
     *
     * @test
     * @TestlinkId	TL-MAGE-3489
     */
    public function deleteWithoutStoreView()
    {
        //Preconditions
        $storeData = $this->loadData('generic_store');
        $this->storeHelper()->createStore($storeData, 'store');
        $this->assertMessagePresent('success', 'success_saved_store');
        //Data
        $deleteStoreData = array('store_name' =>$storeData['store_name']);
        //Steps
        $this->storeHelper()->deleteStore($deleteStoreData);
    }

    /**
     * <p>Delete Store with Store View</p>
     * <p>Preconditions:</p>
     * <p>Store with Store View created;</p>
     * <p>Steps:</p>
     * <p>1. Navigate to "System->Manage Stores";</p>
     * <p>2. Select created Store from the grid and open it;</p>
     * <p>3. Click "Delete Store" button;</p>
     * <p>4. Select "No" on Backup Options page;</p>
     * <p>5. Click "Delete Store" button;</p>
     * <p>Expected result:</p>
     * <p>Success message appears - "The store has been deleted."</p>
     *
     * @test
     * @TestlinkId	TL-MAGE-3488
     */
    public function deletableWithStoreView()
    {
        //Preconditions
        $storeData = $this->loadData('generic_store');
        $storeViewData = $this->loadData('generic_store_view', array('store_name' => $storeData['store_name']));
        $this->storeHelper()->createStore($storeData, 'store');
        $this->assertMessagePresent('success', 'success_saved_store');
        $this->storeHelper()->createStore($storeViewData, 'store_view');
        $this->assertMessagePresent('success', 'success_saved_store_view');
        //Data
        $deleteStoreData = array('store_name' =>$storeData['store_name']);
        //Steps
        $this->storeHelper()->deleteStore($deleteStoreData);
    }
}