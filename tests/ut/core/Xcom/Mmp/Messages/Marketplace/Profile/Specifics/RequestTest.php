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
 * @package     Xcom_Mmp
 * @copyright   Copyright (c) 2010 Magento Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

class Xcom_Mmp_Message_Marketplace_Profile_Specifics_RequestTest extends Xcom_TestCase
{
    public function setUp()
    {
        parent::setUp();
        $this->_object = Mage::helper('xcom_xfabric')->getMessage('marketplace/profile/marketSpecifics');
    }

    public function tearDown()
    {
        $this->_object = null;
    }

    public function testInstanceOf()
    {
        $this->assertInstanceOf('Xcom_Mmp_Model_Message_Marketplace_Profile_Specifics_Request', $this->_object);
    }

    public function testGetTopic()
    {
        $topic = $this->_object->getTopic();
        $this->assertEquals('com.x.marketplace.ebay.v1/MarketplaceProfileSpecifics', $topic);
    }

    public function testProcess()
    {
        $data = new Varien_Object(array(
            'location'          => 'test',
            'postalCode'        => '12345',
            'countryCode'       => 'US',
//            'payPalEmailAddress' => '',
//            'handlingTime'      => '',
//            'useTaxTable'       => ''
        ));
        $this->_object->process($data);

        $messageData = $this->_object->getMessageData();

        $this->assertArrayHasKey('location', $messageData, "Location is wrong");
        $this->assertArrayHasKey('postalCode', $messageData, "postalCode is wrong");
        $this->assertArrayHasKey('payPalEmailAddress', $messageData, "payPalEmailAddress is wrong");
        $this->assertNull($messageData['handlingTime'], "handlingTime is wrong");
        $this->assertArrayHasKey('useTaxTable', $messageData, "useTaxTable is wrong");
        $this->assertEquals($messageData['countryCode'], 'US', "countryCode Name is wrong");

        //handling time is zero
        $data->setData('handlingTime', 0);
        $this->_object->process($data);
        $messageData = $this->_object->getMessageData();
        $this->assertEquals(0, $messageData['handlingTime'], "handlingTime is wrong");

        //handling time is higher than zero
        $data->setData('handlingTime',10);
        $this->_object->process($data);
        $messageData = $this->_object->getMessageData();
        $this->assertEquals(10, $messageData['handlingTime'], "handlingTime is wrong");

        //handling time is 'none'
        $data->setData('handlingTime','none');
        $this->_object->process($data);
        $messageData = $this->_object->getMessageData();
        $this->assertNull($messageData['handlingTime'], "handlingTime is wrong");

    }

}
