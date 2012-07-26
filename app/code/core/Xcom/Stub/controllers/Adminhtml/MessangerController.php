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

class Xcom_Stub_Adminhtml_MessangerController extends Mage_Adminhtml_Controller_Action
{
    /**
     * Array of actions which can be processed without secret key validation
     *
     * @var array
     */
    protected $_publicActions = array('index', 'edit', 'import');//TODO: remove this

    /**
     * Initialize general settings for message
     *
     * @return Xcom_Stub_Model_Message
     */
    protected function _initMessage()
    {
        $messageId = (int) $this->getRequest()->getParam('id');
        $message = Mage::getModel('xcom_stub/message');

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
        $this->_title($this->__('X.commerce Fabric Messanger'));
        $this->loadLayout();
        $this->_setActiveMenu('system/xfabric');
        $this->_addContent($this->getLayout()->createBlock('xcom_stub/adminhtml_messanger'));
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

    public function importAction()
    {
        $model = Mage::getModel('xcom_stub/updater');
        $model->loadStub();
        $this->_redirect('*/message/index');
    }

    public function exportAction()
    {
        $model = Mage::getModel('xcom_stub/updater');
        $model->saveStub();
        $this->_redirect('*/message/index');
    }

    public function testlistingsearchAction()
    {
        Mage::getModel('xcom_mmp/observer')
            ->sendListingSearchRequest(new Varien_Object());
    }

    public function testtaxonomymessagesAction()
    {
        $locales = Mage::helper('xcom_mapping')->getSupportedLocales();

        foreach ($locales as $locale) {
            Mage::helper('xcom_xfabric')->send('productTaxonomy/productType/get', $locale);
        }
    }

    public function decodecategoriesAction()
    {
        ini_set('memory_limit', '512M');
        $start = time();
        $time = $this->getRequest()->getParam('time');
        $categories = file_get_contents(Mage::getBaseDir('var') . '/log/cat_message' . $time . '.log');
        $message = Mage::helper('xcom_xfabric')->getMessage('/marketplace/category/searchSucceeded');
        $message->setBody($categories)
            ->addHeader("X-XC-SCHEMA-URI", 'https://ocl.xcommercecloud.com/marketplace/category/searchSucceeded/1.0.0')
            ->decode();
        var_dump('spent time: ' . (time() - $start));
        var_dump($message->getBody());
        die();
    }

    public function tansactionAction()
    {
        $name = 'transaction';
        $messageData = array();
        $options = array('message_data' => $messageData);

        Mage::getModel('xcom_choreography/transaction')
            ->getTransaction($name, $options)
            ->execute();
    }
}

