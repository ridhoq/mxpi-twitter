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

class Xcom_Chronicle_Model_Message_Product_Get_Inbound extends Xcom_Xfabric_Model_Message_Response
{

    const PRODUCT_ID_TYPE_SKU = "SKU";
    const PRODUCT_ID_TYPE_ID = "PRODUCT_ID";

    protected $_foundProducts = array();
    protected $_errors = array();
    protected $_notFoundProducts = array();

    /**
     * Initialization of class
     */
    protected function _construct()
    {
        $this->_topic               = 'com.x.pim.v1/ProductLookup/LookupProduct';
        $this->_schemaRecordName    = 'LookupProduct';
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
        try {
            $this->_processSearchQuery($data['ids']);
            if (count($this->_foundProducts) > 0 ){
                $this->_sendFoundProducts();
            }
            if (count($this->_errors) > 0 ) {
                $this->_sendErrors();
            }
        } catch(Exception $e) {
            Mage::logException($e);
            $this->_notFoundProducts = $data['ids'];
            $this->_addSearchError($e->getMessage());
            $this->_sendErrors();
        }
        return $this;
    }

    /**
     * Save/update return policy data to DB
     *
     * @param array $data
     * @return array
     */
    protected function _processSearchQuery(array $ids)
    {
        foreach ($ids as $id) {
            try {
                /** @var $product Mage_Catalog_Model_Product */
                $product = $this->_loadProduct($id);
                if ($product->getId()) {
                    $this->_addFoundProduct($product);
                } else {
                    $this->_notFoundProducts[] = $id;
                    $this->_addSearchError('Product not found');
                }
            } catch (Exception $e) {
                $this->_notFoundProducts[] = $id;
                $this->_addSearchError($e->getMessage());
                Mage::logException($e);
            }
        }

        return $this;
    }

    /**
     * Adds found product to the list of products
     *
     * @param Mage_Catalog_Model_Product $product
     * @return Xcom_Chronicle_Model_Message_Product_Get_Inbound
     */
    protected function _addFoundProduct(Mage_Catalog_Model_Product $product)
    {
        $this->_foundProducts[] = Mage::getModel('xcom_chronicle/message_product', $product)
            ->toArray();
        return $this;
    }

    /**
     * id is an array(
     *     "type"  => ..
     *     "value" => ..
     * )
     *
     * @param array $id
     * @return array|null
     */
    protected function _loadProduct(array $idData)
    {
        $id = null;
        $product = Mage::getModel('catalog/product');
        if ($idData['type'] == self::PRODUCT_ID_TYPE_ID) {
            $id = (int)$idData['value'];
        } elseif ($idData['type'] == self::PRODUCT_ID_TYPE_SKU) {
            $id = $product->getIdBySku($idData['value']);
        }
        if (!is_null($id)) {
            $product->load($id);
        }
        return $product;
    }

    /**
     * Adds id to the list of not found products
     *
     * @param $id
     * @param $message
     */
    protected function _addSearchError($message)
    {
        $this->_errors[] = array(
            'code' => '-1',
            'message' => $message,
            'parameters' => null,
        );
    }

    protected function _sendFoundProducts()
    {
        $data = $this->getBody();
        $response = array(
            'products' => $this->_foundProducts,
            'locales' => $data['locales'],
            'filter' => $data['filter'],
            'destination_id' => $this->getPublisherPseudonym(),
            'correlation_id' => $this->getCorrelationId(),
        );
        Mage::helper('xcom_xfabric')->send('com.x.pim.v1/ProductLookup/LookupProductSucceeded', $response);
    }

    protected function _sendErrors()
    {
        $data = $this->getBody();
        $response = array(
            'ids' => $this->_notFoundProducts,
            'filter' => $data['filter'],
            'locales'=> $data['locales'],
            'errors' => $this->_errors,
            'destination_id' => $this->getPublisherPseudonym(),
            'correlation_id' => $this->getCorrelationId(),
        );
        Mage::helper('xcom_xfabric')->send('com.x.pim.v1/ProductLookup/LookupProductFailed', $response);
    }
}
