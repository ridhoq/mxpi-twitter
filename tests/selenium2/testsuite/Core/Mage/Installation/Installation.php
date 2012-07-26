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
 * @package     selenium
 * @subpackage  tests
 * @author      Magento Core Team <core@magentocommerce.com>
 * @copyright   Copyright (c) 2010 Magento Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/**
 * Customer registration tests
 *
 * @package     selenium
 * @subpackage  tests
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class Core_Mage_Installation extends Mage_Selenium_TestCase
{
    /**
     * Make sure that customer is not logged in, and navigate to homepage
     * @BeforeMethod
     */
    protected function assertPreConditions()
    {
        $data = $this->loadData('configuration_data');
        $host = $data['host'];
        $user = $data['user_name'];
        $pswd = $data['user_password'];
        $baseName = $data['database_name'];
        mysql_connect($host, $user, $pswd) or die("Couldn't connect to MySQL server!");
        mysql_query("DROP DATABASE IF EXISTS `$baseName`");
        mysql_query("CREATE DATABASE `$baseName`") or die("Couldn't create DATABASE!");
        //$this->installationHelper()->removeInstallData();
    }

    /**
     * @test
     */
    public function installTest()
    {
        $this->setArea('frontend');
        $this->open($this->_configHelper->getBaseUrl());

        // 'License Agreement' page
        $this->assertTrue($this->checkCurrentPage('license_agreement'), $this->getParsedMessages());
        $this->fillForm($this->loadData('license_agreement_data'));
        $this->clickButton('continue');

        // 'Localization' page
        $localeData = $this->loadData('localization_data');
        $this->assertTrue($this->checkCurrentPage('localization'), $this->getParsedMessages());
        $this->fillForm($localeData);

        // Add 'config' parameter to UImap
        $page = $this->getCurrentLocationUimapPage();
        $config = '?';
        $i = 1;
        $n = count($localeData);
        foreach ($localeData as $key => $value) {
            $xpath = $page->findDropdown($key);
            $v = $this->getValue($xpath . "/option[text()='$value']");
            $config .="config[$key]=$v";
            if ($i < $n) {
                $config .= '&';
            }
            $i++;
        }
        $this->addParameter('config', urlencode($config));

        $this->clickButton('continue');

        // 'Configuration' page
        $this->assertMessagePresent('error');
        $this->assertTrue($this->checkCurrentPage('configuration'), $this->getParsedMessages());
        $this->fillForm('configuration_data');
        $this->clickButton('continue');

        // 'Create Admin Account' page
        $this->assertMessagePresent('error');
        $this->assertTrue($this->checkCurrentPage('create_admin_account'), $this->getParsedMessages());
        $this->fillForm('admin_account_data');
        $this->clickButton('continue');

        // 'You're All Set!' page
        $this->assertMessagePresent('error');
        $this->assertTrue($this->checkCurrentPage('end_installation'), $this->getParsedMessages());

        // Log in to Admin
        $this->loginAdminUser();
        $this->assertTrue($this->checkCurrentPage('dashboard'), $this->getParsedMessages());
        //Go to Frontend
        $this->assertTrue($this->frontend());
    }
}