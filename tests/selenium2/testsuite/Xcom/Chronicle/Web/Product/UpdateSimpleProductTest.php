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
 * Product life cycle
 *
 * @package     selenium
 * @subpackage  tests
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
use Xcom_Chronicle_XMessages_WebStore as WebStore;
use Xcom_Chronicle_XMessages_Pim as Pim;
use Xcom_Chronicle_XMessages_Inventory as Inventory;

class Xcom_Chronicle_Web_Product_UpdateSimpleProductTest extends Xcom_Chronicle_TestCase
{
    protected $_productSku;
    /**
     * <p>Preconditions:</p>
     * <p>Log in to Backend.</p>
     * <p>Create a simple product.</p>
     */
    public function setUpBeforeTests()
    {

        $this->loginAdminUser();
        $productData = $this->_createSimpleProduct();
        $this->_productSku = $productData['general_sku'];
    }

    protected function assertPreConditions()
    {
        $this->addParameter('id', '0');
    }

    /**
     * <p>Update Product</p>
     * <p>Preconditions:</p>
     * <p>1.A simple product is created.</p>
     * <p>Steps:</p>
     * <p>1. update product with given $updateProductData
     * <p>Expected result:</p>
     * <p>Check given $updateProductGeneratedMsgs are sent out
     *
     * @param $updateProductData
     * @param null $updateProductGeneratedMsgs
     *
     * @dataProvider updateProductDataProvider
     * @test
     */
    public function updateProduct($updateProductData, $updateProductGeneratedMsgs = NULL)
    {
        $this->markTestIncomplete();
        // Update Product
        $searchProductData = array('product_sku' =>  $this->_productSku);
        $this->_updateProduct( $searchProductData, $updateProductData);

        $msgs = $this->_get2dXMessages();

        //Verify
        $this->verifyXMessage($updateProductGeneratedMsgs, $msgs);
    }

    public function updateProductDataProvider(){
        $updateProductNameData = array("general_name" => "updated_name_" . $this->generate('string', 5, ':lower:'));
        $updateProductDescriptionData = array("general_description" => "Updated Description - " . $this->generate('string', 5, ':lower:') . rand(1, 1000));
        $updateProductShortDescriptionData = array("general_short_description" => "Updated Short Description - " . $this->generate('string', 5, ':lower:'));
        $updateProductDescriptionGeneratedMsgs = array();
        $updateProductDescriptionGeneratedMsgs[] = array ("topic" => Pim::PRODUCT_UPDATED);
        $updateProductSkuData = array("general_sku" => "updated_sku_" . $this->generate('string', 5, ':lower:') . rand(1, 1000));
        $updateProductSkuGeneratedMsgs = array();
        $updateProductSkuGeneratedMsgs[] = array("topic" => Pim::PRODUCT_UPDATED);
        $updateProductSkuGeneratedMsgs[] = array("topic" => Inventory::STOCK_ITEM_UPDATED);
        $updateProductSkuGeneratedMsgs[] = array("topic" => WebStore::OFFER_QUANTITY_UPDATED);
        $updateProductPriceData = array("prices_price" => rand(5, 1000)/100);
        $updateProductPriceGeneratedMsgs = array();
        $updateProductPriceGeneratedMsgs[] = array("topic" => WebStore::OFFER_PRICE_UPDATED);
        $updateProductMsrpData = array("prices_manu_suggested_retail_price" => rand(5, 1000)/100);
        $updateProductMsrpMsgs = array();
        $updateProductMsrpMsgs[] = array("topic" => Pim::PRODUCT_UPDATED);
        $updateProductInventoryQtyData = array("inventory_qty" => rand(1, 1000));
        $updateProductInventoryQtyMsgs = array();
        $updateProductInventoryQtyMsgs[] = array("topic" => WebStore::OFFER_QUANTITY_UPDATED);
        $updateProductInventoryQtyMsgs[] = array("topic" => Inventory::STOCK_ITEM_UPDATED);
        $updateProductInventoryOutOfStockData = array("inventory_stock_availability" => "Out of Stock");
        $updateProductInventoryOutOfStockMsgs = array();
        $updateProductInventoryOutOfStockMsgs[] = array("topic" => Inventory::OUT_OF_STOCK);
        $updateProductUrlKeyData = array("general_url_key" => "url-key-" . $this->generate('string', 5, ':lower:') . rand(1, 1000));
        $updateProductUrlKeyGeneratedMsgs = array(
            array( "topic" => WebStore::OFFER_UPDATED )
        );
        return array(
            "Update Simple Product Name" => array($updateProductNameData, $updateProductDescriptionGeneratedMsgs,),
            "Update Simple Product Short Description" => array($updateProductShortDescriptionData, $updateProductDescriptionGeneratedMsgs,),
            "Update Simple Product Description" => array($updateProductDescriptionData, $updateProductDescriptionGeneratedMsgs,),
            "Update Simple Product Sku" => array($updateProductSkuData, $updateProductSkuGeneratedMsgs,),
            "Update Simple Product Price" => array($updateProductPriceData, $updateProductPriceGeneratedMsgs,),
            "Update Simple Product MSRP" => array($updateProductMsrpData, $updateProductMsrpMsgs,),
            "Update Simple Product Qty > 0" => array($updateProductInventoryQtyData, $updateProductInventoryQtyMsgs,),
            "Update Simple Product Out Of Stock" => array($updateProductInventoryOutOfStockData, $updateProductInventoryOutOfStockMsgs,),
            "Update Simple Product Url Key" => array($updateProductUrlKeyData, $updateProductUrlKeyGeneratedMsgs,),
        );
    }

