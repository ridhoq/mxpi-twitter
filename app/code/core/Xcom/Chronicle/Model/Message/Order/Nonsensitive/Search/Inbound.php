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

class Xcom_Chronicle_Model_Message_Order_Nonsensitive_Search_Inbound extends Xcom_Xfabric_Model_Message_Response
{
    /**
     * Initialization of class
     */
    protected function _construct()
    {
        $this->_topic               = 'com.x.ordermanagement.v2/OrderSearch.NonSensitive/SearchOrder';
        $this->_schemaVersion       = "2.2.8";

        parent::_construct();
    }

    /**
     * Process data on message received
     * @return Xcom_Chronicle_Model_Message_Order_Search_Inbound
     */
    public function process()
    {
        parent::process();
        $data = $this->getBody();
        if (!isset($data)) {
            $data = array();
        }
        try {
            if ($this->_validateSchema()) {
                $results = $this->_processSearchQuery($data);
                $response = array(
                    'orders' => $results,
                    'query' => $data['query'],
                    'destination_id' => $this->getPublisherPseudonym(),
                    'correlation_id' => $this->getCorrelationId(),
                );
                Mage::helper('xcom_xfabric')->send(
                    'com.x.ordermanagement.v2/OrderSearch.NonSensitive/SearchNonSensitiveOrderSucceeded', $response);
            }
        }
        catch(Xcom_Xfabric_Exception $ex) {
            Mage::logException($ex);
            $errorResponse = $this->_generateFailureData($data['query'],$ex,$ex->getCode());
            Mage::helper('xcom_xfabric')->send(
                'com.x.ordermanagement.v2/OrderSearch.NonSensitive/SearchOrderFailed',$errorResponse);
            Mage::helper("Sent failure");

        }
        catch(Exception $ex){
            Mage::logException($ex);
            $errorResponse = $this->_generateFailureData($data['query'],$ex);
            Mage::helper('xcom_xfabric')->send(
                'com.x.ordermanagement.v2/OrderSearch.NonSensitive/SearchOrderFailed', $errorResponse);
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
        /** @var $orders Mage_Sales_Model_Mysql4_Collection_Abstract */
        $orders = Mage::getResourceModel('sales/order_collection')
            ->addFieldToSelect('*')
            ->setOrder('created_at', 'asc');
        $count = 0;
        if (isset($data['query'])) {
            $query = $data['query'];
            $limit = null;
            $offset = null;
            if (isset($query['numberItems']) && $query['numberItems'] > 0) {

                $temp = (int)($query['numberItems']);
                $query['numberItems'] = (int)$temp;
                $limit = $query['numberItems'];
            }
            if (isset($query['startItemIndex']) && $query['startItemIndex'] >= 0) {
                $temp = (int)($query['startItemIndex']);
                $query['startItemIndex'] = (int)$temp;
                $offset = $query['startItemIndex'];
            }
            if (isset($query['fields'])) {
                Mage::throwException('Unsupported query parameter: fields');
            }
            if (isset($query['predicates'])) {
                Mage::throwException('Unsupported query parameter: predicates');
            }
            if (isset($query['ordering']) && !empty($query['ordering'])) {
                Mage::throwException('Unsupported query parameter: ordering');
            }

            $count = $orders->getSize();

            $orders->getSelect()->limit($limit,$offset);
        } else {
            $data['query'] = array(
                'numberItemsFound' => null,
                'fields' => null,
                'predicates' => null,
                'ordering' => array(),
                'numberItems' => null,
                'startItemIndex' => null,
            );
            $count = $orders->getSize();
        }
        $orders->load();
        $data['query']['numberItemsFound'] = $count;
        $results = array();
        foreach ($orders as $order) {
            $results[] = Mage::getModel('xcom_chronicle/message_order_nonsensitive',
                array(
                    'order' => $order,
                    'type' => Xcom_Chronicle_Model_Message_Order::TYPE_NON_SENSITIVE
                )
            )->toArray();
        }
        return $results;
    }

    protected function _generateFailureData($query,$ex,$code=null)
    {
        $errorResponse = array(
            'search' => array('query' => $query),
            'errors' => array(
                array(
                    'code' => empty($code) ? '-1' : '' . $code,
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
