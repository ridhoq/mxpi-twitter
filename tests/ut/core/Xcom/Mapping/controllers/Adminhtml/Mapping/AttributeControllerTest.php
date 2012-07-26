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
require_once 'Xcom/Mapping/controllers/Adminhtml/Mapping/AttributeController.php';

class Xcom_Mapping_Adminhtml_Mapping_AttributeControllerTest extends Xcom_TestCase
{
    /** @var Xcom_Mapping_Adminhtml_Mapping_AttributeController  */
    protected $_object;

    public function tearDown()
    {
        $this->_object = null;
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
        $this->_object = $this->getMock('Xcom_Mapping_Adminhtml_Mapping_AttributeController', $methods);
        $this->_object->expects($this->any())
            ->method('getRequest')
            ->will($this->returnValue($request));
        $this->_object->expects($this->any())
            ->method('getResponse')
            ->will($this->returnValue($response));
    }

    public function testSaveAction()
    {
        $attributeSetId = 'attribute_set_id_test';
        $mappingProductTypeId = 'map_prod_type_id_test';
        $attributeId = 'attribute_id_test';
        $mappingAttributeId = Xcom_Mapping_Model_Relation::DIRECT_MAPPING;

        $request = $this->_getRequest(
            $attributeSetId,
            $mappingProductTypeId,
            $attributeId,
            $mappingAttributeId);

        $this->_mockController($request, new Varien_Object(), array('_redirect', 'getRequest', 'getResponse'));

        $this->_mockRedirect($attributeSetId, $mappingProductTypeId);

        $relationMock = $this->_mockRelationModel(
            $attributeSetId,
            $mappingProductTypeId,
            $attributeId,
            $mappingAttributeId);

        $this->_object->saveAction();
    }

    protected function _mockRedirect($attributeSetId, $mappingProductTypeId)
    {
        $this->_object->expects($this->once())
            ->method('_redirect')
            ->with($this->equalTo('*/*/index'), $this->equalTo(array(
                'attribute_set_id' => $attributeSetId,
                'mapping_product_type_id' => $mappingProductTypeId)));
    }

    protected function _mockRelationModel($attributeSetId, $mappingProductTypeId, $attributeId, $mappingAttributeId)
    {
        $relationMock = $this->mockModel('xcom_mapping/relation', array('saveRelation'));
        $relationMock->expects($this->once())
            ->method('saveRelation')
            ->with(
                $this->equalTo($attributeSetId),
                $this->equalTo($mappingProductTypeId),
                $this->equalTo($attributeId),
                $this->equalTo($mappingAttributeId),
                $this->equalTo(array())
            );
        return $relationMock;
    }

    protected function _getRequest($attributeSetId, $mappingProductTypeId, $attributeId, $mappingAttributeId)
    {
        $request = new Varien_Object(array('params' => array(
            'attribute_set_id'        => $attributeSetId,
            'mapping_product_type_id' => $mappingProductTypeId,
            'attribute_id'            => $attributeId,
            'mapping_attribute_id'    => $mappingAttributeId
        )));
        return $request;
    }

    public function testDeleteAction()
    {
        $request = new Varien_Object(array('params' => array(
            'relation_attribute_ids'    => array(1,2,3),
            'attribute_set_id' => 'attribute_set_id_test',
            'mapping_product_type_id' => 'map_prod_type_id_test',
        )));

        $this->_mockController($request, new Varien_Object(), array('_redirect', 'getRequest', 'getResponse'));
        $this->_object->expects($this->once())
            ->method('_redirect')
            ->with($this->equalTo('*/*/index'), $this->equalTo(array(
                'attribute_set_id' => 'attribute_set_id_test',
                'mapping_product_type_id' => 'map_prod_type_id_test')));

        $relationMock = $this->mockModel('xcom_mapping/attribute', array('deleteRelation'));
        $relationMock->expects($this->once())
            ->method('deleteRelation')
            ->with($this->equalTo(array(1,2,3)));

        $this->_object->deleteAction();
    }
}
