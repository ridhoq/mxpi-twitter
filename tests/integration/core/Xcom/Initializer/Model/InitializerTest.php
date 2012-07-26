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
class Xcom_Initializer_Model_InitializerTest extends Xcom_Integration_TestCase
{
    protected $_issuer = 'integration_test';

    public function testInitializer()
    {
        // Saving initializer job
        Mage::getModel('xcom_initializer/job')
            ->setTopic('productTaxonomy/productType/get')
            ->setStatus(Xcom_Initializer_Model_Job::STATUS_PENDING)
            ->setMessageParams(json_encode(array('country' => 'US', 'language'=> 'en')))
            ->setIssuer($this->_issuer)
            ->save();

        $this->addRollbackQuery(sprintf('delete from xcom_initializer_job where issuer="%s"', $this->_issuer));

        // Checking whether it was actually saved
        $select = $this->getConnection()
            ->select()
            ->from('xcom_initializer_job', array('*'))
            ->where('issuer = ?', $this->_issuer);
        $result = $this->getConnection()->fetchOne($select);
        $this->assertNotEmpty($result);

        // Running method which sends data from saved job
        /* @var $observer Xcom_Initializer_Model_Observer */
        $observer = Mage::getModel('xcom_initializer/observer');
        // @todo remove and replace with checking testsubscriber for arrived message
        Mage::app()->getStore()->setConfig('xfabric/connection_settings/adapter', 'xcom_stub/transport_stub');
        Mage::app()->getStore()->setConfig('xfabric/connection_settings/encoding', 'json');
        $observer->runCollectProcess(new Varien_Event_Observer());

        // Check whether saved job has been sent
        $select = $this->getConnection()
            ->select()
            ->from('xcom_initializer_job', array('status'))
            ->where('issuer = ?', $this->_issuer);
        $result = $this->getConnection()->fetchOne($select);

        $constraint = new PHPUnit_Framework_Constraint_Or();

        $constraint->setConstraints(
            array(Xcom_Initializer_Model_Job::STATUS_SENT, Xcom_Initializer_Model_Job::STATUS_RECEIVED)
        );

        $this->assertThat($result, $constraint);

        // Make sure stop actions work while not all messages has been processed
        $this->_ensureDenied($this->makeHttpRequest('/initializertest/index/some'));
        $this->_ensureAllowed($this->makeHttpRequest('/initializertest/index/index'));

        // Imitate that we've processed received results
        $this->getConnection()->update(
            'xcom_initializer_job',
            array('status' => Xcom_Initializer_Model_Job::STATUS_SAVED),
            sprintf('issuer = "%s"', $this->_issuer)
        );

        // Make sure stop actions doesn't work anymore
        $this->_ensureAllowed($this->makeHttpRequest('/initializertest/index/some'));
    }

    protected function _ensureDenied(Mage_HTTP_Client_Curl $curl)
    {
        $this->assertEquals(200, $curl->getStatus());
        $this->assertContains('admin/index/forgotpassword', $curl->getBody());
    }

    private function _ensureAllowed(Mage_HTTP_Client_Curl $curl)
    {
        $this->assertEquals(200, $curl->getStatus());
        $this->assertEmpty($curl->getBody());
    }
}
