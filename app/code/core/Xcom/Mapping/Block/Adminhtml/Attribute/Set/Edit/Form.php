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

class Xcom_Mapping_Block_Adminhtml_Attribute_Set_Edit_Form extends Mage_Adminhtml_Block_Widget_Form
{
    /**
     * Prepare attribute set mapping form.
     *
     * @return Mage_Adminhtml_Block_Widget_Form
     */
    protected function _prepareForm()
    {
        $form = new Varien_Data_Form(array(
            'id' => 'attributeSetForm',
            'action' => $this->getUrl('*/*/saveSet'),
            'method' => 'post'
        ));
        $fieldset = $form->addFieldset('settings', array(
            'legend' => $this->__('Attribute Set Mapping Settings'
        )));

        $attributeSet = $this->getAttributeSet();

        $fieldset->addField('attribute_set', 'label',
            array(
                'label' => $this->__('Magento Attribute Set'),
                'title' => $this->__('Magento Attribute Set'),
                'name' => 'attribute_set',
                'value' => $this->__('Target Attribute Set'),
                'after_element_html'    => '&nbsp;<span class="required">*</span>'
            )
        );

        $mappingProductTypeId = $this->getRequest()->getParam('mapping_product_type_id');
        $fieldset->addField('mapping_product_type_id_src', 'hidden', array(
            'name' => 'mapping_product_type_id_src',
            'value' => $mappingProductTypeId,
        ));

        $fieldset->addField('attribute_set_id', 'hidden', array(
            'name' => 'attribute_set_id',
            'required' => true,
            'value' => $attributeSet->getId(),
        ));

        $fieldset->addField('target_attribute_set_tree', 'note', array(
            'label' => $attributeSet->getAttributeSetName(),
            'title' => $attributeSet->getAttributeSetName(),
            'text'  => $this->getChildHtml('target_attribute_set_tree'),
        ));

        $fieldset->addField('continue_button', 'note', array(
            'text' => $this->getChildHtml('continue_button'),
        ));

        $form->setUseContainer(true);
        $this->setForm($form);

        return parent::_prepareForm();
    }

    /**
     * @return Mage_Core_Model_Abstract
     */
    public function getAttributeSet()
    {
        $attributeSetId = (int)$this->getRequest()->getParam('attribute_set_id');
        $attributeSet = Mage::getModel('eav/entity_attribute_set');
        if ($attributeSetId) {
            $attributeSet->load($attributeSetId);
        }
        return $attributeSet;
    }

    /**
     * Prepare layout.
     * Creates continue button block.
     *
     * @return Mage_Core_Block_Abstract
     */
    protected function _prepareLayout()
    {
        $this->setChild('continue_button',
        $this->getLayout()->createBlock('adminhtml/widget_button')
            ->setData(array(
                'label'     => $this->__('Continue'),
                'onclick'   => sprintf("setSettings('%s', '%s', '%s')",
                    $this->getNoActionUrl(), $this->getSaveUrl(), 'mapping_product_type_id'),
                'class'     => 'save'
            )));

        return parent::_prepareLayout();
    }

    /**
     * Returns continue button url.
     * Default (Index) action.
     *
     * @return string
     */
    public function getNoActionUrl()
    {
        return $this->getUrl('*/mapping_attribute/index', array(
            '_current'  => true,
        ));
    }

    /**
     * Returns continue button url.
     * SaveSet action.
     *
     * @return string
     */
    public function getSaveUrl()
    {
        return $this->getUrl('*/*/saveSet', array(
            '_current'  => true,
            'mapping_product_type_id' => '{{mapping_product_type_id}}'
        ));
    }
}
