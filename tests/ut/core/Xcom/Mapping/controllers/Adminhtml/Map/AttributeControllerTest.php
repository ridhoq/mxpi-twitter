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
require_once 'Xcom/Mapping/controllers/Adminhtml/Map/AttributeController.php';

class Xcom_Mapping_Adminhtml_Map_AttributeControllerTest extends Xcom_TestCase
{
    /** @var Xcom_Mapping_Adminhtml_Map_AttributeController  */
    protected $_object;

    /** @var Mage_Core_Controller_Request_Http */
    protected $_request;

    public function tearDown()
    {
        $this->_object = null;
        $this->_request = null;
    }

    public function setUp()
    {
        $this->_request = Mage::app()->getRequest()
            ->setParam('attribute_set_id', 'attribute_set_id_' . mt_rand(1000, 9999))
            ->setParam('attribute_id', 'attribute_id_' . mt_rand(1000, 9999))
            ->setParam('mapping_attribute_id', 'mapping_attribute_id_' . mt_rand(1000, 9999));
    }
    /**
     * Get controller's mock object
     *
     * @param $request
     * @param $response
     * @param array $methods
     */
    protected function _mockController($request, $response, array $methods = array())
    {
        $this->_object = $this->getMock('Xcom_Mapping_Adminhtml_Map_AttributeController', $methods);
        $this->_object->expects($this->any())
            ->method('getRequest')
            ->will($this->returnValue($request));
        $this->_object->expects($this->any())
            ->method('getResponse')
            ->will($this->returnValue($response));
    }

    public function testSaveValueAction()
    {
        $request = new Mage_Core_Controller_Request_Http();
        $request->setParam('attribute_set_id', 'attribute_set_id_test');
        $request->setParam('attribute_id', 'attribute_id_test');
        $request->setParam('mapping_attribute_id', 'test_mapping_attribute_id');

        $this->_mockController($request, new Varien_Object(), array('_redirect', 'getRequest', 'getResponse'));
        $this->_object->expects($this->once())
            ->method('_redirect')
            ->with($this->equalTo('*/mapping_attribute/index'), $this->equalTo(array(
            'target_system'             => null,
            'type'                      => 'edit',
            'mapping_product_type_id'   => null,
            'attribute_set_id'          => 'attribute_set_id_test'
        )));

        $relationMock = $this->mockModel('xcom_mapping/relation', array('saveValuesRelation'));
        $relationMock->expects($this->once())
            ->method('saveValuesRelation')
            ->with(
                $this->equalTo('attribute_set_id_test'),
                $this->equalTo('attribute_id_test'),
                $this->equalTo('test_mapping_attribute_id'),
                $this->equalTo($request->getParams())
            );

        $this->_object->saveValueAction();
    }

    public function testSaveCustomvalueAction()
    {
        $request = new Mage_Core_Controller_Request_Http();
        $request->setParam('attribute_set_id', 'attribute_set_id_test');
        $request->setParam('attribute_id', 'attribute_id_test');
        $request->setParam('attribute_code', array(
            'value1', 'value2', 'value3'
        ));

        $this->_mockController($request, new Varien_Object(), array('_redirect', 'getRequest', 'getResponse'));
        $this->_object->expects($this->once())
            ->method('_redirect')
            ->with($this->equalTo('*/mapping_attribute/index'), $this->equalTo(array(
                'target_system'             => null,
                'type'                      => 'edit',
                'mapping_product_type_id'   => null,
                'attribute_set_id'          => 'attribute_set_id_test'
            )));

        $resultValues = array(
            'attribute_value_0' => 'value1',
            'target_attribute_value_0' => -1,
            'attribute_value_1' => 'value2',
            'target_attribute_value_1' => -1,
            'attribute_value_2' => 'value3',
            'target_attribute_value_2' => -1,
        );

        $relationMock = $this->mockModel('xcom_mapping/relation', array('saveValuesRelation'));
        $relationMock->expects($this->once())
            ->method('saveValuesRelation')
            ->with(
                $this->equalTo('attribute_set_id_test'),
                $this->equalTo('attribute_id_test'),
                $this->equalTo(Xcom_Mapping_Model_Relation::DIRECT_MAPPING),
                $this->equalTo($resultValues)
            );

        $this->_object->saveCustomvalueAction();
    }

