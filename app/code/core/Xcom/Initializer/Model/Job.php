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
 * @package     Xcom_Ititializer
 * @copyright   Copyright (c) 2010 Magento Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/**
 * @method Xcom_Initializer_Model_Job setIssuer()
 * @method string                     getIssuer()
 */
class Xcom_Initializer_Model_Job extends Mage_Core_Model_Abstract
{
    /**
     * Job table statuses
     */
    const STATUS_PENDING   = 1;
    const STATUS_SENT      = 2;
    const STATUS_RECEIVED  = 3;
    const STATUS_SAVED     = 4;

    /**
     * Time in minutes when we update status of the job back to STATUS_PENDING
     */
    const SENT_EXPIRATION_MINUTES = 15;
    const RECEIVED_EXPIRATION_MINUTES = 120;

    /**
     * Initialize class
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init('xcom_initializer/job');
    }

    /**
     * Process single job
     *
     * @return Xcom_Initializer_Model_Job
     * @throws Mage_Core_Exception
     */
    public function process()
    {
        /** @var $message Xcom_Xfabric_Model_Message_Request */
        $message =  Mage::helper('xcom_xfabric')->getMessage($this->getTopic());
        if (!$message) {
            throw Mage::exception('Xcom_Xfabric',
                Mage::helper('xcom_xfabric')->__("Message for topic %s should be created", $this->getTopic()));
        }

        $data = json_decode($this->getMessageParams(), true);

        $message->process(new Varien_Object($data));

        // we set the flag to make requests slower since it adds a timeout to wait for responce
        $message->setIsWaitResponse();

        // Save correlationId to get stub scenario working
        Mage::getModel('xcom_initializer/job')
            ->load($this->getJobId())
            ->setCorrelationId($message->getCorrelationId())
            ->save();

        try {
            Mage::helper('xcom_xfabric')->getTransport()
                ->setMessage($message)
                ->send();
        } catch (Exception $ex) {
            Mage::logException($ex);
        }

        /*
        Update only jobs in pending status since we could get responses from the stub
        and some jobs are already set to saved status at the moment
        */
        Mage::getResourceModel('xcom_initializer/job')
            ->updateStatusByCorrelationId(Xcom_Initializer_Model_Job::STATUS_SENT,
            $message->getCorrelationId(), array('status = ?' => Xcom_Initializer_Model_Job::STATUS_PENDING)
        );
        return $this;
    }
}
