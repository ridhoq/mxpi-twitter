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

class Xcom_Chronicle_Model_Message_Customer_Search_Inbound extends Xcom_Xfabric_Model_Message_Response
{
    /**
     * Initialization of class
     */
    protected function _construct()
    {
        $this->_topic               = 'com.x.customer.v1/CustomerSearch/SearchCustomer';
        $this->_schemaRecordName    = 'SearchCustomer';
        $this->_schemaVersion = "1.1.1";

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
        try {
            if ($this->_validateSchema()) {
                    $searchResults = $this->_processSearchQuery($data);
                    $response = array(
                        'customers' => $searchResults['results'],
                        'totalItemsFound' => $searchResults['totalItemsFound'],
                        'request' => array(
                            'modifiedSince' => $data['modifiedSince'],
                            'itemsRequested' => $data['itemsRequested'],
                            'startItemIndex' => $data['startItemIndex'],
                        ),
                        'destination_id' => $this->getPublisherPseudonym(),
                        'correlation_id' => $this->getCorrelationId(),
                    );

                Mage::helper('xcom_xfabric')->send('com.x.customer.v1/CustomerSearch/SearchCustomerSucceeded', $response);
            }
        }
        catch(Xcom_Xfabric_Exception $ex) {
            Mage::logException($ex);
            $errorResponse = $this->_generate_failure_data($data['modifiedSince'],$data['itemsRequested'],$data['startItemIndex'],$ex,$ex->getCode());
            Mage::helper('xcom_xfabric')->send('com.x.customer.v1/CustomerSearch/SearchCustomerFailed',$errorResponse);
        }
        catch(Exception $ex){
            if(!is_null($ex)){
                $message = $ex->getMessage();
            }
            Mage::logException($ex);
            $errorResponse = $this->_generate_failure_data($data['modifiedSince'],$data['itemsRequested'],$data['startItemIndex'],$ex,$ex->getCode());
            Mage::helper('xcom_xfabric')->send('com.x.customer.v1/CustomerSearch/SearchCustomerFailed', $errorResponse);
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
        $customers = Mage::getResourceModel('customer/customer_collection');
        $customers->setOrder('created_at', 'asc');

        $orders = Mage::getResourceModel('sales/order_collection')
            ->addFieldToFilter('customer_is_guest', '1')
            ->addFieldToSelect('*')
            ->setOrder('created_at', 'asc');

        $limit = null;
        $offset = null;
        $modifiedSince = null;

        if(isset($data['itemsRequested']) && $data['itemsRequested'] > 0) {
            $limit = (int)$data['itemsRequested'];
        }

        if (isset($data['startItemIndex']) && $data['startItemIndex'] > 0) {
            $offset = (int)$data['startItemIndex'];
        }

        if (isset($data['modifiedSince']) && $data['modifiedSince'] != '') {
            $modifiedSince = $data['modifiedSince'];
            $customers->addFieldToFilter('updated_at', array('gt' => $modifiedSince));
            $orders->addFieldToFilter('updated_at', array('gt' => $modifiedSince));
        }

        $count = $customers->getSize() + $orders->getSize();

        // as requested by product, list will be from customers table first and then guest orders
        $results = array();

        if ($offset < $customers->getSize()) {
            $customers->getSelect()->limit($limit, $offset);
            $customers->load();

            foreach ($customers as $customer) {
                $c = Mage::getModel('customer/customer')->load((int)($customer->getId()));
                $results[] = Mage::getModel('xcom_chronicle/message_customer', $c)->toArray();
            }
        }

        $shouldUseOrders = false;
        if (!isset($limit)) {
            $shouldUseOrders = true;
        } else {
            if (isset($offset)) {
                $shouldUseOrders = ($offset + $limit >= $customers->getSize()) ? true : false;
            } else {
                $shouldUseOrders = ($limit >= $customers->getSize()) ? true : false;
            }
        }

        if ($shouldUseOrders) {
            $orderLimit = null;
            if (isset($limit)) {
                $orderLimit = $limit - count($results);
            }

            $orderOffset = null;
            if (isset($offset)) {
                if ($offset >= $customers->getSize()) {
                    $orderOffset = $offset - $customers->getSize();
                }
            }

            $orders->getSelect()->limit($orderLimit, $orderOffset);
            $orders->load();

            foreach ($orders as $order) {
                $results[] = Mage::getModel('xcom_chronicle/message_customer_guest', $order)->toArray();
            }
        }

        return array(
            'results' => $results,
            'totalItemsFound' => $count
        );
    }

    protected function _generate_failure_data($modifiedSince, $itemsRequested, $startItemIndex, $ex, $code=null)
    {
        $errorResponse = array(
            'request' => array(
                'modifiedSince' => $modifiedSince,
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
