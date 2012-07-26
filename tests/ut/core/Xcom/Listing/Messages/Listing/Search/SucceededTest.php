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
class Xcom_Listing_Message_Listing_Search_SucceededTest extends Xcom_TestCase
{
    /**
     * @var Xcom_Listing_Model_Message_Listing_Search_Succeeded
     */
    protected $_object     = null;

    public function setUp()
    {
        parent::setUp();
        $this->_object = Mage::helper('xcom_xfabric')
          ->getMessage('listing/searchSucceeded');
    }

    public function tearDown()
    {
        $this->_object = null;
    }

    public function testInstanceOf()
    {
        $this->assertInstanceOf('Xcom_Listing_Model_Message_Listing_Search_Succeeded', $this->_object);
    }

    public function testGetTopic()
    {
        $topic = $this->_object->getTopic();
        $this->assertEquals('listing/searchSucceeded', $topic);
    }

    public function testProcess()
    {
        $result = $this->_object->process();
        $this->assertInstanceOf('Xcom_Listing_Model_Message_Listing_Search_Succeeded', $result);
    }

    public function testProcessWithoutChannel()
    {
        $body = array(
            'listings' => array('test_data'),
            'xProfileId' => 'test_xprofile_id'
        );
        /** @var $objectMock Xcom_Listing_Model_Message_Listing_Search_Succeeded */
        $objectMock = $this->getMock(get_class($this->_object), array('_getChannelFromPolicy'));
        $objectMock->expects($this->once())
            ->method('_getChannelFromPolicy')
            ->with($this->equalTo('test_xprofile_id'))
            ->will($this->returnValue(null));

        $objectMock->setBody($body);

        $objectMock->process();
        $result = $objectMock->getBody();

        $this->assertArrayHasKey('listings', $result);
    }

    public function testGetChannelFromPolicyWithoutChannelId()
    {
        $body = $this->_getListingData();

        $policyMock = $this->mockModel('xcom_mmp/policy', array('load'));
        $policyMock->expects($this->once())
            ->method('load')
            ->will($this->returnValue($policyMock));

        /** @var $objectMock Xcom_Listing_Model_Message_Listing_Search_Succeeded */
        $objectMock = $this->getMock(get_class($this->_object), array('_updateProductStatus'));

        $objectMock->setBody($body);
        $objectMock->process();
        $result = $objectMock->getBody();


        $this->assertArrayHasKey('listings', $result);
    }

    public function testGetChannelFromPolicyWithChannelIdFalse()
    {
        $body = $this->_getListingData();

        $policyMock = $this->mockModel('xcom_mmp/policy', array('load'));
        $policyMock->setId('test_policy_id')
            ->setChannelId('test_channel_id');

        $policyMock->expects($this->once())
            ->method('load')
            ->will($this->returnValue($policyMock));

        $channelMock = $this->mockModel('xcom_mmp/channel', array('load'));
        $channelMock->expects($this->once())
            ->method('load')
            ->will($this->returnValue($channelMock));

        /** @var $objectMock Xcom_Listing_Model_Message_Listing_Search_Succeeded */
        $objectMock = $this->getMock(get_class($this->_object), array('_updateProductStatus'));

        $objectMock->setBody($body);
        $objectMock->process();
        $result = $objectMock->getBody();

        $this->assertArrayHasKey('listings', $result);
    }

    public function testGetChannelFromPolicyWithChannelId()
    {
        $body = $this->_getListingData();

        $policyMock = $this->mockModel('xcom_mmp/policy', array('load'));
        $policyMock->setId('test_policy_id')
            ->setChannelId('test_channel_id');

        $policyMock->expects($this->once())
            ->method('load')
            ->will($this->returnValue($policyMock));

        $channelMock = $this->mockModel('xcom_mmp/channel', array('load'));
        $channelMock->setId('test_channel_id');

        $channelMock->expects($this->once())
            ->method('load')
            ->will($this->returnValue($channelMock));

        /** @var $objectMock Xcom_Listing_Model_Message_Listing_Search_Succeeded */
        $objectMock = $this->getMock(get_class($this->_object), array('_updateProductStatus'));

        $objectMock->setBody($body);
        $objectMock->process();
        $result = $objectMock->getBody();

        $this->assertArrayHasKey('listings', $result);
    }

