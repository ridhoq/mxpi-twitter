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
class Xcom_Listing_Message_Listing_Update_FailedTest extends Xcom_TestCase
{
    /**
     * @var Xcom_Listing_Model_Message_Listing_Update_Failed
     */
    protected $_object;

    public function setUp()
    {
        parent::setUp();
        $this->_object = Mage::helper('xcom_xfabric')
          ->getMessage('listing/updateFailed');
    }

    public function tearDown()
    {
        parent::tearDown();
        $this->_object = null;
    }

    public function testInstanceOf()
    {
        $this->assertInstanceOf('Xcom_Listing_Model_Message_Listing_Update_Failed', $this->_object);
    }

    /**
     * @expectedException Mage_Core_Exception
     */
    public function testProcessWithNoXprofileId()
    {
        $this->_mockObjectForProcessMethod();
        $this->_object->setBody(null);
        $this->_object->process();
    }

    /**
     * @expectedException Mage_Core_Exception
     */
    public function testProcessWithEmptyErrors()
    {
        $this->_mockObjectForProcessMethod();
        $this->_object->setBody(array('xProfileId' => 'string'));
        $this->_object->process();
    }

    /**
     * @expectedException Mage_Core_Exception
     * @expectedExceptionMessage Message is not valid
     */
    public function testProcessWithNotArrayErrors()
    {
        $this->_mockObjectForProcessMethod();
        $this->_object->setBody(array('xProfileId' => 'string123', 'errors' => 'string'));
        $this->_object->setHeaders(array("X-XC-RESULT-CORRELATION-ID" => "1216fc7f0a00a60ea74a8799d8c6693b"));
        $this->assertFalse($this->_object->process());
    }

    public function _mockChannel()
    {
        $channelMock = $this->mockModel('xcom_mmp/channel', array('getId', 'getIdByXProfileId', 'load'));
        $channelMock->expects($this->any())
            ->method('getIdByXProfileId')
            ->with($this->equalTo('string123'))
            ->will($this->returnValue(13));

        $channelMock->expects($this->any())
            ->method('load')
            ->with($this->equalTo(13))
            ->will($this->returnValue($channelMock));
    }

    /**
     * @expectedException Mage_Core_Exception
     * @expectedExceptionMessage Channel with xProfileId doesn't exists: string
     */
    public function testProcessWithNoChannel()
    {
        $this->_object->setBody(array('xProfileId' => 'string', 'errors' => array('string')));
        $this->_object->setHeaders(array("X-XC-RESULT-CORRELATION-ID" => "1216fc7f0a00a60ea74a8799d8c6693b"));

        $this->mockModel('xcom_mmp/channel');

        $this->assertTrue($this->_object->process());
    }

    /**
     * @expectedException Mage_Core_Exception
     * @expectedExceptionMessage No Correlation ID in ResponseArray
     */
    public function testProcessWithChannelAndNoCorrelationId()
    {
        $this->_object->setBody(array(
            'xProfileId' => 'string123',
            'errors' => array(
                array('listing' => array('product' => array('sku' => 'sku_1'))),
                array('listing' => array('product' => array('sku' => 'sku_2')))
            )));
        $this->assertTrue($this->_object->process());
    }

    /**
     * @return void
     */
    public function testProcessWithUpdateChannelHistory()
    {
        $this->_mockChannel();

        $body = array(
            'xProfileId' => 'string123',
            'errors' => array(
                array('listing' => array('product' => array('sku' => 'sku_1'))),
                array('listing' => array('product' => array('sku' => 'sku_2')))
        ));
        $this->_object->setBody($body);
        $this->_object->setHeaders(array('x-xc-result-correlation-id' => '123123123'));

        $methods = array('setResponseBody', 'setCorrelationId', 'save');
        $logResponseMock = $this->mockModel('xcom_listing/message_listing_log_response', $methods);
        $logResponseMock->expects($this->once())
            ->method('setResponseBody')
            ->with($this->equalTo(json_encode($body)))
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

        $historyMock = $this->_mockHistory();

        $historyCollection = $this->mockResource('xcom_listing/channel_history_collection', array('addFieldToFilter'));
        $historyCollection->expects($this->at(0))
            ->method('addFieldToFilter')
            ->will($this->returnValue($historyCollection));
        $historyCollection->expects($this->at(1))
            ->method('addFieldToFilter')
            ->will($this->returnValue(array(
                $historyMock,
                $historyMock,
            )));

        $this->mockModel('xcom_listing/channel_product', array('updateRelations'));
        $this->assertTrue($this->_object->process());
    }

    protected function _mockObjectForProcessMethod($extraMethods = array())
    {
        $this->mockModel('xcom_xfabric/encoder_json');
        $methods = array_merge(array('_initSchema'), $extraMethods);
        $this->_object = $this->getMock(get_class($this->_object), $methods);
    }

    protected function _mockHistory()
    {
        $historyMock = $this->getMock('Xcom_Listing_Model_Channel_History',
            array('setLogResponseId', 'setResponseResult', 'save'));
        $historyMock->expects($this->any())
            ->method('setLogResponseId')
            ->will($this->returnValue($historyMock));
        $historyMock->expects($this->any())
            ->method('setResponseResult')
            ->will($this->returnValue($historyMock));
        $historyMock->expects($this->any())
            ->method('save')
            ->will($this->returnValue($historyMock));
        return $historyMock;
    }
}
