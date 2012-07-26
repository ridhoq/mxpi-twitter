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
class Xcom_Mapping_Model_MapperTest extends Xcom_TestCase
{
    /** @var Xcom_Mapping_Model_Mapper */
    protected $_object;
    protected $_instanceOf = 'Xcom_Mapping_Model_Mapper';

    /**
     * Attribute values for product
     *  array(
     *      {attribute_code} => {attribute_value}
     *      [, ...]
     *  )
     *
     * @var array
     */
    protected $_productAttributeValues  = array(
        'attribute_code_1'  => 'value_1',
        'attribute_code_2'  => 'value_2'
    );

    /**
     * Mapped attribute values
     *  array(
     *      {attribute_set_id} => array(
     *          {attribute_id}  => array(
     *              {attribute_value}    => {mapped_attribute_value}
     *              [, ...]
     *          }[, ...]
     *      )
     *      [, ...]
     *  )
     * @var array
     */
    protected $_attributeValueMapping  = array(
        //attribute_set_id
        '2' => array(
            //attribute_id
            '1' =>array(
            )
        ),
        '3' => array(
            '1' =>array(
                'value_1'   => 'mapped_value_1',
                'value_2'   => 'mapped_value_2'
            )
        )
    );

    public function setUp()
    {
        parent::setUp();
        $this->_object = new Public_Xcom_Mapping_Model_Mapper();
    }

    public function tearDown()
    {
        $this->_object = null;
    }

    public function testInstanceOf()
    {
        $this->assertInstanceOf($this->_instanceOf, $this->_object);
    }

    public function testGetMappingOptions_withNotMappedAttributes()
    {
        $attributeSetId = 1;
        $product        = Mage::getModel('catalog/product')
            ->addData($this->_productAttributeValues)
            ->setData('attribute_set_id', $attributeSetId);

        $objectMock = $this->getMock('Xcom_Mapping_Model_Mapper', array('getMappedEavValues'));
        $objectMock->expects($this->once())
            ->method('getMappedEavValues')
            ->will($this->returnValue(array()));

        $this->_mockGetSelectAttributesMapping($attributeSetId, array());
        $this->assertEmpty($objectMock->getMappingOptions($product));
    }

    public function testGetMappingOptions_withNotMappedValues()
    {
        $attributeSetId = 2;
        $product        = Mage::getModel('catalog/product')
            ->addData($this->_productAttributeValues)
            ->setData('attribute_set_id', $attributeSetId);

        $attributeMapping   = array(
            array(
                'attribute_code'        => 'attribute_code_1',
                'attribute_id'          => '1',
                'mapping_attribute_id'  => 'mapping_attribute_code_1'
            ));

        $objectMock = $this->getMock('Xcom_Mapping_Model_Mapper', array('getMappedEavValues'));
        $objectMock->expects($this->once())
            ->method('getMappedEavValues')
            ->will($this->returnValue(array()));

        $this->_mockGetSelectAttributesMapping($attributeSetId, $attributeMapping);

        $mockAttributeValue = $this->mockModel('xcom_mapping/attribute_value', array('getSelectValuesMapping'));
        $mockAttributeValue->expects($this->any())
            ->method('getSelectValuesMapping')
            ->with($this->equalTo($attributeSetId), $this->equalTo($attributeMapping[0]['attribute_id']))
            ->will($this->returnValue(array()));

        $this->assertEmpty($objectMock->getMappingOptions($product));
    }

