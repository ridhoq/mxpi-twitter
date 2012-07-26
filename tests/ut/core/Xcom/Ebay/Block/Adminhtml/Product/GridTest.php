<?php
class Xcom_Ebay_Block_Adminhtml_Product_GridTest extends Xcom_TestCase
{
    protected $_object;

    public function setUp()
    {
        $this->_checkConnection = true;
        parent::setUp();
        $this->_object = new Xcom_Ebay_Block_Adminhtml_Product_Grid();
    }

    public function tearDown()
    {
        $this->_object = null;
    }

    public function testGrid()
    {
        $this->_mockCollection();

        $objectMock = $this->_mockPrepareGridMethods();
        $objectMock ->prepareGrid();
    }

    protected function _mockCollection()
    {
        Mage::register('current_channeltype', new Varien_Object());
        $methods = array('joinProductQty', 'prepareStoreSensitiveData', 'addFieldToFilter', 'addAttributeToFilter',
            'joinStatusColumn', 'load', 'setOrder'
        );
        $collection = $this->getMock('Xcom_Listing_Model_Resource_Product_Collection', $methods, array(), '', false);
        $collection->expects($this->any())
            ->method('load')
            ->will($this->returnValue(array()));

        $collection->expects($this->any())
            ->method('joinProductQty')
            ->will($this->returnValue($collection));

        $collection->expects($this->any())
            ->method('prepareStoreSensitiveData')
            ->will($this->returnValue($collection));

        $collection->expects($this->any())
            ->method('joinField')
            ->will($this->returnValue($collection));

        $collection->expects($this->any())
            ->method('addFieldToFilter')
            ->will($this->returnValue($collection));

        $collection->expects($this->any())
            ->method('addAttributeToFilter')
            ->will($this->returnValue($collection));

        $collection->expects($this->any())
            ->method('joinStatusColumn')
            ->withAnyParameters()
            ->will($this->returnValue($collection));

        Mage::registerMockResourceModel('xcom_listing/product_collection', $collection);
    }

    public function testPrepareMassaction()
    {
        $this->_mockCollection();
        $objectMock = $this->_mockPrepareGridMethods();
        $objectMock->prepareGrid();
        $this->assertEquals('selected_products', $objectMock->getMassactionBlock()->getFormFieldName());
    }

    /**
     * @dataProvider productStatusProvider
     */
    public function testListingColumnValues($expectedStatusKey)
    {
        $this->_mockCollection();
        $objectMock = $this->_mockPrepareGridMethods();
        $objectMock->prepareGrid();
        $result = $objectMock->getColumn('listing')->getOptions();
        $this->assertArrayHasKey($expectedStatusKey, $result);
    }

    public function productStatusProvider()
    {
        return array(
            array(Xcom_Listing_Model_Channel_Product::STATUS_ACTIVE),
            array(Xcom_Listing_Model_Channel_Product::STATUS_PENDING),
            array(Xcom_Listing_Model_Channel_Product::STATUS_INACTIVE),
            array(Xcom_Listing_Model_Channel_Product::STATUS_FAILURE),
        );
    }

    protected function _mockPrepareGridMethods()
    {
        $methods = array('getChannelOptionHash', 'getChannelOptionArray', '_getAttributeSetOptionHash');
        $objectMock = $this->getMock('Xcom_Ebay_Block_Adminhtml_Product_Grid_Mock', $methods);
        $objectMock->expects($this->any())
            ->method('getChannelOptionHash')
            ->will($this->returnValue(array()));
        $objectMock->expects($this->any())
            ->method('getChannelOptionArray')
            ->will($this->returnValue(array()));
        $objectMock->expects($this->any())
            ->method('_getAttributeSetOptionHash')
            ->will($this->returnValue(array()));

        return $objectMock;
    }

    public function testGetChannelOptionArray()
    {
        Mage::register('current_channeltype', new Varien_Object());
        $expectedResult = array(
            array('value' => 'test_id_1', 'label' => 'test_name_1'),
            array('value' => 'test_id_2', 'label' => 'test_name_2'),
        );

        $methods = array('addChanneltypeCodeFilter', 'addActiveChannelAndPolicyFilter', 'toOptionArray');
        $collection = $this->mockCollection('xcom_mmp/channel', null, $methods);
        $collection->expects($this->any())
            ->method('addChanneltypeCodeFilter')
            ->will($this->returnValue($collection));
        $collection->expects($this->any())
            ->method('addActiveChannelAndPolicyFilter')
            ->will($this->returnValue($collection));
        $collection->expects($this->any())
            ->method('toOptionArray')
            ->will($this->returnValue($expectedResult));

        $result = $this->_object->getChannelOptionArray();
        $this->assertNotEmpty($result);
        $this->assertEquals('test_name_2', $result[1]['label']);
    }

    public function testGetChannelOptionHash()
    {
        Mage::register('current_channeltype', new Varien_Object());
        $expectedResult = array(
            'test_id_1' => 'test_name_1',
            'test_id_2' => 'test_name_2',
        );

        $methods = array('addChanneltypeCodeFilter', 'toOptionHash');
        $collection = $this->mockCollection('xcom_mmp/channel', null, $methods);
        $collection->expects($this->any())
            ->method('addChanneltypeCodeFilter')
            ->will($this->returnValue($collection));
        $collection->expects($this->any())
            ->method('toOptionHash')
            ->will($this->returnValue($expectedResult));

        $result = $this->_object->getChannelOptionHash();
        $this->assertNotEmpty($result);
        $this->assertEquals('test_name_2', $result['test_id_2']);
    }

    public function testGetChannelOptionOnlyOne()
    {
        Mage::register('current_channeltype', new Varien_Object());
        $methods = array('getChannelOptionArray','getMassactionBlock');
        $objectMock = $this->getMock('Xcom_Ebay_Block_Adminhtml_Product_Grid_Mock', $methods);
        $objectMock->expects($this->any())
            ->method('getChannelOptionArray')
            ->will($this->returnValue(array(array('label'=>'first value','value'=>2))));

        $objectMock->expects($this->any())
            ->method('getMassactionBlock')
            ->will($this->returnValue(new Mock_Object()));

        $objectMock->prepareMassaction();

        $itemValue = $objectMock->getMassactionBlock()->_items['create']['additional']['channel_id']['value'];

        $this->assertEquals(2,$itemValue,'expected value is:'.$itemValue);
    }
}

class Mock_Object extends Varien_Object
{
    public $_items = array() ;
    public function addItem($itemId, array $item)
    {

        $this->_items[$itemId] = $item;

    }

}

class Xcom_Ebay_Block_Adminhtml_Product_Grid_Mock extends Xcom_Ebay_Block_Adminhtml_Product_Grid
{
    public function prepareGrid() {
        $this->getRequest()->setParam('store', 1);
        $this->setLayout(Mage::app()->getLayout());
        $this->setRequest(new Varien_Object());

        $this->_prepareColumns();
        $this->_prepareMassactionBlock();
        $this->_prepareCollection();
    }

    public function prepareMassaction()
    {
        return $this->_prepareMassaction();
    }

    public function getChanneltype()
    {
        return new Varien_Object();
    }
}

