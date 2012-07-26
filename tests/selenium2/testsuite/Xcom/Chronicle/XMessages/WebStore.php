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
 * @category    tests
 * @package     selenium
 * @subpackage  tests
 * @author      Magento Core Team <core@magentocommerce.com>
 * @copyright   Copyright (c) 2010 Magento Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/**
 * Defined constants for supported messages
 *
 * @package     selenium
 * @subpackage  Xcom_Chronicle
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class Xcom_Chronicle_XMessages_WebStore
{
    const NAME_SPACE = "com.x.webstore.v1";
    const SEARCH_OFFER_FAILED = "/com.x.webstore.v1/WebStoreOfferSearch/SearchWebStoreOfferFailed";
    const GET_ALL_CATEGORY_FAILED = "/com.x.webstore.v1/WebStoreMetadataProvision/GetAllCategoryFailed";
    const OFFER_QUANTITY_UPDATED = "/com.x.webstore.v1/WebStoreOfferUpdate/WebStoreOfferQuantityUpdated";
    const GET_ALL_WEB_STORE_SUCCEEDED = "/com.x.webstore.v1/WebStoreMetadataProvision/GetAllWebStoreSucceeded";
    const GET_ALL_CATEGORY_SUCCEEDED = "/com.x.webstore.v1/WebStoreMetadataProvision/GetAllCategorySucceeded";
    const OFFER_PRICE_UPDATED = "/com.x.webstore.v1/WebStoreOfferUpdate/WebStoreOfferPriceUpdated";
    const OFFER_DELELTED = "/com.x.webstore.v1/WebStoreOfferDeletion/WebStoreOfferDeleted";
    const OFFER_CREATED = "/com.x.webstore.v1/WebStoreOfferCreation/WebStoreOfferCreated";
    const OFFER_UPDATED = "/com.x.webstore.v1/WebStoreOfferUpdate/WebStoreOfferUpdated";
    const SEARCH_WEB_STORE_OFFER_SUCCEEDED = "/com.x.webstore.v1/WebStoreOfferSearch/SearchWebStoreOfferSucceeded";
    const GET_ALL_WEB_STORE_FAILED = "/com.x.webstore.v1/WebStoreMetadataProvision/GetAllWebStoreFailed";
    const GET_ALL_WEB_STORE_PROVIDER_SUCCEEDED = "/com.x.webstore.v1/WebStoreMetadataProvision/GetAllWebStoreProviderSucceeded";
}

?>
