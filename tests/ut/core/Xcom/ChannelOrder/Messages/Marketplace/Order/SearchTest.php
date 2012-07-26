<?php
class Xcom_ChannelOrder_Messages_Marketplace_Order_SearchTest
        extends Xcom_TestCase
{
    /**
     * @var Xcom_ChannelOrder_Model_Message_Marketplace_Order_Search
     */
    protected $_object;

    protected $_instanceOf = 'Xcom_ChannelOrder_Model_Message_Marketplace_Order_Search';

    public function setUp()
    {
        parent::setUp();
        $this->_object = Mage::getModel('xcom_channelorder/message_marketplace_order_search');
    }

    public function tearDown()
    {
        parent::tearDown();
        $this->_object = null;
    }

    public function testInstanceOf()
    {
        $this->assertInstanceOf($this->_instanceOf, $this->_object);
    }

    public function testTopic()
    {
        $this->assertEquals('marketplace/order/search', $this->_object->getTopic());
    }

    public function testPrepareData()
    {
        $this->_mockObject();
        $options = new Varien_Object(array(
            'site_code' => 'test_site_code',
            'seller_account_id' => 'test_seller_account_id',
            'fields'    => 'test_fields',
            'from_date' => 'test_from_date',
            'to_date'   => 'test_to_date',
            'source_id' => 'test_source_id',
            'ordering'  => 'test_ordering'
        ));
        $this->_object->process($options);
        $messageData = $this->_object->getMessageData();
        $this->assertArrayHasKey('siteCode', $messageData);
        $this->assertArrayHasKey('sellerAccountId', $messageData);
        $this->assertArrayHasKey('query', $messageData);
        $this->assertArrayHasKey('fields', $messageData['query']);
        $this->assertArrayHasKey('predicates', $messageData['query']);
        $this->assertArrayHasKey('ordering', $messageData['query']);

        $this->assertEquals(3, count($messageData['query']['predicates']));
        foreach ($messageData['query']['predicates'] as $predicate) {
            $this->assertArrayHasKey('field', $predicate);
            $this->assertArrayHasKey('operator', $predicate);
            $this->assertArrayHasKey('values', $predicate);
        }

        $this->assertContains($options->getSiteCode(), $messageData);
        $this->assertContains($options->getSellerAccountId(), $messageData);
        $this->assertContains($options->getFields(), $messageData['query']);
        $this->assertContains($options->getFromDate(), $messageData['query']['predicates'][0]['values']);
        $this->assertContains($options->getToDate(), $messageData['query']['predicates'][1]['values']);
        $this->assertContains($options->getSourceId(), $messageData['query']['predicates'][2]['values']);
        $this->assertContains($options->getOrdering(), $messageData['query']);
    }

    protected function _mockObject()
    {
        $objectMock = $this->getMock($this->_instanceOf, array('setEncoder', 'prepareHeaders', 'encode'));
        $this->_object = $objectMock;
        return $this;
    }
}
