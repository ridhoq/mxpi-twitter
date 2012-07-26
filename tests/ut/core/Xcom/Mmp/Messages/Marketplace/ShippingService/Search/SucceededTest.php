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

class Xcom_Mmp_Message_Marketplace_ShippingService_Search_SucceededTest extends Xcom_TestCase
{
    /**
     * @var Xcom_Mmp_Model_Message_Marketplace_ShippingService_Search_Succeeded
     */
    protected $_object     = null;

    protected $_messageBody    = array(
        'services'     => array(
            array(
                'description'           => 'test_description',
                'carrier'               => 'test carrier',
                'serviceName'           => 'Shipping test',
                'shippingTimeMax'       => 10,
                'shippingTimeMin'       => 3,
                'rateType'              => null,
                'dimensionsRequired'    => false,
                'weightRequired'        => true,
                'surchargeApplicable'   => true,
            )
        ),
        'marketplace'       => 'test',
        'siteCode'          => 'US',
        'environmentName'   => 'test_env'
    );

    public function setUp()
    {
        parent::setUp();
        $this->_object = Mage::helper('xcom_xfabric')
          ->getMessage('marketplace/shippingService/searchSucceeded');
    }

    public function tearDown()
    {
        $this->_object = null;
    }

    public function testInstanceOf()
    {
        $this->assertInstanceOf('Xcom_Mmp_Model_Message_Marketplace_ShippingService_Search_Succeeded', $this->_object);
    }

    public function testGetTopic()
    {
        $topic = $this->_object->getTopic();
        $this->assertEquals('marketplace/shippingService/searchSucceeded', $topic);
    }

    /**
     * Test process with wrong schema in body message
     *
     * @dataProvider providerValidateSchema
     *
     * @param array $body
     * @return void
     */
    public function testProcessWithUnsuccessfulValidation($body)
    {
        $objectMock = $this->mockModel('xcom_mmp/message_marketplace_shippingService_search_succeeded',
                                       array('_saveShippingServices'));

        $objectMock->expects($this->any())
            ->method('_saveShippingServices');

        $objectMock->setBody($body);
        $objectMock->process();
    }

    /**
     * Test process
     *
     * @return void
     */
    public function testProcessWithSuccessfulValidation()
    {
        $body   =  $this->_messageBody;
        $objectMock = $this->mockModel('xcom_mmp/message_marketplace_shippingService_search_succeeded',
                                       array('_saveShippingServices'));

        $objectMock->expects($this->once())
            ->method('_saveShippingServices')
            ->with($this->equalTo($body))
            ->will($this->returnValue($objectMock));

        $objectMock->setBody($body);
        $objectMock->process();
    }

    /**
     * Test that upgrade method gets right parameters
     * for xcom_mmp/shippingService resource model
     *
     * @return void
     */
    public function testSaveShippingServices()
    {
        $body   =  $this->_messageBody;

        $resourceData           = array(array(
            'description'           => 'test_description',
            'carrier'               => 'test carrier',
            'service_name'          => 'Shipping test',
            'shipping_time_max'     => 10,
            'shipping_time_min'     => 3,
            'rate_type'             => null,
            'dimensions_required'   => false,
            'weight_required'       => true,
            'surcharge_applicable'  => true,

        ));
        $resourceMock   = $this->mockResource('xcom_mmp/shippingService', array('upgrade'));

        $resourceMock->expects($this->once())
            ->method('upgrade')
            ->with($this->equalTo($body['marketplace']),
                   $this->equalTo($body['siteCode']),
                   $this->equalTo($body['environmentName']),
                   $this->equalTo($resourceData));

        $this->_object->setBody($body);
        $this->_object->process();
    }

    /**
     * Response data test with empty services
     *
     * @return void
     */
    public function testGetResponseData_Empty()
    {
        $body = array_diff_assoc($this->_messageBody, array('services' => $this->_messageBody['services']));
        $this->_object->setBody($body);
        $response   = $this->_object->getResponseData();

        $this->assertEmpty($response);
    }
    /**
     * Response data test with services
     *
     * @return void
     */
    public function testGetResponseData_Full()
    {
        $body = $this->_messageBody;
        $this->_object->setBody($body);
        $response   = $this->_object->getResponseData();

        $this->assertArrayHasKey('service_name', $response[0]);
    }

    /**
     * Provider data with wrong schema data
     * for testProcessWithUnsuccessfulValidation
     *
     * @return array
     */
    public function providerValidateSchema()
    {
        return array(
            array(
                array_diff_assoc($this->_messageBody, array('services' => $this->_messageBody['services']))
            ),
            array(
                array_diff_assoc($this->_messageBody, array('marketplace' => $this->_messageBody['marketplace']))
            ),
            array(
                array_diff_assoc($this->_messageBody, array('siteCode' => $this->_messageBody['siteCode']))
            ),
            array(
                array_diff_assoc($this->_messageBody,
                                 array('environmentName' => $this->_messageBody['environmentName']))
            )
        );
    }
}
