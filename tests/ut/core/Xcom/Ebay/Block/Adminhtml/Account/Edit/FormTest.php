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
 * @package     Xcom_Ebay
 * @copyright   Copyright (c) 2010 Magento Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class Xcom_Ebay_Block_Adminhtml_Account_Edit_FormTest extends Xcom_TestCase
{
    /** @var Xcom_Ebay_Block_Adminhtml_Channel */
    protected $_object;

    public function setUp()
    {
        $this->_checkConnection = true;
        parent::setUp();
        $this->_object = new Xcom_Ebay_Block_Adminhtml_Account_Edit_Form();
    }

    public function tearDown()
    {
        $this->_object = null;
    }

    public function testGetAccount()
    {
        Mage::clearRegistry();
        $this->assertNull($this->_object->getAccount());

        Mage::register('current_account', new Varien_Object());
        $result = $this->_object->getAccount();

        $this->assertNotEmpty($result);
        $this->assertInstanceOf('Varien_Object', $result);
        Mage::clearRegistry();
    }

    public function testGetValidatedAtText()
    {
        $validatedAt = date('Y-m-d');

        Mage::clearRegistry();
        Mage::register('current_account', new Varien_Object(array(
            'validated_at' => $validatedAt,
        )));
        $result = $this->_object->getValidatedAtText();
        $this->assertEquals("Valid Authorization Token.<br/>Valid until {$validatedAt}", $result);

        Mage::clearRegistry();
        Mage::register('current_account', new Varien_Object(array(
            'validation_expired' => 1,
        )));
        $result = $this->_object->getValidatedAtText();
        $this->assertEquals('<span class="error">Expired</span>', $result);
    }
}
