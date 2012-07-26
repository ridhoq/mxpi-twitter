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
class Xcom_Mapping_Model_RelationTest extends Xcom_TestCase
{
    /** @var Xcom_Mapping_Model_Relation */
    protected $_object;
    protected $_instanceOf = 'Xcom_Mapping_Model_Relation';

    public function setUp()
    {
        parent::setUp();
        $this->_object = new Xcom_Mapping_Model_Relation();
    }

    public function tearDown()
    {
        $this->_object = null;
    }

    public function testInstanceOf()
    {
        $this->assertInstanceOf($this->_instanceOf, $this->_object);
    }

    public function testSaveRelation()
    {
        $attributeSetId = 'test_attr_set_id';
        $productTypeId = 'test_prod_type_id';
        $relationProductTypeId = 'test_rel_prod_type_id';
        $relationAttributeId = 'test_rel_att_id';
        $attributeId = 'test_attribute_id';
        $mappingAttributeId = 'test_mapping_attr_id';
        $values = array(
            'attribute_value_0' => '123',
            'target_attribute_value_0' => 'test_target_attribute_value_0'
        );
        $resourceMock = $this->mockResource('xcom_mapping/relation', array('beginTransaction', 'commit'));
        $objectMock = $this->getMock(get_class($this->_object), array('getResource'));
        $objectMock->expects($this->any())
            ->method('getResource')
            ->will($this->returnValue($resourceMock));

        $productTypeMock = $this->mockModel('xcom_mapping/product_type', array('saveRelation'));
        $productTypeMock->expects($this->once())
            ->method('saveRelation')
            ->with($this->equalTo($attributeSetId), $this->equalTo($productTypeId))
            ->will($this->returnValue($relationProductTypeId));

        $attributeMock = $this->mockModel('xcom_mapping/attribute', array('saveRelation'));
        $attributeMock->expects($this->once())
            ->method('saveRelation')
            ->with(
                $this->equalTo($relationProductTypeId),
                $this->equalTo($attributeId),
                $this->equalTo($mappingAttributeId))
            ->will($this->returnValue($relationAttributeId));

        $productTypeMock->expects($this->once())
            ->method('saveRelation')
            ->with($this->equalTo($attributeSetId), $this->equalTo($productTypeId))
            ->will($this->returnValue($relationAttributeId));

        $result = $this->_object->saveRelation(
            $attributeSetId, $productTypeId, $attributeId, $mappingAttributeId, $values);

        $this->assertInstanceOf($this->_instanceOf, $result);
    }

    public function testAddFilterOnlyMappedAttributes()
    {
        $this->_mockObjectMethod('addFilterOnlyMappedAttributes', 'value_1', 'value_2');
        $result = $this->_object->addFilterOnlyMappedAttributes('value_1', 'value_2');
        $this->assertInstanceOf($this->_instanceOf, $result);
    }

    public function testAddFilterOnlyMappedMappingAttributes()
    {
        $this->_mockObjectMethod('addFilterOnlyMappedMappingAttributes', 'value_1', 'value_2');
        $result = $this->_object->addFilterOnlyMappedMappingAttributes('value_1', 'value_2');
        $this->assertInstanceOf($this->_instanceOf, $result);
    }

    protected function _mockObjectMethod($methodName, $value1, $value2)
    {
        $resourceMock = $this->mockResource('xcom_mapping/relation', array($methodName));
        $resourceMock->expects($this->once())
            ->method($methodName)
            ->with($this->equalTo($value1), $this->equalTo($value2))
            ->will($this->returnValue($resourceMock));
    }

    public function testSaveRelationWithVarcharAttributeType()
    {
        $attributeSetId = 'test_attr_set_id';
        $productTypeId = 'test_prod_type_id';
        $relationProductTypeId = 'test_rel_prod_type_id';
        $relationAttributeId = 'test_rel_att_id';
        $attributeId = 'test_attribute_id';
        $mappingAttributeId = 'test_mapping_attr_id';
        $values = array(
            'attribute_value_0' => '123',
            'target_attribute_value_0' => 'test_target_attribute_value_0'
        );
        $resourceMock = $this->mockResource('xcom_mapping/relation', array('beginTransaction', 'commit'));
        $objectMock = $this->getMock(get_class($this->_object), array('getResource'));
        $objectMock->expects($this->any())
            ->method('getResource')
            ->will($this->returnValue($resourceMock));

        $productTypeMock = $this->mockModel('xcom_mapping/product_type', array('saveRelation'));
        $productTypeMock->expects($this->once())
            ->method('saveRelation')
            ->with($this->equalTo($attributeSetId), $this->equalTo($productTypeId))
            ->will($this->returnValue($relationProductTypeId));

        $attributeMock = $this->mockModel('xcom_mapping/attribute', array('saveRelation'));
        $attributeMock->expects($this->once())
            ->method('saveRelation')
            ->with(
                $this->equalTo($relationProductTypeId),
                $this->equalTo($attributeId),
                $this->equalTo($mappingAttributeId))
            ->will($this->returnValue($relationAttributeId));

        $productTypeMock->expects($this->once())
            ->method('saveRelation')
            ->with($this->equalTo($attributeSetId), $this->equalTo($productTypeId))
            ->will($this->returnValue($relationAttributeId));

        $result = $this->_object->saveRelation(
            $attributeSetId, $productTypeId, $attributeId, $mappingAttributeId, $values);

        $this->assertInstanceOf($this->_instanceOf, $result);
    }

