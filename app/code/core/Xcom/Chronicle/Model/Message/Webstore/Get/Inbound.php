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

class Xcom_Chronicle_Model_Message_Webstore_Get_Inbound extends Xcom_Xfabric_Model_Message_Response
{
    /**
     * Initialization of class
     */
    protected function _construct()
    {
        $this->_topic               = 'com.x.webstore.v1/WebStoreMetadataProvision/GetAllWebStore';
        $this->_schemaRecordName    = 'GetAllWebStore';
        $this->_schemaVersion       = "1.0.0";

        parent::_construct();
    }

    /**
     * Process data on message received
     * @return Xcom_Chronicle_Model_Message_Webstore_Get_Inbound
     */
    public function process()
    {
        parent::process();

        $providerName = Mage::getBaseUrl();
        try {
            if ($this->_validateSchema()) {
                $resultSet = $this->_processGetStores();

                if (!empty($resultSet['results'])) {
                    $response = $this->_generateSuccessMessage($providerName, $resultSet['results']);
                    Mage::helper('xcom_xfabric')->send('com.x.webstore.v1/WebStoreMetadataProvision/GetAllWebStoreSucceeded', $response);
                }
                else {
                    $response = $this->_generateFailureMessage($providerName, $resultSet['error']);
                    Mage::helper('xcom_xfabric')->send('com.x.webstore.v1/WebStoreMetadataProvision/GetAllWebStoreFailed', $response);
                }
            }
        }
        catch(Exception $ex) {
            Mage::logException($ex);
            $response = $this->_generateFailureMessage($providerName, $ex->getMessage());
            Mage::helper('xcom_xfabric')->send('com.x.webstore.v1/WebStoreMetadataProvision/GetAllWebStoreFailed', $response);
        }
        return $this;
    }

    /**
     * Gets all stores associated with this magento instance
     *
     * @return array
     */
    protected function _processGetStores()
    {
        $results = null;
        $error = null;
        $allStores = Mage::app()->getStores();
        if (!empty($allStores)) {
            $results = array();
            foreach ($allStores as $store) {
                if ($store->getIsActive()) {
                    $results[] = $this->_buildStore($store);
                }
            }
            if (empty($results)) {
                // all stores are inactive
                $error = 'no sites available';
            }
        }
        else {
            $error = 'no sites available';
        }

        $resultSet = array(
            'results'  => $results,
            'error'    => $error,
        );

        return $resultSet;
    }

    /**
     *  Builds a record for a given store
     *
     * @param Mage_Core_Model_Store $store
     * @return array
     */
    protected function _buildStore(Mage_Core_Model_Store $store)
    {
        $locale = preg_split('/_/', $store->getConfig('general/locale/code'));
        return array(
            'webStoreName'      => $store->getName(),
            'webStoreId'        => $store->getStoreId(),
            'language'          => $locale[1],
            'currencyCode'      => $store->getCurrentCurrencyCode(),
            'url'               => $store->getUrl()
        );
    }

    /**
     * Generates a success message for topic: GetAllWebStoreSucceeded
     *
     * @param $providerName
     * @param $destinationId
     * @param array $stores
     * @return array
     */
    protected function _generateSuccessMessage($providerName, array $stores)
    {
        return array(
            'providerName'      => $providerName,
            'stores'            => $stores,
            'destination_id'    => $this->getPublisherPseudonym(),
            'correlation_id'    => $this->getCorrelationId(),
        );
    }

    /**
     * Generates a failure message for topic: GetAllWebStoreFailed
     *
     * @param $providerName
     * @param $message
     * @return array
     */
    protected function _generateFailureMessage($providerName, $message)
    {
        return array(
            'providerName'  => $providerName,
            'errors'        => array(
                array(
                    'code'          => '-1',
                    'message'       => $message,
                    'parameters'    => null
                )
            ),
            'destination_id' => $this->getPublisherPseudonym(),
            'correlation_id' => $this->getCorrelationId(),
        );
    }
}