    public function testGetMappingOptions_withMappedValues()
    {
        $attributeSetId = 3;
        $product        = Mage::getModel('catalog/product')
            ->addData($this->_productAttributeValues)
            ->setData('attribute_set_id', $attributeSetId);

        $attributeMapping   = array(
            array(
                'attribute_code'        => 'attribute_code_1',
                'attribute_id'          => '1',
                'mapping_attribute_id'  => 'mapping_attribute_id_1',
                'origin_attribute_id'   => 'mapping_attribute_code_1'
            ),
            array(
                'attribute_code'        => 'attribute_code_2',
                'attribute_id'          => '2',
                'mapping_attribute_id'  => 'mapping_attribute_id_2',
                'origin_attribute_id'   => 'mapping_attribute_code_2'
            ));

        $this->_mockGetSelectAttributesMapping($attributeSetId, $attributeMapping);

        $attributeMappingValue  = array(
            array(array(
                'mapping_value_id'   => 'mapped_value_1',
                'origin_value_id'   => 'origin_value_1',
                'mapping_attribute_value'   => 'mapped_value_1'
            )),
            array(array(
                'mapping_value_id'   => 'mapped_value_2',
                'origin_value_id'   => 'origin_value_2',
                'mapping_attribute_value'   => 'mapped_value_2'
            )),
        );
        $mockAttributeValue = $this->mockModel('xcom_mapping/attribute_value', array('getSelectValuesMapping'));
        $mockAttributeValue->expects($this->any())
            ->method('getSelectValuesMapping')
            ->will($this->onConsecutiveCalls($attributeMappingValue[0], $attributeMappingValue[1]));

        $expectedResult = array(
            'mapping_attribute_code_1'  => 'origin_value_1',
            'mapping_attribute_code_2'  => 'origin_value_2'
        );
        $objectMock = $this->getMock('Xcom_Mapping_Model_Mapper', array('getMappedEavValues'));
        $objectMock->expects($this->once())
            ->method('getMappedEavValues')
            ->will($this->returnValue(array()));
        $this->assertEquals($expectedResult, $objectMock->getMappingOptions($product));
    }

    public function testGetMappingOptions_withNotMappedRequiredCustomAttributes()
    {
        $this->mockResource('xcom_mapping/mapper', array('getMappedEavValues'));
        $this->_mockGetSelectAttributesMapping(null, array());
        $mockProduct = $this->mockModel('catalog/product', array('getAttributeText'));
        $mockProduct->expects($this->once())
            ->method('getAttributeText')
            ->with($this->equalTo('xcom_condition'))
            ->will($this->returnValue('test_xcom_condition_value'));

        $expectedResult = array('xcom_condition' => 'test_xcom_condition_value');
        $this->assertEquals($expectedResult, $this->_object->getMappingOptions($mockProduct));
    }

    public function testGetMappingOptions_withMappedRequiredCustomAttributes()
    {
        $this->mockResource('xcom_mapping/mapper', array('getMappedEavValues'));
        $attributeSetId = 3;
        $product        = Mage::getModel('catalog/product')
            ->addData(array('xcom_condition' => 'xcom_condition_value'))
            ->setData('attribute_set_id', $attributeSetId);

        $attributeMapping   = array(
        array(
            'attribute_code'        => 'xcom_condition',
            'attribute_id'          => '1',
            'mapping_attribute_id'  => 'mapping_attribute_id_1',
            'origin_attribute_id'   => 'mapping_xcom_condition_attribute'
        ));

        $this->_mockGetSelectAttributesMapping($attributeSetId, $attributeMapping);

        $attributeMappingValue  = array(
            array(
                'mapping_value_id'          => 'mapped_xcom_condition_value_id',
                'origin_value_id'           => 'xcom_condition_value_id',
                'mapping_attribute_value'   => 'mapped_xcom_condition_value'
            )
        );
        $mockAttributeValue = $this->mockModel('xcom_mapping/attribute_value', array('getSelectValuesMapping'));
        $mockAttributeValue->expects($this->once())
            ->method('getSelectValuesMapping')
            ->will($this->returnValue($attributeMappingValue));

        $expectedResult = array(
            'mapping_xcom_condition_attribute'  => 'xcom_condition_value_id',
        );
        $this->assertEquals($expectedResult, $this->_object->getMappingOptions($product));
    }

    public function testRetrieveAttributeValueMapping_CustomValueMapping()
    {
        $mappedAttribute = array(
            'origin_attribute_id'   => 'origin attribute',
            'attribute_code'        => 'attribute_code'
        );
        $mappedValue = array(
            'mapping_value_id'          => null,
            'mapping_attribute_value'   => '22'
        );
        $product = $this->mockModel('catalog/product', array('getAttributeText'));
        $product->expects($this->once())
            ->method('getAttributeText')
            ->with($this->equalTo($mappedAttribute['attribute_code']))
            ->will($this->returnValue($mappedAttribute['attribute_code']));

        $expectedResult = array('origin attribute' => 'attribute_code');
        $this->_object->retrieveAttributeValueMapping($product, $mappedAttribute, $mappedValue);
        $actualResult = $this->_object->getOptions();
        $this->assertEquals($expectedResult, $actualResult);
    }

