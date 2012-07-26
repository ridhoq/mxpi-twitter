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
 * @package     Xcom_Ebay
 * @copyright   Copyright (c) 2010 Magento Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class Xcom_Ebay_Model_Source_SiteTest extends Xcom_TestCase
{
    /** @var Xcom_Ebay_Model_Source_Site */
    protected $_object;


    protected function _getSitesResult()
    {
        return array(
            array('site_code' => 'test_1', 'name' => 'United Test 1'),
            array('site_code' => 'test_2', 'name' => 'United Test 2'),
        );
    }

    public function setUp()
    {
        $this->_checkConnection = true;
        parent::setUp();
        $this->_object = new Xcom_Ebay_Model_Source_Site();
    }

    public function tearDown()
    {
        parent::tearDown();
        $this->_object = null;
    }

    public function testToOptionHashEmpty()
    {
        $mockObject = $this->getMock(get_class($this->_object), array('getSites'));
        $mockObject->expects($this->once())
            ->method('getSites')
            ->will($this->returnValue(array()));
        $this->assertEmpty($mockObject->toOptionHash(false));
    }

    public function testToOptionHash()
    {
        $sites      = $this->_getSitesResult();
        $mockObject = $this->getMock(get_class($this->_object), array('getSites'));
        $mockObject->expects($this->once())
            ->method('getSites')
            ->will($this->returnValue($sites));

        $options = $mockObject->toOptionHash();

        $this->assertArrayHasKey($sites[0]['site_code'], $options);
        $this->assertEquals(count($sites) + 1, count($options));
    }

    public function testToOptionHashWithCustomDefault()
    {
        $mockObject = $this->getMock(get_class($this->_object), array('getSites'));
        $mockObject->expects($this->once())
            ->method('getSites')
            ->will($this->returnValue(array()));
        $options = $mockObject->toOptionHash(true, 'custom_label', 'custom_value');
        $this->assertArrayHasKey('custom_value', $options);
    }

    public function testToOptionArrayEmpty()
    {
        $mockObject = $this->getMock(get_class($this->_object), array('getSites'));
        $mockObject->expects($this->once())
            ->method('getSites')
            ->will($this->returnValue(array()));
        $options = $mockObject->toOptionArray();

        $this->assertArrayHasKey(0, $options);
    }

    public function testToOptionArray()
    {
        $sites      = $this->_getSitesResult();
        $mockObject = $this->getMock(get_class($this->_object), array('getSites'));
        $mockObject->expects($this->once())
            ->method('getSites')
            ->will($this->returnValue($sites));
        $options = $mockObject->toOptionArray();

        $this->assertEquals($sites[0]['site_code'], $options[1]['value']);
        $this->assertEquals(count($options), count($sites) + 1);
    }

    public function testToOptionArrayEmptyWithoutDefault()
    {
        $mockObject = $this->getMock(get_class($this->_object), array('getSites'));
        $mockObject->expects($this->once())
            ->method('getSites')
            ->will($this->returnValue(array()));
        $options = $mockObject->toOptionArray(false);

        $this->assertEmpty($options);
    }

    public function testToOptionArrayWithCustomDefault()
    {
        $mockObject = $this->getMock(get_class($this->_object), array('getSites'));
        $mockObject->expects($this->exactly(2))
            ->method('getSites')
            ->will($this->onConsecutiveCalls(array(), $this->_getSitesResult()));
        //with empty sites
        $options_1 = $mockObject->toOptionArray(true, 'custom_label', 'custom_value');
        $this->assertEquals('custom_value', $options_1[0]['value']);
        $this->assertEquals('custom_label', $options_1[0]['label']);
        $this->assertEquals(count($options_1), 1);

        //with not empty sites
        $options_2 = $mockObject->toOptionArray(true, 'custom_label', 'custom_value');
        $this->assertEquals('custom_value', $options_2[0]['value']);
        $this->assertEquals('custom_label', $options_2[0]['label']);
        $this->assertEquals(count($options_2), count($this->_getSitesResult()) + 1);
    }

    public function testGetSitesByChannelType()
    {
        $siteResourceMock = $this->mockResource('xcom_mmp/site', array('getSites'));
        $siteResourceMock->expects($this->once())
            ->method('getSites')
            ->with(Mage::helper('xcom_ebay')->getChanneltypeCode())
            ->will($this->returnValue($this->_getSitesResult()));

        $this->assertEquals($this->_getSitesResult(), $this->_object->getSites());
    }

    public function testGetSites_withEmptyResults()
    {
        $countryResourceMock = $this->mockResource('xcom_mmp/site', array('getSites'));
        $countryResourceMock->expects($this->once())
            ->method('getSites')
            ->will($this->returnValue(array()));

        $this->assertEmpty($this->_object->getSites());
    }

    public function testGetSites_withFullResults()
    {
        $countryResourceMock = $this->mockResource('xcom_mmp/site', array('getSites'));
        $countryResourceMock->expects($this->once())
            ->method('getSites')
            ->will($this->returnValue($this->_getSitesResult()));

        //first call get data from DB
        $this->_object->getSites();
        //second call - retrieve data from cache
        $this->assertEquals($this->_getSitesResult(), $this->_object->getSites());
    }
}
