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
class Xcom_Initializer_Model_Observer
{

    protected $_responseTopic = '';

    /**
     * Forward to xcomDenied action
     * if no full Xcom data and user try to open one of xcom page except Xcom_Xfabric and Xcom_Stub
     *
     * @param Varien_Event_Observer $observer
     * @return Xcom_Initializer_Model_Observer
     */
    public function controllerPreDispatch($observer)
    {
        /* @var $controller Mage_Core_Controller_Varien_Action */
        $controller = $observer->getData('controller_action');

        if ($controller->getRequest()->isDispatched()
            && !Mage::getSingleton('xcom_initializer/initializer')->isAllowed($controller->getRequest())
        ) {
            Mage::getSingleton('adminhtml/session')
                ->setIsUrlNotice($controller->getFlag('', Mage_Adminhtml_Controller_Action::FLAG_IS_URLS_CHECKED));
            $request = $controller->getRequest();
            $request->initForward()
                ->setControllerName('initializer')
                ->setModuleName('admin')
                ->setActionName('xcomDenied')
                ->setDispatched(false);
        }

        return $this;
    }

    /**
     * Sends requests to xFabric to collect data
     *
     * @param $observer
     * @return Xcom_Initializer_Model_Observer
     */
    public function runCollectProcess(Varien_Object $observer)
    {
        try {
            $authKey = (bool)Mage::helper('xcom_xfabric')->getResponseAuthorizationKey();
            if (!$authKey) {
                Mage::log("Configuration is not ready. Exit.", null, 'Initializer.log');
                return $this;
            }

            $jobCollection = Mage::getResourceModel('xcom_initializer/job_collection')
                ->addFieldToFilter('status', Xcom_Initializer_Model_Job::STATUS_PENDING)
                ->addForUpdate();

            Mage::log('Found ' . count($jobCollection) . ' topics', null, 'Initializer.log');

            foreach ($jobCollection as $job) {
                $job->process();
                Mage::log("Topic " . $job->getTopic() . ' ' . $job->getMessageParams() .
                    ' was processed.', null, 'Initializer.log');
            }

            Mage::getResourceModel('xcom_initializer/job')
                ->reviveExpiredJobs();

        } catch (Exception $e) {
            Mage::logException($e);
        }
        return $this;
    }

    /**
     * Update job status on response_message_received event
     *
     * @param Varien_Event_Observer $observer
     * @return mixed
     */
    public function updateJobStatus(Varien_Event_Observer $observer)
    {
        $correlationId = $observer->getEvent()->getMessage()->getCorrelationId();
        if (empty($correlationId)) {
            return;
        }
        Mage::getResourceModel('xcom_initializer/job')
            ->updateStatusByCorrelationId(Xcom_Initializer_Model_Job::STATUS_RECEIVED,
            $correlationId, array('status <= ?' => Xcom_Initializer_Model_Job::STATUS_SENT)
        );
    }
}
