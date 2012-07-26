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
 * @category    tests
 * @package     selenium unit tests
 * @subpackage  Mage_PHPUnit
 * @author      Magento Core Team <core@magentocommerce.com>
 * @copyright   Copyright (c) 2010 Magento Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class Mage_Selenium_TestConfigurationTest extends Mage_PHPUnit_TestCase
{
    /**
     * @covers Mage_Selenium_TestConfiguration::getInstance
     * @covers Mage_Selenium_TestConfiguration::init
     */
    public function testGetInstance()
    {
        $instance = Mage_Selenium_TestConfiguration::getInstance();
        $this->assertInstanceOf('Mage_Selenium_TestConfiguration', $instance);
        $this->assertEquals($instance, Mage_Selenium_TestConfiguration::getInstance());
        $this->assertAttributeInstanceOf('Mage_Selenium_Helper_Config', '_configHelper', $instance);
        $this->assertAttributeInternalType('array', '_configFixtures', $instance);
        $this->assertAttributeInstanceOf('Mage_Selenium_Helper_Uimap', '_uimapHelper', $instance);
        $this->assertAttributeInstanceOf('Mage_Selenium_Helper_Data', '_dataHelper', $instance);
    }

    /**
     * @covers Mage_Selenium_TestConfiguration::getHelper
     */
    public function testGetHelper()
    {
        $instance = Mage_Selenium_TestConfiguration::getInstance();
        $this->assertInstanceOf('Mage_Selenium_Helper_Cache', $instance->getHelper('cache'));
        $this->assertInstanceOf('Mage_Selenium_Helper_Config', $instance->getHelper('config'));
        $this->assertInstanceOf('Mage_Selenium_Helper_Data', $instance->getHelper('data'));
        $this->assertInstanceOf('Mage_Selenium_Helper_DataGenerator', $instance->getHelper('dataGenerator'));
        $this->assertInstanceOf('Mage_Selenium_Helper_File', $instance->getHelper('file'));
        $this->assertInstanceOf('Mage_Selenium_Helper_Params', $instance->getHelper('params'));
        $this->assertInstanceOf('Mage_Selenium_Helper_Uimap', $instance->getHelper('uimap'));
        $this->assertInstanceOf('Mage_Selenium_Helper_Uimap', $instance->getHelper('Uimap'));

        $sameValue = $instance->getHelper('cache');
        $this->assertEquals($sameValue, $instance->getHelper('cache'));
    }

    /**
     * @covers Mage_Selenium_TestConfiguration::getHelper
     * @expectedException OutOfRangeException
     */
    public function testGetHelperException()
    {
        $instance = Mage_Selenium_TestConfiguration::getInstance();
        $instance->getHelper('NotExistingHelper');
    }

    /**
     * @covers Mage_Selenium_TestConfiguration::getConfigFixtures
     */
    public function testGetConfigFixtures()
    {
        $instance = Mage_Selenium_TestConfiguration::getInstance();
        $sameValue = $instance->getConfigFixtures();
        $this->assertEquals($sameValue, $instance->getConfigFixtures());
        $this->assertInternalType('array', $sameValue);
        $this->assertNotEmpty($sameValue);
        $this->assertInternalType('array', current($sameValue));
        $this->assertNotEmpty(current($sameValue));
    }
}