    public function testValueActionNoUserDefined()
    {
        $session = Mage::getModel('adminhtml/session');
        $request = new Mage_Core_Controller_Request_Http();
        $request->setParam('attribute_id', 1);
        $request->setParam('attribute_set_id', 1);
        $request->setParam('mapping_attribute_id', 1);
        $request->setParam('mapping_product_type_id', 1);

        $this->_getMockEntityAttribute(false);

        $this->_mockController($request, new Varien_Object(), array(
            '_redirect', 'getRequest', 'getResponse', '_getSession', '_saveAttributeRelation', '__'));
        $error = 'Attribute is not available for mapping.';
        $this->_object->expects($this->once())
                     ->method('__')
                     ->will($this->returnValue($error));
        $this->_mockSession($session);
        $this->_getMappingRelation();
        $result = $this->_object->valueAction();
        $messages = $session->getMessages();

        $this->assertInstanceOf('Mage_Core_Model_Message_Collection', $messages);

        $success = $messages->getLastAddedMessage();
        $this->assertInstanceOf('Mage_Core_Model_Message_Error', $success);
        $this->assertEquals($error, $success->getCode());
        $this->assertNull($result);
    }

    public function testValueActionNoMappedValue()
    {
        $session = Mage::getModel('adminhtml/session');
        Mage::unregister('current_magento_attribute');
        $request = new Mage_Core_Controller_Request_Http();
        $request->setParam('attribute_id', 1);
        $request->setParam('attribute_set_id', 1);
        $request->setParam('mapping_attribute_id', 1);
        $request->setParam('mapping_product_type_id', 1);

        $this->_getMockEntityAttribute(true);

        $this->_getMockMappingValidator(false);

        $testAttrib = 'test_attribb';

        $mappingHelperMock = $this->mockHelper('xcom_mapping', array('getAttribute'));
        $mappingHelperMock->expects($this->once())
                          ->method('getAttribute')
                          ->with($this->equalTo(1), $this->equalTo(1))
                          ->will($this->returnValue($testAttrib));

        $this->_mockController($request, new Varien_Object(), array(
            '_redirect', 'getRequest', 'getResponse', 'loadLayout', 'getLayout', 'renderLayout', '_getSession',
            '_saveAttributeRelation', '__'));
        $errorText = 'Mandatory Attribute. Please map at least one Value';
        $this->_object->expects($this->any())
                     ->method('__')
                     ->will($this->returnValue($errorText));
        $this->_getMappingRelation();
        $this->_mockMenu();

        $this->_mockSession($session);

        $this->_object->valueAction();

        $messages = $session->getMessages();
        $this->assertInstanceOf('Mage_Core_Model_Message_Collection', $messages);

        $errorMessage = $messages->getLastAddedMessage();
        $this->assertInstanceOf('Mage_Core_Model_Message_Error', $errorMessage);
        $this->assertEquals($errorText, $errorMessage->getCode());

        $result = Mage::registry('current_magento_attribute');
        $this->assertEquals($testAttrib, $result);
    }

    public function testValuecustomActionNoUserDefined()
    {
        $session = Mage::getModel('adminhtml/session');
        Mage::unregister('current_magento_attribute');
        $request = new Mage_Core_Controller_Request_Http();
        $request->setParam('attribute_id', 1);
        $request->setParam('attribute_set_id', 1);

        $this->_getMockEntityAttribute(false);

        $this->_mockController($request, new Varien_Object(),
            array('_redirect', 'getRequest', 'getResponse', 'loadLayout', 'getLayout', 'renderLayout', '_getSession',
                '_saveAttributeRelation', '__'));
        $errorText = 'Attribute is not available for mapping.';
        $this->_object->expects($this->once())
                     ->method('__')
                     ->will($this->returnValue($errorText));
        $this->_getMappingRelation();

        $this->_mockMenu();

        $this->_mockSession($session);

        $result = $this->_object->valuecustomAction();
        $messages = $session->getMessages();
        $this->assertInstanceOf('Mage_Core_Model_Message_Collection', $messages);

        $errorMessage = $messages->getLastAddedMessage();
        $this->assertInstanceOf('Mage_Core_Model_Message_Error', $errorMessage);
        $this->assertEquals($errorText, $errorMessage->getCode());
        $this->assertNull($result);
    }

