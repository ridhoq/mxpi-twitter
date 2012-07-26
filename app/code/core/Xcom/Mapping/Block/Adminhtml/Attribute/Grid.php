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

class Xcom_Mapping_Block_Adminhtml_Attribute_Grid extends Mage_Adminhtml_Block_Widget_Grid
{
    /**
     * Prepare grid.
     *
     * @return void
     */
    protected function _construct()
    {
        parent::_construct();
        $this->setId('attributes_grid');
    }

    /**
     * Prepare collection.
     *
     * @return Xcom_Mapping_Block_Adminhtml_Attribute_Grid
     */
    protected function _prepareCollection()
    {
        $params     = Mage::registry('current_params');
        $collection = Mage::getResourceModel('xcom_mapping/attribute_collection')
            ->setUniqueIdentifier('relation_attribute_id')
            ->initAttributeRelations($params->getAttributeSetId());

        $collection->addIsAttributeRequiredColumn()
            ->addIsAttributeHasMappedValues();
        $this->setFilterVisibility(false);
        $this->setCollection($collection);
        parent::_prepareCollection();
        return $this;
    }

    /**
     * Prepare grid columns.
     *
     * @return Mage_Adminhtml_Block_Widget_Grid
     */
    protected function _prepareColumns()
    {
        $params = Mage::registry('current_params');

        $this->setColumnRenderers(array(
            'custom_attribute'  => 'xcom_mapping/adminhtml_widget_grid_column_renderer_text',
            'action'            => 'xcom_mapping/adminhtml_widget_grid_column_renderer_attribute_action'
        ));

        $this->addColumn('attribute',
            array(
                'header'    => $this->__('Magento Attribute'),
                'width'     => '100px',
                'index'     => 'attribute_name',
                'sortable'  => true,
        ));

        $this->addColumn('mapping_attribute_name',
            array(
                'header'    => $this->__('Target Attribute'),
                'width'     => '100px',
                'type'      => 'custom_attribute',
                'index'     => 'mapping_attribute_name',
                'sortable'  => true,
        ));

        $this->addColumn('action', array(
            'header'            => $this->__('Action'),
            'width'             => '100px',
            'type'              => 'action',
            'getter'            => 'getAttributeId',
            'actions'           => array(
                array(
                    'caption'       => $this->__('Edit Mapping'),
                    'is_mapped'     => 1,
                    'url'           => array(
                        'base'              =>'*/map_attribute/value',
                        'params'    => array(
                            'mapping_product_type_id'   => $params->getMappingProductTypeId(),
                            'attribute_set_id'          => $params->getAttributeSetId()
                        )
                    ),
                    'field'         => 'attribute_id',
                    'attributes'    => array('mapping_attribute_id', 'mapping_product_type_id')
                ),
                array(
                    'caption' => $this->__('Map Now'),
                    'is_mapped' => 0,
                    'url'     => array(
                        'base'=>'*/map_attribute/value',
                        'params'    => array(
                            'mapping_product_type_id'   => $params->getMappingProductTypeId(),
                            'attribute_set_id'          => $params->getAttributeSetId()
                        )
                    ),
                    'field'         => 'attribute_id',
                    'attributes'    => array('mapping_attribute_id', 'mapping_product_type_id')
                ),
            ),
            'filter'    => false,
            'sortable'  => false,
    ));

        return parent::_prepareColumns();
    }

    /**
     * Prepare grid mass-action block
     *
     * @return Xcom_Mapping_Block_Adminhtml_Attribute_Grid
     */
    protected function _prepareMassaction()
    {
        $params = Mage::registry('current_params');
        $this->setMassactionIdField('relation_attribute_id');
        $this->getMassactionBlock()->setFormFieldName('relation_attribute_ids');

        $this->getMassactionBlock()->addItem('delete', array(
             'label'=> $this->__('Delete'),
             'url'  => $this->getUrl('*/*/delete', array(
                 'mapping_product_type_id'   => $params->getMappingProductTypeId(),
                 'attribute_set_id'          => $params->getAttributeSetId()
             ))
        ));

        return $this;
    }

    /**
     * Prepare grid massaction column
     *
     * @return Xcom_Mapping_Block_Adminhtml_Attribute_Grid
     */
    protected function _prepareMassactionColumn()
    {
        parent::_prepareMassactionColumn();
        $this->_columns['massaction']->setUseIndex(true);
        return $this;
    }
}
