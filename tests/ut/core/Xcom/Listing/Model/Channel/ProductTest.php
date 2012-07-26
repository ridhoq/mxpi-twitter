<?php
/**
 * Test listing product
 */
class Xcom_Listing_Model_Channel_ProductTest extends Xcom_TestCase
{
    /**
     * Test object.
     *
     * @var Xcom_Listing_Model_Channel_Product
     */
    protected $_object;

    /**
     * Test prepareDataByProducts method.
     *
     * @return void
     */
    public function setUp()
    {
        parent::setUp();
        $this->_checkConnection = true;
        $this->_object = new Xcom_Listing_Model_Channel_Product();
    }

    public function testGetProductAttributeSets()
    {
        $expectedValue = 11;
        $productIds = array(1,2,3);
        $resource = $this->mockResource('xcom_listing/channel_product', array('getProductAttributeSets'));
        $resource->expects($this->once())
            ->method('getProductAttributeSets')
            ->with($this->equalTo($productIds))
            ->will($this->returnValue(array($expectedValue)));

        $result = $this->_object->getProductAttributeSets($productIds);
        $this->assertEquals($expectedValue, $result[0]);
    }

    public function testUpdateRelations()
    {
        $channelId = 'test_channel_id';
        $productId = 'test_product_id';
        $marketItemId = 'test_market_item_id';
        $status = 'test_status';
        $resource = $this->mockResource('xcom_listing/channel_product', array('updateRelations'));
        $resource->expects($this->once())
            ->method('updateRelations')
            ->with(
                $this->equalTo($channelId),
                $this->equalTo($productId),
                $this->equalTo($marketItemId),
                $this->equalTo('test_status')
            )
            ->will($this->returnValue($resource));

        $result = $this->_object->updateRelations($channelId, $productId, $marketItemId, $status);
        $this->assertInstanceOf(get_class($this->_object), $result);
    }

    public function testGetProductsMarketId()
    {
        $expectedChannelId = 11;
        $productIds = array(1,2,3);
        $expectedResult = 100500;
        $resource = $this->mockResource('xcom_listing/channel_product', array('getProductMarketIds'));
        $resource->expects($this->once())
            ->method('getProductMarketIds')
            ->with($this->equalTo($expectedChannelId), $this->equalTo($productIds))
            ->will($this->returnValue($expectedResult));
        $actualResult = $this->_object->getProductMarketIds($expectedChannelId, $productIds);

        $this->assertEquals($expectedResult, $actualResult);
    }

    public function prepareGetListingProductsMock($product, $methods = array())
    {
        $methods = array_merge(array('getListingProducts'), $methods);
        $objectMock = $this->mockModel('xcom_listing/channel_product', $methods);
        $objectMock->expects($this->any())
            ->method('getListingProducts')
            ->will($this->returnValue(array(1 => $product)));
        $this->_object = $objectMock;
    }

    public function testGetPublishedListingId()
    {
        $expectedChannelId = 11;
        $productIds = array(1,2,3);
        $expectedResult = 100500;
        $resource = $this->mockResource('xcom_listing/channel_product', array('getPublishedListingId'));
        $resource->expects($this->once())
            ->method('getPublishedListingId')
            ->with($this->equalTo($expectedChannelId), $this->equalTo($productIds))
            ->will($this->returnValue($expectedResult));
        $actualResult = $this->_object->getPublishedListingId($expectedChannelId, $productIds);

        $this->assertEquals($expectedResult, $actualResult);
    }

    public function testGetValidListingIdZero()
    {
        $expectedChannelId = 11;
        $productIds = array(1,2,3);
        $expectedResult = 100500;
        $methods = array('getPublishedListingId', 'getPublishedProductIds');
        $resource = $this->mockResource('xcom_listing/channel_product', $methods);
        $resource->expects($this->once())
            ->method('getPublishedListingId')
            ->will($this->returnValue($expectedResult));
        $resource->expects($this->once())
            ->method('getPublishedProductIds')
            ->will($this->returnValue(array(1,2)));

        $actualResult = $this->_object->getValidListingId($expectedChannelId, $productIds);

        $this->assertEquals(0, $actualResult);
    }

    public function testGetValidListingIdZero2()
    {
        $productIds = array(1,2);
        $expectedResult = 100500;
        $methods = array('getPublishedListingId', 'getPublishedProductIds');
        $resource = $this->mockResource('xcom_listing/channel_product', $methods);
        $resource->expects($this->once())
            ->method('getPublishedListingId')
            ->will($this->returnValue($expectedResult));
        $resource->expects($this->once())
            ->method('getPublishedProductIds')
            ->will($this->returnValue(array(1,2,3)));

        $actualResult = $this->_object->getValidListingId('test_channel', $productIds);

        $this->assertEquals(0, $actualResult);
    }

    public function testGetValidListingId()
    {
        $productIds = array(1,3,4,2);
        $expectedResult = 100500;
        $methods = array('getPublishedListingId', 'getPublishedProductIds');
        $resource = $this->mockResource('xcom_listing/channel_product', $methods);
        $resource->expects($this->once())
            ->method('getPublishedListingId')
            ->will($this->returnValue($expectedResult));
        $resource->expects($this->once())
            ->method('getPublishedProductIds')
            ->will($this->returnValue(array(1,2,3,4)));

        $actualResult = $this->_object->getValidListingId('test_channel', $productIds);

        $this->assertEquals($expectedResult, $actualResult);
    }

    public function testSaveProducts()
    {
        $products = array(
            new Varien_Object(array('id' => 22)),
            new Varien_Object(array('id' => 23)),
            new Varien_Object(array('id' => 24)),
        );

        $listingId = 33;
        $channelId = 34;
        $date = now();

        $mockObject = $this->getMock(get_class($this->_object), array('getDate'));
        $mockObject->expects($this->exactly(1))
            ->method('getDate')
            ->will($this->returnValue($date));

        $mockObject->setListingProducts($products);
        $mockObject->setListingId($listingId);
        $mockObject->setChannelId($channelId);

        $resource = $this->mockResource('xcom_listing/channel_product', array('saveRelations'));
        foreach ($products as $i => $product) {
            $resource->expects($this->at($i))
                ->method('saveRelations')
                ->with(
                    $this->equalTo($channelId),
                    $this->equalTo($product->getId()),
                    $this->equalTo(
                        array(
                            'listing_status'    => Xcom_Listing_Model_Channel_Product::STATUS_PENDING,
                            'created_at'        => $date,
                            'listing_id'        => $listingId,
                        )
                    )
                )
                ->will($this->returnSelf());
        }
        $result = $mockObject->saveProducts();
        $this->assertInstanceOf(get_class($mockObject), $result);
    }
}
