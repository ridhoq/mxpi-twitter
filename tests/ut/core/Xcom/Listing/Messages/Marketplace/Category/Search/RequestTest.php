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

class Xcom_Listing_Message_Marketplace_Category_Search_RequestTest extends Xcom_TestCase
{
    public function setUp()
    {
        parent::setUp();
        $this->_object = Mage::helper('xcom_xfabric')->getMessage('marketplace/category/search');
    }

    public function tearDown()
    {
        $this->_object = null;
    }

    public function testPrepareData()
    {
        $data = new Varien_Object();
        $this->mockModel('xcom_xfabric/encoder_avro', array('encode', 'encodeText'));
        $this->mockModel('xcom_xfabric/schema', array(), FALSE);
        $this->_object->process($data);

        $data = $this->_object->getMessageData();
        $this->assertTrue(is_array($data), "Wrong data");

        $this->assertEquals($data['siteCode'], 'US', "Wrong SiteCode");
    }
}
