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
 * @category    Xcom
 * @package     Xcom_Listing
 * @copyright   Copyright (c) 2010 Magento Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class Xcom_Listing_Message_Listing_Create_CreatedTest extends Xcom_TestCase
{
    /**
     * @var Xcom_Listing_Model_Message_Listing_Create_Created
     */
    protected $_object;

    public function setUp()
    {
        parent::setUp();
        $this->_object = Mage::helper('xcom_xfabric')
            ->getMessage('listing/created');
    }

    protected function _mockObjectForProcessMethod($extraMethods = array())
    {
        $this->mockModel('xcom_xfabric/encoder_json');
        $methods = array_merge(array('_initSchema'), $extraMethods);
        $this->_object = $this->getMock(get_class($this->_object), $methods);
    }

    public function tearDown()
    {
        parent::tearDown();
        $this->_object = null;
    }

    public function testInstanceOf()
    {
        $this->assertObjectOf($this->_object);
    }

    /**
     * @dataProvider isNotValidBodyProvider
     * @return void
     */
//    public function testIsNotValidBody($body)
//    {
//        $this->_mockObjectForProcessMethod();
//        $this->_object->setBody($body);
//        $this->assertFalse($this->_object->process());
//    }

    public function assertObjectOf($object)
    {
        $this->assertInstanceOf('Xcom_Listing_Model_Message_Listing_Create_Created', $object);
    }

    public function isNotValidBodyProvider()
    {
        return array(
            array(),
            array('xProfileId'),
            array(array('xProfileId')),
            array(array('xProfileId' => '', 'listings')),
            array(array('xProfileId' => 'string', 'listings' => array())),
            array(array('xProfileId' => '', 'listings' => array('string')))
        );
    }

    public function testGetChannelByXProfileId()
    {
//        $this->mockChannel();

        $this->_object->setBody(array(
            'xProfileId' => '123',
            'listings'   => array('test')
        ));
        $channelMock = $this->mockModel('xcom_mmp/channel', array('getIdByXProfileId', 'load'));
        $channelMock->expects($this->any())
            ->method('getIdByXProfileId')
            ->will($this->returnValue('123'));
        $methods = array('setResponseBody', 'setCorrelationId', 'save');
        $logResponseMock = $this->mockModel('xcom_listing/message_listing_log_response', $methods);
        $logResponseMock->expects($this->once())
            ->method('setResponseBody')
            ->with($this->equalTo(json_encode($this->_object->getBody())))
            ->will($this->returnValue($logResponseMock));
        $logResponseMock->expects($this->once())
            ->method('setCorrelationId')
            ->will($this->returnValue($logResponseMock));
        $logResponseMock->expects($this->once())
            ->method('save')
            ->will($this->returnValue($logResponseMock));

        $logRequestMock = $this->mockModel('xcom_listing/message_listing_log_request', array('load', 'getId'));
        $logRequestMock->expects($this->any())
            ->method('getId')
            ->will($this->returnValue(1));
        $logRequestMock->expects($this->any())
            ->method('load')
            ->will($this->returnValue($logRequestMock));

        $this->_object->setHeaders(array("X-XC-RESULT-CORRELATION-ID" => "1216fc7f0a00a60ea74a8799d8c6693b"));
        $this->assertTrue($this->_object->process());
    }

    /**
     * @expectedException Mage_Core_Exception
     * @expectedExceptionMessage No Correlation ID in ResponseArray
     */
    public function testWithoutCorrelationId()
    {
        $this->_object->setBody(array(
            'xProfileId' => '123',
            'listings'   => array(
                'product' => array(
                    'sku' => 'sku_' . rand(1, 10000)),
                ),
        ));
        $this->_object->process();
    }

    public function testUpdateProductStatus()
    {
        $this->mockChannel();
        $this->_object->setBody(array(
            'xProfileId' => '123',
            'listings'   => array(
                'product' => array(
                    'sku' => 'sku_' . rand(1, 10000)),
            ),
        ));
        $productMock = $this->mockModel('catalog/product', array('load', 'getIdBySku', 'getId'));
        $productMock->expects($this->any())
            ->method('load')
            ->will($this->returnValue($productMock));
        $productMock->expects($this->any())
            ->method('getIdBySku')
            ->will($this->returnValue('test_id'));
        $productMock->expects($this->any())
            ->method('getIdBySku')
            ->will($this->returnValue('test_id'));
        $productMock->expects($this->any())
            ->method('getId')
            ->will($this->returnValue('test_product_id'));
        $methods = array('setResponseBody', 'setCorrelationId', 'save');
        $logResponseMock = $this->mockModel('xcom_listing/message_listing_log_response', $methods);
        $logResponseMock->expects($this->once())
            ->method('setResponseBody')
//            ->with($this->equalTo(json_encode($this->_object->getBody())))
            ->will($this->returnValue($logResponseMock));
        $logResponseMock->expects($this->once())
            ->method('setCorrelationId')
            ->will($this->returnValue($logResponseMock));
        $logResponseMock->expects($this->once())
            ->method('save')
            ->will($this->returnValue($logResponseMock));
        $logRequestMock = $this->mockModel('xcom_listing/message_listing_log_request', array('load', 'getId'));
        $logRequestMock->expects($this->any())
            ->method('getId')
            ->will($this->returnValue(1));
        $logRequestMock->expects($this->any())
            ->method('load')
            ->will($this->returnValue($logRequestMock));
        $this->mockModel('xcom_listing/channel_product', array('updateRelations'));

        $methods = array('setLogResponseId', 'setResponseResult', 'save');
        $channelHistoryMock = $this->mockModel('xcom_listing/channel_history', $methods);
        $channelHistoryMock->expects($this->any())
            ->method('setLogResponseId')
            ->will($this->returnValue($channelHistoryMock));
        $channelHistoryMock->expects($this->any())
            ->method('setResponseResult')
            ->will($this->returnValue($channelHistoryMock));

        $historyCollection = $this->mockResource('xcom_listing/channel_history_collection',
            array('addFieldToFilter'));
        $historyCollection->expects($this->any())
            ->method('addFieldToFilter')
            ->will($this->returnValue(array($channelHistoryMock)));
        $this->_object->setBody(array(
            'xProfileId' => '123',
            'listings'   => array(
                array('product' => array('sku' => 'sku_1'),
                    'listingURL'   => 'listingUrl_' . rand(1, 10000),
                    'marketItemId' => 'marketItemId_' . rand(1, 10000)
                ),
                array('product' => array('sku' => 'sku_2'),
                    'listingURL'   => 'listingUrl_' . rand(1, 10000),
                    'marketItemId' => 'marketItemId_' . rand(1, 10000)
                ),
            )
        ));
        $this->_object->setHeaders(array("X-XC-RESULT-CORRELATION-ID" => "1216fc7f0a00a60ea74a8799d8c6693b"));
        $this->assertTrue($this->_object->process());
    }

    public function mockChannel()
    {
        $channelMock = $this->mockModel('xcom_mmp/channel', array('getId', 'getIdByXProfileId', 'load'));
        $channelMock->expects($this->any())
            ->method('getIdByXProfileId')
            ->with($this->equalTo('123'))
            ->will($this->returnValue(13));

        $channelMock->expects($this->any())
            ->method('load')
            ->with($this->equalTo(13))
            ->will($this->returnValue($channelMock));
    }
}
