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
 * @package     Xcom_Xfabric
 * @copyright   Copyright (c) 2010 Magento Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/**
 * Render block specified in <block> node
 *
 * @category    Xcom
 * @package     Xcom_Xfabric
 */
class Xcom_Xfabric_Block_Adminhtml_System_Config_Form_Fieldset_Element_Renderer_Block
    extends Xcom_Xfabric_Block_Adminhtml_System_Config_Form_Fieldset_Element_Renderer_Raw
{
    /**
     * Renders block and returns its output
     *
     * @param Varien_Data_Form_Element_Abstract $element
     * @return string
     */
    protected function _getElementHtml(Varien_Data_Form_Element_Abstract $element)
    {
        // Specified in <block> node in the field description
        $blockType = isset($element->getFieldConfig()->block) ? (string)$element->getFieldConfig()->block : '';

        if (!$blockType) {
            return '';
        }

        /* @var $block Mage_Adminhtml_Block_Template */
        $block = Mage::app()->getLayout()->createBlock($blockType);

        if (isset($element->getFieldConfig()->config)) {
            // Pass data to created block from <config> node
            foreach ($element->getFieldConfig()->config->children() as $key => $value) {
                $block->setData($key, (string)$value);
            }
        }

        if (isset($element->getFieldConfig()->template)) {
            $block->setTemplate((string)$element->getFieldConfig()->template);
        }

        return $block->toHtml();
    }
}
