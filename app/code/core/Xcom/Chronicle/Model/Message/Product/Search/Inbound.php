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

class Xcom_Chronicle_Model_Message_Product_Search_Inbound extends Xcom_Xfabric_Model_Message_Response
{
    /**
     * Initialization of class
     */
    protected function _construct()
    {
        $this->_topic               = 'com.x.pim.v1/ProductSearch/SearchProduct';
        $this->_schemaRecordName    = 'SearchProduct';
        $this->_schemaVersion       = "1.0.0";

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
        if ( !isset( $data['locales'] ) ) {
            $data['locales'] = null;
        }
        if ( !isset( $data['query'] ) ) {
            $data['query'] = null;
        }
        try {
            if ($this->_validateSchema()) {
                $results = $this->_processSearchQuery($data);
                    $response = array(
                        'products' => $results,
                        'locales' => $data['locales'],
                        'query' => $data['query'],
                        'destination_id' => $this->getPublisherPseudonym(),
                        'correlation_id' => $this->getCorrelationId(),
                    );

                    Mage::helper('xcom_xfabric')->send('com.x.pim.v1/ProductSearch/SearchProductSucceeded', $response);
            }
        } catch(Xcom_Xfabric_Exception $ex) {
            Mage::logException($ex);
            $errorResponse = $this->_generateFailureData($data['query'],$data['locales'],$ex,$ex->getCode());
            Mage::helper('xcom_xfabric')->send('com.x.pim.v1/ProductSearch/SearchProductFailed',$errorResponse);
        } catch(Exception $ex){
            Mage::logException($ex);
            if(!is_null($ex)){
                $message = $ex->getMessage();
            }
            $errorResponse = $this->_generateFailureData($data['query'],$data['locales'],$ex);
            Mage::helper('xcom_xfabric')->send('com.x.pim.v1/ProductSearch/SearchProductFailed', $errorResponse);
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
            if (isset($query['predicates'])){
                Mage::throwException('Unsupported query parameter: predicates');
            }
            if (isset($query['ordering'])) {
                Mage::throwException('Unsupported query parameter: ordering');
            }

            $count = $products->getSize();

            $products->getSelect()->limit($limit,$offset);
        }
        else{
            $data['query'] = array(
                'numberItemsFound' => null,
                'fields' => null,
                'predicates' => null,
                'ordering' => null,
                'numberItems' => null,
                'startItemIndex' => null,
            );

            $count = $products->getSize();
        }
        $products->load();

        $results = array();
        foreach ($products as $product) {
            $p =Mage::getModel('catalog/product')->load((int)$product->getId());
            $results[] = Mage::getModel('xcom_chronicle/message_product', $p)->toArray();
        }
        $data['query']['numberItemsFound'] = $count;

        return $results;
    }

    protected function _generateFailureData($query, $locale, $ex, $code=null)
    {
        $errorResponse = array(
            'query' => $query,
            'locales' => $locale,
            'errors' => array(
                array(
                    'code' => empty($code) ? '-1': '' . $code,
                    'message' => $ex->getMessage(),
                    'parameters' => null,
                )
            ),
            'destination_id' => $this->getPublisherPseudonym(),
            'correlation_id' => $this->getCorrelationId(),
        );
        return $errorResponse;
    }
}
