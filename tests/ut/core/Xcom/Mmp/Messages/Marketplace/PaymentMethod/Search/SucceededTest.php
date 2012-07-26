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

class Xcom_Mmp_Message_Marketplace_PaymentMethod_Search_SucceededTest extends Xcom_TestCase
{
    /**
     * @var Xcom_Mmp_Model_Message_Marketplace_PaymentMethod_Search_Succeeded
     */
    protected $_object     = null;

    protected $_messageBody    = array(
        'methods'     => array('AMEX', 'PAYPAL'),
        'marketplace'       => 'test',
        'siteCode'          => 'US',
        'environmentName'   => 'test_env'
    );

    public function setUp()
    {
        parent::setUp();
        $this->_object = Mage::helper('xcom_xfabric')->getMessage('marketplace/paymentMethod/searchSucceeded');
    }

    public function tearDown()
    {
        $this->_object = null;
    }

    public function testInstanceOf()
    {
        $this->assertInstanceOf('Xcom_Mmp_Model_Message_Marketplace_PaymentMethod_Search_Succeeded', $this->_object);
    }

    public function testGetTopic()
    {
        $topic = $this->_object->getTopic();
        $this->assertEquals('marketplace/paymentMethod/searchSucceeded', $topic);
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
        $objectMock = $this->mockModel('xcom_mmp/message_marketplace_paymentMethod_search_succeeded',
                                       array('_savePaymentMethod'));

        $objectMock->expects($this->any())
            ->method('_savePaymentMethod');

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
        $body       =  $this->_messageBody;
        $objectMock = $this->mockModel('xcom_mmp/message_marketplace_paymentMethod_search_succeeded',
                                       array('_savePaymentMethod'));

        $objectMock->expects($this->once())
            ->method('_savePaymentMethod')
            ->with($this->equalTo($body))
            ->will($this->returnValue($objectMock));

        $objectMock->setBody($body);
        $objectMock->process();
    }

    /**
     * Test that upgrade method gets right parameters
     * for xcom_mmp/paymentMethod resource model
     *
     * @return void
     */
    public function testSavePaymentMethod()
    {
        $body   =  $this->_messageBody;

        $resourceData           = $this->_messageBody['methods'];
        $resourceMock   = $this->mockResource('xcom_mmp/paymentMethod', array('upgrade'));

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
     * Response data test
     *
     * @return void
     */
    public function testGetResponseData()
    {
        $body = $this->_messageBody;

        $expectedResponse   = $this->_messageBody['methods'];

        $this->_object->setBody($body);
        $response   = $this->_object->getResponseData();
        $this->assertEquals($expectedResponse, $response);
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
                array_diff_assoc($this->_messageBody, array('methods' => $this->_messageBody['methods']))
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
