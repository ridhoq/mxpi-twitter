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
 * @package     Xcom_Ebay
 * @copyright   Copyright (c) 2010 Magento Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class Xcom_Ebay_Model_ObserverTest extends Xcom_TestCase
{
    /**
     * @var Xcom_Ebay_Model_Observer
     */
    protected $_object     = null;

    public function setUp()
    {
        $this->_checkConnection = true;
        parent::setUp();
        $this->_object = new Xcom_Ebay_Model_Observer();
    }

    public function tearDown()
    {
        $this->_object = null;
    }

    public function testInstanceOf()
    {
        $this->assertInstanceOf('Xcom_Ebay_Model_Observer', $this->_object);
    }

    /**
     * @param bool $configInventoryToZero
     * @param bool|null $qtyChanged
     * @param bool|null $qty
     * @param bool|null $configOutOfStock
     * @param bool|null $isInStockChanged
     * @param bool|null $isInStock
     * @param bool $callMethod
     *
     * @dataProvider  providerSynchronizeInventoryAfterStockChanges
     */
    public function testSynchronizeInventoryAfterStockChanges($configInventoryToZero, $qtyChanged, $qty,
                    $configOutOfStock, $isInStockChanged, $isInStock, $callMethod)
    {
        $helperMock     = $this->mockHelper('xcom_ebay', array('isUpdateChannelToZeroOnInventoryToZero',
                            'isUpdateChannelToZeroOnProductOutOfStock'));
        $helperMock->expects($this->once())
            ->method('isUpdateChannelToZeroOnInventoryToZero')
            ->will($this->returnValue($configInventoryToZero));
        if (is_null($configOutOfStock)) {
            $helperMock->expects($this->never())
                ->method('isUpdateChannelToZeroOnProductOutOfStock');
        } else {
            $helperMock->expects($this->once())
                ->method('isUpdateChannelToZeroOnProductOutOfStock')
                ->will($this->returnValue($configOutOfStock));
        }

        $stockItemMock  = $this->mockModel('catalogInventory/stock_item', array('dataHasChangedFor',
            'getIsInStock', 'getQty', 'getProductId'));
        if (is_null($qtyChanged) && is_null($isInStockChanged)) {
            $stockItemMock->expects($this->never())
                ->method('dataHasChangedFor');
        } elseif (!is_null($qtyChanged) && !is_null($isInStockChanged)) {
            $stockItemMock->expects($this->exactly(2))
                ->method('dataHasChangedFor')
                ->will($this->onConsecutiveCalls($qtyChanged, $isInStockChanged));
        } else {
            $stockItemMock->expects($this->once())
                ->method('dataHasChangedFor')
                ->with($this->equalTo(is_null($qtyChanged) ? 'is_in_stock': 'qty'))
                ->will($this->returnValue(is_null($qtyChanged) ? $isInStockChanged : $qtyChanged));
        }
        if (is_null($qty)) {
            $stockItemMock->expects($this->never())
                ->method('getQty');
        } else {
            $stockItemMock->expects($this->once())
                ->method('getQty')
                ->will($this->returnValue($qty));
        }
        if (is_null($isInStock)) {
            $stockItemMock->expects($this->never())
                ->method('getIsInStock');
        } else {
            $stockItemMock->expects($this->once())
                ->method('getIsInStock')
                ->will($this->returnValue($isInStock));
        }

        if ($callMethod) {
            $productId  = rand();
            $stockItemMock->expects($this->once())
                ->method('getProductId')
                ->will($this->returnValue($productId));
            $channelProductMock = $this->mockModel('xcom_listing/channel_product', array('getPublishedListingIds'));
            $channelProductMock->expects($this->once())
                ->method('getPublishedListingIds')
                ->with($this->equalTo(array($productId)))
                ->will($this->returnValue(array()));
        } else {
            $stockItemMock->expects($this->never())
                ->method('getProductId');
        }
        $this->_object->synchronizeInventoryAfterStockChanges(new Varien_Object(array(
            'event' => new Varien_Object(array('item' => $stockItemMock))
        )));
    }

//    /**
//     * @param Varien_Object|array $items
//     * @param array $productIds
//     * @dataProvider providerSynchronizeInventoryAfterQuoteSubmit
//     */
//    public function testSynchronizeInventoryAfterQuoteSubmit($items, $productIds)
//    {
//        if ($productIds) {
//            $channelProductMock = $this->mockModel('xcom_listing/channel_product', array('getPublishedListingIds'));
//            $channelProductMock->expects($this->once())
//                ->method('getPublishedListingIds')
//                ->with($this->equalTo($productIds))
//                ->will($this->returnValue(array()));
//        } else {
//            $channelProductMock = $this->mockModel('xcom_listing/channel_product', array('getPublishedListingIds'));
//            $channelProductMock->expects($this->never())
//                ->method('getPublishedListingIds');
//        }
//        $this->_object->synchronizeInventoryAfterQuoteSubmit(new Varien_Object(array(
//            'event' => new Varien_Object(array(
//                'quote' => new Varien_Object(array(
//                    'all_items' => $items))))
//        )));
//    }

    /**
     * Data provider for testSynchronizeInventoryAfterStockChanges
     *
     * @return array
     */
    public function providerSynchronizeInventoryAfterStockChanges()
    {
        return array(
            //no reason to update listing
            array(true, false, null, true, false, null, false),
            array(true, false, null, true, true, 1, false),
            array(true, true, 1, true, false, null, false),
            array(true, true, 1, true, true, 1, false),
            array(false, null, null, false, null, null, false),
            array(false, null, null, true, false, null, false),
            array(false, null, null, true, true, 1, false),
            array(true, false, null, false, null, null, false),
            array(true, true, 1, false, null, null, false),
            //listing must be updated
            array(true, true, 0, null, null, null, true),
            array(true, false, null, true, true, 0, true),
            array(false, null, null, true, true, 0, true),
        );
    }

//    /**
//     * Data provider for testSynchronizeInventoryAfterQuoteSubmit
//     *
//     * @return array
//     */
//    public function providerSynchronizeInventoryAfterQuoteSubmit()
//    {
//        return array(
//            array(array(), false),
//            array(array(new Varien_Object(array('product_id'=>0)),
//                        new Varien_Object(array('product_id'=>1)),
//                        new Varien_Object(array('product_id'=>2))),
//                array(1,2)),
//        );
//    }
}