    public function testRetrieveAttributeValueMapping_SimpleValueMapping()
    {
        $mappedAttribute = array(
            'origin_attribute_id'   => 'origin attribute',
            'attribute_code'        => 'attribute_code'
        );
        $mappedValue = array(
            'mapping_value_id'          => '11',
            'origin_value_id'   => '22'
        );
        $expectedResult = array('origin attribute' => '22');
        $this->_object->retrieveAttributeValueMapping(null, $mappedAttribute, $mappedValue);
        $actualResult = $this->_object->getOptions();
        $this->assertEquals($expectedResult, $actualResult);
    }

    public function testRetrieveAttributeMapping_CustomAttributeMapping()
    {
        $mappedAttribute = array(
            'origin_attribute_id'   => 'origin attribute',
            'attribute_code'        => 'attribute_code',
            'mapping_attribute_id'  => null,
        );

        $product = $this->mockModel('catalog/product', array('getData', 'getAttributeText'));

        $product->expects($this->once())
            ->method('getData')
            ->with($this->equalTo($mappedAttribute['attribute_code']))
            ->will($this->returnValue($mappedAttribute['attribute_code']));

        $product->expects($this->once())
            ->method('getAttributeText')
            ->with($this->equalTo($mappedAttribute['attribute_code']))
            ->will($this->returnValue($mappedAttribute['attribute_code']));

        $attributeSetId = 1;
        $expectedResult = array('attribute_code' => 'attribute_code');
        $this->_object->retrieveAttributeMapping($product, $mappedAttribute, $attributeSetId);
        $actualResult = $this->_object->getOptions();
        $this->assertEquals($expectedResult, $actualResult);
    }

    public function testRetrieveAttributeMapping_SimpleAttributeMapping()
    {
        $mappedAttribute = array(
            'origin_attribute_id'   => 'origin attribute',
            'attribute_code'        => 'attribute_code',
            'mapping_attribute_id'  => '44',
        );

        $mappedValue = array(
            'mapping_value_id'          => '11',
            'mapping_attribute_value'   => '22'
        );

        $product = $this->mockModel('catalog/product', array('getData', 'getAttributeText'));

        $product->expects($this->once())
            ->method('getData')
            ->with($this->equalTo($mappedAttribute['attribute_code']))
            ->will($this->returnValue($mappedAttribute['attribute_code']));

        $attributeSetId = 1;

        $valueModel = $this->mockModel('xcom_mapping/attribute_value', array('getSelectValuesMapping'));
        $valueModel->expects($this->once())
            ->method('getSelectValuesMapping')
            ->with($this->equalTo($attributeSetId),
                $this->equalTo($mappedAttribute['attribute_id']),
                $this->equalTo($mappedAttribute['attribute_code']))
            ->will($this->returnValue($mappedValue));

        $objectMock = $this->getMock(get_class($this->_object), array('_retrieveAttributeValueMapping'));
        $objectMock->retrieveAttributeMapping($product, $mappedAttribute, $attributeSetId);
    }

    protected function _mockGetSelectAttributesMapping($attributeSetId, $returnValue)
    {
        $attribute = $this->mockModel('xcom_mapping/attribute', array('getSelectAttributesMapping'));
        $attribute->expects($this->any())
            ->method('getSelectAttributesMapping')
            ->with($this->equalTo($attributeSetId))
            ->will($this->returnValue($returnValue));
        return $attribute;
    }
}

class Public_Xcom_Mapping_Model_Mapper extends Xcom_Mapping_Model_Mapper
{
    public function retrieveAttributeValueMapping($product, $mappedAttribute, $mappedValue)
    {
        return $this->_retrieveAttributeValueMapping($product, $mappedAttribute, $mappedValue);
    }

    public function retrieveAttributeMapping($product, $mappedAttribute, $attributeSetId)
    {
        return $this->_retrieveAttributeMapping($product, $mappedAttribute, $attributeSetId);
    }

    public function getOptions()
    {
        return $this->_options;
    }
}

class Product extends Varien_Object
{
    public function getAttributeText($attributeCode)
    {
        return $attributeCode;
    }
}
