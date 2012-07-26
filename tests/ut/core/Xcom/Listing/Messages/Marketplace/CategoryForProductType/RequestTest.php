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
 * @package     Xcom_Listing
 * @copyright   Copyright (c) 2010 Magento Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class Xcom_Listing_Model_Message_Marketplace_CategoryForProductType_Search_RequestTest extends Xcom_TestCase
{
    /** @var Xcom_Listing_Model_Message_Marketplace_CategoryForProductType_Search_Request */
    protected $_object;
    protected $_instanceOf = 'Xcom_Listing_Model_Message_Marketplace_CategoryForProductType_Search_Request';

    public function setUp()
    {
        parent::setUp();
        $this->_object = Mage::helper('xcom_xfabric')->getMessage('marketplace/categoryForProductType/search');
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

    public function testSchema()
    {
        $this->_disableSchema();
        $this->_object->process(new Varien_Object());
        $messageData = $this->_object->getMessageData();

        $this->assertArrayHasKey('xProductTypeId', $messageData);
        $this->assertArrayHasKey('siteCode', $messageData);
        $this->assertArrayHasKey('environmentName', $messageData);
    }

    public function testProcess()
    {
        $this->_disableSchema();
        $data = array(
            'product_type_id'      => 'test_type_id',
            'siteCode'             => 'test_US',
            'environmentName'      => 'test_name'
        );
        $this->_object->process(new Varien_Object($data));
        $messageData = $this->_object->getMessageData();

        $this->assertEquals($data['product_type_id'], $messageData['xProductTypeId']);
        $this->assertEquals($data['siteCode'], $messageData['siteCode']);
        $this->assertEquals($data['environmentName'], $messageData['environmentName']);
    }

    protected function _disableSchema()
    {
        $objectMock = $this->getMock(get_class($this->_object), array('_initSchema', 'encode', 'setEncoder'));
        $this->_object = $objectMock;
    }
}
