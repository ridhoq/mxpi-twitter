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
class Mage_Selenium_Helper_ConfigTest extends Mage_PHPUnit_TestCase
{
    public function test__construct()
    {
        $configHelper = new Mage_Selenium_Helper_Config($this->_config);
        $this->assertInstanceOf('Mage_Selenium_Helper_Config', $configHelper);
    }

    /**
     * @covers Mage_Selenium_TestConfiguration::getConfigValue
     */
    public function testGetConfigValue()
    {
        $configHelper = new Mage_Selenium_Helper_Config($this->_config);
        $this->assertInternalType('array', $configHelper->getConfigValue());
        $this->assertNotEmpty($configHelper->getConfigValue());

        $this->assertFalse($configHelper->getConfigValue('invalid-path'));

        $this->assertInternalType('array', $configHelper->getConfigValue('browsers'));
        $this->assertArrayHasKey('default', $configHelper->getConfigValue('browsers'));

        $this->assertInternalType('array', $configHelper->getConfigValue('browsers/default'));
        $this->assertArrayHasKey('browser', $configHelper->getConfigValue('browsers/default'));

        $this->assertInternalType('string', $configHelper->getConfigValue('browsers/default/browser'));
        $this->assertInternalType('int', $configHelper->getConfigValue('browsers/default/port'));
    }

    /**
     * @covers Mage_Selenium_Helper_Config::setArea
     */
    public function testSetArea()
    {
        $configHelper = new Mage_Selenium_Helper_Config($this->_config);
        $this->assertInstanceOf('Mage_Selenium_Helper_Config', $configHelper->setArea('frontend'));
        $this->assertInstanceOf('Mage_Selenium_Helper_Config', $configHelper->setArea('admin'));
    }

    /**
     * @covers Mage_Selenium_Helper_Config::setArea
     */
    public function testSetAreaOutOfRangeException()
    {
        $configHelper = new Mage_Selenium_Helper_Config($this->_config);
        $this->setExpectedException('OutOfRangeException', 'Area with name');
        $configHelper->setArea('invalid-area');
    }

    /**
     * @covers Mage_Selenium_Helper_Config::getArea
     */
    public function testGetArea()
    {
        $configHelper = new Mage_Selenium_Helper_Config($this->_config);
        $configHelper->setArea('frontend');
        $this->assertInternalType('string', $configHelper->getArea());
        $this->assertNotEmpty($configHelper->getArea());
        $this->assertEquals('frontend', $configHelper->getArea());
    }

    /**
     * @covers Mage_Selenium_Helper_Config::getBaseUrl
     */
    public function testGetBaseUrl()
    {
        $configHelper = new Mage_Selenium_Helper_Config($this->_config);
        $this->assertInternalType('string', $configHelper->getBaseUrl());
        $this->assertNotEmpty($configHelper->getBaseUrl());

        $configHelper->setArea('admin');
        $this->assertRegExp('/^(https?:\/\/)?([\da-z\.-]+)\.([a-z\.]{2,6})([\/\w \?=.-]*)*\/?$/',
                            $configHelper->getBaseUrl());

        $configHelper->setArea('frontend');
        $this->assertRegExp('/^(https?:\/\/)?([\da-z\.-]+)\.([a-z\.]{2,6})([\/\w \?=.-]*)*\/?$/',
                            $configHelper->getBaseUrl());
    }

    /**
     * @covers Mage_Selenium_Helper_Config::getDefaultLogin
     */
    public function testGetDefaultLogin()
    {
        $configHelper = new Mage_Selenium_Helper_Config($this->_config);
        $configHelper->setArea('admin');
        $login = $configHelper->getDefaultLogin();
        $this->assertInternalType('string', $login);
        $this->assertNotEmpty($login);
        $configHelper->setArea('frontend');
        $login = $configHelper->getDefaultLogin();
        $this->assertInternalType('string', $login);
    }

    /**
     * @covers Mage_Selenium_Helper_Config::getDefaultPassword
     */
    public function testGetDefaultPassword()
    {
        $configHelper = new Mage_Selenium_Helper_Config($this->_config);
        $configHelper->setArea('admin');
        $password = $configHelper->getDefaultPassword();
        $this->assertInternalType('string', $password);
        $this->assertNotEmpty($password);
        $configHelper->setArea('frontend');
        $password = $configHelper->getDefaultPassword();
        $this->assertInternalType('string', $password);
    }

    /**
     * @covers Mage_Selenium_Helper_Config::getBasePath
     */
    public function testGetBasePath()
    {
        $configHelper = new Mage_Selenium_Helper_Config($this->_config);
        $configHelper->setApplication('mage');
        $configHelper->setArea('admin');
        $uimapPath = $configHelper->getBasePath();
        $this->assertInternalType('string', $uimapPath);
        $this->assertSame($uimapPath, 'admin');
    }

