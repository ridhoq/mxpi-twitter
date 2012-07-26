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

class Xcom_Mmp_Message_Marketplace_Environment_Search_ResponseTest extends Xcom_TestCase
{
    const TOPIC = 'marketplace/environment/searchSucceeded';

    public function setUp()
    {
        /** @var $_object Xcom_Mmp_Model_Message_Marketplace_Environment_Search_Succeeded */
        $this->_object = Mage::helper('xcom_xfabric')->getMessage('marketplace/environment/searchSucceeded');
        parent::setUp();
    }

    public function tearDown()
    {
        $this->_object = null;
    }

    public function testTopic()
    {
        $this->assertEquals(self::TOPIC, $this->_object->getTopic());
    }

    public function testProcess()
    {
        $channelTypeCode = 'test';
        $mpChannelCode = 'test_mp_code';
        $data = array('eBay_test_1', 'eBay_test_2');

        /** @var $envMessageMock Xcom_Mmp_Model_Message_Marketplace_Environment_Search_Succeeded */
        $envMessageMock = $this->mockModel('xcom_mmp/message_marketplace_environment_search_succeeded',
            array('_validateSchema'));

        $envModelMock = $this->mockResource('xcom_mmp/environment', array('upgrade'));
        $envModelMock->expects($this->once())
                     ->method('upgrade')
                     ->with('test', 'test', $data);
        $envMessageMock->expects($this->once())
                       ->method('_validateSchema')
                       ->will($this->returnValue(true));

        $envMessageMock->setBody(array(
            'marketplace'  => 'test',
            'siteCode'     => 'test',
            'environments' => $data
        ));
        $envMessageMock->process();
    }

    /**
     * @dataProvider providerResponseData
     * @param $data
     * @param $expect
     */
    public function test_prepareResponseData($data, $expect)
    {
        $this->_object->setBody($data);
        $return = $this->_object->getResponseData();
        $this->assertEquals($expect, $return);
    }

    public function providerResponseData()
    {
        return array(
            array( array('environments' => array()), array()),
            array( array('environments' => array('test_1', 'test_2')), array('test_1', 'test_2'))
        );
    }
}
