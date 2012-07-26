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

class Xcom_Ebay_Block_Adminhtml_Channel_EditTest extends Xcom_TestCase
{
    /**
     * @var Xcom_Ebay_Block_Adminhtml_Channel_Edit
     */
    public $object;

    public function setUp()
    {
        $this->_checkConnection = true;
        parent::setUp();
        /** @var $object Xcom_Ebay_Block_Adminhtml_Channel_Edit */
        $channel = Mage::getModel('xcom_ebay/channel');
        $channel->setId(1);
        $channel->setName('Ebay US');
        $channel->setChanneltypeCode('ebay');
        Mage::register('current_channel', $channel);
        $this->object = new Xcom_Ebay_Block_Adminhtml_Channel_Edit();
    }

    public function tearDown()
    {
        $this->object = null;
        Mage::unregister('current_channel');
    }

    public function testCurrentChannel()
    {
        $this->assertInstanceOf('Xcom_Ebay_Model_Channel', Mage::registry('current_channel'));
    }
    public function testGetChannel()
    {
        $this->assertInstanceOf('Xcom_Ebay_Model_Channel', $this->object->getChannel());
    }

    public function testGetEbayChannel()
    {
        $this->assertEquals('ebay', $this->object->getChannel()->getChanneltypeCode());
    }

    public function testGetHeaderTextEdit()
    {
        $this->assertEquals("Edit \"Ebay US\" Channel", $this->object->getHeaderText());
    }

    public function testGetBackUrl()
    {
        $this->assertContains('type/ebay', $this->object->getBackUrl());
    }

    public function testGetHeaderTextNew()
    {
        Mage::unregister('current_channel');
        Mage::register('current_channel', new Varien_Object());
        $channeltypeMock = $this->mockModel('xcom_channelgroup/config_channeltype');
        $channeltypeMock->expects($this->once())
            ->method('getChannelType')
            ->will($this->returnValue(new Varien_Object(array('title' => 'Test eBay'))));
        $this->assertEquals("New \"Test eBay\" Channel", $this->object->getHeaderText());
    }

    public function testGetChannelType()
    {
        Mage::clearRegistry();
        Mage::register('current_channel', new Varien_Object(array(
            'channeltype_code' => 'test_channeltype_code',
        )));

        $result = $this->object->getChannelType();
        $this->assertInstanceOf('Varien_Object', $result);
        $this->assertFalse($result->hasData());

        $channel = Mage::getModel('xcom_ebay/channel');
        $channel->setId(1);
        $channel->setName('Ebay US');
        $channel->setChanneltypeCode('ebay');
        Mage::clearRegistry();
        Mage::register('current_channel', $channel);

        $result = $this->object->getChannelType();
        $this->assertInstanceOf('Varien_Object', $result);
        $this->assertTrue($result->hasData());
        $this->assertEquals('ebay', $result->getCode());
    }
}
