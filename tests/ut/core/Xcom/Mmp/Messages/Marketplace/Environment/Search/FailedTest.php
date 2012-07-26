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
 * @category    Xcom
 * @package     Xcom_Mmp
 * @copyright   Copyright (c) 2010 Magento Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

class Xcom_Mmp_Message_Marketplace_Environment_Search_FailedTest extends Xcom_TestCase
{
    const TOPIC = 'marketplace/environment/searchFailed';

    protected $_object;

    public function setUp()
    {
        $this->_object = Mage::helper('xcom_xfabric')->getMessage('marketplace/environment/searchFailed');
        parent::setUp();
    }

    public function tearDown()
    {
        parent::tearDown();
        $this->_object = null;
    }

    public function testTopic()
    {
        $this->assertEquals(self::TOPIC, $this->_object->getTopic());
    }

    /**
     * @expectedException Mage_Core_Exception
     */
    public function testProcess()
    {
        $body = 'test';
        $this->_object->setBody($body);
        $this->_object->process();
    }
}
