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
class Core_Mage_CheckoutMultipleAddresses_Helper extends Mage_Selenium_TestCase
{
    /**
     *
     * @var string
     */
    protected static $activeTab = "[contains(@class,'active')]";

    /**
     * Create order using multiple addresses checkout
     *
     * @param array|string  $checkoutData
     * @param bool          $placeOrder
     * @return array       $orderIds
     */
    public function frontCreateMultipleCheckout($checkoutData, $placeOrder = true)
    {
        if (is_string($checkoutData)) {
            $checkoutData = $this->loadData($checkoutData);
        }
        $checkoutData = $this->arrayEmptyClear($checkoutData);
        $this->frontDoMultipleCheckoutSteps($checkoutData);
        if ($placeOrder) {
            $this->frontOrderReview($checkoutData);
            $this->clickButton('place_order', false);
            $this->waitForAjax();
            $this->assertTrue($this->checkoutOnePageHelper()->verifyNotPresetAlert(), $this->getParsedMessages());
            $this->waitForTextNotPresent('Submitting order information.');
            $this->validatePage();
            $this->assertMultipleAddrCheckoutPageOpened('order_success');
            $xpath = $this->_getControlXpath('link', 'order_number');
            if ($this->isElementPresent($xpath)) {
                return $this->formOrderIdsArray($this->getText($xpath));
            }
            return $this->formOrderIdsArray($this->getText("//*[contains(text(),'Your order')]"));
        }
    }

    /**
     * Returns order Ids in Array
     *
     * @param string $text
     *
     * @return array
     */
    public function formOrderIdsArray($text)
    {
        $nodes = explode(',', $text);
        $orderIds = array();
        foreach ($nodes as $value) {
            $orderIds[] = preg_replace('/[^0-9]/', '', $value);
        }
        return $orderIds;
    }


    /**
     * Checks the page opened
     *
     * @param string $pageElName
     */
    public function assertMultipleAddrCheckoutPageOpened($pageElName)
    {
        $setXpath = $this->_getControlXpath('pageelement', $pageElName);
        if (!$this->isElementPresent($setXpath . self::$activeTab)) {
            $messages = $this->getMessagesOnPage();
            if ($messages && is_array($messages)) {
                $messages = implode("\n", call_user_func_array('array_merge', $messages));
            }
            $this->fail("'" . $pageElName . "' step is not selected:\n" . $messages);
        }
    }

    /**
     * Provides checkout steps
     *
     * @param array  $checkoutData
     */
    public function frontDoMultipleCheckoutSteps(array $checkoutData)
    {
        $checkoutData = $this->arrayEmptyClear($checkoutData);
        $products   = (isset($checkoutData['products_to_add'])) ? $checkoutData['products_to_add'] : array();
        $customer   = (isset($checkoutData['checkout_as_customer'])) ? $checkoutData['checkout_as_customer'] : null;
        $generalShippingAddress = null;
        foreach ($checkoutData as $key => $value) {
            if (preg_match('/^general/', $key)) {
                $generalShippingAddress = $value;
            }
        }
        $shipping = (isset($checkoutData['shipping_address_data'])) ? $checkoutData['shipping_address_data'] : null;
        $shipInfo = (isset($checkoutData['shipping_data'])) ? $checkoutData['shipping_data'] : array();
        $billing = (isset($checkoutData['billing_address_data'])) ? $checkoutData['billing_address_data'] : null;
        $payMethod = (isset($checkoutData['payment_data'])) ? $checkoutData['payment_data'] : null;
        if ($products) {
            foreach ($products as $data) {
                $this->productHelper()->frontOpenProduct($data['general_name']);
                $options = (isset($data['options'])) ? $data['options'] : array();
                $this->productHelper()->frontAddProductToCart($options);
            }
        }
        $this->clickControl('link', 'checkout_with_multiple_addresses');
        if ($customer) {
            $this->frontSelectMultipleCheckoutMethod($customer);
        }
        if ($generalShippingAddress) {
            $currentPage = $this->getCurrentPage();
            if ($currentPage == 'checkout_multishipping_add_new_address' ||
                $currentPage == 'checkout_multishipping_register'
            ) {
                $this->fillForm($generalShippingAddress);
                if ($customer['checkout_method'] == 'register') {
                    $this->clickButton('submit');
                } else {
                    $this->clickButton('save_address');
                }
            }
        }
        if ($shipping) {
            $this->assertMultipleAddrCheckoutPageOpened('select_addresses');
            foreach ($shipping as $value) {
                $type = 'new';
                if (isset($value['general_name'])) {
                    $this->addParameter('productName', $value['general_name']);
                    $type = 'exist';
                }
                if (isset($value['shipping_address'])) {
                    $this->frontFillAddress($value['shipping_address'], $type);
                }
                if (isset($value['qty'])) {
                    $this->fillForm(array('qty' => $value['qty']));
                }
            }
            $this->clickButton('update_qty_and_addresses');
            $this->clickButton('continue_to_shipping_information');
        }
        if ($shipInfo) {
            $this->fillShippingInfo($shipInfo);
        }
        if ($billing) {
            $this->frontSelectBillingAddress($billing);
        }
        if ($payMethod) {
            $this->frontSelectPaymentMethod($payMethod);
        }
    }