    /**
     * <p>Update Product Status From Enabled To Disabled.</p>
     * <p>Preconditions:</p>
     * <p>1.A simple product is created.</p>
     * <p>Steps:</p>
     * <p>1. Open this product.</p>
     * <p>2. Change status from enabled to disabled.</p>
     * <p>3. Click 'Save' button.</p>
     * <p>Expected result:</p>
     * <p>WebStoreOfferUpdated message is sent out.</p>
     * <p>offerState in the message is SUSPENDED.</p>
     *
     * @test
     */
    public function updateProductStatusFromEnabledToDisabled()
    {
        // Update Product
        $updateProductData = array('general_status' => 'Disabled');
        $searchProductData = array('product_sku' =>  $this->_productSku);
        $this->_updateProduct( $searchProductData, $updateProductData);

        $expectedMsgs = array(
            array(
                'topic' => WebStore::OFFER_UPDATED,
                'message.offer.offerState' => 'SUSPENDED',
            )
        );
        $msgs = $this->_get2dXMessages();

        //Verify
        $this->verifyXMessage($expectedMsgs, $msgs);

        return $this->_productSku;
    }

    /**
     * <p>Update Product Status From Enabled To Disabled.</p>
     * <p>Preconditions:</p>
     * <p>1.A simple product is created.</p>
     * <p>2.The product status is disabled.</p>
     * <p>Steps:</p>
     * <p>1. Open this product.</p>
     * <p>2. Change status from disabled to enabled.</p>
     * <p>3. Click 'Save' button.</p>
     * <p>Expected result:</p>
     * <p>WebStoreOfferUpdated message is sent out.</p>
     * <p>offerState in the message is PUBLISHED.</p>
     *
     * @depends updateProductStatusFromEnabledToDisabled
     * @test
     */
    public function updateProductStatusFromDisabledToEnabled($sku)
    {
        // Update Product
        $updateProductData = array('general_status' => 'Enabled');
        $searchProductData = array('product_sku' =>  $sku);
        $this->_updateProduct( $searchProductData, $updateProductData);

        $expectedMsgs = array(
            array(
                'topic' => WebStore::OFFER_UPDATED,
                'message.offer.offerState' => 'PUBLISHED',
            )
        );
        $msgs = $this->_get2dXMessages();

        //Verify
        $this->verifyXMessage($expectedMsgs, $msgs);
    }

    /**
     * <p>Update Product Visibility To Not Visible</p>
     * <p>Preconditions:</p>
     * <p>1.A simple product is created.</p>
     * <p>Steps:</p>
     * <p>1. Open this product.</p>
     * <p>2. Change visibility from visible to not visible.</p>
     * <p>3. Click 'Save' button.</p>
     * <p>Expected result:</p>
     * <p>WebStoreOfferUpdated message is sent out.</p>
     * <p>offerState in the message is SUSPENDED.</p>
     *
     * @test
     */
    public function updateProductVisibilityToNotVisible()
    {
        // Update Product
        $updateProductData = array('general_visibility' => 'Not Visible Individually');
        $searchProductData = array('product_sku' =>  $this->_productSku);
        $this->_updateProduct( $searchProductData, $updateProductData);

        $expectedMsgs = array(
            array(
                'topic' => WebStore::OFFER_UPDATED,
                'message.offer.offerState' => 'SUSPENDED',
            )
        );
        $msgs = $this->_get2dXMessages();

        //Verify
        $this->verifyXMessage($expectedMsgs, $msgs);

        return $this->_productSku;
    }

    /**
     * <p>Update Product Visibility To Visible</p>
     * <p>Preconditions:</p>
     * <p>1.A simple product is created.</p>
     * <p>2.The product is not visible.</p>
     * <p>Steps:</p>
     * <p>1. Open this product.</p>
     * <p>2. Change visibility from not visible to visible.</p>
     * <p>3. Click 'Save' button.</p>
     * <p>Expected result:</p>
     * <p>WebStoreOfferUpdated message is sent out.</p>
     * <p>offerState in the message is PUBLISHED.</p>
     *
     * @depends updateProductVisibilityToNotVisible
     * @test
     */
    public function updateProductVisibilityToVisible($sku)
    {
        // Update Product
        $updateProductData = array('general_visibility' => 'Catalog, Search');
        $searchProductData = array('product_sku' =>  $sku);
        $this->_updateProduct( $searchProductData, $updateProductData);

        $expectedMsgs = array(
            array(
                'topic' => WebStore::OFFER_UPDATED,
                'message.offer.offerState' => 'PUBLISHED',
            )
        );
        $msgs = $this->_get2dXMessages();

        //Verify
        $this->verifyXMessage($expectedMsgs, $msgs);
    }
}