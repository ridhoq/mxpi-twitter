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

class Xcom_Chronicle_Model_Message_Product_Search_Outbound extends Xcom_Xfabric_Model_Message_Request
{

    protected function _construct()
    {
        parent::_construct();
        $this->_topic = PIM::SEARCH_PRODUCT;
        $this->_schemaRecordName = 'SearchProduct';
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
            'query' => array(
                'fields'            => null,
                'predicates'        => null,
                'ordering'          => null,
                'numberItems'       => null,
                'startItemIndex'    => null,
                'numberItemsFound'  => null
            ),
            'locales'               => null
        );

        if ($dataObject != null) {
            $data = array(
                'query' => array(
                    'fields'            => $dataObject["fields"],
                    'predicates'        => $dataObject["predicates"],
                    'ordering'          => $dataObject["ordering"],
                    'numberItems'       => $dataObject["numberItems"],
                    'startItemIndex'    => $dataObject["startItemIndex"],
                    'numberItemsFound'  => $dataObject["numberItemsFound"]
                ),
                'locales'               => null
            );
        }


        $this->setMessageData($data);
        return parent::_prepareData($dataObject);
    }
};

/**
 * Search Products
 *
 * @package     selenium
 * @subpackage  tests
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class Xcom_Chronicle_Message_Product_SearchTest extends Xcom_Chronicle_TestCase
{
    const MINIMUM_PRODUCT_COUNT = 20;
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
     * Search Product
     *
     * @dataProvider searchProductDataProvider
     * @test
     *
     */
    public function searchProduct($action, $options, $expectedMsgs)
    {
        $this->markTestIncomplete();
        if ($this->_getProductCount() > 5000) {
            $this->markTestSkipped("Skipping test in case there are too many products initially");
        }

        /* issue the search */
        $requester = new Xcom_Chronicle_Model_Message_Product_Search_Outbound();

        $this->_mockRequest( $requester, $action, $options );

        $msgs = $this->_get2dXMessages();

        //Verify
        $this->verifyXMessage($expectedMsgs, $msgs);

    }

    public function searchProductDataProvider()
    {
        if ($this->_getProductCount() > self::MINIMUM_PRODUCT_COUNT) {
            $initialProductCount = $this->_getProductCount();
        } else {
            $initialProductCount = self::MINIMUM_PRODUCT_COUNT;
        }       

        $action1 = array( 'topic' => Pim::SEARCH_PRODUCT,
                         'version' => SCHEMA_VERSION
                        );
        $options1 = array();
        $expectedMsgs1 = array( 0 => array(  "topic" => Pim::SEARCH_PRODUCT_SUCCEEDED,
                                             "message.query.numberItems" => null,
                                             "message.query.startItemIndex" => null,
                                             "message.query.numberItemsFound.long" => $initialProductCount,            
                                             "message.products." . ($initialProductCount-1) . ".id" => "!\d+!"
                                          )
                               );
        $options2 = array( "numberItems" => 1,
                           "startItemIndex" => 0
                         );
        $expectedMsgs2 = array( 0 => array(  "topic" => Pim::SEARCH_PRODUCT_SUCCEEDED,
                                             "message.query.numberItems.long" => 1,
                                             "message.query.startItemIndex.long" => 0,
                                             "message.query.numberItemsFound.long" => $initialProductCount,
                                             "message.products.0.id" => "!\d+!"
                                          )
                               );
        $options3 = array( "numberItems" => 10,
                           "startItemIndex" => 5
                         );
        $expectedMsgs3 = array( 0 => array(  "topic" => Pim::SEARCH_PRODUCT_SUCCEEDED,
                                             "message.query.numberItems.long" => 10,
                                             "message.query.startItemIndex.long" => 5,
                                             "message.query.numberItemsFound.long" => $initialProductCount,
                                             "message.products.9.id" => "!\d+!"
                                          )
                               );  
        $options4 = array( "numberItems" => 1,
                           "startItemIndex" => 0,
                           "fields" => array("field1", "field2")
                         );
        $expectedMsgs4 = array( 0 => array(  "topic" => Pim::SEARCH_PRODUCT_FAILED,
                                             "message.query.fields.array.0" => "field1",
                                             "message.query.fields.array.1" => "field2",
                                             "message.query.numberItems.long" => 1,
                                             "message.query.startItemIndex.long" => 0,
                                             "message.errors.0.code" => -1,
                                             "message.errors.0.message.string" => "Unsupported query parameter: fields"                                             
                                          )
                               );
        $options5 = array( "numberItems" => 1,
                           "startItemIndex" => 0,
                           "predicates" => array("predicate1" => array("field" => "field", "operator" => "EQUALS", "values" => array("value")), 
                                                 "predicate2" => array("field" => "field", "operator" => "EQUALS", "values" => array("value")))
                         );
        $expectedMsgs5 = array( 0 => array(  "topic" => Pim::SEARCH_PRODUCT_FAILED,
                                             "message.query.numberItems.long" => 1,
                                             "message.query.startItemIndex.long" => 0,
                                             "message.errors.0.code" => -1,
                                             "message.errors.0.message.string" => "Unsupported query parameter: predicates"                                             
                                          )
                               );
        $options6 = array( "numberItems" => 1,
                           "startItemIndex" => 0,
                           "ordering" => array("order1" => array("field" => "field1", "sortOrder" => "ASCENDING"),
                                               "order2" => array("field" => "field2", "sortOrder" => "ASCENDING"))
                         );
        $expectedMsgs6 = array( 0 => array(  "topic" => Pim::SEARCH_PRODUCT_FAILED,
                                             "message.query.numberItems.long" => 1,
                                             "message.query.startItemIndex.long" => 0,
                                             "message.errors.0.code" => -1,
                                             "message.errors.0.message.string" => "Unsupported query parameter: ordering"                                             
                                          )
                               );        
        return array(
            "Search Product No Query" => array($action1, $options1, $expectedMsgs1),
            "Search Product Start at 0, numberItems 1" => array($action1, $options2, $expectedMsgs2),
            "Search Product Start at 5, numberItems 10" => array($action1, $options3, $expectedMsgs3),
            "Search Product with fields" => array($action1, $options4, $expectedMsgs4),
            "Search Product with predicates" => array($action1, $options5, $expectedMsgs5),
            "Search Product with ordering" => array($action1, $options6, $expectedMsgs6),
        );
    }

    /**
     * Issues search product request and page through.
     *
     * @test
     */
    public function searchLargeProductsWithClientSidePagination()
    {
        $this->markTestIncomplete();
        $iterationCount = 5;
        $initialProductCount = $this->_getProductCount();
        $requester = new Xcom_Chronicle_Model_Message_Product_Search_Outbound();
        
        $action = array( 'topic' => Pim::SEARCH_PRODUCT,
                         'version' => SCHEMA_VERSION
                        );
        
        $lastPageProductIds = array();
        $latestMessageId = $this->_getLatestXMessageId(); 
        for ($offset = 0; $offset < 20; $offset += $iterationCount) {

            /* issue the search */
            $options = array( "numberItems" => $iterationCount,
                              "startItemIndex" => $offset
                         );
            $this->_mockRequest( $requester, $action, $options );

            $expectedMsgs = array( 0 => array(  "topic" => Pim::SEARCH_PRODUCT_SUCCEEDED,
                                             "message.query.numberItems.long" => $iterationCount,
                                             "message.query.startItemIndex.long" => $offset,
                                             "message.query.numberItemsFound.long" => $initialProductCount,
                                             "message.products.4.id" => "!\d+!"
                                          )
                                    );                
            $msgs = $this->_get2dXMessages($latestMessageId + 1);
            //Verify
            $this->verifyXMessage($expectedMsgs, $msgs);
            $curPageProductIds= array();
            for($index = 0; isset($msgs[0]["message.products.$index.id"]); $index++){
                $curPageProductIds[] = $msgs[0]["message.products.$index.id"];
            }
            $this->assertEquals(5, count($curPageProductIds), "There should be 5 products in the search result");
            if (isset($lastPageProductIds)){
                foreach($curPageProductIds as $productId){
                    $this->assertFalse(in_array($productId, $lastPageProductIds), "$productId reappears in new page");
                }
            }

            $latestMessageId = $this->_getLatestXMessageId(); 
            $lastPageProductIds = $curPageProductIds;
        }
    }
}