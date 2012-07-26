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
class Xcom_Mmp_Message_Marketplace_HandlingTime_Search_FailedTest extends Xcom_TestCase
{
    /**
     * @var Xcom_Mmp_Model_Message_Marketplace_HandlingTime_Search_Failed
     */
    protected $_object     = null;

    public function setUp()
    {
        parent::setUp();
        $this->_object = Mage::helper('xcom_xfabric')
          ->getMessage('marketplace/handlingTime/searchFailed');
    }

    public function tearDown()
    {
        $this->_object = null;
    }

    public function testInstanceOf()
    {
        $this->assertInstanceOf('Xcom_Mmp_Model_Message_Marketplace_HandlingTime_Search_Failed', $this->_object);
    }

    public function testGetTopic()
    {
        $topic = $this->_object->getTopic();
        $this->assertEquals('marketplace/handlingTime/searchFailed', $topic);
    }

    public function testPrepareResponseDataEmpty()
    {
        $result = $this->_object->getResponseData();
        $this->assertEmpty($result);
    }

    /**
     * Process method test
     *
     * @return void
     */
    public function testProcess()
    {
        $body = array(
            'errors'     => array(
                array(
                    'severity'  => 'ERROR',
                    'code'      => '007',
                    'message'   => 'Handling type error message'
                )
            ),
            'marketplace'       => 'test',
            'siteCode'          => 'US',
            'environmentName'   => 'test_env'
        );

        $this->_object->setBody($body);
        $result   = $this->_object->process();
        $this->assertInstanceOf('Xcom_Mmp_Model_Message_Marketplace_HandlingTime_Search_Failed', $result);
    }

    /**
     * Response data test
     *
     * @return void
     */
    public function testGetResponseData()
    {
        $body = array(
            'errors'     => array(
                array(
                    'severity'  => 'ERROR',
                    'code'      => '007',
                    'message'   => 'Handling type error message'
                )
            ),
            'marketplace'       => 'test',
            'siteCode'          => 'US',
            'environmentName'   => 'test_env'
        );

        $this->_object->setBody($body);
        $response   = $this->_object->getResponseData();
        $this->assertEquals(array(), $response);
    }
}
