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
 * Helper class
 *
 * @package     selenium
 * @subpackage  tests
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class Core_Mage_ShoppingCart_Helper extends Mage_Selenium_TestCase
{
    const QTY = 'Qty';
    const EXCLTAX = '(Excl. Tax)';
    const INCLTAX = '(Incl. Tax)';

    /**
     * Get table column names and column numbers
     *
     * @param string $tableHeadName
     * @param bool $transforKeys
     *
     * @return array
     */
    public function getColumnNamesAndNumbers($tableHeadName = 'product_table_head', $transforKeys = true)
    {
        $headXpath = $this->_getControlXpath('pageelement', $tableHeadName);
        $isExlAndInclInHead = false;
        $lineQty = $this->getXpathCount($headXpath . '/tr');
        if ($lineQty == 2) {
            $isExlAndInclInHead = true;
            $headXpath .= "/tr[contains(@class,'first')]";
        }
        $columnXpath = $headXpath . '//th';
        $columnQty = $this->getXpathCount($columnXpath);
        $returnData = array();
        $y = 1;
        for ($i = 1; $i <= $columnQty; $i++) {
            if ($this->isElementPresent($columnXpath . "[$i][@colspan]")) {
                $text = $this->getText($columnXpath . "[$i]");
                if ($isExlAndInclInHead && $this->getAttribute($columnXpath . "[$i]/@colspan") == 2) {
                    $returnData[$y] = $text . self::EXCLTAX;
                    $returnData[$y + 1] = $text . self::INCLTAX;
                } else {
                    $returnData[$y] = $text;
                }
                $y = $y + $this->getAttribute($columnXpath . "[$i]/@colspan");
            } else {
                $text = $this->getText($columnXpath . "[$i]");
                $returnData[$y++] = $text;
            }
        }
        $returnData = array_diff($returnData, array(''));
        if ($transforKeys) {
            foreach ($returnData as $key => &$value) {
                $value = trim(strtolower(preg_replace('#[^0-9a-z]+#i', '_', $value)), '_');
                if ($value == 'action') {
                    unset($returnData[$key]);
                }
            }
        }

        return array_flip($returnData);
    }

    /**
     * Get all Products info in Shopping Cart
     *
     * @return array
     */
    public function getProductInfoInTable()
    {
        $productValues = array();

        $tableRowNames = $this->getColumnNamesAndNumbers();
        $productLine = $this->_getControlXpath('pageelement', 'product_line');

        $productCount = $this->getXpathCount($productLine);
        for ($i = 1; $i <= $productCount; $i++) {
            foreach ($tableRowNames as $key => $value) {
                $xpathValue = $productLine . "[$i]//td[$value]";
                if ($key == 'qty' && $this->isElementPresent($xpathValue . '/input/@value')) {
                    $productValues['product_' . $i][$key] = $this->getAttribute($xpathValue . '/input/@value');
                } else {
                    $text = $this->getText($xpathValue);
                    if (preg_match('/Excl. Tax/', $text)) {
                        $text = preg_replace("/ \\n/", ':', $text);
                        $values = explode(':', $text);
                        $values = array_map('trim', $values);
                        foreach ($values as $k => $v) {
                            if ($v == 'Excl. Tax' && isset($values[$k + 1])) {
                                $productValues['product_' . $i][$key . '_excl_tax'] = $values[$k + 1];
                            }
                            if ($v == 'Incl. Tax' && isset($values[$k + 1])) {
                                $productValues['product_' . $i][$key . '_incl_tax'] = $values[$k + 1];
                            }
                        }
                    } elseif (preg_match('/Ordered/', $text)) {
                        $values = explode(' ', $text);
                        $values = array_map('trim', $values);
                        foreach ($values as $k => $v) {
                            if ($k % 2 != 0 && isset($values[$k - 1])) {
                                $productValues['product_' . $i][$key . '_'
                                    . strtolower(preg_replace('#[^0-9a-z]+#i', '', $values[$k - 1]))] = $v;
                            }
                        }
                    } else {
                        $productValues['product_' . $i][$key] = trim($text);
                    }
                }
            }
        }

        foreach ($productValues as $key => &$productData) {
            $productData = array_diff($productData, array(''));
            foreach ($productData as $fieldName => &$fieldValue) {
                if (preg_match('/([\d]+\.[\d]+)|([\d]+)/', $fieldValue)) {
                    preg_match_all('/^-?.?([\d]+\.[\d]+(\%)?)|([\d]+(\%)?)/', $fieldValue, $price);
                    $fieldValue = $price[0][0];
                }
                if (preg_match('/SKU:/', $fieldValue)) {
                    $fieldValue = substr($fieldValue, 0, strpos($fieldValue, ':') - 3);
                }
            }
        }

        return $productValues;
    }

    /**
     * Get all order prices info in Shopping Cart
     *
     * @return array
     */
    public function getOrderPriceData()
    {
        $setXpath = $this->_getControlXpath('pageelement', 'price_totals') . '/descendant::tr';
        $count = $this->getXpathCount($setXpath);
        $returnData = array();
        for ($i = $count; $i >= 1; $i--) {
            if ($this->getXpathCount($setXpath . "[$i]/*") > 1) {
                $fieldName = $this->getText($setXpath . "[$i]/*[1]");
                if (!preg_match('/\$\(([\d]+\.[\d]+)|([\d]+)\%\)/', $fieldName)) {
                    $fieldName = trim(strtolower(preg_replace('#[^0-9a-z]+#i', '_', $fieldName)), '_');
                }
                $fieldValue = $this->getText($setXpath . "[$i]/*[2]");
                $returnData[$fieldName] = trim($fieldValue, "\x00..\x1F");
            }
        }

        return array_diff($returnData, array(''));
    }

    /**
     * Verify prices data on page
     *
     * @param string|array $productData
     * @param string|array $orderPriceData
     */
    public function verifyPricesDataOnPage($productData, $orderPriceData)
    {
        if (is_string($productData)) {
            $productData = $this->loadData($productData);
        }
        if (is_string($orderPriceData)) {
            $orderPriceData = $this->loadData($orderPriceData);
        }
        //Get Products data and order prices data
        $actualProductData = $this->getProductInfoInTable();
        $actualOrderPriceData = $this->getOrderPriceData();
        //Verify Products data
        $actualProductQty = count($actualProductData);
        $expectedProductQty = count($productData);
        if ($actualProductQty != $expectedProductQty) {
            $this->addVerificationMessage("'" . $actualProductQty . "' product(s) added to Shopping cart but must be '"
                                              . $expectedProductQty . "'");
        } else {
            for ($i = 1; $i <= $actualProductQty; $i++) {
                $productName = '';
                foreach ($actualProductData['product_' . $i] as $key => $value) {
                    if (preg_match('/^product/', $key)) {
                        $productName = $value;
                        break;
                    }
                }
                $this->compareArrays($actualProductData['product_' . $i], $productData['product_' . $i], $productName);
            }
        }
        //Verify order prices data
        $this->compareArrays($actualOrderPriceData, $orderPriceData);
        $this->assertEmptyVerificationErrors();
    }

    /**
     *
     * @param array $actualArray
     * @param array $expectedArray
     * @param string $productName
     */
    public function compareArrays($actualArray, $expectedArray, $productName = '')
    {
        foreach ($actualArray as $key => $value) {
            if (array_key_exists($key, $expectedArray) && (strcmp($expectedArray[$key], trim($value)) == 0)) {
                unset($expectedArray[$key]);
                unset($actualArray[$key]);
            }
        }

        if ($productName) {
            $productName = $productName . ': ';
        }

        if ($actualArray) {
            $actualErrors = $productName . "Data is displayed on the page: \n";
            foreach ($actualArray as $key => $value) {
                $actualErrors .= "Field '$key': value '$value'\n";
            }
        }
        if ($expectedArray) {
            $expectedErrors = $productName . "Data should appear on the page: \n";
            foreach ($expectedArray as $key => $value) {
                $expectedErrors .= "Field '$key': value '$value'\n";
            }
        }
        if (isset($actualErrors)) {
            $this->addVerificationMessage(trim($actualErrors, "\x00..\x1F"));
        }
        if (isset($expectedErrors)) {
            $this->addVerificationMessage(trim($expectedErrors, "\x00..\x1F"));
        }
    }

    /**
     *
     * @param string|array $shippingAddress
     * @param string|array $shippingMethod
     * @param boolean $validate
     */
    public function frontEstimateShipping($shippingAddress, $shippingMethod, $validate = true)
    {
        if (is_string($shippingAddress)) {
            $shippingAddress = $this->loadData($shippingAddress);
        }
        $shippingAddress = $this->arrayEmptyClear($shippingAddress);
        $this->fillForm($shippingAddress);
        $this->clickButton('get_quote');
        $this->chooseShipping($shippingMethod, $validate);
        $this->clickButton('update_total');
    }

    /**
     *
     * @param array $shippingMethod
     */
    public function chooseShipping($shippingMethod)
    {
        if (is_string($shippingMethod)) {
            $shippingMethod = $this->loadData($shippingMethod);
        }
        $shipService = (isset($shippingMethod['shipping_service'])) ? $shippingMethod['shipping_service'] : null;
        $shipMethod = (isset($shippingMethod['shipping_method'])) ? $shippingMethod['shipping_method'] : null;
        if (!$shipService or !$shipMethod) {
            $this->addVerificationMessage('Shipping Service(or Shipping Method) is not set');
        } else {
            $this->addParameter('shipService', $shipService);
            $this->addParameter('shipMethod', $shipMethod);
            if ($this->isElementPresent($this->_getControlXpath('field', 'ship_service_name'))) {
                $method = $this->_getControlXpath('radiobutton', 'ship_method');
                if ($this->isElementPresent($method)) {
                    $this->click($method);
                    $this->waitForAjax();
                } else {
                    $this->addVerificationMessage('Shipping Method "' . $shipMethod . '" for "'
                                                      . $shipService . '" is currently unavailable.');
                }
            } else {
                $this->addVerificationMessage('Shipping Service "' . $shipService . '" is currently unavailable.');
            }
        }
        $this->assertEmptyVerificationErrors();
    }

    /**
     * Open and clear Shopping Cart
     */
    public function frontClearShoppingCart()
    {
        if ($this->getArea() == 'frontend' && !$this->isElementPresent("//a[text()='My Cart']")) {
            $this->frontend('shopping_cart');
            $productLine = $this->_getControlXpath('pageelement', 'product_line');
            $productCount = $this->getXpathCount($productLine);
            for ($i = 1; $i <= $productCount; $i++) {
                $this->addParameter('productNumber', $i);
                $this->type($this->_getControlXpath('field', 'product_qty'), 0);
            }
            $this->clickButton('update_shopping_cart');
            $this->assertMessagePresent('success', 'shopping_cart_is_empty');
        }
    }

    /**
     * Moves products to the wishlist from Shopping Cart
     *
     * @param string|array $productNameSet Name or array of product names to move
     */
    public function frontMoveToWishlist($productNameSet)
    {
        if (is_string($productNameSet)) {
            $productNameSet = array($productNameSet);
        }
        foreach ($productNameSet as $productName) {
            $this->addParameter('productName', $productName);
            if ($this->controlIsPresent('checkbox', 'move_to_wishlist')) {
                $this->fillForm(array('move_to_wishlist' => 'Yes'));
            } else {
                $this->fail('Product ' . $productName . ' is not in the shopping cart.');
            }
        }
        $this->clickButton('update_shopping_cart');
    }

    /**
     * Verifies if the product(s) are in the Shopping Cart
     *
     * @param string|array $productNameSet Product name (string) or array of product names to check
     *
     * @return bool|array True if the products are all present.
     *                    Otherwise returns an array of product names that are absent.
     */
    public function frontShoppingCartHasProducts($productNameSet)
    {
        if (is_string($productNameSet)) {
            $productNameSet = array($productNameSet);
        }
        $absentProducts = array();
        foreach ($productNameSet as $productName) {
            $this->addParameter('productName', $productName);
            if (!$this->controlIsPresent('link', 'product_name')) {
                $absentProducts[] = $productName;
            }
        }
        return (empty($absentProducts)) ? true : $absentProducts;
    }
}