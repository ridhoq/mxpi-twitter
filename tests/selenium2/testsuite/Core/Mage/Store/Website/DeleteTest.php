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
 * Delete Website into Backend
 *
 * @package     selenium
 * @subpackage  tests
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class Core_Mage_Store_Website_DeleteTest extends Mage_Selenium_TestCase
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
     * <p>Delete Website without Store</p>
     * <p>Preconditions:</p>
     * <p>Website created without Store;</p>
     * <p>Steps:</p>
     * <p>1. Navigate to "System->Manage Stores";</p>
     * <p>2. Select created Website from the grid and open it;</p>
     * <p>3. Click "Delete Website" button;</p>
     * <p>4. Select "No" on Backup Options page;</p>
     * <p>5. Click "Delete Website" button;</p>
     * <p>Expected result:</p>
     * <p>Success message appears - "The website has been deleted."</p>
     *
     * @test
     * @TestlinkId	TL-MAGE-3491
     */
    public function deleteWithoutStore()
    {
        //Preconditions
        $websiteData = $this->loadData('generic_website');
        $this->storeHelper()->createStore($websiteData, 'website');
        $this->assertMessagePresent('success', 'success_saved_website');
        //Data
        $deleteWebsiteData = array('website_name' =>$websiteData['website_name']);
        //Steps
        $this->storeHelper()->deleteStore($deleteWebsiteData);
    }

    /**
     * <p>Delete Website with Store</p>
     * <p>Preconditions:</p>
     * <p>Website created with Store;</p>
     * <p>Steps:</p>
     * <p>1. Navigate to "System->Manage Stores";</p>
     * <p>2. Select created Website from the grid and open it;</p>
     * <p>3. Click "Delete Website" button;</p>
     * <p>4. Select "No" on Backup Options page;</p>
     * <p>5. Click "Delete Website" button;</p>
     * <p>Expected result:</p>
     * <p>Success message appears - "The website has been deleted."</p>
     *
     * @test
     * @TestlinkId	TL-MAGE-3492
     */
    public function deleteWithStore()
    {
        //Preconditions
        $websiteData = $this->loadData('generic_website');
        $storeData = $this->loadData('generic_store', array('website' => $websiteData['website_name']));
        $this->storeHelper()->createStore($websiteData, 'website');
        $this->assertMessagePresent('success', 'success_saved_website');
        $this->storeHelper()->createStore($storeData, 'store');
        $this->assertMessagePresent('success', 'success_saved_store');
        //Data
        $deleteWebsiteData = array('website_name' =>$websiteData['website_name']);
        //Steps
        $this->storeHelper()->deleteStore($deleteWebsiteData);
    }

    /**
     * <p>Delete Website with Store and Store View</p>
     * <p>Preconditions:</p>
     * <p>Website created with Store and Store View;</p>
     * <p>Steps:</p>
     * <p>1. Navigate to "System->Manage Stores";</p>
     * <p>2. Select created Website from the grid and open it;</p>
     * <p>3. Click "Delete Website" button;</p>
     * <p>4. Select "No" on Backup Options page;</p>
     * <p>5. Click "Delete Website" button;</p>
     * <p>Expected result:</p>
     * <p>Success message appears - "The website has been deleted."</p>
     *
     * @test
     * @TestlinkId	TL-MAGE-3493
     */
    public function deleteWithStoreAndStoreView()
    {
        //Preconditions
        $websiteData = $this->loadData('generic_website');
        $storeData = $this->loadData('generic_store', array('website' => $websiteData['website_name']));
        $storeViewData = $this->loadData('generic_store_view', array('store_name' => $storeData['store_name']));
        $this->storeHelper()->createStore($websiteData, 'website');
        $this->assertMessagePresent('success', 'success_saved_website');
        $this->storeHelper()->createStore($storeData, 'store');
        $this->assertMessagePresent('success', 'success_saved_store');
        $this->storeHelper()->createStore($storeViewData, 'store_view');
        $this->assertMessagePresent('success', 'success_saved_store_view');
        //Data
        $deleteWebsiteData = array('website_name' =>$websiteData['website_name']);
        //Steps
        $this->storeHelper()->deleteStore($deleteWebsiteData);
    }

    /**
     * <p>Delete Website with assigned product</p>
     * <p>Preconditions:</p>
     * <p>Create product and assign it to created Website</p>
     * <p>Steps:</p>
     * <p>1. Navigate to "System->Manage Stores";</p>
     * <p>2. Select created Website from the grid and open it;</p>
     * <p>3. Click "Delete Website" button;</p>
     * <p>4. Select "No" on Backup Options page;</p>
     * <p>5. Click "Delete Website" button;</p>
     * <p>Expected result:</p>
     * <p>Success message appears - "The website has been deleted."</p>
     *
     * @test
     * @TestlinkId	TL-MAGE-3490
     */
    public function deleteWithAssignedProduct()
    {
        //Preconditions
        $websiteData = $this->loadData('generic_website');
        $productData = $this->loadData('simple_product_visible', array('websites' => $websiteData['website_name']), array('general_name', 'general_sku'));
        $this->storeHelper()->createStore($websiteData, 'website');
        $this->assertMessagePresent('success', 'success_saved_website');
        $this->navigate('manage_products');
        $this->productHelper()->createProduct($productData);
        $this->assertMessagePresent('success', 'success_saved_product');
        //Data
        $deleteWebsiteData = array('website_name' =>$websiteData['website_name']);
        //Steps
        $this->navigate('manage_stores');
        $this->storeHelper()->deleteStore($deleteWebsiteData);
    }
}