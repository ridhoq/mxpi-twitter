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
 * Prices Validation on the Backend
 *
 * @package     selenium
 * @subpackage  tests
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class Core_Mage_Tax_TaxAndPricesValidationBackendTest extends Mage_Selenium_TestCase
{
    public function setUpBeforeTests()
    {
        $this->loginAdminUser();
        $this->navigate('system_configuration');
        $this->systemConfigurationHelper()->configure('shipping_settings_default');
        $this->systemConfigurationHelper()->configure('flat_rate_for_price_verification');
        $this->navigate('manage_tax_rule');
        $this->taxHelper()-> deleteRulesExceptSpecified(array('Retail Customer-Taxable Goods-Rate 1'));
    }

    protected function assertPreConditions()
    {
        $this->addParameter('id', '0');
    }

    /**
     * Create Customer for tests
     *
     * @return string $userData
     * @test
     * @group preConditions
     */
    public function createCustomer()
    {
        //Data
        $userData = $this->loadData('customer_account_for_prices_validation', null, 'email');
        $addressData = $this->loadData('customer_account_address_for_prices_validation');
        //Steps
        $this->navigate('manage_customers');
        $this->customerHelper()->createCustomer($userData, $addressData);
        //Verifying
        $this->assertMessagePresent('success', 'success_saved_customer');

        return $userData['email'];
    }

    /**
     * Create Simple Products for tests
     *
     * @return array $products
     * @test
     * @group preConditions
     */
    public function createProducts()
    {
        $products = array();
        $this->navigate('manage_products');
        for ($i = 1; $i <= 3; $i++) {
            $simpleProductData = $this->loadData("simple_product_for_prices_validation_$i",
                null, array('general_name', 'general_sku'));
            $products['sku'][$i] = $simpleProductData['general_sku'];
            $products['name'][$i] = $simpleProductData['general_name'];
            $this->productHelper()->createProduct($simpleProductData);
            $this->assertMessagePresent('success', 'success_saved_product');
        }
        return $products;
    }

    /**
     * Create Order on the backend and validate prices with taxes
     *
     * @param $sysConfigData
     * @param $customer
     * @param $products
     *
     * @dataProvider createOrderBackendDataProvider
     * @depends createCustomer
     * @depends createProducts
     *
     * @test
     *
     * @group skip_due_to_bug
     */
    public function createOrderBackend($sysConfigData, $customer, $products)
    {
        //Preconditions
        $this->navigate('system_configuration');
        $this->systemConfigurationHelper()->configure($sysConfigData);
        //Data for order creation
        $crOrder = $this->loadData($sysConfigData . '_backend_create_order', array('email' => $customer));
        //Data for prices and total verification after order creation
        $priceAftOrdCr = $this->loadData($sysConfigData . '_backend_product_prices_after_order');
        $totAftOrdCr = $this->loadData($sysConfigData . '_backend_total_after_order');
        //Data for prices and total verification before invoice creation
        $priceBefInvCr = $this->loadData($sysConfigData . '_backend_product_prices_before_invoice');
        $totBefInvCr = $this->loadData($sysConfigData . '_backend_total_before_invoice');
        //Data for prices and total verification after invoice creation on order page
        $priceAftInvCr = $this->loadData($sysConfigData . '_backend_product_prices_after_invoice');
        $totAftInvCr = $this->loadData($sysConfigData . '_backend_total_after_invoice');
        //Data for prices and total verification after invoice creation on invoice page
        $priceAftInvCrOnInv = $this->loadData($sysConfigData . '_backend_product_prices_after_invoice_on_invoice');
        $totAftInvCrOnInv = $this->loadData($sysConfigData . '_backend_total_after_invoice_on_invoice');
        //Data for prices and total verification after invoice creation on invoice page
        $priceAftShipCr = $this->loadData($sysConfigData . '_backend_product_prices_after_shipment');
        $totAftShipCr = $this->loadData($sysConfigData . '_backend_total_after_shipment');
        //Data for prices and total verification before refund creation on refund page
        $priceBefRefCr = $this->loadData($sysConfigData . '_backend_product_prices_before_refund');
        $totBefRefCr = $this->loadData($sysConfigData . '_backend_total_before_refund');
        //Data for prices and total verification after refund creation on order page
        $priceAftRefCr = $this->loadData($sysConfigData . '_backend_product_prices_after_refund');
        $totAftRefCr = $this->loadData($sysConfigData . '_backend_total_after_refund');
        //Data for prices and total verification after refund creation on refund page
        $priceAftRefCrOnRef = $this->loadData($sysConfigData . '_backend_product_prices_after_refund_on_refund');
        $totAftRefCrOnRef = $this->loadData($sysConfigData . '_backend_total_after_refund_on_refund');
        for ($i = 1; $i <= 3; $i++) {
            $crOrder['products_to_add']['product_' . $i]['filter_sku'] = $products['sku'][$i];
            $crOrder['prod_verification']['product_' . $i]['product'] = $products['name'][$i];
            $priceAftOrdCr['product_' . $i]['product'] = $products['name'][$i];
            $priceBefInvCr['product_' . $i]['product'] = $products['name'][$i];
            $priceAftInvCr['product_' . $i]['product'] = $products['name'][$i];
            $priceAftInvCrOnInv['product_' . $i]['product'] = $products['name'][$i];
            $priceAftShipCr['product_' . $i]['product'] = $products['name'][$i];
            $priceBefRefCr['product_' . $i]['product'] = $products['name'][$i];
            $priceAftRefCr['product_' . $i]['product'] = $products['name'][$i];
            $priceAftRefCrOnRef['product_' . $i]['product'] = $products['name'][$i];
        }
        //Create Order and validate prices during order creation
        $this->navigate('manage_sales_orders');
        $this->orderHelper()->createOrder($crOrder);
        //Define Order Id to work with
        $orderId = $this->orderHelper()->defineOrderId();
        //Verify prices on order review page after order creation
        $this->shoppingCartHelper()->verifyPricesDataOnPage($priceAftOrdCr, $totAftOrdCr);
        //Verify prices before creating Invoice
        $this->clickButton('invoice');
        $this->shoppingCartHelper()->verifyPricesDataOnPage($priceBefInvCr, $totBefInvCr);
        //Verify prices after creating invoice on order page
        $this->clickButton('submit_invoice');
        $this->shoppingCartHelper()->verifyPricesDataOnPage($priceAftInvCr, $totAftInvCr);
        //Verify prices after creating shipment on order page
        $this->clickButton('ship');
        $this->clickButton('submit_shipment');
        $this->shoppingCartHelper()->verifyPricesDataOnPage($priceAftShipCr, $totAftShipCr);
        //Verify prices before creating refund on refund page
        $this->clickButton('credit_memo');
        $this->shoppingCartHelper()->verifyPricesDataOnPage($priceBefRefCr, $totBefRefCr);
        //Verify prices after creating refund on order page
        $this->clickButton('refund_offline');
        $this->shoppingCartHelper()->verifyPricesDataOnPage($priceAftRefCr, $totAftRefCr);
        //Verify prices after creating invoice on invoice page
        $this->navigate('manage_sales_invoices');
        $this->orderInvoiceHelper()->openInvoice(array('filter_order_id' => $orderId));
        $this->shoppingCartHelper()->verifyPricesDataOnPage($priceAftInvCrOnInv, $totAftInvCrOnInv);
        //Verify prices after creating Refund on Refund page
        $this->navigate('manage_sales_creditmemos');
        $this->searchAndOpen(array('filter_order_id' => $orderId));
        $this->shoppingCartHelper()->verifyPricesDataOnPage($priceAftRefCrOnRef, $totAftRefCrOnRef);
    }

    public function createOrderBackendDataProvider()
    {
        return array(
            array('unit_cat_ex_ship_ex'),
            array('row_cat_ex_ship_ex'),
            array('total_cat_ex_ship_ex'),
            array('unit_cat_ex_ship_in'),
            array('row_cat_ex_ship_in'),
            array('total_cat_ex_ship_in'),
            array('unit_cat_in_ship_ex'),
            array('row_cat_in_ship_ex'),
            array('total_cat_in_ship_ex'),
            array('unit_cat_in_ship_in'),
            array('row_cat_in_ship_in'),
            array('total_cat_in_ship_in')
        );
    }
}
