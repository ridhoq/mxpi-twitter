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

class Xcom_Chronicle_Model_Message_Inventory_Stock_Item extends Varien_Object
{
    /**
     * @param array $params with Mage_CatalogInventory_Model_Stock_Item stock_item and string product_sku
     */
    public function __construct($params)
    {
        $this->setData($this->_createStockItem($params['stock_item'], $params['product_sku']));
    }

    /**
     * @param Mage_CatalogInventory_Model_Stock_Item $stockItem the stock item that needs to be constructed
     * @param $productSku a string representing the SKU for product
     * @return array
     */
    protected function _createStockItem($stockItem, $productSku)
    {
        $shippingOrigin = Mage::getStoreConfig('shipping/origin');
        $locationName = '';
        if (isset($shippingOrigin['country_id'])) {
            $locationName .= $shippingOrigin['country_id'];
        }

        $locationName .= ":";
        if (isset($shippingOrigin['postcode'])) {
            $locationName .= $shippingOrigin['postcode'];
        }

        $locationName .= ":";
        if (isset($shippingOrigin['region_id'])) {
            $locationName .= $shippingOrigin['region_id'];
        }

        $data = array(
            'sku' => $productSku,
            'quantity' => (int)$stockItem->getQty(),
            'locationName' => $locationName
        );

        return $data;
    }
}
