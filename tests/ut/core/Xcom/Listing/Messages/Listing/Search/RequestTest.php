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
class Xcom_Listing_Message_Listing_Search_RequestTest extends Xcom_TestCase
{
    /**
     * @var Xcom_Listing_Model_Message_Listing_Search_Request
     */
    protected $_object     = null;

    public function setUp()
    {
        parent::setUp();
        $this->_object = Mage::helper('xcom_xfabric')
          ->getMessage('listing/search');
    }

    public function tearDown()
    {
        $this->_object = null;
    }

    public function testInstanceOf()
    {
        $this->assertInstanceOf('Xcom_Listing_Model_Message_Listing_Search_Request', $this->_object);
    }

    public function testGetTopic()
    {
        $topic = $this->_object->getTopic();
        $this->assertEquals('listing/search', $topic);
    }

    public function testPrepareData()
    {
        $this->mockStoreConfig('xfabric/connection_settings/encoding', 'json');
        $policy = Mage::getModel('xcom_mmp/policy');
        $data = array('policy' => $policy);
        $this->mockModel('xcom_xfabric/encoder_avro', array('encode', 'encodeText'));
        $this->mockModel('xcom_xfabric/schema', array(), FALSE);
        $this->_object->process(new Varien_Object($data));

        $messageData = $this->_object->getMessageData();
        $this->assertArrayHasKey('filter', $messageData);
        $this->assertArrayHasKey('xProfileId', $messageData);
        $this->assertArrayHasKey('endTime', $messageData['filter']);
        $this->assertArrayHasKey('skus', $messageData['filter']);
        $this->assertArrayHasKey('startTime', $messageData['filter']);

    }

    public function testGetXprofileId()
    {
        $options = array(
            'xprofile_id'  => 'test_x_profile_id'
        );
        $dataObject = new Varien_Object($options);
        $this->mockModel('xcom_xfabric/encoder_avro', array('encode', 'encodeText'));
        $this->mockModel('xcom_xfabric/schema', array(), FALSE);
        $this->_object->process($dataObject);
        $messageData = $this->_object->getMessageData();
        $this->assertEquals('test_x_profile_id', $messageData['xProfileId']);
    }

    public function testPrepareDataBinary()
    {
        if (!function_exists('gmp_testbit')) {
            $this->markTestSkipped('GMP PHP extension is not installed. Test skipped');
        }

        $this->mockStoreConfig('xfabric/connection_settings/encoding', 'json');
        $options = array(
            'xprofile_id'  => 'test_x_profile_id',
            'product_skus'  => array('test_sku1', 'test_sku2', 'test_sku3'),
        );
        $this->_object->process(new Varien_Object($options));
        $this->_object->getEncoder()->decode($this->_object);
        $jsonArray = $this->_object->getBody();

        $this->_object->setEncoder('binary');
        $this->_object->process(new Varien_Object($options));
        $this->_object->getEncoder()->decode($this->_object);
        $binaryArray = $this->_object->getBody();
        $this->assertEquals($jsonArray, $binaryArray);
    }
}
