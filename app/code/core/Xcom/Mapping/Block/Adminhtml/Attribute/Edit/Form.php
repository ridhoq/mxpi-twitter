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


class Xcom_Mapping_Block_Adminhtml_Attribute_Edit_Form extends Mage_Adminhtml_Block_Widget_Form
{
    /**
     * Required attributes flag.
     *
     * @var bool
     */
    protected $_requiredAttrExists = false;

    public function __construct()
    {
        parent::__construct();
        $this->setId('edit_form');
        $this->_controller = 'adminhtml_mapping_attribute';
    }

    /**
     * @return Xcom_Mapping_Block_Adminhtml_Attribute_Form
     */
    protected function _prepareLayout()
    {
        parent::_prepareLayout();
        Varien_Data_Form::getFieldsetElementRenderer()
            ->setTemplate('xcom/mapping/widget/form/renderer/fieldset/element.phtml');
        Varien_Data_Form::getFieldsetRenderer()
            ->setTemplate('xcom/mapping/widget/form/renderer/fieldset.phtml');
        return $this;
    }

    /**
     * Prepare attribute mapping form.
     *
     * @return Mage_Adminhtml_Block_Widget_Form
     */
    protected function _prepareForm()
    {
        $params = Mage::registry('current_params');

        $form = new Varien_Data_Form(array(
            'id'        => $this->getId(),
            'action'    => $this->getUrl('*/*/save'),
            'method'    => 'post')
        );

        $fieldset = $form->addFieldset('settings', array(
            'legend' => $this->__('Attribute Mapping Settings')
        ));

        $fieldset->addType('select_with_size',
            Mage::getConfig()->getBlockClassName('xcom_mapping/form_element_select'));

        $form->addData($params->getData());

        $fieldset->addField('attribute_set_id', 'hidden', array(
            'name'      => 'attribute_set_id',
            'value'     => $params->getAttributeSetId()
        ));

        $fieldset->addField('mapping_product_type_id', 'hidden', array(
            'name'      => 'mapping_product_type_id',
            'value'     => $params->getMappingProductTypeId()
        ));
        $fieldset->addField('attributes', 'note', array(
            'text'      => '<tr>'
        ));

        $fieldset->addField('attribute_id', 'select_with_size', array(
            'label'     => $this->__('Magento Attribute'),
            'title'     => $this->__('Magento Attribute'),
            'name'      => 'attribute_id',
            'size'      => 10,
            'required'  => true,
            'value_class' => 'select-div',
            'style'     => 'width:320px;height:150px',
            'class'     => 'required-entry select validate-select',
            'no_span'   => false,
            'values'    => $this->getAttributesOptionArray($params->getAttributeSetId())
        ));

        $fieldset->addField('mapping_attribute_id', 'select_with_size', array(
            'label'     => $this->__('X.commerce Attribute'),
            'title'     => $this->__('X.commerce Attribute'),
            'name'      => 'mapping_attribute_id',
            'size'      => 10,
            'required'  => true,
            'value_class' => 'select-div',
            'class'     => 'required-entry select validate-select',
            'style'     => 'width:320px;height:150px',
            'no_span'   => false,
            'values'    => $this->_getMappingAttributesOptionArray(),
            'required_attr_exists' => $this->getRequiredAttrExists(),
        ));

        $widgetButtonStyle = $this->getRequiredAttrExists() ? 'position:relative;left:125px;top:-35px'
            : 'position:relative;left:125px;';
        $button = $this->getLayout()->createBlock('adminhtml/widget_button')
            ->setData(array(
                'label'     => $this->__('Add Attribute Mapping'),
                'onclick'   => 'editForm.submit()',
                'class'     => 'save',
                'style'     => $widgetButtonStyle
            ));

        $fieldset->addField('button', 'note', array(
            'value_class' => 'button-div',
            'text'      => $button->toHtml()
        ));

        $form->setUseContainer(true);
        $this->setForm($form);

        return parent::_prepareForm();
    }

    /**
     * Returns attributes by attribute set
     *
     * @param int $attributeSetId
     * @return array
     */
    public function getAttributesOptionArray($attributeSetId)
    {
        $relation = Mage::getModel('xcom_mapping/relation');
        $options = array();
        $collection = Mage::getResourceModel('catalog/product_attribute_collection')
            ->addVisibleFilter()
            ->setAttributeSetFilter($attributeSetId)
            ->addStoreLabel(Mage_Core_Model_App::ADMIN_STORE_ID)
            ->addFilter('is_user_defined', 1)
            ->unshiftOrder('frontend_label', Varien_Data_Collection::SORT_ORDER_ASC);
        $relation->addFilterOnlyMappedAttributes($collection, $attributeSetId);

        foreach ($collection as $item) {
            $options[] = array(
                'value' => $item->getAttributeId(),
                'label' => sprintf($item->getFrontendLabel())
            );
        }
        return $options;
    }

    /**
     * Retrieve attributes by product type.
     *
     * @return array
     */
    protected function _getMappingAttributesOptionArray()
    {
        $params = Mage::registry('current_params');
        $options = array(
            array(
                'value' => -1,
                'label' => 'Custom Attribute',
                'style' => 'color: grey'

            ),
        );
        $relation = Mage::getModel('xcom_mapping/relation');
        /** @var $collection Xcom_Mapping_Model_Resource_Attribute_Collection */
        $collection = Mage::getResourceModel('xcom_mapping/attribute_collection')
            ->addFilter('mapping_product_type_id', $params->getMappingProductTypeId())
            ->addIsAttributeRequiredColumn()
            ->setOrder('is_required', Varien_Data_Collection_Db::SORT_ORDER_DESC)
            ->setOrder('name', Varien_Data_Collection_Db::SORT_ORDER_ASC);
        $relation->addFilterOnlyMappedMappingAttributes($collection, $params->getAttributeSetId());
        foreach ($collection as $item) {
            /** @var $item Xcom_Mapping_Model_Target_Attribute_Name */
            $isRequired         = $item->getIsRequired() ? ' *' : '';
            /* turn on the flag if we have any required attributes */
            if ($item->getIsRequired()) {
                $this->setRequiredAttrExists(true);
            }
            $options[]    = array(
                'value' => $item->getId(),
                'label' => sprintf("%s %s", $item->getName(),  $isRequired),
                'style' => $isRequired ? 'color:red;' : ''
            );
        }
        return $options;
    }
}