    /**
     * Filling shipping information (changing shipping address, selecting shipment method, adding git message)
     *
     * @param array $shipInfo
     */
    public function fillShippingInfo(array $shipInfo)
    {
        $this->assertMultipleAddrCheckoutPageOpened('shipping_information');
        foreach ($shipInfo as $data) {
            foreach ($data as $key => $value) {
                if (preg_match('/^search/', $key)) {
                    $this->setAddressHeader($value);
                }
                if (preg_match('/^change/', $key)) {
                    $this->clickControl('link', 'change_shipping_address');
                    $this->addParameter('id', $this->defineIdFromUrl());
                    $this->fillForm($value);
                    $this->clickButton('save_address');
                }
                if (preg_match('/^shipping_method/', $key)) {
                    $this->frontSelectShippingMethod($value);
                }
                if (preg_match('/^gift/', $key)) {
                    $this->frontAddGiftMessage($value);
                }
            }
        }
        $this->clickButton('continue_to_billing_information');
    }

    /**
     * Sets the addressHeader parameter
     *
     * @param array $addressInfo
     */
    public function setAddressHeader(array $addressInfo)
    {
        $formXpathString = '';
        foreach ($addressInfo as $value) {
            $formXpathString .= "[contains(.,'" . $value . "')]";
        }
        $this->addParameter('addressParams', $formXpathString);
        $xpath = $this->_getControlXpath('pageelement', 'address_box_ship');
        $this->addParameter('addressHeader', $this->getText($xpath));
    }

    /**
     * Adding gift message for each item
     *
     * @param array $giftOptions
     *
     */
    public function frontAddGiftMessage(array $giftOptions)
    {
        if (isset($giftOptions['individual_items'])) {
            $this->fillForm(array('add_gift_options'                 => 'Yes',
                                  'gift_option_for_individual_items' => 'Yes'));
            foreach ($giftOptions['individual_items'] as $key => $data) {
                $this->addParameter('productName', $key);
                $this->fillForm($data);
            }
        }
        if (isset($giftOptions['entire_order'])) {
            $this->fillForm(array('add_gift_options'                 => 'Yes',
                                  'gift_option_for_the_entire_order' => 'Yes'));
            $this->fillForm($giftOptions['entire_order']);
        }
    }

    /**
     * Selects/Edit/Add new billing address
     *
     * @param array $billing
     *
     * @return void
     */
    public function frontSelectBillingAddress(array $billing)
    {
        $this->assertMultipleAddrCheckoutPageOpened('billing_information');
        $this->clickControl('link', 'change_billing_address');
        foreach ($billing as $key => $value) {
            if (preg_match('/^exist/', $key)) {
                $formXpathString = '';
                foreach ($value as $v) {
                    $formXpathString .= "[contains(.,'" . $v . "')]";
                    $this->addParameter('addressParams', $formXpathString);
                    if (is_array($v)) {
                        $this->clickControl('link', 'edit_address');
                        $this->addParameter('id', $this->defineIdFromUrl());
                        $this->fillForm($v);
                        $this->clickButton('save_address');
                    }
                }
            }
            if (preg_match('/^new/', $key)) {
                $this->clickButton('add_new_address');
                $this->fillForm($value);
                $this->saveForm('save_address');
            }
            if (preg_match('/^select/', $key)) {
                $formXpathString = '';
                foreach ($value as $v) {
                    $formXpathString .= "[contains(.,'" . $v . "')]";
                }
                $this->addParameter('addressParams', $formXpathString);
                $this->clickControl('link', 'select_address');
            }
        }
    }

    /**
     * Select Checkout Method(Multiple Addresses Checkout)
     *
     * @param array $method register|login
     */
    public function frontSelectMultipleCheckoutMethod(array $method)
    {
        $checkoutType = (isset($method['checkout_method'])) ? $method['checkout_method'] : '';
        switch ($checkoutType) {
            case 'register':
                $this->clickButton('create_account');
                break;
            case 'login':
                if (isset($method['additional_data'])) {
                    $this->fillForm($method['additional_data']);
                }
                $this->clickButton('login');
                break;
            default:
                break;
        }
    }

