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
 * @package     Xcom_Mapping
 * @copyright   Copyright (c) 2010 Magento Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class Xcom_Mapping_Model_Resource_Attribute_ValueTest extends Xcom_TestCase
{
    /** @var Xcom_Mapping_Model_Resource_Attribute_Value */
    protected $_object;
    protected $_instanceOf = 'Xcom_Mapping_Model_Resource_Attribute_Value';

    public function setUp()
    {
        parent::setUp();
        $this->_object = new Xcom_Mapping_Model_Resource_Attribute_Value();
    }

    public function tearDown()
    {
        $this->_object = null;
    }

    public function testInstanceOf()
    {
        $this->assertInstanceOf($this->_instanceOf, $this->_object);
    }

    public function testBeforeSave()
    {
        $testObject = new Varien_Object(array('channel_codes' => array('test_1', 'test_2')));
        $object = new Fixture_Xcom_Mapping_Model_Resource_Attribute_Value();
        $object->beforeSave($testObject);

        $this->assertEquals('test_1,test_2', $testObject->getData('channel_codes'));
    }

    public function testAfterLoad()
    {
        $testObject = new Varien_Object(array('channel_codes' => 'test_1,test_2'));
        $this->_object->afterLoad($testObject);

        $this->assertEquals(array('test_1', 'test_2'), $testObject->getData('channel_codes'));
    }
}
class Fixture_Xcom_Mapping_Model_Resource_Attribute_Value extends Xcom_Mapping_Model_Resource_Attribute_Value
{
    public function beforeSave($object)
    {
        return parent::_beforeSave($object);
    }
}
