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
 * @package     Xcom_Mapping
 * @copyright   Copyright (c) 2010 Magento Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class Xcom_Mapping_Model_Message_ProductTaxonomy_ProductType_Get_RequestTest extends Xcom_TestCase
{
    /** @var Xcom_Mapping_Model_Message_ProductTaxonomy_ProductType_Search_Request */
    protected $_object;
    protected $_instanceOf = 'Xcom_Mapping_Model_Message_ProductTaxonomy_ProductType_Get_Request';

    public function setUp()
    {
        parent::setUp();
        $this->_object = Mage::helper('xcom_xfabric')->getMessage('productTaxonomy/productType/get');
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

        $this->assertArrayHasKey('locale', $messageData);
        $this->assertArrayHasKey('filter', $messageData);

        $this->assertArrayHasKey('detailLevel', $messageData['filter']);
        $this->assertArrayHasKey('channelIds', $messageData['filter']);
    }

    public function testSchemaLocale()
    {
        $this->_disableSchema();
        $this->_object->process(new Varien_Object(array(
            'country' => 'test_country',
            'language' => 'test_language'
        )));
        $messageData = $this->_object->getMessageData();

        $this->assertArrayHasKey('locale', $messageData);

        $this->assertArrayHasKey('language', $messageData['locale']);
        $this->assertArrayHasKey('country', $messageData['locale']);
        $this->assertArrayHasKey('variant', $messageData['locale']);

    }

    public function testProcess()
    {
        $this->_disableSchema();
        $data = array(
            'language'      => 'en',
            'country'       => 'US',
            'variant'       => 'test_variant',
            'channelIds'    => array('ebay_1', 'ebay_2')
        );
        $this->_object->process(new Varien_Object($data));
        $messageData = $this->_object->getMessageData();

        $this->assertEquals($data['product_class_id'], $messageData['criteria']['productClassId']);
        $this->assertEquals($data['language'], $messageData['locale']['language']);
        $this->assertEquals($data['country'], $messageData['locale']['country']);
        //$this->assertEquals($data['variant'], $messageData['locale']['variant']);
        $this->assertEquals(2, $messageData['filter']['detailLevel']);
        $this->assertEquals($data['channel_ids'], $messageData['filter']['channel_ids']);
    }

    protected function _disableSchema()
    {
        $objectMock = $this->getMock(get_class($this->_object), array('_initSchema', 'encode', 'setEncoder'));
        $this->_object = $objectMock;
    }
}
