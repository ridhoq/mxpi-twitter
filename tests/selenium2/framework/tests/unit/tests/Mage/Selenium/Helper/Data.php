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
class Mage_Selenium_Helper_DataTest extends Mage_PHPUnit_TestCase
{
    /**
     * @covers Mage_Selenium_TestConfiguration::GetDataValue
     */
    public function testGetDataValue()
    {
        $this->assertInternalType('array', $this->_config->getDataValue());
        $this->assertNotEmpty($this->_config->getDataValue());

        $this->assertFalse($this->_config->getDataValue('invalid-path'));

        $this->assertArrayHasKey('generic_admin_user', $this->_config->getDataValue());
        $this->assertInternalType('array', $this->_config->getDataValue('generic_admin_user'));
        $this->assertInternalType('string', $this->_config->getDataValue('generic_admin_user/user_name'));
    }
}