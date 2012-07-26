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
 * @package     Xcom_Ebay
 * @copyright   Copyright (c) 2010 Magento Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
require_once 'Xcom/Ebay/controllers/Adminhtml/Ebay/ProductController.php';
class Xcom_Ebay_Controllers_Adminhtml_Ebay_ProductControllerTest extends Xcom_TestCase
{
    /** @var Xcom_Ebay_Adminhtml_Ebay_ProductController */
    protected $_object;

    public function setUp()
    {
        $this->_checkConnection = true;
        parent::setUp();
        $request = new Varien_Object();
        $response = new Varien_Object();
        $this->_object = new Xcom_Ebay_Adminhtml_Ebay_ProductController($request, $response);
    }

    public function tearDown()
    {
        $this->_object = null;
    }

    public function testPublishActionNoChannelId()
    {
        $session = Mage::getModel('adminhtml/session');

        $channelId = 1;

        $objectMock = $this->_getControllerMock(new Varien_Object(), new Varien_Object(),
            array('_getSession', '_redirect'));
        $objectMock->expects($this->any())
            ->method('_getSession')
            ->will($this->returnValue($session));
        $objectMock->expects($this->any())
            ->method('_redirect')
            ->with($this->equalTo('*/channel_product/'))
            ->will($this->returnValue($objectMock));

        $ebayChannelMock = $this->mockModel('xcom_ebay/channel', array('load'));
        $ebayChannelMock->expects($this->any())
            ->method('load')
            ->with($this->equalTo($channelId))
            ->will($this->returnValue($ebayChannelMock));

        $objectMock->getRequest()->setData('param', array('channel_id' => $channelId));
        $objectMock->publishAction();

        $messages = $session->getMessages();
        $this->assertInstanceOf('Mage_Core_Model_Message_Collection', $messages);

        $warning = $messages->getLastAddedMessage();
        $this->assertInstanceOf('Mage_Core_Model_Message_Warning', $warning);
        $this->assertEquals('Channel must be specified', $warning->getCode());
    }

    public function testPublishActionIsInactiveAccount()
    {
        $session = Mage::getModel('adminhtml/session');

        $channelId = 1;

        $objectMock = $this->_getControllerMock(new Varien_Object(), new Varien_Object(),
            array('_getSession', '_redirect', 'loadLayout', 'renderLayout', '_setActiveMenu'));
        $objectMock->expects($this->any())
            ->method('_getSession')
            ->will($this->returnValue($session));
        $objectMock->expects($this->any())
            ->method('_redirect')
            ->with($this->equalTo('*/channel_product/'))
            ->will($this->returnValue($objectMock));
        $objectMock->expects($this->any())
            ->method('loadLayout')
            ->will($this->returnValue($objectMock));
        $objectMock->expects($this->any())
            ->method('renderLayout')
            ->will($this->returnValue($objectMock));
        $objectMock->expects($this->any())
            ->method('_setActiveMenu')
            ->with($this->equalTo('channels/products'))
            ->will($this->returnValue($objectMock));

        $ebayChannelMock = $this->mockModel('xcom_ebay/channel', array('load', 'getIsInactiveAccount', 'getId'));
        $ebayChannelMock->expects($this->any())
            ->method('load')
            ->with($this->equalTo($channelId))
            ->will($this->returnValue($ebayChannelMock));
        $ebayChannelMock->expects($this->any())
            ->method('getIsInactiveAccount')
            ->will($this->returnValue(true));
        $ebayChannelMock->expects($this->any())
            ->method('getId')
            ->will($this->returnValue($channelId));

        $ebayHelper = $this->mockHelper('xcom_ebay', array('validateIsRequiredAttributeHasMappedValue'));
        $ebayHelper->expects($this->any())
            ->method('validateIsRequiredAttributeHasMappedValue')
            ->will($this->returnValue(false));

        $objectMock->getRequest()->setData('param', array('channel_id' => $channelId));
        $objectMock->publishAction();

        $messages = $session->getMessages();
        $this->assertInstanceOf('Mage_Core_Model_Message_Collection', $messages);

        $error = $messages->getLastAddedMessage();
        $this->assertInstanceOf('Mage_Core_Model_Message_Error', $error);
        $this->assertEquals("User ID / Merchant ID doesn't exist or expired.", $error->getCode());
    }

