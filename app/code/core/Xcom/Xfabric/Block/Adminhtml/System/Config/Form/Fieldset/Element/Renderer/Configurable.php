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
 * Make element configurable through <config> node in element description section
 *
 * @category    Xcom
 * @package     Xcom_Xfabric
 */
class Xcom_Xfabric_Block_Adminhtml_System_Config_Form_Fieldset_Element_Renderer_Configurable
    extends Xcom_Xfabric_Block_Adminhtml_System_Config_Form_Fieldset_Element_Renderer_Raw
{
    /**
     * Copy data level up from _data['field_config'] so it could be later used by button element
     *
     * @param Varien_Data_Form_Element_Abstract $element
     * @return string
     */
    protected function _getElementHtml(Varien_Data_Form_Element_Abstract $element)
    {
        /**
         * There's no way to unset the renderer from the element. We need to clone the current one and set additional
         * data and default renderer to keep standard look and feel.
         */

        /* @see Varien_Data_Form_Abstract::addField() */
        $elementClassName = 'Varien_Data_Form_Element_' . ucfirst(strtolower($element->getType()));
        /* @var $newElement Varien_Data_Form_Element_Abstract */
        $newElement = new $elementClassName($element->getData());
        $newElement->setId($element->getHtmlId());
        $newElement->setForm($this->getForm());
        /* @see Mage_Adminhtml_Block_System_Config_Form::_initObjects() */
        $newElement->setRenderer(Mage::getBlockSingleton('adminhtml/system_config_form_field'));

        if (isset($element->getFieldConfig()->config)) {
            // Push data into internal array from <config> section, so we can reach it later in element building model.
            $newElement->addData((array)$element->getFieldConfig()->config);
        }

        return $newElement->toHtml();
    }
}
