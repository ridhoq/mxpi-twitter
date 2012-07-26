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
class Xcom_Mapping_Block_Adminhtml_Attribute_Set_Grid extends Mage_Adminhtml_Block_Widget_Grid
{
    /**
     * Prepare grid.
     *
     * @return void
     */
    protected function _construct()
    {
        parent::_construct();
        $this->setId('attributeSetGrid');
        $this->setDefaultSort('attribute_name');
        $this->setDefaultDir('desc');
        $this->setVarNameFilter('attribute_set_filter');
    }

    /**
     * @return Xcom_Mapping_Block_Adminhtml_Attribute_Set_Grid
     */
    protected function _prepareCollection()
    {
        /** @var $collection Xcom_Mapping_Model_Resource_Product_Type_Collection */
        $collection = Mage::getResourceModel('xcom_mapping/product_type_collection')
            ->initProductTypeRelations();

        if ($this->getColumn('product_type_name')) {
            $this->getColumn('product_type_name')->setFilterIndex($collection->getProductTypeNameExpr());
        }

        $this->setCollection($collection);
        return parent::_prepareCollection();
    }

    /**
     * @return Xcom_Mapping_Block_Adminhtml_Attribute_Set_Grid
     */
    protected function _afterLoadCollection()
    {
        /** @var $validator Xcom_Mapping_Model_Validator */
        $validator = Mage::getSingleton('xcom_mapping/validator');
        foreach($this->getCollection() as $item) {
            $id = $item->getMappingProductTypeId();
            $attributeSetId = $item->getAttributeSetId();
            if (!$validator->validateIsRequiredAttributeHasMappedValue($id, null, $attributeSetId)) {
                $item->setProductTypeName($item->getProductTypeName() . ' (' . $this->__('Incomplete') . ')');
            }
        }
        return parent::_afterLoadCollection();
    }

    /**
     * Prepare grid columns.
     *
     * @return Mage_Adminhtml_Block_Widget_Grid
     */
    protected function _prepareColumns()
    {
        $this->addColumn('attribute_set_name', array(
            'header'=> $this->__('Attribute Set'),
            'index' => 'attribute_set_name',
            'sortable'  => false,
        ));
        $this->addColumn('product_type_name', array(
            'header'=> $this->__('Target Attribute Set'),
            'index' => 'product_type_name',
        ));

        $this->setColumnRenderers(array('action' => 'xcom_mapping/adminhtml_widget_grid_column_renderer_action'));
        $this->addColumn('action',
            array(
                'header'            => $this->__('Action'),
                'width'             => '100px',
                'type'              => 'action',
                'is_mapped_getter'  => 'getMappingProductTypeId',
                'getter'            => 'getAttributeSetId',
                'actions'           => array(
                    array(
                        'caption'   => $this->__('Edit Mapping'),
                        'is_mapped' => 1,
                        'url'       => array(
//                            'base'   => '*/mapping_attribute/index',
                            'base'   => '*/*/editSet',
                            'params' => array(
                                'target_system' => 'xcommerce',
                                'type'          => 'edit'
                            )
                        ),
                        'field'         => 'attribute_set_id',
                        'attributes'    => array('mapping_product_type_id')
                    ),
                    array(
                        'caption'   => $this->__('Map Now'),
                        'is_mapped' => 0,
                        'url'       => array(
                            'base'   => '*/*/editSet',
                            'params' => array(
                                'target_system' => 'xcommerce'
                            ),
                        ),
                        'field'   => 'attribute_set_id'
                    ),
                ),
                'filter'    => false,
                'sortable'  => false,
        ));

        return parent::_prepareColumns();
    }

    /**
     * @return Xcom_Mapping_Block_Adminhtml_Attribute_Set_Grid
     */
    protected function _prepareGrid()
    {
        parent::_prepareGrid();
        $massaction = $this->getColumn('massaction');
        if ($massaction) {
            $massaction->setData('width', '30');
        }
        return $this;
    }
}