    public function testPublishActionIsRequiredAttributeHasMappedValue()
    {
        $session = Mage::getModel('adminhtml/session');

        $channelId = 1;

        $objectMock = $this->_getControllerMock(new Varien_Object(), new Varien_Object(),
            array('_getSession', '_redirect', 'loadLayout', 'renderLayout', '_setActiveMenu'));
        $objectMock->expects($this->any())
            ->method('_getSession')
            ->will($this->returnValue($session));
        $objectMock->expects($this->any())
            ->method('_redirect')
            ->with($this->equalTo('*/channel_product/'))
            ->will($this->returnValue($objectMock));
        $objectMock->expects($this->any())
            ->method('loadLayout')
            ->will($this->returnValue($objectMock));
        $objectMock->expects($this->any())
            ->method('renderLayout')
            ->will($this->returnValue($objectMock));
        $objectMock->expects($this->any())
            ->method('_setActiveMenu')
            ->with($this->equalTo('channels/products'))
            ->will($this->returnValue($objectMock));

        $ebayChannelMock = $this->mockModel('xcom_ebay/channel', array('load', 'getIsInactiveAccount', 'getId'));
        $ebayChannelMock->expects($this->any())
            ->method('load')
            ->with($this->equalTo($channelId))
            ->will($this->returnValue($ebayChannelMock));
        $ebayChannelMock->expects($this->any())
            ->method('getIsInactiveAccount')
            ->will($this->returnValue(false));
        $ebayChannelMock->expects($this->any())
            ->method('getId')
            ->will($this->returnValue($channelId));

        $ebayHelper = $this->mockHelper('xcom_ebay', array('validateIsRequiredAttributeHasMappedValue'));
        $ebayHelper->expects($this->any())
            ->method('validateIsRequiredAttributeHasMappedValue')
            ->will($this->returnValue(true));

        $objectMock->getRequest()->setData('param', array('channel_id' => $channelId));
        $objectMock->publishAction();

        $messages = $session->getMessages();
        $this->assertInstanceOf('Mage_Core_Model_Message_Collection', $messages);

        $error = $messages->getLastAddedMessage();
        $this->assertInstanceOf('Mage_Core_Model_Message_Error', $error);
        $this->assertEquals('You may have unmapped mapping for selected AttributeSet. ' .
            'Your publish may fail. Please complete mapping before hitting publish.', $error->getCode());
    }

    public function testHistoryActionNoChannelId()
    {
        $session = Mage::getModel('adminhtml/session');

        $channelId = 0;

        $objectMock = $this->_getControllerMock(new Varien_Object(), new Varien_Object(),
            array('_getSession', '_redirect'));
        $objectMock->expects($this->any())
            ->method('_getSession')
            ->will($this->returnValue($session));
        $objectMock->expects($this->any())
            ->method('_redirect')
            ->with($this->equalTo('*/channel_product/'))
            ->will($this->returnValue($objectMock));

        $ebayChannelMock = $this->mockModel('xcom_ebay/channel', array('load'));
        $ebayChannelMock->expects($this->any())
            ->method('load')
            ->with($this->equalTo($channelId))
            ->will($this->returnValue($ebayChannelMock));

        $objectMock->getRequest()->setData('param', array('channel' => $channelId));
        $objectMock->historyAction();

        $messages = $session->getMessages();
        $this->assertInstanceOf('Mage_Core_Model_Message_Collection', $messages);

        $warning = $messages->getLastAddedMessage();
        $this->assertInstanceOf('Mage_Core_Model_Message_Warning', $warning);
        $this->assertEquals('Channel not exist', $warning->getCode());
    }

