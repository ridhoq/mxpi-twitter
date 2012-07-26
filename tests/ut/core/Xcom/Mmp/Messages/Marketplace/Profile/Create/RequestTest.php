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

class Xcom_Mmp_Message_Marketplace_Profile_Create_RequestTest extends Xcom_TestCase
{
    /** @var Xcom_Mmp_Model_Message_Marketplace_Profile_Create_Request */
    protected $_object;

    public function setUp()
    {
        parent::setUp();
        $this->_object = Mage::helper('xcom_xfabric')->getMessage('marketplace/profile/create');
    }

    public function tearDown()
    {
        $this->_object = null;
    }

    protected function _getDefaultDataObject()
    {
        $dataObject = new Varien_Object(array(
            'destination_id' => 'SDID',
            'policy' => new Varien_Object(array(
                'name' => 'test_name',
                'channel_id' => 'SomeId',
                'shipping_data' => array(
                    1 => array('shipping_id' => 1, 'sort_order' => 1, 'cost' => 2),
                    2 => array('shipping_id' => 2, 'sort_order' => 2, 'cost' => 3)
                ),
                'payment_name' => array('VISA', 'CHECK', 'WTF?')
            )),
            'channel' => new Varien_Object(array(
                'auth_id'       => 'some auth_id',
                'xaccount_id'   => 'test_xaccount_id',
                'site_code'     => 'test_site_code'
            )),
        ));
        return $dataObject;
    }

    public function testProcess()
    {
        $this->_mockReturnPolicyResource();
        /** @var $ob Xcom_Mmp_Model_Message_Marketplace_Profile_Create_Request */
        $ob = $this->_object;
        $ob->process($this->_getDefaultDataObject());
        $data = $ob->getMessageData();

        $this->assertTrue(isset($data['p']), "data['p'] is not set");
        $data = $data['p'];

        $this->assertEquals('test_xaccount_id', $data['xAccountId'], 'Wrong account Id');

        $this->assertEquals('test_name', $data['name'], "Name is wrong");
        $this->assertEquals('test_site_code', $data['siteCode'], "SiteCode is wrong");

        $this->assertEquals(array('VISA','CHECK'), $data['payment']['acceptedPaymentTypes'],  "Payment is wrong");
        $this->assertEquals( 2,count($data['shipping']['shippingLocaleServices'][0]['shippingServiceOptions']),
            "Wrong number of ShippingServices");

        $this->assertEquals( 'test_one',
            $data['shipping']['shippingLocaleServices'][0]['shippingServiceOptions'][0]['serviceName'],
            "Wrong name for first shipping service");
        $this->assertEquals(1,
            $data['shipping']['shippingLocaleServices'][0]['shippingServiceOptions'][0]['sellerPriority'],
            "Wrong priority for first shipping service");
        $this->assertEquals('test_two',
            $data['shipping']['shippingLocaleServices'][0]['shippingServiceOptions'][1]['serviceName'],
            "Wrong name for second shipping service");
        $this->assertEquals(2,
            $data['shipping']['shippingLocaleServices'][0]['shippingServiceOptions'][1]['sellerPriority'],
            "Wrong priority for second shipping service");
    }