    /**
     * @covers Mage_Selenium_Helper_Config::getFixturesFallbackOrder
     */
    public function testGetFixturesFallbackOrder()
    {
        $configHelper = new Mage_Selenium_Helper_Config($this->_config);
        $configHelper->setApplication('mage');
        $fallbackOrder = $configHelper->getFixturesFallbackOrder();
        $this->assertInternalType('array', $fallbackOrder);
        $this->assertSame($fallbackOrder, array('default'));
        $configHelper->setApplication('enterprise');
        $fallbackOrder = $configHelper->getFixturesFallbackOrder();
        $this->assertInternalType('array', $fallbackOrder);
        $this->assertSame($fallbackOrder, array('default', 'enterprise'));
    }

    /**
     * @covers Mage_Selenium_Helper_Config::getHelpersFallbackOrder
     */
    public function testGetHelpersFallbackOrder()
    {
        $configHelper = new Mage_Selenium_Helper_Config($this->_config);
        $configHelper->setApplication('mage');
        $fallbackOrder = $configHelper->getHelpersFallbackOrder();
        $this->assertInternalType('array', $fallbackOrder);
        $this->assertSame($fallbackOrder, array('Core'));
        $configHelper->setApplication('enterprise');
        $fallbackOrder = $configHelper->getHelpersFallbackOrder();
        $this->assertInternalType('array', $fallbackOrder);
        $this->assertSame($fallbackOrder, array('Core', 'Enterprise'));
    }

    /**
     * @covers Mage_Selenium_Helper_Config::setScreenshotDir
     * @covers Mage_Selenium_Helper_Config::getScreenshotDir
     */
    public function testGetSetScreenshotDir()
    {
        $configHelper = new Mage_Selenium_Helper_Config($this->_config);
        //Default directory
        $this->assertEquals(SELENIUM_TESTS_SCREENSHOTDIR, $configHelper->getScreenshotDir());
        //Create a directory
        $parentDir = 'test testGetSetScreenshotDir';
        $dirName = $parentDir . '/ss-dir-test';
        $this->assertTrue(!is_dir($dirName) || (rmdir($dirName) && rmdir($parentDir)));
        $this->assertInstanceOf('Mage_Selenium_Helper_Config', $configHelper->setScreenshotDir($dirName));
        $this->assertTrue(is_dir($dirName));
        $this->assertEquals($dirName, $configHelper->getScreenshotDir());
        //Set to existing directory
        $this->assertInstanceOf('Mage_Selenium_Helper_Config', $configHelper->setScreenshotDir($dirName));
        $this->assertTrue(is_dir($dirName));
        $this->assertEquals($dirName, $configHelper->getScreenshotDir());
        //Cleanup
        rmdir($dirName); rmdir($parentDir);
    }

    /**
     * @covers Mage_Selenium_Helper_Config::setScreenshotDir
     * @depends testGetSetScreenshotDir
     */
    public function testSetScreenshotDirInvalidPathException()
    {
        $configHelper = new Mage_Selenium_Helper_Config($this->_config);
        $this->setExpectedException('PHPUnit_Framework_Error_Warning', 'mkdir():');
        $configHelper->setScreenshotDir('!#$@%*^&:?');
    }

    /**
     * @covers Mage_Selenium_Helper_Config::setScreenshotDir
     * @depends testGetSetScreenshotDir
     */
    public function testSetScreenshotDirInvalidParameterException()
    {
        $configHelper = new Mage_Selenium_Helper_Config($this->_config);
        $this->setExpectedException('PHPUnit_Framework_Error_Warning', 'mkdir():');
        $configHelper->setScreenshotDir(null);
    }

    /**
     * @covers Mage_Selenium_Helper_Config::setLogDir
     * @covers Mage_Selenium_Helper_Config::getLogDir
     */
    public function testGetSetLogDir()
    {
        $configHelper = new Mage_Selenium_Helper_Config($this->_config);
        //Default directory
        $this->assertEquals(SELENIUM_TESTS_LOGS, $configHelper->getLogDir());
        //Create a directory
        $dirName = 'log-dir-test';
        $this->assertTrue(!is_dir($dirName) || rmdir($dirName));
        $this->assertInstanceOf('Mage_Selenium_Helper_Config', $configHelper->setLogDir($dirName));
        $this->assertTrue(is_dir($dirName));
        $this->assertEquals($dirName, $configHelper->getLogDir());
        //Set to existing directory
        $this->assertInstanceOf('Mage_Selenium_Helper_Config', $configHelper->setLogDir($dirName));
        $this->assertTrue(is_dir($dirName));
        $this->assertEquals($dirName, $configHelper->getLogDir());
        //Cleanup
        rmdir($dirName);
    }

    /**
     * @covers Mage_Selenium_Helper_Config::setLogDir
     * @depends testGetSetLogDir
     */
    public function testSetLogDirInvalidPathException()
    {
        $configHelper = new Mage_Selenium_Helper_Config($this->_config);
        $this->setExpectedException('PHPUnit_Framework_Error_Warning', 'mkdir():');
        $configHelper->setLogDir('!#$@%*^&:?');
    }

    /**
     * @covers Mage_Selenium_Helper_Config::setLogDir
     * @depends testGetSetLogDir
     */
    public function testSetLogDirInvalidParameterException()
    {
        $configHelper = new Mage_Selenium_Helper_Config($this->_config);
        $this->setExpectedException('PHPUnit_Framework_Error_Warning', 'mkdir():');
        $configHelper->setLogDir(null);
    }
}
