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
class Mage_Selenium_Helper_DataGeneratorTest extends Mage_PHPUnit_TestCase
{
    public function test__construct()
    {
        $dataGenerator = new Mage_Selenium_Helper_DataGenerator($this->_config);
        $this->assertInstanceOf('Mage_Selenium_Helper_DataGenerator', $dataGenerator);
    }

    /**
     * @covers Mage_Selenium_Helper_DataGenerator::generate
     * @depends test__construct
     */
    public function testGenerate()
    {
        $dataGenerator = new Mage_Selenium_Helper_DataGenerator($this->_config);
        // Default values
        $this->assertInternalType('string', $dataGenerator->generate());
        $this->assertEquals(100, strlen($dataGenerator->generate()));

        // String generations
        $this->assertEquals(20, strlen($dataGenerator->generate('string', 20, ':alnum:')));
        $this->assertEquals(20, strlen($dataGenerator->generate('string', 20, ':alnum:', '')));
        $this->assertEmpty($dataGenerator->generate('string', 0, ':alnum:', ''));
        $this->assertEmpty($dataGenerator->generate('string', -1, ':alnum:', ''));
        $this->assertEquals(1000000, strlen($dataGenerator->generate('string', 1000000, ':alnum:', '')));

        $this->assertEquals(26, strlen($dataGenerator->generate('string', 20, ':alnum:', 'prefix')));
        $this->assertStringStartsWith('prefix', $dataGenerator->generate('string', 20, '', 'prefix'));

        $this->assertStringMatchesFormat('%s', $dataGenerator->generate('string', 20, ':alnum:'));
        $this->assertStringMatchesFormat('%d', $dataGenerator->generate('string', 20, ':digit:'));

        // Text generations
        $this->assertEquals(26, strlen($dataGenerator->generate('text', 20, '', 'prefix')));
        $this->assertStringStartsWith('prefix', $dataGenerator->generate('text', 20, '', 'prefix'));

        $this->assertEquals(100, strlen($dataGenerator->generate('text')));
        $this->assertEquals(20, strlen($dataGenerator->generate('text', 20)));
        $this->assertEmpty($dataGenerator->generate('text', 0));
        $this->assertEmpty($dataGenerator->generate('text', -1));
        $this->assertEquals(1000000, strlen($dataGenerator->generate('text', 1000000)));

        $this->assertEquals(20, strlen($dataGenerator->generate('text', 20, '')));
        $this->assertEquals(26, strlen($dataGenerator->generate('text', 20, '', 'prefix')));
        $this->assertStringStartsWith( 'prefix', $dataGenerator->generate('text', 20, '', 'prefix'));

        $this->assertStringMatchesFormat('%s', $dataGenerator->generate('text', 20, array('class'=>':alnum:')));
        $this->assertRegExp('/[0-9 ]+/', $dataGenerator->generate('text', 20, array('class'=>':digit:')));

        // Email generations
        $this->assertEquals(100, strlen($dataGenerator->generate('email')));
        $this->assertEquals(20, strlen($dataGenerator->generate('email', 20, 'valid')));
        $this->assertEquals(20, strlen($dataGenerator->generate('email', 20, 'some_value')));
        $this->assertEmpty($dataGenerator->generate('email', 0));
        $this->assertEmpty($dataGenerator->generate('email', -1));
        $this->assertEquals(255, strlen($dataGenerator->generate('email', 255, 'valid')));

        $this->assertRegExp("/^([a-z0-9,!\#\$%&'\*\+\/=\?\^_`\{\|\}~-])+(\.([a-z0-9,!\#\$%&'\*\+\/=\?\^_`\{\|\}~-])+)*@([a-z0-9-])+(\.([a-z0-9-])+)*\.(([a-z]){2,})$/i",
                $dataGenerator->generate('email', 20, 'valid'));
        $this->assertRegExp('|([a-z0-9_\.\-]+)@([a-z0-9\.\-]+)\.([a-z]{2,4})|is', $dataGenerator->generate('email'));
    }

    /**
     * @covers Mage_Selenium_Helper_DataGenerator::generateEmailAddress
     * @depends test__construct
     */
    public function testGenerateEmailAddress()
    {
        $dataGenerator = new Mage_Selenium_Helper_DataGenerator($this->_config);
        $this->assertNotEmpty($dataGenerator->generateEmailAddress());
        $this->assertEquals(20, strlen($dataGenerator->generateEmailAddress()));
        $this->assertEquals(20, strlen($dataGenerator->generateEmailAddress(20)));
        $this->assertEmpty($dataGenerator->generateEmailAddress(0));
        $this->assertEmpty($dataGenerator->generateEmailAddress(-1));

        $this->assertEquals(20, strlen($dataGenerator->generateEmailAddress(20, 'valid')));
        $this->assertEquals(20, strlen($dataGenerator->generateEmailAddress(20, 'invalid')));
        $this->assertEquals(20, strlen($dataGenerator->generateEmailAddress(20, 'some_value')));

        $this->assertRegExp("/^([a-z0-9,!\#\$%&'\*\+\/=\?\^_`\{\|\}~-])+(\.([a-z0-9,!\#\$%&'\*\+\/=\?\^_`\{\|\}~-])+)*@([a-z0-9-])+(\.([a-z0-9-])+)*\.(([a-z]){2,})$/i",
            $dataGenerator->generateEmailAddress(20, 'valid'));
        $this->assertNotRegExp("/^([a-z0-9,!\#\$%&'\*\+\/=\?\^_`\{\|\}~-])+(\.([a-z0-9,!\#\$%&'\*\+\/=\?\^_`\{\|\}~-])+)*@([a-z0-9-])+(\.([a-z0-9-])+)*\.(([a-z]){2,})$/i",
            $dataGenerator->generateEmailAddress(20, 'invalid'));
    }

