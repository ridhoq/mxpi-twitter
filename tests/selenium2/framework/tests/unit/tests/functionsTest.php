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
class Mage_FunctionsTest extends Mage_PHPUnit_TestCase
{
    /**
     * @covers array_replace_recursive
     */
    public function test_array_replace_recursiveExists()
    {
        $this->assertTrue(function_exists('array_replace_recursive'));
    }

    /**
     * @covers array_replace_recursive
     *
     * @dataProvider test_array_replace_recursiveDataProvider
     */
    public function test_array_replace_recursive($arraySource, $arrayToMerge, $expected)
    {
        $result = array_replace_recursive($arraySource, $arrayToMerge);
        $this->assertNotNull($result);
        $this->assertEquals($result, $expected);
    }

    public function test_array_replace_recursiveDataProvider()
    {
        return array(
            array(array('browser' => array('default' => array('browser' => 'chrome')), 'applications' => array('magento-ce')),
                  array('browser' => array('default' => array('browser' => 'firefox'), 'firefox')),
                  array('browser' => array('default' => array('browser' => 'firefox'), 'firefox'), 'applications' => array('magento-ce'))),
        );
    }
}