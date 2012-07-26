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
class Xcom_Mapping_Block_Adminhtml_Attribute_Set_Edit_Tree extends Mage_Core_Block_Template
{
    /**
     * @var array
     */
    protected $_productTypes = array();

    protected function _construct()
    {
        $this->setTemplate('xcom/mapping/attribute/set/tree.phtml');
        parent::_construct();
    }

    /**
     * @return array
     */
    public function getProductTypeArray()
    {
        if (empty($this->_productTypes)) {
            $productTypes = Mage::getModel('xcom_mapping/source_product_type')->toOptionArray();
            $none = array(array('label' => $this->__('None'), 'value' => '-1', 'active-item' => true));
            $this->_productTypes = array_merge($none, $productTypes);
        }
        return $this->_productTypes;
    }

    /**
     * @return string
     */
    public function getTreeJson()
    {
        $productTypeArray = $this->getProductTypeArray();
        $rootArray = array();
        foreach ($productTypeArray as $productType) {
            $rootArray[] = $this->_getNodeJson($productType);
        }

        return Mage::helper('core')->jsonEncode($rootArray);
    }


    /**
     * Get JSON of a tree node or an associative array
     *
     * @param array $node
     * @param int $level
     * @return string
     */
    protected function _getNodeJson($node, $level = 0)
    {
        $item = array();
        $item['text'] = $node['label'];
        $item['id']  = !is_array($node['value']) ? $node['value'] : '';

        $item['allowDrop'] = false;
        $item['allowDrag'] = false;
        $item['selected_node']  = 0;
        if (is_array($node['value']) && !empty($node['value'])) {
            $item['children'] = array();
            foreach ($node['value'] as $child) {
                $item['children'][] = $this->_getNodeJson($child, $level+1);
            }
        }

        if (isset($node['active-item']) && $node['active-item']) {
            $item['cls'] = 'folder active-category';
        } else {
            $item['cls'] = 'folder no-active-category';
        }

        if ($level < 1) {
            $item['expanded'] = true;
        }

        return $item;
    }

    /**
     * @return int
     */
    public function getSelectedNodeId()
    {
        return (int)$this->getRequest()->getParam('mapping_product_type_id');
    }

    /**
     * @return bool
     */
    public function getIsExpanded()
    {
        return false;
    }
}