    public function testHistoryActionNoProductsInChannel()
    {
        $session = Mage::getModel('adminhtml/session');

        $channelId = 1;
        $paramId = 2;

        $objectMock = $this->_getControllerMock(new Varien_Object(), new Varien_Object(),
            array('_getSession', '_redirect'));
        $objectMock->expects($this->any())
            ->method('_getSession')
            ->will($this->returnValue($session));
        $objectMock->expects($this->any())
            ->method('_redirect')
            ->with($this->equalTo('*/channel_product/'))
            ->will($this->returnValue($objectMock));

        $ebayChannelMock = $this->mockModel('xcom_ebay/channel', array('load', 'getId'));
        $ebayChannelMock->expects($this->any())
            ->method('load')
            ->with($this->equalTo($channelId))
            ->will($this->returnValue($ebayChannelMock));
        $ebayChannelMock->expects($this->any())
            ->method('getId')
            ->will($this->returnValue($channelId));

        $listingChannelProductMock = $this->mockModel('xcom_listing/channel_product', array('isProductsInChannel'));
        $listingChannelProductMock->expects($this->any())
            ->method('isProductsInChannel')
            ->with($this->equalTo($channelId), $this->equalTo(array($paramId)))
            ->will($this->returnValue(false));

        $objectMock->getRequest()->setData('param', array('channel' => $channelId, 'id' => $paramId));
        $objectMock->historyAction();

        $messages = $session->getMessages();
        $this->assertInstanceOf('Mage_Core_Model_Message_Collection', $messages);

        $warning = $messages->getLastAddedMessage();
        $this->assertInstanceOf('Mage_Core_Model_Message_Warning', $warning);
        $this->assertEquals('Product not in Channel', $warning->getCode());
    }

    public function testSaveActionWithNoChannelId()
    {
        $this->_mockSessionWarning();
        $this->_mockChannel(new Varien_Object());
        $objectMock = $this->getMock(get_class($this->_object), array('_redirect'),
            array(new Varien_Object(), new Varien_Object()));
        $objectMock->expects($this->once())
            ->method('_redirect')
            ->with($this->equalTo('*/channel_product/'));

        $this->assertNull($objectMock->saveAction());
    }

    protected function _mockSessionWarning()
    {
        $session = $this->mockModel('adminhtml/session');
        $session->expects($this->any())
            ->method('addWarning')
            ->with($this->equalTo('Channel must be specified'));
        return $session;
    }

    protected function _mockChannel($object)
    {
        $channel = $this->mockModel('xcom_ebay/channel');
        $channel->expects($this->any())
            ->method('load')
            ->will($this->returnValue($object));
        return $channel;
    }

    public function testSaveActionProcessNewListing()
    {
        $productIds = array(1,2,3);
        $channel = new Varien_Object(array('id' => 'test_channel_id', 'channeltype_code' => 'test_ebay'));
        $this->_mockChannel($channel);
        $policyMock = $this->mockModel('xcom_ebay/policy');
        $policyMock->expects($this->any())
            ->method('load')
            ->will($this->returnValue($policyMock));
        $session = $this->mockModel('adminhtml/session');
        $session->expects($this->once())
            ->method('addSuccess');

        $request = new Mage_Core_Controller_Request_Http();
        $request->setParam('channel_id', 111);
        $storeId = 12;
        $request->setParam('store', $storeId);
        $request->setParam('product_ids', $productIds);
        $request->setPost(array('channel_id' => $channel->getId()));

        $objectMock = $this->getMock(get_class($this->_object), array('_redirect'),
            array($request, new Varien_Object()));
        $objectMock->expects($this->once())
            ->method('_redirect')
            ->with(
                $this->equalTo('*/channel_product/'),
                $this->equalTo(array(
                    'type' => $channel->getChanneltypeCode(),
                    'store' => $storeId,
                )));

        $channelProductMock = $this->mockModel('xcom_listing/channel_product',
            array('isProductsInChannel', 'addData', 'getValidListingId',
                'initProducts', 'validate', 'send', 'setListingId', 'saveProducts'));


        $channelProductMock->expects($this->once())
            ->method('isProductsInChannel')
            ->with($this->equalTo($channel->getId()), $this->equalTo($productIds))
            ->will($this->returnValue(false));

        $listingMock = $this->mockModel('xcom_listing/listing',
            array('load', 'addData', 'save', 'prepareProducts', 'send', 'saveProducts'));
        $listingMock->expects($this->once())
            ->method('addData')
            ->with($this->equalTo($request->getPost()));
        $listingMock->expects($this->once())
            ->method('prepareProducts')
            ->with($this->equalTo($productIds));
        $listingMock->expects($this->once())
            ->method('send')
            ->with($this->equalTo(array('policy' => $policyMock, 'channel' => $channel)));
        $listingMock->expects($this->once())
            ->method('saveProducts');

        $validatorMock = $this->mockHelper('xcom_listing/validator',
            array('validateFields', 'validateProducts'));
        $validatorMock->expects($this->once())
            ->method('validateFields');
        $validatorMock->expects($this->once())
            ->method('validateProducts');
        $validatorMock->setListing($listingMock);

        $this->assertNull($objectMock->saveAction());
    }

