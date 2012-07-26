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
 * @package     Xcom_Stub
 * @copyright   Copyright (c) 2010 Magento Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

class Xcom_Stub_Block_Adminhtml_Message_Edit extends Mage_Adminhtml_Block_Widget_Form_Container
{
    public function __construct()
    {
        parent::__construct();

        $this->_objectId = 'id';
        $this->_blockGroup = 'xcom_stub';
        $this->_controller = 'adminhtml_message';

        if( Mage::registry('current_message') && Mage::registry('current_message')->getId() ) {
            $requestButton = array(
                'label' => 'Send Request',
                'onclick'=> "setLocation('"
                    . $this->getUrl('*/*/sendrequest', array('id' => Mage::registry('current_message')->getId()))
                    . "')"
            );
            $this->addButton('send_request', $requestButton);

            $responseButton = array(
                'label' => 'Send Response',
                'onclick'=> "setLocation('"
                    . $this->getUrl('*/*/sendresponse', array('id' => Mage::registry('current_message')->getId()))
                    . "')"
            );
            $this->addButton('send_response', $responseButton);
        }
    }

    public function getHeaderText()
    {
        if( Mage::registry('current_message') && Mage::registry('current_message')->getId() ) {
            return $this->__('Edit Message');
        } else {
            return $this->__('Add Message');
        }
    }
}
