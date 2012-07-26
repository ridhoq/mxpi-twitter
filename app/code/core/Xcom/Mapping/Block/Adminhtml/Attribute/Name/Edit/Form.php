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


class Xcom_Mapping_Block_Adminhtml_Attribute_Name_Edit_Form extends Mage_Adminhtml_Block_Widget_Form
{
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
                'class'     => 'save'
                ))
            );

        return parent::_prepareLayout();
    }

    /**
     * Retrieve attributes by attribute set
     *
     * @param $attributeSetId
     * @return array
     */
    protected function _getAttributesOptionArray($attributeSetId)
    {
        $options = array(
            array(
                'value' => null,
                'label' => '--- Please Select ---'
            )
        );

        $collection = Mage::getResourceModel('catalog/product_attribute_collection')
            ->addVisibleFilter()
            ->setAttributeSetFilter($attributeSetId)
            ->addStoreLabel(Mage_Core_Model_App::ADMIN_STORE_ID)
            ->addFilter('is_user_defined', 1)
            ->unshiftOrder('frontend_label', Varien_Data_Collection_Db::SORT_ORDER_ASC)
            ->load();

        foreach ($collection as $item) {
            $options[] = array(
                'value' => $item->getAttributeId(),
                'label' => sprintf($item->getFrontendLabel())
            );
        }
        return $options;
    }

    /**
     * Retrieve attributes by product type
     *
     * @param $mappingProductTypeId
     * @return array
     */
    protected function _getMappingAttributesOptionArray($mappingProductTypeId)
    {
        $options = array(
            array(
                'value' => null,
                'label' => '--- Please Select ---'
            ),
            array(
                'value' => -1,
                'label' => '--- Custom Attribute ---',
                'style' => 'color: grey'

            ),
        );
        /** @var $collection Xcom_Mapping_Model_Resource_Attribute_Collection */
        $collection = Mage::getResourceModel('xcom_mapping/attribute_collection')
            ->addFilter('mapping_product_type_id', $mappingProductTypeId)
                ->addIsAttributeRequiredColumn()
                ->setOrder('is_required', Varien_Data_Collection_Db::SORT_ORDER_DESC)
                ->setOrder('name', Varien_Data_Collection_Db::SORT_ORDER_ASC)
                ->load();
        foreach ($collection as $item) {
            /** @var $item Xcom_Mapping_Model_Target_Attribute_Name */
            $isRequired         = $item->getIsRequired() ? ' *' : '';
            $options[]    = array(
                'value' => $item->getId(),
                'label' => sprintf("%s %s", $item->getName(),  $isRequired),
                'style' => $isRequired ? 'color:red;' : ''
            );
        }
        return $options;
    }

    /**
     * Prepare attribute mapping form.
     *
     * @return Mage_Adminhtml_Block_Widget_Form
     */
    protected function _prepareForm()
    {
        $form = new Varien_Data_Form(
            array('id' => 'attributeNameForm', 'action' => $this->getUrl('*/*/saveName'), 'method' => 'post'));
        $fieldset = $form->addFieldset('settings',
            array('legend' => $this->__('Attribute Mapping Settings')));

        $attributeId            = (int)$this->getRequest()->getParam('attribute_id');
        $targetAttributeSetId   = (int)$this->getRequest()->getParam('mapping_product_type_id');
        $targetAttributeId      = (int)$this->getRequest()->getParam('mapping_attribute_id');
        $attributeSetId         = (int)$this->getRequest()->getParam('attribute_set_id');

        $fieldset->addField('attribute_set_id', 'hidden', array(
            'name'          => 'attribute_set_id',
            'value'         => $attributeSetId
        ));

        $fieldset->addField('attribute_id', 'select', array(
                'label'     => $this->__('Magento Attribute'),
                'title'     => $this->__('Magento Attribute'),
                'name'      => 'attribute_id',
                'required'  => true,
                'class'     => 'required-entry select validate-select',
                'no_span'   => false,
                'values'    => $this->_getAttributesOptionArray($attributeSetId),
                'value'     => $attributeId,
                'onchange'  => '',
        ));

        $note = $this->__('Items marked in red with an asterisk ( * ) indicate that these Target Attributes are'
            . ' <b>required</b> to be mapped for saving the Mapping of the Attribute Sets');

        $fieldset->addField('mapping_attribute_id', 'select', array(
                'label'     => $this->__('Target Attribute'),
                'title'     => $this->__('Target Attribute'),
                'name'      => 'mapping_attribute_id',
                'required'  => true,
                'class'     => 'required-entry select validate-select',
                'no_span'   => false,
                'values'    => $this->_getMappingAttributesOptionArray($targetAttributeSetId),
                'value'     => $targetAttributeId,
                'onchange'  => '',
                'note'      => $note
        ));

        $fieldset->addField('continue_button', 'note', array(
            'text' => $this->getChildHtml('continue_button'),
        ));

        $form->setUseContainer(true);
        $this->setForm($form);

        return parent::_prepareForm();
    }
}

