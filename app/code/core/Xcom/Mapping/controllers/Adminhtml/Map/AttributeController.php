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

class Xcom_Mapping_Adminhtml_Map_AttributeController extends Mage_Adminhtml_Controller_Action
{
    /**
     * Constructor.
     * Set used module name for translations.
     *
     * @return void
     */
    protected function _construct()
    {
        parent::_construct();
        $this->setUsedModuleName("Xcom_Mapping");
        $this->_publicActions = array('value');
    }

    protected function _getValidator()
    {
        return Mage::getSingleton('xcom_mapping/validator');
    }
    /**
     * Index page for attribute mapping.
     *
     * @return void
     */
    public function indexAction()
    {
        $this->_title($this->__('Manage Attribute Set Mapping'));
        $this->loadLayout();
        $this->_setActiveMenu('channels');
        $this->renderLayout();
    }

    /**
     * Edit attribute set mapping page.
     *
     * @return void
     */
    public function editSetAction()
    {
        //TODO to be done
        $attributeSetId = (int) $this->getRequest()->getParam('attribute_set_id');
        if (empty($attributeSetId)) {
            $this->_redirect('*/*/index');
        }

        if ($this->getRequest()->getParam('mapping_product_type_id')) {
            $this->_title($this->__('Edit Attribute Set Mapping'));
        } else {
            $this->_title($this->__('New Attribute Set Mapping'));
        }

        $this->loadLayout();
        $this->_setActiveMenu('channels');
        $this->renderLayout();
    }

    /**
     * Mass delete action for deleting attribute set mapping.
     *
     * @return void
     */
    public function massSetDeleteAction()
    {
        throw new Exception ('Does not support now!');
        $this->_redirect('*/*/index');
    }

    /**
     * Attribute Name mapping page.
     *
     * @return void
     */
    public function nameAction()
    {
        $attributeSetId = (int)$this->getRequest()->getParam('attribute_set_id');
        $productTypeId  = (int)$this->getRequest()->getParam('mapping_product_type_id');

        $isRequiredAttributeHasMappedValue =
            $this->_getValidator()->validateIsRequiredAttributeHasMappedValue($productTypeId, null, $attributeSetId);
        if (!$isRequiredAttributeHasMappedValue) {
            $this->_getSession()->addError($this->__('You have pending mandatory Attributes to be mapped'));
        }
        $this->_title($this->__('Manage Attribute Mapping'));

        $this->loadLayout();
        $this->_setActiveMenu('channels');
        $this->renderLayout();
    }


    /**
     * Map Attribute page.
     *
     * @return void
     */
    public function editNameAction()
    {
        $this->_title($this->__('New Attribute Mapping'));

        $this->loadLayout();
        $this->_setActiveMenu('channels');
        $this->renderLayout();
    }

    protected function _saveAttributeRelation()
    {
        $attributeId        = (int)$this->getRequest()->getParam('attribute_id');
        $attributeSetId     = (int)$this->getRequest()->getParam('attribute_set_id');
        $mappingAttributeId = (int)$this->getRequest()->getParam('mapping_attribute_id');
        $productTypeId      = (int)$this->getRequest()->getParam('mapping_product_type_id');

        $relationModel      = Mage::getModel('xcom_mapping/relation');
        if($mappingAttributeId == Xcom_Mapping_Model_Relation::DIRECT_MAPPING){
            $relationModel->saveRelation($attributeSetId, $productTypeId, $attributeId, $mappingAttributeId, array());
        }
    }

