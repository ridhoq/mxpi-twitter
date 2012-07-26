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
class Xcom_Chronicle_XMessages_Pim
{
    const NAME_SPACE = "com.x.pim.v1";
    const SEARCH_PRODUCT = "/com.x.pim.v1/ProductSearch/SearchProduct";
    const SEARCH_PRODUCT_FAILED = "/com.x.pim.v1/ProductSearch/SearchProductFailed";
    const SEARCH_PRODUCT_SUCCEEDED = "/com.x.pim.v1/ProductSearch/SearchProductSucceeded";
    const LOOKUP_PRODUCT = "/com.x.pim.v1/ProductLookup/LookupProduct";
    const LOOKUP_PRODUCT_FAILED = "/com.x.pim.v1/ProductLookup/LookupProductFailed";
    const LOOKUP_PRODUCT_SUCCEEDED = "/com.x.pim.v1/ProductLookup/LookupProductSucceeded";
    const PRODUCT_DELELTED = "/com.x.pim.v1/ProductDeletion/ProductDeleted";
    const PRODUCT_CREATED = "/com.x.pim.v1/ProductCreation/ProductCreated";
    const PRODUCT_UPDATED = "/com.x.pim.v1/ProductUpdate/ProductUpdated";

}

?>
