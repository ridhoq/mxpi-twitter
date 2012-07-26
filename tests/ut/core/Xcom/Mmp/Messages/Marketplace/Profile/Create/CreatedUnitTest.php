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
class Xcom_Mmp_Message_Marketplace_Profile_Create_CreatedUnitTest extends Xcom_TestCase
{
    /** @var Xcom_Mmp_Model_Message_Marketplace_Profile_Create_Created */
    protected $_object;
    protected $_instanceOf = 'Xcom_Mmp_Model_Message_Marketplace_Profile_Create_Created';

    public function setUp()
    {
        parent::setUp();
        $this->_object = Mage::helper('xcom_xfabric')->getMessage('marketplace/profile/created');
        $this->_object->setEncoding(Xcom_Xfabric_Model_Message_Abstract::AVRO_JSON);
    }

    public function tearDown()
    {
        $this->_object = null;
    }

    public function testInstanceOf()
    {
        $this->assertInstanceOf($this->_instanceOf, $this->_object);
    }

    public function testGetTopic()
    {
        $this->assertEquals('marketplace/profile/created', $this->_object->getTopic());
    }

    public function testProcess()
    {
        $this->_object->setBody(array('p' => array()));
        $this->assertInstanceOf($this->_instanceOf, $this->_object->process());
    }

    public function testSavePolicy()
    {
        $headers = array(
            'X-XC-RESULT-CORRELATION-ID' => '01234567890123456789012345678901'
        );
        $this->_object->setHeaders($headers);

        $policyMock = $this->mockModel('xcom_mmp/policy', array('load', 'setXprofileId', 'save'));
        $policyMock->expects($this->once())
            ->method('load')
            ->with($this->equalTo('01234567890123456789012345678901'), $this->equalTo('correlation_id'))
            ->will($this->returnValue($policyMock));
        $policyMock->expects($this->once())
            ->method('setXprofileId')
            ->with($this->equalTo('123456789'))
            ->will($this->returnValue($policyMock));
        $policyMock->expects($this->once())
            ->method('save')
            ->will($this->returnValue($policyMock));

        $this->assertInstanceOf($this->_instanceOf, $this->_object->savePolicy(array('xId' => '123456789')));
    }
}
