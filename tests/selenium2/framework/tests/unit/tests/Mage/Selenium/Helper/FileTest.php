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
class Mage_Selenium_Helper_FileTest extends Mage_PHPUnit_TestCase
{
    public function test__construct()
    {
        $fileHelper = new Mage_Selenium_Helper_File($this->_config);
        $this->assertInstanceOf('Mage_Selenium_Helper_File', $fileHelper);
    }

    /**
     * @covers Mage_Selenium_Helper_File::loadYamlFile
     * @depends test__construct
     */
    public function testLoadYamlFile()
    {
        $fileHelper = new Mage_Selenium_Helper_File($this->_config);
        $filePath = SELENIUM_TESTS_BASEDIR . DIRECTORY_SEPARATOR . 'fixture' . DIRECTORY_SEPARATOR . 'default'
                . DIRECTORY_SEPARATOR . 'Core' . DIRECTORY_SEPARATOR . 'Mage' . DIRECTORY_SEPARATOR . 'Customer'
                . DIRECTORY_SEPARATOR . 'data' . DIRECTORY_SEPARATOR . 'Customers.yml';
        $customers = $fileHelper->loadYamlFile($filePath);

        $this->assertInternalType('array', $customers);
        $this->assertNotEmpty($customers);
        $this->assertGreaterThanOrEqual(5, count($customers));
        $this->assertArrayHasKey('customer_account_register', $customers);
        $this->assertArrayHasKey('generic_customer_account', $customers);
        $this->assertArrayHasKey('all_fields_customer_account', $customers);
        $this->assertArrayHasKey('generic_address', $customers);
        $this->assertArrayHasKey('all_fields_address', $customers);
        $this->assertArrayHasKey('first_name', $customers['customer_account_register']);

        $this->assertFalse($fileHelper->loadYamlFile(''));
        $this->assertFalse($fileHelper->loadYamlFile('some_file.yml'));
    }

    /**
     * @covers Mage_Selenium_Helper_File::loadYamlFile
     * @depends test__construct
     *
     * @expectedException InvalidArgumentException
     */
    public function testLoadYamlFileException()
    {
        $fileHelper = new Mage_Selenium_Helper_File($this->_config);
        $this->assertFalse($fileHelper->loadYamlFile(SELENIUM_TESTS_BASEDIR . DIRECTORY_SEPARATOR . 'phpunit.xml'));
    }

    /**
     * @covers Mage_Selenium_Helper_File::loadYamlFiles
     * @depends test__construct
     */
    public function testLoadYamlFiles()
    {
        $fileHelper = new Mage_Selenium_Helper_File($this->_config);
        $filePath = SELENIUM_TESTS_BASEDIR . DIRECTORY_SEPARATOR . 'fixture' . DIRECTORY_SEPARATOR . 'default'
                . DIRECTORY_SEPARATOR . '*' . DIRECTORY_SEPARATOR . '*' . DIRECTORY_SEPARATOR . '*'
                . DIRECTORY_SEPARATOR . 'data' . DIRECTORY_SEPARATOR . '*.yml';
        $allYmlData = $fileHelper->loadYamlFiles($filePath);

        $this->assertInternalType('array', $allYmlData);
        $this->assertNotEmpty($allYmlData);
        $this->assertGreaterThanOrEqual(25, count($allYmlData));

        $this->assertEmpty($fileHelper->loadYamlFiles(''));
        $this->assertEmpty($fileHelper->loadYamlFiles('*.yml'));
    }
}