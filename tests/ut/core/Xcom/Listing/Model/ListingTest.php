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
class Xcom_Listing_Model_ListingTest extends Xcom_TestCase
{
    /**
     * @var Xcom_Listing_Model_Listing
     */
    protected $_object     = null;
    protected $_instanceOf = 'Xcom_Listing_Model_Listing';
    protected $_productOne;
    protected $_productTwo;

    public function setUp()
    {
        parent::setUp();
        $this->_object = new Xcom_Listing_Model_Listing();
        $this->_productOne = new Varien_Object(
            array(
                'id'          => 1,
                'sku'         => 'Product1',
                'status'      => Mage_Catalog_Model_Product_Status::STATUS_ENABLED,
                'price'       => 100,
                'is_in_stock' => 1,
                'stock_item'  => new Varien_Object(
                    array(
                        'qty' => 10
                    )
                ),
            )
        );

        $this->_productTwo = new Varien_Object(
            array(
                'id'          => 2,
                'sku'         => 'Product2',
                'status'      => Mage_Catalog_Model_Product_Status::STATUS_ENABLED,
                'price'       => 200,
                'is_in_stock' => 1,
                'stock_item'  => new Varien_Object(
                    array(
                        'qty' => 20
                    )
                ),
            )
        );
    }

    public function tearDown()
    {
        $this->_object = null;
    }

    public function testPrepareProducts()
    {
        $product1 = new Varien_Object(array(
            'stock_item' => new Varien_Object(array('qty' => 55.123)),
            'price' => 55.178,
            'id'    => 1
        ));
        $product2 = new Varien_Object(array(
            'stock_item' => new Varien_Object(array('qty' => 4)),
            'price' => 5,
        ));
        $productIds = array(1,2);
        $productMock = $this->mockModel('catalog/product', array('load'));
        $productMock->expects($this->exactly(2))
            ->method('load')
            ->will($this->onConsecutiveCalls($product1, $product2));

        $channelProduct = $this->mockModel('xcom_listing/channel_product');
        $channelProduct->expects($this->once())
            ->method('getProductMarketIds')
            ->with($this->equalTo('test_channel_id'), $this->equalTo(array(1,2)))
            ->will($this->returnValue(array(1 => 10, 2 => 20)));

        $this->_object->setPriceType('magentoprice');
        $this->_object->setQtyValueType('percent');
        $this->_object->setQtyValue(10);
        $this->_object->setCategoryId(5);
        $this->_object->setChannelId('test_channel_id');
        $this->assertInstanceOf(get_class($this->_object), $this->_object->prepareProducts($productIds));

        $this->assertArrayHasKey('listing_price', $product1->getData());
        $this->assertArrayHasKey('listing_qty', $product1->getData());
        $this->assertArrayHasKey('listing_category_id', $product1->getData());
        $this->assertArrayHasKey('listing_market_item_id', $product1->getData());
    }

    protected function _prepareGetProductsMock($product, $methods = array())
    {
        $methods = array_merge(array('getProducts'), $methods);
        $objectMock = $this->mockModel('xcom_listing/listing', $methods);
        $objectMock->expects($this->any())
            ->method('getProducts')
            ->will($this->returnValue(array(1 => $product)));
        $this->_object = $objectMock;
    }

    public function testSend()
    {
        $channelId = 'test_channel_id';
        $options = array('policy' => 'testPolicy1', 'channel' => 'channel1');

        $channelProduct = $this->mockModel('xcom_listing/channel_product');
        $channelProduct->expects($this->once())
            ->method('getProductMarketIds')
            ->with($this->equalTo('test_channel_id'), $this->equalTo(array(1,2)))
            ->will($this->returnValue(array(1 => 10)));

        $methods = array('getChannelId', 'getProducts');
        $objectMock = $this->mockModel('xcom_listing/listing', $methods);

        $objectMock->expects($this->atLeastOnce())
            ->method('getChannelId')
            ->will($this->returnValue($channelId));
        $objectMock->expects($this->any())
            ->method('getProducts')
            ->will($this->returnValue(array(1 => $this->_productOne, 2 => $this->_productTwo)));

        $xfabricHelperMock = $this->mockHelper('xcom_xfabric', array('send'));
        $options['products'] = array(2 => $this->_productTwo);
        $xfabricHelperMock->expects($this->at(0))
            ->method('send')
            ->with(
            $this->equalTo('listing/create'),
            $this->equalTo($options))
            ->will($this->returnValue(null));

        $options['products'] = array(1 => $this->_productOne);
        $xfabricHelperMock->expects($this->at(1))
            ->method('send')
            ->with(
            $this->equalTo('listing/update'),
            $this->equalTo($options))
            ->will($this->returnValue(null));

        $this->assertInstanceOf(get_class($objectMock),
            $objectMock->send(array('policy' => 'testPolicy1', 'channel' => 'channel1')));
    }

