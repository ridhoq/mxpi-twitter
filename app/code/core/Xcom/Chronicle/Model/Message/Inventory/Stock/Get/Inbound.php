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

class Xcom_Chronicle_Model_Message_Inventory_Stock_Get_Inbound extends Xcom_Xfabric_Model_Message_Response
{
    /**
     * Initialization of class
     */
    protected function _construct()
    {
        $this->_topic               = 'com.x.inventory.v1/StockItemLookup/LookupStockItem';
        $this->_schemaRecordName    = 'LookupStockItem';
        $this->_schemaVersion       = "1.0.0";

        parent::_construct();
    }

    /**
     * Builds the Get success and/or failure outbound messages.
     * @return Xcom_Chronicle_Model_Message_Inventory_Stock_Get_Inbound this message
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
                $resultSet = $this->_processLookup($data);

                if (sizeof($resultSet['results']) > 0) {
                    $response = array(
                        'stockItems' => $resultSet['results'],
                        'destination_id' => $this->getPublisherPseudonym(),
                        'correlation_id' => $this->getCorrelationId(),
                    );
                    Mage::helper('xcom_xfabric')->send('com.x.inventory.v1/StockItemLookup/LookupStockItemSucceeded', $response);
                }
                if (sizeof($resultSet['errors']) > 0) {
                    $response = array(
                        'errors' => $resultSet['errors'],
                        'destination_id' => $this->getPublisherPseudonym(),
                        'correlation_id' => $this->getCorrelationId(),
                    );
                    Mage::helper('xcom_xfabric')->send('com.x.inventory.v1/StockItemLookup/LookupStockItemFailed', $response);
                }
            }
        } catch (Exception $ex) {
            Mage::logException($ex);
            $errors = array();
            foreach ($data['stockItemIds'] as $id) {
                $errors[] = array(
                    'sku' => $id,
                    'errors' => array(array(
                        'code' => '-1',
                        'message' => 'Exception raised while processing lookup for inventory.',
                        'parameters' => null))
                );
            }
            $response = array(
                'errors' => $errors,
                'destination_id' => $this->getPublisherPseudonym(),
                'correlation_id' => $this->getCorrelationId(),
            );
            Mage::helper('xcom_xfabric')->send('com.x.inventory.v1/StockItemLookup/LookupStockItemFailed', $response);
        }

        return $this;
    }

    /**
     * Save/update return policy data to DB
     *
     * @param array $data
     * @return array
     */
    protected function _processLookup(&$data)
    {
        $results = array();
        $errors = array();

        foreach ($data['skus'] as $id) {
            $stockItem = null;

            try{
                $stockItem = $this->_getStockItemBySku($id);

                if (is_null($stockItem)) {
                    $errors[] = array(
                        'sku' => $id,
                        'errors' => array(array(
                            'code' => '-1',
                            'message' => 'Could not find product with given sku',
                            'parameters' => null))
                    );
                }
                else{
                    $results[] = $stockItem;
                }
            }
            catch(Exception $ex){
                 Mage::logException($ex);
                 $errors[] = array(
                    'sku' => $id,
                    'errors' => array(array(
                        'code' => '-1',
                        'message' => 'Exception when looking up product.',
                        'parameters' => null))
                );
            }
        }
        $resultSet = array(
            'results' => $results,
            'errors' => $errors,
        );
        return $resultSet;
    }

    protected function _getStockItemBySku($sku)
    {
        $products = Mage::getResourceModel('catalog/product_collection')
            ->addFieldToFilter('sku', $sku);
        $products->load();
        if (count($products)==1) {
            foreach ($products as $product) {
                $p = Mage::getModel('catalog/product')->load((int)$product->getId());
                return Mage::getModel('xcom_chronicle/message_inventory_stock_item',
                    array('stock_item' => $p->getStockItem(), 'product_sku' => $sku))->toArray();
            }
        }

        return null;
    }
}