    /**
     * Map attribute values page.
     *
     * @return void
     */
    public function valueAction()
    {
        $attributeId        = (int)$this->getRequest()->getParam('attribute_id');
        $attributeSetId     = (int)$this->getRequest()->getParam('attribute_set_id');
        $targetAttributeId  = (int)$this->getRequest()->getParam('mapping_attribute_id');
        $productTypeId      = (int)$this->getRequest()->getParam('mapping_product_type_id');
        $this->_saveAttributeRelation();

        if (!Mage::getModel('catalog/entity_attribute')->load($attributeId)->getData('is_user_defined')) {
            $this->_getSession()->addError($this->__('Attribute is not available for mapping.'));
            $this->_redirectReferer();
            return;
        }

        $isRequiredAttributeHasMappedValue =
            $this->_getValidator()->validateIsRequiredAttributeHasMappedValue($productTypeId, $targetAttributeId,
                $attributeSetId, $attributeId);
        if (!$isRequiredAttributeHasMappedValue) {
            $this->_getSession()->addError($this->__('Mandatory Attribute. Please map at least one Value'));
        }

        $targetAttribute = Mage::getModel('xcom_mapping/attribute')->load($targetAttributeId);

        $this->_title($this->__('Attribute Value Mapping'));
        $this->loadLayout();
        $this->_setActiveMenu('channels');

        Mage::register('current_magento_attribute',
            Mage::helper('xcom_mapping')->getAttribute($attributeId, $attributeSetId));
        Mage::register('current_target_attribute', $targetAttribute);

        $this->renderLayout();
    }

    /**
     * Map "Custom Attribute" values page.
     *
     * @return void
     */
    public function valuecustomAction()
    {
        $attributeId        = (int)$this->getRequest()->getParam('attribute_id');
        $attributeSetId     = (int)$this->getRequest()->getParam('attribute_set_id');
        $this->_saveAttributeRelation();

        if (!Mage::getModel('catalog/entity_attribute')->load($attributeId)->getData('is_user_defined')) {
            $this->_getSession()->addError($this->__('Attribute is not available for mapping.'));
            $this->_redirectReferer();
            return;
        }

        $this->_title($this->__('Attribute Value Mapping'));
        $this->loadLayout();
        $this->_setActiveMenu('channels');

        Mage::register('current_magento_attribute',
            Mage::helper('xcom_mapping')->getAttribute($attributeId, $attributeSetId));

        $this->renderLayout();
    }

    /**
     * Redirect after save
     */
    protected function _afterSaveRedirect()
    {
        $redirectBack   = $this->getRequest()->getParam('back', false);
        $options = array(
            'target_system'             => $this->getRequest()->getParam('target_system'),
            'type'                      => 'edit',
            'mapping_product_type_id'   => $this->getRequest()->getParam('mapping_product_type_id'),
            'attribute_set_id'          => $this->getRequest()->getParam('attribute_set_id'),
        );

        if ($redirectBack) {
            $options['mapping_attribute_id']    = $this->getRequest()->getParam('mapping_attribute_id');
            $options['attribute_id']            = $this->getRequest()->getParam('attribute_id');
            // Modify redirect for custom attributes.
            if (is_null($options['mapping_attribute_id'])) {
                $this->_redirect('*/*/valuecustom', $options);
            } else {
                $this->_redirect('*/*/value', $options);
            }
        } else {
            $this->_redirect('*/mapping_attribute/index', $options);
        }
    }

    /**
     * Save attribute mappings action.
     *
     * @return void
     */
    public function saveValueAction()
    {
        $attributeSetId     = $this->getRequest()->getParam('attribute_set_id');
        $attributeId        = $this->getRequest()->getParam('attribute_id');
        $mappingAttributeId = $this->getRequest()->getParam('mapping_attribute_id');
        $values             = $this->getRequest()->getParams();

        try {
            Mage::getModel('xcom_mapping/relation')
                ->saveValuesRelation($attributeSetId, $attributeId, $mappingAttributeId, $values);
            $this->_getSession()->addSuccess($this->__('Attribute Mapping has been saved.'));
        } catch (Mage_Core_Exception $e) {
            $this->_getSession()->addError($e->getMessage());
        }
        $this->_afterSaveRedirect();
    }

