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

class Xcom_Ebay_Block_Adminhtml_Product_Edit_Tab_SettingsTest extends Xcom_TestCase
{
    protected $_object;

    public function setUp()
    {
        $this->_checkConnection = true;
        parent::setUp();
        $this->_object = new Xcom_Ebay_Block_Adminhtml_Product_Edit_Tab_Settings_Mock();
    }

    public function tearDown()
    {
        $this->_object = null;
    }

    public function testGetProductIds()
    {
        $requestedIds = array(1,2,3);
        $helper = $this->mockHelper('xcom_ebay', array('getRequestProductIds'));
        $helper->expects($this->once())
            ->method('getRequestProductIds')
            ->will($this->returnValue($requestedIds));
        $result = $this->_object->getProductIds();
        $this->assertEquals($requestedIds, $result);
    }

    public function testGetPolicyOptionArray()
    {
        $channel = new Varien_Object(array('id' => 1));
        Mage::register('current_channel', $channel);


        $expectedArray = array(
            array(
                'value' => 'Test 1',
                'label' => 'Test 1',
            ),
            array(
                'value' => 'Test 2',
                'label' => 'Test 2',
            ),
        );

        $collection = $this->mockCollection('xcom_ebay/policy',
            array('addFieldToFilter', 'toOptionArray'));
        $collection->expects($this->at(0))
            ->method('addFieldToFilter')
            ->with($this->equalTo('channel_id'), $this->equalTo($channel->getId()))
            ->will($this->returnSelf());
        $collection->expects($this->at(1))
            ->method('addFieldToFilter')
            ->with($this->equalTo('is_active'), $this->equalTo(1))
            ->will($this->returnSelf());
        $collection->expects($this->at(2))
            ->method('addFieldToFilter')
            ->with($this->equalTo('xprofile_id'), $this->equalTo(array('notnull' => true)))
            ->will($this->returnSelf());
        $collection->expects($this->once())
            ->method('toOptionArray')
            ->will($this->returnValue($expectedArray));

        $listingMock = $this->mockModel('xcom_listing/listing', array('load'));
        $listingMock->expects($this->any())
            ->method('load')
            ->will($this->returnSelf());
        $this->_object->setListing($listingMock);

        $policyMock = $this->mockModel('xcom_ebay/policy', array('getCollection'));
        $policyMock->expects($this->once())
            ->method('getCollection')
            ->will($this->returnValue($collection));
        array_unshift($expectedArray, array(
                'value' => '',
                'label' => 'Please Select One',
            )
        );
        $this->assertEquals($expectedArray, $this->_object->getPolicyOptionArray());
    }

    public function testTabFunctions()
    {
        $this->assertEquals($this->_object->getTabLabel(), 'Publish to Channel');
        $this->assertEquals($this->_object->getTabTitle(), 'Publish to Channel');
        $this->assertTrue($this->_object->canShowTab());
        $this->assertFalse($this->_object->isHidden());

        Mage::register('current_channel', 'test_1');
        $this->assertEquals($this->_object->getCurrentChannel(), 'test_1');
    }

    public function testGetListingNoListing()
    {
        $objectMock = $this->getMock('Xcom_Ebay_Block_Adminhtml_Product_Edit_Tab_Settings',
            array('_getPublishedListingId'));
        $objectMock->expects($this->any())
            ->method('_getPublishedListingId')
            ->will($this->returnValue(0));

        $result = $objectMock->getListing();
        $this->assertInstanceOf('Xcom_Listing_Model_Listing', $result);
    }

    public function testGetListing()
    {
        $objectMock = $this->getMock('Xcom_Ebay_Block_Adminhtml_Product_Edit_Tab_Settings',
            array('_getPublishedListingId'));
        $objectMock->expects($this->any())
            ->method('_getPublishedListingId')
            ->will($this->returnValue(1));

        $listingMock = $this->mockModel('xcom_listing/listing', array('load'));
        $listingMock->expects($this->once())
            ->method('load')
            ->will($this->returnValue($listingMock));

        $result = $objectMock->getListing();
        $this->assertInstanceOf('Xcom_Listing_Model_Listing', $result);
    }
}

class Xcom_Ebay_Block_Adminhtml_Product_Edit_Tab_Settings_Mock
    extends Xcom_Ebay_Block_Adminhtml_Product_Edit_Tab_Settings
{
    protected function _isEachProductInChannel()
    {
        return false;
    }

    public function setListing($listing)
    {
        $this->_listing = $listing;
    }
}
