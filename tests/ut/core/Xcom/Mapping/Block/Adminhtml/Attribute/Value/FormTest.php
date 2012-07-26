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

class Xcom_Mapping_Block_Adminhtml_Attribute_Value_FormTest extends Xcom_TestCase
{
    /** @var Xcom_Mapping_Block_Adminhtml_Attribute_Value_Form */
    protected $_object;

    public function setUp()
    {
        parent::setUp();

        $this->_mockModel();
    }

    public function tearDown()
    {
        $this->_object = null;
    }

    protected function _mockModel()
    {
        $methods = array('getRequest');
        $formMock = $this->getMock('Xcom_Mapping_Block_Adminhtml_Attribute_Value_Form_Mock', $methods, array(), '', false);
        $request = new Mage_Core_Controller_Request_Http();
        $request->setParam('attribute_id', 1);
        $request->setParam('target_attribute_id', 1);
        $request->setParam('target_attribute_set_id', 1);

        $formMock->expects($this->any())
            ->method('getRequest')
            ->will($this->returnValue(3));
        $this->_object = $formMock;
    }

    public function testInstanceOf()
    {
        $this->assertInstanceOf('Xcom_Mapping_Block_Adminhtml_Attribute_Value_Form', $this->_object);
    }

    public function testGetTargetAttributeValues()
    {
        return;
        $resourceMethods = array('addFieldToFilter', 'load', 'toOptionArray');
        $resourceMock = $this->mockResource('xcom_mapping/target_attribute_value_collection', $resourceMethods);
        $resourceMock->expects($this->once())
            ->method('addFieldToFilter')
            ->will($this->returnValue($resourceMock));
        $resourceMock->expects($this->once())
            ->method('load')
            ->will($this->returnValue($resourceMock));

        $options = array(
            array('value' => '1', 'label' => 'Test 1'),
            array('value' => '2', 'label' => 'Test 2'),
        );

        $resourceMock->expects($this->once())
            ->method('toOptionArray')
            ->will($this->returnValue($options));

        $valueMock = $this->mockModel('xcom_mapping/target_attribute_value', array('getCollection'));
        $valueMock->expects($this->once())
            ->method('getCollection')
            ->will($this->returnValue($resourceMock));

        $targetAttributeValues = $this->_object->getTargetAttributeValues(1);
        $this->assertArrayHasKey(1, $targetAttributeValues);
        $this->assertEquals('Test 2', $targetAttributeValues[1]['label']);
    }

    public function testGetTargetAttributeUniqueValue()
    {
        $resourceMethods = array('addFieldToFilter', 'load', 'toOptionArray');
        $resourceMock = $this->mockResource('xcom_mapping/attribute_value_collection', $resourceMethods);
        $resourceMock->expects($this->any())
            ->method('addFieldToFilter')
            ->will($this->returnValue($resourceMock));
        $resourceMock->expects($this->any())
            ->method('load')
            ->will($this->returnValue($resourceMock));


        $options = array(
            array('value' => 3, 'label' => 'Test 2'),
        );

        $resourceMock->expects($this->once())
            ->method('toOptionArray')
            ->will($this->returnValue($options));

        $valueMock = $this->mockModel('xcom_mapping/attribute_value', array('getCollection'));
        $valueMock->expects($this->once())
            ->method('getCollection')
            ->will($this->returnValue($resourceMock));

        $this->_object->_targetAttribute = new Varien_Object();


        $this->_object->getTargetAttributeValues(1);
        $this->assertEquals('3',$this->_object->_variableCode,'valus :'.$this->_object->_variableCode);


    }
}
class Xcom_Mapping_Block_Adminhtml_Attribute_Value_Form_Mock
    extends Xcom_Mapping_Block_Adminhtml_Attribute_Value_Form {
    public $_variableCode = '';
    public $_targetAttribute;
}