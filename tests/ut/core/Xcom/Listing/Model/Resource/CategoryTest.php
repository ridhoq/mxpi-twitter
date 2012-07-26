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
 * @package     Xcom_Listing
 * @copyright   Copyright (c) 2010 Magento Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class Xcom_Listing_Model_Resource_CategoryTest extends Xcom_TestCase
{
    /**
     * @var Xcom_Listing_Model_Resource_Category
     */
    protected $_object     = null;
    protected $_instanceOf = 'Xcom_Listing_Model_Resource_Category';
    /** @var Xcom_Listing_Model_Category */
    protected $_category;

    public function setUp()
    {
        parent::setUp();
        $this->_object = new Xcom_Listing_Model_Resource_Category();
        $this->_category = new Xcom_Listing_Model_Category();
    }

    public function tearDown()
    {
        $this->_object = null;
        $this->_category = null;
    }

    public function testInstanceOf()
    {
        $this->assertInstanceOf($this->_instanceOf, $this->_object);
    }

    public function testGetRecommendedCategoryIds()
    {
        $productType = new Varien_Object();
        $productType->setId('test_id_1');
        $productTypeId = 'test_id_1';
        $expectedResult = array(1,2,3);
        $selectMock = $this->_getSelectMock(array('category_id'), 'mapping_product_type_id', 'test_id_1');

        $adapterMock = $this->_getAdapterMock($selectMock, $expectedResult);
        $this->_mockReadAdapter($adapterMock);

        $result = $this->_object->getRecommendedCategoryIds($productTypeId);
        $this->assertEquals($expectedResult, $result);
    }

    protected function _mockAdapterQuoteInto($adapterMock, $expects, $will)
    {
        $adapterMock->expects($expects)
            ->method('quoteInto')
            ->will($this->returnValue($will));
    }

    protected function _prepareFixtureObject()
    {
        $object = new Fixture_Xcom_Listing_Model_Resource_Category();
        $this->_object = $object;
    }

    protected function _mockReadAdapter($adapterMock)
    {
        $objectMock = $this->getMock(get_class($this->_object), array('_getReadAdapter'));
        $objectMock->expects($this->any())
            ->method('_getReadAdapter')
            ->will($this->returnValue($adapterMock));
        $this->_object = $objectMock;
    }

    protected function _getSelectMock($columns, $whereColumn, $whereValue)
    {
        $productTypeTable = $this->_object->getTable('xcom_listing/category_product_type');
        $selectMock = $this->getMock('Fixture_Varien_Db_Select_Category', array('where', 'from'));
        $selectMock->expects($this->once())
            ->method('from')
            ->with($this->equalTo($productTypeTable), $this->equalTo($columns))
            ->will($this->returnValue($selectMock));
        $selectMock->expects($this->once())
            ->method('where')
            ->with($this->equalTo("{$whereColumn} = ?"), $this->equalTo($whereValue))
            ->will($this->returnValue($selectMock));
        return $selectMock;
    }

    protected function _getAdapterMock($selectObject, $expectedFetchCol = array())
    {
        $methods = array('select', 'fetchCol');
        $adapterMock = $this->getMock('Fixture_Varien_Db_Adapter_Pdo_Mysql_Category', $methods);
        $adapterMock->expects($this->any())
            ->method('select')
            ->will($this->returnValue($selectObject));
        $adapterMock->expects($this->any())
            ->method('fetchCol')
            ->with($this->equalTo($selectObject))
            ->will($this->returnValue($expectedFetchCol));

        return $adapterMock;
    }
}

class Fixture_Varien_Db_Adapter_Pdo_Mysql_Category
{
    public function fetchCol($select)
    {
    }
}
class Fixture_Varien_Db_Select_Category
{
    public function from()
    {}

    public function select()
    {}

    public function insertMultiple()
    {}
}
class Fixture_Xcom_Listing_Model_Resource_Category extends Xcom_Listing_Model_Resource_Category
{
    public function afterSave($object)
    {
        return parent::_afterSave($object);
    }
}
