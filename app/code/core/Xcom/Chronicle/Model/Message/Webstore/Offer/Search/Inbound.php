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

class Xcom_Chronicle_Model_Message_Webstore_Offer_Search_Inbound extends Xcom_Xfabric_Model_Message_Response
{
    /**
     * Initialization of class
     */
    protected function _construct()
    {
        $this->_topic               = 'com.x.webstore.v1/WebStoreOfferSearch/SearchWebStoreOffer';
        $this->_schemaRecordName    = 'SearchWebStoreOffer';
        $this->_schemaVersion       = "1.0.0";

        parent::_construct();
    }

    /**
     * Process data on message received
     * @return Xcom_Chronicle_Model_Message_Customer_Search_Inbound
     */
    public function process()
    {
        parent::process();

        $data = $this->getBody();
        if (!isset($data)) {
            $data = array();
        }
        if(!isset($data['modifiedSince'])){
            $data['modifiedSince'] = null;
        }
        if(!isset($data['itemsRequested'])){
            $data['itemsRequested'] = null;
        }
        if(!isset($data['startItemIndex'])){
            $data['startItemIndex'] = null;
        }
        if(!isset($data['webStoreId'])){
            $data['webStoreId'] = null;
        }
        try {
            if ($this->_validateSchema()) {
                    $searchResults = $this->_processSearchQuery($data);
                    $response = array(
                        'webStoreOffers'  => $searchResults['results'],
                        'totalItemsFound' => $searchResults['totalItemsFound'],
                        'request'         => array(
                            'webStoreId'     => $data['webStoreId'],
                            'modifiedSince'  => $data['modifiedSince'],
                            'itemsRequested' => $data['itemsRequested'],
                            'startItemIndex' => $data['startItemIndex'],
                        ),
                        'destination_id'  => $this->getPublisherPseudonym(),
                        'correlation_id'  => $this->getCorrelationId(),
                    );

                Mage::helper('xcom_xfabric')->send('com.x.webstore.v1/WebStoreOfferSearch/SearchWebStoreOfferSucceeded', $response);
            }
        }
        catch(Xcom_Xfabric_Exception $ex) {
            Mage::logException($ex);
            $errorResponse = $this->_generate_failure_data($data['modifiedSince'], $data['itemsRequested'], $data['startItemIndex'], $data['webStoreId'], $ex, $ex->getCode());
            Mage::helper('xcom_xfabric')->send('com.x.webstore.v1/WebStoreOfferSearch/SearchWebStoreOfferFailed', $errorResponse);
        }
        catch(Exception $ex){
            if(!is_null($ex)){
                $message = $ex->getMessage();
            }
            Mage::logException($ex);
            $errorResponse = $this->_generate_failure_data($data['modifiedSince'], $data['itemsRequested'], $data['startItemIndex'], $data['webStoreId'], $ex, $ex->getCode());
            Mage::helper('xcom_xfabric')->send('com.x.webstore.v1/WebStoreOfferSearch/SearchWebStoreOfferFailed', $errorResponse);
        }
        return $this;
    }

    /**
     * Save/update return policy data to DB
     *
     * @param array $data
     * @return array
     */
    protected function _processSearchQuery(&$data)
    {
        $products = Mage::getResourceModel('catalog/product_collection');
        $products->setOrder('created_at', 'asc');

        $limit = null;
        $offset = null;
        $modifiedSince = null;
        $webStoreId = null;

        if(isset($data['itemsRequested']) && $data['itemsRequested'] > 0) {
            $limit = (int)$data['itemsRequested'];
        }

        if (isset($data['startItemIndex']) && $data['startItemIndex'] > 0) {
            $offset = (int)$data['startItemIndex'];
        }

        if (isset($data['modifiedSince']) && $data['modifiedSince'] != '') {
            $modifiedSince = $data['modifiedSince'];
            $products->addFieldToFilter('updated_at', array('gt' => $modifiedSince));
        }

        if(isset($data['webStoreId'])){
            $webStoreId = $data['webStoreId'];
        }

        $products->load();

        $total = 0;
        $added = 0;
        $skippedCount = 0;
        $results = array();

        foreach ($products as $product) {
            $p = Mage::getModel('catalog/product')->load((int)$product->getId());

            foreach ($p->getStoreIds() as $sid) {
                if (isset($webStoreId) && $webStoreId != $sid) {
                    continue;
                }

                $total++;
                if (isset($offset) && $skippedCount < $offset) {
                    $skippedCount++;
                    continue;
                } else{
                    if (!isset($limit) || $added < $limit) {
                        $added++;
                        $results[] = Mage::getModel('xcom_chronicle/message_webstore_offer',
                                                    array('product'  => $product, 'store_id' => $sid))->toArray();
                    }
                }
            }
        }

        return array(
            'results' => $results,
            'totalItemsFound' => $total
        );
    }

    protected function _generate_failure_data($modifiedSince, $itemsRequested, $startItemIndex, $webStoreId, $ex, $code=null)
    {
        $errorResponse = array(
            'request' => array(
                'webStoreId'     => $webStoreId,
                'modifiedSince'  => $modifiedSince,
                'itemsRequested' => $itemsRequested,
                'startItemIndex' => $startItemIndex
            ),
            'errors' => array(
                array(
                    'code' => empty($code) ? '-1': ''.$code,
                    'message' => $ex->getMessage(),
                    'parameters' => null
                )
            ),
            'destination_id' => $this->getPublisherPseudonym(),
            'correlation_id' => $this->getCorrelationId(),
        );
        return $errorResponse;
    }
}
