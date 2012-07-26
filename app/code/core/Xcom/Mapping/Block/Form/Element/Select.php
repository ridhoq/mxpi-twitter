<?php
/**
 * Magento Enterprise Edition
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Magento Enterprise Edition License
 * that is bundled with this package in the file LICENSE_EE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://www.magentocommerce.com/license/enterprise-edition
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
 * @category    Varien
 * @package     Varien_Data
 * @copyright   Copyright (c) 2010 Magento Inc. (http://www.magentocommerce.com)
 * @license     http://www.magentocommerce.com/license/enterprise-edition
 */

/**
 * Form select element
 *
 * @category   Varien
 * @package    Varien_Data
 * @author      Magento Core Team <core@magentocommerce.com>
 */
class Xcom_Mapping_Block_Form_Element_Select extends Varien_Data_Form_Element_Select
{
    /**
     * Retrieve HTML Attributes.
     *
     * @return array
     */
    public function getHtmlAttributes()
    {
        return array('title', 'class', 'style', 'onclick', 'onchange', 'disabled', 'readonly', 'tabindex', 'size');
    }

    /**
     * Retrieve element html.
     * Add "Mandatory Attributes" warning.
     *
     * @return string
     */
    public function getElementHtml()
    {
        $html = parent::getElementHtml();
        if ($this->getRequiredAttrExists()) {
            $html .= '<div class="required" style="margin-top:5px;">'
                . '<img src="' . Mage::getDesign()->getSkinUrl('images/error_msg_icon.gif')
                . '" border="0" align="absmiddle" style="margin-right:5px;" />'
                . Mage::helper('xcom_mapping')->__('Mandatory Attributes')
                . '</div>' ."\n";
        }
        return $html;
    }
}
