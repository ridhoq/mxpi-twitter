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

    use Xcom_Chronicle_XMessages_Pim as Pim;

    const SCHEMA_VERSION = "1.0.0";
    
    class Xcom_Chronicle_Model_Message_Product_Lookup_Outbound extends Xcom_Xfabric_Model_Message_Request
    {

        protected function _construct()
        {
            parent::_construct();
            $this->_topic = PIM::LOOKUP_PRODUCT;
            $this->_schemaRecordName = 'LookupProduct';
            $this->_schemaFile = 'Xcom_Chronicle/ProductInformationManagementCapability.avpr';
            $this->_schemaVersion  = SCHEMA_VERSION;
        }


        /**
         * @param null|Varien_Object $dataObject
         * @return Xcom_Xfabric_Model_Message_Request
         */
        public function _prepareData(Varien_Object $dataObject = null)
        {
            $data = array(
                'ids'       => null,
                'filter'    => null,
                'locales'   => null
            );

            if ($dataObject != null) {
                $data = array(
                    'ids'       => $dataObject['ids'],
                    'filter'    => $dataObject['filter'],
                    'locales'   => $dataObject['locales']
                );
            }
            $this->setMessageData($data);
            return parent::_prepareData($dataObject);
        }
    };

    /**
     * Search Orders
     *
     * @package     selenium
     * @subpackage  tests
     * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
     */
class Xcom_Chronicle_Message_Product_GetTest extends Xcom_Chronicle_TestCase
{
    const MINIMUM_PRODUCT_COUNT = 5;
    /**
     * <p>Preconditions:</p>
     * <p>Log in to Backend.</p>
     */
    public function setUpBeforeTests()
    {
        $initialProductCount = $this->_getProductCount();
        if ($initialProductCount < self::MINIMUM_PRODUCT_COUNT) {
            $this->loginAdminUser();
//            var_dump("createProducts()");
//            $this->_createProducts(10 - $initialProductCount);
            for($i = $initialProductCount; $i < self::MINIMUM_PRODUCT_COUNT; $i++) {
                $this->_createSimpleProduct();
            }
            $initialProductCount = $i;
        }
    }
    
    protected function assertPreConditions()
    {
        parent::assertPreConditions();
        $this->addParameter('id', '0');
    }

    /**
     * Lookup Product
     *
     * @dataProvider lookupProductDataProvider
     * @test
     *
     */
    public function lookupProduct($action, $options, $expectedMsgs)
    {        
        $this->markTestIncomplete();
        /* issue the lookup */
        $requester = new Xcom_Chronicle_Model_Message_Product_Lookup_Outbound();
        $this->_mockRequest( $requester, $action, $options );
        
        sleep(1);

        $msgs = $this->_get2dXMessages();

        //Verify
        $this->verifyXMessage($expectedMsgs, $msgs);
    }
    
    public function lookupProductDataProvider()
    {
        $products = $this->_getProducts(2, rand(0, 3));
        $product1 = $products->getFirstItem();
        $product2 = $products->getLastItem();
        $action1 = array( 'topic' => Pim::LOOKUP_PRODUCT,
                         'version' => SCHEMA_VERSION
                        );
        $options1 = array("ids" => array(array("value" => $product1->getSku(), "type" => "SKU")));
        $options2 = array("ids" => array(array("value" => $product1->getIdBySku($product1->getSku()), "type" => "PRODUCT_ID")));
        $expectedMsgs1 = array( 0 => array(  "topic" => Pim::LOOKUP_PRODUCT_SUCCEEDED,
                                             "message.products.0.skuList.array.0.productId.string" => $product1->getIdBySku($product1->getSku()),
                                             "message.products.0.skuList.array.0.sku" => $product1->getSku(),
                                          )
                               );
        $options3 = array("ids" => array(array("value" => $product1->getSku(), "type" => "SKU"),
                                         array("value" => $product2->getIdBySku($product2->getSku()), "type" => "PRODUCT_ID"),
                                        ),
                        );
        $expectedMsgs3 = array( 0 => array(  "topic" => Pim::LOOKUP_PRODUCT_SUCCEEDED,
                                             "message.products.0.skuList.array.0.productId.string" => $product1->getIdBySku($product1->getSku()),
                                             "message.products.0.skuList.array.0.sku" => $product1->getSku(),
                                             "message.products.1.skuList.array.0.productId.string" => $product2->getIdBySku($product2->getSku()),
                                             "message.products.1.skuList.array.0.sku" => $product2->getSku(),            
                                          )
                        );
        $options4 = array("ids" => array(array("value" => "unknown_sku", "type" => "SKU")));
        $expectedMsgs4 = array( 0 => array(  "topic" => Pim::LOOKUP_PRODUCT_FAILED,
                                             "message.errors.0.code" => "-1",
                                             "message.errors.0.message.string" => "Product not found",
                                          ));
        $options5 = array("ids" => array(array("value" => "unknown_product_id", "type" => "PRODUCT_ID")));

        return array(
            "Lookup Product w/ Sku" => array($action1, $options1, $expectedMsgs1),
            "Lookup Product w/ Product_Id" => array($action1, $options2, $expectedMsgs1),
            "Lookup Product w/ Sku and Product_Id" => array($action1, $options3, $expectedMsgs3),
            "Lookup Product w/ unknown Sku" => array($action1, $options4, $expectedMsgs4),
            "Lookup Product w/ unknown product id" => array($action1, $options5, $expectedMsgs4),
        );
    }
}