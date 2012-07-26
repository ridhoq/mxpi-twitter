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
 require_once 'Mage/Adminhtml/Controller/Action.php';

class Xcom_Mapping_Adminhtml_Mapping_AttributeController extends Mage_Adminhtml_Controller_Action
{
    /**
     * Store parameters to registry
     *
     * @return Varien_Object
     */
    protected function _initParams()
    {
        $params = new Varien_Object($this->getRequest()->getParams());
        Mage::register('current_params', $params);
        return $params;
    }

    /**
     * validate request parameters
     *
     * @return bool
     */
    protected function _validateRequestParams()
    {
        $params     = Mage::registry('current_params');
        $isValidParams = true;
        if ($params->getMappingProductTypeId() && $params->getAttributeSetId()) {
            $mappingProductTypeId = Mage::getModel('xcom_mapping/product_type')
                ->load((int)$params->getMappingProductTypeId())
                ->getId();
            $attributeSetId = Mage::getModel('eav/entity_attribute_set')
                ->load((int)$params->getAttributeSetId())
                ->getId();
            if ($attributeSetId == null || ($mappingProductTypeId == null
                && $params->getMappingProductTypeId() != Xcom_Mapping_Model_Relation::DIRECT_MAPPING)) {
                $isValidParams = false;
            }
        } else {
            $isValidParams = false;
        }
        if (!$isValidParams) {
            $this->_getSession()->addError($this->__('Invalid Product Type or Attribute Set!'));
            $this->_redirect('*/map_attribute/index');
            return false;
        }
        return $isValidParams;
    }

    /**
     * Validate attribute mapping
     */
    protected function _validateMapping()
    {
        $params     = Mage::registry('current_params');
        /** @var $validator Xcom_Mapping_Model_Validator */
        $validator  = Mage::getSingleton('xcom_mapping/validator');
        $isRequiredAttributeHasMappedValue = $validator->validateIsRequiredAttributeHasMappedValue(
            $params->getMappingProductTypeId(),
            null, $params->getAttributeSetId());
        if (!$isRequiredAttributeHasMappedValue) {
            $this->_getSession()->addError($this->__('You have pending mandatory Attributes to be mapped'));
        }
    }

    /**
     * Index action
     */
    public function indexAction()
    {
        $this->_initParams();
        $this->_validateMapping();
        $this->loadLayout();

        $this->_validateRequestParams();
        $this->_title($this->__('Manage Attributes Mapping'));
        $this->_setActiveMenu('channels');
        $this->renderLayout();
    }

    /**
     * Save attribute relation
     */
    public function saveAction()
    {
        $params = $this->_initParams();
        try {
            Mage::getModel('xcom_mapping/relation')->saveRelation(
                $params->getAttributeSetId(),
                $params->getMappingProductTypeId(),
                $params->getAttributeId(),
                $params->getMappingAttributeId(),
                array()
            );
        } catch (Mage_Core_Exception $e) {
            $this->_getSession()->addError($e->getMessage());
        }
        $this->_redirectToIndex($params);
    }

    public function deleteAction()
    {
        $params     = $this->_initParams();
        try {
            Mage::getModel('xcom_mapping/attribute')->deleteRelation($params->getRelationAttributeIds());
        } catch (Mage_Core_Exception $e) {
            $this->_getSession()->addError($e->getMessage());
        }
        $this->_redirectToIndex($params);
    }

    /**
     * @param Varien_Object $params
     * @return void
     */
    protected function _redirectToIndex(Varien_Object $params)
    {
        $this->_redirect('*/*/index', array(
            'attribute_set_id'          => $params->getAttributeSetId(),
            'mapping_product_type_id'   => $params->getMappingProductTypeId()
        ));
    }
}
