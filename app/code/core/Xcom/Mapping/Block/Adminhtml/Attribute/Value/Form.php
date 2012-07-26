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

class Xcom_Mapping_Block_Adminhtml_Attribute_Value_Form extends Mage_Adminhtml_Block_Widget_Form
{
    /**
     * Target attribute options.
     *
     * @var array
     */
    protected $_targetAttributeValues;

    /**
     * Store magento to target relation
     *
     * @var array
     */
    protected $_valueRelation;

    /**
     * @var Xcom_Mapping_Model_Target_Attribute_Name
     */
    protected $_targetAttribute;

    /**
     * @var Mage_Eav_Model_Entity_Attribute_Abstract
     */
    protected $_magentoAttribute;

    /**
     * @var int|string
     */
    protected $_variableCode = '';

    /**
     * Initialize magento and target attributes.
     *
     * @return Xcom_Mapping_Block_Adminhtml_Attribute_Value_Form
     */
    protected function _initAttributes()
    {
        $this->_magentoAttribute = Mage::registry('current_magento_attribute');
        $this->_targetAttribute = Mage::registry('current_target_attribute');

        return $this;
    }

    /**
     * Returns target attribute option values.
     *
     * @param Xcom_Mapping_Model_Target_Attribute_Name $targetAttribute
     * @return array
     */
    public function getTargetAttributeValues($targetAttributeId)
    {
        if (null === $this->_targetAttributeValues) {
            /** @var $collection Xcom_Mapping_Model_Resource_Target_Attribute_Value_Collection */
            $collection = Mage::getModel('xcom_mapping/attribute_value')->getCollection();
            $options = array(
                array(
                    'value' => '',
                    'label' => $this->__("--- Please Select One ---")
                ));

            if (!$this->_targetAttribute->getIsRestricted()) {
                $options[] = array(
                    'value' => -1,
                    'label' => $this->__("--- Custom Value ---"),
                    'style' => 'color: grey'
                );
            }
            $targetAttributeValues = $collection
                ->addFieldToFilter('mapping_attribute_id', (int)$targetAttributeId)
                ->load()
                ->toOptionArray();

            if (count($targetAttributeValues) === 1) {
                $this->_variableCode = $targetAttributeValues[0]['value'];
            }

            $targetAttributeValues = array_merge($options, $targetAttributeValues);


            $this->_targetAttributeValues = $targetAttributeValues;
        }

        return $this->_targetAttributeValues;
    }

    /**
     * Returns relation between attribute values and mapping-attribute values
     *
     * @param int $attributeSetId
     * @param int $attributeId
     * @return array
     */
    public function getValueRelations($attributeSetId, $attributeId)
    {
        if (!$this->_valueRelation) {
            $this->_valueRelation = Mage::getResourceModel('xcom_mapping/attribute_value_collection')
            ->initValueRelations($attributeSetId, $attributeId)
            ->toOptionHash('value_id', 'mapping_value_form_id');
        }
        return $this->_valueRelation;
    }

    /**
     * Prepare attribute mapping form.
     *
     * @return Mage_Adminhtml_Block_Widget_Form
     */
    protected function _prepareForm()
    {
        $this->_initAttributes();
        $form = new Varien_Data_Form(array(
            'id' => 'attributeValueForm',
            'action' => $this->getFormUrl(),
            'method' => 'post'
        ));
        /** @var $fieldset Varien_Data_Form_Element_Fieldset */
        $fieldset = $form->addFieldset('settings',
            array('legend' => $this->__('Attribute Value Mapping Settings')));

        $fieldset->addType('label', 'Xcom_Mapping_Block_Form_Element_Label');
        $fieldset->addField('attribute_name', 'label', array(
            'label'         => $this->__("Magento Attribute Value"),
            'name'          => 'attribute_name',
            'required'      => false,
            'class'         => '',
            'bold'          => true,
            'value'         => $this->__("Target Attribute Value"),
            'bold_label'    => true
        ));

        $this->_addHiddenFieldWithRequestParam($fieldset, 'mapping_attribute_id');
        $this->_addHiddenFieldWithRequestParam($fieldset, 'attribute_id');
        $this->_addHiddenFieldWithRequestParam($fieldset, 'mapping_product_type_id');
        $this->_addHiddenFieldWithRequestParam($fieldset, 'attribute_set_id');

        if (!$this->_targetAttribute || !$this->_magentoAttribute) {
            return parent::_prepareForm();
        }

        $this->_initFields($fieldset);

        $form->setUseContainer(true);
        $this->setForm($form);

        return parent::_prepareForm();
    }