    /**
     * Save Custom Attribute mappings action.
     *
     * @return void
     */
    public function saveCustomvalueAction()
    {
        $attributeSetId     = $this->getRequest()->getParam('attribute_set_id');
        $attributeId        = $this->getRequest()->getParam('attribute_id');
        $mappingAttributeId = Xcom_Mapping_Model_Relation::DIRECT_MAPPING;

        $values = array();
        if ($arrAttributeCode = $this->getRequest()->getParam('attribute_code')) {
            $counter = 0;
            foreach ($arrAttributeCode as $value) {
                /*skip saving value of the checkbox in the header*/
                if ($value == 'on') {
                    continue;
                }
                $values['attribute_value_' . $counter] = $value;
                $values['target_attribute_value_' . $counter] = $mappingAttributeId;
                ++$counter;
            }
            unset($arrAttributeCode);
        }

        try {
            Mage::getModel('xcom_mapping/relation')
                ->saveValuesRelation($attributeSetId, $attributeId, $mappingAttributeId, $values);
            $this->_getSession()->addSuccess($this->__('Attribute Mapping has been saved.'));
        } catch (Mage_Core_Exception $e) {
            $this->_getSession()->addError($e->getMessage());
        } catch (Exception $e) {
            Mage::logException($e);
            $this->_getSession()->addError('An error occurred while saving the Attribute Mapping.');
        }
        $this->_afterSaveRedirect();
    }

    /**
     * Attribute Mapping validate before continue Action.
     *
     * @return void
     */
    public function validateBeforeContinueAction()
    {
        // TODO: move to Validator class
        $error = false;

        $attributeId        = (int)Mage::app()->getRequest()->getParam('attribute_id');
        $attributeSetId     = (int)Mage::app()->getRequest()->getParam('attribute_set_id');
        $targetAttributeId  = (int)Mage::app()->getRequest()->getParam('mapping_attribute_id');

        if (empty($attributeId) || empty($attributeSetId) || empty($targetAttributeId) ) {
            $error = true;
        } else {
            $attribute          = Mage::helper('xcom_mapping')->getAttribute($attributeId);
            $targetAttribute    = Mage::helper('xcom_mapping')->getProductTypeAttribute($targetAttributeId);
        }

        if ($error) {
            $this->getResponse()->setBody(Mage::helper('core')->jsonEncode(array(
                'exception' => 1,
            )));
            return;
        }

        $isSelectTargetRenderType       = ($targetAttribute->getRenderType() == 'select');
        $isTextFrontendInput            = (strtolower($attribute->getFrontendInput()) == 'text');
        $isNotSelectFrontendInput       = (strtolower($attribute->getFrontendInput()) != 'select');
        $isNotMultiSelectFrontendInput  = (strtolower($attribute->getFrontendInput()) != 'multiselect');

        $errorConditions = $isNotSelectFrontendInput && $isNotMultiSelectFrontendInput &&
            $targetAttribute->hasPredefinedValues();

        if ($errorConditions && !($isSelectTargetRenderType || $isTextFrontendInput)) {
            $this->getResponse()->setBody(Mage::helper('core')->jsonEncode(array(
                'error'   => 1,
                'message' => $this->__('Cannot map Magento non-Select attribute to Target Select attribute.' .
                                       ' Please choose another Magento attribute of the Select type.')
            )));
            return;
        }

        $this->getResponse()->setBody(Mage::helper('core')->jsonEncode(array('success' => true)));
    }

    /**
     * Save custom set mapping
     */
    public function saveSetAction()
    {
        $attributeSetId         = (int)$this->getRequest()->getParam('attribute_set_id');
        $mappingProductTypeId   = (int)$this->getRequest()->getParam('mapping_product_type_id');
        try {
            Mage::getModel('xcom_mapping/product_type')->deleteAttributeSetMappingRelation($attributeSetId);
            if ($mappingProductTypeId == Xcom_Mapping_Model_Relation::DIRECT_MAPPING) {
                Mage::getModel('xcom_mapping/relation')->saveRelation($attributeSetId, null, null, null, array());
            }
        } catch (Mage_Core_Exception $e) {
            $this->_getSession()->addError($e->getMessage());
        }
        $this->_redirect('*/mapping_attribute/index', array('_current'  => true));
    }

    /**
     * Clear taxonomy mapping
     */
    public function clearTaxonomyAction()
    {
        try {
            Mage::getResourceModel('xcom_mapping/relation')->deleteTaxonomy();
            Mage::dispatchEvent('taxonomy_data_cleared', array());
            $this->_getSession()->addSuccess($this->__('Taxonomy was cleared successfully'));
        } catch (Mage_Core_Exception $e) {
            $this->_getSession()->addError($e->getMessage());
        }
        $this->_redirect('*/map_attribute/index');
    }
}