    public function testValuecustomAction()
    {
        Mage::unregister('current_magento_attribute');
        $request = new Mage_Core_Controller_Request_Http();
        $request->setParam('attribute_id', 1);
        $request->setParam('attribute_set_id', 1);

        $this->_getMockEntityAttribute(true);

        $testAttrib = 'test_attribb';

        $mappingHelperMock = $this->mockHelper('xcom_mapping', array('getAttribute'));
        $mappingHelperMock->expects($this->once())
                          ->method('getAttribute')
                          ->with($this->equalTo(1), $this->equalTo(1))
                          ->will($this->returnValue($testAttrib));

        $this->_mockController($request, new Varien_Object(), array(
            '_redirect', 'getRequest', 'getResponse', 'loadLayout', 'getLayout', 'renderLayout',
            '_saveAttributeRelation'));

        $this->_getMappingRelation();

        $this->_mockMenu();
        $this->_object->valuecustomAction();
        $result = Mage::registry('current_magento_attribute');
        $this->assertEquals($testAttrib, $result);
    }

    public function testValidateBeforeContinueActionError()
    {
        Mage::app()->getRequest()
            ->setParam('attribute_set_id', 0)
            ->setParam('attribute_id', 0)
            ->setParam('mapping_attribute_id', 0);

        $errorBody = 'error_responce';

        $helperMock = $this->mockHelper('core', array('jsonEncode'));
        $helperMock->expects($this->once())
                   ->method('jsonEncode')
                   ->will($this->returnValue($errorBody));
        $this->_mockController(null, new Varien_Object(), array(
            'getRequest', 'getResponse'));

        $this->_object->validateBeforeContinueAction();
        $this->assertEquals($errorBody, $this->_object->getResponse()->getBody());
    }

    public function testValidateBeforeContinueActionError2()
    {
        Mage::app()->getRequest()
            ->setParam('attribute_set_id', 1)
            ->setParam('attribute_id', 1)
            ->setParam('mapping_attribute_id', 1);

        $helperMock = $this->mockHelper('xcom_mapping', array('getAttribute', 'getProductTypeAttribute'));
        $helperMock->expects($this->once())
                   ->method('getAttribute')
                   ->will($this->returnValue(
                        new Varien_Object(array(
                            'frontend_input' => 'some_wrong_value'
                        ))));
        $helperMock->expects($this->once())
                   ->method('getProductTypeAttribute')
                   ->will($this->returnValue(
                        new Varien_Object(array(
                            'predefined_values' => true,
                            'render_type'       => 'not_select'
                        ))));
        $this->_mockController(null, new Varien_Object(), array(
            'getRequest', 'getResponse'));

        $error = array('error' => 1, 'message' => 'test_message');

        $helperMock = $this->mockHelper('core', array('jsonEncode'));
        $helperMock->expects($this->once())
                   ->method('jsonEncode')
                   ->will($this->returnValue($error));

        $this->_object->validateBeforeContinueAction();
        $this->assertEquals($error, $this->_object->getResponse()->getBody());
    }

    public function testValidateBeforeContinueAction()
    {
        Mage::unregister('mock_helpers');
        Mage::app()->getRequest()
            ->setParam('attribute_set_id', 1)
            ->setParam('attribute_id', 1)
            ->setParam('mapping_attribute_id', 1);

        $helperMock = $this->mockHelper('xcom_mapping', array('getAttribute', 'getProductTypeAttribute'));
        $helperMock->expects($this->once())
                   ->method('getAttribute')
                   ->will($this->returnValue(
                        new Varien_Object(array(
                            'frontend_input' => 'some_wrong_value'
                        ))));
        $helperMock->expects($this->once())
                   ->method('getProductTypeAttribute')
                   ->will($this->returnValue(
                        new Varien_Object(array(
                            'predefined_values' => true,
                            'render_type'       => 'select'
                        ))));
        $this->_mockController(null, new Varien_Object(), array(
            'getRequest', 'getResponse'));

        $this->_object->validateBeforeContinueAction();
        $this->assertEquals(json_encode(array('success' => true)), $this->_object->getResponse()->getBody());
    }

