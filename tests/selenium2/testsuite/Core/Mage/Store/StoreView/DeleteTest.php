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
 * Delete Store View in Backend
 *
 * @package     selenium
 * @subpackage  tests
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class Core_Mage_Store_StoreView_DeleteTest extends Mage_Selenium_TestCase
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
     * <p>Create Store View. Fill in only required fields.</p>
     * <p>Steps:</p>
     * <p>1. Click 'Create Store View' button.</p>
     * <p>2. Fill in required fields.</p>
     * <p>3. Click 'Save Store View' button.</p>
     * <p>Expected result:</p>
     * <p>Store View is created.</p>
     * <p>Success Message is displayed</p>
     *
     * @return array
     * @test
     * @TestlinkId TL-MAGE-3486
     */
    public function creationStoreView()
    {
        //Data
        $storeViewData = $this->loadData('generic_store_view');
        //Steps
        $this->storeHelper()->createStore($storeViewData, 'store_view');
        //Verifying
        $this->assertMessagePresent('success', 'success_saved_store_view');

        return $storeViewData;
    }

    /**
     * <p>Delete Store View Without creating DB backup</p>
     * <p>Steps:</p>
     * <p>1. Navigate to "System->Manage Stores";</p>
     * <p>2. Select created Store View from the grid and open it;</p>
     * <p>3. Click "Delete Store View" button;</p>
     * <p>4. Select "No" on Backup Options page;</p>
     * <p>5. Click "Delete Store View" button;</p>
     * <p>Expected result:</p>
     * <p>Success message appears - "The store view has been deleted."</p>
     *
     * @param $storeViewData
     * @test
     * @depends creationStoreView
     * @TestlinkId	TL-MAGE-3487
     */
    public function deleteStoreViewWithoutBackup($storeViewData)
    {
        //Data
        $storeData = array('store_view_name' =>$storeViewData['store_view_name']);
        //Steps
        $this->storeHelper()->deleteStore($storeData);
    }
}