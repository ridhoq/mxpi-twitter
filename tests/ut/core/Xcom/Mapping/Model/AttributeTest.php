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
class Xcom_Mapping_Model_AttributeTest extends Xcom_TestCase
{
    /** @var Xcom_Mapping_Model_Attribute */
    protected $_object;
    protected $_instanceOf = 'Xcom_Mapping_Model_Attribute';

    public function setUp()
    {
        parent::setUp();
        $this->_object = new Xcom_Mapping_Model_Attribute();
    }

    public function tearDown()
    {
        $this->_object = null;
    }

    public function testSaveRelation()
    {
        $relationProductTypeId = 1;
        $attributeId = 2;
        $mappingAttributeId = 3;

        $resourceMock = $this->mockResource('xcom_mapping/attribute', array('saveRelation'));
        $resourceMock->expects($this->once())
            ->method('saveRelation')
            ->with(
                $this->equalTo($relationProductTypeId),
                $this->equalTo($attributeId),
                $this->equalTo($mappingAttributeId)
            )
            ->will($this->returnValue(13));
        $result = $this->_object->saveRelation($relationProductTypeId, $attributeId, $mappingAttributeId);
        $this->assertInternalType('int', $result);
    }

    public function testDeleteRelation()
    {
        $relationAttributeIds = 'test';

        $resourceMock = $this->mockResource('xcom_mapping/attribute', array('deleteRelation'));
        $resourceMock->expects($this->once())
            ->method('deleteRelation')
            ->with(
                $this->equalTo($relationAttributeIds)
            );
        $result = $this->_object->deleteRelation($relationAttributeIds);
        $this->assertInstanceOf($this->_instanceOf, $result);
    }

    public function testGetSelectAttributesMapping()
    {
        $localeCode = 'test_locale';
        $attributeSetId = 1;

        $collectionMock = $this->getMock('Xcom_Mapping_Model_Resource_Attribute_Collection',
            array(), array(), '', false);
        $methods = array('getCollection', 'setLocaleCode',
            'initAttributeRelations', 'addSelectOnlyFilter', 'getCollectionData');
        $this->_object = $this->getMock(get_class($this->_object), $methods);
        $this->_object->expects($this->once())
            ->method('getCollection')
            ->will($this->returnValue($collectionMock));
        $collectionMock->expects($this->once())
            ->method('setLocaleCode')
            ->with($this->equalTo($localeCode))
            ->will($this->returnValue($collectionMock));
        $collectionMock->expects($this->once())
            ->method('initAttributeRelations')
            ->will($this->returnValue($collectionMock));
        $collectionMock->expects($this->once())
            ->method('addSelectOnlyFilter')
            ->will($this->returnValue($collectionMock));
        $collectionMock->expects($this->once())
            ->method('getCollectionData')
            ->will($this->returnValue(array(1,2,3)));

        $this->assertEquals(array(1,2,3), $this->_object->getSelectAttributesMapping($attributeSetId, $localeCode));
    }
}
