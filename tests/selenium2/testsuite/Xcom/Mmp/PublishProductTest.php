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

class XCom_Mmp_PublishProductTest extends Mage_Selenium_TestCase {

        protected $_initializer_in_progress=false;
    /**
     * <p>Log in to Backend.</p>
     */
    public function setUpBeforeTests() {
   /**
     * small trick. we are adding value in table xcom_xore_policy for column xprofile_id to make policy active
     */
        $this->windowMaximize();
        $this->loginAdminUser();
        /** @var $resource Mage_Core_Model_Resource */
        $resource = Mage::getSingleton('core/resource');
        /** @var $conn Varien_Db_Adapter_Pdo_Mysql */
        $conn = $resource->getConnection('core_write');
        $data = $conn->update($resource->getTableName('xcom_mmp/policy'),
                array('xprofile_id' => 'eBay_test'), 'name = "Equipment"');
    }

    protected function assertPreConditions() {
        $this->addParameter('url_listing_status', '0');
        $this->addParameter('product_id', '0');
        $this->addParameter('ebay_channel_id', '0');
        $this->addParameter('ebay_store_id', '0');
    }
  /**
     * <p>Remove Product from Channel <-p>
     */

    public function test_CreateSimpleProduct() {
        $this->navigate('manage_products');
        $productData = $this->loadData('simple_product_required');
        $productSearch = $this->loadData('product_search', array('product_sku' => $productData['general_sku']));
        $this->productHelper()->createProduct($productData);
    }

     /**
       * <p> Navigate to Channels->Channel Products</p>
       * Verify Initializer
       */
    protected function Precondition() {
        $this->navigate('channel_products');
        $init_message1 = $this->isTextPresent('Updating Channel information from X.commerce');
        $init_message2 = $this->isTextPresent('You will not be able to use the Channel functionality ' .
                'until this process is complete.');
        $init_message3 = $this->isTextPresent('This process may take some time.');
        if ($init_message1 == true and $init_message2 == true and $init_message3 == true){
            $this->_initializer_in_progress=true;
        }
    }
    /**
     * <p>Navigate to Channels->Channel Products<-p>
     */
    public function test_Publish_Product()
    {
        $this->Precondition();
        if ($this->_initializer_in_progress == true) {
            $this->markTestSkipped('Initializer work is not complete');
            return;
        }
        $this->assertElementPresent($this->_getControlXpath('button', 'reset_filter'));
        $this->assertElementPresent($this->_getControlXpath('button', 'search'));
        $this->assertElementPresent($this->_getControlXpath('button', 'submit'));
        $this->assertElementPresent($this->_getControlXpath('dropdown', 'publish_massaction'));
        $this->assertElementPresent($this->_getControlXpath('dropdown', 'filter_massaction'));
        $this->assertElementPresent($this->_getControlXpath('dropdown', 'channel'));
        $this->assertElementPresent($this->_getControlXpath('dropdown', 'product_attribute_set'));
        $this->assertElementPresent($this->_getControlXpath('dropdown', 'product_price_currency'));
        $this->assertElementPresent($this->_getControlXpath('dropdown', 'channels_published_to'));
        $this->assertElementPresent($this->_getControlXpath('dropdown', 'channel_listing_status'));
        $this->assertElementPresent($this->_getControlXpath('field', 'product_id_from'));
        $this->assertElementPresent($this->_getControlXpath('field', 'product_id_to'));
        $this->assertElementPresent($this->_getControlXpath('field', 'product_name'));
        $this->assertElementPresent($this->_getControlXpath('field', 'product_sku'));
        $this->assertElementPresent($this->_getControlXpath('field', 'product_price_from'));
        $this->assertElementPresent($this->_getControlXpath('field', 'product_price_to'));
        $this->assertElementPresent($this->_getControlXpath('field', 'product_qty_from'));
        $this->assertElementPresent($this->_getControlXpath('field', 'product_qty_to'));
        $this->assertElementPresent($this->_getControlXpath('field', 'timestamp_from'));
        $this->assertElementPresent($this->_getControlXpath('field', 'timestamp_to'));
        $this->assertElementPresent($this->_getControlXpath('dropdown', 'choose_store_view'));
        $this->assertElementPresent($this->_getControlXpath('dropdown', 'choose_channel_type'));
        $this->assertElementPresent($this->_getControlXpath('checkboxe', 'product1'));
    }

    /**
     * <p>Navigate to Publish Setting <-p>
     *
     * @depends test_Publish_Product
     */
    public function test_Submit_Publish() {
    /**
     * <p>Steps:</p>
     * <p>Select first checkbox in product's list  <-p>
     * <p>Select in action dropdown "Publish to Channel" <-p>
     * <p>Select in channel dropdown "my eBay site" <-p>
     * <p>Press "Submit" button <-p>
     */
        $this->clickControl('checkboxe', 'product1', false);
        $submit_publish_product = $this->loadData('channel_products');
        $this->fillForm($submit_publish_product);
        $this->clickControl('button', 'submit');
    }

