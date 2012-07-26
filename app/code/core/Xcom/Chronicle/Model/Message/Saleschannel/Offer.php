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
class Xcom_Chronicle_Model_Message_Saleschannel_Offer extends Varien_Object
{

    /** @var Mage_Catalog_Model_Product */
    private $_product;
    private $_storeId;
    private $_productId;

    /**
     * @param Mage_Catalog_Model_Product $product
     */
    public function __construct($params)
    {
        $this->_product = $params['product'];
        $this->_storeId = $params['store_id'];
        $this->_productId = $this->_product->getId();

        $product = $this->_product = Mage::getModel('catalog/product')
            ->setStoreId($this->_storeId)
            ->load($this->_product->getId());

        $this->setData($this->_createOffer());

    }

    protected function _createOffer()
    {
        $data = array(
            'id'                => $this->getId(),
            'state'             => $this->_createState(),
            'channelId'         => $this->_createChannelId(),
            'channelAssignedId' => $this->_productId,
            'offerChannelUrl'   => $this->_getOfferUrl(),
            'channelStatus'     => 'Active',
            'sku'               => $this->_getSku(),
            'price'             => $this->_createPrice(),
            'quantity'          => $this->_getQuantity(),
            'startTime'         => null,
            'endTime'           => null,
            'extension'         => null,
         );
        return $data;
    }

    /**
     * Id for the offer
     * @return mixed
     */
    public function getId()
    {
        // The offer id will consist of the base URL stripped of the schema and the store view id plus the product id.
        $formattedBase = preg_replace('/(.*)\:\/\/(.*?)((\/index\.php\/?$)|$|(\/index.php\/admin\/?))/is', '$2', Mage::getBaseUrl());
        $id = $formattedBase . '*' . $this->_storeId . '*' . $this->_productId;
        return $id;
    }

    /**
     * Magento id for the offer
     * @return mixed
     */
    protected function _getChannelAssignedId()
    {
//        return $this->_product->getId();
        return null;
    }

    /**
     * Returns the state of this offer.  Since we only ever send events for published offers this will be hardcoded.
     * @return string
     */
    protected function _createState()
    {
        $status = $this->_product->isInStock() ? 'PUBLISHED' : 'SUSPENDED';
        if(!$this->_product->isVisibleInSiteVisibility()) {
            $status = 'SUSPENDED';
        }
        if (!Mage::app()->getStore($this->_storeId)->getIsActive()) {
            $status = 'SUSPENDED';
        }
        return $status;
    }

    /**
     * Still unsure on exaclty what the contract expects us to put here.  For now Magento makes the most sense
     * @return string
     */
    protected function _createChannelId()
    {
        return 'MAGENTO/' . Mage::app()->getStore($this->_storeId)->getName();
    }

    protected function _getOfferUrl()
    {
//        return Mage::getModel('core/url')->setStore($this->_storeId)->getUrl('catalog/product/view', array('id'=>$this->_product->getId()));
        return $this->_product->getProductUrl();
    }


    protected function _getSku()
    {
        return $this->_product->getSku();
    }

    protected function _createPrice()
    {
        //TODO: use getFinalPrice() once we support start and end time
        return array(
            'amount'    => $this->_product->getPrice(),
            'code'      => Mage::app()->getStore($this->_storeId)->getBaseCurrencyCode(), //getDefaultCurrencyCode(),
        );
    }

    protected function _getQuantity()
    {
        $stockData = $this->_product->getStockData();
        return (int)$this->_product->getStockItem()->getQty(); //(int)$stockData['qty'];
    }
}
