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

class Xcom_Mapping_Block_Adminhtml_Attribute_Name_Grid extends Mage_Adminhtml_Block_Widget_Grid
{
    /**
     * Prepare grid.
     *
     * @return void
     */
    protected function _construct()
    {
        parent::_construct();
        $this->setId('attributeNameGrid');
        $this->setDefaultSort('attribute_name');
        $this->setVarNameFilter('attribute_name_filter');
    }

    /**
     * Prepare collection.
     *
     * @return Xcom_Mapping_Block_Adminhtml_Attribute_Name_Grid
     */
    protected function _prepareCollection()
    {
        $productTypeId  = $this->getRequest()->getParam('mapping_product_type_id', -1);
        $attributeSetId = $this->getRequest()->getParam('attribute_set_id', -1);

        $collection = Mage::getResourceModel('xcom_mapping/attribute_collection')
            ->initAttributeRelations($attributeSetId);

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
        $this->addColumn('attribute',
            array(
                'header'    => $this->__('Magento Attribute'),
                'width'     => '100px',
                'index'     => 'attribute_name',
                'sortable'  => true,
        ));

        $this->addColumn('target_attribute_name',
            array(
                'header'    => $this->__('Target Attribute'),
                'width'     => '100px',
                'index'     => 'mapping_attribute_name',
                'sortable'  => true,
        ));

        $this->setColumnRenderers(array('action' => 'xcom_mapping/adminhtml_widget_grid_column_renderer_action'));
        $this->addColumn('action',
            array(
                'header'    => $this->__('Action'),
                'width'     => '50px',
                'type'      => 'action',
                'getter'    => 'getAttributeId',
                'actions'   => array(
                    array(
                        'caption'       => $this->__('Edit'),
                        'url'           => array(
                            'base'      =>'*/*/value',
                            'params'    => array(
                                'target_system'             => $this->getRequest()->getParam('target_system'),
                                'mapping_product_type_id'   => $this->getRequest()->getParam('mapping_product_type_id'),
                                'attribute_set_id'          => $this->getRequest()->getParam('attribute_set_id'),
                                'type'                      => $this->getRequest()->getParam('type', null)
                            )
                        ),
                        'field'         => 'attribute_id',
                        'attributes'    => array('mapping_attribute_id', 'mapping_product_type_id')
                    )
                ),
                'filter'    => false,
                'sortable'  => false,
        ));

        return parent::_prepareColumns();
    }
}