    /**
     * <p>Publish Products to Channel <-p>
     *
     * @depends test_Submit_Publish
     */
    public function test_Publish() {
   /**
     * <p>Steps:</p>
     * <p>Select the category: Antiquities > Other  <-p>
     * <p>Select for price: Markup and 10%<-p>
     * <p>Select for quantity: 10% <-p>
     * <p>Press policy: Equipment <-p>
     */
        $this->assertElementPresent($this->_getControlXpath('field', 'quantity'));
        $this->assertElementPresent($this->_getControlXpath('field', 'price'));
        $this->assertElementPresent($this->_getControlXpath('dropdown', 'policy'));
        $this->assertElementPresent($this->_getControlXpath('dropdown', 'price_percent'));
        $this->assertElementPresent($this->_getControlXpath('dropdown', 'quantity_percent'));
        $this->assertElementPresent($this->_getControlXpath('dropdown', 'price_markup_discount'));
        $this->assertElementPresent($this->_getControlXpath('button', 'back'));
        $this->assertElementPresent($this->_getControlXpath('button', 'reset'));
        $this->assertElementPresent($this->_getControlXpath('button', 'publish'));
        if (false === (bool)$this->isElementPresent($this->_getControlXpath('checkboxe', 'turn_off_recommendation_disabled'))) {
            $this->clickControl('checkboxe','turn_off_recommendation',false);
            $this->pleaseWait();
        }
        $this->clickControl('link', 'parent_category', false);
        sleep(2);
        $this->pleaseWait();
        $this->clickControl('link', 'category', false);
        $pp = $this->loadData('publish_products_data');
        $this->fillForm($pp);
        $this->clickButton('publish');
        $this->assertTrue($this->successMessage('success_publish_saved'));
    }
     public function test_PublishStatusDetail()
     {
   /**
     * <p>Steps:</p>
     * <p>Press on channel listing status link
     * <p>Verify page elements
     *
     * @depends test_Publish
     */
        $xpath_link = $this->_getControlXpath('link', 'published_product');
        $link = $this->getAttribute($xpath_link . '@href');
        $xpath_product_id = $this->mmpHelper()->getIdFromLink($link,'id');
        $this->addParameter('product_id', $xpath_product_id);
        $xpath_channel_id = $this->mmpHelper()->getIdFromLink($link,'channel');
        $this->addParameter('ebay_channel_id', $xpath_channel_id);
        $xpath_store_id = $this->mmpHelper()->getIdFromLink($link,'store');
        $this->addParameter('ebay_store_id', $xpath_store_id);
        $this->clickControl('link', 'published_product');
        $this->assertElementPresent($this->_getControlXpath('field', 'category'));
        $this->assertElementPresent($this->_getControlXpath('field', 'policy'));
        $this->assertElementPresent($this->_getControlXpath('field', 'publish_date_from'));
        $this->assertElementPresent($this->_getControlXpath('field', 'publish_date_to'));
        $this->assertElementPresent($this->_getControlXpath('field', 'product_price_from'));
        $this->assertElementPresent($this->_getControlXpath('field', 'product_price_to'));
        $this->assertElementPresent($this->_getControlXpath('field', 'product_qty_from'));
        $this->assertElementPresent($this->_getControlXpath('field', 'product_qty_to'));
        $this->assertElementPresent($this->_getControlXpath('dropdown', 'channel'));
        $this->assertElementPresent($this->_getControlXpath('dropdown', 'action'));
        $this->assertElementPresent($this->_getControlXpath('dropdown', 'result'));
        $this->assertElementPresent($this->_getControlXpath('dropdown', 'price_in'));
        $this->assertElementPresent($this->_getControlXpath('button', 'back'));
        $this->assertElementPresent($this->_getControlXpath('button', 'reset'));
        $this->assertElementPresent($this->_getControlXpath('button', 'search'));
        $this->clickButton('back');

     }
    /**
     * <p>Remove Product from Channel <-p>
     *
     * @depends test_Publish
     */
        public function test_RemoveProductFromChannel() {
    /**
     * <p>Steps:</p>
     * <p>Select first checkbox in product's list  <-p>
     * <p>Select in action dropdown "Remove from Channel" <-p>
     * <p>Select in channel dropdown "my eBay site" <-p>
     * <p>Press "Publish" button <-p>
     */
        $xpath_product = $this->_getControlXpath('checkboxe', 'product1');
        $product_value = $this->getAttribute($xpath_product . '@value');
                /** @var $resource Mage_Core_Model_Resource */
        $resource = Mage::getSingleton('core/resource');
        /** @var $conn Varien_Db_Adapter_Pdo_Mysql */
        $conn = $resource->getConnection('core_write');
        $data = $conn->update($resource->getTableName('xcom_listing/channel_product'),
                array('listing_status' => '1'), "product_id = $product_value");
        $this->clickControl('checkboxe', 'product1', false);
        $submit_remove_product = $this->loadData('remove_products');
        $this->fillForm($submit_remove_product);
        $this->clickButtonAndConfirm('submit', 'confirmation_remove');
        $this->assertTrue($this->successMessage('success_remove_publish_saved'));
     }

    public function test_deleteSimpleProduct()
    {
   /**
     * <p>Delete product.</p>
     * <p>Steps:</p>
     * <p>1. Open product;</p>
     * <p>2. Click "Delete" button;</p>
     * <p>Expected result:</p>
     * <p>Product is deleted, confirmation message appears;</p>
     *
     * @depends test_CreateSimpleProduct
     */
        $this->navigate('manage_products');
        $productData = $this->loadData('simple_product_required');
        $productSearch = $this->loadData('product_search', array('product_sku' => $productData['general_sku']));
        $this->productHelper()->openProduct($productSearch);
        $this->clickButtonAndConfirm('delete', 'confirmation_for_delete');
        $this->assertTrue($this->successMessage('success_deleted_product'));
    }
}
