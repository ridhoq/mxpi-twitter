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
 * @package     Xcom_Initializer
 * @copyright   Copyright (c) 2010 Magento Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class Xcom_Initializer_Block_Set extends Mage_Adminhtml_Block_Widget_Container
{
    /**
     * Prepare title.
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_headerText = $this->__('Initializer Status');
        $this->_controller = 'adminhtml_initializer';
        parent::_construct();
    }

    /**
     * Prepare button Clear Xcom Data
     *
     * @return Mage_Core_Block_Abstract
     */
    protected function _prepareLayout()
    {
        $confirmMessage = $this->__('This will reinstall Xcom extension. All data will be deleted. Are you sure?');
        $this->addButton('clear_extension_button', array(
            'label'     => $this->__('Clear Xcom Data'),
            'onclick'   => "confirmSetLocation('{$confirmMessage}', '{$this->getUrl('*/*/cleanExtensionData')}')",
            'class'     => 'save',
        ));
        return parent::_prepareLayout();
    }

}
