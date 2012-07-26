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
class Xcom_Mapping_Model_Product_TypeTest extends Xcom_TestCase
{
    /** @var Xcom_Mapping_Model_Product_Type */
    protected $_object;
    protected $_instanceOf = 'Xcom_Mapping_Model_Product_Type';
    public function setUp()
    {
        parent::setUp();
        $this->_object = new Xcom_Mapping_Model_Product_Type();
    }

    public function tearDown()
    {
        $this->_object = null;
    }

    public function testInstanceOf()
    {
        $this->assertInstanceOf($this->_instanceOf, $this->_object);
    }

    public function testSaveRelation()
    {
        $expectedResult = null;
        $this->_mockResourceSaveRelation($expectedResult);

        $result = $this->_object->saveRelation(1,1);
        $this->assertEquals($expectedResult, $result);
    }

    protected function _mockResourceSaveRelation($expectedResult)
    {
        $objectResourceMock = $this->mockResource('xcom_mapping/product_type', array('saveRelation'));
        $objectResourceMock->expects($this->once())
            ->method('saveRelation')
            ->will($this->returnValue($expectedResult));
        return $objectResourceMock;
    }
}
