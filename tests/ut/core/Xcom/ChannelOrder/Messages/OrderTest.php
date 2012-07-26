<?php

/**
 * Test for message order/created
 */

class Xcom_ChannelOrder_Model_Message_OrderTest
    extends Xcom_TestCase
{
    /**
     * @var Xcom_ChannelOrder_Model_Message_Order
     */
    protected $_object;

    protected $_instanceOf = 'Xcom_ChannelOrder_Model_Message_Order';

    public function setUp()
    {
        $this->_checkConnection = true;
        parent::setUp();
        $this->_object = Mage::getModel('xcom_channelorder/message_order');
    }

    /**
     * @expectedException Xcom_ChannelOrder_Exception
     */
    public function testCreateOrderWithoutMessageData()
    {
        $objectMock = $this->getMock(get_class($this->_object), array('getChannelOrder'));
        $objectMock->expects($this->never())
            ->method('getChannelOrder');
        $this->assertInstanceOf(get_class($this->_object), $objectMock->createOrder(array()));
    }

    /**
     * Try to create order which was already existed
     * @expectedException Xcom_ChannelOrder_Exception
     */
    public function testCreateOrderExisted()
    {
        $data   = array('orderNumber'   => rand());
        $objectMock = $this->getMock(get_class($this->_object), array('getChannelOrder'));
        $objectMock->expects($this->once())
            ->method('getChannelOrder')
            ->will($this->returnValue(new Varien_Object(array('order_id'=>rand()))));

        $objectMock->setOrderMessageData($data)->createOrder($data);
    }

    /**
     * Try to create order which was already existed
     * @expectedException Xcom_ChannelOrder_Exception
     */
    public function testCreateOrderWithoutGrandTotal()
    {
        $data   = array('orderNumber'   => rand());
        $objectMock = $this->getMock(get_class($this->_object), array('getChannelOrder', 'getQuote',
            'addItemsToQuote'));
        $objectMock->expects($this->once())
            ->method('getChannelOrder')
            ->will($this->returnValue(new Varien_Object(array())));

        $objectMock->expects($this->never())
            ->method('getQuote');
        $objectMock->expects($this->never())
            ->method('addItemsToQuote');

        $objectMock->setOrderMessageData($data)->createOrder($data);
    }

    public function testCreateOrder()
    {
        $data   = array('orderNumber'   => rand());
        $objectMock = $this->getMock(get_class($this->_object), array('getChannelOrder', 'prepareQuote',
            'getQuote', 'saveOrder', 'setFlatOrderId', 'saveChannelOrderSpecific',
            'saveChannelOrderItems', 'prepareShippingInfo', 'saveChannelPaymentSpecific', 'createInvoice'));

        $quoteMock = $this->mockModel('adminhtml/session_quote', array('collectTotals','save'));

        $objectMock->expects($this->once())
            ->method('getChannelOrder')
            ->will($this->returnValue(new Varien_Object(array())));

        $objectMock->expects($this->once())
            ->method('prepareQuote')
            ->will($this->returnValue($objectMock));

        $objectMock->expects($this->once())
            ->method('getQuote')
            ->will($this->returnValue($quoteMock));

        $objectMock->expects($this->once())
            ->method('prepareShippingInfo')
            ->will($this->returnValue($objectMock));

        $objectMock->expects($this->once())
            ->method('createInvoice');

        $quoteMock->expects($this->once())
            ->method('collectTotals')
            ->will($this->returnValue($quoteMock));
        $quoteMock->expects($this->once())
            ->method('save')
            ->will($this->returnValue($quoteMock));

        $orderId    = 'order_entity_id';
        $objectMock->expects($this->once())
            ->method('saveOrder')
            ->will($this->returnValue(new Varien_Object(array('entity_id' => $orderId))));

        $objectMock->expects($this->once())
            ->method('setFlatOrderId')
            ->will($this->equalTo($orderId))
            ->will($this->returnValue($objectMock));
        $objectMock->expects($this->once())
            ->method('saveChannelOrderSpecific')
            ->will($this->returnValue($objectMock));
        $objectMock->expects($this->once())
            ->method('saveChannelOrderItems')
            ->will($this->returnValue($objectMock));
        $objectMock->expects($this->once())
            ->method('saveChannelPaymentSpecific')
            ->will($this->returnValue($objectMock));


        $objectMock->setOrderMessageData($data);//->createOrder($data);
        $this->assertInstanceOf(get_class($this->_object), $objectMock->createOrder($data));
    }

    public function testGetQuote()
    {
        $quoteMock = $this->mockModel('adminhtml/session_quote', array('getQuote'));
        $quoteMock->expects($this->once())
            ->method('getQuote')
            ->will($this->returnValue(true));
        $this->assertTrue($this->_object->getQuote());
    }


    public function testGetOrderChannelIdWithoutOrderData()
    {
        $result = $this->_object->getOrderChannelId();
        $this->assertEquals(0, $result);
    }

    /**
     * @expectedException Xcom_ChannelOrder_Exception
     */
    public function testGetOrderChannelIdException()
    {
        $this->mockHelper('xcom_channelorder');
        $objectMock = $this->getMock($this->_instanceOf, array('getOrderData'));
        $objectMock->expects($this->exactly(2))
            ->method('getOrderData')
            ->will($this->returnValue(array(
                'items' => array(
                    'sku_1' => array('channel_id' => 10),
                    'sku_2' => array('channel_id' => 9)
                )
            )));
        $objectMock->getOrderChannelId();
    }

    public function testGetOrderChannelId()
    {
        $this->mockHelper('xcom_channelorder');
        $objectMock = $this->getMock($this->_instanceOf, array('getOrderData'));
        $objectMock->expects($this->exactly(2))
            ->method('getOrderData')
            ->will($this->returnValue(array(
            'items' => array(
                'sku_1' => array('channel_id' => 10),
                'sku_2' => array('channel_id' => 10)
            )
        )));
        $result = $objectMock->getOrderChannelId();
        $this->assertEquals(10, $result);
    }


    /**
     * @expectedException Xcom_ChannelOrder_Exception
     */
    public function testPrepareQuoteEmptyData()
    {
        $this->_object->prepareQuote( array());
    }

    public function testPrepareQuote()
    {
        $storeId    = rand();
        $data = array(
                'grandTotal' => array(
                    'amount' => 'test_amount',
                    'code' => 'test_code'
                )
        );
        $quoteMock = $this->mockModel('adminhtml/session_quote',
            array('setGrandTotal', 'setBaseCurrencyCode', 'reserveOrderId', 'setStoreId'));
        $quoteMock->expects($this->once())
            ->method('setGrandTotal')
            ->with($this->equalTo($data['grandTotal']['amount']))
            ->will($this->returnValue($quoteMock));
        $quoteMock->expects($this->once())
            ->method('setBaseCurrencyCode')
            ->with($this->equalTo($data['grandTotal']['code']))
            ->will($this->returnValue($quoteMock));
        $quoteMock->expects($this->once())
            ->method('reserveOrderId')
            ->will($this->returnValue($quoteMock));
        $quoteMock->expects($this->once())
            ->method('setStoreId')
            ->with($this->equalTo($storeId))
            ->will($this->returnValue($quoteMock));

        $objectMock = $this->getMock($this->_instanceOf,
            array('addCustomerToQuote', 'addBillingAddressToQuote',
                'addShippingAddressToQuote', 'addItemsToQuote', 'getQuote', '_getStoreId'));
        $objectMock->expects($this->once())
            ->method('addCustomerToQuote')
            ->will($this->returnValue($objectMock));
        $objectMock->expects($this->once())
            ->method('addBillingAddressToQuote')
            ->will($this->returnValue($objectMock));
        $objectMock->expects($this->once())
            ->method('addShippingAddressToQuote')
            ->will($this->returnValue($objectMock));
        $objectMock->expects($this->once())
            ->method('addItemsToQuote')
            ->will($this->returnValue($objectMock));
        $objectMock->expects($this->once())
            ->method('getQuote')
            ->will($this->returnValue($quoteMock));
        $objectMock->expects($this->once())
            ->method('_getStoreId')
            ->will($this->returnValue($storeId));

        $this->assertInstanceOf($this->_instanceOf, $objectMock->prepareQuote($data));
    }

    /**
     * @expectedException Xcom_ChannelOrder_Exception
     */
    public function testAddCustomerToQuoteEmpty()
    {
        $result = $this->_object->addCustomerToQuote(array());
        $this->assertInstanceOf($this->_instanceOf, $result);
    }

    public function testAddCustomerToQuote()
    {
        $data = array(
            'customer' => array(
                'email' => array(
                    'emailAddress' => 'test_email',
                ),
                'name' => array(
                    'firstName' => 'test_name',
                    'middleName' => 'test_middle',
                    'lastname'   => 'test_lastname',
                    'prefix'    => 'test_prefix',
                    'suffix'    => 'test_suffix'
                )
            )
        );

        $customerData = array(
            'email' => $data['customer']['email']['emailAddress'],
            'firstname' => $data['customer']['name']['firstName'],
            'middlename' => $data['customer']['name']['middleName'],
            'lastname' => $data['customer']['name']['lastName'],
            'prefix' => $data['customer']['name']['prefix'],
            'suffix' => $data['customer']['name']['suffix'],
        );
        $customerMock = $this->mockModel('customer/customer', array('addData'));
        $customerMock->expects($this->once())
            ->method('addData')
            ->with($this->equalTo($customerData))
            ->will($this->returnValue($customerData));

        $quoteMock = $this->mockModel('sales/quote', array('setCustomer'));
        $quoteMock->expects($this->once())
            ->method('setCustomer')
            ->will($this->returnValue($quoteMock));
        $sessionQuoteMock = $this->mockModel('adminhtml/session_quote', array('getQuote'));
        $sessionQuoteMock->expects($this->once())
            ->method('getQuote')
            ->will($this->returnValue($quoteMock));


        $this->assertInstanceOf($this->_instanceOf, $this->_object->addCustomerToQuote($data));
    }

    /**
     * @expectedException Xcom_ChannelOrder_Exception
     */
    public function testAddBillingAddressToQuoteEmpty()
    {
        $result = $this->_object->addBillingAddressToQuote(array());
        $this->assertInstanceOf($this->_instanceOf, $result);
    }

    public function testAddBillingAddressToQuote()
    {
        $data = array(
            'customer' => array(
                'email' => array(
                    'emailAddress' => 'test_email',
                ),
                'name' => array(
                    'firstName' => 'test_name',
                    'middleName' => 'test_middle',
                    'lastname'   => 'test_lastname',
                    'prefix'    => 'test_prefix',
                    'suffix'    => 'test_suffix'
                ),
                'phone' => array('number' => 'test_phone')
            ),
            'billingAddress' => array(
                'street1' => 'test_street_1',
                'street2' => 'test_street_2',
                'street3' => 'test_street_3',
                'street4' => 'test_street_4',
                'city' => 'test_city',
                'region' => 'test_stateOfProvince',
                'postcode' => 'test_postalCode',
                'telephone' => 'test_phone',
            )
        );

        $billingData = array(
            'address_id' => null,
            'region_id' => null,
            'country_id'    => 'US',
            'firstname' => $data['customer']['name']['firstName'],
            'lastname' => $data['customer']['name']['lastname'],
            'lastname' => $data['customer']['name']['middleName']
                . ' ' . $data['customer']['name']['lastName'],
            'email' => $data['customer']['email']['emailAddress'],
            'street' => array($data['billingAddress']['street1'] .
                    ' ' . $data['billingAddress']['street2'],
                $data['billingAddress']['street3'] .
                    ' ' . $data['billingAddress']['street4'],
            ),
            'city'  => $data['billingAddress']['city'],
            'region'  => $data['billingAddress']['stateOrProvince'],
            'postcode'  => $data['billingAddress']['postalCode'],
            'telephone'  => $data['customer']['phone']['number'],
        );
        $billingAddressMock = $this->mockModel('sales/quote_address',
            array('setData', 'implodeStreetAddress'));
        $billingAddressMock->expects($this->once())
            ->method('setData')
            ->with($this->equalTo($billingData))
            ->will($this->returnValue($billingAddressMock));
        $billingAddressMock->expects($this->once())
            ->method('implodeStreetAddress')
            ->will($this->returnValue($billingAddressMock));

        $objectMock = $this->getMock($this->_instanceOf, array('getQuote'));
        $objectMock->expects($this->once())
            ->method('getQuote')
            ->will($this->returnValue(new Varien_Object()));

        $result = $objectMock->addBillingAddressToQuote($data);
        $this->assertInstanceOf($this->_instanceOf, $result);
    }

    /**
     * @expectedException Xcom_ChannelOrder_Exception
     */
    public function testAddShippingAddressToQuoteEmpty()
    {
        $result = $this->_object->addShippingAddressToQuote(array());
        $this->assertInstanceOf($this->_instanceOf, $result);
    }

    public function testAddShippingAddressToQuote()
    {
        $data = array(
            'destination' => array(
                'name' => array(
                    'firstName' => 'test_name',
                    'middleName' => 'test_middle',
                    'lastname'   => 'test_lastname',
                    'prefix'    => 'test_prefix',
                    'suffix'    => 'test_suffix'
                ),
                'address' => array(
                    'street1' => 'test_street_1',
                    'street2' => 'test_street_2',
                    'street3' => 'test_street_3',
                    'street4' => 'test_street_4',
                    'city' => 'test_city',
                    'stateOrProvince' => 'test_stateOfProvince',
                    'postalCode' => 'test_postalCode'
                )
            ),
            'customer' => array(
                'phone' => array('number' => 'test_phone'),
            ),
            'grandTotal'    => array(
                'amount'    => 10.95
            )
        );

        $shippingData = array(
            'address_id' => null,
            'region_id' => null,
            'country_id'    => 'US',
            'firstname' => $data['destination']['name']['firstName'],
            'lastname' => $data['destination']['name']['lastname'],
            'lastname' => $data['destination']['name']['middleName']
                . ' ' . $data['destination']['name']['lastName'],
            'street' => array($data['destination']['address']['street1'] .
                ' ' . $data['destination']['address']['street2'],
                $data['destination']['address']['street3'] .
                    ' ' . $data['destination']['address']['street4'],
            ),
            'city'  => $data['destination']['address']['city'],
            'region'  => $data['destination']['address']['stateOrProvince'],
            'postcode'  => $data['destination']['address']['postalCode'],
            'telephone'  => $data['customer']['phone']['number'],
        );
        $shippingAddressMock = $this->mockModel('sales/quote_address',
            array('setData', 'implodeStreetAddress'));
        $shippingAddressMock->expects($this->once())
            ->method('setData')
            ->with($this->equalTo($shippingData))
            ->will($this->returnValue($shippingAddressMock));
        $shippingAddressMock->expects($this->once())
            ->method('implodeStreetAddress')
            ->will($this->returnValue($shippingAddressMock));

        $objectMock = $this->getMock($this->_instanceOf, array('getQuote'));
        $objectMock->expects($this->once())
            ->method('getQuote')
            ->will($this->returnValue(new Varien_Object()));

        $result = $objectMock->addShippingAddressToQuote($data);
        $this->assertInstanceOf($this->_instanceOf, $result);
    }

    /**
     * Test saving order
     *
     * @return void
     */
    public function testSaveOrder()
    {
        //mock payment model
        $paymentMock = $this->getMock('stdClass', array('setMethod'));
        $paymentMock->expects($this->once())
            ->method('setMethod')
            ->with($this->equalTo('free'))
            ->will($this->returnValue($paymentMock));

        //mock data for quote model
        $cnt = 0;
        $quoteMockData = array(
            'getReservedOrderId'    => '681681',
            'getEntityId'           => '1354',
            'getBillingAddress'     => 'billing address test',
            'getShippingAddress'    => 'shipping address test',
            'getGrandTotal'         => '3851',
            'getBaseGrandTotal'     => '3214',
            'getBaseCurrencyCode'   => 'code_base',
            'getPayment'            => $paymentMock,
            'getAllItems'           => array('quote_item'),
        );

        $quoteMock = $this->getMock('stdClass', array_keys($quoteMockData));
        foreach ($quoteMockData as $method => $returnValue) {
            $matcher = $method == 'getBaseCurrencyCode' ? $this->exactly(4) : $this->atLeastOnce();
            $quoteMock->expects($matcher)
                ->method($method)
                ->will($this->returnValue($returnValue));
        }

        //mock converter model
        $converterMock = $this->mockModel('sales/convert_quote', array(
            'addressToOrderAddress',
            'paymentToOrderPayment',
            'itemToOrderItem',
        ));
        $converterMock->expects($this->at(0))
            ->method('addressToOrderAddress')
            ->with($this->equalTo($quoteMockData['getBillingAddress']))
            ->will($this->returnValue($quoteMockData['getBillingAddress']));
        $converterMock->expects($this->at(1))
            ->method('addressToOrderAddress')
            ->with($this->equalTo($quoteMockData['getShippingAddress']))
            ->will($this->returnValue($quoteMockData['getShippingAddress']));
        $paymentOrder = 'payment_free';
        $converterMock->expects($this->once())
            ->method('paymentToOrderPayment')
            ->with($this->equalTo($paymentMock))
            ->will($this->returnValue($paymentOrder));
        $converterMock->expects($this->once())
            ->method('itemToOrderItem')
            ->with($this->equalTo($quoteMockData['getAllItems'][0]))
            ->will($this->returnValue($quoteMockData['getAllItems'][0]));

        $shippingFeesAmount = 11.99;
        $storeId            = rand();
        //mock order model
        $orderMockData = array(
            'setIncrementId'        => $quoteMockData['getReservedOrderId'],
            'setStoreId'            => $storeId,
            'setCustomerIsGuest'    => true,
            'setCustomerGroupId'    => Mage_Customer_Model_Group::NOT_LOGGED_IN_ID,
            'setState'              => Mage_Sales_Model_Order::STATE_NEW,
            'setStatus'             => 'pending',
            'setQuoteId'            => $quoteMockData['getEntityId'],
            'setBillingAddress'     => $quoteMockData['getBillingAddress'],
            'setShippingAddress'    => $quoteMockData['getShippingAddress'],
            'setPayment'            => $paymentOrder,
            'addItem'               => $quoteMockData['getAllItems'][0],
            'setGrandTotal'         => $quoteMockData['getGrandTotal'] + $shippingFeesAmount,
            'setBaseGrandTotal'     => $quoteMockData['getBaseGrandTotal'] + $shippingFeesAmount,
            'setBaseCurrencyCode'   => $quoteMockData['getBaseCurrencyCode'],
            'setGlobalCurrencyCode' => $quoteMockData['getBaseCurrencyCode'],
            'setStoreCurrencyCode'  => $quoteMockData['getBaseCurrencyCode'],
            'setOrderCurrencyCode'  => $quoteMockData['getBaseCurrencyCode'],
            'setSubtotal'           => $quoteMockData['getBaseGrandTotal'],
            'setSubtotalInclTax'    => $quoteMockData['getBaseGrandTotal'],
            'setBaseSubtotal'       => $quoteMockData['getBaseGrandTotal'],
            'setBaseSubtotalInclTax'=> $quoteMockData['getBaseGrandTotal'],
            'setShippingAmount'     => $shippingFeesAmount,
            'setBaseShippingAmount' => $shippingFeesAmount,
            'setQuote'              => $quoteMock,
            'setQuote'              => $quoteMock,
            'save'                  => null
        );
        $orderMock = $this->mockModel('xcom_channelorder/order_save', array_keys($orderMockData));
        $orderMock->expects($this->once())
                  ->method('save');
        unset($orderMockData['save']);

        foreach ($orderMockData as $method => $equalTo) {
            $orderMock->expects($this->once())
                ->method($method)
                ->with($this->equalTo($equalTo))
                ->will($this->returnValue($orderMock));
        }
        //mock transaction model
//        $transactionMock = $this->mockModel('core/resource_transaction', array(
//            'addObject',
//            'addCommitCallback',
//            'save',
//        ));
//        $transactionMock->expects($this->once())
//            ->method('addObject')
//            ->with($this->equalTo($orderMock));
//        $transactionMock->expects($this->once())
//            ->method('addCommitCallback')
//            ->with($this->equalTo(array($orderMock, 'save')));
//        $transactionMock->expects($this->once())
//            ->method('save');


        //mock core helper
        $helperMock = $this->mockHelper('core', array('copyFieldset'));
        $helperMock->expects($this->once())
                ->method('copyFieldset')
                ->with(
                    $this->equalTo('customer_account'),
                    $this->equalTo('to_quote'),
                    $this->isNull(),
                    $this->isInstanceOf('Mage_Sales_Model_Order')
                    )
                ->will($this->returnValue(true));

        //mock main object
        $objectMock = $this->mockModel('xcom_channelorder/message_order', array('getQuote', 'save',
            'getShippingFees', '_getStoreId'));
        $objectMock->expects($this->once())
                ->method('getQuote')
                ->will($this->returnValue($quoteMock));
        $objectMock->expects($this->exactly(4))
            ->method('getShippingFees')
            ->will($this->returnValue($shippingFeesAmount));
        $objectMock->expects($this->once())
            ->method('_getStoreId')
            ->will($this->returnValue($storeId));

        //mock observers
        $this->mockStoreConfig('config/global/events', array());
        //add data params
        $data = array(
            'dateOrdered' => '2012-03-27T10:44:44+01:00'
        );
        $expect = '2012-03-27 09:44:44';
        //call tested method
        $objectMock->saveOrder($data);

        //check if we get expected values for date
        $this->assertEquals($expect,$orderMock->getCreatedAt());
    }

    /**
     * Test isOrderCanUpdateOrder when order has no protected status and channel order can updated
     *
     * @return void
     */
    public function testIsOrderCanUpdateSuccess()
    {
        $orderNumber = '123456';
        $this->_object->setOrderMessageData(array('orderNumber' => $orderNumber));
        $order = Mage::getModel('sales/order');
        $order->setId(1);
        $order->setData('status', 'some_status');

        $channelOrder = $this->getMock('Xcom_ChannelOrder_Model_Order', array('getOrder'));
        $channelOrder->expects($this->once())
            ->method('getOrder')
            ->will($this->returnValue($order));
        $channelOrder->setId(1);
        $this->assertTrue($this->_object->isOrderCanUpdate($channelOrder));
    }

    /**
     * Test isOrderCanUpdateOrder when order has protected status and channel order cannot updated
     *
     * @return void
     */
    public function testIsOrderCanNotUpdate()
    {
        $orderNumber = '123456';
        $this->_object->setOrderMessageData(array('orderNumber' => $orderNumber));
        $order = Mage::getModel('sales/order');
        $order->setId(1);

        $protectedStatuses = array(
            Mage_Sales_Model_Order::STATE_COMPLETE,
            Mage_Sales_Model_Order::STATE_CLOSED,
            Mage_Sales_Model_Order::STATE_CANCELED,
        );

        foreach ($protectedStatuses as $status) {
            $order->setData('status', $status);
            $channelOrder = $this->getMock('Xcom_ChannelOrder_Model_Order', array('getOrder'));
            $channelOrder->expects($this->once())
                ->method('getOrder')
                ->will($this->returnValue($order));
            $channelOrder->setId(1);
            try {
                $this->_object->isOrderCanUpdate($channelOrder);
            } catch (Xcom_ChannelOrder_Exception $e) {
                $this->assertEquals(
                    'Xcom_ChannelOrder_Exception',
                    get_class($e),
                    'Expected exception "Xcom_ChannelOrder_Exception".');
            }
            $this->assertTrue(isset($e), 'Expected throwing exception.');
            unset($e);
        }
    }

    /**
     * Test isOrderCanUpdateOrder when channel order not initialized and channel order cannot updated
     *
     * @return void
     */
    public function testIsOrderCanUpdateOrderChannelNotFound()
    {
        $channelOrder = $this->getMock('Xcom_ChannelOrder_Model_Order', array('getOrder'));
        $channelOrder->expects($this->never())
            ->method('getOrder');

        try {
            $this->_object->isOrderCanUpdate($channelOrder);
        } catch (Exception $e) {
            $this->assertEquals('Exception', get_class($e), 'Expected default base exception class.');
        }
        $this->assertTrue(isset($e), 'Expected throwing exception.');
    }

    /**
     * Test isOrderCanUpdateOrder when order not initialized and channel order cannot updated
     *
     * @return void
     */
    public function testIsOrderCanUpdateOrderNotFound()
    {
        $channelOrder = $this->getMock('Xcom_ChannelOrder_Model_Order');
        try {
            $this->_object->isOrderCanUpdate($channelOrder);
        } catch (Exception $e) {
            $this->assertEquals('Exception', get_class($e), 'Expected default base exception class.');
        }
        $this->assertTrue(isset($e), 'Expected throwing exception.');
    }

    public function testCreateInvoiceValidateFalse()
    {
        $channelOrderMock = $this->mockModel('xcom_channelorder/message_order',
            array('validateInvoice'));
        $channelOrderMock->expects($this->once())
                         ->method('validateInvoice')
                         ->will($this->returnValue(false));
        $result = $channelOrderMock->createInvoice();
        $this->assertFalse($result);
    }

    /**
     * @expectedExceptionMessage Error
     */
    public function testCreateInvoiceMageException()
    {
        $channelOrderMock = $this->mockModel('xcom_channelorder/message_order',
            array('validateInvoice', 'getChannelOrder'));
        $channelOrderMock->expects($this->once())
                         ->method('validateInvoice')
                         ->will($this->returnValue(true));
        $orderNumber = rand(1, 1000);

        $channelOrderModelMock = new Varien_Object(array('order_id' => $orderNumber));

        $this->_setChannelOrder($channelOrderMock, $channelOrderModelMock);

        $orderMock = $this->mockModel('sales/order', array('load'));
        $orderMock->expects($this->once())
                  ->method('load')
                  ->with($this->equalTo($orderNumber))
                  ->will($this->returnSelf());

        $invoiceMock = $this->mockModel('sales/service_order', array(
            'prepareInvoice', 'getTotalQty'));
        $invoiceMock->expects($this->once())
                         ->method('prepareInvoice')
                         ->will($this->returnSelf());
        $invoiceMock->expects($this->once())
                         ->method('getTotalQty')
                         ->will($this->returnValue(false));

        $helperMock = $this->mockHelper('core', array('__'));
        $helperMock->expects($this->once())
                   ->method('__')
                   ->will($this->returnValue('Error'));

        $result = $channelOrderMock->createInvoice();
        $this->assertFalse($result);
    }

    public function testCreateInvoiceException()
    {
        $channelOrderMock = $this->mockModel('xcom_channelorder/message_order',
            array('validateInvoice', 'getChannelOrder'));
        $channelOrderMock->expects($this->once())
                         ->method('validateInvoice')
                         ->will($this->returnValue(true));

        $channelOrderMock->expects($this->once())
                         ->method('getChannelOrder')
                         ->will($this->throwException(new Exception()));

        $result = $channelOrderMock->createInvoice();
        $this->assertFalse($result);
    }

    public function testCreateInvoice1()
    {
        $channelOrderMock = $this->mockModel('xcom_channelorder/message_order',
           array('validateInvoice', 'getChannelOrder'));
        $channelOrderMock->expects($this->once())
                         ->method('validateInvoice')
                         ->will($this->returnValue(true));
        $orderNumber = rand(0, 1000);

        $channelOrderModelMock = new Varien_Object(array('order_id' => $orderNumber));

        $this->_setChannelOrder($channelOrderMock, $channelOrderModelMock);

        $orderMock = $this->mockModel('sales/order', array('load'));
        $orderMock->expects($this->once())
                  ->method('load')
                  ->with($this->equalTo($orderNumber))
                  ->will($this->returnSelf());

        $invoiceMock = $this->mockModel('sales/service_order', array(
             'prepareInvoice', 'getTotalQty', 'setRequestedCaptureCase', 'register', 'getOrder'));
        $invoiceMock->expects($this->once())
                    ->method('prepareInvoice')
                    ->will($this->returnSelf());
        $invoiceMock->expects($this->once())
                    ->method('getTotalQty')
                    ->will($this->returnValue(true));
        $invoiceMock->expects($this->once())
                    ->method('setRequestedCaptureCase')
                    ->with($this->equalTo(Mage_Sales_Model_Order_Invoice::CAPTURE_OFFLINE))
                    ->will($this->returnSelf());
        $invoiceMock->expects($this->once())
                    ->method('register')
                    ->will($this->returnSelf());
        $invoiceMock->expects($this->once())
                    ->method('getOrder')
                    ->will($this->returnValue($orderMock));

        $transactionMock = $this->mockModel('core/resource_transaction',
            array('addObject', 'save'));
        $transactionMock->expects($this->at(0))
                        ->method('addObject')
                        ->with($this->equalTo($invoiceMock))
                        ->will($this->returnSelf());
        $transactionMock->expects($this->at(1))
                        ->method('addObject')
                        ->with($this->equalTo($orderMock))
                        ->will($this->returnSelf());
        $transactionMock->expects($this->once())
                        ->method('save')
                        ->will($this->returnSelf());
        $result = $channelOrderMock->createInvoice();
        $this->assertTrue($result);
    }

    public function testValidateInvoiceFalse1()
    {
        $channelOrderMock = $this->mockModel('xcom_channelorder/message_order',
           array('getOrderId', 'getChannelOrder'));
        $orderNumber = rand(0, 1000);

        $channelOrderModelMock = new Varien_Object(array('order_id' => false));

        $this->_setChannelOrder($channelOrderMock, $channelOrderModelMock);

        $result = $channelOrderMock->validateInvoice();
        $this->assertFalse($result);
    }

    public function testValidateInvoiceFalse2()
    {
        $channelOrderMock = $this->mockModel('xcom_channelorder/message_order',
           array('getOrderId', 'getChannelOrder'));
        $orderNumber = rand(0, 1000);

        $channelOrderModelMock = new Varien_Object(array('order_id' => $orderNumber));
        $this->_setChannelOrder($channelOrderMock, $channelOrderModelMock);

        $orderMock = $this->mockModel('sales/order', array('load', 'canInvoice'));
        $orderMock->expects($this->once())
                  ->method('load')
                  ->with($this->equalTo($orderNumber))
                  ->will($this->returnSelf());
        $orderMock->expects($this->once())
                  ->method('canInvoice')
                  ->will($this->returnValue(false));

        $result = $channelOrderMock->validateInvoice();
        $this->assertFalse($result);
    }

    public function testValidateInvoiceFalse3()
    {
        $channelOrderMock = $this->mockModel('xcom_channelorder/message_order',
           array('getOrderId', 'getChannelOrder'));
        $orderNumber = rand(0, 1000);

        $paymentStatus = 'None';

        $channelOrderModelMock = new Varien_Object(array(
            'order_id' => $orderNumber,
            'payment' => new Varien_Object(array('payment_status' => $paymentStatus))));

        $this->_setChannelOrder($channelOrderMock, $channelOrderModelMock);;

        $orderMock = $this->mockModel('sales/order', array('load', 'canInvoice'));
        $orderMock->expects($this->once())
                  ->method('load')
                  ->with($this->equalTo($orderNumber))
                  ->will($this->returnSelf());
        $orderMock->expects($this->once())
                  ->method('canInvoice')
                  ->will($this->returnValue(true));

        $result = $channelOrderMock->validateInvoice();
        $this->assertFalse($result);
    }


    public function testValidateInvoiceTrue()
    {
        $channelOrderMock = $this->mockModel('xcom_channelorder/message_order',
           array('getOrderId', 'getChannelOrder'));
        $orderNumber = rand(0, 1000);

        $paymentStatus = 'PAID';

        $channelOrderModelMock = new Varien_Object(array(
            'order_id' => $orderNumber,
            'payment' => new Varien_Object(array('payment_status' => $paymentStatus))));

        $this->_setChannelOrder($channelOrderMock, $channelOrderModelMock);

        $orderMock = $this->mockModel('sales/order', array('load', 'canInvoice'));
        $orderMock->expects($this->once())
                  ->method('load')
                  ->with($this->equalTo($orderNumber))
                  ->will($this->returnSelf());
        $orderMock->expects($this->once())
                  ->method('canInvoice')
                  ->will($this->returnValue(true));

        $result = $channelOrderMock->validateInvoice();
        $this->assertTrue($result);
    }

    public function testGetOrderNumber()
    {
        $this->_object->setOrderMessageData(array(
            'orderNumber' => null,
        ));
        $result = $this->_object->getOrderNumber();
        $this->assertNull($result);

        $orderNumber = rand(0, 100);
        $this->_object->setOrderMessageData(array(
            'orderNumber' => $orderNumber,
        ));
        $result = $this->_object->getOrderNumber();
        $this->assertEquals($orderNumber, $result);
    }

    /**
     * @expectedException Xcom_ChannelOrder_Exception
     */
    public function testAddItemsToQuoteNoOrderItems()
    {
        $orderNumber = rand(0, 100);
        $this->_object->setOrderMessageData(array(
            'orderNumber' => $orderNumber,
        ));
        $this->_object->addItemsToQuote(array());
    }

    /**
     * @expectedException Xcom_ChannelOrder_Exception
     */
    public function testAddItemsToQuoteEmptyOrderItems()
    {
        $orderNumber = rand(0, 100);
        $this->_object->setOrderMessageData(array(
            'orderNumber' => $orderNumber,
        ));
        $this->_object->addItemsToQuote(array('orderItems' => null));
    }

    /**
     * @expectedException Xcom_ChannelOrder_Exception
     */
    public function testAddItemsToQuoteNoProductSku()
    {
        $orderNumber = rand(0, 100);
        $this->_object->setOrderMessageData(array(
            'orderNumber' => $orderNumber,
        ));
        $data = array(
            'orderItems' => array(
                array(
                    'itemId' => 1
                ),
            )
        );
        $this->_object->addItemsToQuote($data);
    }

    /**
     * @expectedException Xcom_ChannelOrder_Exception
     */
    public function testAddItemsToQuoteNoItemId()
    {
        $orderNumber = rand(0, 100);
        $this->_object->setOrderMessageData(array(
            'orderNumber' => $orderNumber,
        ));
        $data = array(
            'orderItems' => array(
                array(
                    'productSku' => 'test_productSku'
                ),
            )
        );
        $this->_object->addItemsToQuote($data);
    }

    /**
     * @expectedException Xcom_ChannelOrder_Exception
     */
    public function testAddItemsToQuoteNoProductEntityId()
    {
        $orderNumber = rand(0, 100);
        $this->_object->setOrderMessageData(array(
            'orderNumber' => $orderNumber,
        ));
        $data = array(
            'orderItems' => array(
                array(
                    'itemId' => 1,
                    'productSku' => 'test_productSku'
                ),
            )
        );

        $helperMock = $this->mockHelper('xcom_mmp', array('getProductBySku'));
        $helperMock->expects($this->once())
            ->method('getProductBySku')
            ->with($this->equalTo('test_productSku'))
            ->will($this->returnValue(new Varien_Object()));

        $this->_object->addItemsToQuote($data);
    }

    public function testAddItemsToQuote()
    {
        $orderNumber = rand(0, 100);
        $this->_object->setOrderMessageData(array(
            'orderNumber' => $orderNumber,
        ));
        $data = array(
            'orderItems' => array(
                array(
                    'itemId' => 1,
                    'productSku' => 'test_productSku',
                    'quantity' => 1,
                    'price' => array('price' => array('amount' => 10)),
                ),
            )
        );

        $helperMock = $this->mockHelper('xcom_mmp', array('getProductBySku'));
        $helperMock->expects($this->once())
            ->method('getProductBySku')
            ->with($this->equalTo('test_productSku'))
            ->will($this->returnValue(new Varien_Object(array('entity_id' => 123))));

        $mockListingChannelProduct = $this->mockModel('xcom_listing/channel_product', array('load'));
        $mockListingChannelProduct->expects($this->once())
            ->method('load')
            ->with($this->equalTo(1), $this->equalTo('market_item_id'))
            ->will($this->returnValue(new Varien_Object(array('channel_id' => 456))));

        $result = $this->_object->addItemsToQuote($data);
        $this->assertInstanceOf('Xcom_ChannelOrder_Model_Message_Order', $result);

        $resultOrderData = $result->getOrderData();

        $this->assertArrayHasKey('items', $resultOrderData);
        $this->assertTrue(is_array($resultOrderData['items']));

        $this->assertArrayHasKey('test_productSku', $resultOrderData['items']);
        $this->assertTrue(is_array($resultOrderData['items']['test_productSku']));

        $this->assertArrayHasKey('item_id', $resultOrderData['items']['test_productSku']);
        $this->assertEquals(123, $resultOrderData['items']['test_productSku']['item_id']);

        $this->assertArrayHasKey('channel_id', $resultOrderData['items']['test_productSku']);
        $this->assertEquals(456, $resultOrderData['items']['test_productSku']['channel_id']);

        $resultAllQuoteItems = $result->getQuote()->getAllItems();
        $this->assertTrue(is_array($resultAllQuoteItems));
        $this->assertArrayHasKey(0, $resultAllQuoteItems);

        $resultQuoteItem = $resultAllQuoteItems[0];
        $this->assertInstanceOf('Mage_Sales_Model_Quote_Item', $resultQuoteItem);
        $this->assertEquals(1, $resultQuoteItem->getQty());
        $this->assertEquals(10, $resultQuoteItem->getPrice());

        $resultQuoteItemProduct = $resultQuoteItem->getProduct();
        $this->assertInstanceOf('Varien_Object', $resultQuoteItemProduct);
        $this->assertEquals(123, $resultQuoteItemProduct->getEntityId());
        $this->assertEquals(10, $resultQuoteItemProduct->getPrice());
    }

    /**
     * @expectedException Xcom_ChannelOrder_Exception
     */
    public function testAddItemsToQuoteNoInfoChannelId()
    {
        $orderNumber = rand(0, 100);
        $this->_object->setOrderMessageData(array(
            'orderNumber' => $orderNumber,
        ));
        $data = array(
            'orderItems' => array(
                array(
                    'itemId' => 1,
                    'productSku' => 'test_productSku',
                    'quantity' => 1,
                    'price' => array('price' => array('amount' => 10)),
                ),
            )
        );

        $helperMock = $this->mockHelper('xcom_mmp', array('getProductBySku'));
        $helperMock->expects($this->once())
            ->method('getProductBySku')
            ->with($this->equalTo('test_productSku'))
            ->will($this->returnValue(new Varien_Object(array('entity_id' => 123))));

        $mockListingChannelProduct = $this->mockModel('xcom_listing/channel_product', array('load'));
        $mockListingChannelProduct->expects($this->once())
            ->method('load')
            ->with($this->equalTo(1), $this->equalTo('market_item_id'))
            ->will($this->returnValue(new Varien_Object(array('channel_id' => null))));

        $this->_object->addItemsToQuote($data);
    }

    protected function _setChannelOrder($channelOrderMock, $willParam)
    {
        $channelOrderMock->expects($this->once())
                         ->method('getChannelOrder')
                         ->will($this->returnValue($willParam));
        return $this;
    }

    /**
     * @expectedException Xcom_ChannelOrder_Exception
     */
    public function testUpdateOrderNoOrderId()
    {
        $orderNumber = rand(0, 100);
        $this->_object->setOrderMessageData(array(
            'orderNumber' => $orderNumber,
        ));

        $channelOrderMock = $this->mockModel('xcom_channelorder/order', array('load'));
        $channelOrderMock->expects($this->once())
            ->method('load')
            ->with($this->equalTo($orderNumber), $this->equalTo('order_number'))
            ->will($this->returnValue($channelOrderMock));

        $this->_object->updateOrder(array());
    }

    public function testUpdateOrder()
    {
        $orderNumber = rand(0, 100);

        $channelOrderMock = $this->mockModel('xcom_channelorder/order',
            array('load', 'getOrderId', 'getId', 'getOrder', 'save'));
        $channelOrderMock->expects($this->once())
            ->method('load')
            ->with($this->equalTo($orderNumber), $this->equalTo('order_number'))
            ->will($this->returnValue($channelOrderMock));
        $channelOrderMock->expects($this->any())
            ->method('getOrderId')
            ->will($this->returnValue(1));
        $channelOrderMock->expects($this->any())
            ->method('getId')
            ->will($this->returnValue(1));
        $channelOrderMock->expects($this->any())
            ->method('getOrder')
            ->will($this->returnValue(new Varien_Object(
                array('id' => 1, 'status' => Mage_Sales_Model_Order::STATE_NEW))));
        $channelOrderMock->expects($this->once())
            ->method('save')
            ->will($this->returnValue($channelOrderMock));

        $data = array(
            'status' => Mage_Sales_Model_Order::STATE_PROCESSING
        );

        $objectMock = $this->getMock(get_class($this->_object),
            array('updateChannelPaymentSpecific', 'createInvoice'));
        $objectMock->expects($this->once())
            ->method('updateChannelPaymentSpecific')
            ->with($this->equalTo($data))
            ->will($this->returnValue($objectMock));
        $objectMock->expects($this->once())
            ->method('createInvoice')
            ->will($this->returnValue($objectMock));

        $objectMock->setOrderMessageData(array(
            'orderNumber' => $orderNumber,
        ));

        $result = $objectMock->updateOrder($data);
        $this->assertInstanceOf('Xcom_ChannelOrder_Model_Message_Order', $result);

        $this->assertEquals($orderNumber, $result->getOrderNumber());
        $this->assertEquals(1, $result->getFlatOrderId());
    }

    /**
     * @expectedException Xcom_ChannelOrder_Exception
     */
    public function testUpdateChannelPaymentSpecificNoMethod()
    {
        $orderId = 99999;

        $data = array(
            'paymentMethods' => array(
                array(),
            )
        );

        $orderPaymentMock = $this->mockModel('xcom_channelorder/order_payment', array('load'));
        $orderPaymentMock->expects($this->once())
            ->method('load')
            ->with($this->equalTo($orderId), $this->equalTo('order_id'))
            ->will($this->returnValue(new Varien_Object(array('id' => 1))));

        $objectMock = $this->getMock(get_class($this->_object), array('getFlatOrderId'));
        $objectMock->expects($this->once())
            ->method('getFlatOrderId')
            ->will($this->returnValue($orderId));

        $objectMock->updateChannelPaymentSpecific($data);
    }

    /**
     * @expectedException Xcom_ChannelOrder_Exception
     */
    public function testUpdateChannelPaymentSpecificNoMethodNoId()
    {
        $orderId = 99998;

        $data = array(
            'paymentMethods' => array(
                array(),
            )
        );

        $orderPaymentMock = $this->mockModel('xcom_channelorder/order_payment', array('load'));
        $orderPaymentMock->expects($this->once())
            ->method('load')
            ->with($this->equalTo($orderId), $this->equalTo('order_id'))
            ->will($this->returnValue(new Varien_Object(array())));

        $objectMock = $this->getMock(get_class($this->_object), array('getFlatOrderId'));
        $objectMock->expects($this->once())
            ->method('getFlatOrderId')
            ->will($this->returnValue($orderId));

        $objectMock->updateChannelPaymentSpecific($data);
    }

    /**
     * @expectedException Xcom_ChannelOrder_Exception
     */
    public function testSaveChannelPaymentSpecificNoMethod()
    {
        $data = array(
            'paymentMethods' => array(
                array(),
            )
        );
        $this->_object->saveChannelPaymentSpecific($data);
    }

    /**
     * @expectedException Xcom_ChannelOrder_Exception
     */
    public function testSaveChannelOrderItemsNoProductSku()
    {
        $objectMock = $this->getMock(get_class($this->_object), array('getOrderData'));
        $objectMock->expects($this->once())
            ->method('getOrderData')
            ->will($this->returnValue(array()));

        $data = array(
            'orderItems' => array(
                array(
                    'itemId'     => 1,
                ),
            ),
        );
        $objectMock->saveChannelOrderItems($data);
    }

    /**
     * @expectedException Xcom_ChannelOrder_Exception
     */
    public function testSaveChannelOrderItemsEmptyProductSku()
    {
        $objectMock = $this->getMock(get_class($this->_object), array('getOrderData'));
        $objectMock->expects($this->once())
            ->method('getOrderData')
            ->will($this->returnValue(array()));

        $data = array(
            'orderItems' => array(
                array(
                    'productSku' => '    ', // 4 spaces
                    'itemId'     => 1,
                ),
            ),
        );
        $objectMock->saveChannelOrderItems($data);
    }

    /**
     * @expectedException Xcom_ChannelOrder_Exception
     */
    public function testSaveChannelOrderItemsNoItemId()
    {
        $objectMock = $this->getMock(get_class($this->_object), array('getOrderData'));
        $objectMock->expects($this->once())
            ->method('getOrderData')
            ->will($this->returnValue(array()));

        $data = array(
            'orderItems' => array(
                array(
                    'productSku' => 'testProductSku',
                ),
            ),
        );
        $objectMock->saveChannelOrderItems($data);
    }
}