    public function testSaveProducts()
    {
        $resourceMock = $this->mockResource('xcom_listing/channel_product', array('saveRelations'));
        $resourceMock->expects($this->exactly(2))
            ->method('saveRelations');

        $product = $this->mockModel('catalog/product', array('load'));
        $product->expects($this->exactly(2))
            ->method('load')
            ->will($this->onConsecutiveCalls($this->_productOne, $this->_productTwo));

        $productIds = array(1, 2);
        $this->_object->prepareProducts($productIds);
        $this->_object->setListingId('test_listing_id');
        $this->_object->saveProducts();
    }

    /**
     * @param $priceType
     * @param $priceValue
     * @param $priceValueType
     * @param $price
     * @param $expectedPrice
     * @dataProvider calculateProductPriceProvider
     */
    public function testCalculatePrice($priceType, $priceValue, $priceValueType, $price, $expectedPrice)
    {
        $this->_object = new Public_Xcom_Listing_Model_Listing();
        $this->_object->setData('price_type', $priceType);
        $this->_object->setData('price_value', $priceValue);
        $this->_object->setData('price_value_type', $priceValueType);
        $this->_productOne->setPrice($price);
        $actualPrice = $this->_object->calculatePrice($this->_productOne);
        $this->assertEquals($expectedPrice, $actualPrice);
    }

    public function calculateProductPriceProvider()
    {
        return array(
            array('markup', 20, 'percent', 100, 120),
            array('markup', 20, 'percent', 10.15, 12.18),
            array('markup', 3.25, 'abs', 10.15, 13.40),
            array('discount', 10.10, 'abs', 10.15, 0.05),
            array('discount', 10, 'percent', 10.15, 9.14),
            array('discount', 5, 'percent', 0.50, 0.48),
            array('magentoprice', null, null, 0.50, 0.50)
        );
    }

    /**
     * @param int $qtyValue
     * @param string $qtyValueType
     * @param int $qty
     * @param bool $isQtyDecimal
     * @param double $expectedQty
     *
     * @dataProvider calculateProductQtyProvider
     */
    public function testCalculateQty($qtyValue, $qtyValueType, $qty, $isQtyDecimal, $expectedQty)
    {
        $this->_object = new Public_Xcom_Listing_Model_Listing();
        $this->_object->setData('qty_value', $qtyValue);
        $this->_object->setData('qty_value_type', $qtyValueType);
        $this->_productOne->getStockItem()->setQty($qty);
        $this->_productOne->getStockItem()->setIsQtyDecimal($isQtyDecimal);
        $actualQuantity = $this->_object->calculateQty($this->_productOne);
        $this->assertEquals($expectedQty, $actualQuantity);
    }

    public function calculateProductQtyProvider()
    {
        return array(
            array(20, 'percent', 100, false, 20),
            array(20, 'percent', 14.563, true, 2),
            array(7.13, 'abs', 14.563, false, 7),
            array(20, 'percent', 17, true, 3),
            array(1, 'percent', 20, true, 0)
        );
    }
}

class Public_Xcom_Listing_Model_Listing extends Xcom_Listing_Model_Listing
{
    public function calculatePrice($product)
    {
        return $this->_calculatePrice($product);
    }

    public function calculateQty($product)
    {
        return $this->_calculateQty($product);
    }
}
