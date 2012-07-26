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
 * @category    Xcom
 * @package     Xcom_Listing
 * @copyright   Copyright (c) 2010 Magento Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

class Xcom_Listing_Message_Listing_Create_RequestTest extends Xcom_TestCase
{
    protected $_productOne = null;
    protected $_productTwo = null;
    /**
     * @var Xcom_Listing_Model_Message_Listing_Create_Request
     */
    protected $_object     = null;

    public function setUp()
    {
        parent::setUp();
        $this->_object = Mage::helper('xcom_xfabric')
          ->getMessage('listing/create');

        $this->_createProducts();
    }

    /**
     * @dataProvider providerListings
     */
    public function testListings($policyData, $expectedMarketSpecifics)
    {
        $this->_productOne->setListingQty($this->_productOne->getQtyForTest());
        $this->_productOne->setListingPrice($this->_productOne->getPriceForTest());

        $this->_productTwo->setListingQty($this->_productTwo->getQtyForTest());
        $this->_productTwo->setListingPrice($this->_productTwo->getPriceForTest());

        $this->_testGetListingAttributes();


        $mockObject = $this->getMock('Xcom_Listing_Model_Message_Listing_Create_Request_Fixture',
            array('getListingAttributes', 'getMappingOptions'));
        $mockObject
            ->expects($this->any())
            ->method('getListingAttributes')
            ->will($this->returnValue(array(
                'entity_id' => 'entity_id',
                'imageURL' => 'image',
                'test_attribute' => 'test_attribute',
                'sku' => 'sku',
                'price' => 'price'
            )));
        $mockObject->expects($this->any())
            ->method('getMappingOptions')
            ->will($this->returnValue(array('mapping_attribute_code' => 'mapping_attribute_value')));

        $dataObject = new Varien_Object(array(
            'products'     => array($this->_productOne, $this->_productTwo),
            'policy'       => $this->getMock('Xcom_Mmp_Model_Policy', array('save', 'load'), array($policyData)),
            'channel'      => new Varien_Object(
                array(
                    'store_id' => 0,
                    'name'     => 'test_channel',
                    'code'     => 'test_code'
                )
            )
        ));
        $this->_mockMarketSpecificsMessage();
        $this->_mockPrepareChannelHistoryMethod();
        $mockObject->_prepareData($dataObject);
        $data = $mockObject->getMessageData();

        $this->_testListingInformation($data);
        $this->_testPayment($data);
        $this->_testShipping($data);
        $this->_testProducts($data);
        $this->_testMarketSpecifics($data, $expectedMarketSpecifics);
        $this->_testCorrelationIdHeader($mockObject);
    }

    protected function _testCorrelationIdHeader($object)
    {
        $this->assertArrayHasKey($object->getCorrelationId(),
            array_flip($object->getHeaders()));
    }

    protected function _testGetListingAttributes()
    {
        $attributes = $this->_object->getListingAttributes();
        $this->assertArrayHasKey('sku', $attributes);
        $this->assertArrayHasKey('xProductTypeId', $attributes);
        $this->assertEquals(10, count($attributes));
    }

    protected function _mockMarketSpecificsMessage()
    {
        $messageMock = $this->getMock('Xcom_Listing_Model_Message_Listing_Specifics_Request',
            array('process', 'getMessageData'));
        $messageMock->expects($this->any())
            ->method('process')
            ->will($this->returnValue($messageMock));

        $messageMock->expects($this->any())
            ->method('getMessageData')
            ->will($this->returnValue(array('location' => 'CA, Los Angeles Test')));

        $helperMock = $this->mockHelper('xcom_xfabric', array('getMessage'));
        $helperMock->expects($this->any())
            ->method('getMessage')
            ->will($this->returnValue($messageMock));
    }

    protected function _testMarketSpecifics(&$data, $expected)
    {
        $marketSpecifics = $data['listings'][0]['embeddedMessage'];
        foreach ($expected as $name => $data) {
            if ($name == 'location') {
                $this->assertEquals($marketSpecifics['payload'][$name], 'CA, Los Angeles Test',
                    $name . ' in marketSpecifics is wrong');
            }
        }
    }

    protected function _mockPrepareChannelHistoryMethod()
    {
        $methods = array('setRequestBody', 'save', 'setCorrelationId', 'getCorrelationId');
        $logRequestMock = $this->mockModel('xcom_listing/message_listing_log_request', $methods);
        $logRequestMock->expects($this->once())
            ->method('setRequestBody')
            ->will($this->returnValue($logRequestMock));
        $logRequestMock->expects($this->exactly(1))
            ->method('save')
            ->will($this->returnValue($logRequestMock));
        $logRequestMock->expects($this->once())
            ->method('setCorrelationId')
            ->will($this->returnValue($logRequestMock));
        $logRequestMock->expects($this->any())
            ->method('getCorrelationId')
            ->will($this->returnValue('test_correlation_id'));

        $methods = array('addData', 'save');
        $channelHistoryMock = $this->mockModel('xcom_listing/channel_history', $methods);
        $channelHistoryMock->expects($this->any())
            ->method('addData')
            ->will($this->returnValue($channelHistoryMock));
    }

