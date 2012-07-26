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
class Xcom_Chronicle_Model_Message_Product_Created_OutboundTest extends Xcom_TestCase
{
    /** @var Xcom_Chronicle_Model_Message_Product_Created_Outbound */
    protected $_object;
    protected $_instanceOf = 'Xcom_Chronicle_Model_Message_Product_Created_Outbound';

    public function setUp()
    {
        parent::setUp();
        $this->_object = Mage::helper('xcom_xfabric')->getMessage('com.x.pim.v1/ProductCreation/ProductCreated');
        $this->_object
            ->setEncoding(Xcom_Xfabric_Model_Message_Abstract::AVRO_JSON);
    }

    public function tearDown()
    {
        $this->_object = null;
        parent::tearDown();
    }

    public function testInstanceOf()
    {
        $this->assertInstanceOf($this->_instanceOf, $this->_object);
    }

    public function testConstructorSetFields()
    {
        $this->assertEquals('com.x.pim.v1/ProductCreation/ProductCreated', $this->_object->getTopic());
        $this->assertEquals('ProductCreated', $this->_object->getSchemaRecordName());
        // _schemaFile is also set but there is no magical getter
    }

    public function testSchema()
    {
        $this->_disableSchema();

        $productMock = $this->_createProductMock();

        $mockDataObj = $this->_createProcessDataObjectMock($productMock);

        $this->_object->process($mockDataObj);
        $messageData = $this->_object->getMessageData();

        $this->assertArrayHasKey('products', $messageData);
    }

    protected function _createProcessDataObjectMock($productMock)
    {
        $mockDataObj = $this->getMock('Varien_Object', array('getProduct'));
        $mockDataObj->expects($this->once())
            ->method('getProduct')
            ->will($this->returnValue($productMock));

        return $mockDataObj;
    }

    protected function _createProductMock($productMethodsToMock = array('getEntityId',
        'getAttributeSetId', 'getName', 'getShortDescription', 'getSku'))
    {

        $productTypeMock = $this->mockModel('xcom_mapping/product_type', array('getProductTypeId'));
        $productTypeMock->expects($this->any())
            ->method('getProductTypeId')
            ->withAnyParameters()->will($this->returnValue('1234'));

        $attributeMock = $this->mockModel('xcom_mapping/attribute');
        $attributeMock->expects($this->any())
            ->method('getSelectAttributesMapping')
            ->withAnyParameters()
            ->will($this->returnValue(array()));

        $productMock = $this->getMock('Mage_Catalog_Model_Product', $productMethodsToMock);
        $productMock->expects($this->once())
            ->method('getEntityId')
            ->will($this->returnValue('12'));

        $productMock->expects($this->any())
            ->method('getAttributeSetId')
            ->will($this->returnValue('1'));

        $productMock->expects($this->once())
            ->method('getName')
            ->will($this->returnValue('Product Name'));

        $productMock->expects($this->once())
            ->method('getShortDescription')
            ->will($this->returnValue('Short Description'));

        $productMock->expects($this->any())
            ->method('getSku')
            ->will($this->returnValue('sku-12456'));

        return $productMock;
    }

    protected function _disableSchema()
    {
        $objectMock = $this->getMock(get_class($this->_object), array('_initSchema', 'encode', 'setEncoder'));
        $this->_object = $objectMock;
    }
}
