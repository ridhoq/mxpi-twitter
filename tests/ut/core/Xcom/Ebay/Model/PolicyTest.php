<?php
class Xcom_Ebay_Model_PolicyTest extends Xcom_TestCase
{
    /**
     * @var Xcom_Ebay_Model_Policy
     */
    protected $_object;

    public function setUp()
    {
        $this->_checkConnection = true;
        parent::setUp();
        $this->_object = new Xcom_Ebay_Model_Policy();
    }

    public function tearDown()
    {
        parent::tearDown();
        $this->_object = null;
    }

    public function testConstruct()
    {
        $this->assertInstanceOf('Xcom_Ebay_Model_Resource_Policy', $this->_object->getResource(),
                                'Wrong instance of Ebay policy recource');
    }

    /**
     * @dataProvider beforeSaveProvider
     * @param array $testData
     * @param array $expectedData
     * @return void
     */
    public function testBeforeSave($testData, $expectedData)
    {
        $this->_object->addData($testData);
        $objectMock = $this->mockResource('xcom_ebay/policy',
            array('beginTransaction', 'addCommitCallback', 'save'));
        $objectMock->expects($this->once())
            ->method('addCommitCallback')
            ->will($this->returnValue(new Xcom_Ebay_Model_PolicyTest_Adapter()));

        $this->_object->save();
        $this->assertEquals($expectedData, $this->_object->getData());
    }

    public function beforeSaveProvider()
    {
        return array(
            array(
                array('payment_name'  => null),
                array('payment_name' => null, 'payment_paypal_email' => null, 'channeltype_code' => 'ebay')
            ),
            array(
                array('payment_name'  => array(), 'payment_paypal_email' => 'email'),
                array('payment_name' => '', 'payment_paypal_email' => 'email', 'channeltype_code' => 'ebay')
            ),
            array(
                array('payment_name'  => array('test_1', 'test_2'), 'payment_paypal_email' => 'email'),
                array('payment_name' => 'test_1,test_2', 'payment_paypal_email' => 'email','channeltype_code' => 'ebay')
            ),
        );
    }

    public function testPrepareShippingData()
    {
        $shippingNames = array(
            'test_shipping_id'
        );
        $shippingCosts = array(
            'test_shipping_id' => 0.089
        );
        $shippingSortOrder = array(
            'test_shipping_id' => 'test_shipping_sort_order'
        );

        $object = $this->getMock(get_class($this->_object), array('getShippingName', 'getShippingCost',
            'getShippingSortOrder'));
        $object->expects($this->any())
            ->method('getShippingName')
            ->will($this->returnValue($shippingNames));
        $object->expects($this->any())
            ->method('getShippingCost')
            ->will($this->returnValue($shippingCosts));


        $object->prepareShippingData();

        $result = $object->getShippingData();

        $this->assertTrue(is_array($result));

        $this->assertEquals(1, count($result));
        $this->assertEquals(3, count($result['test_shipping_id']));

        $this->assertEquals('test_shipping_id', $result['test_shipping_id']['shipping_id']);
        $this->assertEquals(0.09, $result['test_shipping_id']['cost']);
    }

    /**
     * @param bool $isPolicyNameUniqueResult
     * @param array $data
     * @param string $message
     * @dataProvider validateExceptionProvider
     * @expectedException Mage_Core_Exception
     */
    public function testValidateException($isPolicyNameUniqueResult, $data, $message)
    {
        $objectMock = $this->_mockIsPolicyNameUniqueMethod($isPolicyNameUniqueResult);
        $objectMock->addData($data);
        $objectMock->validate();
    }

    public function validateExceptionProvider()
    {
        return array(
            array(false, array(), 'Policy with name "" is already exist.'),
            array(true, array(), 'Payment method for policy "" is required.'),
            array(true, array('payment_name' => 'test'), 'Shipping method for policy "" is required.'),
            array(true, array('payment_name' => 'test', 'shipping_data' => '123'),
                'Product location for policy "" is required.'),
            array(true, array('payment_name' => 'test', 'shipping_data' => '123', 'location' => '123'),
                'Postal code for policy "" is required.'),
            array(true, array('payment_name' => 'test', 'shipping_data' => '123',
                'location' => '123', 'postal_code' => '123'),
                'Handling Time for policy "" is required.'),
        );
    }

    /**
     * @param $data
     * @dataProvider validateProvider
     */
    public function testValidate($data)
    {
        $objectMock = $this->_mockIsPolicyNameUniqueMethod(true);
        $objectMock->addData($data);
        $objectMock->validate();
    }

    public function validateProvider()
    {
        return array(
            array(array('payment_name' => 'test', 'shipping_data' => '123',
            'location' => '123', 'postal_code' => '123', 'handling_time' => '123')),
            array(array('payment_name' => 'test', 'shipping_data' => '123',
                'location' => '123', 'postal_code' => '123', 'handling_time' => 0)),
            array(array('payment_name' => 'test', 'shipping_data' => '123',
                'location' => '123', 'postal_code' => '123', 'handling_time' => '0')),
        );
    }

    protected function _mockIsPolicyNameUniqueMethod($returnValue)
    {
        $objectMock = $this->getMock(get_class($this->_object),
            array('isPolicyNameUnique'));
        $objectMock->expects($this->once())
            ->method('isPolicyNameUnique')
            ->will($this->returnValue($returnValue));
        return $objectMock;
    }
}

class Xcom_Ebay_Model_PolicyTest_Adapter
{
    public function commit()
    {

    }
}
