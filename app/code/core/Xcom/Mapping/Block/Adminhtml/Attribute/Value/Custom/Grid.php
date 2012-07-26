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

class Xcom_Mapping_Block_Adminhtml_Attribute_Value_Custom_Grid extends Mage_Adminhtml_Block_Widget_Grid
{
    /**
     * Store magento to target relation
     *
     * @var array
     */
    protected $_valueRelation;

    /**
     * @var Mage_Eav_Model_Entity_Attribute_Abstract
     */
    protected $_magentoAttribute;

    /**
     * Constructor.
     */
    public function __construct()
    {
        parent::__construct();

        $this->setPagerVisibility(false);
        $this->setFilterVisibility(false);

        $this->_emptyText = $this->__('No records found.');
    }

    /**
     * Initialize magento and target attributes.
     *
     * @return Xcom_Mapping_Block_Adminhtml_Attribute_Value_Form
     */
    protected function _initAttributes()
    {
        $this->_magentoAttribute = Mage::registry('current_magento_attribute');
        return $this;
    }

    /**
     * Prepare collection for custom attributes mapping.
     *
     * @return Xcom_Mapping_Block_Adminhtml_Attribute_Value_Custom_Grid
     */
    protected function _prepareCollection()
    {
        $this->_initAttributes();
        $collection = new Varien_Data_Collection();

        $attributeOptions = $this->helper('xcom_mapping')->getAttributeOptionsHash($this->_magentoAttribute);
        foreach ($attributeOptions as $code => $value) {
            $item = array(
                'attribute_code'    => $code,
                'attribute_value'   => $value,
            );
            $collection->addItem(new Varien_Object($item));
        }
        $this->setCollection($collection);
        return parent::_prepareCollection();
    }

    /**
     * Prepare collection for custom attributes mapping grid.
     *
     * @return Xcom_Mapping_Block_Adminhtml_Attribute_Value_Custom_Grid
     */
    protected function _prepareColumns()
    {
        $this->addColumn('attribute_code',
            array(
                'header_css_class' => 'a-center',
//                'header'    => $this->__('Use Mapping'),
                'index'     => 'attribute_code',
                'field_name' => 'attribute_code[]',
                'type'      => 'checkbox',
                'values'    => $this->_getUseMapping(),
                'sortable'  => false,
                'align'     => 'center',
                'width'     => 100,
        ));
        $this->addColumn('attribute_value',
            array(
                'header' => $this->__('Magento Attribute Value'),
                'index'  => 'attribute_value',
                'sortable'  => false,
        ));

        return parent::_prepareColumns();
    }

    /**
     * Retrieve selected related products
     *
     * @return array
     */
    protected function _getUseMapping()
    {
        $attributeSetId         = (int)$this->getRequest()->getParam('attribute_set_id');
        $attributeId            = (int)$this->getRequest()->getParam('attribute_id');
        $mappingValueRelation = $this->getValueRelations($attributeSetId, $attributeId);
        return array_keys($mappingValueRelation);
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
}
