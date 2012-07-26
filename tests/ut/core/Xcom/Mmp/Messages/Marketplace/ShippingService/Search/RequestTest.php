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
class Xcom_Mmp_Message_Marketplace_ShippingService_Search_RequestTest extends Xcom_TestCase
{
    /**
     * @var Xcom_Mmp_Model_Message_Marketplace_ShippingService_Search_Request
     */
    protected $_object     = null;

    public function setUp()
    {
        parent::setUp();
        $this->_object = Mage::helper('xcom_xfabric')
            ->getMessage('marketplace/shippingService/search');
    }

    public function tearDown()
    {
        $this->_object = null;
    }

    public function testInstanceOf()
    {
        $this->assertInstanceOf('Xcom_Mmp_Model_Message_Marketplace_ShippingService_Search_Request', $this->_object);
    }

    public function testGetTopic()
    {
        $topic = $this->_object->getTopic();
        $this->assertEquals('marketplace/shippingService/search', $topic);
    }

    public function testProcess()
    {
        $data = new Varien_Object(array('destination_id' => 'test', 'siteCode' => 'US',
                                       'environmentName' => 'sandbox'));
        $this->_object->process($data);

        $data = $this->_object->getMessageData();

        $this->assertEquals($data['siteCode'], 'US', "Site Code is wrong");
        $this->assertEquals($data['environmentName'], 'sandbox', "Environment Name is wrong");
    }
}
