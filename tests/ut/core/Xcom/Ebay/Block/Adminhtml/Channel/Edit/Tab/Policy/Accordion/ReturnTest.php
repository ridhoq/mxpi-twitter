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

class Xcom_Ebay_Block_Adminhtml_Channel_Edit_Tab_Policy_Accordion_ReturnTest extends Xcom_TestCase
{
    /**
     * @var Xcom_Ebay_Block_Adminhtml_Channel_Edit
     */
    public $object;

    public function setUp()
    {
        parent::setUp();

        $channel = Mage::getModel('xcom_ebay/channel');
        $channel->setId(1);
        $channel->setChanneltypeCode('ebay');
        $channel->setSiteCode('US');
        Mage::register('current_channel', $channel);

        $policy = Mage::getModel('xcom_ebay/policy');
        Mage::register('current_policy', $policy);

        /** @var $object Xcom_Ebay_Block_Adminhtml_Channel_Edit */
        $this->object = new Xcom_Ebay_Block_Adminhtml_Channel_Edit_Tab_Policy_Accordion_ReturnMock();
    }

    public function tearDown()
    {
        $this->object = null;
        Mage::unregister('current_channel');
        Mage::unregister('current_policy');
    }
    public function testGetPolicy()
    {
        $this->assertInstanceOf('Xcom_Ebay_Model_Policy', $this->object->getPolicy());
    }

    /**
     * @return void
     */
    public function testGetReturnPeriodValues()
    {
        $data       = $this->object->getReturnPeriodValues(1);
        $expected   = array(array('value' => 1, 'label' => $this->object->__('1 Days')));
        $this->assertEquals($expected, $data);

        $data       = $this->object->getReturnPeriodValues(3);
        $expected   = array(array('value' => 3, 'label' => $this->object->__('3 Days')));
        $this->assertEquals($expected, $data);

        $data       = $this->object->getReturnPeriodValues(14);
        $expected   = array(
            array('value' => 3, 'label' => $this->object->__('3 Days')),
            array('value' => 7, 'label' => $this->object->__('7 Days')),
            array('value' => 14, 'label' => $this->object->__('14 Days'))
        );
        $this->assertEquals($expected, $data);

        $data       = $this->object->getReturnPeriodValues(66);
        $this->assertEquals(count($data), 6);
        $this->assertEquals($data[5], array('value' => 66, 'label' => $this->object->__('66 Days')));
    }

    /**
     * @return void
     */
    public function testCountChildren()
    {
        $this->object->toHtml();
        $fieldset   = $this->object->getForm()->getElement('return_fieldset');

        $this->assertEquals(5, $fieldset->getElements()->count(), 'Wrong form elements for US channel');

        //for UK channel
        $channel = Mage::registry('current_channel');
        $channel->setSiteCode('UK');

        $this->object->toHtml();
        $fieldset   = $this->object->getForm()->getElement('return_fieldset');
        $this->assertEquals(1, $fieldset->getElements()->count(), 'Wrong form elements for UK channel');

    }
}

class Xcom_Ebay_Block_Adminhtml_Channel_Edit_Tab_Policy_Accordion_ReturnMock
    extends Xcom_Ebay_Block_Adminhtml_Channel_Edit_Tab_Policy_Accordion_Return
{
    public function getReturnPolicies()
    {
        return array(
            'returnsAccepted'   => true,
            'maxReturnByDays'   => 60,
            'method'            => array('MONEY_BACK', 'STORE_CREDIT'));
    }
}
