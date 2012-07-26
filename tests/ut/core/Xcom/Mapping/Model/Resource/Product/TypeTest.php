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
class Xcom_Mapping_Model_Resource_Product_TypeTest extends Xcom_TestCase
{
    /** @var Xcom_Mapping_Model_Resource_Product_Type */
    protected $_object;
    protected $_instanceOf = 'Xcom_Mapping_Model_Resource_Product_Type';
    protected $_resource;
    protected $_productType;

    public function setUp()
    {
        parent::setUp();
        $this->_object = new Public_Xcom_Mapping_Model_Resource_Product_Type();
        $this->_resource = new Mage_Core_Model_Resource();
        $this->_productType = new Xcom_Mapping_Model_Product_Type();
        $this->_productType
            ->setId(101)
            ->setProductTypeId(1)
            ->setIsMultiselect(1)
            ->setLocaleCode('en_US')
            ->setName('Product Type');
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
        $localeTable = $this->_resource->getTableName('xcom_mapping/product_type_locale');

        $expectedLocaleData = array(
            'locale_code'           => 'en_US',
            'mapping_product_type_id'       => 101,
            'name'                  => 'Product Type',
        );

        $adaptorMock = $this->getMock('Fixture_Varien_Db_Adapter_Pdo_Mysql', array('insertOnDuplicate'), array(), '',
            false);
        $objectMock = $this->getMock(get_class($this->_object), array('_getWriteAdapter'));
        $adaptorMock->expects($this->any())
            ->method('insertOnDuplicate')
            ->with($this->equalTo($localeTable),
                   $this->equalTo($expectedLocaleData),
                   $this->equalTo($this->_object->getLocaleTableColumns()));

        $objectMock->expects($this->any())
            ->method('_getWriteAdapter')
            ->will($this->returnValue($adaptorMock));

        $objectMock->afterSave($this->_productType);


    }
}

class Fixture_Varien_Db_Adapter_Pdo_Mysql
{
    public function insertOnDuplicate($table, $data, $bind)
    {
    }
}

class Public_Xcom_Mapping_Model_Resource_Product_Type extends Xcom_Mapping_Model_Resource_Product_Type
{
    public function afterSave($object)
    {
        return $this->_afterSave($object);
    }

    public function getLocaleTableColumns()
    {
        return $this->_localeTableColumns;
    }
}
