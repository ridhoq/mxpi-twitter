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
 * Configure System Configuration General Web
 *
 * @package     selenium
 * @subpackage  tests
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
use Xcom_Chronicle_XMessages_WebStore as WebStore;

class Xcom_Chronicle_Web_SystemConfiguration_GeneralWeb_ConfigureTest extends Xcom_Chronicle_TestCase
{
    const MINIMUM_PRODUCT_COUNT = 5;
    const HOST_NAME1 = "joan.x.com";
    const HOST_NAME2 = "joan.test.com";
    protected $_generalWebData;

    /**
     * <p>Preconditions:</p>
     * <p>1. There are at least 5 products in the system</p>
     * <p>2. Record the current setting in System Configuration General Web
     * <p>3. Log in to Backend.</p>
     * <p>4. Navigate to System Configuration General Web section
     * <p>5. Clear pending mass_offer_update cron job records from database
     */
    public function setUpBeforeTests()
    {
        //Make sure there is at least minimum products in magento
        $initialProductCount = $this->_getProductCount();
        if ($initialProductCount < self::MINIMUM_PRODUCT_COUNT) {
            $this->loginAdminUser();
            for ($i = $initialProductCount; $i < self::MINIMUM_PRODUCT_COUNT; $i++) {
                $this->_createSimpleProduct();
            }
        }
        //Record the current general web settings
        $this->_saveSetting();
        //Log in to backend
        $this->loginAdminUser();
        //Clear mass_offer_update cron job records from database
        $this->_clearPendingMassOfferUpdateFromCronSchedule();
        //Navigate to System Configuration General Web Section
        $this->admin('system_configuration');
        $xpath = $this->_getControlXpath('tab', 'general_web');
        $this->addParameter('tabName', 'web');
        $this->clickAndWait($xpath, $this->_browserTimeoutPeriod);
    }
    
    protected function assertPreConditions()
    {
        $this->addParameter('id', '0');
    }

    public function tearDown()
    {
        //Restore System Configuration General Web Settings
        $this->_restoreSetting();
    }

    /**
     * Toggle Add Store Code To Urls
     * <p>Preconditions:</p>
     * <p>1.No mass_offer_update cron job scheduled</p>
     * <p>Steps:</p>
     * <p>Toggle 'Add Store Code To Urls'</p>
     * <p>Expected result:</p>
     * <p>mass_offer_update cron job is scheduled</p>
     *
     * @test
     */
    public function toggleAddStoreCodeToUrls()            
    {
        //Precondition
        $this->assertEquals(0, sizeof($this->_getPendingMassOfferUpdateCronJob()), 'Should not have mass_offer_update cron job scheduled');

        //Action
        $value1 = $this->_generalWebData['web/url/use_store'] == 0 ? 'Yes' : 'No';
        $this->_setAddStoreCodeToUrls($value1);

        //Verify
        $this->assertEquals(1, sizeof($this->_getPendingMassOfferUpdateCronJob()), 'Should have one mass_offer_update cron job scheduled');
    }

    /**
     * Toggle Use Web Server Rewrites
     * <p>Preconditions:</p>
     * <p>1.No mass_offer_update cron job scheduled</p>
     * <p>Steps:</p>
     * <p>Toggle 'Use Web Server Rewrites'</p>
     * <p>Expected result:</p>
     * <p>mass_offer_update cron job is scheduled</p>
     *
     * @test
     */
    public function toggleUseWebServerRewrites()            
    {
        //Precondition
        $this->assertEquals(0, sizeof($this->_getPendingMassOfferUpdateCronJob()), 'Should not have mass_offer_update cron job scheduled');

        //Action
        $value1 = $this->_generalWebData['web/seo/use_rewrites'] == 0 ? 'Yes' : 'No';
        $this->_setUseWebServerRewrites($value1);

        //Verify
        $this->assertEquals(1, sizeof($this->_getPendingMassOfferUpdateCronJob()), 'Should have one mass_offer_update cron job scheduled');
    }

    /**
     * Change unsecure base url
     * <p>Preconditions:</p>
     * <p>1.No mass_offer_update cron job scheduled</p>
     * <p>Steps:</p>
     * <p>Change 'unsecure base url'</p>
     * <p>Expected result:</p>
     * <p>mass_offer_update cron job is scheduled</p>
     *
     * @test
     */
    public function changUnsecureBaseUrl()
    {
        //Precondition
        $this->assertEquals(0, sizeof($this->_getPendingMassOfferUpdateCronJob()), 'Should not have mass_offer_update cron job scheduled');

        $url1 = 'http://' . self::HOST_NAME1 . '/1.11/';
        $url2 = 'http://' . self::HOST_NAME2 . '/1.11/';

        //Action
        $url = strcasecmp($this->_generalWebData['web/unsecure/base_url'], $url1) == 0 ? $url2 : $url1;
        $this->_setUnsecureBaseUrl($url);

        //Verify
        $this->assertEquals(1, sizeof($this->_getPendingMassOfferUpdateCronJob()), 'Should have one mass_offer_update cron job scheduled');
    }

