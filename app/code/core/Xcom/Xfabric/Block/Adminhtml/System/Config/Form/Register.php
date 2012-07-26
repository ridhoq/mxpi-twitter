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
 * Merchant registration form
 *
 * @method string                                                   getLabel()
 * @method Xcom_Xfabric_Block_Adminhtml_System_Config_Form_Register setLabel()
 *
 * @category    Xcom
 * @package     Xcom_Xfabric
 */
class Xcom_Xfabric_Block_Adminhtml_System_Config_Form_Register extends Mage_Adminhtml_Block_Abstract
{
    /**
     * Returns HTML code of "Register" button
     *
     * @return string
     */
    protected function _toHtml()
    {
        /* @var $block Xcom_Xfabric_Block_Adminhtml_System_Config_Form_Fieldset_Element_Button */
        $block = $this->getLayout()->createBlock('xcom_xfabric/adminhtml_system_config_form_fieldset_element_button');
        /* @var $authorizationModel Xcom_Xfabric_Model_Authorization */
        $authorizationModel = Mage::getModel('xcom_xfabric/authorization');
        $onboardingUri = $authorizationModel->getOnboardingUri();
        $fabricConfigInfo = Mage::helper('core')->jsonEncode($authorizationModel->getFabricConfigInfo());
        // Replace " to make JS strings parsed correctly inside the onclick attribute
        $fabricConfigInfo = urlencode($fabricConfigInfo);

        $authorizationModel = Mage::getModel('xcom_xfabric/authorization');
        $onboardingUri = $authorizationModel->getOnboardingUri();
        $sslEnabled = $authorizationModel->pingConnection($authorizationModel->getEndpointUrl());
        if ($sslEnabled) {
            $block->setOnclick("xcom.onboarding.register('{$onboardingUri}', '{$fabricConfigInfo}')")
                ->setLabel($this->getLabel() ? $this->getLabel() : $this->__('Register'));
        } else {
            Mage::getSingleton('adminhtml/session')->addError(
                Mage::helper('xcom_xfabric')
                    ->__('SSL is not supported by your web server. Please verify endpoint url: ')
                . $authorizationModel->getEndpointUrl());

            $block->setOnclick("window.location='". Mage::helper('adminhtml')->getUrl('*/*/*', array('section' => 'xcom_fabric')) ."'")
                ->setLabel($this->getLabel() ? $this->getLabel() : $this->__('Register'));
        }
        return $block->toHtml();
    }
}
