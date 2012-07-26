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
 * @package     Xcom_Chronicle
 * @copyright   Copyright (c) 2010 Magento Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class Xcom_Chronicle_Model_ObserverTest extends Xcom_TestCase
{
    /**
     * @var Xcom_Chronicle_Model_Observer
     */
    protected $_object     = null;

    /**
     * @var Mage_Sales_Model_Order mock
     */
    protected $_orderMock  = null;

    /**
     * @var Mage_Sales_Model_Order_Shipment mock
     */
    protected $_shipmentMock  = null;

    /**
     * @var Mage_Catalog_Model_Product mock
     */
    protected $_productMock = null;

    /**
     * @var Varien_Object mock
     */
    protected $_eventMock  = null;

    /**
     * @var Varien_Object mock
     */
    protected $_observerMock = null;

    /**
     * @var Varien_Object mock
     */
    protected $_creditMemoMock = null;

    /**
     * @var Varien_Object mock
     */
    protected $_orderItemMock = null;

    /**
     * @var Varien_Object mock
     */
    protected $_creditMemoItemMock = null;

    /**
     * @var Varien_Object mock
     */
    protected $_stockItemMock = null;

    public function setUp()
    {
        parent::setUp();
        $this->_object = new Xcom_Chronicle_Model_Observer();
        $this->_orderMock = null;
        $this->_shipmentMock = null;
        $this->_productMock = null;
        $this->_eventMock = null;
        $this->_observerMock = null;
    }

    private function _setupObserverMock()
    {
        $this->_orderMock = $this->mockModel('Mage_Sales_Model_Order');

        $this->_eventMock = $this->getMock('Varien_Object', array('getOrder'));
        $this->_eventMock->expects($this->once())
            ->method('getOrder')
            ->will($this->returnValue($this->_orderMock));

        $this->_observerMock = $this->getMock('Varien_Object', array('getEvent'));
        $this->_observerMock->expects($this->once())
            ->method('getEvent')
            ->will($this->returnValue($this->_eventMock));
    }

    private function _setupShipmentObserverMock()
    {
        $this->_orderMock = $this->mockModel('Mage_Sales_Model_Order');

        $this->_shipmentMock = $this->mockModel('Mage_Sales_Model_Order_Shipment');
        $this->_shipmentMock->expects($this->any())
            ->method('isObjectNew')
            ->will($this->returnValue(true));

        $this->_shipmentMock->expects($this->any())
            ->method('getOrder')
            ->will($this->returnValue($this->_orderMock));

        $this->_eventMock = $this->getMock('Varien_Object', array('getShipment'));
        $this->_eventMock->expects($this->once())
            ->method('getShipment')
            ->will($this->returnValue($this->_shipmentMock));

        $this->_observerMock = $this->getMock('Varien_Object', array('getEvent'));
        $this->_observerMock->expects($this->once())
            ->method('getEvent')
            ->will($this->returnValue($this->_eventMock));
    }

    private function _setupProductObserverMock()
    {
        $this->_productMock = $this->getMock('Mage_Catalog_Model_Product', array('isObjectNew', 'getTypeId'));

        $this->_productMock->expects($this->any())
            ->method('isObjectNew')
            ->will($this->returnValue(true));

        $this->_productMock->expects($this->any())
            ->method('getTypeId')
            ->will($this->returnValue('simple'));

        $this->_eventMock = $this->getMock('Varien_Object', array('getProduct'));
        $this->_eventMock->expects($this->once())
            ->method('getProduct')
            ->will($this->returnValue($this->_productMock));

        $this->_observerMock = $this->getMock('Varien_Object', array('getEvent'));
        $this->_observerMock->expects($this->once())
            ->method('getEvent')
            ->will($this->returnValue($this->_eventMock));
    }

    private function _setupCreditMemoMock()
    {
        $this->_stockItemMock = $this->MockModel('Mage_CatalogInventory_Model_Stock_Item');
        $this->_orderItemMock = $this->MockModel('Mage_Sales_Model_Order_Item');

        $this->_creditMemoItemMock = $this->getMock('Mage_Sales_Model_Order_Creditmemo_Item',
            array('getOrderItem','hasBackToStock','getBackToStock','getQty'));
        $this->_creditMemoItemMock->expects($this->any())
            ->method('getOrderItem')
            ->will($this->returnValue($this->_orderItemMock));
        $this->_creditMemoItemMock->expects($this->any())
            ->method('hasBackToStock')
            ->will($this->returnValue(true));
        $this->_creditMemoItemMock->expects($this->any())
            ->method('getBackToStock')
            ->will($this->returnValue(1));
        $this->_creditMemoItemMock->expects($this->any())
            ->method('getQty')
            ->will($this->returnValue(1));

        $this->_creditMemoMock = $this->getMock('Mage_Sales_Model_Order_Creditmemo',array('getItemsCollection'));

        $this->_creditMemoMock->expects($this->any())
            ->method('getItemsCollection')
            ->will($this->returnValue(array($this->_creditMemoItemMock)));

        $this->_observerMock = $this->getMock('Varien_Object', array('getCreditmemo'));
        $this->_observerMock->expects($this->once())
            ->method('getCreditmemo')
            ->will($this->returnValue($this->_creditMemoMock));
    }

    public function tearDown()
    {
        $this->_object = null;
        $this->_orderMock = null;
        $this->_shipmentMock = null;
        $this->_productMock = null;
        $this->_eventMock = null;
        $this->_observerMock = null;

        parent::tearDown();
    }

    public function testInstanceOf()
    {
        $this->assertInstanceOf('Xcom_Chronicle_Model_Observer', $this->_object);
    }

    public function testOrderAfterCreate()
    {
        $this->_setupObserverMock();

        $helperMock = $this->mockHelper('xcom_xfabric', array('send'));
        $helperMock->expects($this->once())
            ->method('send')
            ->with('order/created', array('order' => $this->_orderMock));

        $this->assertNull(Mage::registry("order_sent"), "Don't expect order_sent to be set yet");
        $retObj = $this->_object->orderAfterCreate($this->_observerMock);
        $this->assertSame($this->_object, $retObj);
    }

    public function testOrderAfterCancel()
    {
        $this->markTestIncomplete("Fails");
        $this->_setupObserverMock();

        $helperMock = $this->mockHelper('xcom_xfabric', array('send'));
        $helperMock->expects($this->once())
            ->method('send')
            ->with('order/cancelled', array('order' => $this->_orderMock));

        $retObj = $this->_object->orderAfterCancel($this->_observerMock);
        $this->assertSame($this->_object, $retObj);
    }

    public function testOrderBeforeShip()
    {
        $this->markTestIncomplete('Method doesn\'t exists');
        $this->_setupShipmentObserverMock();
        $retObj = $this->_object->orderBeforeShip($this->_observerMock);
        $this->assertSame($this->_object, $retObj);
        $this->assertTrue(Mage::registry('xcom_shipment_new'));
    }

    public function testOrderAfterShip()
    {
        $this->markTestIncomplete("Causes fatal error");
        $this->_setupShipmentObserverMock();
        Mage::register('xcom_shipment_new', true);

        $helperMock = $this->mockHelper('xcom_xfabric', array('send'));
        $helperMock->expects($this->once())
            ->method('send')
            ->with('order/shipment/shipped', array('shipment' => $this->_shipmentMock));

        $retObj = $this->_object->orderAfterShip($this->_observerMock);
        $this->assertSame($this->_object, $retObj);
    }

    public function testOrderUpdateAfterShip()
    {
        $this->markTestIncomplete("Fails");
        $this->_setupShipmentObserverMock();
        $helperMock = $this->mockHelper('xcom_xfabric', array('send'));

        $helperMock->expects($this->once())
            ->method('send')
            ->with('order/updated', array('order' => $this->_orderMock));

        $observer = $this->mockModel('xcom_chronicle/observer', array('_isShipmentNew', '_isValueRegistered'));
        $observer->expects($this->once())
            ->method('_isValueRegistered')
            ->will($this->returnValue(false));
        $retObj = $observer->orderUpdateAfterShip($this->_observerMock);
        $this->assertSame($observer, $retObj);
    }

    public function testProductBeforeSafe()
    {
        $this->markTestIncomplete('Include all cases in test');
        $this->_setupProductObserverMock();
        $observer = $this->mockModel('xcom_chronicle/observer', array('_isSimpleProduct'));
        $observer->expects($this->once())
            ->method('_isSimpleProduct')
            ->will($this->returnValue(true));
        $retObj = $observer->productBeforeSave($this->_observerMock);
        $this->assertSame($observer, $retObj);
        $this->assertTrue(Mage::registry('xcom_product_new'));
    }

    public function testProductAfterSafeCreate()
    {
        $this->_setupProductObserverMock();
        $helperMock = $this->mockHelper('xcom_xfabric', array('send'));
        Mage::register('xcom_product_new', true);

        $helperMock->expects($this->once())
            ->method('send')
            ->with('com.x.pim.v1/ProductCreation/ProductCreated', array('product' => $this->_productMock));

        $retObj = $this->_object->productAfterSave($this->_observerMock);
        $this->assertSame($this->_object, $retObj);
    }

    public function testProductAfterSafeUpdate()
    {
        $this->markTestIncomplete('Include all cases');
        $this->_setupProductObserverMock();
        $helperMock = $this->mockHelper('xcom_xfabric', array('send'));
        Mage::register('xcom_product_changed', true);

        //com.x.pim.v1/ProductCreation/ProductCreated
        $helperMock->expects($this->once())
            ->method('send')
            ->with('com.x.pim.v1/ProductUpdate/ProductUpdated', array('product' => $this->_productMock));

        $retObj = $this->_object->productAfterSave($this->_observerMock);
        $this->assertSame($this->_object, $retObj);
    }


    public function testProductAfterDelete()
    {
        $this->markTestIncomplete("Fix all scenarios");
        $this->_setupProductObserverMock();

        $mockProduct =  $this->getMock('Mage_Catalog_Model_Product', array('getEntityId'));
        $mockProduct->expects($this->any())
            ->method('getEntityId')
            ->will($this->returnValue('1'));

        $collectionMock = $this->mockResource('catalog/product_collection', array('addFieldToFilter'));
        $collectionMock->expects($this->any())
            ->method('addFieldToFilter')
            ->will($this->returnValue(array($mockProduct)));

        $mockProductModel = $this->mockModel('catalog/product', array('load'), FALSE);
        $mockProductModel->expects($this->any())
            ->method('load')
            ->withAnyParameters()
            ->will($this->returnValue($mockProduct));

        $mockMessageProduct = $this->mockModel('xcom_chronicle/message_product', array('toArray'), FALSE);
        $mockMessageProduct->expects($this->any())
            ->method('toArray')
            ->will($this->returnValue(array(1)));

        $helperMock = $this->mockHelper('xcom_xfabric', array('send'));

        $helperMock->expects($this->at(0))
            ->method('send')
            ->with('com.x.pim.v1/ProductDeletion/ProductDeleted', array('product' => $this->_productMock));

        $retObj = $this->_object->productAfterDelete($this->_observerMock);
        $this->assertSame($this->_object, $retObj);
    }

    public function testStockItemUpdatedOnCreditMemo()
    {
        $this->_setupCreditMemoMock();
        $helperMock = $this->mockHelper('xcom_xfabric', array('send'));
        $stockItem = Mage::getModel('cataloginventory/stock_item')->load((int)$this->_creditMemoItemMock->getProductId());
        $helperMock->expects($this->at(0))
            ->method('send')
            ->with('com.x.inventory.v1/StockItemUpdate/StockItemUpdated', array('stock_item' => $stockItem, 'product_sku' => $this->_creditMemoItemMock->getSku()));
        $retObj = $this->_object->creditmemoSaveAfter($this->_observerMock);

        $this->assertSame($this->_object, $retObj);
    }

    protected function _testMassOfferUpdateJobSetup($isFirstTime)
    {
        $mockScheduleCollection = $this->mockResource(
            'cron/schedule_collection', array('addFieldToFilter', 'load', 'count')
        );

        $countReturn = $isFirstTime ? 0 : mt_rand(1, mt_getrandmax());
        $mockScheduleCollection->expects($this->once())->method('count')->will($this->returnValue($countReturn));
        $mockScheduleCollection->expects($this->any())->method('addFieldToFilter')->will($this->returnSelf());
        $mockScheduleCollection->expects($this->once())->method('load')->will($this->returnSelf());

        $mockSchedule = $this->mockModel(
            'cron/schedule', array('getCollection', 'setJobCode', 'setCreatedAt', 'setScheduledAt', 'save')
        );

        $mockSchedule
            ->expects($this->once())
            ->method('getCollection')
            ->will($this->returnValue($mockScheduleCollection));

        $mockSchedule
            ->expects($isFirstTime ? $this->once() : $this->never())
            ->method('setJobCode')
            ->will($this->returnSelf());

        $mockSchedule
            ->expects($isFirstTime ? $this->once() : $this->never())
            ->method('setCreatedAt')
            ->will($this->returnSelf());

        $mockSchedule
            ->expects($isFirstTime ? $this->once() : $this->never())
            ->method('setScheduledAt')
            ->will($this->returnSelf());

        $mockSchedule->expects($isFirstTime ? $this->once() : $this->never())->method('save');
        $retObj = $this->_object->massOfferUpdateJobSetup();
        $this->assertSame($this->_object, $retObj);
    }

    public function testMassOfferUpdateJobSetup_firstTime()
    {
        $this->_testMassOfferUpdateJobSetup(true);
    }

    public function testMassOfferUpdateJobSetup_alreadySet()
    {
        $this->_testMassOfferUpdateJobSetup(false);
    }

    public function testUpdateAllOffers()
    {
        $this->markTestIncomplete("Fails");
        $products = array();
        $productsCnt = mt_rand(1, 20);

        for($i = 0; $i < $productsCnt; $i++) {
            $productMock = $this->mockModel('catalog/product', array('getStoreIds'), false);
            $productMock->expects($this->any())->method('getStoreIds')->will($this->returnValue(array(1)));
            $products[] = $productMock;
        }

        $collectionMock = $this->mockResource('catalog/product_collection', array('getIterator'));

        $collectionMock
            ->expects($this->any())
            ->method('getIterator')
            ->will($this->returnValue(new ArrayIterator($products)));

        $helperMock = $this->mockHelper('xcom_xfabric', array('send'));

        $helperMock
            ->expects($this->exactly($productsCnt))
            ->method('send')
            ->with($this->equalTo('com.x.webstore.v1/WebStoreOfferUpdate/WebStoreOfferUpdated'), $this->anything());

        $retObj = $this->_object->updateAllOffers();
        $this->assertSame($this->_object, $retObj);
    }
}
