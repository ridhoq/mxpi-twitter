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
 * @copyright   Copyright (c) 2012 Magento Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/**
 * Simple and virtual product review test
 *
 * @package     selenium
 * @subpackage  tests
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class Core_Mage_Product_ReviewTest extends Mage_Selenium_TestCase
{
    /**
     * <p>Preconditions:</p>
     */
    protected function assertPreConditions()
    {
        $this->loginAdminUser();
        $this->navigate('manage_products');
        $this->addParameter('id', '0');
    }

    /**
     * <p>Review product on frontend.</p>
     * <p>Steps:</p>
     * <p>1. Create simple and virtual products in stock and out of stock;</p>
     * <p>2. Fill custom options for each product;</p>
     * <p>3. Navigate to frontend;</p>
     * <p>4. Validate the product details;</p>
     * <p>Expected result:</p>
     * <p>Products are created, Custom options are available for in stock product and disabled for out of stock;</p>
     *
     * @param $productType
     * @param $availability
     *
     * @test
     * @dataProvider reviewInfoInProductDetailsDataProvider
     * @TestlinkId    TL-MAGE-3469
     */
    public function reviewInfoInProductDetails($productType, $availability)
    {
        $productData = $this->loadData('frontend_' . $productType . '_product_details_validation',
                array('inventory_stock_availability' => $availability), array('general_name', 'general_sku'));
        $this->productHelper()->createProduct($productData, $productType);
        $this->assertMessagePresent('success', 'success_saved_product');
        $this->reindexInvalidedData();
        $this->clearInvalidedCache();
        $this->productHelper()->frontVerifyProductInfo($productData);
    }

    public function reviewInfoInProductDetailsDataProvider()
    {
        return array(
            array('simple', 'In Stock'),
            array('simple', 'Out of Stock'),
            array('virtual', 'In Stock'),
            array('virtual', 'Out of Stock'),
        );
    }
}