    public function testSaveSetActionNoRelation()
    {
        $request = new Mage_Core_Controller_Request_Http();
        $setId = rand(1, 10000);
        // x < x+1, x = min
        $productTypeId = rand((Xcom_Mapping_Model_Relation::DIRECT_MAPPING + 1), 10000);
        $request->setParam('attribute_set_id', $setId);
        $request->setParam('mapping_product_type_id', $productTypeId);

        $mappingProductTypeMock = $this->mockModel('xcom_mapping/product_type', array(
            'deleteAttributeSetMappingRelation'));
        $mappingProductTypeMock->expects($this->once())
                               ->method('deleteAttributeSetMappingRelation')
                               ->with($this->equalTo($setId));
        $this->_mockController($request, new Varien_Object(), array(
            'getRequest', 'getResponse', '_redirect'));
        $this->_object->saveSetAction();
    }

    public function testSaveSetAction()
    {
        $request = new Mage_Core_Controller_Request_Http();
        $setId = rand(1, 10000);
        $request->setParam('attribute_set_id', $setId);
        $request->setParam('mapping_product_type_id', Xcom_Mapping_Model_Relation::DIRECT_MAPPING);

        $mappingProductTypeMock = $this->mockModel('xcom_mapping/product_type', array(
            'deleteAttributeSetMappingRelation'));
        $mappingProductTypeMock->expects($this->once())
                               ->method('deleteAttributeSetMappingRelation')
                               ->with($this->equalTo($setId));

        $mappingProductTypeMock = $this->mockModel('xcom_mapping/relation', array(
            'saveRelation'));
        $mappingProductTypeMock->expects($this->any())
                               ->method('saveRelation');

        $this->_mockController($request, new Varien_Object(), array(
            'getRequest', 'getResponse', '_redirect'));
        $this->_object->saveSetAction();
    }

    public function testClearTaxonomyAction()
    {
        $session = Mage::getModel('adminhtml/session');

        $mappingRellation = $this->mockResource('xcom_mapping/relation', array(
            'deleteTaxonomy'));
        $mappingRellation->expects($this->once())
                         ->method('deleteTaxonomy');

        $this->_mockController(new Mage_Core_Controller_Request_Http(), new Varien_Object(), array(
            'getRequest', 'getResponse', '_getSession', '_redirect'));

        $this->_mockSession($session);

        $this->_object->clearTaxonomyAction();

        $messages = $session->getMessages();
        $this->assertInstanceOf('Mage_Core_Model_Message_Collection', $messages);

        $success = $messages->getLastAddedMessage();
        $this->assertInstanceOf('Mage_Core_Model_Message_Success', $success);
        $this->assertEquals('Taxonomy was cleared successfully', $success->getCode());
    }

    protected function _getMappingRelation()
    {
        $this->_object->expects($this->any())
                      ->method('_saveAttributeRelation');
    }

    protected function _getMockEntityAttribute($will)
    {
        $entityAttributeMock = $this->mockModel('catalog/entity_attribute', array('load', 'getData'));
        $entityAttributeMock->expects($this->any())
                            ->method('load')
                            ->will($this->returnSelf());
        $entityAttributeMock->expects($this->any())
                            ->method('getData')
                            ->will($this->returnValue($will));
        return $entityAttributeMock;
    }

    protected function _getMockMappingValidator($will)
    {
        $mappingRelationMock = $this->mockModel('xcom_mapping/validator', array(
            'validateIsRequiredAttributeHasMappedValue'));
        $mappingRelationMock->expects($this->once())
                            ->method('validateIsRequiredAttributeHasMappedValue')
                            ->with(
                                $this->equalTo(1),
                                $this->equalTo(1),
                                $this->equalTo(1),
                                $this->equalTo(1))
                            ->will($this->returnValue($will));
        return $mappingRelationMock;
    }

    protected function _mockMenu()
    {
        $blockMock = $this->getMock('Mage_Core_Block_Abstract', array('setActive'));
        $blockMock->expects($this->any())
                  ->method('setActive');
        $layoutMock = $this->getMock('Mage_Core_Model_Layout', array('getBlock'));
        $layoutMock->expects($this->any())
               ->method('getBlock')
               ->will($this->returnValue($blockMock));
        $this->_object->expects($this->any())
                      ->method('getLayout')
                      ->will($this->returnValue($layoutMock));
    }

    protected function _mockSession($session)
    {
        $this->_object->expects($this->any())
                      ->method('_getSession')
                      ->will($this->returnValue($session));
    }
}
