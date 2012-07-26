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
 * Adminhtml notification block
 *
 * @category    Xcom
 * @package     Xcom_Xfabric
 */

class Xcom_Xfabric_Block_Adminhtml_Notifications extends Mage_Adminhtml_Block_Template
{
    /**
     * Get x management url
     *
     * @return string
     */
    public function getManageUrl()
    {
        return $this->getUrl('adminhtml/system_config/edit', array('section' => 'xcom_fabric'));
    }

    /**
     * Check if all necessary xFabric configurations are filled
     *
     * @return bool
     */
    public function isRequiredSettingsFilled()
    {
        return (bool)Mage::helper('xcom_xfabric')->getResponseAuthorizationKey();
    }
}
