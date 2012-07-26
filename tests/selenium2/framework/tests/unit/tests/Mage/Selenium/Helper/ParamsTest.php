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
class Mage_Selenium_Helper_ParamsTest extends Mage_PHPUnit_TestCase
{

    /**
     * @covers Mage_Selenium_Helper_Params::__construct
     */
    public function test__construct()
    {
        $params = new Mage_Selenium_Helper_Params();
        $this->assertInstanceOf('Mage_Selenium_Helper_Params', $params);
    }

    /**
     * @covers Mage_Selenium_Helper_Params::__construct
     * @dataProvider test__constructWithParamsDataProvider
     */
    public function test__constructWithParams($paramName, $paramvalue)
    {
        $params = new Mage_Selenium_Helper_Params(array($paramName => $paramvalue));
        $this->assertInstanceOf('Mage_Selenium_Helper_Params', $params);
        $this->assertEquals($params->getParameter($paramName), $paramvalue);
    }

    public function test__constructWithParamsDataProvider()
    {
        return array(
            array('paramName', 'paramValue'),
            array('', ''),
        );
    }

    /**
     * @covers Mage_Selenium_Helper_Params::getParameter
     * @covers Mage_Selenium_Helper_Params::setParameter
     * @dataProvider testGetSetParameterDataProvider
     */
    public function testGetSetParameter($name, $value)
    {
        $params = new Mage_Selenium_Helper_Params();
        $params->setParameter($name, $value);
        $this->assertEquals($params->getParameter($name), $value);
    }

    public function testGetSetParameterDataProvider()
    {
        return array(
            array('', 'some value'),
            array('somekey', ''),
            array('%', ''),
            array('user_id', 1),
        );
    }

    /**
     * @covers Mage_Selenium_Helper_Params::getParameter
     * @covers Mage_Selenium_Helper_Params::setParameter
     */
    public function testGetSetParameterExistingName()
    {
        $name = 'some name';
        $value1 = 'some value';
        $value2 = 'another value';
        $params = new Mage_Selenium_Helper_Params();
        $params->setParameter($name, $value1);
        $params->setParameter($name, $value2);
        $this->assertEquals($params->getParameter($name), $value2);
        $this->assertNotEquals($params->getParameter($name), $value1);
    }

    /**
     * @covers Mage_Selenium_Helper_Params::replaceParameter
     * @dataProvider testReplaceParametersDataProvider
     */
    public function testReplaceParameters($paramsArray, $sourceToReplace, $expected)
    {
        $params = new Mage_Selenium_Helper_Params($paramsArray);
        $result = $params->replaceParameters($sourceToReplace);
        $this->assertEquals($result, $expected);
    }

    public function testReplaceParametersDataProvider()
    {
        return array(
            array(null, 'id=%id%', 'id=%id%'),
            array(array('id' => 13), 'id=%id%', 'id=13'),
            array(array('id' => 13), 'id=%id%&name=%name%', 'id=13&name=%name%'),
            array(array('id' => 13, 'name' => 'Chuck Norris'), 'id=%id%&name=%name%', 'id=13&name=Chuck Norris'),
            array(array('id' => 13), '', ''),
            array(array('id' => 13), null, null),
        );
    }

    /**
     * @covers Mage_Selenium_Helper_Params::replaceParametersWithRegexp
     * @dataProvider testReplaceParametersWithRegexpDataProvider
     */
    public function testReplaceParametersWithRegexp($paramsArray, $sourceToReplace, $regexp, $expected)
    {
        $params = new Mage_Selenium_Helper_Params($paramsArray);
        $result = $params->replaceParametersWithRegexp($sourceToReplace, $regexp);
        $this->assertEquals($result, $expected);
    }

    public function testReplaceParametersWithRegexpDataProvider()
    {
        return array(
            array(array('id' => 13), 'id=%id%', 'REGEXP', 'id=REGEXP'),
            array(array('id' => 13, 'name' => 'Chuck Norris'), 'id=%id%&name=%name%', 'REGEXP', 'id=REGEXP&name=REGEXP'),
            array(null, 'id=%id%', 'REGEXP', 'id=%id%'),
            array(array('name' => 'Chuck Norris'), 'id=%id%', 'REGEXP', 'id=%id%'),
        );
    }
}