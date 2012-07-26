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
 * @package     Xcom_Chronicle
 * @copyright   Copyright (c) 2010 Magento Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

class Xcom_Chronicle_Model_Message_Webstore_Provider_Get_Inbound extends Xcom_Xfabric_Model_Message_Response
{
    /**
     * Initialization of class
     */
    protected function _construct()
    {
        $this->_topic               = 'com.x.webstore.v1/WebStoreMetadataProvision/GetAllWebStoreProvider';
        $this->_schemaRecordName    = 'GetAllWebStoreProvider';
        $this->_schemaVersion       = "1.0.0";

        parent::_construct();
    }

    /**
     * Process data on message received
     * @return Xcom_Chronicle_Model_Message_Webstore_Provider_Get_Inbound
     */
    public function process()
    {
        parent::process();

        try {
            if ($this->_validateSchema()) {
                $providerName = Mage::getBaseUrl();
                $response = $this->_generateSuccessMessage($providerName);
                Mage::helper('xcom_xfabric')->send('com.x.webstore.v1/WebStoreMetadataProvision/GetAllWebStoreProviderSucceeded', $response);
            }
        }
        catch(Exception $ex) {
            //will just log the exception and just drop the request on the floor, because there is no
            //error message for this particular request
            Mage::logException($ex);
       }
        return $this;
    }

   /**
    * Generates a success message for topic: GetAllWebStoreProviderSucceeded
    *
    * @param $providerName
    * @param $destinationId
    * @return array
    */
    protected function _generateSuccessMessage($providerName)
    {
        return array(
            'providerName'      => $providerName,
            'destination_id'    => $this->getPublisherPseudonym(),
            'correlation_id'    => $this->getCorrelationId(),
        );
    }
}