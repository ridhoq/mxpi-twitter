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
 * @package     selenium2
 * @subpackage  runner
 * @author      Magento Core Team <core@magentocommerce.com>
 * @copyright   Copyright (c) 2012 Magento Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
if (!defined('INTEGRATION_TEST_FLAG')) {
    if (version_compare(PHPUnit_Runner_Version::id(), '3.6.0', '<')) {
        throw new RuntimeException('PHPUnit 3.6.0 (or later) is required.');
    }
}

$rootDir = dirname(dirname(dirname(__FILE__)));
define('TBP', $rootDir . DIRECTORY_SEPARATOR . 'tests');

require_once $rootDir . DIRECTORY_SEPARATOR . 'tests/framework/Mage.php';

if (!defined('INTEGRATION_TEST_FLAG')) {
    require_once $rootDir . DIRECTORY_SEPARATOR . 'tests/framework/Xcom_TestCase_Abstract.php';
    require_once $rootDir . DIRECTORY_SEPARATOR . 'tests/framework/Xcom_TestCase.php';
    require_once $rootDir . DIRECTORY_SEPARATOR . 'tests/framework/Xcom_Database_TestCase.php';
    require_once $rootDir . DIRECTORY_SEPARATOR . 'tests/framework/Xcom_Collection_TestCase.php';
    require_once $rootDir . DIRECTORY_SEPARATOR . 'tests/framework/Xcom_Integration_TestCase.php';
    require_once $rootDir . DIRECTORY_SEPARATOR . 'tests/framework/Xcom_Fabric_TestCase.php';
    require_once $rootDir . DIRECTORY_SEPARATOR . 'tests/framework/Xcom_Messaging_TestCase.php';
}

Mage::setRoot($rootDir . DIRECTORY_SEPARATOR . 'app');

if (!defined('INTEGRATION_TEST_FLAG')) {
    Mage::app();
}
