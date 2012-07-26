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

class Xcom_Initializer_Adminhtml_InitializerController extends Mage_Adminhtml_Controller_Action
{

    /**
     * Denied action.
     * User is not able to work with xcom data until it will be full loaded.
     */
    public function xcomDeniedAction()
    {
        $this->loadLayout(array('default', 'xcom_denied'));
        $this->renderLayout();
    }

    public function indexAction()
    {
        $this->loadLayout();
        $this->_title($this->__('Initializer'))->_title($this->__('Status'));
        $this->_setActiveMenu('system/xcom_xfabric/initializer');
        $this->renderLayout();
    }

    /**
     * Used to delete all XCOM installation data
     *
     */
    public function cleanExtensionDataAction()
    {
        try {
            Mage::getResourceModel('xcom_initializer/extension')
                ->cleanExtensionData();
            $this->_getSession()->addSuccess($this->__('Xcom extension was reinstalled. All data was successfully cleared.'));
        } catch (Mage_Core_Exception $e) {
            $this->_getSession()->addError($e->getMessage());
        }
        $this->_redirect('*/initializer/index');
    }
}
