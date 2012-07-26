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

class Xcom_Ebay_Block_Adminhtml_Channel_Edit_Tab_PolicyTest extends Xcom_TestCase
{
    protected $_object;

    public function setUp()
    {
        $this->_checkConnection = true;
        parent::setUp();
    }

    public function tearDown()
    {
        $this->_object = null;
    }

    public function testGetFormNoChannel()
    {
        $objectMock = $this->getMock('Xcom_Ebay_Block_Adminhtml_Channel_Edit_Tab_Policy',
            array('_construct', 'getChannel', '_getAdditionalClass'));
        $objectMock->expects($this->any())
            ->method('_construct');
        $objectMock->expects($this->any())
            ->method('getChannel')
            ->will($this->returnValue(new Varien_Object(array('id' => 0))));
        $objectMock->expects($this->once())
            ->method('_getAdditionalClass')
            ->will($this->returnValue(new Varien_Object('')));

        $result = $objectMock->getForm();
        $this->assertInstanceOf('Varien_Data_Form', $result);

        $this->assertEquals('policy_edit_form', $result->getId());
        $this->assertTrue($result->getUseContainer());

        $fieldsetElements = $result->getElements();
        $this->assertInstanceOf('Varien_Data_Form_Element_Collection', $fieldsetElements);
        $this->assertGreaterThan(0, count($fieldsetElements->count()));

        $fieldset = $fieldsetElements[0];
        $this->assertInstanceOf('Varien_Data_Form_Element_Fieldset', $fieldset);
        $this->assertEquals('accordion_fieldset', $fieldset ->getId());

        $fieldElements = $fieldset->getSortedElements();
        $this->assertTrue(is_array($fieldElements));

        $this->assertEquals('accordion', $fieldElements[0]->getId());
        $this->assertEquals('note', $fieldElements[0]->getType());
    }

    public function testGetForm()
    {
        $objectMock = $this->getMock('Xcom_Ebay_Block_Adminhtml_Channel_Edit_Tab_Policy',
            array('_construct', 'getChannel', '_getAdditionalClass'));
        $objectMock->expects($this->any())
            ->method('_construct');
        $objectMock->expects($this->any())
            ->method('getChannel')
            ->will($this->returnValue(new Varien_Object(array('id' => 1))));
        $objectMock->expects($this->once())
            ->method('_getAdditionalClass')
            ->will($this->returnValue(new Varien_Object('')));

        $result = $objectMock->getForm();
        $this->assertInstanceOf('Varien_Data_Form', $result);

        $this->assertEquals('policy_edit_form', $result->getId());
        $this->assertTrue($result->getUseContainer());

        $fieldsetElements = $result->getElements();
        $this->assertInstanceOf('Varien_Data_Form_Element_Collection', $fieldsetElements);
        $this->assertGreaterThan(0, count($fieldsetElements->count()));

        // channel_id
        $elem = $fieldsetElements[0];
        $this->assertInstanceOf('Varien_Data_Form_Element_Hidden', $elem);
        $this->assertEquals('channel_id', $elem->getid());
        $this->assertEquals('hidden', $elem->getType());

        // accordion
        $elem = $fieldsetElements[1];
        $this->assertInstanceOf('Varien_Data_Form_Element_Fieldset', $elem);
        $this->assertEquals('accordion_fieldset', $elem ->getid());

        $fieldElements = $elem->getSortedElements();
        $this->assertTrue(is_array($fieldElements));

        $this->assertEquals('accordion', $fieldElements[0]->getId());
        $this->assertEquals('note', $fieldElements[0]->getType());
    }

    public function testGetChannel()
    {
        $objectMock = $this->getMock('Xcom_Ebay_Block_Adminhtml_Channel_Edit_Tab_Policy', array('_construct'));
        $objectMock->expects($this->any())
            ->method('_construct');

        Mage::clearRegistry();
        $this->assertNull($objectMock->getChannel());

        Mage::register('current_channel', new Varien_Object());
        $result = $objectMock->getChannel();

        $this->assertNotEmpty($result);
        $this->assertInstanceOf('Varien_Object', $result);
        Mage::clearRegistry();
    }
}