    /**
     * Add hidden field to the fieldset.
     *
     * @param Varien_Data_Form_Element_Fieldset $fieldset
     * @param string $name
     * @return Xcom_Mapping_Block_Adminhtml_Attribute_Value_Form
     */
    protected function _addHiddenFieldWithRequestParam($fieldset, $name)
    {
        $fieldset->addField($name, 'hidden', array(
            'name'          => $name,
            'value'         => $this->getRequest()->getParam($name)
        ));
        return $this;
    }

    /**
     * Prepare form fields.
     *
     * @param Varien_Data_Form_Element_Fieldset $fieldset
     * @return Xcom_Mapping_Block_Adminhtml_Attribute_Value_Form
     */
    protected function _initFields($fieldset)
    {

        $attributeType = $this->_targetAttribute->getAttributeType();
        if (!in_array($attributeType, array('boolean'))) {

            $this->_initSelectFields($fieldset);

        } else {
            $this->_initTextField($fieldset);
        }
        return $this;
    }


    /**
     * Prepare select fields.
     *
     * @param Varien_Data_Form_Element_Fieldset $fieldset
     * @param Varien_Object $attribute
     * @param Xcom_Mapping_Model_Target_Attribute_Name $targetAttribute
     * @return Xcom_Mapping_Block_Adminhtml_Attribute_Value_Form
     */
    protected function _initSelectFields($fieldset)
    {
        $attributeSetId         = (int)$this->getRequest()->getParam('attribute_set_id');
        $attributeId            = (int)$this->getRequest()->getParam('attribute_id');
        $attributeOptions       = $this->helper('xcom_mapping')->getAttributeOptionsHash($this->_magentoAttribute);
        $targetAttributeValues  = $this->getTargetAttributeValues($this->_targetAttribute->getId());
        $mappingValueRelation   = $this->getValueRelations($attributeSetId, $attributeId);

        $i = 0;
        foreach ($attributeOptions as $code => $value) {
            if (!empty($mappingValueRelation[$code])) {
                $mappingValue = $mappingValueRelation[$code];
            } else {
                $mappingValue = $this->_variableCode;
            }
            $fieldset->addField('target_attribute_value_' . $i, 'select', array(
                'label'     => $value,
                'title'     => $this->__("Target Attribute Value"),
                'name'      => 'target_attribute_value_' . $i,
                'required'  => false,
                'class'     => '',
                'no_span'   => false,
                'values'    => $targetAttributeValues,
                'value'     => $mappingValue,
                'onchange'  => '',
            ));
            $fieldset->addField('attribute_value_' . $i, 'hidden', array(
                'value'     => $code,
                'name'      => 'attribute_value_' . $i,
            ));
            $i++;
        }
    }
    /**
     * Returns form url.
     *
     * @return string
     */
    public function getFormUrl()
    {
        return $this->getUrl('*/*/saveValue');
    }

    /**
     * Return array of text values by attribute id
     *
     * @param $attributeId
     * @return array
     */
    protected function _getTextValuesArray($attributeId)
    {
        $attributeModel = Mage::getModel('xcom_mapping/target_attribute');
        $textValues = $attributeModel->getMappedTextAttributes($attributeId);
        return $textValues;
    }
}