    public function testProcessReturnPolicy()
    {
        $this->_mockReturnPolicyResource();
        $dataObject = $this->_getDefaultDataObject();
        /**
         * US without return accepted
         */
        $channel = $dataObject->getChannel();
        $channel->setSiteCode('US');
        $dataObject->setChannel($channel);
        $policy = $dataObject->getPolicy();
        $policy->setReturnAccepted(false);
        $dataObject->setPolicy($policy);
        $expectedResult = array(
            'description'               => null,
            'returnAccepted'            => false,
            'buyerPaysReturnShipping'   => null,
            'returnByDays'              => null,
            'refundMethod'              => null
        );
        /** @var $ob Xcom_Mmp_Model_Message_Marketplace_Profile_Create_Request */
        $ob = $this->_object;
        $ob->process($dataObject);
        $data = $ob->getMessageData();
        $data = $data['p']['returnPolicy'];
        $this->assertEquals($expectedResult, $data, 'Wrong returnPolicy for US without return accepted');


        /**
         * UK without return accepted
         */
        $channel = $dataObject->getChannel();
        $channel->setSiteCode('UK');
        $dataObject->setChannel($channel);

        $ob->process($dataObject);
        $data = $ob->getMessageData();
        $data = $data['p']['returnPolicy'];
        $this->assertEquals($expectedResult, $data, 'Wrong returnPolicy for UK without return accepted');


        /**
         * UK with return accepted
         */
        $policy = $dataObject->getPolicy();
        $policy->setReturnAccepted(true);
        $dataObject->setPolicy($policy);
        $expectedResult['returnAccepted'] = true;

        $ob->process($dataObject);
        $data = $ob->getMessageData();
        $data = $data['p']['returnPolicy'];
        $this->assertEquals($expectedResult, $data, 'Wrong returnPolicy for UK with return accepted');

        /**
         * US with return accepted but empty policy data
         */
        $channel = $dataObject->getChannel();
        $channel->setSiteCode('US');
        $dataObject->setChannel($channel);

        $ob->process($dataObject);
        $data = $ob->getMessageData();
        $data = $data['p']['returnPolicy'];
        $this->assertEquals($expectedResult, $data, 'Wrong returnPolicy for US with return accepted but empty data');

        /**
         * US with return accepted and full data and shipping Paid by buyer
         */
        $policy = $dataObject->getPolicy();
        $policy->setReturnDescription('test_description');
        $policy->setShippingPaidBy('buyer');
        $policy->setReturnByDays('30');
        $policy->setRefundMethod('MONEY_BACK');
        $dataObject->setPolicy($policy);

        $expectedResult = array(
            'description'               => 'test_description',
            'returnAccepted'            => true,
            'buyerPaysReturnShipping'   => true,
            'returnByDays'              => 30,
            'refundMethod'              => 'MONEY_BACK'
        );

        $ob->process($dataObject);
        $data = $ob->getMessageData();
        $data = $data['p']['returnPolicy'];
        $this->assertEquals($expectedResult, $data, 'Wrong returnPolicy for US with full data, shipping paid by buyer');

        /**
         * US with return accepted and full data and shipping Paid by seller
         */
        $policy = $dataObject->getPolicy();
        $policy->setShippingPaidBy('seller');
        $dataObject->setPolicy($policy);

        $expectedResult['buyerPaysReturnShipping'] = false;

        $ob->process($dataObject);
        $data = $ob->getMessageData();
        $data = $data['p']['returnPolicy'];
        $this->assertEquals($expectedResult, $data, 'Wrong returnPolicy for US, full data, shipping paid by seller');
    }

    public function testCorrelationIdHeader()
    {
        $options = $this->_getDefaultDataObject();
        $options->getPolicy()->setCorrelationId('test_correlation_id');
        $shippingService = $this->mockResource('xcom_mmp/shippingService', array('getShippingServices'));
        $shippingService->expects($this->once())
            ->method('getShippingServices')
            ->will($this->returnValue(
                array(
                    array('shipping_id' => 1),
                    array('service_name' => 'test_name')
                ),
                array(
                    array('shipping_id' => 2),
                    array('service_name' => 'test_name2')
                )
            ));
        $avro = $this->mockModel('xcom_xfabric/encoder_avro', array());
        $json = $this->mockModel('xcom_xfabric/encoder_json', array());

        $this->_object->process($options);

        $this->assertArrayHasKey('X-XC-RESULT-CORRELATION-ID', $this->_object->getHeaders());
        $this->assertContains($this->_object->getCorrelationId(), $this->_object->getHeaders());
    }

    protected function _mockReturnPolicyResource()
    {
        $returnPolicyResourceMock   = $this->mockResource('xcom_mmp/shippingService', array('getShippingServices'));
        $returnPolicyResourceMock->expects($this->any())
            ->method('getShippingServices')
            ->will($this->returnValue(array(
                array('shipping_id' => 1, 'service_name' => 'test_one'),
                array('shipping_id' => 2, 'service_name' => 'test_two'),
                array('shipping_id' => 4, 'service_name' => 'test_two2'),
            )));
    }
}
