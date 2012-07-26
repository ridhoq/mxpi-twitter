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
class Xcom_Ebay_Block_Adminhtml_Channel_Edit_ChannelTest extends Xcom_TestCase
{
    protected $_object;

    public function setUp()
    {
        $this->_checkConnection = true;
        parent::setUp();
//        $this->_object = new Xcom_Ebay_Block_Adminhtml_Channel_Edit_Channel();
    }

    public function tearDown()
    {
        $this->_object = null;
    }

    public function testIsActiveFieldEnabledByDefault()
    {
        $objectMock = $this->getMock('Xcom_Ebay_Block_Adminhtml_Channel_Edit_Tab_ChannelFixture', array('getChannel'));
        $objectMock->expects($this->any())
            ->method('getChannel')
            ->will($this->returnValue(new Varien_Object()));

        $siteMock = $this->mockModel('xcom_ebay/source_site', array('toOptionArray'));
        $siteMock->expects($this->once())
            ->method('toOptionArray')
            ->will($this->returnValue(array()));

        $accountMock = $this->mockModel('xcom_ebay/source_account', array('getActiveAccountHash'));
        $accountMock->expects($this->once())
            ->method('getActiveAccountHash')
            ->will($this->returnValue(array()));

        $objectMock->prepareForm();
        $isActiveField = $objectMock->getForm()->getElement('is_active');

        $this->assertEquals(1, $isActiveField->getValue(), 'is_active field value should be set to Enabled');
    }

    public function testGetEnabledAndValidAccounts()
    {
        $object = new Xcom_Ebay_Block_Adminhtml_Channel_Edit_Tab_ChannelFixture();
//        Mage::registry('current_channel', new Varien_Object());
        $arrayResult = array(
            array(
                'value' => 'test_id_1',
                'label' => 'test_user_id_1',
            ),
            array(
                'value' => 'test_id_2',
                'label' => 'test_user_id_2',
            ),
        );
        $sourceAccount = $this->mockModel('xcom_ebay/source_account', array('toOptionArray'));
        $sourceAccount->expects($this->any())
            ->method('toOptionArray')
            ->will($this->returnValue($arrayResult));


        $result = $object->getEnabledAndValidAccounts();

        $this->assertEquals('test_id_2', $result[1]['value']);
        $this->assertEquals('test_user_id_2', $result[1]['label']);
    }
}

/**
 * Class fixture
 */
class Xcom_Ebay_Block_Adminhtml_Channel_Edit_Tab_ChannelFixture
    extends Xcom_Ebay_Block_Adminhtml_Channel_Edit_Tab_Channel
{
    /**
     * Publishing _prepareForm()
     *
     * @return Mage_Adminhtml_Block_Widget_Form
     */
    public function prepareForm()
    {
        return parent::_prepareForm();
    }

    /**
     * Publishing _getEnabledAndValidAccounts()
     *
     * @return Mage_Adminhtml_Block_Widget_Form
     */
    public function getEnabledAndValidAccounts()
    {
        return parent::_getEnabledAndValidAccounts();
    }
}
