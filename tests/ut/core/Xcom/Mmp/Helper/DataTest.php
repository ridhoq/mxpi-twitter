<?php
class Xcom_Mmp_Helper_DataTest extends Xcom_TestCase
{
    /** @var Xcom_Mmp_Helper_Data */
    protected $_object;

    public function setUp()
    {
        parent::setUp();
        $this->_object = new Xcom_Mmp_Helper_Data();
    }

    public function testGetProductBySku()
    {
        $sku = 'test_sku_1';
        $productId = 1;
        $product = $this->mockModel('catalog/product', array('getIdBySku', 'load'));
        $this->_mockGetIdBySku($product, $sku, $productId);
        $product->expects($this->once())
            ->method('load')
            ->with($productId)
            ->will($this->returnValue($product));
        $result = $this->_object->getProductBySku($sku);
        $this->assertInstanceOf('Mage_Catalog_Model_Product', $result);
    }

    public function testGetProductBySkuEmpty()
    {
        $sku = 'test_sku_1';
        $product = $this->mockModel('catalog/product', array('getIdBySku'));
        $this->_mockGetIdBySku($product, $sku);
        $result = $this->_object->getProductBySku($sku);
        $this->assertInstanceOf('Mage_Catalog_Model_Product', $result);
    }

    protected function _mockGetIdBySku($mockObject, $with, $will = false)
    {
        $mockObject->expects($this->once())
            ->method('getIdBySku')
            ->with($this->equalTo($with))
            ->will($this->returnValue($will));
        return $mockObject;
    }
}
