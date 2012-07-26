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

class Xcom_Mapping_Block_Adminhtml_Attribute_Value_Custom extends Mage_Adminhtml_Block_Widget_Container
{
    /**
     * Constructor.
     */
    public function __construct()
    {
        parent::__construct();

        $this->_headerText = $this->__('Attribute Value Mapping');
    }

    /**
     * Update layout. Add buttons.
     *
     * @return Mage_Core_Block_Abstract
     */
    protected function _prepareLayout()
    {
        $this->addButton('back_button', array(
            'label'     => $this->__('Back'),
            'onclick'   => 'setLocation(\'' . $this->getBackButtonUrl() .'\')',
            'class'     => 'back',
        ));

        $this->addButton('reset_button', array(
            'label'     => $this->__('Reset'),
            'onclick'   => 'setLocation(\'' . $this->getUrl('*/*/*', array('_current' => true)) . '\')',
            'class'     => 'reset',
        ));

        $this->addButton('save_button', array(
            'label'     => $this->__('Save'),
            'onclick'   => 'editForm.submit();',
            'class'     => 'save',
        ));

        $this->addButton('save_and_continue_button', array(
            'label'     => $this->__('Save and Continue Edit'),
            'onclick'   => 'editForm.submit(\''.$this->getSaveAndContinueUrl().'\');',
            'class'     => 'save',
        ));

        return parent::_prepareLayout();
    }

    /**
     * Retrieve Save Url.
     *
     * @return string
     */
    public function getSaveUrl()
    {
        return $this->getUrl('*/*/saveCustomvalue');
    }

    /**
     * Retrieve Save and Continue Url.
     *
     * @return string
     */
    public function getSaveAndContinueUrl()
    {
        return $this->getUrl('*/*/saveCustomvalue', array(
            '_current'   => true,
            'back'       => 'edit',
        ));
    }

    /**
     * Retrieve back button Url.
     *
     * @return string
     */
    public function getBackButtonUrl()
    {
        return $this->getUrl('*/mapping_attribute/index', array(
            '_current' => true,
        ));
    }

    /**
     * Retrieve parameter value by parameter name.
     *
     * @param string $name
     * @return mixed|null
     */
    public function getParam($name = '')
    {
        if (empty($name)) {
            return null;
        }
        if ($result = $this->getRequest()->getParam($name)) {
            return $result;
        }
        return null;
    }
}
