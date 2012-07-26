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
 * Render nice orange button
 *
 * @category    Xcom
 * @package     Xcom_Xfabric
 */
class Xcom_Xfabric_Block_Adminhtml_System_Config_Form_Fieldset_Element_Renderer_Button
    extends Mage_Adminhtml_Block_System_Config_Form_Field
{
    protected $_template = 'xcom/xfabric/system/config/form/fieldset/element/button.phtml';

    /**
     * Unset scope label and pass further to parent render()
     *
     * @param Varien_Data_Form_Element_Abstract $element
     * @return string
     */
    public function render(Varien_Data_Form_Element_Abstract $element)
    {
        // Unset the scope label near the button
        $element->unsScope()->unsCanUseWebsiteValue()->unsCanUseDefaultValue();
        return parent::render($element);
    }

    /**
     * Pass data from config.xml describe section to _data array
     *
     * @param Varien_Data_Form_Element_Abstract $element
     * @return string
     */
    protected function _getElementHtml(Varien_Data_Form_Element_Abstract $element)
    {
        $block = $this->getLayout()->createBlock('xcom_xfabric/adminhtml_system_config_form_fieldset_element_button');

        if (isset($element->getFieldConfig()->config)) {
            // Pass all nodes from <config> to block configuration
            foreach ($element->getFieldConfig()->config->children() as $key => $value) {
                if ($key == 'redirect') {
                    $key = 'onclick';
                    $value = 'window.location.href=\'' . $this->getUrl('*/' . $value) . '\'';
                }

                $block->setData($key, $value);
            }
        }

        return $block->toHtml();
    }
}
