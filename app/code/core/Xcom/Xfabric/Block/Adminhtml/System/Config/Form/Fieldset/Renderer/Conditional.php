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
 * Either render or return empty string depending on <conditions> node of the current fieldset
 *
 * @category    Xcom
 * @package     Xcom_Xfabric
 */
class Xcom_Xfabric_Block_Adminhtml_System_Config_Form_Fieldset_Renderer_Conditional
    extends Mage_Adminhtml_Block_System_Config_Form_Fieldset
{
    /**
     * @var array Group container to tell different fieldsets from each other
     */
    protected $_groups;

    /**
     * @var string Current group name to allow getGroup() with no parameters
     */
    protected $_currentGroupName;

    /**
     * Pushes group into array. For custom frontend_model Mage_Adminhtml_Block_System_Config_Form::initForm() returns
     * singleton, if we don't overload this method - group is going to be overwritten and we'll get same <conditions>
     * node for all fieldsets those use this renderer.
     *
     * @param Mage_Core_Model_Config_Element $group
     * @return Xcom_Xfabric_Block_Adminhtml_System_Config_Form_Fieldset_Renderer_Conditional
     */
    public function setGroup(Mage_Core_Model_Config_Element $group)
    {
        $this->_groups[(string)$group->label] = $group;
        return $this;
    }

    /**
     * Returns group object associated with current fieldset
     *
     * @return Mage_Core_Model_Abstract
     */
    public function getGroup()
    {
        return isset($this->_groups[$this->_currentGroupName])
            ? $this->_groups[$this->_currentGroupName]
            : Mage::getModel('core/config_element');
    }

    /**
     * If all conditions described in <conditions> section of the group description are TRUE - render as it was
     * specified as <frontend_type>text</frontend_type>. Otherwise don't render anything.
     *
     * @param Varien_Data_Form_Element_Abstract $fieldset
     * @return string
     */
    public function render(Varien_Data_Form_Element_Abstract $fieldset)
    {
        $this->_currentGroupName = $fieldset->getLegend();
        $group = $this->getGroup();
        $doRender = !empty($group) && isset($group->conditions);

        if ($doRender) {
            foreach ($group->conditions as $conditionLabels) {
                /* @var $conditionLabels Mage_Core_Model_Config_Element */
                $xpath = '';
                $shouldBeEmpty = null;
                $value = '';

                foreach ($conditionLabels as $condition) {
                    foreach ($condition as $conditionElement) {
                        /* @var $conditionElement Mage_Core_Model_Config_Element */
                        switch ($conditionElement->getName()) {
                            case 'xpath':
                                $xpath = (string)$conditionElement;
                                break;
                            case 'empty':
                                $shouldBeEmpty = true;
                                break;
                            case 'non_empty':
                                $shouldBeEmpty = false;
                                break;
                            case 'value':
                                $value = (string)$conditionElement;
                                break;
                        }
                    }

                    $node = Mage::app()->getConfig()->getNode($xpath);

                    if ($shouldBeEmpty !== null) {
                        $doRender = $shouldBeEmpty ? empty($node) : !empty($node);
                    } else {
                        $doRender = (string)$node == $value;
                    }

                    if (!$doRender) {
                        break;
                    }
                }
            }
        }

        return $doRender ? parent::render($fieldset) : '';
    }
}
