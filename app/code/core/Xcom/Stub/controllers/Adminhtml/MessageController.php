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

class Xcom_Stub_Adminhtml_MessageController extends Mage_Adminhtml_Controller_Action
{
    /**
     * Array of actions which can be processed without secret key validation
     *
     * @var array
     */
    protected $_publicActions = array('index', 'new', 'edit');//TODO: remove this

    /**
     * Initialize general settings for message
     *
     * @return Xcom_Stub_Model_Message
     */
    protected function _initMessage()
    {
        $message = Mage::getModel('xcom_stub/message');
        $messageId = (int) $this->getRequest()->getParam('id');
        if ($messageId) {
            $message->load($messageId);
        }
        Mage::register('current_message', $message);
        return $message;
    }

    /**
     * Segments list
     *
     * @return void
     */
    public function indexAction()
    {
        $this->_title($this->__('X.commerce Fabric Stub Messages'));
        $this->loadLayout();
        $this->_setActiveMenu('system/xfabric');
        $this->_addContent($this->getLayout()->createBlock('xcom_stub/adminhtml_message'));
        $this->renderLayout();
    }

    public function newAction()
    {
        $this->_forward('edit');
    }

    public function editAction()
    {
        $id     = $this->getRequest()->getParam('id');
        $model  = $this->_initMessage();

        if ($model->getId() || $id == 0) {
            $data = Mage::getSingleton('adminhtml/session')->getFormData(true);
            if (!empty($data)) {
                $model->setData($data);
            }
            Mage::register('current_data', $model);

            $this->loadLayout();
            $this->_setActiveMenu('system/xfabric');
            $this->_addContent($this->getLayout()->createBlock('xcom_stub/adminhtml_message_edit'));
            $this->renderLayout();
        } else {
            $this->_getSession()->addError($this->__('Message does not exist'));
            $this->_redirect('*/*/');
        }
    }

    public function sendrequestAction()
    {
        $stub  = $this->_initMessage();
        $request = Mage::helper('xcom_xfabric')->getMessage($stub->getSenderTopicName());
        $request->setEncoding(Mage::getStoreConfig('xfabric/connection_settings/encoding'));

        // Prepare data
        $body = json_decode($stub->getSenderMessageBody(), true);
        $request->setMessageData($body);
        $request->setBody($body);

        // Prepare headers
        $srcHeaders = $stub->getSenderMessageHeader();
        $headers = !empty($srcHeaders) ? explode("\n", $srcHeaders) : array();
        $headers = array_merge($headers, Mage::helper('xcom_xfabric')->getAuthorizationHeader());
        foreach ($headers as $headerName => $headerValue) {
            $request->addHeader($headerName,$headerValue );
        }

        $request->getEncoder()->encode($request);

        $transport = Mage::getModel('xcom_stub/transport_stub')
            ->setMessage($request)
            ->send();

        $this->_getSession()->addSuccess($this->__('The message has been sent.'));
        $this->_redirect('*/*/');
    }

    public function sendresponseAction()
    {
        $stub  = $this->_initMessage();
        // Get empty response model
        $response = Mage::helper('xcom_xfabric')->getMessage($stub->getRecipientTopicName());

        /** @var $message Xcom_Xfabric_Model_Message_Request */
        $message  = Mage::getModel('xcom_xfabric/message_request')
            ->setSchemaRecordName($response->getSchemaRecordName())
            ->setTopic($response->getTopic())
            ->setEncoding();

        // Prepare data
        $message->setMessageData(json_decode($stub->getRecipientMessageBody(), true));

        $headers = $stub->getRecipientHeaderArray();
        foreach ($headers as $headerName => $headerValue) {
            $message->addHeader($headerName, $headerValue);
        }
        $message->addHeader("X-XC-SCHEMA-URI", $message->getSchemaUri());

        if ($schemaVersion = $message->getHeader(Xcom_Xfabric_Model_Message_Abstract::SCHEMA_VER_SRV_HDR)) {
            $message->setSchemaVersion($schemaVersion);
        }
        $message->encode();

        // Add Authorization ID, but first remove 'Magento Bearer Token'
        $message->addHeader("Authorization", Mage::helper('xcom_xfabric')->getResponseAuthorizationKey());
        $config = array(
            'url' => Mage::getBaseUrl() . '/xfabric/endpoint/',
            'port' => 80
        );

        Mage::dispatchEvent('response_message_received', array('message' => $message));
        try {
            Mage::getModel('xcom_xfabric/transport_xfabric', $config)
                ->setMessage($message)
                ->send();
        } catch (Exception $e) {
            $this->_getSession()->addError($e->getMessage());
            $this->_redirect('*/*/');
        }
        $this->_getSession()->addSuccess($this->__('The message has been sent.'));
        $this->_redirect('*/*/');
    }

    /**
     * Edit message action
     *
     * @return void
     */
    public function saveAction()
    {
        $data = $this->getRequest()->getPost();
        if ($data) {
            $message        = $this->_initMessage();
            $message->addData($data);

            try {
                $message->save();
                $this->_getSession()->addSuccess($this->__('The message has been saved.'));
            }
            catch (Exception $e) {
                Mage::logException($e);
                $this->_getSession()->addError($e->getMessage());
            }
        }
        else {
            $this->_getSession()->addError($this->__('Message has not been saved.'));
        }
        $this->_redirect('*/*/');
    }

    /**
     * Delete message action
     *
     * @return void
     */
    public function deleteAction()
    {
        $message        = $this->_initMessage();
        if ($message) {
            try {
                $message->delete();
                $this->_getSession()->addSuccess($this->__('The message has been removed.'));
            }
            catch (Exception $e) {
                Mage::logException($e);
                $this->_getSession()->addError($e->getMessage());
            }
        }
        else {
            $this->_getSession()->addError($this->__('Message can not be removed.'));
        }
        $this->_redirect('*/*/');
    }
}
