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
class Xcom_Listing_Message_Listing_Search_FailedTest extends Xcom_TestCase
{
    /**
     * @var Xcom_Listing_Model_Message_Listing_Search_Succeeded
     */
    protected $_object     = null;

    public function setUp()
    {
        parent::setUp();
        $this->_object = Mage::helper('xcom_xfabric')
          ->getMessage('listing/searchFailed');
    }

    public function tearDown()
    {
        $this->_object = null;
    }

    public function testInstanceOf()
    {
        $this->assertInstanceOf('Xcom_Listing_Model_Message_Listing_Search_Failed', $this->_object);
    }

    public function testGetTopic()
    {
        $topic = $this->_object->getTopic();
        $this->assertEquals('listing/searchFailed', $topic);
    }

    public function testPrepareResponseDataEmpty()
    {
        $result = $this->_object->getResponseData();
        $this->assertEmpty($result);
    }

    public function testPrepareResponseData()
    {
        $body = array(
            'filter' => array(
                'skus' => array('sku_1', 'sku_2'),
            ),
            'xProfileId' => 'test_xprofile_id'
        );

        /**@TODO Implement test once faild message is implemented*/

        /*$channel = new Varien_Object();
        /** @var $objectMock Xcom_Listing_Model_Message_Listing_Search_Succeeded */
        /*$objectMock = $this->getMock(get_class($this->_object), array('_getChannelFromPolicy', '_updateProductStatus'));
        $objectMock->expects($this->once())
            ->method('_getChannelFromPolicy')
            ->will($this->returnValue($channel));
        $objectMock->expects($this->once())
            ->method('_updateProductStatus')
            ->with($this->equalTo($channel), $this->equalTo($body['filter']['skus']),
                Xcom_Listing_Model_Channel_Product::STATUS_FAILURE);


        $objectMock->setBody($body);
        $result = $objectMock->process();

        $this->assertArrayHasKey('filter', $result);*/
    }
}
