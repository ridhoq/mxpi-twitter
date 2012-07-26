<?php
class Xcom_Mapping_Helper_DataTest extends Xcom_TestCase
{
    /** @var Xcom_Mmp_Helper_Data */
    protected $_object;

    public function setUp()
    {
        parent::setUp();

        $this->_object = new Xcom_Mapping_Helper_Data();
    }

    /**
     * @param $setId
     * @param $mappingProductTypeId
     * @param $setName
     * @param $productTypeName
     * @param $eResult
     * @dataProvider attributeSetBreadcrumbProvider
     */
    public function testGetAttributeSetBreadcrumb($setId, $mappingProductTypeId, $setName, $productTypeName, $eResult)
    {
        $objectMock = $this->getMock(get_class($this->_object),
            array('getAttributeSetId', 'getMappingProductTypeId', 'getAttributeSetName', 'getProductTypeName'));
        $objectMock->expects($this->once())
            ->method('getAttributeSetId')
            ->will($this->returnValue($setId));
        $objectMock->expects($this->once())
            ->method('getMappingProductTypeId')
            ->will($this->returnValue($mappingProductTypeId));
        $objectMock->expects($this->once())
            ->method('getAttributeSetName')
            ->will($this->returnValue($setName));
        $objectMock->expects($this->any())
            ->method('getProductTypeName')
            ->will($this->returnValue($productTypeName));

        $result = $objectMock->getAttributeSetBreadcrumb();
        $this->assertEquals($eResult, $result);
    }

    public function attributeSetBreadcrumbProvider()
    {
        return array(
            array(200, -1, 'Test A S N', 'None', 'Attribute Set: Test A S N ~ None'),
            array(300, 100, 'T A S N', 'Test Product Type Name', 'Attribute Set: T A S N ~ Test Product Type Name'),
            array(300, 100, '', '', 'Attribute Set:  ~ ')
        );
    }

    public function testGetAttributeBreadcrumb()
    {
        $setId = 100500;
        $attributeId = 123;
        $objectMock = $this->getMock(get_class($this->_object),
            array('getAttributeSetId', 'getAttributeId'));
        $objectMock->expects($this->once())
            ->method('getAttributeSetId')
            ->will($this->returnValue($setId));
        $objectMock->expects($this->once())
            ->method('getAttributeId')
            ->will($this->returnValue($attributeId));

        $relationRecord = new Varien_Object(array(
            'attribute_name'    => 'test attribute',
            'mapping_attribute_name' => 'mapping test attribute'
        ));

        $collectionMock = $this->mockResource('xcom_mapping/attribute_collection',
            array('initAttributeRelations', 'addFieldToFilter', 'getFirstItem'));
        $collectionMock->expects($this->once())
            ->method('initAttributeRelations')
            ->with($this->equalTo($setId))
            ->will($this->returnValue($collectionMock));
        $collectionMock->expects($this->once())
            ->method('addFieldToFilter')
            ->with($this->equalTo('eat.attribute_id'), $this->equalTo($attributeId))
            ->will($this->returnValue($collectionMock));
        $collectionMock->expects($this->once())
            ->method('getFirstItem')
            ->will($this->returnValue($relationRecord));

        $result = $objectMock->getAttributeBreadcrumb();
        $this->assertEquals('Attribute: test attribute ~ mapping test attribute', $result);
    }

    /**
     * Test retrieve values for select attribute
     */
    public function testGetAttributeOptionsHash_SelectAttribute()
    {

        $attribute = new Varien_Object(array(
            'frontend_input'    => 'select',
            'source'            => new Attribute_Source_Getter(),
            'backend_type'      => 'int',
        ));
        $expectedHashTable = array('value2' => 'label1', 'value4' => 'label3');

        $actualHashTable = $this->_object->getAttributeOptionsHash($attribute);
        $this->assertEquals($expectedHashTable, $actualHashTable);
    }

    /**
     * Test retrieve values for select attribute
     */
    public function testGetAttributeOptionsHash_TextAttribute()
    {

        $attribute = new Varien_Object(array(
            'frontend_input'    => 'text',
            'source'            => new Attribute_Source_Getter(),
            'backend_type'      => 'varchar',
        ));
        $expectedHashTable = array('code' => 'text');

        $mappingAttribute = $this->mockResource('xcom_mapping/attribute');
        $mappingAttribute->expects($this->once())
            ->method('getEavValuesByAttribute')
            ->will($this->returnValue(array('code' => 'text')));
        $actualHashTable = $this->_object->getAttributeOptionsHash($attribute);
        $this->assertEquals($expectedHashTable, $actualHashTable);
    }

    /**
     * @dataProvider dataProviderisMappingAuto
     * @param $data
     * @param $_result
     */
    protected function isMappingValueAuto($data, $_result)
    {
        $mappingAttributeValue = $this->mockModel('xcom_mapping/attribute_value', array(
                    'getByAttributeId'));
                $mappingAttributeValue->expects($this->once())
                                      ->method('getByAttributeId')
                                      ->will($this->returnValue($data));
        $result = $this->_object->isMappingValueAuto();
        $this->assertEquals($_result, $result);
    }

    public function dataProviderisMappingAuto()
    {
        return array(
            array(array(), false),
            array(false, false),
            array(array(123), true)
        );
    }
}

class Attribute_Source_Getter
{
    public function getAllOptions($param = false)
    {
        return array(
            array(
                'label' => 'label1',
                'value' => 'value2' ),
            array(
                'label' => 'label3',
                'value' => 'value4' ),
            );
    }
}

class Fixture_Xcom_Mapping_Model_Resource_Attribute
{
    public function getEavValuesByAttribute()
    {
        return array(
            'hash1' => 'value1',
            'hash3' => 'value4',
        );
    }
}