    public function testSaveActionProcessDifferentListings()
    {
        $productIds = array(1,2,3);
        $channel = new Varien_Object(array('id' => 'test_channel_id', 'channeltype_code' => 'test_ebay'));

        $this->_mockChannel($channel);
        $policyMock = $this->mockModel('xcom_ebay/policy');
        $policyMock->expects($this->any())
            ->method('load')
            ->will($this->returnValue($policyMock));

        $session = $this->mockModel('adminhtml/session');
        $session->expects($this->once())
            ->method('addSuccess');

        $request = new Mage_Core_Controller_Request_Http();
        $request->setParam('channel_id', 111);
        $storeId = 12;
        $request->setParam('store', $storeId);
        $request->setParam('product_ids', $productIds);
        $request->setPost(array('channel_id' => $channel->getId(), 'policy_id' => 0));

        $objectMock = $this->getMock(get_class($this->_object), array('_redirect'),
            array($request, new Varien_Object()));
        $objectMock->expects($this->once())
            ->method('_redirect')
            ->with(
                $this->equalTo('*/channel_product/'),
                $this->equalTo(array(
                    'type' => $channel->getChanneltypeCode(),
                    'store' => $storeId,
                )));

        $channelProductMock = $this->mockModel('xcom_listing/channel_product',
            array('isProductsInChannel', 'addData', 'getPublishedListingIds',
                'saveProducts'));

        $channelProductMock->expects($this->once())
            ->method('isProductsInChannel')
            ->with($this->equalTo($channel->getId()), $this->equalTo($productIds))
            ->will($this->returnValue(true));
        $channelProductMock->expects($this->once())
            ->method('getPublishedListingIds')
            ->with($this->equalTo($productIds), $this->equalTo($channel->getId()))
            ->will($this->returnValue(array(3 => array(
                'product_ids'   => array(1,2),
                'channel_id'    => $channel->getId()
            ))));

        $listingMock = $this->mockModel('xcom_listing/listing',
            array('load', 'addData', 'save', 'prepareProducts', 'send', 'saveProducts', '_isChanged'));
        $listingMock->expects($this->any())
            ->method('_isChanged')
            ->will($this->returnValue(true));
        $listingMock->expects($this->any())
            ->method('addData')
            ->with($this->equalTo($request->getPost()));
        $listingMock->expects($this->any())
            ->method('prepareProducts')
            ->with($this->equalTo(array(1,2)));
        $listingMock->expects($this->any())
            ->method('send')
            ->with($this->equalTo(array('policy' => $policyMock, 'channel' => $channel)));
        $listingMock->expects($this->any())
            ->method('saveProducts');

        $validatorMock = $this->mockHelper('xcom_listing/validator',
            array('validateOptionalFields', 'validateProducts', 'isPriceChanged', 'isQtyChanged'));
        $validatorMock->expects($this->once())
            ->method('validateOptionalFields');
        $validatorMock->expects($this->once())
            ->method('validateProducts');
        $validatorMock->expects($this->once())
            ->method('isPriceChanged');
        $validatorMock->expects($this->once())
            ->method('isQtyChanged');
        $validatorMock->setListing($listingMock);

        $this->assertNull($objectMock->saveAction());
    }

    /**
     * Get controller's mock object
     *
     * @param $request
     * @param $response
     * @param array $methods
     * @return PHPUnit_Framework_MockObject_MockObject
     */
    protected function _getControllerMock($request, $response, array $methods = array())
    {
        return $this->getMock('Xcom_Ebay_Adminhtml_Ebay_ProductController',
                                        $methods,
                                        array($request, $response));
    }
}