    protected function _testListingInformation(&$data)
    {
        $listing = $data['listings'][0];
        $this->assertEquals($listing['title'],    'test_name', "Wrong Listing Title");
        $this->assertEquals($listing['subTitle'], null, "Wrong Listing Code");
    }

    protected function _testShipping(&$data)
    {
        $this->assertEquals(null, $data['listings'][0]['shipping']);
    }

    protected function _testPayment(&$data)
    {
        $this->assertNull($data['listings'][0]['payment']);
    }


    protected function _testProducts(&$data)
    {
        $listings = $data['listings'];
        $this->assertTrue(count($listings) == 2, "Message data has wrong count");
        $this->_testConcreteProduct($listings[0], $this->_productOne);
        $this->_testConcreteProduct($listings[1], $this->_productTwo);
    }

    protected function _testConcreteProduct(&$data, $product)
    {
        $productData = $data['product'];

        $this->assertEquals($productData['sku'], $product->getSku(), "Product SKU is wrong");
        $this->assertEquals($productData['test_attribute'], $product->getTestAttribute(), "TestAttribute is wrong");
        $this->assertEquals($productData['price'], $product->getPrice(), "Price in the product array is wrong");
        $this->assertEquals($data['quantity'], $product->getQtyForTest(), "Qty in the product array is wrong");
        //call additional test for _getImageUrls for every product
        $testImageMethodName = '_testGetImageUrlForProduct' . $product->getId();
        $this->$testImageMethodName($productData['imageURL']);

        $this->assertEquals(
            $data['price'],
            array(
                'amount'   => (double)$product->getPriceForTest(),
                'code'     => 'TEST',
            ),
            "Product PRICE data is wrong"
        );

    }

    protected function _testGetImageUrlForProduct1($productImageUrls)
    {
        //product 1 has maximum allowed images
        $this->assertEquals(count($productImageUrls), Xcom_Listing_Model_Message_Listing_Create_Request::MAX_IMAGES_COUNT,
            "count of Image urls is wrong for product 1");
        //first image is base
        $this->assertEquals(
            $productImageUrls[0]['locationURL'],
            (string)Mage::helper('catalog/image')->init($this->_productOne, 'image'), "Image url is wrong");
        $this->assertEquals(
            $productImageUrls[1]['locationURL'],
            $this->_productOne->getMediaGalleryImages()->getItemById(0)->getUrl(), "Image url is wrong");
        $this->assertNull($productImageUrls[0]['purpose'], "image purpose is wrong for product 1");
        $this->assertNull($productImageUrls[1]['purpose'], "image purpose is wrong for product 1");
        $this->assertNull($productImageUrls[2]['purpose'], "image purpose is wrong for product 1");

    }

    protected function _testGetImageUrlForProduct2($productImageUrls)
    {
        $this->assertEquals($productImageUrls, null, "Image urls is wrong for product 2");
    }

    protected function _mockCollection()
    {
        $methods = array('addFieldToFilter', 'addPriceData', 'load',
                         'addAttributeToSelect', 'joinProductQty');
        $collection = $this->getMock('Varien_Data_Collection', $methods);
        Mage::registerMockResourceModel('xcom_listing/product_collection', $collection);

        foreach(array($this->_productOne, $this->_productTwo) as $ob)
        {
            $collection->addItem($ob);
        }

        foreach ($methods as $method) {
            $collection->expects($this->any())
              ->method($method)
              ->will($this->returnValue($collection));
        }

        return $collection;
    }

    public function providerListings()
    {
        $policyData[0] = array(
            'id'   => 'testId',
            'name' => 'test',
            'currency'  => 'TEST',
            'payment_name' => 'AMEX,CHECK',
            'shippings' => array(
                new Varien_Object(
                    array(
                        'rate_type'      => 'FLAT',
                        'international'  => 0,
                        'service_name'   => 'test_shipping_service'
                    )
                ),
                new Varien_Object(
                    array(
                        'rate_type'      => 'FLAT',
                        'international'  => 0,
                        'service_name'   => 'test_shipping_service2'
                    )
                )
            ),
            'location'  => 'CA, Los Angeles'
        );
        $policyData[]   = array_merge($policyData[0], array('location'=>'91234-0345'));

        return array(
            array(
                'policy' => $policyData[0],
                'expected_embeddedMessage' => array('location' => 'CA, Los Angeles', 'postalCode' => '')),
            array(
                'policy' => $policyData[1],
                'expected_embeddedMessage' => array('location' => null, 'postalCode' => '91234-0345'))
        );
    }


