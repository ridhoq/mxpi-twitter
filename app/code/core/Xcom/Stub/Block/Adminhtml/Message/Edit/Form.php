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

class Xcom_Stub_Block_Adminhtml_Message_Edit_Form extends Mage_Adminhtml_Block_Widget_Form
{
    protected function _prepareForm()
    {
        $message = Mage::registry('current_message');
        $form = new Varien_Data_Form(array(
              'id' => 'edit_form',
              'action' => $this->getUrl('*/*/save', array('id' => $this->getRequest()->getParam('id'))),
              'method' => 'post'
            )
        );

        $generalFieldset = $form->addFieldset('general_fieldset', array('legend'=>$this->__('Information')));
        $generalFieldset->addField('description', 'text', array(
            'label'     => $this->__('Comment'),
            'class'     => 'required-entry',
            'required'  => true,
            'name'      => 'description',
        ));

        $requestFieldset = $form->addFieldset('sender_fieldset', array('legend'=>$this->__('Request')));
        $requestFieldset->addField('sender_topic_name', 'text', array(
            'label'     => $this->__('Topic'),
            'class'     => 'required-entry',
            'required'  => true,
            'name'      => 'sender_topic_name',
        ));
        $requestFieldset->addField('sender_message_name', 'text', array(
            'label'     => $this->__('Message Name'),
            'comment'   => 'test',
            'class'     => 'required-entry',
            'required'  => true,
            'name'      => 'sender_message_name',
        ));
        $requestFieldset->addField('sender_message_header', 'editor', array(
            'name'      => 'sender_message_header',
            'label'     => $this->__('Headers'),
            'title'     => $this->__('Headers'),
            'comment'   => 'test',
            'style'     => 'width:500px; height:50px;',
            'wysiwyg'   => false,
            'required'  => false,
        ));
        $requestFieldset->addField('sender_message_body', 'editor', array(
            'name'      => 'sender_message_body',
            'label'     => $this->__('Body'),
            'title'     => $this->__('Body'),
            'style'     => 'width:500px; height:200px;',
            'wysiwyg'   => false,
            'required'  => false,
        ));

        $responseFieldset = $form->addFieldset('recipient_fieldset', array('legend'=>$this->__('Response')));
        $responseFieldset->addField('recipient_topic_name', 'text', array(
            'label'     => $this->__('Topic'),
            'class'     => 'required-entry',
            'required'  => true,
            'name'      => 'recipient_topic_name',
        ));
        $responseFieldset->addField('recipient_message_header', 'editor', array(
            'name'      => 'recipient_message_header',
            'label'     => $this->__('Headers'),
            'title'     => $this->__('Headers'),
            'style'     => 'width:500px; height:50px;',
            'wysiwyg'   => false,
            'required'  => false,
        ));
        $responseFieldset->addField('recipient_message_name', 'text', array(
            'label'     => $this->__('Recipient Message Name'),
            'class'     => 'required-entry',
            'required'  => true,
            'name'      => 'recipient_message_name',
        ));
        $responseFieldset->addField('recipient_message_body', 'editor', array(
            'name'      => 'recipient_message_body',
            'label'     => $this->__('Body'),
            'title'     => $this->__('Body'),
            'style'     => 'width:500px; height:300px;',
            'wysiwyg'   => false,
            'required'  => true,
        ));

        $form->setUseContainer(true);
        $form->setValues($message->getData());
        $this->setForm($form);
        return parent::_prepareForm();
    }
}
