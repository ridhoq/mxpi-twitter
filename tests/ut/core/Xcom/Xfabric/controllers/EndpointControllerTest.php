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
 * @package     Xcom_Xfabric
 * @copyright   Copyright (c) 2010 Magento Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

require_once 'Xcom/Xfabric/controllers/EndpointController.php';

class Xcom_Xfabric_Controllers_EndpointControllerTest extends Xcom_TestCase
{
    public function setUp()
    {
        parent::setUp();
    }

    public function tearDown()
    {
        $this->_object = null;
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
        $this->_object = $this->getMock('Xcom_Xfabric_EndpointController',
                                        $methods,
                                        array($request, $response));
    }

    /**
     * Tests index action
     * actual test performed:
     * - test that message object is got from helper
     * - test that body, topic and header was set on message object
     * - test that decode method was executed
     * - test that process method was executed
     */
    public function testIndexAction()
    {
        $this->markTestIncomplete('Need to be moved to EndpontTest.php');
        $this->_getControllerMock(new Varien_Object(), new Varien_Object(),
            array('_getInput', '_getHeaders', 'validateAuthorizationHeader'));
        $body = array('test_key' => 'test_value');
        $this->_object->expects($this->any())
            ->method('_getInput')
            ->will($this->returnValue(json_encode($body)));

        $headers = array(
            'header_key'    => 'header_value',
            'Authorization' => 'test_authorization'
        );
        $this->_object->expects($this->any())
            ->method('_getHeaders')
            ->will($this->returnValue($headers));
        $this->_object->expects($this->any())
            ->method('validateAuthorizationHeader')
            ->with($headers)
            ->will($this->returnValue(true));

        $responseObject = $this->getMock('Xcom_Xfabric_Model_Message_Response', array('_initSchema','process'));
        $responseObject->expects($this->any())
            ->method('_initSchema')
            ->will($this->returnValue($responseObject));
        $responseObject->expects($this->any())
            ->method('process')
            ->will($this->returnValue($responseObject->setData('processed', true)));

        $topic = 'test/test';
        $helperMock = $this->mockHelper('xcom_xfabric', array('getMessage'));
        $helperMock->expects($this->any())
            ->method('getMessage')
            ->with($this->equalTo($topic), $this->equalTo(true))
            ->will($this->returnValue($responseObject));

        $this->mockStoreConfig('xfabric/connection_settings/encoding',
            Xcom_Xfabric_Model_Message_Abstract::AVRO_JSON);

        $debugMock = $this->mockModel('xcom_xfabric/debug', array('start', 'stop'));

        $this->_object->getRequest()->setData('param', array('topic' => $topic));

        ob_start();
        $this->_object->indexAction();
        ob_clean();

        $this->assertEquals($topic, $responseObject->getTopic());
        $this->assertEquals($body, $responseObject->getBody());
        $this->assertEquals($headers, $responseObject->getHeaders());
        $this->assertTrue($responseObject->getProcessed());
    }

    public function testValidateAuthorizationHeader()
    {
        $this->markTestIncomplete('Need to be moved to EndpontTest.php');
        $helperMock = $this->mockHelper('xcom_xfabric', array('getResponseAuthorizationKey'));
        $helperMock->expects($this->any())
            ->method('getResponseAuthorizationKey')
            ->will($this->returnValue('true'));
        $request = new Varien_Object();
        $response = new Varien_Object();
        $this->_object = new Xcom_Xfabric_EndpointController($request, $response);
        $this->_object->validateAuthorizationHeader(array('Authorization' => 'true'));
    }
    /**
     * @expectedException Mage_Core_Exception
     * @dataProvider providerAuthorizationHeaders
     *
     * @param array $headers
     */
    public function testValidateAuthorizationHeaderException($headers)
    {
        $this->markTestIncomplete('Need to be moved to EndpontTest.php');
        $helperMock = $this->mockHelper('xcom_xfabric', array('getResponseAuthorizationKey'));
                $helperMock->expects($this->any())
                    ->method('getResponseAuthorizationKey')
                    ->will($this->returnValue('true'));
        $request = new Varien_Object();
        $response = new Varien_Object();
        $this->_object = new Xcom_Xfabric_EndpointController($request, $response);
        $this->_object->validateAuthorizationHeader($headers);
    }

    public function providerAuthorizationHeaders()
    {
        return array(
            array(array()), //no Authorization header
            array(array('Authorization' => 'false')), //wrong Authorization
        );
    }
}