    protected function _createProducts()
    {
        $media_gallery_images   = new Varien_Data_Collection();
        for ($i=0; $i<=Xcom_Listing_Model_Message_Listing_Create_Request::MAX_IMAGES_COUNT; $i++) {
            $media_gallery_images->addItem(new Varien_Object(array('url'=>'test_url_' . $i)));
        }

        $this->_productOne = new Mage_Catalog_Model_Product(
            array(
                'sku'                   => 'test_one',
                'name'                  => 'test_name',
                'id'                    => 1,
                'price'                 => rand(1000, 99999),
                'price_for_test'        => rand(1000, 99999),
                'isbn'                  => 'isbn_test',
                'id_type'               => 'isbn',
                'id_value'              => 'isbn_test',
                'entity_id'             => 1,
                'image'                 => 'base_image_url_1',
                'test_attribute'        => 'test_1',
                'qty'                   => rand(1000, 99999),
                'qty_for_test'          => rand(1000, 99999),
                //'image_url'             => 'test_image_url',
                'media_gallery_images'  => $media_gallery_images
            )
        );

        $this->_productTwo = new Mage_Catalog_Model_Product(
            array(
                'sku'                   => 'test_two',
                'name'                  => 'test_name',
                'id'                    => 2,
                'price'                 => rand(10000, 999999),
                'price_for_test'        => rand(10000, 999999),
                'upc'                   => 'upc_test',
                'id_type'               => 'upc',
                'id_value'              => 'upc_test',
                'entity_id'             => 2,
                'test_attribute'        => 'test_2',
                'qty'                   => rand(1000, 99999),
                'qty_for_test'          => rand(1000, 99999),
                'thumbnail'             => 'thumbnail_url',
                'media_gallery_images'  => array()
            )
        );

    }

    /**
     * Test prepareListingAttributeOptions method.
     *
     * @param array $mappingAttributeOptions
     * @param array $listingAttributeOptions
     * @return void
     * @dataProvider listingAttributeOptionProvider
     */
//    public function testPrepareListingAttributeOptions($mappingAttributeOptions, $listingAttributeOptions)
//    {
//        $preparedListingAttributeOptions = $this->_object->prepareListingAttributeOptions($mappingAttributeOptions);
//        $this->assertEquals($listingAttributeOptions, $preparedListingAttributeOptions);
//    }

    public function listingAttributeOptionProvider()
    {
        return array(
            array(array(), array()),
            array(array('test_3' => array('new')), array(array('name' => 'test_3', 'value' => array('new')))),
            array(
                array('test_3' => array(3 => 'new', 5 => 'used')),
                array(array('name' => 'test_3', 'value' => array('new', 'used')))
            ),
        );
    }

//    public function testGetMappingOptions()
//    {
//        $mockAttributeModel = $this->mockModel('xcom_mapping/mapper', array('getMappingOptions'));
//        $mockAttributeModel->expects($this->any())
//            ->method('getMappingOptions')
//            ->will($this->returnValue(array('mapping_attribute_code' => 'mapping_attribute_value')));
//
//        $result = $this->_object->getMappingOptions();
//        $this->assertArrayHasKey('mapping_attribute_code', $result);
//    }

//    public function testPrepareProductOptions()
//    {
//        $objectFixture = new Xcom_Mmp_Model_Message_Listing_Create_Request_Fixture();
//
//        $mockAttributeModel = $this->mockModel('xcom_mapping/mapper', array('getMappingOptions'));
//        $mockAttributeModel->expects($this->any())
//            ->method('getMappingOptions')
//            ->will($this->returnValue(array('Condition' => array('value_1', 'value_2'))));
//
//        $result = $objectFixture->prepareProductOptions($this->_productOne);
//        $this->assertEmpty($result['condition']);
//    }
//
//    /**
//     * @expectedException Mage_Core_Exception
//     * @return void
//     */
//    public function testPrepareDataNoPolicyOptionException()
//    {
//        $this->_mockObjectForProcessMethod();
//        $this->_object->process(new Varien_Object());
//    }
//
//    /**
//     * @expectedException Mage_Core_Exception
//     * @return void
//     */
//    public function testPrepareDataNoPolicyOptionException2()
//    {
//        $this->_mockObjectForProcessMethod();
//        $this->_object->process(new Varien_Object(array('policy' => 'string')));
//    }
//
//    /**
//     * @expectedException Mage_Core_Exception
//     * @return void
//     */
//    public function testPrepareDataNoChannelOptionException()
//    {
//        $this->_mockObjectForProcessMethod();
//        $this->_object->process(new Varien_Object(array('policy' => new Varien_Object())));
//    }
//
//
//    /**
//     * @expectedException Mage_Core_Exception
//     * @return void
//     */
//    public function testPrepareDataNoChannelOptionException2()
//    {
//        $this->_mockObjectForProcessMethod();
//        $this->_object->process(new Varien_Object(array('policy' => new Varien_Object(), 'channel' => 'string')));
//    }
//
//    /**
//     * @expectedException Mage_Core_Exception
//     */
//    public function testPrepareDataWithNonObject()
//    {
//        $this->_mockObjectForProcessMethod();
//        $this->_object->process('string');
//    }

    protected function _mockObjectForProcessMethod()
    {
        $this->mockModel('xcom_xfabric/encoder_json');
        $this->_object = $this->getMock(get_class($this->_object), array('_initSchema'));
    }
}


class Xcom_Listing_Model_Message_Listing_Create_Request_Fixture extends Xcom_Listing_Model_Message_Listing_Create_Request
{
    public function prepareProductOptions($product)
    {
        return parent::_prepareProductOptions($product);
    }
}

