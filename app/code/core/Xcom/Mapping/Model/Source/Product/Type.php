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
class Xcom_Mapping_Model_Source_Product_Type extends Varien_Object
{
    protected $_tree = array();

    public function __construct()
    {
        $data = Mage::getResourceModel('xcom_mapping/product_type')
            ->getTypesClassesTree();
        $this->setData($data);
        return $this;
    }

    protected function _buildTree()
    {
        $data = $this->getData();
        foreach ($data as $item) {
            if ($item['parent'] == 0) {
                $this->_addChild($this->_tree, $item, 0, $data);
            }
        }
        return $this;
    }

    protected function _addChild(&$parent, $item, $level, $data)
    {
        $item['level'] = $level;
        $item['children'] = array();

        $itemId = $item['id'];
        if ($item['type'] == 'class') {
            foreach ($data as $id => $element) {
                if ($element['parent'] == $itemId) {
                    $data[$id] = false;
                    $this->_addChild($item['children'], $element, $level + 1, $data);
                }
            }
        } else {
            $itemId .= '_type';
        }

        $parent[$itemId] = $item;
        return $this;
    }

    public function getTree()
    {
        if (empty($this->_tree)) {
            $this->_buildTree();
        }
        return $this->_tree;
    }

    /**
     * Retrieve options array.
     *
     * @var $data array options
     * @return array
     */
    public function toOptionArray()
    {
        $tree = $this->getTree();
        // Prepare options.
        $optionArray = array();

        foreach ($tree as $item) {
            $this->_addOption($optionArray, $item);
        }
        return $optionArray;
    }

    /**
     * @param array $options
     * @param array $element
     * @return bool
     */
    protected function _addOption(&$options, $element)
    {
        $option = array(
            'label' => $element['name']
        );

        if ($element['type'] == 'class') {
            $option['value'] = array();
            foreach ($element['children'] as $child) {
                $this->_addOption($option['value'], $child);
            }
        } else {
            $option['value'] = $element['id'];
            $option['active-item'] = true;
        }

        $options[] = $option;

        return true;
    }
}