    public function testUpdateProductStatusWithoutProductId()
    {
        $body = $this->_getListingData();

        $channelMock = $this->mockModel('xcom_mmp/channel', array('load'));
        $channelMock->setId('test_channel_id');
        $channelMock->setSiteCode('site_code');

        $channelProductMock = $this->mockModel('xcom_listing/channel_product');
        $productMock = $this->mockModel('catalog/product', array('getIdBySku'));
        $productMock->expects($this->at(0))
            ->method('getIdBySku')
            ->with($this->equalTo('sku_1'))
            ->will($this->returnValue(null));
        $productMock->expects($this->at(1))
            ->method('getIdBySku')
            ->with($this->equalTo('sku_2'))
            ->will($this->returnValue(null));

        /** @var $objectMock Xcom_Listing_Model_Message_Listing_Search_Succeeded */
        $objectMock = $this->getMock(get_class($this->_object), array('_getChannelFromPolicy'));
        $objectMock->expects($this->once())
            ->method('_getChannelFromPolicy')
            ->will($this->returnValue($channelMock));

        $objectMock->setBody($body);
        $objectMock->process();
        $result = $objectMock->getBody();

        $this->assertArrayHasKey('listings', $result);
    }

    public function testUpdateProductStatus()
    {
        $body = array(
            'listings' => array(
                0 => array(
                    'product' => array(
                    'sku' => 'sku_1',
                    ),
                    'status' => 'active'
                ),
                1 => array('product' => array(
                        'sku' => 'sku_2',
                    ),
                    'status' => 'inactive'
                ),
            ),
            'xProfileId' => 'test_xprofile_id'
        );

        $channelMock = $this->mockModel('xcom_mmp/channel', array('load'));
        $channelMock->setId('test_channel_id');
        $channelMock->setSiteCode('site_code');

        $channelProductMock = $this->mockModel('xcom_listing/channel_product', array('updateRelations'));
        $channelProductMock->expects($this->at(0))
            ->method('updateRelations')
            ->with(
                $this->equalTo('test_channel_id'),
                $this->equalTo('test_id_1'),
                null,
                $this->equalTo(Xcom_Listing_Model_Channel_Product::STATUS_ACTIVE)
            );
        $channelProductMock->expects($this->at(1))
            ->method('updateRelations')
            ->with(
                $this->equalTo('test_channel_id'),
                $this->equalTo('test_id_2'),
                null,
                $this->equalTo(Xcom_Listing_Model_Channel_Product::STATUS_INACTIVE)
            );

        $productMock = $this->mockModel('catalog/product', array('load', 'getIdBySku'));
        $productMock->expects($this->at(0))
            ->method('getIdBySku')
            ->with($this->equalTo('sku_1'))
            ->will($this->returnValue(1));

        $productMock->expects($this->at(2))
            ->method('getIdBySku')
            ->with($this->equalTo('sku_2'))
            ->will($this->returnValue(2));

        $product1 = new Varien_Object(array('id' => 'test_id_1'));
        $productMock->expects($this->at(1))
            ->method('load')
            ->with($this->equalTo(1))
            ->will($this->returnValue($product1));

        $product2 = new Varien_Object(array('id' => 'test_id_2'));
        $productMock->expects($this->at(3))
            ->method('load')
            ->with($this->equalTo(2))
            ->will($this->returnValue($product2));

        /** @var $objectMock Xcom_Listing_Model_Message_Listing_Search_Succeeded */
        $objectMock = $this->getMock(get_class($this->_object), array('_getChannelFromPolicy'));
        $objectMock->expects($this->once())
            ->method('_getChannelFromPolicy')
            ->will($this->returnValue($channelMock));

        $objectMock->setBody($body);
        $objectMock->process();
        $result = $objectMock->getBody();

        $this->assertArrayHasKey('listings', $result);
    }

    protected function _getListingData()
    {
        return array(
            'listings' => array(
                0 => array(
                    'product' => array(
                        'sku' => 'sku_1'
                    ),
                    'status' => 'test_status_1'
                ),
                1 => array(
                    'product' => array(
                        'sku' => 'sku_2'
                    ),
                    'status' => 'test_status_1'
                    ),
                ),
            'xProfileId' => 'test_xprofile_id'
        );
    }
}