    /**
     * Change unsecure base link url
     * <p>Preconditions:</p>
     * <p>1.No mass_offer_update cron job scheduled</p>
     * <p>Steps:</p>
     * <p>Change 'unsecure base link url'</p>
     * <p>Expected result:</p>
     * <p>mass_offer_update cron job is scheduled</p>
     *
     * @test
     */
    public function changUnsecureBaseLinkUrl()
    {
        //Precondition
        $this->assertEquals(0, sizeof($this->_getPendingMassOfferUpdateCronJob()), 'Should not have mass_offer_update cron job scheduled');

        $url1 = 'http://' . self::HOST_NAME1 . '/1.11/';
        $url2 = 'http://' . self::HOST_NAME2 . '/1.11/';

        //Action
        $url = strcasecmp($this->_generalWebData['web/unsecure/base_link_url'], $url1) == 0 ? $url2 : $url1;
        $this->_setUnsecureBaseLinkUrl($url);

        //Verify
        $this->assertEquals(1, sizeof($this->_getPendingMassOfferUpdateCronJob()), 'Should have one mass_offer_update cron job scheduled');
    }

    /**
     * Change secure base url
     * <p>Preconditions:</p>
     * <p>1.No mass_offer_update cron job scheduled</p>
     * <p>Steps:</p>
     * <p>Change 'secure base url'</p>
     * <p>Expected result:</p>
     * <p>mass_offer_update cron job is scheduled</p>
     *
     * @test
     */
    public function changSecureBaseUrl()
    {
        //Precondition
        $this->assertEquals(0, sizeof($this->_getPendingMassOfferUpdateCronJob()), 'Should not have mass_offer_update cron job scheduled');

        $url1 = 'http://' . self::HOST_NAME1 . '/1.11/';
        $url2 = 'http://' . self::HOST_NAME2 . '/1.11/';

        //Action
        $url = strcasecmp($this->_generalWebData['web/secure/base_url'], $url1) == 0 ? $url2 : $url1;
        $this->_setSecureBaseUrl($url);

        //Verify
        $this->assertEquals(1, sizeof($this->_getPendingMassOfferUpdateCronJob()), 'Should have one mass_offer_update cron job scheduled');
    }

    /**
     * Change secure base link url
     * <p>Preconditions:</p>
     * <p>1.No mass_offer_update cron job scheduled</p>
     * <p>Steps:</p>
     * <p>Change 'secure base link url'</p>
     * <p>Expected result:</p>
     * <p>mass_offer_update cron job is scheduled</p>
     *
     * @test
     */
    public function changSecureBaseLinkUrl()
    {
        //Precondition
        $this->assertEquals(0, sizeof($this->_getPendingMassOfferUpdateCronJob()), 'Should not have mass_offer_update cron job scheduled');

        $url1 = 'http://' . self::HOST_NAME1 . '/1.11/';
        $url2 = 'http://' . self::HOST_NAME2 . '/1.11/';

        //Action
        $url = strcasecmp($this->_generalWebData['web/secure/base_link_url'], $url1) == 0 ? $url2 : $url1;
        $this->_setSecureBaseLinkUrl($url);

        //Verify
        $this->assertEquals(1, sizeof($this->_getPendingMassOfferUpdateCronJob()), 'Should have one mass_offer_update cron job scheduled');
    }

    /**
     * Execute Mass Offer Update Cron Job
     * <p>Steps:</p>
     * <p>Execute Mass Offer Update Cron Job</p>
     * <p>Expected result:</p>
     * <p>WebStoreOfferUpdated Messages has been sent out for each product.</p>
     *
     * @test
     */
    public function executeMassOfferUpdateCronJob()
    {
        //Action
        $observer = new Xcom_Chronicle_Model_Observer();
        $observer->massOfferUpdateCronJob(null);

        $products = $this->_getProducts();
        $expectedMsgs = array();
        /* @var $product Mage_Catalog_Model_Product  */
        foreach ($products as $product) {
            $storeCount = count($product->getStoreIds());
            while ($storeCount-- > 0) {
                $expectedMsgs[] = array( "topic" => WebStore::OFFER_UPDATED );
            }
        }
        $msgs = $this->_get2dXMessages();

        //Verify
        $this->verifyXMessage($expectedMsgs, $msgs);
    }