    /**
     * @expectedException Mage_Core_Exception
     * @return void
     */
    public function testSaveRelationException()
    {
        $resourceMock = $this->mockResource('xcom_mapping/relation', array('beginTransaction', 'rollback'));
        $objectMock = $this->getMock(get_class($this->_object), array('getResource'));
        $objectMock->expects($this->any())
            ->method('getResource')
            ->will($this->returnValue($resourceMock));

        $objectMock = $this->getMock(get_class($this->_object), array('_getProductType'));
        $objectMock->expects($this->once())
            ->method('_getProductType')
            ->will($this->returnValue(new XcomProductType_Fixture()));

        $objectMock->saveRelation(null, null, null, null, null);
    }

    public function testSaveValuesRelation()
    {
        $attributeSetId = 'test_attr_set_id';
        $relationAttributeId = 'test_rel_att_id';
        $attributeId = 'test_attribute_id';
        $mappingAttributeId = 'test_mapping_attr_id';
        $values = array(
            'attribute_value_0' => '123',
            'target_attribute_value_0' => 'test_target_attribute_value_0'
        );
        $resourceMock = $this->mockResource('xcom_mapping/relation', array('beginTransaction', 'commit'));
        $objectMock = $this->getMock(get_class($this->_object), array('getResource'));
        $objectMock->expects($this->any())
            ->method('getResource')
            ->will($this->returnValue($resourceMock));

        $attributeValueMock = $this->mockModel('xcom_mapping/attribute', array('getRelationAttributeId'));
        $attributeValueMock->expects($this->once())
            ->method('getRelationAttributeId')
            ->with(
            $this->equalTo($attributeSetId),
            $this->equalTo($attributeId),
            $this->equalTo($mappingAttributeId))
            ->will($this->returnValue($relationAttributeId));

        $helperMock = $this->mockHelper('xcom_mapping', array('getAttributeType'));
        $helperMock->expects($this->once())
            ->method('getAttributeType')
            ->will($this->returnValue($attributeId));

        $attributeValueMock = $this->mockModel('xcom_mapping/attribute_value', array('saveRelation'));
        $attributeValueMock->expects($this->once())
            ->method('saveRelation')
            ->with($this->equalTo($relationAttributeId), $this->equalTo(array(
            array(
                'relation_attribute_id' => $relationAttributeId,
                'value_id'              => $values['attribute_value_0'],
                'hash_value'            => null,
                'mapping_value_id'      => $values['target_attribute_value_0']
            )
        )));

        $result = $this->_object->saveValuesRelation(
            $attributeSetId, $attributeId, $mappingAttributeId, $values);

        $this->assertInstanceOf($this->_instanceOf, $result);
    }

    public function testSaveValuesRelationWithVarcharAttributeType()
    {
        $attributeSetId = 'test_attr_set_id';
        $relationAttributeId = 'test_rel_att_id';
        $attributeId = 'test_attribute_id';
        $mappingAttributeId = 'test_mapping_attr_id';
        $values = array(
            'attribute_value_0' => '123',
            'target_attribute_value_0' => 'test_target_attribute_value_0'
        );
        $resourceMock = $this->mockResource('xcom_mapping/relation', array('beginTransaction', 'commit'));
        $objectMock = $this->getMock(get_class($this->_object), array('getResource'));
        $objectMock->expects($this->any())
            ->method('getResource')
            ->will($this->returnValue($resourceMock));

        $attributeValueMock = $this->mockModel('xcom_mapping/attribute', array('getRelationAttributeId'));
        $attributeValueMock->expects($this->once())
            ->method('getRelationAttributeId')
            ->with(
            $this->equalTo($attributeSetId),
            $this->equalTo($attributeId),
            $this->equalTo($mappingAttributeId))
            ->will($this->returnValue($relationAttributeId));

        $helperMock = $this->mockHelper('xcom_mapping', array('getAttributeType'));
        $helperMock->expects($this->once())
            ->method('getAttributeType')
            ->will($this->returnValue('varchar'));

        $attributeValueMock = $this->mockModel('xcom_mapping/attribute_value', array('saveRelation'));
        $attributeValueMock->expects($this->once())
            ->method('saveRelation')
            ->with($this->equalTo($relationAttributeId), $this->equalTo(array(
            array(
                'relation_attribute_id' => $relationAttributeId,
                'value_id'              => null,
                'hash_value'            => $values['attribute_value_0'],
                'mapping_value_id'      => $values['target_attribute_value_0']
            )
        )));

        $result = $this->_object->saveValuesRelation(
            $attributeSetId, $attributeId, $mappingAttributeId, $values);

        $this->assertInstanceOf($this->_instanceOf, $result);
    }
    /**
     * @expectedException Mage_Core_Exception
     * @return void
     */
    public function testSaveValueRelationException()
    {
        $resourceMock = $this->mockResource('xcom_mapping/relation', array('beginTransaction', 'rollback'));
        $objectMock = $this->getMock(get_class($this->_object), array('getResource'));
        $objectMock->expects($this->any())
            ->method('getResource')
            ->will($this->returnValue($resourceMock));

        $objectMock = $this->getMock(get_class($this->_object), array('_getAttributeValue'));
        $objectMock->expects($this->once())
            ->method('_getAttributeValue')
            ->will($this->returnValue(new XcomProductType_Fixture()));

        $objectMock->saveValuesRelation(null, null, null, null);
    }
}
class XcomProductType_Fixture
{
    public function saveRelation()
    {
        throw new Exception();
    }
}