    /**
     * @covers Mage_Selenium_Helper_DataGenerator::generateRandomString
     * @depends test__construct
     */
    public function testGenerateRandomString()
    {
        $dataGenerator = new Mage_Selenium_Helper_DataGenerator($this->_config);
        $this->assertNotEmpty($dataGenerator->generateRandomString());
        $this->assertEquals(100, strlen($dataGenerator->generateRandomString()));
        $this->assertEquals(20, strlen($dataGenerator->generateRandomString(20)));
        $this->assertEmpty($dataGenerator->generateRandomString(0));
        $this->assertEmpty($dataGenerator->generateRandomString(-1));

        $this->assertEquals(20, strlen($dataGenerator->generateRandomString(20, ':alnum:')));

        $this->assertRegExp('|^[a-zA-Z0-9]{20}$|', $dataGenerator->generateRandomString(20, ':alnum:'));
        $this->assertRegExp('|^[a-zA-Z]{20}$|', $dataGenerator->generateRandomString(20, ':alpha:'));
        $this->assertRegExp('|^[0-9]{20}$|', $dataGenerator->generateRandomString(20, ':digit:'));
        $this->assertRegExp('|^[a-z]{20}$|', $dataGenerator->generateRandomString(20, ':lower:'));
        $this->assertRegExp('/^[[:punct:]]{30}$/', $dataGenerator->generateRandomString(30, ':punct:'));
        $this->assertRegExp('|^[\(\)\[\]\\\\\;\:\,\<\>@]{20}$|', $dataGenerator->generateRandomString(20, 'invalid-email'));
    }

    /**
     * @covers Mage_Selenium_Helper_DataGenerator::generateRandomText
     * @depends test__construct
     */
    public function testGenerateRandomText()
    {
        $dataGenerator = new Mage_Selenium_Helper_DataGenerator($this->_config);
        $this->assertNotEmpty($dataGenerator->generateRandomText());
        $this->assertEquals(100, strlen($dataGenerator->generateRandomText()));
        $this->assertEquals(20, strlen($dataGenerator->generateRandomText(20)));
        $this->assertEmpty($dataGenerator->generateRandomText(0));
        $this->assertEmpty($dataGenerator->generateRandomText(-1));

        $this->assertEquals(20, strlen($dataGenerator->generateRandomText(20, '')));
        $this->assertEquals(20, strlen($dataGenerator->generateRandomText(20, array('class'=>':alnum:', 'para'=>3))));
        $this->assertEquals(20, strlen($dataGenerator->generateRandomText(20, array('para'=>0))));

        $randomText = $dataGenerator->generateRandomText(50, array('para'=>5));
        $this->assertEquals(5, count(explode("\n", $randomText)));

        $this->assertRegExp('|^[a-zA-Z0-9 ]{20}$|', $dataGenerator->generateRandomText(20, array('class'=>':alnum:')));
        $this->assertRegExp('|^[a-zA-Z ]{20}$|', $dataGenerator->generateRandomText(20, array('class'=>':alpha:')));
        $this->assertRegExp('|^[0-9 ]{20}$|', $dataGenerator->generateRandomText(20, array('class'=>':digit:')));
        $this->assertRegExp('|^[a-z ]{20}$|', $dataGenerator->generateRandomText(20, array('class'=>':lower:')));
        $this->assertRegExp('|^[[:punct:] ]{20}$|', $dataGenerator->generateRandomText(20, array('class'=>':punct:')));
        $this->assertRegExp('|^[[:punct:] ]{20}$|', $dataGenerator->generateRandomText(20, ':punct:'));
        $this->assertRegExp('|^[[:alnum:] ]{20}$|', $dataGenerator->generateRandomText(20, ''));
    }

    /**
     * @covers Mage_Selenium_Helper_DataGenerator::generateException
     * @depends test__construct
     *
     * @expectedException Mage_Selenium_Exception
     */
    public function testGenerateException()
    {
        $dataGenerator = new Mage_Selenium_Helper_DataGenerator($this->_config);
        $this->assertNull($dataGenerator->generate('some_string'));
    }
}