    #*********************************************
    #*                Helper Methods             *
    #*********************************************    
    /**
     * Set Add Store Code To Urls to the given value.
     * It assumes the current page is system configuration General Web page.
     * 
     * @param string $value 
     */
    protected function _setAddStoreCodeToUrls($value = "Yes")
    {
        $data = array('add_store_code_to_urls'             => ucwords(strtolower($value)),);
        $this->_configureGeneralWeb($data);
    }
    
    /**
     * Set Use Web Server Rewrites to the given value
     * It assumes the current page is system configuration general web page.
     * 
     * @param string $value 
     */
    protected function _setUseWebServerRewrites($value = "Yes")
    {
        $data = array('use_web_server_rewrites'             => ucwords(strtolower($value)),);
        $this->_configureGeneralWeb($data);
    }
    
    /**
     * Set Unsecured Base Url to the given value
     * It assumes the current page is system configuration general web page.
     * 
     * @param string $url 
     */
    protected function _setUnsecureBaseUrl($url)
    {
        $data = array('unsecure_base_url'             => $url,);
        $this->_configureGeneralWeb($data);
        
    }
    
    /**
     * Set Unsecured Base Link Url to the given value
     * It assumes the current page is system configuration general web page.
     * 
     * @param string $url 
     */
    protected function _setUnsecureBaseLinkUrl($url)
    {
        $data = array('unsecure_base_link_url'             => $url,);
        $this->_configureGeneralWeb($data);
    }
    
    /**
     * Set Secure Base Url to the given value
     * It assumes the current page is system configuration general web page.
     * 
     * @param type $url 
     */
    protected function _setSecureBaseUrl($url)
    {
        $data = array('secure_base_url'             => $url,);
        $this->_configureGeneralWeb($data);             
    }
    
    /**
     * Set Secure Base Link Url to the given value
     * It assumes the current page is system configuration general web page.
     * 
     * @param string $url 
     */
    protected function _setSecureBaseLinkUrl($url)
    {
        $data = array('secure_base_link_url'             => $url,);
        $this->_configureGeneralWeb($data);
    }

    /**
     * Configure system configuration general web setting with the given data.
     * It assumes the current page is system configuration general web page.
     * 
     * @param array $data 
     */
    protected function _configureGeneralWeb($data)
    {
        $this->fillForm($data, 'general_web');
        $this->clickButton('save_config', false);
        $this->waitForPageToLoad($this->_browserTimeoutPeriod);
        $successMessages = $this->getMessagesOnPage('success');
        $this->assertEquals("The configuration has been saved.", $successMessages[0], "Configuration has been saved");     
    }

    /**
     * Save current System Configuration General Web Setting
     */
    protected function _saveSetting()
    {
        $this->_generalWebData = array();
        $collection = Mage::getResourceModel('core/config_data_collection');
        $collection = $collection->addScopeFilter('default', '0', 'web');
        $data = $collection->getData();
        foreach ($data as $aConfig) {
            $this->_generalWebData[$aConfig['path']] = $aConfig['value'];
        }
    }
    /**
     *  Restore System Configuration General Web Setting. 
     *  It assumes current page is system configuration General Web page.
     *
     */
    protected function _restoreSetting()
    {
        $coreConfig = Mage::getModel("Core/Config");
        foreach ($this->_generalWebData as $path => $value) {
            $coreConfig ->saveConfig($path, $value);
        }
    }

    /**
     * Clear pending mass_offer_update jobs from Cron Schedule
     */
    protected function _clearPendingMassOfferUpdateFromCronSchedule()
    {
        $collection = Mage::getResourceModel('cron/schedule_collection');
        $collection = $collection->addFilter('job_code', 'mass_offer_update');
        $collection = $collection->addFilter('status', 'pending');
        $jobs = $collection->getItems();
        foreach ($jobs as $job) {
            $id = $job->getId();
            $job->delete();
        }
    }

    /**
     * Get Pending mass_offer_update cron job
     *
     * @return array
     */
    protected function _getPendingMassOfferUpdateCronJob()
    {
        $collection = Mage::getResourceModel('cron/schedule_collection');
        $collection = $collection->addFilter('job_code', 'mass_offer_update');
        $collection = $collection->addFilter('status', 'pending');
        $jobs = $collection->getData();

        return $jobs;
    }
}