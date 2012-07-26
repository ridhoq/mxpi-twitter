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

class Xcom_Mapping_Block_Adminhtml_Attribute_Set_Edit extends Mage_Adminhtml_Block_Widget_Container
{
    /**
     * Construct.
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_controller = 'adminhtml_map_attribute';
        parent::_construct();
    }

    /**
     * Prepare layout.
     * Add Back and Reset buttons.
     * Add Tab.
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

        return parent::_prepareLayout();
    }

    /**
     * Retrieve back button url.
     *
     * @return string
     */
    public function getBackButtonUrl()
    {
        return $this->getUrl('*/*/index', array(
            'target_system' => $this->getRequest()->getParam('target_system')
        ));
    }

    /**
     * Returns header text.
     *
     * @return string
     */
    public function getHeaderText()
    {
        return $this->getRequest()->getParam('mapping_product_type_id')
                ? $this->__('Edit Attribute Set Mapping')
                : $this->__('New Attribute Set Mapping');
    }

    /**
     * Returns target context.
     *
     * @return string
     */
    public function getBreadcrumb()
    {
        return $this->__('Systems: Magento ~ X.Commerce');
    }
}
