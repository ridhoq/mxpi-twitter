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
class Xcom_Mapping_Model_Message_ProductTaxonomy_Get_SucceededTest extends Xcom_TestCase
{
    /** @var Xcom_Mapping_Model_Message_ProductTaxonomy_Get_Succeeded */
    protected $_object;
    protected $_instanceOf = 'Xcom_Mapping_Model_Message_ProductTaxonomy_Get_Succeeded';

    public function setUp()
    {
        parent::setUp();
        $this->_object = Mage::helper('xcom_xfabric')->getMessage('productTaxonomy/getSucceeded');
    }

    public function tearDown()
    {
        $this->_object = null;
    }

    public function testInstanceOf()
    {
        $this->assertInstanceOf($this->_instanceOf, $this->_object);
    }

    public function testGetResponseDataEmpty()
    {
        $this->assertEmpty($this->_object->getResponseData());
    }

    public function testProcess()
    {
        $productClass = $this->mockModel('xcom_mapping/product_class', array('setData', 'save', 'getId'));

        $productClass->expects($this->at(0))
            ->method('setData')
            ->with($this->equalTo(array(
                'locale_code' => 'en_US',
                'product_class_id' => 'test-id-3',
                'name' => 'test-name-3',
                'parent_product_class_id'  => null
            )))
            ->will($this->returnValue($productClass));

        $productClass->expects($this->at(1))
            ->method('save')
            ->will($this->returnValue(1));

        $productClass->expects($this->at(2))
            ->method('getId')
            ->will($this->returnValue(666));

        $productClass->expects($this->at(3))
            ->method('setData')
            ->with($this->equalTo(array(
                'locale_code' => 'en_US',
                'product_class_id' => 'test-id-4',
                'name' => 'test-name-4',
                'parent_product_class_id'  => null
            )))
            ->will($this->returnValue($productClass));

        $productClass->expects($this->at(4))
            ->method('save')
            ->will($this->returnValue(2));

        $productClass->expects($this->at(5))
            ->method('getId')
            ->will($this->returnValue(666));

        $productClass->expects($this->at(6))
            ->method('setData')
            ->with($this->equalTo(array(
                'locale_code' => 'en_US',
                'product_class_id' => 'test-id-5',
                'name' => 'test-name-5',
                'parent_product_class_id'  => '666'
            )));

        $data = array('productTaxonomy' => array(
            'productClasses' => array(
                0 => array(
                    'id' => 'test-id-3',
                    'name' => 'test-name-3',
                    'subClasses' => null
                ),
                1 => array(
                    'id' => 'test-id-4',
                    'name' => 'test-name-4',
                    'subClasses' => array(
                        array(
                            'id' => 'test-id-5',
                            'name' => 'test-name-5',
                            'subClasses' => null
                        )
                    )
                ))
            )
        );
        Mage::register('disable_save_config', 1);
        $this->_object->setBody($data);
        $this->_object->process();
    }


}
