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
class Mage_Selenium_Helper_UimapTest extends Mage_PHPUnit_TestCase
{
    /**
     * @covers Mage_Selenium_Helper_Uimap::__construct
     */
    public function test__construct()
    {
        $uimapHelper = $this->_config->getHelper('uimap');
        $this->assertInstanceOf('Mage_Selenium_Helper_Uimap', $uimapHelper);
    }

    /**
     * @covers Mage_Selenium_Helper_Uimap::getUimapPage
     */
    public function testGetUimapPage()
    {
        $uimapHelper = $this->_config->getHelper('uimap');
        $uipage = $uimapHelper->getUimapPage('admin', 'create_customer');
        $this->assertInstanceOf('Mage_Selenium_Uimap_Page', $uipage);
    }

    /**
     * @covers Mage_Selenium_Helper_Uimap::getUimapPage
     */
    public function testGetUimapPageWrongPageException()
    {
        $uimapHelper = $this->_config->getHelper('uimap');
        $this->setExpectedException('OutOfRangeException', 'Cannot find page');
        $uimapHelper->getUimapPage('admin', 'wrong_name');
    }

    /**
     * @covers Mage_Selenium_Helper_Uimap::getUimapPageByMca
     */
    public function testGetUimapPageByMca()
    {
        $uimapHelper = $this->_config->getHelper('uimap');
        $uipage = $uimapHelper->getUimapPageByMca('admin', 'customer/new/');
        $this->assertInstanceOf('Mage_Selenium_Uimap_Page', $uipage);
    }

    /**
     * @covers Mage_Selenium_Helper_Uimap::getUimapPageByMca
     */
    public function testGetUimapPageByMcaWrongPageException()
    {
        $uimapHelper = $this->_config->getHelper('uimap');
        $this->setExpectedException('OutOfRangeException', 'Cannot find page with mca');
        $uimapHelper->getUimapPageByMca('admin', 'wrong-path');
    }

    /**
     * @covers Mage_Selenium_Helper_Uimap::getPageUrl
     */
    public function testGetPageUrl()
    {
        $uimapHelper = $this->_config->getHelper('uimap');
        $this->assertStringEndsWith('/home', $uimapHelper->getPageUrl('frontend', 'home'));
        $this->assertStringEndsWith('/permissions_user/', $uimapHelper->getPageUrl('admin', 'manage_admin_users'));
    }

    /**
     * @covers Mage_Selenium_Helper_Uimap::getPageUrl
     */
    public function testGetPageUrlWrongPageException()
    {
        $uimapHelper = $this->_config->getHelper('uimap');
        $this->setExpectedException('OutOfRangeException', 'Cannot find page');
        $uimapHelper->getPageUrl('admin', 'not_existing_page');
    }

    /**
     * @covers Mage_Selenium_Helper_Uimap::getPageUrl
     */
    public function testGetPageUrlEmptyPageException()
    {
        $uimapHelper = $this->_config->getHelper('uimap');
        $this->setExpectedException('OutOfRangeException', 'Cannot find page');
        $uimapHelper->getPageUrl('admin', '');
    }

    /**
     * @covers Mage_Selenium_Helper_Uimap::getPageUrl
     */
    public function testGetPageUrlWrongAreaException()
    {
        $uimapHelper = $this->_config->getHelper('uimap');
        $this->setExpectedException('OutOfRangeException', 'area do not exist');
        $uimapHelper->getPageUrl('admin-bla-bla-bla', 'not_existing_page');
    }

    /**
     * @covers Mage_Selenium_Helper_Uimap::getPageMca
     */
    public function testGetPageMca()
    {
        $uimapHelper = $this->_config->getHelper('uimap');
        $this->assertEquals('home', $uimapHelper->getPageMca('frontend', 'home'));
        $this->assertEquals('permissions_user/', $uimapHelper->getPageMca('admin', 'manage_admin_users'));
    }
}