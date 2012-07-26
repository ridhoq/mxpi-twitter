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
class Xcom_Ebay_Model_CatalogInventory_StockTest extends Xcom_TestCase
{
    /**
     * @var Xcom_Ebay_Model_CatalogInventory_Stock
     */
    protected $_object     = null;

    public function setUp()
    {
        $this->_checkConnection = true;
        parent::setUp();
        $this->_object = new Mage_CatalogInventory_Model_StockFixture();
    }

    public function tearDown(){
        parent::tearDown();
        $this->_object = null;
    }

    public function testInstanceOf()
    {
        $this->assertInstanceOf('Mage_CatalogInventory_Model_Stock', $this->_object);
    }


    /**
     * @param $items
     * @param $configInventoryToZero
     * @param $configOutOfStock
     * @param $productStocks
     * @param $productIds
     * @dataProvider providerRegisterProductsSale
     */
    public function testRegisterProductsSale($items, $configInventoryToZero, $configOutOfStock,
                                             $productStocks, $productIds)
    {
        $fixtureMock = $this->getMock('Mage_CatalogInventory_Model_StockFixture', array(
            '_prepareProductQtys'
        ));
        $qtys = array(10, 100, 0, 20);
        $fixtureMock->expects($this->any())
                    ->method('_prepareProductQtys')
                    ->will($this->returnValue($qtys));
        $stockItemMock = $this->mockModel('cataloginventory/stock_item', array(
            'setData',
        ));
        $resourceStock = $this->mockResource('cataloginventory/stock', array(
            'getProductsStock', 'correctItemsQty', 'commit'
        ));
        $resourceStock->expects($this->once())
                      ->method('getProductsStock')
                      ->will($this->returnValue($items));
        $resourceStock->expects($this->once())
                      ->method('correctItemsQty');
        $resourceStock->expects($this->once())
                      ->method('commit');
        $stockItemMock->expects($this->any())
                      ->method('setData');

        $helperMock = $this->mockHelper('xcom_ebay', array('isUpdateChannelToZeroOnInventoryToZero',
                            'isUpdateChannelToZeroOnProductOutOfStock', 'updateListingForProduct'));
        $helperMock->expects($this->any())
                   ->method('isUpdateChannelToZeroOnInventoryToZero')
                   ->will($this->returnValue($configInventoryToZero));
        $helperMock->expects($this->any())
                   ->method('isUpdateChannelToZeroOnProductOutOfStock')
                   ->will($this->returnValue($configOutOfStock));
        $productMock = $this->mockModel('catalog/product', array(
            'load'
        ));
        $product = new Varien_Object(array(
            'stock_item' => new Varien_Object()
        ));
        if (is_object($productStocks)) {
            $product = new Varien_Object(array(
                'stock_item' => $productStocks
            ));
        }
        $productMock->expects($this->any())
                    ->method('load')
                    ->will($this->returnValue($product));
        if (is_object($items)) {
            $helperMock->expects($this->any())
                       ->method('updateListingForProduct')
                       ->with($items->getProductId());
        }
        $fixtureMock->registerProductsSale($items);
    }

    /**
     * Data provider for testSynchronizeInventoryAfterQuoteSubmit
     *
     * @return array
     */
    public function providerRegisterProductsSale()
    {
        return array(
            array(array('product_id' => 1), null, null, null, null, null),
            array(array(new Varien_Object(array('product_id'=>1))), false, false, null, null, null),
            array(
                array(new Varien_Object(array('product_id'=>1))),
                true,
                null,
                array(new Varien_Object(array('qty'=>1, 'is_in_stock'=>1))),
                array()),
            array(array(new Varien_Object(array('product_id'=>1))), false, true, 1, 1, array()),
            array(array(new Varien_Object(array('product_id'=>1)),
                        new Varien_Object(array('product_id'=>2))),
                true, array(1,2)),
        );
    }
}

class Mage_CatalogInventory_Model_StockFixture extends Xcom_Ebay_Model_CatalogInventory_Stock
{
    public function _prepareProductQtys($items)
    {
        return parent::_prepareProductQtys($items);
    }
}