    /**
     * Fills address on frontend
     *
     * @param array $addressData
     * @param string $addressChoice 'new' or 'exist'
     */
    public function frontFillAddress(array $addressData, $addressChoice)
    {
        switch ($addressChoice) {
            case 'new':
                $this->clickButton('add_new_address');
                $this->fillForm($addressData);
                $this->saveForm('save_address');
                break;
            case 'exist':
                $addressLine = $this->orderHelper()->defineAddressToChoose($addressData, 'shipping');
                $this->fillForm(array('shipping_address_choice' => $addressLine));
                break;
            default:
                $this->fail('Incorrect address type');
                break;
        }
    }

    /**
     * The way to ship the order
     *
     * @param array $shipMethod
     *
     */
    public function frontSelectShippingMethod(array $shipMethod)
    {
        $this->assertMultipleAddrCheckoutPageOpened('shipping_information');

        $service = (isset($shipMethod['shipping_service'])) ? $shipMethod['shipping_service'] : null;
        $method = (isset($shipMethod['shipping_method'])) ? $shipMethod['shipping_method'] : null;

        if (!$service or !$method) {
            $this->addVerificationMessage('Shipping Service(or Shipping Method) is not set');
        } else {
            $this->addParameter('shipService', $service);
            $this->addParameter('shipMethod', $method);
            $methodUnavailable = $this->_getControlXpath('message', 'ship_method_unavailable');
            $noShipping = $this->_getControlXpath('message', 'no_shipping');
            if ($this->isElementPresent($methodUnavailable) || $this->isElementPresent($noShipping)) {
                $this->addVerificationMessage('No Shipping Method is available for this order');
            } elseif ($this->isElementPresent($this->_getControlXpath('field', 'ship_service_name'))) {
                $methodXpath = $this->_getControlXpath('radiobutton', 'ship_method');
                $selectedMethod = $this->_getControlXpath('radiobutton', 'one_method_selected');
                if ($this->isElementPresent($methodXpath)) {
                    $this->click($methodXpath);
                    $this->waitForAjax();
                } elseif (!$this->isElementPresent($selectedMethod)) {
                    $this->addVerificationMessage('Shipping Method "' . $method . '" for "'
                                                      . $service . '" is currently unavailable');
                }
            } else {
                $this->addVerificationMessage('Shipping Service "' . $service . '" is currently unavailable.');
            }
        }
        $this->assertEmptyVerificationErrors();
    }

    /**
     * Selecting payment method
     *
     * @param array $paymentMethod
     *
     */
    public function frontSelectPaymentMethod(array $paymentMethod)
    {
        $this->assertMultipleAddrCheckoutPageOpened('billing_information');

        $payment = (isset($paymentMethod['payment_method'])) ? $paymentMethod['payment_method'] : null;
        $card = (isset($paymentMethod['payment_info'])) ? $paymentMethod['payment_info'] : null;
        if ($payment) {
            $this->addParameter('paymentTitle', $payment);
            $xpath = $this->_getControlXpath('radiobutton', 'check_payment_method');
            $selectedPayment = $this->_getControlXpath('radiobutton', 'selected_one_payment');
            if ($this->isElementPresent($xpath)) {
                $this->click($xpath);
            } elseif (!$this->isElementPresent($selectedPayment)) {
                $this->fail('Payment Method "' . $payment . '" is currently unavailable.');
            }
            if ($card) {
                $paymentId = $this->getAttribute($xpath . '/@value');
                $this->addParameter('paymentId', $paymentId);
                $this->fillForm($card);
            }
        }
        $this->clickButton('continue_to_review_order', false);
        try {
            $this->waitForPageToLoad($this->_browserTimeoutPeriod);
            $this->validatePage();
        } catch (Exception $e) {
        }

    }

    /**
     * Order review
     *
     * @param array  $checkoutData
     *
     */
    public function frontOrderReview(array $checkoutData)
    {
        $this->assertMultipleAddrCheckoutPageOpened('place_order');
        $this->checkoutOnePageHelper()->frontValidate3dSecure();

        $checkoutData = $this->arrayEmptyClear($checkoutData);
        if (isset($checkoutData['verify_products_data'])) {
            foreach ($checkoutData['verify_products_data'] as $data) {
                $addressToSearch = (isset($data['search_shipping_address'])) ? $data['search_shipping_address'] : null;
                $checkProd  = (isset($data['validate_prod_data'])) ? $data['validate_prod_data'] : null;
                $checkTotal = (isset($data['validate_total_data'])) ? $data['validate_total_data'] : null;
                if ($addressToSearch) {
                    $formXpathString = '';
                    foreach ($addressToSearch as $v) {
                        $formXpathString .= "[contains(.,'" . $v . "')]";
                        $this->addParameter('addressParams', $formXpathString);
                        $xpath = $this->_getControlXpath('pageelement', 'address_box');
                        $this->addParameter('addressHeader', $this->getText($xpath));
                    }
                }
                if ($checkProd && $checkTotal) {
                    $this->shoppingCartHelper()->verifyPricesDataOnPage($checkProd, $checkTotal);
                }
            }
        }
    }
}