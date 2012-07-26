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
 * Helper class
 *
 * @package     selenium
 * @subpackage  tests
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class Core_Mage_OrderInvoice_Helper extends Mage_Selenium_TestCase
{
    /**
     * Provides partial or full invoice
     *
     * @param string $captureType
     * @param array $invoiceData
     */
    public function createInvoiceAndVerifyProductQty($captureType = null, $invoiceData = array())
    {
        $invoiceData = $this->arrayEmptyClear($invoiceData);
        $verify = array();
        $this->clickButton('invoice');
        foreach ($invoiceData as $options) {
            if (is_array($options)) {
                $sku = (isset($options['invoice_product_sku'])) ? $options['invoice_product_sku'] : null;
                $productQty = (isset($options['qty_to_invoice'])) ? $options['qty_to_invoice'] : '%noValue%';
                if ($sku) {
                    $verify[$sku] = $productQty;
                    $this->addParameter('sku', $sku);
                    $this->fillForm(array('qty_to_invoice' => $productQty));
                }
            }
        }
        if ($captureType) {
            $this->fillForm(array('amount' => $captureType));
        }
        if (!$verify) {
            $productCount = $this->getXpathCount($this->_getControlXpath('fieldset', 'product_line_to_invoice'));
            for ($i = 1; $i <= $productCount; $i++) {
                $this->addParameter('productNumber', $i);
                $skuXpath = $this->_getControlXpath('field', 'product_sku');
                $qtyXpath = $this->_getControlXpath('field', 'product_qty');
                $prodSku = trim(preg_replace('/SKU:|\\n/', '', $this->getText($skuXpath)));
                if ($this->isElementPresent($qtyXpath . "/input")) {
                    $prodQty = $this->getAttribute($qtyXpath . '/input/@value');
                } else {
                    $prodQty = $this->getText($qtyXpath);
                }
                $verify[$prodSku] = $prodQty;
            }
        }
        $buttonXpath = $this->_getControlXpath('button', 'update_qty');
        if ($this->isElementPresent($buttonXpath . "[not(@disabled)]")) {
            $this->click($buttonXpath);
            $this->pleaseWait();
        }
        $this->clickButton('submit_invoice');
        $this->assertMessagePresent('success', 'success_creating_invoice');
        foreach ($verify as $productSku => $qty) {
            if ($qty == '%noValue%') {
                continue;
            }
            $this->addParameter('sku', $productSku);
            $this->addParameter('invoicedQty', $qty);
            $xpathInvoiced = $this->_getControlXpath('field', 'qty_invoiced');
            $this->assertTrue($this->isElementPresent($xpathInvoiced),
                    'Qty of invoiced products is incorrect at the orders form');
        }
    }

    /**
     * Opens invoice
     *
     * @param array|string $searchData
     */
    public function openInvoice($searchData)
    {
        if (is_string($searchData)) {
            $searchData = $this->loadData($searchData);
        }
        $searchData = $this->arrayEmptyClear($searchData);
        $xpathTR = $this->search($searchData, 'sales_invoice_grid');
        $this->assertNotEquals(null, $xpathTR, 'Invoice is not found');
        $text = $this->getText($xpathTR . '//td[' . $this->getColumnIdByName('Invoice #') . ']');
        $this->addParameter('invoiceId', '#' . $text);
        $this->addParameter('id', $this->defineIdFromTitle($xpathTR));
        $this->click($xpathTR . "//a[text()='View']");
        $this->waitForPageToLoad($this->_browserTimeoutPeriod);
        $this->validatePage();
    }
}