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
class Xcom_Mmp_Model_Resource_PaymentMethodTest extends Xcom_TestCase
{
    /**
     * @var Xcom_Mmp_Model_Resource_PaymentMethod
     */
    protected $_object     = null;
    protected $_instanceOf = 'Xcom_Mmp_Model_Resource_PaymentMethod';

    public function setUp()
    {
        parent::setUp();
        $this->_object = new Xcom_Mmp_Model_Resource_PaymentMethod();
    }

    public function tearDown()
    {
        $this->_object = null;
    }

    public function testInstanceOf()
    {
        $this->assertInstanceOf($this->_instanceOf, $this->_object);
    }

    public function testGetPaymentMethods()
    {
        $channelTypeCode    = 'test_channel_type';
        $mpChannelCode      = 'test_channel_code';
        $environment        = 'test_env';
        $expectedResult     = array('PM_1', 'PM_2');
        $selectMock = $this->_getSelectMock(
            array('method_name'),
            array(
                    'channel_type_code' => $channelTypeCode,
                    'site_code' => $mpChannelCode,
                    'environment' => $environment
            ),
            'method_name');

        $adapterMock = $this->_getAdapterMock($selectMock, $expectedResult);
        $this->_mockReadAdapter($adapterMock);

        $result = $this->_object->getPaymentMethods(new Varien_Object(array(
            'channeltype_code' => $channelTypeCode,
            'site_code' => $mpChannelCode,
            'auth_environment' => $environment
        )));
        $this->assertEquals($expectedResult, $result);
    }

    protected function _mockReadAdapter($adapterMock)
    {
        $objectMock = $this->getMock(get_class($this->_object), array('_getReadAdapter'));
        $objectMock->expects($this->any())
            ->method('_getReadAdapter')
            ->will($this->returnValue($adapterMock));
        $this->_object = $objectMock;
    }

    protected function _getSelectMock($columns, $whereColumn, $order)
    {
        $productTypeTable = $this->_object->getTable('xcom_mmp/payment_method');
        $selectMock = $this->getMock('Fixture_Varien_Db_Select_PaymentMethod', array('from', 'where', 'order'));
        $selectMock->expects($this->once())
            ->method('from')
            ->with($this->equalTo($productTypeTable), $this->equalTo($columns))
            ->will($this->returnValue($selectMock));
        $selectMock->expects($this->at(1))
            ->method('where')
            ->with($this->equalTo("channel_type_code=?"), $this->equalTo($whereColumn['channel_type_code']))
            ->will($this->returnValue($selectMock));
        $selectMock->expects($this->at(2))
            ->method('where')
            ->with($this->equalTo("site_code=?"), $this->equalTo($whereColumn['site_code']))
            ->will($this->returnValue($selectMock));
        $selectMock->expects($this->at(3))
            ->method('where')
            ->with($this->equalTo("environment=?"), $this->equalTo($whereColumn['environment']))
            ->will($this->returnValue($selectMock));
        $selectMock->expects($this->once())
            ->method('order')
            ->with($this->equalTo($order))
            ->will($this->returnValue($selectMock));
        return $selectMock;
    }

    protected function _getAdapterMock($selectObject, $expectedFetchCol = array())
    {
        $methods = array('select', 'fetchCol');
        $adapterMock = $this->getMock('Fixture_Varien_Db_Adapter_Pdo_Mysql_PaymentMethod', $methods);
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

class Fixture_Varien_Db_Adapter_Pdo_Mysql_PaymentMethod
{
    public function fetchCol($select)
    {
    }
}
class Fixture_Varien_Db_Select_PaymentMethod
{

    public function from()
    {}

    public function where()
    {}

    public function order()
    {}
}
