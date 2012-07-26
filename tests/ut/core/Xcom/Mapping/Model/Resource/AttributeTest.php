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
class Xcom_Mapping_Model_Resource_AttributeTest extends Xcom_TestCase
{
    /** @var Xcom_Mapping_Model_Resource_Attribute */
    protected $_object;
    protected $_instanceOf = 'Xcom_Mapping_Model_Resource_Attribute';
    protected $_resource;
    protected $_attribute;

    public function setUp()
    {
        parent::setUp();
        $this->_resource = new Mage_Core_Model_Resource();
        $this->_object = new Public_Xcom_Mapping_Model_Resource_Attribute();
        $this->_attribute = new Xcom_Mapping_Model_Attribute();
        $this->_attribute
            ->setId(101)
            ->setProductTypeId(1)
            ->setIsMultiselect(1)
            ->setLocaleCode('en_US')
            ->setName('Attribute Name')
            ->setChannelDecoration(array(
                array('channel_code' => 'channel_code_1', 'is_required' => 1, 'is_variation' => 0),
                array('channel_code' => 'channel_code_2', 'is_required' => 0, 'is_variation' => 1),
                array('channel_code' => 'channel_code_3', 'is_required' => 1, 'is_variation' => 1)));

    }

    public function tearDown()
    {
        $this->_object = null;
    }

    public function testInstanceOf()
    {
        $this->assertInstanceOf($this->_instanceOf, $this->_object);
    }

    public function testAfterSave()
    {
        $channelTable = $this->_resource->getTableName('xcom_mapping/attribute_channel');
        $localeTable = $this->_resource->getTableName('xcom_mapping/attribute_locale');
        $expectedChannelData = array(
                array('channel_code' => 'channel_code_1',
                    'is_required' => 1,
                    'is_variation' => 0,
                    'mapping_attribute_id' => 101),
                array('channel_code' => 'channel_code_2',
                    'is_required' => 0,
                    'is_variation' => 1,
                    'mapping_attribute_id' => 101),
                array('channel_code' => 'channel_code_3',
                    'is_required' => 1,
                    'is_variation' => 1,
                    'mapping_attribute_id' => 101)
            );
        $expectedLocaleData = array(
            'mapping_attribute_id'  => 101,
            'locale_code'   => 'en_US',
            'name'          =>'Attribute Name'
        );

        $adaptorMock = $this->getMock('Fixture_Varien_Db_Adapter_Pdo_Mysql_Mapping', array('insertOnDuplicate'),
            array(), '', false);
        $objectMock = $this->getMock(get_class($this->_object), array('_getWriteAdapter'));
        $adaptorMock->expects($this->at(0))
            ->method('insertOnDuplicate')
            ->with($this->equalTo($localeTable),
                   $this->equalTo($expectedLocaleData),
                   $this->equalTo($this->_object->getLocaleTableColumns()));

        $adaptorMock->expects($this->at(1))
            ->method('insertOnDuplicate')
            ->with($this->equalTo($channelTable),
                   $this->equalTo($expectedChannelData),
                   $this->equalTo(array('is_required', 'is_variation')));

        $objectMock->expects($this->any())
            ->method('_getWriteAdapter')
            ->will($this->returnValue($adaptorMock));

        $objectMock->afterSave($this->_attribute);


    }
}

class Fixture_Varien_Db_Adapter_Pdo_Mysql_Mapping
{
    public function insertOnDuplicate($table, $data, $bind)
    {
    }
}

class Public_Xcom_Mapping_Model_Resource_Attribute extends Xcom_Mapping_Model_Resource_Attribute
{
    public function afterSave($object)
    {
        return $this->_afterSave($object);
    }

    public function getLocaleTable()
    {
        return $this->_localeTable;
    }
    public function getLocaleTableColumns()
    {
        return $this->_localeTableColumns;
    }
}
