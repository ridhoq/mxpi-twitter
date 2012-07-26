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

class Xcom_Mmp_Message_Marketplace_Authorization_Init_RequestTest extends Xcom_TestCase
{
    /**
     * @var Xcom_Mmp_Model_Message_Marketplace_Authorization_Init_Request
     */
    protected $_object;

    protected $_instanceOf = 'Xcom_Mmp_Model_Message_Marketplace_Authorization_Init_Request';
    public function setUp()
    {
        parent::setUp();
        $this->_object = Mage::helper('xcom_xfabric')->getMessage('marketplace/authorization/init');
    }

    public function tearDown()
    {
        parent::tearDown();
        $this->_object = null;
    }

    public function testInstanceOf()
    {
        $this->assertInstanceOf($this->_instanceOf, $this->_object);
    }

    public function testProcessData()
    {
        $this->_mockProcess();

        $dataObject = new Varien_Object(array(
            'return_url'         => 'test_return_url',
            'cancel_url'         => 'test_cancel_url',
            'environment_name'   => 'test_env_name',
            'guid'              => 'test_guid',
            'user_marketplace_id' => 'test_user_mp_id'
        ));
        $this->_object->process($dataObject);

        $messageData = $this->_object->getMessageData();
        $this->assertArrayHasKey('returnURL', $messageData);
        $this->assertArrayHasKey('cancelURL', $messageData);
        $this->assertArrayHasKey('environmentName', $messageData);
        $this->assertArrayHasKey('guid', $messageData);
        $this->assertArrayHasKey('userMarketplaceId', $messageData);
    }

    protected function _mockProcess()
    {
        $this->_object = $this->getMock($this->_instanceOf, array('setEncoder', 'prepareHeaders', 'encode'));
        return $this;
    }
}
