<?php
class Xcom_Ebay_Model_ChannelTest extends Xcom_TestCase
{
    /** @var Xcom_Ebay_Model_Channel */
    protected $_object;

    public function setUp()
    {
        $this->_checkConnection = true;
        parent::setUp();
        $this->_object = new Xcom_Ebay_Model_Channel();
    }

    public function tearDown()
    {
        parent::tearDown();
        $this->_object = null;
    }

    public function testConstruct()
    {
        $this->assertEquals('ebay', $this->_object->getChanneltypeCode(), 'Wrong channel type code');
    }

    public function testGetAccountModelClass()
    {
        $result = $this->_object->getAccountModelClass();
        $this->assertEquals('xcom_mmp/account', $result);
    }
}
