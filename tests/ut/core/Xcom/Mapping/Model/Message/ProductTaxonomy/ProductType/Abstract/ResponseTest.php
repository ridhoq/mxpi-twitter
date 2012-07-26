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
 * @package     Xcom_Mapping
 * @copyright   Copyright (c) 2010 Magento Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class Xcom_Mapping_Model_Message_ProductTaxonomy_ProductType_Abstract_ResponseTest extends Xcom_TestCase
{
    /** @var Xcom_Mapping_Model_Message_ProductTaxonomy_ProductType_Search_Succeeded */
    protected $_object;
    protected $_instanceOf = 'Xcom_Mapping_Model_Message_ProductTaxonomy_ProductType_Abstract_Response';

    public function setUp()
    {
        parent::setUp();
        $this->_object = Mage::getModel('xcom_mapping/message_productTaxonomy_productType_abstract_response');
    }

    public function tearDown()
    {
        $this->_object = null;
    }

    public function testInstanceOf()
    {
        $this->assertInstanceOf($this->_instanceOf, $this->_object);
    }

    public function testGetResponseDataEmpty()
    {
        $this->assertEmpty($this->_object->getResponseData());
    }

    public function testProcess()
    {
        $data = array('productTypes' => array(
            0 => array(
                'attributes' => array(
                    0 => array('recommendedValues' => array('value1', 'value2'), 'value2'),
                    1 => array('enumerators' => array('value1', 'value2'), 'value2'),
                )),
            1 => array(
                'attributes' => array(
                    0 => array('defaultValue' => true),
                    1 => array('attributeData2')
            ))
        ));

        $this->_mockProductTypeCollection();
        $this->_mockModels();

        $this->_object->setBody($data);
        $result = $this->_object->process();
        $this->assertInstanceOf($this->_instanceOf, $result);
    }

    protected function _mockProductTypeCollection()
    {
        $collection = $this->mockCollection('xcom_mapping/product_type',
            array(), array('addFieldToFilter', 'setLocaleCode', 'getAllIds'));
        $collection->expects($this->once())
            ->method('addFieldToFilter')
            ->will($this->returnSelf());
        $collection->expects($this->once())
            ->method('setLocaleCode')
            ->will($this->returnSelf());
    }

    protected function _mockAttributeCollection()
    {
        $collection = $this->mockCollection('xcom_mapping/attribute',
            array(), array('addFieldToFilter', 'setLocaleCode', 'getAllIds', 'getFirstItem'));
        $collection->expects($this->any())
            ->method('addFieldToFilter')
            ->will($this->returnSelf());
        $collection->expects($this->any())
            ->method('setLocaleCode')
            ->will($this->returnSelf());
        $collection->expects($this->any())
            ->method('getFirstItem')
            ->will($this->returnValue(new Varien_Object(array('id' => 'test_id'))));
        return $collection;
    }

    protected function _mockAttributeValueCollection()
    {
        $collection = $this->mockCollection('xcom_mapping/attribute_value',
            array(), array('addFieldToFilter', 'setLocaleCode', 'getAllIds', 'getFirstItem'));
        $collection->expects($this->any())
            ->method('addFieldToFilter')
            ->will($this->returnSelf());
        $collection->expects($this->any())
            ->method('setLocaleCode')
            ->will($this->returnSelf());
        $collection->expects($this->any())
            ->method('getFirstItem')
            ->will($this->returnValue(new Varien_Object(array('id' => 'test_id'))));
        return $collection;
    }

    public function testCompareVersionNull()
    {
        $oldVersion = null;
        $newVersion = '0.1';
        $result = version_compare($newVersion, $oldVersion);
        $this->assertEquals(1, $result);
    }

    public function testSaveProductTypes()
    {
        $this->mockModel('xcom_mapping/product_type', array('deleteByIds'));
        /** @var $objectMock Xcom_Mapping_Model_Message_ProductTaxonomy_ProductType_Search_Succeeded */
        $objectMock = $this->getMock(get_class($this->_object), array('saveProductType'));
        $objectMock->expects($this->any())
            ->method('saveProductType')
            ->will($this->returnValue($objectMock));

        $data = array('test1', 'test2');
        $result = $objectMock->saveProductTypes($data);
        $this->assertInstanceOf(get_class($objectMock), $result);
    }

    public function testSaveProductType()
    {
        $data = array(
            'id' => "22",
            'name'  => 'test_name',
            'version' => 'test_version',
            'description'   => 'test_description',
            'productClassIds'  => array('1', '2', '3'),
            'locale'    => array('country' => 'test_US', 'language' => 'test_en')
        );
        $productTypeMock = $this->mockModel('xcom_mapping/product_type');
        $expectedData = $data;
        $expectedData['product_type_id'] = (int)$data['id'];
        $expectedData['locale_code'] = 'test_en_test_US';
        $expectedData['product_class_ids'] = array('1', '2', '3');
        unset($expectedData['id']);
        unset($expectedData['locale']);
        unset($expectedData['productClassIds']);
        $productTypeMock->expects($this->any())
            ->method('addData')
            ->with($this->equalTo($expectedData))
            ->will($this->returnValue($productTypeMock));
        $productTypeMock->expects($this->any())
            ->method('save')
            ->will($this->returnValue($productTypeMock));
        $this->_object->setBody($data);

        $result = $this->_object->saveProductType($productTypeMock, $data);
        $this->assertInstanceOf($this->_instanceOf, $result);
    }

    public function testSaveAttributes()
    {
        $attributes = array('test1', 'test2');
        $productType = new Varien_Object();
        /** @var $objectMock Xcom_Mapping_Model_Message_ProductTaxonomy_ProductType_Search_Succeeded */
        $attributeMock = $this->mockModel('xcom_mapping/attribute', array('getCollection'));
        $attributeMock->expects($this->any())
            ->method('getCollection')
            ->will($this->returnValue($this->_mockAttributeCollection()));

        $objectMock = $this->getMock($this->_instanceOf, array('saveAttribute', 'saveAttributeValues'));
        $objectMock->expects($this->at(0))
            ->method('saveAttribute')
            ->with($this->equalTo($productType), $this->equalTo($attributeMock), $this->equalTo($attributes[0]))
            ->will($this->returnValue($objectMock));

        $objectMock->expects($this->at(1))
            ->method('saveAttributeValues')
            ->with($this->equalTo($attributeMock), $this->equalTo($attributes[0]))
            ->will($this->returnValue($objectMock));

        $objectMock->expects($this->at(2))
            ->method('saveAttribute')
            ->with($this->equalTo($productType), $this->equalTo($attributeMock), $this->equalTo($attributes[1]))
            ->will($this->returnValue($objectMock));

        $objectMock->expects($this->at(3))
            ->method('saveAttributeValues')
            ->with($this->equalTo($attributeMock), $this->equalTo($attributes[1]))
            ->will($this->returnValue($objectMock));

        $result = $objectMock->saveAttributes($productType, $attributes);
        $this->assertInstanceOf(get_class($objectMock), $result);
    }

    public function testSaveAttribute()
    {
        $data = array(
            'id' => 'test_id',
            'name'  => 'test_name',
            'channelAttributeDecorations' => array(
                0 => array(
                    'channelId' => 'test_channel_id_1',
                    'required'  => true,
                    'supportsVariation' => true
                ),
                1 => array(
                    'channelId' => 'test_channel_id_2',
                    'required'  => true,
                    'supportsVariation' => false
                ),
            ),
            'description'   => 'test_description',
            'allowMultipleValues'  => true,
            'defaultValue' => 'test_value_id',
            'locale'    => array('country' => 'test_US', 'language' => 'test_en')
        );

        $productTypeMock = $this->mockModel('xcom_mapping/product_type');
        $productTypeMock->expects($this->any())
            ->method('getId')
            ->will($this->returnValue('100500'));

        $expectedData = array(
            'attribute_id' => $data['id'],
            'mapping_product_type_id'   => $productTypeMock->getId(),
            'name'  => $data['name'],
            'channel_decoration'    => array(
                0 => array(
                    'channel_code' => $data['channelAttributeDecorations'][0]['channelId'],
                    'is_required' => $data['channelAttributeDecorations'][0]['required'],
                    'is_variation' => $data['channelAttributeDecorations'][0]['supportsVariation'],
                ),
                1 => array(
                    'channel_code' => $data['channelAttributeDecorations'][1]['channelId'],
                    'is_required' => $data['channelAttributeDecorations'][1]['required'],
                    'is_variation' => $data['channelAttributeDecorations'][1]['supportsVariation'],
                ),
            ),
            'description' => $data['description'],
            'is_multiselect' => $data['allowMultipleValues'],
            'default_value_ids' => $data['defaultValue'],
            'locale_code' => 'test_en_test_US',
            'is_restricted' => 1
        );
        $attributeMock = $this->mockModel('xcom_mapping/attribute', array('deleteAllIds', 'save', 'addData'));
        $attributeMock->expects($this->any())
            ->method('addData')
            ->with($this->equalTo($expectedData))
            ->will($this->returnValue($attributeMock));
        $attributeMock->expects($this->any())
            ->method('save')
            ->will($this->returnValue($attributeMock));
        $this->_object->setBody($data);
        $result = $this->_object->saveAttribute($productTypeMock, $attributeMock, $data);
        $this->assertInstanceOf($this->_instanceOf, $result);
    }

    /**
     * @dataProvider saveAttributeValuesProvider
     */
    public function testSaveAttributeValues($attributeData, $expectedData, $id, $name)
    {
        $attribute = new Varien_Object();

        $objectMock = $this->getMock($this->_instanceOf, array('saveAttributeValueData'));
        $objectMock->expects($this->at(0))
            ->method('saveAttributeValueData')
            ->with(
                $this->equalTo($attribute),
                $this->equalTo($expectedData),
                $this->equalTo($id),
                $this->equalTo($name)
            )
            ->will($this->returnValue($objectMock));

        $result = $objectMock->saveAttributeValues($attribute, $attributeData);
        $this->assertInstanceOf(get_class($objectMock), $result);

    }

    public function saveAttributeValuesProvider()
    {
        return array(
            array(array('recommendedValues' => array('test_1')), 'test_1', 'valueId', 'localizedValue'),
            array(array('enumerators' => array('test_2')), 'test_2', 'id', 'name'),
            array(array(
                    array('valueId' => -1, 'name' => 'True', 'channelId' => null), 'defaultValue' => true,
                    array('valueId' => -2, 'name' => 'False', 'channelId' => null), 'defaultValue' => false,
                ),
                array('valueId' => -1, 'name' => 'True', 'channelId' => null), 'id', 'name'),
        );
    }

    protected function _mockModels()
    {
        $this->_mockProductType();
        $attributeMock = $this->mockModel('xcom_mapping/attribute',
            array('addData', 'save', 'deleteAllIds', 'getCollection'));
        $attributeMock->expects($this->any())
            ->method('getCollection')
            ->will($this->returnValue($this->_mockAttributeCollection()));

        $attributeMock->expects($this->any())
            ->method('addData')
            ->will($this->returnSelf());
        $attributeMock->expects($this->any())
            ->method('save')
            ->will($this->returnSelf());
        $attributeValueMock = $this->mockModel('xcom_mapping/attribute_value');
        $attributeValueMock->expects($this->any())
            ->method('addData')
            ->will($this->returnSelf());
        $attributeValueMock->expects($this->any())
            ->method('save')
            ->will($this->returnSelf());
        $attributeValueMock->expects($this->any())
            ->method('getCollection')
            ->will($this->returnValue($this->_mockAttributeValueCollection()));

        return $this;
    }

    protected function _mockProductType()
    {
        $productTypeMock = $this->mockModel('xcom_mapping/product_type', array('addData', 'save', 'deleteByIds'));
        $productTypeMock->expects($this->any())
            ->method('addData')
            ->will($this->returnValue($productTypeMock));
        $productTypeMock->expects($this->any())
            ->method('save')
            ->will($this->returnValue($productTypeMock));
        return $this;
    }

    public function testSaveAttributeValueData()
    {
        $attributeMock = $this->mockModel('xcom_mapping/attribute');
        $attributeMock->expects($this->any())
            ->method('getId')
            ->will($this->returnValue('100500'));
        $data = array(
            'channelValueDecorations' => array(
                array('channelId' => 'test_channel_id_1'),
                array('channelId' => 'test_channel_id_2'),
            ),
            'id_key' => 'test_id_1',
            'name_key' => 'test_name_1',
            'locale'    => array('country' => 'test_US', 'language' => 'test_en')
        );
        $attributeValueMock = $this->mockModel('xcom_mapping/attribute_value');
        $attributeValueMock->expects($this->any())
            ->method('addData')
            ->with($this->equalTo(array(
                'mapping_attribute_id' => $attributeMock->getId(),
                'channel_codes' => array(
                    'test_channel_id_1',
                    'test_channel_id_2'
                ),
                'value_id'  => 'test_id_1',
                'name'     => 'test_name_1',
                'locale_code'    => 'test_en_test_US'
            )))
            ->will($this->returnValue($attributeValueMock));
        $attributeValueMock->expects($this->any())
            ->method('save')
            ->will($this->returnSelf());
        $attributeValueMock->expects($this->any())
            ->method('getCollection')
            ->will($this->returnValue($this->_mockAttributeValueCollection()));
        $this->_object->setBody($data);
        $result = $this->_object->saveAttributeValueData($attributeMock, $data, 'id_key', 'name_key');
        $this->assertInstanceOf($this->_instanceOf, $result);
    }

    public function testGetLocaleCodeDefault()
    {
        $result = $this->_object->getLocaleCode();
        $this->assertEquals('en_US', $result);
    }
}
